<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Domain\Repository;

use GAYA\ContentUsage\Domain\Model\Content;
use GAYA\ContentUsage\Domain\Model\Page;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\TableColumnType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

abstract class AbstractRepository
{
    protected DataMapper $dataMapper;

    public function __construct(DataMapper $dataMapper, ObjectManagerInterface $objectManager)
    {
        $this->dataMapper = $dataMapper;

        // t3ver_wsid field is not configured in TCA, so the dataMapper won't map it because it's a "non persistable property" for him
        // To fix that, we manually add the field in the dataMapper
        foreach ([Page::class, Content::class] as $class) {
            $dataMap = $this->dataMapper->getDataMap($class);
            $columnMap = $objectManager->get(ColumnMap::class, 't3ver_wsid', 't3verWsid');
            $columnMap->setType(new TableColumnType(TableColumnType::PASSTHROUGH));
            $dataMap->addColumnMap($columnMap);
        }
    }

    abstract protected function getTableName(): string;

    protected function getQueryBuilder(string $status): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->getTableName());
        $queryBuilder->getRestrictions()->removeAll();

        $queryBuilder->from($this->getTableName());

        switch ($status) {
            case 'active':
                $queryBuilder->where($this->getActiveConstraints($queryBuilder));
                break;
            case 'disabled':
                $queryBuilder->where($this->getDisabledConstraints($queryBuilder));
                break;
            case 'deleted':
                $queryBuilder->where($this->getDeletedConstraints($queryBuilder));
                break;
        };

        return $queryBuilder;
    }

    /**
     * @return CompositeExpression|string
     */
    private function getActiveConstraints(QueryBuilder $queryBuilder)
    {
        return $queryBuilder->expr()->andX(
            $queryBuilder->expr()->eq('deleted', 0),
            $queryBuilder->expr()->eq('hidden', 0),
            $queryBuilder->expr()->orX(
                $queryBuilder->expr()->eq('endtime', 0),
                $queryBuilder->expr()->gt('endtime', time()),
            )
        );
    }

    /**
     * @return CompositeExpression|string
     */
    private function getDisabledConstraints(QueryBuilder $queryBuilder)
    {
        return $queryBuilder->expr()->andX(
            $queryBuilder->expr()->eq('deleted', 0),
            $queryBuilder->expr()->orX(
                $queryBuilder->expr()->eq('hidden', 1),
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gt('endtime', 0),
                    $queryBuilder->expr()->lt('endtime', time()),
                )
            )
        );
    }

    /**
     * @return CompositeExpression|string
     */
    private function getDeletedConstraints(QueryBuilder $queryBuilder)
    {
        return $queryBuilder->expr()->eq('deleted', 1);
    }
}
