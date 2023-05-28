<?php

//パスワードの変更完了画面表示プログラム

namespace controllers\public;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_PUBLIC);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

//ユーザーID
$user_id = (isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';

$context = [];
$context['user_id'] = $user_id;

$template = $twig->loadTemplate('update_password_complete.html.twig');
$template->display($context);