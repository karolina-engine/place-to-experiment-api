<?php

namespace Karolina\Http;
use Communicator\SignedJwt as Message;

Class ProxyRequest {
	
	private $proxyEndpoint;
	private $client;
	private $body;
	private $verb;
	private $finalEndpoint;
	private $response;
	private $proxySecret;

	public function __construct () {

		$this->proxyEndpoint = "https://relay.karolina.io/request/";
		$this->proxyEndpoint = "https://relay.karolina.io/request/test/";
    	$this->client = new \GuzzleHttp\Client();

	}

	public function setProxySecret ($key) {

		$this->proxySecret = $key;
		return $this;

	}

	public function setBody ($body) {

		$this->body = $body;
		return $this;

	}


	public function setVerb ($verb) {

		$this->verb = strtoupper($verb);
		return $this;
	}

	public function setRoute ($route) {

		$this->route = $route;
		return $this;
	}

	public function setEndpoint ($endpoint) {

		$this->finalEndpoint = $endpoint;
		return $this;

	}

	public function send () {


	    $message = new Message($this->proxySecret, 3200); 
	    $message->write('endpoint', $this->finalEndpoint);
	    $token = $message->getTokenString();		
	    $headers['verb'] = $this->verb;
	    $headers['route'] = $this->route;
	    $headers['token'] = $token;

		try {
			
			$response = $this->client->request('POST', $this->proxyEndpoint, [
			    'headers' => $headers, 'json' => $this->body
			    ]);
			
		} catch (ClientException $e) {
			
			$response = $e->getResponse();


		} catch (\Exception $e) {

			$response = $e->getResponse();
		}

		$this->response = $response;

		return $this;

	}

	public function getResponseBody() {

		return (string) $this->response->getBody();

	}

	public function getResponseCode () {

		return $this->response->getStatusCode();
	}

}