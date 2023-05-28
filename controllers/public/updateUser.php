<?php

//登録情報の変更画面表示プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\InitMaster;
use models\User;
use models\UserLogin;


$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User($db);
$log = new UserLogin($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';

$dataArr = $user->getUserData($user_id);
$dataArr['user_id'] = $user_id;

// //エラーメッセージの定義、初期
$errArr = [];
$errArr = $user->createErrorArr($dataArr);

list($yearArr, $monthArr, $dayArr) = initMaster::getDate();

$sexArr = initMaster::getSex();

$context = [];

$context['yearArr'] = $yearArr;
$context['monthArr'] = $monthArr;
$context['dayArr'] = $dayArr;
$context['sexArr'] = $sexArr;
$context['dataArr'] = $dataArr[0];
$context['errArr'] = $errArr;
$context['user_id'] = $user_id;

$template = $twig->loadTemplate('update_user.html.twig');
$template->display($context);
