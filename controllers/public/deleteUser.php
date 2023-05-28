<?php

//会員登録退会プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
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

//ユーザーの登録情報取得
$dataArr = $user->getUserData($user_id);

//退会ボタンを押した場合
if(isset($_POST['delete']))
{
  //会員登録情報の削除
  $res = $user->deleteMember($user_id);
  if($res === true)
  {
    //ログアウトする
    $log->logout();
    //退会完了画面にリダイレクト
    header('Location:' . Bootstrap::ENTRY_URL . 'public/deleteUserComplete.php');
    exit;
  }else
  {
    //失敗したら退会画面にリダイレクト
    header('Location:' . Bootstrap::ENTRY_URL . 'public/deleteUser.php');
    exit;
  }
}

$context = [];
$context['dataArr'] = $dataArr[0];
$context['user_id'] = $user_id;

$template = $twig->loadTemplate('delete_user.html.twig');
$template->display($context);
