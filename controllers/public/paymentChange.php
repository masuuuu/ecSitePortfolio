<?php

//お支払い方法変更の表示プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\UserLogin;
use models\Cart;
use models\Order;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$log = new UserLogin($db);
$cart = new Cart($db);
$order = new Order($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';

$payment_error = '';

if($user_id !== '')
{
  
  if(isset($_GET['pay'])) //お支払い方法の変更をクリックされたら
  {
    //お支払い方法取得
    $paymentArr = $order->getPaymentList();
  }
  
  if(isset($_GET['pay_ch'])) //このお支払い方法を使うをクリックされたら
  {
    if(!isset($_GET['payment'])) //お支払い方法選択されていない場合
    {
      $payment_error = 'お支払い方法を選択してください';
      //お支払い方法の取得
      $paymentArr = $order->getPaymentList();
    }else{
      //選択されたお支払い方法のパラメータをつけて購入画面にリダイレクトする
      $payment_id = $_GET['payment'];
      header('Location:' . Bootstrap::ENTRY_URL . 'public/cashRegister.php?payment_id=' . $payment_id);
      exit;
    }
  }
}else{
  header('Location:' . Bootstrap::ENTRY_URL . 'public/userLogin.php');
  exit;
}

$context = [];
$context['paymentArr'] = $paymentArr;
$context['payment_error'] = $payment_error;
$context['user_id'] = $user_id;

$template = $twig->loadTemplate('payment_change.html.twig');
$template->display($context);
