<?php

//登録内容修正の処理確認画面表示プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\PDODatabase;
use models\Bootstrap;
use models\InitMaster;
use models\User;
use models\UserLogin;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User($db);
$log = new UserLogin($db);

//テンプレート指定
$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

//ユーザーID
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';

if(isset($_POST['confirm']) === true)
{
  unset($_POST['confirm']);
  $dataArr = $_POST;
  
  //エラーメッセージの配列作成
  $errArr = $user->errorCheck2($dataArr);
  $errFlag = $user->getErrorFlg($errArr);

  $template = ($errFlag === true) ? 'update_user_confirm.html.twig' : 'update_user.html.twig';
}


if(isset($_POST['back']) === true)
{
  $dataArr = $_POST;
  unset($dataArr['back']);

  $errArr = $user->createErrorArr($dataArr);
  $template = 'update_user.html.twig';
}

if(isset($_POST['complete']) === true)
{
  $dataArr = $_POST;
  unset($dataArr['complete']);

  $res = $user->insUpdateData($dataArr);

  if($res === true)
  {
    //登録成功時は完了ページへ
    header('Location:' . Bootstrap::ENTRY_URL . 'public/updateUserComplete.php');
    exit();
  }else{
    //登録失敗時は登録画面に戻る
    $template = 'update_user.html.twig';
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
$context['user_id'] = $user_id;

$template = $twig->loadTemplate($template);
$template->display($context);
