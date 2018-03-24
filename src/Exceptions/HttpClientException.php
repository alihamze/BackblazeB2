<?php
	/**
	 * Created by PhpStorm.
	 * User: alihamze
	 * Date: 3/24/18
	 * Time: 3:15 PM
	 */
	
	namespace TechYet\B2\Exceptions;
	
	
	class HttpClientException extends \Exception {
		const TOO_MANY_RETRIES = 1000;
		const UNHANDLED_HTTP_ERROR = 1001;
	}
