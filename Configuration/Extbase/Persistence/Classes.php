<?php

declare(strict_types=1);

use GAYA\ContentUsage\Domain\Model\Content;
use GAYA\ContentUsage\Domain\Model\Page;

return [
    Page::class => [
        'tableName' => 'pages',
    ],
    Content::class => [
        'tableName' => 'tt_content',
    ],
];
