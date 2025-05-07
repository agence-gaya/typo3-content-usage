<?php

declare(strict_types=1);

use GAYA\ContentUsage\Domain\Model\Page;
use GAYA\ContentUsage\Domain\Model\Content;

return [
    Page::class => [
        'tableName' => 'pages',
    ],
    Content::class => [
        'tableName' => 'tt_content',
    ],
];
