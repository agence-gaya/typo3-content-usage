<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Tests\Functional\Domain\Repository;

use GAYA\ContentUsage\Domain\Model\Ctype;
use GAYA\ContentUsage\Domain\Model\Doktype;
use GAYA\ContentUsage\Domain\Repository\ContentRepository;
use GAYA\ContentUsage\Domain\Repository\PageRepository;
use GAYA\ContentUsage\Filter\RecordFilters;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class UsageRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [__DIR__ . '/../../../..'];

    public static function recordKinds(): iterable
    {
        yield 'contents' => ['tt_content', 'CType', 'text', 'header', ContentRepository::class, Ctype::class, 'Ctype'];
        yield 'pages' => ['pages', 'doktype', 1, 254, PageRepository::class, Doktype::class, 'Doktype'];
    }

    #[DataProvider('recordKinds')]
    public function testStatusListsAndCountsPartitionRecordsAndKeepReferenceTime(
        string $table,
        string $typeField,
        string|int $typeId,
        string|int $otherTypeId,
        string $repositoryClass,
        string $typeClass,
        string $methodSuffix,
    ): void {
        $now = 1700000000;
        $context = $this->get(Context::class);
        $context->setAspect('date', new DateTimeAspect(new \DateTimeImmutable('@' . $now)));

        $repository = new $repositoryClass($this->get(DataMapper::class), $context);
        $type = new $typeClass();
        $type->setId($typeId);

        $connection = $this->get(ConnectionPool::class)->getConnectionForTable($table);
        $expected = ['Active' => [], 'Disabled' => [], 'Deleted' => []];
        $uid = 0;
        foreach ([0, 1] as $deleted) {
            foreach ([0, 1] as $hidden) {
                foreach ([0, $now - 1, $now, $now + 1] as $starttime) {
                    foreach ([0, $now - 1, $now, $now + 1] as $endtime) {
                        $connection->insert($table, [
                            'uid' => ++$uid,
                            'pid' => 0,
                            $typeField => $typeId,
                            'deleted' => $deleted,
                            'hidden' => $hidden,
                            'starttime' => $starttime,
                            'endtime' => $endtime,
                        ]);
                        $status = $deleted ? 'Deleted' : (($hidden || ($endtime > 0 && $endtime <= $now)) ? 'Disabled' : 'Active');
                        $expected[$status][] = $uid;
                    }
                }
            }
        }

        // A different type must not leak into any of the three result sets.
        foreach (['Active', 'Disabled', 'Deleted'] as $status) {
            $connection->insert($table, [
                'uid' => ++$uid,
                $typeField => $otherTypeId,
                'hidden' => (int)($status === 'Disabled'),
                'deleted' => (int)($status === 'Deleted'),
            ]);
        }

        // All queries from the repository must retain their original reference time.
        $context->setAspect('date', new DateTimeAspect(new \DateTimeImmutable('@' . ($now + 100))));
        foreach ($expected as $status => $expectedUids) {
            $records = $repository->{'find' . $status . 'By' . $methodSuffix}($type);
            $count = $repository->{'count' . $status . 'By' . $methodSuffix}($type);
            self::assertSame(count($expectedUids), $count, $status);
            self::assertCount($count, $records, $status);
            self::assertEqualsCanonicalizing($expectedUids, array_map(static fn($record) => $record->getUid(), $records), $status);
            foreach ($records as $record) {
                self::assertSame((string)$typeId, $record->{'get' . $methodSuffix}());
            }
        }

        // Keep the summary query budget independent of the number of types and records.
        $countingRepository = $this->getMockBuilder($repositoryClass)
            ->setConstructorArgs([$this->get(DataMapper::class), $context])
            ->onlyMethods(['getQueryBuilder'])->getMock();
        $queryMethod = new \ReflectionMethod($repositoryClass, 'getQueryBuilder');
        $countingRepository->expects($this->exactly(3))->method('getQueryBuilder')
            ->willReturnCallback(static fn(string $status) => $queryMethod->invoke($repository, $status));
        $grouped = $countingRepository->countByTypeAndStatus();
        foreach ($expected as $status => $expectedUids) {
            self::assertSame(count($expectedUids), $grouped[$typeId][strtolower($status)]);
            self::assertSame(1, $grouped[$otherTypeId][strtolower($status)]);
            $allUids = [];
            $counter = count($expectedUids);
            for ($offset = 0; $offset < $counter; $offset += 5) {
                $records = $repository->{'find' . $status . 'By' . $methodSuffix}($type, 5, $offset);
                self::assertLessThanOrEqual(5, count($records));
                array_push($allUids, ...array_map(static fn($record) => $record->getUid(), $records));
            }

            self::assertSame($expectedUids, $allUids);
            self::assertSame([], $repository->{'find' . $status . 'By' . $methodSuffix}($type, 5, 1000));
        }

        $type->setId($table === 'pages' ? 999 : 'unused');
        foreach (array_keys($expected) as $status) {
            self::assertSame(0, $repository->{'count' . $status . 'By' . $methodSuffix}($type));
            self::assertSame([], $repository->{'find' . $status . 'By' . $methodSuffix}($type));
        }
    }

    #[DataProvider('recordKinds')]
    public function testLanguageAndWorkspaceFiltersApplyToCountsAndLists(string $table, string $typeField, string|int $typeId, string|int $otherTypeId, string $repositoryClass, string $typeClass, string $methodSuffix): void
    {
        $repository = new $repositoryClass($this->get(DataMapper::class), $this->get(Context::class));
        $type = new $typeClass();
        $type->setId($typeId);

        $connection = $this->get(ConnectionPool::class)->getConnectionForTable($table);
        foreach ([-1, 0, 1] as $language) {
            foreach ([0, 2] as $workspace) {
                foreach (['Active', 'Disabled', 'Deleted'] as $status) {
                    $connection->insert($table, [$typeField => $typeId, 'sys_language_uid' => $language, 't3ver_wsid' => $workspace, 'hidden' => (int)($status === 'Disabled'), 'deleted' => (int)($status === 'Deleted')]);
                }
            }
        }

        foreach ([[null, null, 6], [0, null, 2], [null, 0, 3], [0, 0, 1], [1, 2, 1], [-1, 2, 1], [99, 0, 0]] as [$language, $workspace, $expected]) {
            $filters = new RecordFilters($language, $workspace);
            $grouped = $repository->countByTypeAndStatus($filters);
            foreach (['Active', 'Disabled', 'Deleted'] as $status) {
                self::assertSame($expected, $repository->{'count' . $status . 'By' . $methodSuffix}($type, $filters));
                self::assertSame($expected, $grouped[$typeId][strtolower($status)] ?? 0);
                $records = $repository->{'find' . $status . 'By' . $methodSuffix}($type, 1, 0, $filters);
                self::assertCount(min(1, $expected), $records);
                foreach ($records as $record) {
                    if ($language !== null) {
                        self::assertSame($language, $record->getSysLanguageUid());
                    }

                    if ($workspace !== null) {
                        self::assertSame($workspace, $record->getT3verWsid());
                    }
                }
            }
        }
    }

}
