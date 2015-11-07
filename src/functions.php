<?php
namespace andrefelipe\Orchestrate;

const DEFAULT_HOST = 'https://api.orchestrate.io';
const DEFAULT_VERSION = 'v0';

/**
 * Form default configuration settings for Guzzle Client.
 *
 * @param string $apiKey  Orchestrate API key. Defaults to getenv('ORCHESTRATE_API_KEY').
 * @param string $host    Orchestrate API host. Defaults to 'https://api.orchestrate.io'
 * @param string $version Orchestrate API version. Defaults to 'v0'
 */
function default_http_config($apiKey = null, $host = null, $version = null)
{
    $base_uri = $host ? trim($host, '/') : DEFAULT_HOST;
    $base_uri .= '/'.($version ? trim($version, '/') : DEFAULT_VERSION).'/';

    return [
        'base_uri' => $base_uri,
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'auth' => [$apiKey ?: getenv('ORCHESTRATE_API_KEY'), null],
        'http_errors' => false,
    ];
}
