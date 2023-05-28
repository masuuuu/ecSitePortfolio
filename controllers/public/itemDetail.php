<?php

//商品詳細を表示するプログラム

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

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
//クリックした商品のitem_idが渡ってくる
$item_id = (isset($_GET['item_id']) === true && preg_match('/^\d+$/', $_GET['item_id']) === 1) ? $_GET['item_id'] : '';

//item_idが取得できない場合、商品一覧リダイレクト
if($item_id === '')
{
  header('Location:' . Bootstrap::ENTRY_URL. 'public/itemList.php');
}

//商品詳細取得
$dataArr = $itm->getItemDetailData($item_id);
//商品SKU取得
$skuArr = $itm->getskuData($item_id);
//商品画像取得
$imageArr = $itm->getImageData($item_id);

$context = [];

$context['dataArr'] = $dataArr[0];
$context['skuArr'] = $skuArr;
$context['imageArr'] = $imageArr;
$context['user_id'] = $user_id;


$template = $twig->loadTemplate('item_detail.html.twig');
$template->display($context);
