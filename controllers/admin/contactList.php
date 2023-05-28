<?php

//お問い合わせ一覧を表示するプログラム

namespace controllers\admin;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\Contact;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$con = new Contact($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_ADMIN);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

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
    $order = ' regist_date DESC ';
  break;

  case 'old':
    $order = ' regist_date ASC ';
  break;
}

//お問い合わせ一覧取得
$contact_count = $con->countContactList();
$pages = ceil($contact_count / $limit);
$dataArr = $con->getSortContactList($order, $limit, $now_page);

//ページネーション設定
if($now_page == 1 || $now_page == $pages) {
  $range = 4;
} elseif ($now_page == 2 || $now_page == $pages - 1) {
  $range = 3;
} else {
  $range = 2;
}

$context = [];
$context['dataArr'] = $dataArr;
$context['contact_count'] = $contact_count;
$context['limit'] = $limit;
$context['sort'] = $sort;
$context['pages'] = $pages;
$context['now_page'] = $now_page;
$context['range'] = $range;

$template = $twig->loadTemplate('contact_list.html.twig');
$template->display($context);

