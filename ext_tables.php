<?php

defined('TYPO3') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'ContentUsage',
    'tools',
    'ContentUsage',
    '',
    [
        \GAYA\ContentUsage\Controller\ReportController::class => 'main, doktypes, ctypes, listTypes, doktypeDetail, ctypeDetail, listTypeDetail',
    ],
    [
        'access' => 'user,group',
        'icon' => 'EXT:content_usage/Resources/Public/Icons/Extension.png',
        'labels' => 'LLL:EXT:content_usage/Resources/Private/Language/backend.xlf',
    ]
);