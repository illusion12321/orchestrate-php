<?php
namespace andrefelipe\Orchestrate\Response;

use GuzzleHttp\Message\ResponseInterface;


/*

URL_PATTERNS = [
    "/v0/(?P<collection>.+)/(?P<key>.+)/events/(?P<type>.+)/(?P<timestamp>\d+)/(?P<ordinal>\d+)",
    "/v0/(?P<collection>.+)/(?P<key>.+)/events/(?P<type>.+)/(?P<timestamp>\d+)",
    "/v0/(?P<collection>.+)/(?P<key>.+)/events/(?P<type>.+)",
    "/v0/(?P<collection>.+)/(?P<key>.+)/refs/(?P<ref>.+)",
    "/v0/(?P<collection>.+)/(?P<key>.+)/refs",
    "/v0/(?P<collection>.+)/(?P<key>.+)/relations/(?P<kind>.+)/(?P<to_collection>.+)/(?P<to_key>.+)",
    "/v0/(?P<collection>.+)/(?P<key>.+)/relations/(?P<kinds>.+)",
    "/v0/(?P<collection>.+)/(?P<key>.+)",
    "/v0/(?P<collection>.+)"
]


*/

class Response
{
	
	
	protected $error;

	/**
	 * @var \GuzzleHttp\Message\ResponseInterface
	 */
	protected $httpResponse;


	/**
	 * @param \GuzzleHttp\Message\ResponseInterface $httpResponse
	 */
	public function __construct(ResponseInterface $httpResponse)
	{
	    $this->httpResponse = $httpResponse;
	    
	    // set error message
	    if (!$this->success()) {
	    	$this->error = $httpResponse->json();
	    }
	}



	public function success()
	{
		$code = $this->getStatusCode();
		return !($code >= 400 && $code <= 599);
	}	

	public function getError()
	{
		return $this->error;
	}

	public function getStatusCode()
	{
		return $this->httpResponse->getStatusCode();
	}

	public function getReasonPhrase()
	{
		return $this->httpResponse->getReasonPhrase();
	}

	public function getEffectiveUrl()
	{
		return $this->httpResponse->getEffectiveUrl();
	}

	public function getLocation()
	{
		$location = $this->http_response->getHeader('Content-Location');

		if (!$location)
			$location = $this->http_response->getHeader('Location');			

		return $location;
	}

	public function getETag()
	{
		return $this->httpResponse->getHeader('ETag');
	}

	public function getRequestId()
	{
		return $this->httpResponse->getHeader('X-ORCHESTRATE-REQ-ID');
	}

	public function getDate()
	{
		return $this->httpResponse->getHeader('Date');
	}
	


	


}