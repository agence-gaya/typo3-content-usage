<?php

defined('TYPO3_MODE') || die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'GAYA.ContentUsage',
    'tools',
    'ContentUsage',
    '',
    [
        'Report' => 'main, doktypes, ctypes, listTypes, doktypeDetail, ctypeDetail, listTypeDetail',
    ],
    [
        'access' => 'user,group',
        'icon' => 'EXT:content_usage/Resources/Public/Icons/Extension.png',
        'labels' => 'LLL:EXT:content_usage/Resources/Private/Language/backend.xlf',
    ]
);
