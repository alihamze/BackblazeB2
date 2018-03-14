<?php
	/**
	 * Created by PhpStorm.
	 * User: alihamze
	 * Date: 3/12/18
	 * Time: 5:49 PM
	 */
	
	namespace TechYet\B2;
	
	
	class File {
		protected $id;
		protected $bucket;
		protected $name;
		protected $info;
		protected $type;
		protected $sha1;
		protected $size;
		protected $action;
		protected $uploadedTimestamp;
		
		/**
		 * File constructor.
		 * @param $id
		 * @param Bucket $bucket
		 * @param $name
		 * @param $info
		 * @param $type
		 * @param $sha1
		 * @param $size
		 * @param $action
		 * @param $uploadedTimestamp
		 */
		public function __construct($id, Bucket $bucket, $name, $info, $type, $sha1, $size, $action,
									$uploadedTimestamp) {
			$this->id = $id;
			$this->bucket = $bucket;
			$this->name = $name;
			$this->info = $info;
			$this->type = $type;
			$this->sha1 = $sha1;
			$this->size = $size;
			$this->action = $action;
			$this->uploadedTimestamp = $uploadedTimestamp;
		}
		
		/**
		 * @return mixed
		 */
		public function getId() {
			return $this->id;
		}
		
		/**
		 * @return Bucket
		 */
		public function getBucket() {
			return $this->bucket;
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
		public function getInfo() {
			return $this->info;
		}
		
		/**
		 * @return mixed
		 */
		public function getType() {
			return $this->type;
		}
		
		/**
		 * @return mixed
		 */
		public function getSha1() {
			return $this->sha1;
		}
		
		/**
		 * @return mixed
		 */
		public function getSize() {
			return $this->size;
		}
		
		/**
		 * @return mixed
		 */
		public function getAction() {
			return $this->action;
		}
		
		/**
		 * @return mixed
		 */
		public function getUploadedTimestamp() {
			return $this->uploadedTimestamp;
		}
		
		/**
		 * @param $options
		 * @return bool|\Psr\Http\Message\StreamInterface
		 * @throws \Exception
		 */
		public function download($options = []) {
			$url = $this->bucket->getClient()->urlForEndpoint('b2_download_file_by_id');
			
			$sink = isset($options['SaveAs']) ? $options['SaveAs'] : null;
			unset($options['SaveAs']);
			
			$options = array_merge($options, [
				'headers' => [
					'Authorization' => $this->bucket->getClient()->getAuthorizationToken(),
				],
				'sink'    => $sink,
				'json'    => [
					'fileId' => $this->id,
				],
			]);
			
			$response = $this->bucket->getClient()->getHttpClient()->request('GET', $url, $options, false);
			
			return !empty($sink) ? true : $response;
		}
		
		/**
		 * @param array $options
		 * @return string
		 * @throws \Exception
		 */
		public function getDownloadAuthorization($options = []) {
			$url = $this->bucket->getClient()->urlForEndpoint('b2_get_download_authorization');
			
			$options = array_merge([
									   'validDurationInSeconds' => 86400,
								   ], $options);
			
			$response = $this->bucket->getClient()->getHttpClient()->request('POST', $url, [
				'headers' => [
					'Authorization' => $this->bucket->getClient()->getAuthorizationToken(),
				],
				'json'    => [
					'bucketId'               => $this->bucket->getId(),
					'validDurationInSeconds' => $options['validDurationInSeconds'],
					'fileNamePrefix'         => $this->getName(),
				],
			]);
			
			return $response['authorizationToken'];
		}
		
		/**
		 * @throws \Exception
		 */
		public function delete() {
			$url = $this->bucket->getClient()->urlForEndpoint('b2_delete_file_version');
			
			$this->bucket->getClient()->getHttpClient()->request('POST', $url, [
				'headers' => [
					'Authorization' => $this->bucket->getClient()->getAuthorizationToken(),
				],
				'json'    => [
					'fileName' => $this->name,
					'fileId'   => $this->id,
				],
			]);
			
			return true;
		}
	}
