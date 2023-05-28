<?php

//決済処理プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';
require_once dirname(__FILE__) . '/../../vendor/payjp/payjp-php/init.php';

use models\Bootstrap;
use models\PDODatabase;
use models\UserLogin;
use models\Cart;
use models\Order;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$log = new UserLogin($db);
$cart = new Cart($db);
$order = new Order($db);

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';

if($user_id !== '')
{
  if(isset($_POST['order']))
  {
    $payment_id = filter_input(INPUT_POST, 'payment_id');
    $total_quantity = filter_input(INPUT_POST, 'totalQuantity');
    $total_amount = filter_input(INPUT_POST, 'totalAmount');
    $total_payment_amount = filter_input(INPUT_POST, 'totalPaymentAmount');
    $sku_code = filter_input(INPUT_POST, 'sku_code', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    $unit_price = filter_input(INPUT_POST, 'unit_price', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    $payjp_token = filter_input(INPUT_POST, 'payjp-token');

    //クレジットカード決済のトークンが空の場合
    if($payment_id == 1 && $payjp_token === '')
    {
      header('Location:' . Bootstrap::ENTRY_URL . 'public/cashRegister.php?card_error=error');
      exit;
    }elseif($payment_id == 1)
    {
      $db->beginTransaction();
      try{
        //Payjpライブラリ 決済情報反映
        \Payjp\Payjp::setApiKey("sk_test_510d8dcd78d99362616a24fc");
        $charge = \Payjp\Charge::create(array(
          'card' => $payjp_token,
          'amount' => $total_payment_amount,
          'currency' => 'jpy'
        ));
        //orderDetailDataArrを作成
        $orderDetailDataArr = $order->createOrderDetailDataArr($sku_code, $quantity, $unit_price);
        //order_noを作成
        $order_no = $order->createOrderNumber();
        //ordersテーブルに注文内容挿入
        $order->insOrder($order_no, $user_id, $payment_id, $total_quantity, $total_amount, $total_payment_amount);
        //order_detailsテーブルに注文詳細挿入
        $order->insOrderDetail($order_no, $orderDetailDataArr);
        //$user_idのカート内商品削除
        $cart->deleteUserCartData($user_id);
  
        $db->commit();
        //購入完了画面にリダイレクト
        header('Location:' . Bootstrap::ENTRY_URL . 'public/orderComplete.php');
        exit;
        
      }catch(\PDOException $e)
      {
        $db->rollback();
        //エラー画面にリダイレクト
        header('Location:' . Bootstrap::ENTRY_URL . 'public/error.php');
        exit;
      }
    }elseif($payment_id == 2 || $payment_id == 3) //お支払い方法が代引きか銀行振り込みの場合
    {
      $db->beginTransaction();
      try{
        //orderDetailDataArrを作成
        $orderDetailDataArr = $order->createOrderDetailDataArr($sku_code, $quantity, $unit_price);
        //order_noを作成
        $order_no = $order->createOrderNumber();
        //ordersテーブルに注文内容挿入
        $order->insOrder($order_no, $user_id, $payment_id, $total_quantity, $total_amount, $total_payment_amount);
        //order_detailsテーブルに注文詳細挿入
        $order->insOrderDetail($order_no, $orderDetailDataArr);
        //$user_idのカート内商品削除
        $cart->deleteUserCartData($user_id);
  
        $db->commit();
        //購入完了画面にリダイレクト
        header('Location:' . Bootstrap::ENTRY_URL . 'public/orderComplete.php');
        exit;
        
      }catch(\PDOException $e)
      {
        $db->rollback();
        //エラー画面にリダイレクト
        header('Location:' . Bootstrap::ENTRY_URL . 'public/error.php');
        exit;
      }
    }
  }
}else{
  header('Location:' . Bootstrap::ENTRY_URL . 'public/userLogin.php');
  exit;
}

