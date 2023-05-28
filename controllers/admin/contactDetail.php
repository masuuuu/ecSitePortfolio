<?php

//お問い合わせ詳細を表示するプログラム

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

//お問い合わせID設定
$contact_id = (isset($_GET['contact_id']) === true) ? $_GET['contact_id'] : '';

//お問い合わせ詳細表示
if(isset($_POST['state']))
{
  //対応ステータスがクリックされたら、以下処理実行してお問い合わせ詳細にリダイレクト
  $con->updateState($contact_id);
  $dataArr = $con->getContactData($contact_id);
  header('Location:' . Bootstrap::ENTRY_URL . "admin/contactDetail.php?contact_id=$contact_id");
  exit();
}else{
  $dataArr = $con->getContactData($contact_id);
}

$context = [];
$context['dataArr'] = $dataArr[0];

$template = $twig->loadTemplate('contact_detail.html.twig');
$template->display($context);

