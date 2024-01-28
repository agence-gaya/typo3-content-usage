<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Controller;

use GAYA\ContentUsage\Configuration\TcaConfiguration;
use GAYA\ContentUsage\Domain\Repository\ContentRepository;
use GAYA\ContentUsage\Domain\Repository\PageRepository;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Beuser\Domain\Model\ModuleData;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ReportController extends ActionController
{
    private TcaConfiguration $tcaConfiguration;

    private PageRepository $pageRepository;

    private ContentRepository $contentRepository;

    protected ?ModuleData $moduleData = null;

    protected ?ModuleTemplate $moduleTemplate = null;

    public function __construct(
        TcaConfiguration $tcaConfiguration,
        PageRepository $pageRepository,
        ContentRepository $contentRepository
    ) {
        $this->pageRepository = $pageRepository;
        $this->tcaConfiguration = $tcaConfiguration;
        $this->contentRepository = $contentRepository;

    }

    public function mainAction()
    {

    }

    public function doktypesAction()
    {
        $doktypes = $this->tcaConfiguration->getDoktypes();
        foreach ($doktypes as $doktype) {
            $doktype->setTotalActivePages($this->pageRepository->countActiveByDoktype($doktype));
            $doktype->setTotalDisabledPages($this->pageRepository->countDisabledByDoktype($doktype));
            $doktype->setTotalDeletedPages($this->pageRepository->countDeletedByDoktype($doktype));
        }

        $this->view->assign('doktypes', $doktypes);
    }

    public function ctypesAction()
    {
        $ctypes = $this->tcaConfiguration->getCtypes();
        foreach ($ctypes as $ctype) {
            $ctype->setTotalActiveContents($this->contentRepository->countActiveByCtype($ctype));
            $ctype->setTotalDisabledContents($this->contentRepository->countDisabledByCtype($ctype));
            $ctype->setTotalDeletedContents($this->contentRepository->countDeletedByCtype($ctype));
        }

        $this->view->assign('ctypes', $ctypes);
    }

    public function listTypesAction()
    {
        $plugins = $this->tcaConfiguration->getPlugins();
        foreach ($plugins as $plugin) {
            $plugin->setTotalActiveContents($this->contentRepository->countActiveByPlugin($plugin));
            $plugin->setTotalDisabledContents($this->contentRepository->countDisabledByPlugin($plugin));
            $plugin->setTotalDeletedContents($this->contentRepository->countDeletedByPlugin($plugin));
        }

        $this->view->assign('plugins', $plugins);
    }

    public function doktypeDetailAction(int $doktypeId, string $status)
    {
        $doktype = null;
        $doktypeFound = false;
        foreach ($this->tcaConfiguration->getDoktypes() as $doktype) {
            if ($doktype->getId() === $doktypeId) {
                $doktypeFound = true;
                break;
            }
        }

        if (!$doktypeFound) {
            $this->redirect('main');
        }

        switch ($status) {
            case 'active':
                $doktype->setActivePages($this->pageRepository->findActiveByDoktype($doktype));
                break;
            case 'disabled':
                $doktype->setDisabledPages($this->pageRepository->findDisabledByDoktype($doktype));
                break;
            case 'deleted':
                $doktype->setDeletedPages($this->pageRepository->findDeletedByDoktype($doktype));
                break;
        };

        $this->view->assign('doktype', $doktype);
        $this->view->assign('status', $status);
    }

    public function ctypeDetailAction(string $ctypeName, string $status)
    {
        $ctype = null;
        $ctypeFound = false;
        foreach ($this->tcaConfiguration->getCtypes() as $ctype) {
            if ($ctype->getId() === $ctypeName) {
                $ctypeFound = true;
                break;
            }
        }

        if (!$ctypeFound) {
            $this->redirect('main');
        }

        switch ($status) {
            case 'active':
                $ctype->setActiveContents($this->contentRepository->findActiveByCtype($ctype));
                break;
            case 'disabled':
                $ctype->setDisabledContents($this->contentRepository->findDisabledByCtype($ctype));
                break;
            case 'deleted':
                $ctype->setDeletedContents($this->contentRepository->findDeletedByCtype($ctype));
                break;
        };

        $this->view->assign('ctype', $ctype);
        $this->view->assign('status', $status);
    }

    public function listTypeDetailAction(string $listType, string $status)
    {
        $plugin = null;
        $pluginFound = false;
        foreach ($this->tcaConfiguration->getPlugins() as $plugin) {
            if ($plugin->getId() === $listType) {
                $pluginFound = true;
                break;
            }
        }

        if (!$pluginFound) {
            $this->redirect('main');
        }

        switch ($status) {
            case 'active':
                $plugin->setActiveContents($this->contentRepository->findActiveByPlugin($plugin));
                break;
            case 'disabled':
                $plugin->setDisabledContents($this->contentRepository->findDisabledByPlugin($plugin));
                break;
            case 'deleted':
                $plugin->setDeletedContents($this->contentRepository->findDeletedByPlugin($plugin));
                break;
        };

        $this->view->assign('plugin', $plugin);
        $this->view->assign('status', $status);
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
