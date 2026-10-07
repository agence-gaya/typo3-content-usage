<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Filter;

final readonly class RecordFilters
{
    public function __construct(public ?int $language = null, public ?int $workspace = null) {}

    public function toParameters(): array
    {
        return ['language' => $this->language ?? 'all', 'workspace' => $this->workspace ?? 'all'];
    }
}
