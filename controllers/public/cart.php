<?php

//カート内処理プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\UserLogin;
use models\Cart;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$log = new UserLogin($db);
$cart = new Cart($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';

//数量変更したら渡される
$sku_code = (isset($_GET['sku_code']) === true) ? $_GET['sku_code'] : '';
$quantity = (isset($_GET['quantity']) === true && preg_match('/^\d+$/', $_GET['quantity']) === 1) ? $_GET['quantity'] : '';
//カートから削除したら渡される
$crt_id = (isset($_GET['crt_id']) === true && preg_match('/^\d+$/', $_GET['crt_id']) === 1) ? $_GET['crt_id'] : '';

if($user_id !== '')
{
  //カートへ入れるボタンが押されたら
  if(isset($_POST['cart_in']))
  {
    $item = $_POST['item'];
    //カートに$item_idがあれば取得
    $userCartItem = $cart->getCartItem($user_id, $item['sku_code']);
    if($userCartItem === [])
    {
      //カートに登録する
      $res = $cart->insCartData($user_id, $item['item_id'], $item['sku_code']);
    }else
    {
      //カートに$item_idの数量を1足す
      $res = $cart->updateItemQuantity($userCartItem[0]['quantity'], $user_id, $item['sku_code']);
    }
    if($res === false)
    {
      //登録に失敗した場合、エラーページを表示する
      header('Location:' . Bootstrap::ENTRY_URL . 'public/error.php');
      exit;
    }
    //カートページにリダイレクト
    header('Location:' . Bootstrap::ENTRY_URL . 'public/cart.php');
    exit();
  }
  
  //数量変更したとき、sku_codeとquantityが設定されていたら数量変更
  if($sku_code !== '' && $quantity !== '')
  {
    //数量を変更する
    $res = $cart->updateItemQuantity2($quantity, $user_id, $sku_code);
    
    if($res === false)
    {
      //登録に失敗した場合、エラーページを表示する
      header('Location:' . Bootstrap::ENTRY_URL . 'public/error.php');
      exit;
    }
    header('Location:' . Bootstrap::ENTRY_URL . 'public/cart.php');
    exit();
  }
  
  //crt_idが設定されていれば、商品を削除する
  if($crt_id !== '')
  {
    // $crt_idのflgを0から1に変えるupdateの処理
    $res = $cart->deleteCartData($crt_id);
  }
  //$user_idのカート情報取得
  $dataArr = $cart->getCartData($user_id);
  //アイテム数と金額を取得する
  $res = $cart->getItemQuantityAndPrice($user_id);
  //アイテム数と金額の合計を計算
  list($totalAmount, $totalQuantity) = $cart->totalQuantityAndPrice($res);
  //カートの数量変更ボタン作成
  $quantityArr = $cart->getCartQuantity();
}else{
  //user_idが設定されていなければログイン画面にリダイレクト
  header('Location:' . Bootstrap::ENTRY_URL . 'public/userLogin.php');
  exit;
}


$context = [];
$context['totalQuantity'] = $totalQuantity;
$context['totalAmount'] = $totalAmount;
$context['quantityArr'] = $quantityArr;
$context['dataArr'] = $dataArr;
$context['user_id'] = $user_id;

$template = $twig->loadTemplate('cart.html.twig');
$template->display($context);
