<?php

//商品登録プログラム

namespace controllers\admin;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\ItemRegist;
use models\Item;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$itemRegist = new ItemRegist($db);
$item = new Item($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_ADMIN);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

//初期データを設定
$dataArr = [
  'item_name' => '',
  'detail' => '',
  'unit_price' => '',
  'image' => '',
  'category_id' => '',
  'color' => '',
  'size' => '',
];

//エラーメッセージの定義、初期
$errArr = [];
foreach($dataArr as $key => $value)
{
  $errArr[$key] = '';
}

$transactionMessage = '';

//登録の処理
if(isset($_POST['register']) === true)
{
  unset($_POST['register']);
  $dataArr = $_POST;
  $errArr = $itemRegist->errorCheck($dataArr);
  $errCheck = $itemRegist->getErrorFlg();

  if($errCheck === true)
  {
    $colorArr = $dataArr['color'];
    $sizeArr = $dataArr['size'];
    $colorArr = array_map('trim', explode("\n", $colorArr));
    $sizeArr = array_map('trim', explode("\n", $sizeArr));

    $res = $itemRegist->insItemImageSkuRegist($dataArr, $colorArr, $sizeArr);
  
    if($res === true)
    {
      header('Location:' . Bootstrap::ENTRY_URL . 'admin/itemList.php');
      exit();
    }else{
      $transactionMessage = '登録時に問題が発生したため、もう一度登録をお願いいたします。';
    }
  }

}

$cateArr = itemRegist::getCategory();

$context = [];

$context['dataArr'] = $dataArr;
$context['errArr'] = $errArr;
$context['cateArr'] = $cateArr;
$context['transactionMessage'] = $transactionMessage;

$template = $twig->loadTemplate('item_regist.html.twig');
$template->display($context);
