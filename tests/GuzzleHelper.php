<?php
	/**
	 * Created by PhpStorm.
	 * User: alihamze
	 * Date: 3/12/18
	 * Time: 7:02 PM
	 */
	
	namespace TechYet\B2\Tests;
	
	
	use GuzzleHttp\Handler\MockHandler;
	use GuzzleHttp\HandlerStack;
	use GuzzleHttp\Psr7\Response;
	use TechYet\B2\HTTP\Client;
	
	trait GuzzleHelper {
		protected function responseFromFile($file, $statusCode, $headers = []) {
			$response = file_get_contents(__DIR__ . '/responses/' . $file);
			
			return new Response($statusCode, $headers, $response);
		}
		
		protected function buildGuzzleFromResponses(array $responses, $history = null) {
			$mock = new MockHandler($responses);
			$handler = new HandlerStack($mock);
			if ($history) {
				$handler->push($history);
			}
			
			return new Client(['handler' => $handler]);
		}
	}
