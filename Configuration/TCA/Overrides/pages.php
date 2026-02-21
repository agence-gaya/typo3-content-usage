<?php

declare(strict_types=1);

// t3ver_wsid field is not configured in TCA, so the dataMapper won't map it because it's a "non persistable property" for him
// To fix that, we manually add the field in the TCA
if (!isset($GLOBALS['TCA']['pages']['columns']['t3ver_wsid'])) {
    $GLOBALS['TCA']['pages']['columns']['t3ver_wsid'] = [];
}
