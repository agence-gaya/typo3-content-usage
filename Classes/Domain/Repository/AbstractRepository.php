<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Domain\Repository;

use GAYA\ContentUsage\Domain\Model\Content;
use GAYA\ContentUsage\Domain\Model\Page;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\TableColumnType;
use TYPO3\CMS\Core\Schema\FieldTypeFactory;
use TYPO3\CMS\Core\Schema\RelationMapBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMapFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

abstract class AbstractRepository
{
    public function __construct(
        protected DataMapper $dataMapper,
        ColumnMapFactory $columnMapFactory,
        FieldTypeFactory $fieldTypeFactory,
        RelationMapBuilder $relationMapBuilder,
    ) {
        // t3ver_wsid field is not configured in TCA, so the dataMapper won't map it because it's a "non persistable property" for him
        // To fix that, we manually add the field in the dataMapper
        $relationMap = $relationMapBuilder->buildFromStructure($GLOBALS['TCA']);
        foreach ([Page::class, Content::class] as $class) {
            $dataMap = $this->dataMapper->getDataMap($class);
            $dataMap->addColumnMap(
                't3verWsid',
                $columnMapFactory->create(
                    $fieldTypeFactory->createFieldType(
                        't3ver_wsid',
                        ['config' => ['type' => TableColumnType::PASSTHROUGH->value]],
                        $dataMap->getTableName(),
                        $relationMap
                    ),
                    't3verWsid',
                    $class
                )
            );
        }
    }

    abstract protected function getTableName(): string;

    protected function getQueryBuilder(string $status): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->getTableName());
        $queryBuilder->getRestrictions()->removeAll();

        $queryBuilder->from($this->getTableName());

        match ($status) {
            'active' => $queryBuilder->where($this->getActiveConstraints($queryBuilder)),
            'disabled' => $queryBuilder->where($this->getDisabledConstraints($queryBuilder)),
            'deleted' => $queryBuilder->where($this->getDeletedConstraints($queryBuilder)),
        };

        return $queryBuilder;
    }

    private function getActiveConstraints(QueryBuilder $queryBuilder): CompositeExpression
    {
        return $queryBuilder->expr()->and(
            $queryBuilder->expr()->eq('deleted', 0),
            $queryBuilder->expr()->eq('hidden', 0),
            $queryBuilder->expr()->or(
                $queryBuilder->expr()->eq('endtime', 0),
                $queryBuilder->expr()->gt('endtime', time()),
            )
        );
    }

    private function getDisabledConstraints(QueryBuilder $queryBuilder): CompositeExpression
    {
        return $queryBuilder->expr()->and(
            $queryBuilder->expr()->eq('deleted', 0),
            $queryBuilder->expr()->or(
                $queryBuilder->expr()->eq('hidden', 1),
                $queryBuilder->expr()->and(
                    $queryBuilder->expr()->gt('endtime', 0),
                    $queryBuilder->expr()->lt('endtime', time()),
                )
            )
        );
    }

    private function getDeletedConstraints(QueryBuilder $queryBuilder): string
    {
        return $queryBuilder->expr()->eq('deleted', 1);
    }
}
