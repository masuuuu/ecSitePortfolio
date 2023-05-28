<?php

//お届け先の変更プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\UserLogin;
use models\User;
use models\Cart;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$log = new UserLogin($db);
$user = new User($db);
$cart = new Cart($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';

if($user_id !== '')
{

}else{
  header('Location:' . Bootstrap::ENTRY_URL . 'public/userLogin.php');
  exit;
}

$context = [];
$context['sumNum'] = $sumNum;
$context['sumPrice'] = $sumPrice;
$context['dataArr'] = $dataArr;
$context['deliveryAddress'] = $deliveryAddress;
$context['user_id'] = $user_id;

$template = $twig->loadTemplate('cash_register.html.twig');
$template->display($context);
