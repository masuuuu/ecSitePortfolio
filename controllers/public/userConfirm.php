<?php

//ユーザー登録確認処理プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\PDODatabase;
use models\Bootstrap;
use models\InitMaster;
use models\User;

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User($db);

//登録確認をクリックした場合
if(isset($_POST['confirm']) === true)
{
  unset($_POST['confirm']);
  $dataArr = $_POST;
  //この値を入れないでPOSTするとUndefinedとなるので未定義の場合は空白状態としてセットしておく
  if(isset($_POST['sex']) === false)
  {
    $dataArr['sex'] = '';
  }
  //入力情報のエラーチェック
  $errArr = $user->errorCheck($dataArr);
  //エラーがあるかチェック
  $err_check = $user->getErrorFlg();
  $template = ($err_check === true) ? 'user_confirm.html.twig' : 'user_regist.html.twig';  
}

//戻るをクリックした場合
if(isset($_POST['back']) === true)
{
  $dataArr = $_POST;
  unset($dataArr['back']);

  //エラー配列作成
  $errArr = $user->createErrorArr($dataArr);
  $template = 'user_regist.html.twig';
}

//登録完了をクリックした場合
if(isset($_POST['complete']) === true)
{
  $dataArr = $_POST;
  unset($dataArr['complete']);

  //ユーザー情報を登録
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

list($yearArr, $monthArr, $dayArr) = initMaster::getDate();
$sexArr = initMaster::getSex();

$context['sexArr'] = $sexArr;
$context['yearArr'] = $yearArr;
$context['monthArr'] = $monthArr;
$context['dayArr'] = $dayArr;
$context['dataArr'] = $dataArr;
$context['errArr'] = $errArr;

$template = $twig->loadTemplate($template);
$template->display($context);
