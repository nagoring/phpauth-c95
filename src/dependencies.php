<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};


// pdo
$container['pdo'] = function ($c) {
	try{
		$username = 'root';
		$password = '';
		$database = 'skysemi_phpauth_c95';
		$host = 'localhost';
		$pdo = new \PDO("mysql:dbname={$database};host={$host};charset=utf8mb4", $username, $password);
		return $pdo;

	}catch(\Throwable $ex){
	    echo $ex->getMessage();
	}

};

$container['auth'] = function ($c){
	$auth = new \Delight\Auth\Auth($c['pdo']);
	return $auth;
};

$container['SITE_URL'] = 'http://phpauth-c95.test';