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
     * Plese note, base_url must have a trailing slash, for example: https://api.orchestrate.io/v0/
     *
     * @param array $config Client configuration settings
     *     - base_url: Base URL of the client that is merged into relative URLs.
     *       Can be a string or an array that contains a URI template followed
     *       by an associative array of expansion variables to inject into the
     *       URI template.
     *     - handler: callable RingPHP handler used to transfer requests
     *     - message_factory: Factory used to create request and response object
     *     - defaults: Default request options to apply to each request
     *     - emitter: Event emitter used for request events
     *     - fsm: (internal use only) The request finite state machine. A
     *       function that accepts a transaction and optional final state. The
     *       function is responsible for transitioning a request through its
     *       lifecycle events.
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['base_url'])) {
            $config['base_url'] = self::DEFAULT_HOST . '/' . self::DEFAULT_VERSION . '/';
        }
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
