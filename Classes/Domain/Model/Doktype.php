<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Domain\Model;

class Doktype
{
    private int $id;

    private string $label;

    private string $icon;

    private int $totalActivePages = 0;

    private int $totalDisabledPages = 0;

    private int $totalDeletedPages = 0;

    /**
     * @var Page[]
     */
    private array $activePages = [];

    /**
     * @var Page[]
     */
    private array $disabledPages = [];

    /**
     * @var Page[]
     */
    private array $deletedPages = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    public function getTotalActivePages(): int
    {
        return $this->totalActivePages;
    }

    public function setTotalActivePages(int $totalActivePages): void
    {
        $this->totalActivePages = $totalActivePages;
    }

    public function getTotalDisabledPages(): int
    {
        return $this->totalDisabledPages;
    }

    public function setTotalDisabledPages(int $totalDisabledPages): void
    {
        $this->totalDisabledPages = $totalDisabledPages;
    }

    public function getTotalDeletedPages(): int
    {
        return $this->totalDeletedPages;
    }

    public function setTotalDeletedPages(int $totalDeletedPages): void
    {
        $this->totalDeletedPages = $totalDeletedPages;
    }

    public function getActivePages(): array
    {
        return $this->activePages;
    }

    public function setActivePages(array $activePages): void
    {
        $this->setTotalActivePages(count($activePages));
        $this->activePages = $activePages;
    }

    public function getDisabledPages(): array
    {
        return $this->disabledPages;
    }

    public function setDisabledPages(array $disabledPages): void
    {
        $this->setTotalDisabledPages(count($disabledPages));
        $this->disabledPages = $disabledPages;
    }

    public function getDeletedPages(): array
    {
        return $this->deletedPages;
    }

    public function setDeletedPages(array $deletedPages): void
    {
        $this->setTotalDeletedPages(count($deletedPages));
        $this->deletedPages = $deletedPages;
    }
}
