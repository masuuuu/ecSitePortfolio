<?php

//商品詳細を表示するプログラム

namespace controllers\admin;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\Item;
use models\ItemRegist;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$itm = new Item($db);
$itemRegist = new ItemRegist($db);

//テンプレート指定
$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_ADMIN);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$item_id = (isset($_GET['item_id']) === true && preg_match('/^\d+$/', $_GET['item_id']) === 1) ? $_GET['item_id'] : '';
$sku_code = (isset($_GET['sku_code']) === true) ? $_GET['sku_code'] : '';
$sku_code = (isset($_GET['sku_code']) === true) ? $_GET['sku_code'] : '';

//item_idが取得できない場合、商品一覧へリダイレクト
if($item_id === '')
{
  header('Location:' . Bootstrap::ENTRY_URL. 'admin/itemList.php');
  exit;
}

$errArr = [];

//商品詳細の更新
if(isset($_POST['update_detail']))
{
  unset($_POST['update_detail']);
  $dataArr = $_POST;
  $errArr = $itemRegist->updateDetailErrorCheck($dataArr);
  $errCheck = $itemRegist->getErrorFlg();
  if($errCheck === true)
  {
    $res = $itemRegist->updateDetail($item_id, $dataArr);
    if($res === true)
    {
      header('Location:' . Bootstrap::ENTRY_URL. 'admin/itemDetail.php?item_id=' . $item_id);
      exit;
    }else{
      $errArr['updateDetailError'] = '更新に失敗しました。もう一度やり直してください。';
    }
  }
}

//商品画像の更新
if(isset($_POST['updateImage']))
{
  
  $errArr = $itemRegist->updateimageCheck();
  $errCheck = $itemRegist->getErrorFlg();
  if($errCheck === true)
  {
    $item_id = $_POST['item_id'];
    $itemRegist->updateImageTransaction($item_id);
  }
}

//SKUの追加
if(isset($_POST['add_sku']))
{
  unset($_POST['add_sku']);
  $dataArr = $_POST;
  $errArr = $itemRegist->addSkuErrorCheck($dataArr);
  $errCheck = $itemRegist->getErrorFlg();
  if($errCheck === true)
  {
    $colorArr = $dataArr['color'];
    $sizeArr = $dataArr['size'];
    $colorArr = array_map('trim', explode("\n", $colorArr));
    $sizeArr = array_map('trim', explode("\n", $sizeArr));

    $res = $itemRegist->addSkuRegist($item_id, $colorArr, $sizeArr);
    if($res === true)
    {
      header('Location:' . Bootstrap::ENTRY_URL. 'admin/itemDetail.php?item_id=' . $item_id);
      exit;
    }else{
      $errArr['addSkuError'] = '更新に失敗しました。もう一度やり直してください。';
    }
  }
}

//skuの削除
if($sku_code !== '')
{
  $res = $itemRegist->deleteSku($sku_code);
  if($res === true)
  {
    header('Location:' . Bootstrap::ENTRY_URL. 'admin/itemDetail.php?item_id=' . $item_id);
    exit;
  }
}

//商品詳細取得
$cateArr = itemRegist::getCategory();
$dataArr = $itm->getItemAdminDetailData($item_id);
$imageArr = $itm->getImageData($item_id);
$skuArr = $itm->getskuData($item_id);

$context = [];
$context['cateArr'] = $cateArr;
$context['dataArr'] = $dataArr[0];
$context['imageArr'] = $imageArr;
$context['skuArr'] = $skuArr;
$context['errArr'] = $errArr;

$template = $twig->loadTemplate('item_detail.html.twig');
$template->display($context);
