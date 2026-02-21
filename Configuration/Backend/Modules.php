<?php

declare(strict_types=1);

use GAYA\ContentUsage\Controller\ReportController;

return [
    'tools_ContentUsage' => [
        'parent' => 'system',
        'access' => 'user,group',
        'iconIdentifier' => 'module-content-usage',
        'path' => '/module/system/ContentUsage',
        'labels' => 'LLL:EXT:content_usage/Resources/Private/Language/backend.xlf',
        'extensionName' => 'ContentUsage',
        'routes' => [
            '_default' => [
                'target' => ReportController::class . '::processRequest',
            ],
            'main' => [
                'target' => ReportController::class . '::processRequest',
            ],
            'doktypes' => [
                'target' => ReportController::class . '::processRequest',
            ],
            'ctypes' => [
                'target' => ReportController::class . '::processRequest',
            ],
            'doktypeDetail' => [
                'target' => ReportController::class . '::processRequest',
            ],
            'ctypeDetail' => [
                'target' => ReportController::class . '::processRequest',
            ],
        ],
    ],
];
