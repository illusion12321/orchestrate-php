<?php
namespace andrefelipe\Orchestrate;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;

/**
 * Class that implements the ClientInterface methods and the children classes.
 *
 * @link https://orchestrate.io/docs/apiref
 */
class HttpClient extends GuzzleClient implements HttpClientInterface
{
    /**
     * @var string
     */
    private $_host = 'https://api.orchestrate.io';

    /**
     * @var string
     */
    private $_apiVersion = 'v0';

    /**
     * @var string
     */
    private $_apiKey;

    /**
     * @param string $apiKey
     * @param string $host
     */
    public function __construct($apiKey = null, $host = null)
    {
        $this->setApiKey($apiKey)
             ->setHost($host);

        // set defaults
        parent::__construct([
            'base_url' => $this->getHost() . '/' . $this->getApiVersion() . '/',
            'defaults' => [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'auth' => [$this->getApiKey(), null],
            ],
        ]);
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->_apiKey;
    }

    public function setApiKey($key) // TODO actually change the guzzle defaults upon set

    {
        if ($key) {
            $this->_apiKey = $key;
        } else {
            $this->_apiKey = getenv('ORCHESTRATE_API_KEY');
        }

        return $this;
    }

    public function getHost()
    {
        return $this->_host;
    }

    public function setHost($host)
    {
        if ($host) {
            $this->_host = rtrim($host, '/');
            $this->_host = rtrim($host, '/v0');
        }

        return $this;
    }
    public function getApiVersion()
    {
        return $this->_apiVersion;
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
