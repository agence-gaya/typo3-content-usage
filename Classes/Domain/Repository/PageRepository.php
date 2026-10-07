<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Domain\Repository;

use GAYA\ContentUsage\Domain\Model\Doktype;
use GAYA\ContentUsage\Domain\Model\Page;
use GAYA\ContentUsage\Filter\RecordFilters;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'pages';
    }

    public function countActiveByDoktype(Doktype $doktype, ?RecordFilters $filters = null): int
    {
        $queryBuilder = $this->getQueryBuilder('active', $filters);
        $this->addConstraintsForDoktype($queryBuilder, $doktype);
        $queryBuilder->selectLiteral('count(*)');

        return (int)$queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @return Page[]
     */
    public function findActiveByDoktype(Doktype $doktype, ?int $limit = null, int $offset = 0, ?RecordFilters $filters = null): array
    {
        $queryBuilder = $this->getQueryBuilder('active', $filters);
        $this->addConstraintsForDoktype($queryBuilder, $doktype);
        $queryBuilder->select('uid', 'title', 'doktype', 'sys_language_uid', 't3ver_wsid');

        $queryBuilder->orderBy('uid')->setMaxResults($limit)->setFirstResult($offset);

        return $this->dataMapper->map(Page::class, $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    public function countDisabledByDoktype(Doktype $doktype, ?RecordFilters $filters = null): int
    {
        $queryBuilder = $this->getQueryBuilder('disabled', $filters);
        $this->addConstraintsForDoktype($queryBuilder, $doktype);
        $queryBuilder->selectLiteral('count(*)');

        return (int)$queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @return Page[]
     */
    public function findDisabledByDoktype(Doktype $doktype, ?int $limit = null, int $offset = 0, ?RecordFilters $filters = null): array
    {
        $queryBuilder = $this->getQueryBuilder('disabled', $filters);
        $this->addConstraintsForDoktype($queryBuilder, $doktype);
        $queryBuilder->select('uid', 'title', 'doktype', 'sys_language_uid', 't3ver_wsid');

        $queryBuilder->orderBy('uid')->setMaxResults($limit)->setFirstResult($offset);

        return $this->dataMapper->map(Page::class, $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    public function countDeletedByDoktype(Doktype $doktype, ?RecordFilters $filters = null): int
    {
        $queryBuilder = $this->getQueryBuilder('deleted', $filters);
        $this->addConstraintsForDoktype($queryBuilder, $doktype);
        $queryBuilder->selectLiteral('count(*)');

        return (int)$queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @return Page[]
     */
    public function findDeletedByDoktype(Doktype $doktype, ?int $limit = null, int $offset = 0, ?RecordFilters $filters = null): array
    {
        $queryBuilder = $this->getQueryBuilder('deleted', $filters);
        $this->addConstraintsForDoktype($queryBuilder, $doktype);
        $queryBuilder->select('uid', 'title', 'doktype', 'sys_language_uid', 't3ver_wsid');

        $queryBuilder->orderBy('uid')->setMaxResults($limit)->setFirstResult($offset);

        return $this->dataMapper->map(Page::class, $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    public function countByTypeAndStatus(?RecordFilters $filters = null): array
    {
        return $this->getCountsByTypeAndStatus('doktype', $filters);
    }

    private function addConstraintsForDoktype(QueryBuilder $queryBuilder, Doktype $doktype): void
    {
        $queryBuilder->andWhere(
            $queryBuilder->expr()->eq(
                'doktype',
                $queryBuilder->createNamedParameter($doktype->getId(), Connection::PARAM_INT)
            )
        );
    }
}
