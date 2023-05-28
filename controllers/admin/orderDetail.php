<?php

//注文詳細表示プログラム

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

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_ADMIN);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

//注文番号
$order_no = (isset($_GET['order_no']) === true) ? $_GET['order_no'] : '';
//注文詳細取得
$orderData = $ord->getOrderData($order_no);
//お届け先の取得
$deliveryAddress = $user->getUserDeliveryAddress($orderData[0]['user_id']);
//注文詳細を取得
$orderDetailData = $ord->getOrderDetailData($order_no);

$context = [];

$context['order_no'] = $order_no;
$context['orderData'] = $orderData;
$context['deliveryAddress'] = $deliveryAddress;
$context['orderDetailData'] = $orderDetailData;

//twigファイルを呼び出し
$template = $twig->loadTemplate('order_detail.html.twig');
//$contextデータをtwigファイルで使えるようにする
$template->display($context);