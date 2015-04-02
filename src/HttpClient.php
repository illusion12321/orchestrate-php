<?php
namespace andrefelipe\Orchestrate;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;

/**
 * HTTP Client prepared for Orchestrate API.
 */
class HttpClient extends GuzzleClient implements HttpClientInterface
{
    const DEFAULT_HOST = 'https://api.orchestrate.io';
    const DEFAULT_VERSION = 'v0';

    /**
     *
     * @param array $host Orchestrate API host. Defaults to 'https://api.orchestrate.io'
     * @param array $version Orchestrate API version. Defaults to 'v0'
     * @param array $config Client configuration settings
     *     - base_url: Base URL is set via $host and $version parameters,
     *     - handler: callable RingPHP handler used to transfer requests
     *     - message_factory: Factory used to create request and response object
     *     - defaults: Default request options to apply to each request
     *     - emitter: Event emitter used for request events
     *     - fsm: (internal use only) The request finite state machine. A
     *       function that accepts a transaction and optional final state. The
     *       function is responsible for transitioning a request through its
     *       lifecycle events.
     */
    public function __construct($host = null, $version = null, array $config = [])
    {
        $base_url = $host ? trim($host, '/') : self::DEFAULT_HOST;
        $base_url .= '/' . ($version ? trim($version, '/') : self::DEFAULT_VERSION) . '/';

        $config['base_url'] = $base_url;

        parent::__construct($config);
    }

    protected function getDefaultOptions()
    {
        $settings = parent::getDefaultOptions();
        $settings['headers'] = [
            'Content-Type' => 'application/json',
        ];
        $settings['auth'] = [getenv('ORCHESTRATE_API_KEY'), null];

        return $settings;
    }

    public function setApiKey($key)
    {
        $this->setDefaultOption('auth', [$key, null]);
    }

    public function ping()
    {
        return $this->request('HEAD')->getStatusCode() === 200;
    }

    public function request($method, $url = null, array $options = [])
    {
        $request = $this->createRequest($method, $url, $options);

        try {
            $response = $this->send($request);

        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();

                // honor the Orchestrate error messages
                $body = json_decode($response->getBody());
                if (!empty($body->message)) {
                    $response->setReasonPhrase($body->message);
                }

            } else {
                $options = ['reason_phrase' => $e->getMessage()];
                $response = new Response($e->getCode(), [], null, $options);
            }

        } catch (\Exception $e) {
            $options = ['reason_phrase' => $e->getMessage()];
            $response = new Response($e->getCode(), [], null, $options);
        }

        return $response;
    }
}
