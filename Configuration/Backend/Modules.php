<?php

declare(strict_types=1);

use GAYA\ContentUsage\Controller\ReportController;

return [
    'system_contentusage' => [
        'parent' => 'system',
        'access' => 'user',
        'iconIdentifier' => 'module-content-usage',
        'path' => '/module/system/contentusage',
        'labels' => 'content_usage.modules.main',
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
