<?php
namespace andrefelipe\Orchestrate;

use GuzzleHttp\Client as GuzzleClient;

/**
 * HTTP Client pre-set for Orchestrate API.
 */
class HttpClient extends GuzzleClient
{
    const DEFAULT_HOST = 'https://api.orchestrate.io';
    const DEFAULT_VERSION = 'v0';

    /**
     * @param array $host       Orchestrate API host. Defaults to
     *                          'https://api.orchestrate.io'
     * @param array $version    Orchestrate API version. Defaults to 'v0'
     * @param array $config     Client configuration settings. Please check
     *                          Guzzle documentation for available options.
     */
    public function __construct($host = null, $version = null, array $config = [])
    {
        $base_uri = $host ? trim($host, '/') : self::DEFAULT_HOST;
        $base_uri .= '/' . ($version ? trim($version, '/') : self::DEFAULT_VERSION) . '/';

        $config['base_uri'] = $base_uri;

        if (!isset($config['http_errors'])) {
            $config['http_errors'] = false;
        }

        $config['headers'] = [
            'Content-Type' => 'application/json',
        ];
        $config['auth'] = [getenv('ORCHESTRATE_API_KEY'), null];

        parent::__construct($config);
    }

    // TODO how to change this!? maybe remove this class in favor of a simple Guzzle Client generator?

    // public function setApiKey($key)
    // {
    //     $this->setDefaultOption('auth', [$key, null]);
    // }

    public function ping()
    {
        return $this->request('HEAD')->getStatusCode() === 200;
    }
}
