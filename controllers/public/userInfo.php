<?php

//商品一覧を表示するプログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\UserLogin;

//失敗したらfalseがかえる
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$rog = new UserLogin($db);
//$dbを引数を渡すことでPSODatabaseクラスをItemクラスで使えることができる

//テンプレート指定 twigのテンプレートファイルを読み込むための指定
$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';

$context = [];
$context['user_id'] = $user_id;

$template = $twig->loadTemplate('user_info.html.twig');
$template->display($context);

