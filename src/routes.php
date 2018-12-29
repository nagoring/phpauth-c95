<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->group('/user', function() use($app){
	$app->get('/', function (Request $request, Response $response, array $args) {
		return $this->renderer->render($response, 'user/index.phtml', $args);
	});
})->add(function ($request, $response, $next) {
	$auth = new \Delight\Auth\Auth($this->pdo);
	if (!$auth->isLoggedIn()) {
		header("Location: {$this->SITE_URL}");
		exit;
	}
	$response = $next($request, $response);
	return $response;
});


$app->get('/', function (Request $request, Response $response, array $args) {
//	$auth = new \Delight\Auth\Auth($this->pdo);
	return $this->renderer->render($response, 'index.phtml', $args);
});
$app->get('/login', function (Request $request, Response $response, array $args) {
	return $this->renderer->render($response, 'login.phtml', $args);
});
$app->post('/login', function (Request $request, Response $response, array $args) {
	//認証
	$auth = new \Delight\Auth\Auth($this->pdo);
	$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
	$password = filter_input(INPUT_POST, 'password');
	$auth->login($email, $password);
	if ($auth->isLoggedIn()) {
		header("Location: {$this->SITE_URL}/user/");
		exit;
	}
	else {
		echo 'ログイン失敗';
	}
	exit;
});
$app->post('/store', function (Request $request, Response $response, array $args) {
	try {
		$auth = new \Delight\Auth\Auth($this->pdo);
		$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
		$password = filter_input(INPUT_POST, 'password');

		$userId = $auth->register($email, $password, null, function ($selector, $token) use($email) {
			mb_language("japanese");
			mb_internal_encoding("UTF-8");
			$to = $email;
			$url = $this->SITE_URL . '/verify_email?selector=' . \urlencode($selector) . '&token=' . \urlencode($token);

			$subject = "会員登録認証";
			$body = "認証URLです。¥n{$url}";
			$from = "nagomi.github@gmail.com";
			\mb_send_mail($to,$subject,$body,"From:".$from);
			header("Location: {$this->SITE_URL}/login");
			exit;
		});
	}
	catch (\Delight\Auth\InvalidEmailException $e) {
		die('Invalid email address');
	}
	catch (\Delight\Auth\InvalidPasswordException $e) {
		die('Invalid password');
	}
	catch (\Delight\Auth\UserAlreadyExistsException $e) {
		die('User already exists');
	}
	catch (\Delight\Auth\TooManyRequestsException $e) {
		die('Too many requests');
	}
	header("Location: {$this->SITE_URL}/login");
});
$app->get('/verify_email', function (Request $request, Response $response, array $args) {
	$query = $request->getQueryParams();
	$selector = $query['selector'];
	$token = $query['token'];
	$auth = new \Delight\Auth\Auth($this->pdo);
	$auth->confirmEmail($selector, $token);
	header("Location: {$this->SITE_URL}/login");
	exit;
});
$app->get('/logout', function (Request $request, Response $response, array $args) {
	$auth = new \Delight\Auth\Auth($this->pdo);
	$auth->logOut();
	$auth->destroySession();
	return $this->renderer->render($response, 'logout.phtml', $args);
});



//$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
//    // Sample log message
//    $this->logger->info("Slim-Skeleton '/' route");
//
//    // Render index view
//    return $this->renderer->render($response, 'index.phtml', $args);
//});
