<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Controller;

use GAYA\ContentUsage\Configuration\TcaConfiguration;
use GAYA\ContentUsage\Domain\Model\Ctype;
use GAYA\ContentUsage\Domain\Model\Doktype;
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
        /** @var Route $route */
        $route = $request->getAttribute('route');
        $routeIdentifier = $route->getOption('_identifier');
        $queryParams = $request->getQueryParams();

        if (in_array($routeIdentifier, ['system_contentusage.doktypeDetail', 'system_contentusage.ctypeDetail'], true)) {
            $status = $queryParams['status'] ?? null;
            $type = $queryParams[$routeIdentifier === 'system_contentusage.doktypeDetail' ? 'doktype' : 'ctype'] ?? null;

            if (!in_array($status, ['active', 'disabled', 'deleted'], true)) {
                return new RedirectResponse((string)$this->uriBuilder->buildUriFromRoute('system_contentusage'));
            }

            if ($routeIdentifier === 'system_contentusage.doktypeDetail') {
                if ((!is_string($type) && !is_int($type)) || filter_var($type, FILTER_VALIDATE_INT) === false) {
                    return new RedirectResponse((string)$this->uriBuilder->buildUriFromRoute('system_contentusage'));
                }
            } elseif (!is_string($type) || $type === '') {
                return new RedirectResponse((string)$this->uriBuilder->buildUriFromRoute('system_contentusage'));
            }
        }

        $this->view = $this->moduleTemplateFactory->create($request);
        $this->view->assign('hasRecycler', ExtensionManagementUtility::isLoaded('recycler'));

        switch ($routeIdentifier) {
            case 'system_contentusage.doktypes':
                return $this->doktypesAction();
            case 'system_contentusage.ctypes':
                return $this->ctypesAction();
            case 'system_contentusage.doktypeDetail':
                foreach ($this->tcaConfiguration->getDoktypes() as $doktype) {
                    if ($doktype->getId() === (int)$queryParams['doktype']) {
                        return $this->doktypeDetailAction($doktype, $queryParams['status']);
                    }
                }

                break;
            case 'system_contentusage.ctypeDetail':
                foreach ($this->tcaConfiguration->getCtypes() as $ctype) {
                    if ($ctype->getId() === $queryParams['ctype']) {
                        return $this->ctypeDetailAction($ctype, $queryParams['status']);
                    }
                }

                break;
            default:
                return $this->mainAction();
        }

        // If we are here, there was a problem
        return new RedirectResponse((string)$this->uriBuilder->buildUriFromRoute('system_contentusage'));
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
}
