<?php

namespace Communicator;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;

class SignedJWt implements SignedMessageInterface
{

	private $signer;
	private $key;
	private $token;
	private $builder;

	public function __construct ($signingKey, $expiresIn = 60) {

		$this->setKey($signingKey);
		$this->signer = new Sha256();
		$this->builder = new Builder();
		$this->expiresIn($expiresIn);

	}

	public function expiresIn ($seconds) {
	
		$this->builder->setExpiration(time() + $seconds);
		return $this;
	}

	public function fromTokenString ($tokenString) {

		$this->token = (new Parser())->parse($tokenString);
	}

	public function getTokenString () {

		$this->builder->sign($this->signer, $this->key);
		$this->token = $this->builder->getToken();

		return (string) $this->token;
	}

	public function setKey($key) {

		$minLength = 32;

		if (strlen($key) < $minLength) {

			throw new \Exception('Key is too short. Must not be less than '.$minLength);
		}

		$this->key = $key;

	}
	public function write ($claim, $value) {


		$this->builder->set($claim, $value);
		return $this;


	}

	public function read ($claim) {

		try {
			if ($this->valid()) {
		
				return $this->token->getClaim($claim);

			}

		} catch (\Exception $e) {

			 throw new \Exception($e->getMessage());
		}

	}

	public function readAll () {
		try {

			if ($this->valid()) {
		
				return $this->token->getClaims();

			}
		} catch (\Exception $e) {

			 throw new \Exception($e->getMessage());
		}

	}

	private function valid () {

		if ($this->token->verify($this->signer, $this->key)) {

			$data = new ValidationData();

			if ($this->token->validate($data)) {

				return true;

			} else {
	
		        throw new \Exception('Expired');

			}

		} else {

	        throw new \Exception('Wrong signature');

		}



	}

}




Interface SignedMessageInterface {

	public function expiresIn($seconds);

}