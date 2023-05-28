<?php

//パスワードリマインドの受付処理プログラム

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

//送信ボタンがクリックされた場合
if(isset($_POST['send']) === true)
{
  //セッションのトークンとポストされたトークンが一致していたら
  if($_SESSION['token'] === $_POST['token'])
  {
    $email = $_POST['email'];
    //パスワード再設定送信メールアドレスエラーチェック
    $error = $user->resetPasswordMailErrorCheck($email);

   
    if(isset($error) === true){
      //メールアドレスにエラーがある場合、トークン作成してパスワードリマインドページにリダイレクト
      $token = $userlog->createToken();
      $template = 'password_reset_request_form.html.twig';
    }else{ 
      //エラーがなければ、送信された$emailがuserテーブルに登録されているか確認
      $res = $user->getMailList3($email);
      if($res !== false)
      {
        //$emailがpassword_resetsテーブルにあるか確認
        $res = $user->resetPasswordUserCheck($email);
        if($res === false)
        {
          //$emailがなければ挿入
          $user->insResetPasswordRequestTransaction($email);
        }else{
          //$emailがあればアップデート
          $user->updateResetPasswordRequestTransaction($email);
        }
        header('location:' . Bootstrap::ENTRY_URL . 'public/passwordResetRequestSent.php');
      }else{
        //$emailがuserテーブルに未登録の場合は送信完了画面を表示させるだけ
        header('location:' . Bootstrap::ENTRY_URL . 'public/passwordResetRequestSent.php');
      }
    }
  }else{
    //セッションのトークンとポストされたトークンが一致していない場合
    exit('不正なリクエストです');
  }
}

$context = [];

$context['email'] = $email;
$context['error'] = $error;
$context['user_id'] = $user_id;
$context['token'] = $token;

$template = $twig->loadTemplate($template);
$template->display($context);
