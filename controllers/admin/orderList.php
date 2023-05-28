<?php

//注文一覧表示プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\UserLogin;
use models\Order;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$log = new UserLogin($db);
$ord = new Order($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_ADMIN);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

//ユーザーID
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
//表示件数
$limit = (isset($_GET['limit']) === true) ? intval($_GET['limit']) : 10;
//表示順
$sort = (isset($_GET['sort']) === true) ? $_GET['sort'] : 'new';
//現在のページ
$now_page = (isset($_GET['page_id']) === true) ? $_GET['page_id'] : 1;

$date_err = '';

//表示順設定
switch($sort)
{
  case 'new':
    $order = ' order_date DESC ';
  break;

  case 'old':
    $order = ' order_date ASC ';
  break;
}

$order_count = $ord->countAllOrderList();
$pages = ceil($order_count / $limit);
$orderArr = $ord->getSortAllOrderList($order, $limit, $now_page);

//ページネーション設定
if($now_page == 1 || $now_page == $pages) {
  $range = 4;
} elseif ($now_page == 2 || $now_page == $pages - 1) {
  $range = 3;
} else {
  $range = 2;
}

// 「CSVダウンロード」クリック時
if (isset($_POST['csvoutput']))
{
  $date_start = $_POST['date_start'];
  $date_end = $_POST['date_end'];
  //現在の日時
  $now = new \DateTime();
  // ファイル名
  $csvfilename = "";
  $csvfilename .= 'orderlist-';
  $csvfilename .= $now->format('Ymd');

  //CSVファイル名、出力情報の設定
  $fileNm = $csvfilename.".csv";

  $csvstr = "";

  header("Content-Type: application/octet-stream");
  header("Content-Disposition: attachment; filename=".$fileNm);
  header("Content-Transfer-Encoding: binary");

  //各タイトル行
  $csvstr .= mb_convert_encoding("注文日時", "SJIS", "UTF-8") . ",";
  $csvstr .= mb_convert_encoding("注文番号", "SJIS", "UTF-8") . ",";
  $csvstr .= mb_convert_encoding("注文者", "SJIS", "UTF-8") . ",";
  $csvstr .= mb_convert_encoding("合計金額", "SJIS", "UTF-8"). "\r\n";

  $order_list = $ord->getSpecifiedPeriodOrderList($date_start, $date_end);
  
  //CSV生成
  foreach ($order_list as $key => $val)
  {
    $csvstr .= mb_convert_encoding($val['order_date'], "SJIS", "UTF-8"). ",";
    $csvstr .= mb_convert_encoding($val['order_no'], "SJIS", "UTF-8"). ",";
    $csvstr .= mb_convert_encoding($val['full_name'], "SJIS", "UTF-8"). ",";
    $csvstr .= mb_convert_encoding($val['total_payment_amount'], "SJIS", "UTF-8"). "\r\n";
  }
  echo $csvstr;
  exit();
}

$context = [];
$context['orderArr'] = $orderArr;
$context['pages'] = $pages;
$context['now_page'] = $now_page;
$context['range'] = $range;
$context['order_count'] = $order_count;
$context['limit'] = $limit;
$context['sort'] = $sort;
$context['date_err'] = $date_err;

$template = $twig->loadTemplate('order_list.html.twig');
$template->display($context);