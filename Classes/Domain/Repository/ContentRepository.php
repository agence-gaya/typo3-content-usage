<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Domain\Repository;

use GAYA\ContentUsage\Domain\Model\Content;
use GAYA\ContentUsage\Domain\Model\Ctype;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class ContentRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'tt_content';
    }

    public function countActiveByCtype(Ctype $ctype): int
    {
        $queryBuilder = $this->getQueryBuilder('active');
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->selectLiteral('count(*)');

        return (int) $queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @return Content[]
     */
    public function findActiveByCtype(Ctype $ctype): array
    {
        $queryBuilder = $this->getQueryBuilder('active');
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->select('uid', 'pid', 'header', 'ctype', 'sys_language_uid', 't3ver_wsid');

        return $this->dataMapper->map(Content::class, $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    public function countDisabledByCtype(Ctype $ctype): int
    {
        $queryBuilder = $this->getQueryBuilder('disabled');
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->selectLiteral('count(*)');

        return (int) $queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @return Content[]
     */
    public function findDisabledByCtype(Ctype $ctype): array
    {
        $queryBuilder = $this->getQueryBuilder('disabled');
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->select('uid', 'pid', 'header', 'ctype', 'sys_language_uid', 't3ver_wsid');

        return $this->dataMapper->map(Content::class, $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    public function countDeletedByCtype(Ctype $ctype): int
    {
        $queryBuilder = $this->getQueryBuilder('deleted');
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->selectLiteral('count(*)');

        return (int) $queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @return Content[]
     */
    public function findDeletedByCtype(Ctype $ctype): array
    {
        $queryBuilder = $this->getQueryBuilder('deleted');
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->select('uid', 'pid', 'header', 'ctype', 'sys_language_uid', 't3ver_wsid');

        return $this->dataMapper->map(Content::class, $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    private function addConstraintsForCtype(QueryBuilder $queryBuilder, Ctype $ctype): void
    {
        $queryBuilder->andWhere(
            $queryBuilder->expr()->eq(
                'Ctype',
                $queryBuilder->createNamedParameter($ctype->getId())
            )
        );
    }
}
