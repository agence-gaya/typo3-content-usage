<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Tests\Unit\Filter;

use Doctrine\DBAL\Result;
use GAYA\ContentUsage\Filter\FilterOptions;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;

final class FilterOptionsTest extends TestCase
{
    public function testLanguagesAreDeduplicatedAcrossSitesWithoutFilteringDisabledLanguages(): void
    {
        $sites = [];
        foreach ([[0 => 'English', 1 => 'Français'], [0 => 'English', 1 => 'French', 2 => 'Deutsch']] as $titles) {
            $languages = [];
            foreach ($titles as $id => $title) {
                $language = self::createStub(SiteLanguage::class);
                $language->method('getLanguageId')->willReturn($id);
                $language->method('getTitle')->willReturn($title);
                $languages[$id] = $language;
            }

            $site = $this->createMock(Site::class);
            $site->expects($this->once())->method('getAllLanguages')->willReturn($languages);
            $site->expects($this->never())->method('getLanguages');
            $sites[] = $site;
        }

        $finder = self::createStub(SiteFinder::class);
        $finder->method('getAllSites')->willReturn($sites);
        $options = new FilterOptions($finder, self::createStub(ConnectionPool::class), self::createStub(PackageManager::class));
        self::assertSame(['all' => 'All languages', -1 => 'All-language records (-1)', 0 => 'English', 1 => 'Français / French', 2 => 'Deutsch'], $options->getLanguages());
    }

    public function testLanguageZeroIsAvailableWithoutSiteConfiguration(): void
    {
        $finder = self::createStub(SiteFinder::class);
        $finder->method('getAllSites')->willReturn([]);
        $options = new FilterOptions($finder, self::createStub(ConnectionPool::class), self::createStub(PackageManager::class));
        self::assertSame('Default', $options->getLanguages()[0]);
    }

    public function testWorkspacesIncludeLiveAndConfiguredTitles(): void
    {
        $packageManager = self::createStub(PackageManager::class);
        $packageManager->method('isPackageActive')->willReturn(true);
        $result = self::createStub(Result::class);
        $result->method('fetchAllAssociative')->willReturn([['uid' => 2, 'title' => 'Editorial'], ['uid' => 5, 'title' => 'Review']]);
        $query = self::createStub(QueryBuilder::class);
        foreach (['select', 'from', 'orderBy', 'addOrderBy'] as $method) {
            $query->method($method)->willReturnSelf();
        }

        $query->method('executeQuery')->willReturn($result);
        $pool = $this->createMock(ConnectionPool::class);
        $pool->expects($this->once())->method('getQueryBuilderForTable')->with('sys_workspace')->willReturn($query);
        $options = new FilterOptions(self::createStub(SiteFinder::class), $pool, $packageManager);
        self::assertSame(['all' => 'All workspaces', 0 => 'Live', 2 => 'Editorial', 5 => 'Review'], $options->getWorkspaces());
    }

    public function testWorkspacesDoNotQueryMissingExtensionTables(): void
    {
        $packageManager = self::createStub(PackageManager::class);
        $packageManager->method('isPackageActive')->willReturn(false);
        $pool = $this->createMock(ConnectionPool::class);
        $pool->expects($this->never())->method('getQueryBuilderForTable');
        $options = new FilterOptions(self::createStub(SiteFinder::class), $pool, $packageManager);
        self::assertSame(['all' => 'All workspaces', 0 => 'Live'], $options->getWorkspaces());
    }

}
