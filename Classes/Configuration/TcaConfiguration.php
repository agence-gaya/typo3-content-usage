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
            if ($doktypeItem[1] === '--div--') {
                continue;
            }

            $doktype = new Doktype();
            $doktype->setId((int)$doktypeItem[1]);
            $doktype->setLabel($this->getTranslation($doktypeItem[0]));
            $doktype->setIcon($doktypeItem[2] ?? '');

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
            if ($ctypeItem[1] === '--div--') {
                continue;
            }

            $ctype = new Ctype();
            $ctype->setId($ctypeItem[1]);
            $ctype->setLabel($this->getTranslation($ctypeItem[0]));
            $ctype->setIcon($ctypeItem[2] ?? '');

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

        foreach ($this->tca['tt_content']['columns']['list_type']['config']['items'] as $pluginItem) {
            if ($pluginItem[1] === '') {
                continue;
            }

            $plugin = new Plugin();
            $plugin->setId($pluginItem[1]);
            $plugin->setLabel($this->getTranslation($pluginItem[0]));
            $plugin->setIcon($pluginItem[2] ?? '');

            $plugins[] = $plugin;
        }

        return $plugins;
    }

    private function getTranslation(string $key): string
    {
        if (str_starts_with($key, 'LLL:')) {
            return LocalizationUtility::translate($key);
        } else {
            return $key;
        }
    }
}
