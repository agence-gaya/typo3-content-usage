<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use GAYA\ContentUsage\Controller\ReportController;

defined('TYPO3') or die();

ExtensionManagementUtility::addModule(
    'tools',
    'ContentUsage',
    '',
    '',
    [
        'routeTarget' => ReportController::class . '::processRequest',
        'access' => 'user,group',
        'name' => 'tools_ContentUsage',
        'iconIdentifier' => 'module-content-usage',
        'labels' => 'LLL:EXT:content_usage/Resources/Private/Language/backend.xlf',
    ]
);
