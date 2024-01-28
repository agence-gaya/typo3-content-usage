<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Page extends AbstractEntity
{
    protected string $title = '';

    protected string $doktype = '';

    protected int $sysLanguageUid = 0;

    protected int $t3verWsid = 0;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDoktype(): string
    {
        return $this->doktype;
    }

    public function setDoktype(string $type): void
    {
        $this->doktype = $type;
    }

    public function getSysLanguageUid(): int
    {
        return $this->sysLanguageUid;
    }

    public function setSysLanguageUid(int $sysLanguageUid): void
    {
        $this->sysLanguageUid = $sysLanguageUid;
    }

    public function getT3verWsid(): int
    {
        return $this->t3verWsid;
    }

    public function setT3verWsid(int $t3verWsid): void
    {
        $this->t3verWsid = $t3verWsid;
    }
}
