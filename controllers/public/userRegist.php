<?php

//新規ユーザー登録画面表示プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\InitMaster;
use models\User;


$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

//初期データを設定
$dataArr = [
  'full_name' => '',
  'full_name_kana' => '',
  'sex' => '',
  'year' => '',
  'month' => '',
  'day' => '',
  'zip1' => '',
  'zip2' => '',
  'address' => '',
  'email' => '',
  'phone_number' => '',
  'password' => '',
];

//エラーメッセージの定義、初期
$errArr = [];
foreach($dataArr as $key => $value)
{
  $errArr[$key] = '';
}

//生年月日の生成
list($yearArr, $monthArr, $dayArr) = initMaster::getDate();
$sexArr = initMaster::getSex();

$context = [];

$context['yearArr'] = $yearArr;
$context['monthArr'] = $monthArr;
$context['dayArr'] = $dayArr;
$context['sexArr'] = $sexArr;
$context['dataArr'] = $dataArr;
$context['errArr'] = $errArr;

$template = $twig->loadTemplate('user_regist.html.twig');
$template->display($context);
