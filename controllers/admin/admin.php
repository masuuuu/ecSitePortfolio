<?php

//管理者ページを表示するプログラム

namespace controllers\admin;

require_once dirname(__FILE__) . '/../../models/Bootstrap.class.php';

use models\Bootstrap;
use models\PDODatabase;

$db = new PDODatabase(Bootstrap::DB_HOST, Bootstrap::DB_USER, Bootstrap::DB_PASS, Bootstrap::DB_NAME, Bootstrap::DB_TYPE);

$loader = new \Twig_Loader_Filesystem(Bootstrap::TEMPLATE_DIR_ADMIN);
$twig = new \Twig_Environment($loader, [
  'cache' => Bootstrap::CACHE_DIR
]);

$template = $twig->loadTemplate('admin.html.twig');
$template->display([]);

