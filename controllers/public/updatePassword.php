<?php

//パスワードの変更画面表示プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\PDODatabase;
use models\Bootstrap;
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
$errArr = [];

if(isset($_POST['reset']) === true) //再設定ボタンがクリックされたら
{
  unset($_POST['reset']);
  $dataArr = $_POST;

  //パスワード再設定のエラーチェック
  $errArr = $user->passwordErrorCheck($dataArr);

  if(count($errArr) === 0)
  {
    if($user_id !== '')
    {
      $dataArr['user_id'] = $user_id;

      //user_idの登録されているパスワードを取得
      $oldPassword = $user->getPassword($dataArr['user_id']);
      //oldPasswordと入力されたパスワードのチェック
      $res = $user->oldPasswordCheck($dataArr, $oldPassword);
      if($res === true) //パスワードが一致したら
      {
        //パスワードをアップデート
        $res = $user->updatePassword($dataArr);
        if($res === true)
        {
          //アップデート成功したらログアウトしてパスワードの再設定完了ページにリダイレクト
          $log->logout();
          header('Location:' . Bootstrap::ENTRY_URL . 'public/updatePasswordComplete.php');
          exit();
        }else
        {
          //登録失敗時は再設定画面に戻る
          $template = 'update_password.html.twig';
          $errArr = $user->passwordErrorCheck($dataArr);
        }
      }
    }
  }
}

$context = [];
$context['errArr'] = $errArr;
$context['user_id'] = $user_id;

$template = $twig->loadTemplate('update_password.html.twig');
$template->display($context);
