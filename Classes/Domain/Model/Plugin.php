<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Domain\Model;

class Plugin
{
    private string $id;

    private string $label;

    private string $icon;

    private int $totalActiveContents = 0;

    private int $totalDisabledContents = 0;

    private int $totalDeletedContents = 0;

    /**
     * @var Content[]
     */
    private array $activeContents = [];

    /**
     * @var Content[]
     */
    private array $disabledContents = [];

    /**
     * @var Content[]
     */
    private array $deletedContents = [];

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
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

    public function getTotalActiveContents(): int
    {
        return $this->totalActiveContents;
    }

    public function setTotalActiveContents(int $totalActiveContents): void
    {
        $this->totalActiveContents = $totalActiveContents;
    }

    public function getTotalDisabledContents(): int
    {
        return $this->totalDisabledContents;
    }

    public function setTotalDisabledContents(int $totalDisabledContents): void
    {
        $this->totalDisabledContents = $totalDisabledContents;
    }

    public function getTotalDeletedContents(): int
    {
        return $this->totalDeletedContents;
    }

    public function setTotalDeletedContents(int $totalDeletedContents): void
    {
        $this->totalDeletedContents = $totalDeletedContents;
    }

    /**
     * @return Content[]
     */
    public function getActiveContents(): array
    {
        return $this->activeContents;
    }

    /**
     * @param Content[] $activeContents
     */
    public function setActiveContents(array $activeContents): void
    {
        $this->setTotalActiveContents(count($activeContents));
        $this->activeContents = $activeContents;
    }

    /**
     * @return Content[]
     */
    public function getDisabledContents(): array
    {
        return $this->disabledContents;
    }

    /**
     * @param Content[] $disabledContents
     */
    public function setDisabledContents(array $disabledContents): void
    {
        $this->setTotalDisabledContents(count($disabledContents));
        $this->disabledContents = $disabledContents;
    }

    /**
     * @return Content[]
     */
    public function getDeletedContents(): array
    {
        return $this->deletedContents;
    }

    /**
     * @param Content[] $deletedContents
     */
    public function setDeletedContents(array $deletedContents): void
    {
        $this->setTotalDeletedContents(count($deletedContents));
        $this->deletedContents = $deletedContents;
    }
}
