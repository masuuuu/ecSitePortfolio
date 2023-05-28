<?php

//購入確認画面処理のプログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\UserLogin;
use models\User;
use models\Cart;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$log = new UserLogin($db);
$user = new User($db);
$cart = new Cart($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';

//支払い方法デフォルト2
$payment_id = 2;
//手数料の設定
$priceFee = 330;
$card_error = '';

if($user_id !== '')
{
  if(isset($_GET['reg']))
  {
    //お届け先情報取得
    $deliveryAddress = $user->getUserDeliveryAddress($user_id);
    //$user_idのカート情報取得
    $dataArr = $cart->getCartData($user_id);
    //アイテム数と金額を取得する
    $res = $cart->getItemQuantityAndPrice($user_id);
    //アイテム数と金額の合計を計算
    list($totalAmount, $totalQuantity) = $cart->totalQuantityAndPrice($res);
    $totalPaymentAmount = $totalAmount;

    //お支払い方法が代引きの場合
    if($payment_id == 2)
    {
      //代引き手数料を足す
      $totalPaymentAmount = $totalAmount + $priceFee;
    }
  }elseif(isset($_GET['payment_id'])) //支払い情報変更画面から来た場合
  {
    //お支払い方法設定
    $payment_id = $_GET['payment_id'];
    //お届け先情報取得
    $deliveryAddress = $user->getUserDeliveryAddress($user_id);
    //$user_idのカート情報取得
    $dataArr = $cart->getCartData($user_id);
    //アイテム数と金額を取得する
    $res = $cart->getItemQuantityAndPrice($user_id);
    //アイテム数と金額の合計を計算
    list($totalAmount, $totalQuantity) = $cart->totalQuantityAndPrice($res);
    $totalPaymentAmount = $totalAmount;
    //お支払い方法が代引きの場合
    if($payment_id == 2)
    {
      //代引き手数料を足す
      $totalPaymentAmount = $totalAmount + $priceFee;
    }
  }elseif(isset($_GET['card_error'])) //カード情報エラーの場合
  {
    $payment_id = 1;
    $card_error = 'カード情報を入力してください';
    //お届け先情報取得
    $deliveryAddress = $user->getUserDeliveryAddress($user_id);
    //$user_idのカート情報取得
    $dataArr = $cart->getCartData($user_id);
    //アイテム数と金額を取得する
    $res = $cart->getItemQuantityAndPrice($user_id);
    //アイテム数と金額の合計を計算
    list($totalAmount, $totalQuantity) = $cart->totalQuantityAndPrice($res);
    $totalPaymentAmount = $totalAmount;
  }
}else{
  //user_idが設定されていなければ、ログイン画面にリダイレクト
  header('Location:' . Bootstrap::ENTRY_URL . 'public/userLogin.php');
  exit;
}

$context = [];
$context['totalQuantity'] = $totalQuantity;
$context['totalAmount'] = $totalAmount;
$context['totalPaymentAmount'] = $totalPaymentAmount;
$context['priceFee'] = $priceFee;
$context['dataArr'] = $dataArr;
$context['deliveryAddress'] = $deliveryAddress;
$context['payment_id'] = $payment_id;
$context['user_id'] = $user_id;
$context['card_error'] = $card_error;

$template = $twig->loadTemplate('cash_register.html.twig');
$template->display($context);
