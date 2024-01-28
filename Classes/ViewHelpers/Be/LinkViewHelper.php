<?php

declare(strict_types=1);

/*
 * This file is a copy of \TYPO3\CMS\Fluid\ViewHelpers\Be\LinkViewHelper
 *
 * It has the same behaviour, we just add an anchor option
 */

namespace GAYA\ContentUsage\ViewHelpers\Be;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * A ViewHelper for creating URIs to modules.
 *
 * Examples
 * ========
 *
 * URI to the web_ts module on page 92::
 *
 *    <f:be.link route="web_ts" parameters="{id: 92}">Go to web_ts</f:be.link>
 *
 * ``<a href="/typo3/module/web/ts?token=b6e9c9f&id=92">Go to web_ts</a>``
 */
final class LinkViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'a';

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('route', 'string', 'The name of the route', true);
        $this->registerArgument('parameters', 'array', 'An array of parameters', false, []);
        $this->registerArgument('referenceType', 'string', 'The type of reference to be generated (one of the constants)', false, UriBuilder::ABSOLUTE_PATH);
        $this->registerArgument('anchor', 'string', 'Specifies the anchor', false, '');
        $this->registerTagAttribute('name', 'string', 'Specifies the name of an anchor');
        $this->registerTagAttribute(
            'rel',
            'string',
            'Specifies the relationship between the current document and the linked document'
        );
        $this->registerTagAttribute(
            'rev',
            'string',
            'Specifies the relationship between the linked document and the current document'
        );
        $this->registerTagAttribute('target', 'string', 'Specifies where to open the linked document');
        $this->registerUniversalTagAttributes();
    }

    public function render(): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $route = $this->arguments['route'];
        $parameters = $this->arguments['parameters'];
        $referenceType = $this->arguments['referenceType'];
        $anchor = $this->arguments['anchor'];
        $uri = $uriBuilder->buildUriFromRoute($route, $parameters, $referenceType);

        if ($anchor) {
            $uri .= '#' . $anchor;
        }

        $this->tag->addAttribute('href', (string)$uri);
        $this->tag->setContent((string)$this->renderChildren());
        $this->tag->forceClosingTag(true);

        return $this->tag->render();
    }
}
