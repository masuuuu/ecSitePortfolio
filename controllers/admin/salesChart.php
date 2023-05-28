<?php

//売り上げ管理を表示するプログラム

namespace controllers\admin;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\Sales;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$sl = new Sales($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_ADMIN);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

//月と今月の日数を取得
$month_labels = $sl->getMonth();
$day_labels = $sl->getDay();

//月別売り上げデータ取得
$month_sales = $sl->getMonthSales();
$month_sales_arr = $sl->setMonthSalesArr($month_sales);
$month_sales_arr_val = array_values($month_sales_arr);

//日別売り上げデータ取得
$day_sales = $sl->getDaySales();
$day_sales_arr = $sl->setDaySalesArr($day_sales);
$day_sales_arr_val = array_values($day_sales_arr);


// 「CSVダウンロード」クリック時
if (isset($_POST['csvoutput']))
{
  if($_POST['selectChart'] === 'monthChart')
  {
    //現在の日時
    $now = new \DateTime();
    // ファイル名
    $csvfilename = "";
    $csvfilename .= 'monthSales-';
    $csvfilename .= $now->format('Ymd');

    //CSVファイル名、出力情報の設定
    $fileNm = $csvfilename.".csv";

    $csvstr = "";

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=".$fileNm);
    header("Content-Transfer-Encoding: binary");

    //各タイトル行
    foreach($month_labels as $key => $month)
    {
      if($key != 11)
      {
        $csvstr .= mb_convert_encoding($month, "SJIS", "UTF-8") . ",";
      }else{
        $csvstr .= mb_convert_encoding($month, "SJIS", "UTF-8"). "\r\n";
      }
      
    }

    //CSV生成
    foreach ($month_sales_arr_val as $key => $sales)
    {
      $sales = mb_convert_encoding($sales, "SJIS", "UTF-8");
      if($key != 11)
      {
      $csvstr .= $sales . ",";
      }else{
        $csvstr .= $sales . "\r\n";
      }
    }
    echo $csvstr;
    exit();
  }elseif($_POST['selectChart'] === 'dayChart')
  {
    //現在の日時
    $now = new \DateTime();
    // ファイル名
    $csvfilename = "";
    $csvfilename .= 'daySales-';
    $csvfilename .= $now->format('Ymd');

    //CSVファイル名、出力情報の設定
    $fileNm = $csvfilename.".csv";

    $csvstr = "";

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=".$fileNm);
    header("Content-Transfer-Encoding: binary");

    //各タイトル行
    foreach($day_labels as $key => $day)
    {
      if($key != date('t')-1)
      {
        $csvstr .= mb_convert_encoding($day, "SJIS", "UTF-8") . ",";
      }else{
        $csvstr .= mb_convert_encoding($day, "SJIS", "UTF-8"). "\r\n";
      }
      
    }

    //CSV生成
    foreach ($day_sales_arr_val as $key => $sales)
    {
      $sales = mb_convert_encoding($sales, "SJIS", "UTF-8");
      if($key != date('t')-1)
      {
      $csvstr .= $sales . ",";
      }else{
        $csvstr .= $sales . "\r\n";
      }
    }
    echo $csvstr;
    exit();
  }
}

$context = [];
$context['month_labels'] = $month_labels;
$context['day_labels'] = $day_labels;
$context['month_sales_arr_val'] = $month_sales_arr_val;
$context['day_sales_arr_val'] = $day_sales_arr_val;

$template = $twig->loadTemplate('sales_chart.html.twig');
$template->display($context);

