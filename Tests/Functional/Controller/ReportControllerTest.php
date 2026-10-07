<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Tests\Functional\Controller;

use GAYA\ContentUsage\Configuration\TcaConfiguration;
use GAYA\ContentUsage\Controller\ReportController;
use GAYA\ContentUsage\Domain\Repository\ContentRepository;
use GAYA\ContentUsage\Domain\Repository\PageRepository;
use GAYA\ContentUsage\Pagination\PageSizePreference;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Routing\Route;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class ReportControllerTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [__DIR__ . '/../../..'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->get(ConnectionPool::class)->getConnectionForTable('be_users')->insert('be_users', [
            'uid' => 1,
            'username' => 'report-test-admin',
            'admin' => 1,
        ]);
        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);
    }

    public function testOverviewAndAllDetailStatusesRender(): void
    {
        $controller = $this->get(ReportController::class);
        foreach (['main', 'ctypes', 'doktypes'] as $action) {
            $response = $controller->processRequest($this->request($action));
            self::assertSame(200, $response->getStatusCode());
            self::assertStringContainsString('<h1>', (string)$response->getBody());
        }

        foreach (['ctype' => ['tt_content', 'CType', 'text', 'header'], 'doktype' => ['pages', 'doktype', 1, 'title']] as $type => [$table, $field, $id, $labelField]) {
            foreach (['active', 'disabled', 'deleted'] as $status) {
                $this->get(ConnectionPool::class)->getConnectionForTable($table)->insert($table, [
                    $field => $id,
                    $labelField => 'Report fixture ' . $type . ' ' . $status,
                    'hidden' => (int)($status === 'disabled'),
                    'deleted' => (int)($status === 'deleted'),
                ]);
            }

            foreach (['active', 'disabled', 'deleted'] as $status) {
                $response = $controller->processRequest($this->request($type . 'Detail', [$type => (string)$id, 'status' => $status]));
                self::assertSame(200, $response->getStatusCode());
                $html = (string)$response->getBody();
                self::assertStringContainsString('Report fixture ' . $type . ' ' . $status, $html);
                foreach (array_diff(['active', 'disabled', 'deleted'], [$status]) as $otherStatus) {
                    self::assertStringNotContainsString('Report fixture ' . $type . ' ' . $otherStatus, $html);
                }
            }
        }
    }

    public function testUnknownTypesRedirectToOverview(): void
    {
        $expectedLocation = (string)$this->get(UriBuilder::class)->buildUriFromRoute('system_contentusage');
        foreach (['ctype' => 'not_registered', 'doktype' => '999'] as $type => $id) {
            $response = $this->get(ReportController::class)->processRequest($this->request($type . 'Detail', [$type => $id, 'status' => 'active']));
            self::assertSame(302, $response->getStatusCode());
            self::assertSame($expectedLocation, $response->getHeaderLine('Location'));
        }
    }

    public static function detailLists(): iterable
    {
        yield 'contents' => ['ctype', 'tt_content', 'CType', 'text', 'header'];
        yield 'pages' => ['doktype', 'pages', 'doktype', 1, 'title'];
    }

    #[DataProvider('detailLists')]
    public function testPaginationAndSizeChanges(string $type, string $table, string $field, string|int $id, string $label): void
    {
        $connection = $this->get(ConnectionPool::class)->getConnectionForTable($table);
        for ($uid = 1; $uid <= 151; ++$uid) {
            $connection->insert($table, ['uid' => $uid, $field => $id, $label => sprintf('Record-%03d', $uid)]);
        }

        $controller = $this->get(ReportController::class);
        $parameters = [$type => (string)$id, 'status' => 'active'];
        $html = (string)$controller->processRequest($this->request($type . 'Detail', $parameters))->getBody();
        self::assertStringContainsString('1–100 of 151', $html);
        self::assertStringContainsString('Record-100', $html);
        self::assertStringNotContainsString('Record-101', $html);
        self::assertStringContainsString('value="100" selected="selected"', $html);
        $document = new \DOMDocument();
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        self::assertSame(1, $xpath->query('//select[@name="itemsPerPage"]/option[@selected]')->length);
        foreach ($xpath->query('//nav[@aria-label="Pagination"]//a') as $link) {
            parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
            self::assertSame((string)$id, $query[$type]);
            self::assertSame('active', $query['status']);
            self::assertArrayHasKey('page', $query);
        }

        $html = (string)$controller->processRequest($this->request($type . 'Detail', $parameters + ['page' => 2]))->getBody();
        self::assertStringContainsString('101–151 of 151', $html);
        self::assertStringNotContainsString('Record-100', $html);
        self::assertStringContainsString('Record-151', $html);
        foreach (['50' => '1–50 of 151', '100' => '1–100 of 151', '500' => '1–151 of 151', '1000' => '1–151 of 151', 'all' => '1–151 of 151'] as $size => $range) {
            $request = $this->request($type . 'Detail', $parameters + ['page' => 2])->withMethod('POST')->withParsedBody(['itemsPerPage' => (string)$size]);
            $html = (string)$controller->processRequest($request)->getBody();
            self::assertStringContainsString($range, $html);
            self::assertStringContainsString('value="' . $size . '" selected="selected"', $html);
        }

        self::assertStringNotContainsString('<nav aria-label="Pagination">', $html);
        $html = (string)$controller->processRequest($this->request($type . 'Detail', $parameters + ['itemsPerPage' => ['50']]))->getBody();
        self::assertStringContainsString('1–100 of 151', $html);
    }

    public function testPreferencesPersistIndependentlyForEachScreenAndUser(): void
    {
        $resolver = new PageSizePreference();
        $sizes = ['ctypes' => 50, 'doktypes' => 500, 'ctypeDetail' => 'all', 'doktypeDetail' => 1000];
        foreach ($sizes as $screen => $size) {
            self::assertSame($size, $resolver->resolve($this->request($screen)->withParsedBody(['itemsPerPage' => (string)$size]), $screen, $GLOBALS['BE_USER']));
        }

        // Reload the backend user from the database, with a new session.
        $user = $this->setUpBackendUser(1);
        foreach ($sizes as $screen => $size) {
            self::assertSame($size, $resolver->resolve($this->request($screen), $screen, $user));
        }

        $this->get(ConnectionPool::class)->getConnectionForTable('be_users')->insert('be_users', ['uid' => 2, 'username' => 'other-user', 'admin' => 1]);
        $otherUser = $this->setUpBackendUser(2);
        foreach (array_keys($sizes) as $screen) {
            self::assertSame(100, $resolver->resolve($this->request($screen), $screen, $otherUser));
        }
    }

    public function testOverviewPaginationPreservesTcaOrderAndUnusedTypes(): void
    {
        foreach (['ctypes' => ['tt_content', 'CType'], 'doktypes' => ['pages', 'doktype']] as $screen => [$table, $field]) {
            $items = [];
            for ($id = 1; $id <= 105; ++$id) {
                $items[] = ['value' => $field === 'CType' ? 'type_' . $id : $id, 'label' => sprintf('Type-%03d', $id)];
            }

            $GLOBALS['TCA'][$table]['columns'][$field]['config']['items'] = $items;
            $controller = new ReportController(
                $this->get(ModuleTemplateFactory::class),
                $this->get(UriBuilder::class),
                new TcaConfiguration(),
                $this->get(PageRepository::class),
                $this->get(ContentRepository::class),
                new PageSizePreference(),
            );
            $html = (string)$controller->processRequest($this->request($screen, ['page' => 2]))->getBody();
            self::assertStringContainsString('101–105 of 105', $html);
            self::assertStringContainsString('Type-101', $html);
            self::assertStringContainsString('Type-105', $html);
            self::assertStringNotContainsString('Type-100', $html);
            self::assertLessThan(strpos($html, 'Type-105'), strpos($html, 'Type-101'));
        }
    }

    private function request(string $action, array $parameters = []): ServerRequest
    {
        $moduleConfiguration = require __DIR__ . '/../../../Configuration/Backend/Modules.php';
        $stored = $GLOBALS['BE_USER']->getModuleData('system_contentusage');
        $request = (new ServerRequest('https://example.test/typo3/'))
            ->withAttribute('moduleData', new ModuleData('system_contentusage', is_array($stored) ? $stored : [], $moduleConfiguration['system_contentusage']['moduleData']))
            ->withQueryParams($parameters)
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
            ->withAttribute('route', new Route('/', [], [], [
                '_identifier' => 'system_contentusage.' . $action,
                'packageName' => 'gaya/typo3-content-usage',
            ]));
        return $request->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request));
    }
}
