<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate as Orchestrate;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides the bare basis, a connection to a HTTP client.
 */
abstract class AbstractConnection implements ConnectionInterface
{
    /**
     * @var ClientInterface
     */
    private $_httpClient;

    /**
     * @var array
     */
    private $_body = null;

    /**
     * @var ResponseInterface
     */
    private $_response = null;

    /**
     * @var string
     */
    private $_status = null;

    public function getHttpClient()
    {
        if (!$this->_httpClient) {
            $this->_httpClient = Orchestrate\default_http_client();
        }

        return $this->_httpClient;
    }

    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->_httpClient = $httpClient;

        return $this;
    }

    public function getBody()
    {
        return $this->_body;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function getStatus()
    {
        return $this->_status;
    }

    public function getStatusCode()
    {
        return $this->_response ? $this->_response->getStatusCode() : 0;
    }

    public function getOrchestrateRequestId()
    {
        if ($this->_response) {
            $value = $this->_response->getHeader('X-ORCHESTRATE-REQ-ID');
            return empty($value) ? null : $value[0];
        }
        return null;
    }

    public function isSuccess()
    {
        return !$this->isError();
    }

    public function isError()
    {
        $code = $this->getStatusCode();
        return !$code || ($code >= 400 && $code <= 599);
    }

    public function reset()
    {
        $this->_response = null;
        $this->_body = null;
        $this->_status = null;
    }

    /**
     * Request using the current HTTP client and store the response and
     * decoded json body internally.
     *
     * More information on the parameters please go to the Guzzle docs.
     *
     * @param string $method  HTTP method
     * @param string $uri     URI
     * @param array  $options Request options to apply.
     *
     * @return ResponseInterface
     */
    protected function request($method, $uri = null, array $options = [])
    {
        // safely build query
        if (isset($options['query']) && is_array($options['query'])) {
            $options['query'] = http_build_query($options['query'], null, '&', PHP_QUERY_RFC3986);
        }

        // request
        $this->_response = $this->getHttpClient()
            ->request($method, $uri, $options);

        // set body
        $this->_body = json_decode($this->_response->getBody(), true);

        // set status message
        if ($this->isError() && !empty($this->_body['message'])) {
            // honor the Orchestrate error messages
            $this->_status = $this->_body['message'];
        } else {
            // continue with HTTP Reason-Phrase
            $this->_status = $this->_response->getReasonPhrase();
        }

        return $this->_response;
    }
}
