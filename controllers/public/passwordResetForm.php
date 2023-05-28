<?php

//パスワード再設定用のURLのフォーム画面のプログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;
use models\User;
use models\UserLogin;


$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);
$user = new User($db);
$userlog = new UserLogin($db);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';
//URLのトークン
$passwordResetToken = filter_input(INPUT_GET, 'token');
//tokenに合致するユーザーを取得
$passwordResetUser = $user->passwordResetUser($passwordResetToken);
if(!$passwordResetUser)
{
  exit('無効なURLです');
}
// パスワードの変更リクエストが24時間以上前の場合、有効期限切れとする
$tokenValidPeriod = (new \DateTime())->modify("-24 hour")->format('Y-m-d H:i:s');
if ($passwordResetUser[0]['token_sent_at'] < $tokenValidPeriod) {
    exit('有効期限切れです');
}

$errArr = [];
$token = $userlog->createToken();

$context = [];

$context['errArr'] = $errArr;
$context['user_id'] = $user_id;
$context['token'] = $token;
$context['passwordResetToken'] = $passwordResetToken;

$template = $twig->loadTemplate('password_reset_form.html.twig');
$template->display($context);
