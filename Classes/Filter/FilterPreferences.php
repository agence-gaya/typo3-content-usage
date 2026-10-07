<?php

declare(strict_types=1);

namespace GAYA\ContentUsage\Filter;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

final class FilterPreferences
{
    public function resolve(ServerRequestInterface $request, string $screen, BackendUserAuthentication $user, array $languages, array $workspaces): RecordFilters
    {
        $data = $request->getAttribute('moduleData');
        if (!$data instanceof ModuleData) {
            $storedData = $user->getModuleData('system_contentusage');
            $data = new ModuleData('system_contentusage', is_array($storedData) ? $storedData : []);
        }

        $key = $screen . 'Filters';
        $stored = $data->get($key, []);
        $stored = is_array($stored) ? $stored : [];

        $body = $request->getParsedBody();
        $parameters = array_replace($stored, $request->getQueryParams(), is_array($body) ? $body : []);
        $filters = new RecordFilters(
            $this->normalize($parameters['language'] ?? 'all', $languages),
            $this->normalize($parameters['workspace'] ?? 'all', $workspaces),
        );
        if ($data->get($key) !== $filters->toParameters()) {
            $data->set($key, $filters->toParameters());
            $user->pushModuleData('system_contentusage', $data->toArray());
        }

        return $filters;
    }

    private function normalize(mixed $value, array $options): ?int
    {
        if (!is_string($value) && !is_int($value)) {
            return null;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT);
        return $id !== false && array_key_exists($id, $options) ? $id : null;
    }
}
