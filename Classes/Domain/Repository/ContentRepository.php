<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Domain\Repository;

use GAYA\ContentUsage\Domain\Model\Content;
use GAYA\ContentUsage\Domain\Model\Ctype;
use GAYA\ContentUsage\Filter\RecordFilters;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class ContentRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'tt_content';
    }

    public function countActiveByCtype(Ctype $ctype, ?RecordFilters $filters = null): int
    {
        $queryBuilder = $this->getQueryBuilder('active', $filters);
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->selectLiteral('count(*)');

        return (int)$queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @return Content[]
     */
    public function findActiveByCtype(Ctype $ctype, ?int $limit = null, int $offset = 0, ?RecordFilters $filters = null): array
    {
        $queryBuilder = $this->getQueryBuilder('active', $filters);
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->select('uid', 'pid', 'header', 'CType', 'sys_language_uid', 't3ver_wsid');

        $queryBuilder->orderBy('uid')->setMaxResults($limit)->setFirstResult($offset);

        return $this->dataMapper->map(Content::class, $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    public function countDisabledByCtype(Ctype $ctype, ?RecordFilters $filters = null): int
    {
        $queryBuilder = $this->getQueryBuilder('disabled', $filters);
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->selectLiteral('count(*)');

        return (int)$queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @return Content[]
     */
    public function findDisabledByCtype(Ctype $ctype, ?int $limit = null, int $offset = 0, ?RecordFilters $filters = null): array
    {
        $queryBuilder = $this->getQueryBuilder('disabled', $filters);
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->select('uid', 'pid', 'header', 'CType', 'sys_language_uid', 't3ver_wsid');

        $queryBuilder->orderBy('uid')->setMaxResults($limit)->setFirstResult($offset);

        return $this->dataMapper->map(Content::class, $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    public function countDeletedByCtype(Ctype $ctype, ?RecordFilters $filters = null): int
    {
        $queryBuilder = $this->getQueryBuilder('deleted', $filters);
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->selectLiteral('count(*)');

        return (int)$queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @return Content[]
     */
    public function findDeletedByCtype(Ctype $ctype, ?int $limit = null, int $offset = 0, ?RecordFilters $filters = null): array
    {
        $queryBuilder = $this->getQueryBuilder('deleted', $filters);
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->select('uid', 'pid', 'header', 'CType', 'sys_language_uid', 't3ver_wsid');

        $queryBuilder->orderBy('uid')->setMaxResults($limit)->setFirstResult($offset);

        return $this->dataMapper->map(Content::class, $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    public function countByTypeAndStatus(?RecordFilters $filters = null): array
    {
        return $this->getCountsByTypeAndStatus('CType', $filters);
    }

    private function addConstraintsForCtype(QueryBuilder $queryBuilder, Ctype $ctype): void
    {
        $queryBuilder->andWhere(
            $queryBuilder->expr()->eq(
                'CType',
                $queryBuilder->createNamedParameter($ctype->getId())
            )
        );
    }
}
