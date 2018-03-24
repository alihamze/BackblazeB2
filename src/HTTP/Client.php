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
	use TechYet\B2\Client as B2Client;
	use TechYet\B2\Exceptions\B2Exception;
	use TechYet\B2\Exceptions\HttpClientException;
	
	class Client extends GuzzleClient {
		protected $b2Client = null;
		
		protected static $errors = [
			'expired_auth_token' => [
				'auto_retry'  => true,
				'reauthorize' => true,
			],
			'bad_auth_token'     => [
				'auto_retry'  => true,
				'reauthorize' => true,
			],
		];
		
		public function __construct(array $config = [], B2Client $b2Client) {
			parent::__construct($config);
			$this->b2Client = $b2Client;
		}
		
		
		/**
		 * @param $method
		 * @param string $uri
		 * @param array $options
		 * @param bool $parseJson
		 * @param int $successStatusCode
		 * @param int $attempt
		 * @return mixed|\Psr\Http\Message\StreamInterface
		 * @throws \Exception
		 */
		public function request($method, $uri = '', array $options = [], $parseJson = true, $successStatusCode = 200,
								$attempt = 1) {
			if ($attempt > 3) {
				throw new HttpClientException('Too many attempts', HttpClientException::TOO_MANY_RETRIES);
			}
			/** @var ResponseInterface $response */
			$response = parent::request($method, $uri, $options);
			if ($response->getStatusCode() !== $successStatusCode) {
				$retry = $this->handleError($response);
				if ($retry === true) {
					return $this->request($method, $uri, $options, $parseJson, $successStatusCode, ++$attempt);
				} else {
					throw new HttpClientException('There was an error making this HTTP request',
												  HttpClientException::UNHANDLED_HTTP_ERROR);
				}
			}
			
			if ($parseJson) {
				return json_decode($response->getBody(), true);
			}
			
			return $response->getBody()->getContents();
		}
		
		/**
		 * @param ResponseInterface $response
		 * @return bool If the request should be retried
		 * @throws \Exception
		 */
		protected function handleError($response) {
			$json = json_decode($response->getBody(), true);
			
			$errorMessage = sprintf('Received an error from B2 "%s"', $json['message']);
			
			if (isset(static::$errors[$json['code']])) {
				if (is_array(static::$errors[$json['code']])) {
					$errorDetails = static::$errors[$json['code']];
					if (isset($errorDetails['reauthorize']) && $errorDetails['reauthorize'] === true) {
						$this->b2Client->getNewAccountAuthorization();
					}
					if (isset($errorDetails['auto_retry'])) {
						return boolval($errorDetails['auto_retry']);
					} else {
						return false;
					}
				} else {
					$class = static::$errors[$json['code']];
					throw new $class($errorMessage);
				}
			} else {
				throw new B2Exception($errorMessage);
			}
		}
	}
