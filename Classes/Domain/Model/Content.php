<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Content extends AbstractEntity
{
    protected string $header = '';

    protected string $ctype = '';

    protected string $listType = '';

    protected int $sysLanguageUid = 0;

    protected int $t3verWsid = 0;

    public function getHeader(): string
    {
        return $this->header;
    }

    public function setHeader(string $header): void
    {
        $this->header = $header;
    }

    public function getCtype(): string
    {
        return $this->ctype;
    }

    public function setCtype(string $ctype): void
    {
        $this->ctype = $ctype;
    }

    public function getListType(): string
    {
        return $this->listType;
    }

    public function setListType(string $listType): void
    {
        $this->listType = $listType;
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
