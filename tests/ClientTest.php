<?php
	/**
	 * Created by PhpStorm.
	 * User: alihamze
	 * Date: 3/12/18
	 * Time: 7:01 PM
	 */
	
	namespace TechYet\B2\Tests;
	
	
	use PHPUnit\Framework\TestCase;
	use TechYet\B2\Bucket;
	use TechYet\B2\Client;
	
	class ClientTest extends TestCase {
		use GuzzleHelper;
		
		public function testListBuckets() {
			$guzzle = $this->buildGuzzleFromResponses([
														  $this->responseFromFile('authorize_account.json', 200),
														  $this->responseFromFile('list_buckets.json', 200),
													  ]);
			$client = new Client('testId', 'testKey', ['client' => $guzzle]);
			
			/** @noinspection PhpUnhandledExceptionInspection */
			$buckets = $client->listBuckets();
			
			$this->assertEquals(3, count($buckets), 'Did not get the expected 3 buckets');
			$this->assertTrue(isset($buckets['Kitten-Videos']), 'The first bucket name did not match');
			$this->assertEquals(Bucket::TYPE_PRIVATE, $buckets['Kitten-Videos']->getType(),
								'The first bucket should be private');
			$this->assertTrue(isset($buckets['Puppy-Videos']), 'The second bucket name did not match');
			$this->assertEquals(Bucket::TYPE_PUBLIC, $buckets['Puppy-Videos']->getType(),
								'The second bucket should be public');
			$this->assertTrue(isset($buckets['Vacation-Pictures']), 'The third bucket name did not match');
			$this->assertEquals(Bucket::TYPE_PRIVATE, $buckets['Vacation-Pictures']->getType(),
								'The third bucket should be private');
		}
	}
