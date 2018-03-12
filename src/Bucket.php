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
		 * @return null|File
		 * @throws \Exception
		 */
		public function getFileByName($name) {
			if (empty($this->files))
				$this->listFileNames();
			
			foreach ($this->files as $file) {
				if ($file->getName() === $name)
					return $file;
			}
			
			return null;
		}
	}
