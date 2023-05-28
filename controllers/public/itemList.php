<?php

//商品一覧を表示するプログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\UserLogin;
use models\Item;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$log = new UserLogin($db);
$itm = new Item($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

//ユーザーID
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
//カテゴリーが選択されたらGET通信でカテゴリIDが渡って来る
$category_id = (isset($_GET['category_id']) === true && preg_match('/^[0-9]+$/', $_GET['category_id']) === 1) ? $_GET['category_id'] : '';
//検索キーワード取得
$item_keyword = (isset($_GET['keyword']) === true) ? $_GET['keyword'] : '';
//商品表示件数
$limit = (isset($_GET['limit']) === true) ? intval($_GET['limit']) : 10;
//表示順
$sort = (isset($_GET['sort']) === true) ? $_GET['sort'] : 'new';
//現在のページ
$now_page = (isset($_GET['page_id']) === true) ? $_GET['page_id'] : 1;

//表示順の設定
switch($sort)
{
  case 'new':
    $order = ' it.regist_date DESC ';
  break;

  case 'high':
    $order = ' it.unit_price DESC ';
  break;

  case 'row':
    $order = ' it.unit_price ASC ';
  break;
}

//キーワードが空じゃない場合
if($item_keyword !== '')
{
  //キーワードを部分一致用に直す
  list($where, $arrVal) = $itm->getKeyword($item_keyword);
  $dataArr = $itm->getItemKeywordList($where, $arrVal, $order, $limit, $now_page);
  //キーワード該当全商品数取得
  $item_count = $itm->countItemKeywordList($where, $arrVal);
  $pages = ceil($item_count / $limit);
  
}else{
  //カテゴリ別全商品数取得
  $item_count = $itm->countItemList($category_id);
  $pages = ceil($item_count / $limit);
  //カテゴリ別全商品リスト取得
  $dataArr = $itm->getSortItemList($order, $limit, $category_id, $now_page);
}

//ページネーション設定
if($now_page == 1 || $now_page == $pages) {
  $range = 4;
} elseif ($now_page == 2 || $now_page == $pages - 1) {
  $range = 3;
} else {
  $range = 2;
}

//カテゴリーリスト取得
$cateArr = $itm->getItemCategoryList();
$context = [];
$context['cateArr'] = $cateArr;
$context['dataArr'] = $dataArr;
$context['user_id'] = $user_id;
$context['item_keyword'] = $item_keyword;
$context['category_id'] = $category_id;
$context['item_count'] = $item_count;
$context['pages'] = $pages;
$context['now_page'] = $now_page;
$context['range'] = $range;
$context['sort'] = $sort;
$context['limit'] = $limit;


$template = $twig->loadTemplate('item_list.html.twig');
$template->display($context);

