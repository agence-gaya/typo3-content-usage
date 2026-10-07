<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Tests\Functional\Controller;

use GAYA\ContentUsage\Controller\ReportController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
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

    private function request(string $action, array $parameters = []): ServerRequest
    {
        $request = (new ServerRequest('https://example.test/typo3/'))
            ->withQueryParams($parameters)
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE)
            ->withAttribute('route', new Route('/', [], [], [
                '_identifier' => 'system_contentusage.' . $action,
                'packageName' => 'gaya/typo3-content-usage',
            ]));
        return $request->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request));
    }
}
