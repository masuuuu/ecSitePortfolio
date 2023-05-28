<?php

//お問い合わせの確認プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\PDODatabase;
use models\Bootstrap;
use models\Contact;
use models\UserLogin;

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$con = new Contact($db);
$log = new UserLogin($db);

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';

//お問い合わせが送信された場合
if(isset($_POST['confirm']) === true)
{
  unset($_POST['confirm']);
  $dataArr = $_POST;

  //エラーメッセージの配列作成
  $errArr = $con->contactErrorCheck($dataArr);
  //エラーの確認
  $err_check = $con->getErrorFlg();
  
  
  if($err_check === true)
  {
    //エラーがない場合お問い合わせ確認画面
    $token = $dataArr['token'];
    $template = 'contact_confirm.html.twig';
  }else
  {
    //エラーがある場合お問い合わせフォーム画面
    $token = $log->createToken();
    $template = 'contact.html.twig';  
  }
}

//お問い合わせ確認画面から戻るボタンを押した場合
if(isset($_POST['back']) === true)
{
  $dataArr = $_POST;
  unset($dataArr['back']);
  //エラーメッセージの配列作成
  $errArr = $con->createErrorArr($dataArr);
  //トークン作成
  $token = $log->createToken();
  $template = 'contact.html.twig';
}

//お問い合わせ送信ボタンをおした場合
if(isset($_POST['complete']) === true)
{
  //渡ってきたトークンとセッションのトークンが一致していた場合
  if($_SESSION['token'] === $_POST['token'])
  {
    $dataArr = $_POST;
    unset($dataArr['complete']);
    unset($dataArr['token']);

    //お問い合わせ内容データベースに挿入
    $res = $con->insContactData($dataArr);
    //最後に挿入されたお問い合わせID取得
    $contact_id = $db->getLastId();
    $contact_id = intval($contact_id);

    if($res === true)
    {
      //お問い合わせの内容取得
      $contactData = $con->getContactData($contact_id);
      //お問い合わせのメール送信
      $result = $con->mailSend($contactData);
      if($result === true)
      {
        //登録成功時は完成ページへ
        header('Location:' . Bootstrap::ENTRY_URL . 'public/contactComplete.php');
        exit();
      }else{
        header('Location:' . Bootstrap::ENTRY_URL . 'public/contactError.php');
        exit();
      }
      
    }else{
      //登録失敗時は登録画面に戻る
      $template = 'contact.html.twig';
      $errArr = $con->createErrorArr($dataArr);
    }
  }else
  {
    //トークンが一致しない場合お問い合わせフォームにリダイレクト
    header('Location:' . Bootstrap::ENTRY_URL . 'public/contact.php');
    exit();
  }
  
}

$context['dataArr'] = $dataArr;
$context['errArr'] = $errArr;
$context['token'] = $token;
$context['user_id'] = $user_id;

$template = $twig->loadTemplate($template);
$template->display($context);
