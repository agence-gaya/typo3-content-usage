<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

abstract class AbstractRepository
{
    protected DataMapper $dataMapper;

    public function __construct(DataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
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
