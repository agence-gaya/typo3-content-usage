<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Domain\Repository;

use GAYA\ContentUsage\Domain\Model\Content;
use GAYA\ContentUsage\Domain\Model\Ctype;
use GAYA\ContentUsage\Domain\Model\Plugin;
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

        return (int)$queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @param Ctype $ctype
     * @return Content[]
     */
    public function findActiveByCtype(Ctype $ctype): array
    {
        $queryBuilder = $this->getQueryBuilder('active');
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->select('uid', 'pid', 'header', 'ctype', 'list_type');

        return $this->dataMapper->map(Content::class, $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    public function countDisabledByCtype(Ctype $ctype): int
    {
        $queryBuilder = $this->getQueryBuilder('disabled');
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->selectLiteral('count(*)');

        return (int)$queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @param Ctype $ctype
     * @return Content[]
     */
    public function findDisabledByCtype(Ctype $ctype): array
    {
        $queryBuilder = $this->getQueryBuilder('disabled');
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->select('uid', 'pid', 'header', 'ctype', 'list_type');

        return $this->dataMapper->map(Content::class, $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    public function countDeletedByCtype(Ctype $ctype): int
    {
        $queryBuilder = $this->getQueryBuilder('deleted');
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->selectLiteral('count(*)');

        return (int)$queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @param Ctype $ctype
     * @return Content[]
     */
    public function findDeletedByCtype(Ctype $ctype): array
    {
        $queryBuilder = $this->getQueryBuilder('deleted');
        $this->addConstraintsForCtype($queryBuilder, $ctype);
        $queryBuilder->select('uid', 'pid', 'header', 'ctype', 'list_type');

        return $this->dataMapper->map(Content::class, $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    public function countActiveByPlugin(Plugin $plugin): int
    {
        $queryBuilder = $this->getQueryBuilder('active');
        $this->addConstraintsForPlugin($queryBuilder, $plugin);
        $queryBuilder->selectLiteral('count(*)');

        return (int)$queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @param Plugin $plugin
     * @return Content[]
     */
    public function findActiveByPlugin(Plugin $plugin): array
    {
        $queryBuilder = $this->getQueryBuilder('active');
        $this->addConstraintsForPlugin($queryBuilder, $plugin);
        $queryBuilder->select('uid', 'pid', 'header', 'ctype', 'list_type');

        return $this->dataMapper->map(Content::class, $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    public function countDisabledByPlugin(Plugin $plugin): int
    {
        $queryBuilder = $this->getQueryBuilder('disabled');
        $this->addConstraintsForPlugin($queryBuilder, $plugin);
        $queryBuilder->selectLiteral('count(*)');

        return (int)$queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @param Plugin $plugin
     * @return Content[]
     */
    public function findDisabledByPlugin(Plugin $plugin): array
    {
        $queryBuilder = $this->getQueryBuilder('disabled');
        $this->addConstraintsForPlugin($queryBuilder, $plugin);
        $queryBuilder->select('uid', 'pid', 'header', 'ctype', 'list_type');

        return $this->dataMapper->map(Content::class, $queryBuilder->executeQuery()->fetchAllAssociative());
    }

    public function countDeletedByPlugin(Plugin $plugin): int
    {
        $queryBuilder = $this->getQueryBuilder('deleted');
        $this->addConstraintsForPlugin($queryBuilder, $plugin);
        $queryBuilder->selectLiteral('count(*)');

        return (int)$queryBuilder->executeQuery()->fetchNumeric()[0];
    }

    /**
     * @param Plugin $plugin
     * @return Content[]
     */
    public function findDeletedByPlugin(Plugin $plugin): array
    {
        $queryBuilder = $this->getQueryBuilder('deleted');
        $this->addConstraintsForPlugin($queryBuilder, $plugin);
        $queryBuilder->select('uid', 'pid', 'header', 'ctype', 'list_type');

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

    private function addConstraintsForPlugin(QueryBuilder $queryBuilder, Plugin $plugin): void
    {
        $queryBuilder->andWhere(
            $queryBuilder->expr()->and(
                $queryBuilder->expr()->eq(
                    'Ctype',
                    $queryBuilder->createNamedParameter('list')
                ),
                $queryBuilder->expr()->eq(
                    'list_type',
                    $queryBuilder->createNamedParameter($plugin->getId())
                )
            )
        );
    }
}
