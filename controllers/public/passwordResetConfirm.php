<?php

//パスワード再設定の確認処理プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\User;
use models\UserLogin;


$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User($db);
$userlog = new UserLogin($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
//パスワード再設定用トークン
$passwordResetToken = filter_input(INPUT_POST, 'passwordResetToken');

//送信ボタンがおされた場合
if(isset($_POST['send']) === true)
{

  if($_SESSION['token'] === $_POST['token']) //セッションのトークンとポストされたトークンが一致した場合
  {
    $password = filter_input(INPUT_POST, 'password');
    $password_con = filter_input(INPUT_POST, 'password_con');
    
    //パスワード再設定用のパスワードエラーチェック
    $errArr = $user->resetPasswordErrorCheck($password, $password_con);
    
    if(count($errArr) === 0) //エラーがなければ
    {
      //パスワードのアップデート
      $user->updateResetPasswordTransaction($password, $passwordResetToken);
      header('location:' . Bootstrap::ENTRY_URL . 'public/passwordResetComplete.php');
    }else
      {
        //エラーの場合パスワード再設定フォーム画面を表示
        $token = $userlog->createToken();
        $template = 'password_reset_form.html.twig';
      }
    }else{
      //セッションのトークンとポストされたトークンが一致しない場合
      exit('不正なリクエストです');
  }
}


$context = [];

$context['errArr'] = $errArr;
$context['user_id'] = $user_id;
$context['token'] = $token;
$context['passwordResetToken'] = $passwordResetToken;

$template = $twig->loadTemplate($template);
$template->display($context);
