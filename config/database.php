<?php

return [

	'default' => env('DB_CONNECTION', 'mysql'),

	'connections' => [

		'sqlite' => [
			'driver' => 'sqlite',
			'database' => env('DB_DATABASE', database_path('database.sqlite')),
			'prefix' => '',
		],

		'mysql' => [
			'driver' => 'mysql',
			'host' => env('DB_HOST', '127.0.0.1'),
			'port' => env('DB_PORT', '3306'),
			'database' => env('DB_DATABASE', 'fleet3'),
			'username' => env('DB_USERNAME', 'root'),
			'password' => env('DB_PASSWORD', 'root'),
			'unix_socket' => env('DB_SOCKET', ''),
			'charset' => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix' => '',
			'strict' => false,
			'engine' => null,
		],

		'pgsql' => [
			'driver' => 'pgsql',
			'host' => env('DB_HOST', '127.0.0.1'),
			'port' => env('DB_PORT', '5432'),
			'database' => env('DB_DATABASE', 'forge'),
			'username' => env('DB_USERNAME', 'forge'),
			'password' => env('DB_PASSWORD', ''),
			'charset' => 'utf8',
			'prefix' => '',
			'schema' => 'public',
			'sslmode' => 'prefer',
		],

	],

	'migrations' => 'migrations',

	'redis' => [

		'client' => 'predis',

		'default' => [
			'host' => env('REDIS_HOST', '127.0.0.1'),
			'password' => env('REDIS_PASSWORD', null),
			'port' => env('REDIS_PORT', 6379),
			'database' => 0,
		],

	],

];