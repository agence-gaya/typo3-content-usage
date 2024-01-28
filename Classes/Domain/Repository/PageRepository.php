<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Domain\Repository;

use GAYA\ContentUsage\Domain\Model\Doktype;
use GAYA\ContentUsage\Domain\Model\Page;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class PageRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'pages';
    }

    public function countActiveByDoktype(Doktype $doktype): int
    {
        $queryBuilder = $this->getQueryBuilder('active');
        $this->addConstraintsForDoktype($queryBuilder, $doktype);
        $queryBuilder->selectLiteral('count(*)');

        return (int)$queryBuilder->execute()->fetchNumeric()[0];
    }

    /**
     * @return Page[]
     */
    public function findActiveByDoktype(Doktype $doktype): array
    {
        $queryBuilder = $this->getQueryBuilder('active');
        $this->addConstraintsForDoktype($queryBuilder, $doktype);
        $queryBuilder->select('uid', 'title', 'doktype');

        return $this->dataMapper->map(Page::class, $queryBuilder->execute()->fetchAllAssociative());
    }

    public function countDisabledByDoktype(Doktype $doktype): int
    {
        $queryBuilder = $this->getQueryBuilder('disabled');
        $this->addConstraintsForDoktype($queryBuilder, $doktype);
        $queryBuilder->selectLiteral('count(*)');

        return (int)$queryBuilder->execute()->fetchNumeric()[0];
    }

    /**
     * @return Page[]
     */
    public function findDisabledByDoktype(Doktype $doktype): array
    {
        $queryBuilder = $this->getQueryBuilder('disabled');
        $this->addConstraintsForDoktype($queryBuilder, $doktype);
        $queryBuilder->select('uid', 'title', 'doktype');

        return $this->dataMapper->map(Page::class, $queryBuilder->execute()->fetchAllAssociative());
    }

    public function countDeletedByDoktype(Doktype $doktype): int
    {
        $queryBuilder = $this->getQueryBuilder('deleted');
        $this->addConstraintsForDoktype($queryBuilder, $doktype);
        $queryBuilder->selectLiteral('count(*)');

        return (int)$queryBuilder->execute()->fetchNumeric()[0];
    }

    /**
     * @return Page[]
     */
    public function findDeletedByDoktype(Doktype $doktype): array
    {
        $queryBuilder = $this->getQueryBuilder('deleted');
        $this->addConstraintsForDoktype($queryBuilder, $doktype);
        $queryBuilder->select('uid', 'title', 'doktype');

        return $this->dataMapper->map(Page::class, $queryBuilder->execute()->fetchAllAssociative());
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
