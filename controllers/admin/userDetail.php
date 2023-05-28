<?php

//登録ユーザー詳細表示プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\InitMaster;
use models\User;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User($db);

//テンプレート指定
$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_ADMIN);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$user_id = (isset($_GET['user_id']) === true && preg_match('/^[0-9]+$/', $_GET['user_id']) === 1) ? $_GET['user_id'] : '';

if(isset($_POST['confirm']) === true)
{
  unset($_POST['confirm']);
  unset($_POST['entry_url']);

  $dataArr = $_POST;

  //エラーメッセージの配列作成
  $errArr = $user->errorCheck2($dataArr);
  $errFlag = $user->getErrorFlg($errArr);

  if($errFlag === true)
  {
    $res = $user->insUpdateData($dataArr);
    if($res === true)
    {
      //登録成功時は完了ページへ
      header('Location:' . Bootstrap::ENTRY_URL . 'admin/userDetail.php?user_id='.$user_id);
      exit;
    }else{
      //登録失敗時は登録画面に戻る
      $errArr = $user->createErrorArr($dataArr);
    }
  }
}

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

//twigファイルを呼び出し
$template = $twig->loadTemplate('user_detail.html.twig');
//$contextデータをtwigファイルで使えるようにする
$template->display($context);
