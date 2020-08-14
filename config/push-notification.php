<?php

return array(

	'appNameIOS' => array(
		'environment' => 'development',
		'certificate' => '/path/to/certificate.pem',
		'passPhrase' => 'password',
		'service' => 'apns',
	),
	'appNameAndroid' => array(
		'environment' => 'development',
		'apiKey' => env('server_key'),
		'service' => 'gcm',
	),

);