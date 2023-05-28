<?php

//購入履歴一覧表示のプログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\UserLogin;
use models\Order;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$log = new UserLogin($db);
$ord = new Order($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
//表示件数
$limit = (isset($_GET['limit']) === true) ? intval($_GET['limit']) : 10;
//表示順
$sort = (isset($_GET['sort']) === true) ? $_GET['sort'] : 'new';
//現在のページ
$now_page = (isset($_GET['page_id']) === true) ? $_GET['page_id'] : 1;

//表示順の設定
switch($sort)
{
  case 'new':
    $order = ' order_date DESC ';
  break;

  case 'old':
    $order = ' order_date ASC ';
  break;
}

$order_count = $ord->countOrderList($user_id);
$pages = ceil($order_count / $limit);
$orderArr = $ord->getSortOrderList($user_id, $order, $limit, $now_page);

//ページネーション設定
if($now_page == 1 || $now_page == $pages) {
  $range = 4;
} elseif ($now_page == 2 || $now_page == $pages - 1) {
  $range = 3;
} else {
  $range = 2;
}

$context = [];
$context['user_id'] = $user_id;
$context['orderArr'] = $orderArr;
$context['pages'] = $pages;
$context['now_page'] = $now_page;
$context['range'] = $range;
$context['order_count'] = $order_count;
$context['limit'] = $limit;
$context['sort'] = $sort;


$template = $twig->loadTemplate('purchase_history.html.twig');
$template->display($context);