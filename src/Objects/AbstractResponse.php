<?php
namespace andrefelipe\Orchestrate\Objects;

use Psr\Http\Message\ResponseInterface;

abstract class AbstractResponse extends AbstractConnection
{
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

    /**
     * Gets the body of the response as associative array.
     *
     * @return array|null Body decoded as associative array, or null if unknown.
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * Get the PSR-7 Response object of the last request.
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Gets the status of the last response.
     * If the request was successful the value is the HTTP Reason-Phrase.
     * If not, the value is the Orchestrate Error Description.
     *
     * @return string|null Reason phrase, or null if unknown.
     * @link https://orchestrate.io/docs/apiref#errors
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Gets the response status code.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->_response ? $this->_response->getStatusCode() : 0;
    }

    /**
     * Gets the X-ORCHESTRATE-REQ-ID header.
     *
     * @return string|null
     */
    public function getOrchestrateRequestId()
    {
        if ($this->_response) {
            $value = $this->_response->getHeader('X-ORCHESTRATE-REQ-ID');
            return empty($value) ? null : $value[0];
        }
        return null;
    }

    /**
     * Gets the Date header from the response. Note, it's the request date
     * generated from the Orchestrate server, not our application.
     *
     * @return string|null Response date header.
     */
    public function getResponseDate()
    {
        if ($this->_response) {
            $value = $this->_response->getHeader('Date');
            return empty($value) ? null : $value[0];
        }
        return null;
    }

    /**
     * Check if last request was successful.
     *
     * A request is considered successful if status code is not 4xx or 5xx.
     *
     * @return boolean
     */
    public function isSuccess()
    {
        return !$this->isError();
    }

    /**
     * Check if last request was unsuccessful.
     *
     * A request is considered error if status code is 4xx or 5xx.
     *
     * @return boolean
     */
    public function isError()
    {
        $code = $this->getStatusCode();
        return !$code || ($code >= 400 && $code <= 599);
    }

    /**
     * Resets current object for reuse.
     */
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
