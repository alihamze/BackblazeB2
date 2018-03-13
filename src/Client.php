<?php
	/**
	 * Created by PhpStorm.
	 * User: alihamze
	 * Date: 3/12/18
	 * Time: 5:29 PM
	 */
	
	namespace TechYet\B2;
	
	use TechYet\B2\HTTP\Client as HttpClient;
	
	class Client {
		protected $accountId;
		protected $applicationKey;
		protected $apiUrl;
		protected $downloadUrl;
		protected $authorizationToken;
		protected $client;
		
		/** @var Bucket[] */
		protected $buckets;
		
		/**
		 * Client constructor.
		 * @param $accountId
		 * @param $applicationKey
		 * @param array $config
		 * @throws \Exception
		 */
		public function __construct($accountId, $applicationKey, $config = []) {
			$this->accountId = $accountId;
			$this->applicationKey = $applicationKey;
			
			if (!isset($config['client'])) {
				$config = array_merge($config, [
					'exceptions' => false,
				]);
				
				$this->client = new HttpClient($config);
			} else {
				$this->client = $config['client'];
			}
			
			$this->authorizeAccount();
		}
		
		/**
		 * @throws \Exception
		 */
		protected function authorizeAccount() {
			$url = 'https://api.backblazeb2.com/b2api/v1/b2_authorize_account';
			
			$response = $this->client->request('GET', $url, [
				'auth' => [$this->accountId, $this->applicationKey],
			]);
			
			$this->apiUrl = $response['apiUrl'];
			$this->downloadUrl = $response['downloadUrl'];
			$this->authorizationToken = $response['authorizationToken'];
		}
		
		/**
		 * @return HttpClient
		 */
		public function getHttpClient() {
			return $this->client;
		}
		
		/**
		 * @param $endpoint
		 * @return string
		 */
		public function urlForEndpoint($endpoint) {
			if ($endpoint === 'b2_download_file_by_id')
				return sprintf('%s/%s', $this->downloadUrl, $endpoint);
			
			return sprintf('%s/%s', $this->apiUrl, $endpoint);
		}
		
		/**
		 * @return Bucket[]
		 * @throws \Exception
		 */
		public function listBuckets() {
			if (!empty($this->buckets))
				return $this->buckets;
			
			$response = $this->client->request('POST', $this->urlForEndpoint('b2_list_buckets'), [
				'headers' => [
					'Authorization' => $this->authorizationToken,
				],
				'json'    => [
					'accountId' => $this->accountId,
				],
			]);
			
			foreach ($response['buckets'] as $bucket) {
				$this->buckets[$bucket['bucketName']] = new Bucket($this, $bucket['bucketName'], $bucket['bucketId'],
																   $bucket['bucketType'], $bucket['bucketInfo']);
			}
			
			return $this->buckets;
		}
		
		/**
		 * @return mixed
		 */
		public function getAuthorizationToken() {
			return $this->authorizationToken;
		}
		
		/**
		 * @return string
		 */
		public function getDownloadUrl() {
			return $this->downloadUrl;
		}
	}
