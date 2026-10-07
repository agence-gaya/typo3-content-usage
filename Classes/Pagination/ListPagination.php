<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Pagination;

final readonly class ListPagination
{
    public int $page;

    public int $numberOfPages;

    public ?int $limit;

    public int $offset;

    public function __construct(public int $total, public int|string $pageSize, mixed $requestedPage)
    {
        $this->limit = $pageSize === 'all' ? null : (int)$pageSize;
        $this->numberOfPages = $this->limit === null ? 1 : max(1, (int)ceil($total / $this->limit));
        $page = (is_int($requestedPage) || is_string($requestedPage)) ? filter_var($requestedPage, FILTER_VALIDATE_INT) : false;
        $this->page = min($this->numberOfPages, max(1, $page === false ? 1 : $page));

        $this->offset = ($this->page - 1) * ($this->limit ?? 0);
    }

    public function getStart(): int
    {
        return $this->total === 0 ? 0 : $this->offset + 1;
    }

    public function getEnd(): int
    {
        return min($this->total, $this->offset + ($this->limit ?? $this->total));
    }

    public function getPages(): array
    {
        $start = max(1, min($this->page - 3, $this->numberOfPages - 6));
        return range($start, min($this->numberOfPages, $start + 6));
    }

    public function getPrevious(): int
    {
        return max(1, $this->page - 1);
    }

    public function getNext(): int
    {
        return min($this->numberOfPages, $this->page + 1);
    }
}
