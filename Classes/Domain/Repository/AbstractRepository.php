<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Domain\Repository;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

abstract class AbstractRepository
{
    private readonly int $referenceTime;

    public function __construct(
        protected DataMapper $dataMapper,
        Context $context,
    ) {
        $this->referenceTime = $context->getPropertyFromAspect('date', 'timestamp');
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
                $queryBuilder->expr()->gt('endtime', $this->referenceTime),
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
                    $queryBuilder->expr()->lte('endtime', $this->referenceTime),
                )
            )
        );
    }

    private function getDeletedConstraints(QueryBuilder $queryBuilder): string
    {
        return $queryBuilder->expr()->eq('deleted', 1);
    }
}
