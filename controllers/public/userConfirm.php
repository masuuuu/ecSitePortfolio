<?php

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\PDODatabase;
use models\Bootstrap;
use models\InitMaster;
use models\User;

//テンプレート指定
$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User($db);

if(isset($_POST['confirm']) === true)
{
  unset($_POST['confirm']);
  $dataArr = $_POST;
  //この値を入れないでPOSTするとUndefinedとなるので未定義の場合は空白状態としてセットしておく
  if(isset($_POST['sex']) === false)
  {
    $dataArr['sex'] = '';
  }
  //エラーメッセージの配列作成
  $errArr = $user->errorCheck($dataArr);
  $err_check = $user->getErrorFlg();

  $template = ($err_check === true) ? 'user_confirm.html.twig' : 'user_regist.html.twig';  
}

if(isset($_POST['back']) === true)
{
  $dataArr = $_POST;
  unset($dataArr['back']);

  $errArr = $user->createErrorArr($dataArr);
  $template = 'user_regist.html.twig';
}

if(isset($_POST['complete']) === true)
{
  $dataArr = $_POST;
  unset($dataArr['complete']);

  $res = $user->insRegistData($dataArr);

  if($res === true)
  {
    //登録成功時は完成ページへ
    header('Location:' . Bootstrap::ENTRY_URL . 'public/userComplete.php');
    exit();
  }else{
    //登録失敗時は登録画面に戻る
    $template = 'user_regist.html.twig';
    $errArr = $user->createErrorArr($dataArr);
  }
}


$sexArr = initMaster::getSex();

$context['sexArr'] = $sexArr;

list($yearArr, $monthArr, $dayArr) = initMaster::getDate();

$context['yearArr'] = $yearArr;
$context['monthArr'] = $monthArr;
$context['dayArr'] = $dayArr;

$context['dataArr'] = $dataArr;
$context['errArr'] = $errArr;

$template = $twig->loadTemplate($template);
$template->display($context);
