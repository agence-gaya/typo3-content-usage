<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Controller;

use GAYA\ContentUsage\Configuration\TcaConfiguration;
use GAYA\ContentUsage\Domain\Model\Ctype;
use GAYA\ContentUsage\Domain\Model\Doktype;
use GAYA\ContentUsage\Domain\Model\Plugin;
use GAYA\ContentUsage\Domain\Repository\ContentRepository;
use GAYA\ContentUsage\Domain\Repository\PageRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Routing\Route;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

#[AsController]
class ReportController
{
    protected ServerRequestInterface $request;

    protected ModuleTemplate $view;

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly UriBuilder $uriBuilder,
        private readonly TcaConfiguration $tcaConfiguration,
        private readonly PageRepository $pageRepository,
        private readonly ContentRepository $contentRepository
    ) {}

    public function processRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->view = $this->moduleTemplateFactory->create($request);
        $this->view->assign('hasRecycler', ExtensionManagementUtility::isLoaded('recycler'));

        /** @var Route $route */
        $route = $request->getAttribute('route');

        switch ($route->getOption('_identifier')) {
            case 'tools_ContentUsage.doktypes':
                return $this->doktypesAction();
            case 'tools_ContentUsage.ctypes':
                return $this->ctypesAction();
            case 'tools_ContentUsage.listTypes':
                return $this->listTypesAction();
            case 'tools_ContentUsage.doktypeDetail':
                foreach ($this->tcaConfiguration->getDoktypes() as $doktype) {
                    if ($doktype->getId() === (int) $request->getQueryParams()['doktype']) {
                        return $this->doktypeDetailAction($doktype, $request->getQueryParams()['status']);
                    }
                }

                break;
            case 'tools_ContentUsage.ctypeDetail':
                foreach ($this->tcaConfiguration->getCtypes() as $ctype) {
                    if ($ctype->getId() === $request->getQueryParams()['ctype']) {
                        return $this->ctypeDetailAction($ctype, $request->getQueryParams()['status']);
                    }
                }

                break;
            case 'tools_ContentUsage.listTypeDetail':
                foreach ($this->tcaConfiguration->getPlugins() as $plugin) {
                    if ($plugin->getId() === $request->getQueryParams()['listType']) {
                        return $this->listTypeDetailAction($plugin, $request->getQueryParams()['status']);
                    }
                }

                break;
            default:
                return $this->mainAction();
        }

        // If we are here, there was a problem
        return new RedirectResponse((string) $this->uriBuilder->buildUriFromRoute('tools_ContentUsage'));
    }

    public function mainAction(): ResponseInterface
    {
        return $this->view->renderResponse('Main');
    }

    public function doktypesAction(): ResponseInterface
    {
        $doktypes = $this->tcaConfiguration->getDoktypes();
        foreach ($doktypes as $doktype) {
            $doktype->setTotalActivePages($this->pageRepository->countActiveByDoktype($doktype));
            $doktype->setTotalDisabledPages($this->pageRepository->countDisabledByDoktype($doktype));
            $doktype->setTotalDeletedPages($this->pageRepository->countDeletedByDoktype($doktype));
        }

        $this->view->assign('doktypes', $doktypes);

        return $this->view->renderResponse('Doktypes');
    }

    public function ctypesAction(): ResponseInterface
    {
        $ctypes = $this->tcaConfiguration->getCtypes();
        foreach ($ctypes as $ctype) {
            $ctype->setTotalActiveContents($this->contentRepository->countActiveByCtype($ctype));
            $ctype->setTotalDisabledContents($this->contentRepository->countDisabledByCtype($ctype));
            $ctype->setTotalDeletedContents($this->contentRepository->countDeletedByCtype($ctype));
        }

        $this->view->assign('ctypes', $ctypes);

        return $this->view->renderResponse('Ctypes');
    }

    public function listTypesAction(): ResponseInterface
    {
        $plugins = $this->tcaConfiguration->getPlugins();
        foreach ($plugins as $plugin) {
            $plugin->setTotalActiveContents($this->contentRepository->countActiveByPlugin($plugin));
            $plugin->setTotalDisabledContents($this->contentRepository->countDisabledByPlugin($plugin));
            $plugin->setTotalDeletedContents($this->contentRepository->countDeletedByPlugin($plugin));
        }

        $this->view->assign('plugins', $plugins);

        return $this->view->renderResponse('ListTypes');
    }

    public function doktypeDetailAction(Doktype $doktype, string $status): ResponseInterface
    {
        match ($status) {
            'active' => $doktype->setActivePages($this->pageRepository->findActiveByDoktype($doktype)),
            'disabled' => $doktype->setDisabledPages($this->pageRepository->findDisabledByDoktype($doktype)),
            'deleted' => $doktype->setDeletedPages($this->pageRepository->findDeletedByDoktype($doktype)),
        };

        $this->view->assign('doktype', $doktype);
        $this->view->assign('status', $status);

        return $this->view->renderResponse('DoktypeDetail');
    }

    public function ctypeDetailAction(Ctype $ctype, string $status): ResponseInterface
    {
        match ($status) {
            'active' => $ctype->setActiveContents($this->contentRepository->findActiveByCtype($ctype)),
            'disabled' => $ctype->setDisabledContents($this->contentRepository->findDisabledByCtype($ctype)),
            'deleted' => $ctype->setDeletedContents($this->contentRepository->findDeletedByCtype($ctype)),
        };

        $this->view->assign('ctype', $ctype);
        $this->view->assign('status', $status);

        return $this->view->renderResponse('CtypeDetail');
    }

    public function listTypeDetailAction(Plugin $plugin, string $status): ResponseInterface
    {
        match ($status) {
            'active' => $plugin->setActiveContents($this->contentRepository->findActiveByPlugin($plugin)),
            'disabled' => $plugin->setDisabledContents($this->contentRepository->findDisabledByPlugin($plugin)),
            'deleted' => $plugin->setDeletedContents($this->contentRepository->findDeletedByPlugin($plugin)),
        };

        $this->view->assign('plugin', $plugin);
        $this->view->assign('status', $status);

        return $this->view->renderResponse('ListTypeDetail');
    }
}
