<?php

//エラー表示プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\UserLogin;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$log = new UserLogin($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';

$context = [];
$context['user_id'] = $user_id;

$template = $twig->loadTemplate('error.html.twig');
$template->display($context);