<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Configuration;

use GAYA\ContentUsage\Domain\Model\Ctype;
use GAYA\ContentUsage\Domain\Model\Doktype;
use GAYA\ContentUsage\Domain\Model\Plugin;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class TcaConfiguration
{
    /**
     * @var mixed[]
     */
    private array $tca;

    public function __construct()
    {
        $this->tca = $GLOBALS['TCA'];
    }

    /**
     * @return Doktype[]
     */
    public function getDoktypes(): array
    {
        $doktypes = [];

        foreach ($this->tca['pages']['columns']['doktype']['config']['items'] as $doktypeItem) {
            if ($doktypeItem['value'] === '--div--') {
                continue;
            }

            $doktype = new Doktype();
            $doktype->setId((int) $doktypeItem['value']);
            $doktype->setLabel($this->getTranslation($doktypeItem['label']));
            $doktype->setIcon($doktypeItem['icon'] ?? '');

            $doktypes[] = $doktype;
        }

        return $doktypes;
    }

    /**
     * @return Ctype[]
     */
    public function getCtypes(): array
    {
        $ctypes = [];

        foreach ($this->tca['tt_content']['columns']['CType']['config']['items'] as $ctypeItem) {
            if ($ctypeItem['value'] === '--div--') {
                continue;
            }

            $ctype = new Ctype();
            $ctype->setId($ctypeItem['value']);
            $ctype->setLabel($this->getTranslation($ctypeItem['label']));
            $ctype->setIcon($ctypeItem['icon'] ?? '');

            $ctypes[] = $ctype;
        }

        return $ctypes;
    }

    /**
     * @return Plugin[]
     */
    public function getPlugins(): array
    {
        $plugins = [];

        if (empty($this->tca['tt_content']['columns']['list_type']['config']['items'])) {
            return $plugins;
        }

        foreach ($this->tca['tt_content']['columns']['list_type']['config']['items'] as $pluginItem) {
            if ($pluginItem['value'] === '') {
                continue;
            }

            $plugin = new Plugin();
            $plugin->setId($pluginItem['value']);
            $plugin->setLabel($this->getTranslation($pluginItem['label']));
            $plugin->setIcon($pluginItem['icon'] ?? '');

            $plugins[] = $plugin;
        }

        return $plugins;
    }

    private function getTranslation(string $key): string
    {
        if (str_starts_with($key, 'LLL:')) {
            return LocalizationUtility::translate($key);
        }

        return $key;

    }
}
