<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Filter;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Site\SiteFinder;

final readonly class FilterOptions
{
    public function __construct(private SiteFinder $siteFinder, private ConnectionPool $connectionPool, private PackageManager $packageManager) {}

    public function getLanguages(): array
    {
        $titles = [];
        foreach ($this->siteFinder->getAllSites() as $site) {
            foreach ($site->getAllLanguages() as $language) {
                $titles[$language->getLanguageId()][] = $language->getTitle();
            }
        }

        $titles[0] ??= ['Default'];
        ksort($titles);
        $options = ['all' => 'All languages', -1 => 'All-language records (-1)'];
        foreach ($titles as $id => $labels) {
            $options[$id] = implode(' / ', array_unique($labels));
        }

        return $options;
    }

    public function getWorkspaces(): array
    {
        $options = ['all' => 'All workspaces', 0 => 'Live'];
        if ($this->packageManager->isPackageActive('workspaces')) {
            $query = $this->connectionPool->getQueryBuilderForTable('sys_workspace');
            foreach ($query->select('uid', 'title')->from('sys_workspace')->orderBy('title')->addOrderBy('uid')->executeQuery()->fetchAllAssociative() as $row) {
                $options[(int)$row['uid']] = $row['title'];
            }
        }

        return $options;
    }
}
