<?php

//ユーザーログイン画面表示プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\PDODatabase;
use models\Bootstrap;
use models\UserLogin;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$log = new UserLogin($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$errArr = [];

//ログイン
if(isset($_POST['login']) === true)
{
  unset($_POST['login']);
  $dataArr['email'] = $_POST['email'];
  $dataArr['password'] = $_POST['password'];

  //メールアドレスとパスワードのエラーチェック
  $errArr = $log->errCheck($dataArr);

  if(count($errArr) === 0) //エラーがなければ
  {
    //入力されたemailから一致するemail,passを取得
    $userData = $log->getUserData();
    //ログインのチェック
    $errArr = $log->loginCheck($userData);

    if(count($errArr) === 0) //ログイン入力情報にエラーがなければ
    {
      //リキャプチャのチェックをして商品一覧画面へリダイレクト
      $recaptchaResponse = $_POST["recaptchaResponse"];
      $errArr = $log->recapchaCheck($recaptchaResponse, $userData);
    }
  }
}

//ログアウト
if(isset($_GET['logout']) === true)
{
  $log->logout();
  header('Location:' . Bootstrap::ENTRY_URL . 'public/userLogin.php');
  exit;
}

$context = [];
$context['errArr'] = $errArr;

$template = $twig->loadTemplate('user_login.html.twig');
$template->display($context);


