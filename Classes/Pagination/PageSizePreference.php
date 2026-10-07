<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Pagination;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

final class PageSizePreference
{
    public function resolve(ServerRequestInterface $request, string $screen, BackendUserAuthentication $user): int|string
    {
        $key = $screen . 'PageSize';
        $data = $request->getAttribute('moduleData');
        if (!$data instanceof ModuleData) {
            $stored = $user->getModuleData('system_contentusage');
            $data = new ModuleData('system_contentusage', is_array($stored) ? $stored : []);
        }

        $body = $request->getParsedBody();
        $body = is_array($body) ? $body : [];

        $value = $body['itemsPerPage'] ?? $request->getQueryParams()['itemsPerPage'] ?? $data->get($key, 100);
        $size = match ($value) {
            50, '50' => 50,
            100, '100' => 100,
            500, '500' => 500,
            1000, '1000' => 1000,
            'all' => 'all',
            default => 100,
        };
        if ($data->get($key) !== $size) {
            $data->set($key, $size);
            $user->pushModuleData('system_contentusage', $data->toArray());
        }

        return $size;
    }
}
