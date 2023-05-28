<?php

//パスワードリマインドのメールアドレスフォーム画面プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\User;
use models\UserLogin;


$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User($db);
$userlog = new UserLogin($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';

$email = '';
$error = '';

//トークン作成
$token = $userlog->createToken();

$context = [];

$context['email'] = $email;
$context['error'] = $error;
$context['user_id'] = $user_id;
$context['token'] = $token;

$template = $twig->loadTemplate('password_reset_request_form.html.twig');
$template->display($context);
