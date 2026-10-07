<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Tests\Unit\Controller;

use GAYA\ContentUsage\Configuration\TcaConfiguration;
use GAYA\ContentUsage\Controller\ReportController;
use GAYA\ContentUsage\Domain\Repository\ContentRepository;
use GAYA\ContentUsage\Domain\Repository\PageRepository;
use GAYA\ContentUsage\Pagination\PageSizePreference;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Routing\Route;

final class ReportControllerTest extends TestCase
{
    #[DataProvider('invalidParameters')]
    public function testInvalidDetailRequestRedirectsBeforeRendering(string $type, array $parameters): void
    {
        $uriBuilder = $this->createMock(UriBuilder::class);
        $uriBuilder->expects($this->once())->method('buildUriFromRoute')
            ->with('system_contentusage')->willReturn(new Uri('/module/system/contentusage'));
        $tcaConfiguration = $this->createMock(TcaConfiguration::class);
        $tcaConfiguration->expects($this->never())->method('getCtypes');
        $tcaConfiguration->expects($this->never())->method('getDoktypes');

        // Invalid requests must return without constructing a backend view.
        $templateFactory = (new \ReflectionClass(ModuleTemplateFactory::class))->newInstanceWithoutConstructor();
        $controller = new ReportController(
            $templateFactory,
            $uriBuilder,
            $tcaConfiguration,
            self::createStub(PageRepository::class),
            self::createStub(ContentRepository::class),
            new PageSizePreference(),
        );
        $request = (new ServerRequest())->withQueryParams($parameters)->withAttribute(
            'route',
            new Route('/', [], [], ['_identifier' => 'system_contentusage.' . $type . 'Detail']),
        );

        $response = $controller->processRequest($request);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/module/system/contentusage', $response->getHeaderLine('Location'));
    }

    public static function invalidParameters(): iterable
    {
        foreach (['ctype' => 'text', 'doktype' => '1'] as $type => $validType) {
            yield $type . ' missing parameters' => [$type, []];
            yield $type . ' missing status' => [$type, [$type => $validType]];
            yield $type . ' missing type' => [$type, ['status' => 'active']];
            foreach ([null, '', 'invalid', 'ACTIVE', [], ['active'], 0, true] as $index => $status) {
                yield $type . ' invalid status ' . $index => [$type, [$type => $validType, 'status' => $status]];
            }

            foreach ([null, '', [], [$validType], true, false, 1.5] as $index => $invalidType) {
                yield $type . ' invalid type ' . $index => [$type, [$type => $invalidType, 'status' => 'active']];
            }
        }

        foreach (['1abc', '1.5', '1e2', '99999999999999999999999999'] as $invalidType) {
            yield 'malformed doktype ' . $invalidType => ['doktype', ['doktype' => $invalidType, 'status' => 'active']];
        }

        yield 'integer ctype' => ['ctype', ['ctype' => 1, 'status' => 'active']];
    }
}
