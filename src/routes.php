<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
$app->get('/hello', function (Request $request, Response $response, array $args) {
	var_dump($this->pdo);

	return $this->renderer->render($response, 'index.phtml', $args);
//	return $this->renderer->render($response, 'index.phtml', $args);
});
$app->get('/', function (Request $request, Response $response, array $args) {
	return $this->renderer->render($response, 'index.phtml', $args);
});
$app->get('/login', function (Request $request, Response $response, array $args) {
	return $this->renderer->render($response, 'login.phtml', $args);
});
$app->post('/login', function (Request $request, Response $response, array $args) {
	//認証

	exit;
});
$app->post('/store', function (Request $request, Response $response, array $args) {
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		echo "Invalid request method";
		exit;
	}
	try {
		$auth = new \Delight\Auth\Auth($this->pdo);
		$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
		$password = filter_input(INPUT_POST, 'password');

		$userId = $auth->register($email, $password, null, function ($selector, $token) use($email) {
			mb_language("japanese");
			mb_internal_encoding("UTF-8");
			$to = $email;
			$url = 'http://localhost:8080/verify_email?selector=' . \urlencode($selector) . '&token=' . \urlencode($token);

			$subject = "会員登録認証";
			$body = "認証URLです。¥n{$url}";
			$from = "nagomi.github@gmail.com";
			\mb_send_mail($to,$subject,$body,"From:".$from);
			header("Location: http://localhost:8080/login");
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
//	redirecct();
//	return $this->renderer->render($response, 'index.phtml', $args);
});
$app->get('/verify_email', function (Request $request, Response $response, array $args) {
	$query = $request->getQueryParams();
	$selector = $query['selector'];
	$token = $query['token'];
	$auth = new \Delight\Auth\Auth($this->pdo);
	$auth->confirmEmail($selector, $token);
	var_dump($query);
	exit;

});

//$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
//    // Sample log message
//    $this->logger->info("Slim-Skeleton '/' route");
//
//    // Render index view
//    return $this->renderer->render($response, 'index.phtml', $args);
//});
