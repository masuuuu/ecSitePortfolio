<?php

//ユーザー削除プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\User;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_ADMIN);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

//削除ユーザーのID
$user_id = (isset($_GET['user_id']) === true && preg_match('/^[0-9]+$/', $_GET['user_id']) === 1) ? $_GET['user_id'] : '';

//ユーザー情報取得
$dataArr = $user->getUserData($user_id);

//ユーザー削除処理
if(isset($_POST['delete']))
{
  $res = $user->deleteMember($user_id);
  if($res === true)
  {
    header('Location:' . Bootstrap::ENTRY_URL . 'admin/deleteUserListComplete.php');
    exit;
  }else{
    header('Location:' . Bootstrap::ENTRY_URL . 'admin/deleteUserList.php?user_id='.$user_id);
    exit;
  }
}

$context = [];
$context['dataArr'] = $dataArr[0];

$template = $twig->loadTemplate('delete_user_list.html.twig');
$template->display($context);
