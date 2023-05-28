<?php

//郵便番号から住所を自動で入力されるプログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\PDODatabase;
use models\Bootstrap;
use models\User;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User($db);

if(isset($_GET['zip1']) === true && isset($_GET['zip2']) === true)
{
  $zip1 = $_GET['zip1'];
  $zip2 = $_GET['zip2'];

  $res = $user->getPostcode($zip1, $zip2);
  //出力結果がajaxに渡される
  echo ($res !== '' && count($res) !== 0) ? $res[0]['pref']. $res[0]['city'] . $res[0]['town'] : '';
}else{
  echo 'no';
}