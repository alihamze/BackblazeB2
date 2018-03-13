<?php
	/**
	 * Created by PhpStorm.
	 * User: alihamze
	 * Date: 3/12/18
	 * Time: 5:37 PM
	 */
	
	namespace TechYet\B2\HTTP;
	
	
	use GuzzleHttp\Client as GuzzleClient;
	use Psr\Http\Message\ResponseInterface;
	use TechYet\B2\Exceptions\B2Exception;
	
	class Client extends GuzzleClient {
		protected static $errors = [
		
		];
		
		/**
		 * @param $method
		 * @param string $uri
		 * @param array $options
		 * @param bool $parseJson
		 * @param int $successStatusCode
		 * @return mixed|\Psr\Http\Message\StreamInterface
		 * @throws \Exception
		 */
		public function request($method, $uri = '', array $options = [], $parseJson = true, $successStatusCode = 200) {
			/** @var ResponseInterface $response */
			$response = parent::request($method, $uri, $options);
			if ($response->getStatusCode() !== $successStatusCode) {
				return $this->handleError($response);
			}
			
			if ($parseJson) {
				return json_decode($response->getBody(), true);
			}
			
			return $response->getBody();
		}
		
		/**
		 * @param ResponseInterface $response
		 * @throws \Exception
		 */
		protected function handleError($response) {
			$json = json_decode($response->getBody(), true);
			
			$errorMessage = sprintf('Received an error from B2 "%s"', $json['message']);
			
			if (isset(static::$errors[$json['code']])) {
				$class = static::$errors[$json['code']];
				throw new $class($errorMessage);
			} else {
				throw new B2Exception($errorMessage);
			}
		}
	}
