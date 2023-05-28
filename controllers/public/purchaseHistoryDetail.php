<?php

//購入履歴詳細画面表示プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\UserLogin;
use models\Order;
use models\User;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$log = new UserLogin($db);
$ord = new Order($db);
$user = new User($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
$order_no = (isset($_GET['order_no']) === true) ? $_GET['order_no'] : '';

//$order_noの支払い方法の取得
$orderData = $ord->getOrderData($order_no);
//$user_idの住所を取得
$deliveryAddress = $user->getUserDeliveryAddress($user_id);
//$order_noの注文商品の取得
$orderDetailData = $ord->getOrderDetailData($order_no);

$context = [];
$context['user_id'] = $user_id;
$context['order_no'] = $order_no;
$context['orderData'] = $orderData;
$context['deliveryAddress'] = $deliveryAddress;
$context['orderDetailData'] = $orderDetailData;


$template = $twig->loadTemplate('purchase_history_detail.html.twig');
$template->display($context);