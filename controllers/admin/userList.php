<?php

//商品一覧を表示するプログラム

namespace controllers\admin;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\User;

//失敗したらfalseがかえる
$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
//$dbを引数を渡すことでPSODatabaseクラスをItemクラスで使えることができる
$user = new User($db);

//テンプレート指定 twigのテンプレートファイルを読み込むための指定
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

//表示順設定
switch($sort)
{
  case 'new':
    $order = ' regist_date DESC ';
  break;

  case 'old':
    $order = ' regist_date ASC ';
  break;
}

$arrKeyword = [];

if(isset($_GET['search']))
{
  unset($_GET['search']);

  // キーワードを部分一致用に直す
  foreach($_GET as $key => $val)
  {
    if($val !== '')
    {
      $arrKeyword[$key] = $val;
    }else{
      unset($arrKeyword[$key]);
    }
  }

  //キーワード該当商品取得
  list($where, $arrVal) = $user->getKeyword($arrKeyword);
  $dataArr = $user->getUserKeywordList($where, $arrVal, $order, $limit, $now_page);

  //キーワード該当全商品数取得
  $user_count = $user->countUserKeywordList($where, $arrVal);
  $pages = ceil($user_count / $limit);

}else{
  $user_count = $user->countUserList();
  $pages = ceil($user_count / $limit);
  $dataArr = $user->getSortUserList($order, $limit, $now_page);
}

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

  //全データ取得
  $userList = $user->getAllUserList();
  if($userList !== false)
  {
    //現在の日時
    $now = new \DateTime();
    // ファイル名
    $csvfilename = "";
    $csvfilename .= 'userlist-';
    $csvfilename .= $now->format('Ymd');

    //CSVファイル名、出力情報の設定
    $fileNm = $csvfilename.".csv";
    $csvstr = "";

    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=".$fileNm);
    header("Content-Transfer-Encoding: binary");

    //各タイトル行
    $csvstr .= mb_convert_encoding("ID", "SJIS", "UTF-8"). ",";
    $csvstr .= mb_convert_encoding("お名前", "SJIS", "UTF-8"). ",";
    $csvstr .= mb_convert_encoding("フリガナ", "SJIS", "UTF-8"). ",";
    $csvstr .= mb_convert_encoding("性別", "SJIS", "UTF-8"). ",";
    $csvstr .= mb_convert_encoding("生年月日", "SJIS", "UTF-8"). ",";
    $csvstr .= mb_convert_encoding("郵便番号", "SJIS", "UTF-8"). ",";
    $csvstr .= mb_convert_encoding("住所", "SJIS", "UTF-8"). ",";
    $csvstr .= mb_convert_encoding("メールアドレス", "SJIS", "UTF-8"). ",";
    $csvstr .= mb_convert_encoding("電話番号", "SJIS", "UTF-8"). ",";
    $csvstr .= mb_convert_encoding("登録日", "SJIS", "UTF-8"). "\r\n";

    //CSV生成
    foreach ($userList as $key => $row)
    {
      $row = mb_convert_encoding($row, "SJIS", "UTF-8");

      //「規格」の列は頭0が消えないように頭にイコール付ける
      //「備考」の列は改行が消えないように「str_replace」関数を使用
      $csvstr .= $row['user_id'] . ",";
      $csvstr .= $row['full_name'] . ",";
      $csvstr .= $row['full_name_kana'] . ",";
      $csvstr .= ($row['sex'] === 1) ? mb_convert_encoding("男", "SJIS", "UTF-8") . "," : mb_convert_encoding("女", "SJIS", "UTF-8") . "," ;
      $csvstr .= $row['year'] . $row['month'] . $row['day'] . ",";
      $csvstr .= $row['zip1'] . $row['zip2'] .  ",";
      $csvstr .= $row['address'] . ",";
      $csvstr .= $row['email'] . ",";
      $csvstr .= '="' . $row['phone_number'] . '"'. ",";
      $csvstr .= '"' . str_replace('"', '""', date('Y/m/d H:i:s', strtotime($row['regist_date']))) . '"' . "\t" . "\r\n";
    }
    echo $csvstr;
    exit();
  }
}

$context = [];
$context['dataArr'] = $dataArr;
$context['user_count'] = $user_count;
$context['limit'] = $limit;
$context['sort'] = $sort;
$context['pages'] = $pages;
$context['range'] = $range;
$context['now_page'] = $now_page;


$template = $twig->loadTemplate('user_list.html.twig');
$template->display($context);

