<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Controller;

use GAYA\ContentUsage\Domain\Model\Ctype;
use GAYA\ContentUsage\Domain\Model\Doktype;
use GAYA\ContentUsage\Configuration\TcaConfiguration;
use GAYA\ContentUsage\Domain\Model\Plugin;
use GAYA\ContentUsage\Domain\Repository\ContentRepository;
use GAYA\ContentUsage\Domain\Repository\PageRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Beuser\Domain\Model\ModuleData;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3Fluid\Fluid\View\ViewInterface;

class ReportController
{
    protected ServerRequestInterface $request;

    protected ViewInterface $view;

    protected ?ModuleData $moduleData = null;
    protected ?ModuleTemplate $moduleTemplate = null;

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly UriBuilder $uriBuilder,
        private TcaConfiguration $tcaConfiguration,
        private PageRepository $pageRepository,
        private ContentRepository $contentRepository
    ) {

    }

    public function processRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->moduleTemplate = $this->moduleTemplateFactory->create($request);


        $action = $request->getQueryParams()['action'] ?? 'main';

        $this->initializeView($action);

        switch ($action) {
            case 'doktypes':
                return $this->doktypesAction();
                break;
            case 'ctypes':
                return $this->ctypesAction();
                break;
            case 'listTypes':
                return $this->listTypesAction();
                break;
            case 'doktypeDetail':
                foreach ($this->tcaConfiguration->getDoktypes() as $doktype) {
                    if ($doktype->getId() === (int)$request->getQueryParams()['doktype']) {
                        return $this->doktypeDetailAction($doktype, $request->getQueryParams()['status']);
                    }
                }
                break;
            case 'ctypeDetail':
                foreach ($this->tcaConfiguration->getCtypes() as $ctype) {
                    if ($ctype->getId() === $request->getQueryParams()['ctype']) {
                        return $this->ctypeDetailAction($ctype, $request->getQueryParams()['status']);
                    }
                }
                break;
            case 'listTypeDetail':
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
        return new RedirectResponse((string)$this->uriBuilder->buildUriFromRoute('tools_ContentUsage'));
    }

    /**
     * Sets up the Fluid View.
     *
     * @param string $templateName
     */
    protected function initializeView(string $templateName): void
    {
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setTemplate($templateName);
        $this->view->setTemplateRootPaths(['EXT:content_usage/Resources/Private/Templates']);
        $this->view->setPartialRootPaths(['EXT:content_usage/Resources/Private/Partials']);
        $this->view->setLayoutRootPaths(['EXT:content_usage/Resources/Private/Layouts']);
    }

    public function mainAction(): ResponseInterface
    {
        $this->moduleTemplate->setContent($this->view->render());

        return new HtmlResponse($this->moduleTemplate->renderContent());
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

        $this->moduleTemplate->setContent($this->view->render());

        return new HtmlResponse($this->moduleTemplate->renderContent());
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

        $this->moduleTemplate->setContent($this->view->render());

        return new HtmlResponse($this->moduleTemplate->renderContent());
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

        $this->moduleTemplate->setContent($this->view->render());

        return new HtmlResponse($this->moduleTemplate->renderContent());
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

        $this->moduleTemplate->setContent($this->view->render());

        return new HtmlResponse($this->moduleTemplate->renderContent());
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

        $this->moduleTemplate->setContent($this->view->render());

        return new HtmlResponse($this->moduleTemplate->renderContent());
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

        $this->moduleTemplate->setContent($this->view->render());

        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
