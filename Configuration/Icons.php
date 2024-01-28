<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;

return [
    'module-content-usage' => [
        'provider' => BitmapIconProvider::class,
        // The source bitmap file
        'source' => 'EXT:content_usage/Resources/Public/Icons/Extension.png',
        // All icon providers provide the possibility to register an icon that spins
        'spinning' => false,
    ],
];
