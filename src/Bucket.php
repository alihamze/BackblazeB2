<?php
	/**
	 * Created by PhpStorm.
	 * User: alihamze
	 * Date: 3/12/18
	 * Time: 5:50 PM
	 */
	
	namespace TechYet\B2;
	
	
	class Bucket {
		const TYPE_PUBLIC = 'allPublic';
		const TYPE_PRIVATE = 'allPrivate';
		
		protected $client;
		protected $id;
		protected $name;
		protected $type;
		protected $info;
		
		/**
		 * @var File[] $files The list of files in this bucket
		 */
		protected $files;
		
		/**
		 * Bucket constructor.
		 * @param Client $client
		 * @param $name
		 * @param $id
		 * @param $type
		 * @param null $info
		 */
		public function __construct($client, $name = null, $id = null, $type = null, $info = null) {
			$this->client = $client;
			$this->name = $name;
			$this->id = $id;
			$this->type = $type;
			$this->info = $info;
		}
		
		/**
		 * @return mixed
		 */
		public function getId() {
			return $this->id;
		}
		
		/**
		 * @return mixed
		 */
		public function getName() {
			return $this->name;
		}
		
		/**
		 * @return mixed
		 */
		public function getType() {
			return $this->type;
		}
		
		public function getClient() {
			return $this->client;
		}
		
		/**
		 * @param $options
		 * @return File[]
		 * @throws \Exception
		 */
		public function listFileNames($options = []) {
			if (!empty($this->files))
				return $this->files;
			
			$hasMore = true; //If there are more files
			
			$nextFileName = null; //The next file to pass in to continue looping through the files
			
			while ($hasMore) {
				$response = $this->client->getHttpClient()
										 ->request('POST', $this->client->urlForEndpoint('b2_list_file_names'), [
											 'headers' => [
												 'Authorization' => $this->client->getAuthorizationToken(),
											 ],
											 'json'    => array_merge($options, [
												 'bucketId'      => $this->id,
												 'startFileName' => $nextFileName,
											 ]),
										 ]);
				
				foreach ($response['files'] as $file) {
					$this->files[] = new File($file['fileId'], $this, $file['fileName'], $file['fileInfo'],
											  $file['contentType'], $file['contentSha1'], $file['contentLength'],
											  $file['action'], $file['uploadTimestamp']);
				}
				
				if (empty($response['nextFileName']))
					$hasMore = false;
				else
					$nextFileName = $response['nextFileName'];
			}
			
			return $this->files;
		}
		
		/**
		 * @param $name
		 * @param array $options
		 * @return null|File
		 * @throws \Exception
		 */
		public function getFileByName($name, $options = []) {
			if (!empty($this->files)) {
				foreach ($this->files as $file) {
					if ($file->getName() === $name)
						return $file;
				}
			}
			
			$response = $this->client->getHttpClient()
									 ->request('POST', $this->client->urlForEndpoint('b2_list_file_names'), [
										 'headers' => [
											 'Authorization' => $this->client->getAuthorizationToken(),
										 ],
										 'json'    => array_merge($options, [
											 'bucketId'      => $this->id,
											 'startFileName' => $name,
											 'maxFileCount'  => 1,
										 ]),
									 ]);
			$files = $response['files'];
			if (count($files) === 1) {
				$file = $files[0];
				
				return new File($file['fileId'], $this, $file['fileName'], $file['fileInfo'],
								$file['contentType'], $file['contentSha1'], $file['contentLength'],
								$file['action'], $file['uploadTimestamp']);
			}
			
			return null;
		}
		
		/**
		 * @param $name
		 * @param array $options
		 * @return bool|mixed|\Psr\Http\Message\StreamInterface
		 * @throws \Exception
		 */
		public function downloadFileByName($name, $options = []) {
			$url = sprintf('%s/file/%s/%s', $this->client->getDownloadUrl(), $this->name, $name);
			
			$sink = isset($options['SaveAs']) ? $options['SaveAs'] : null;
			unset($options['SaveAs']);
			
			$options = array_merge($options, [
				'Headers' => [
					'Authorization' => $this->getClient()->getAuthorizationToken(),
				],
				'sink'    => $sink,
			]);
			
			$successStatusCode = 200;
			
			if (isset($options['Range'])) {
				$successStatusCode = 206;
				$options['Headers']['Range'] = $options['Range'];
				unset($options['Range']);
			}
			
			$response = $this->getClient()->getHttpClient()->request('GET', $url, $options, false, $successStatusCode);
			
			return !empty($sink) ? true : $response;
		}
		
		/**
		 * @throws \Exception
		 * @return array
		 */
		public function getUploadUrl() {
			$url = $this->client->urlForEndpoint('b2_get_upload_url');
			
			$options = [
				'Headers' => [
					'Authorization' => $this->getClient()->getAuthorizationToken(),
				],
				'json'    => [
					'bucketId' => $this->id,
				],
			];
			
			$response = $this->getClient()->getHttpClient()->request('POST', $url, $options);
			
			return [
				'uploadUrl'          => $response['uploadUrl'],
				'authorizationToken' => $response['authorizationToken'],
			];
		}
		
		/**
		 * @param $name
		 * @return bool
		 * @throws \Exception
		 */
		public function fileExists($name) {
			if (!empty($this->files)) {
				foreach ($this->files as $file) {
					if ($file->getName() === $name)
						return true;
				}
				
				return false;
			} else {
				return !empty($this->getFileByName($name));
			}
		}
	}
