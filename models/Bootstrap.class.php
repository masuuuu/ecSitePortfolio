<?php

namespace models;

date_default_timezone_set('Asia/Tokyo');

require_once 'c:/xampp/htdocs/shop/vendor/autoload.php';

class Bootstrap
{
  const DB_HOST = 'localhost';
  const DB_NAME = 'shop_db';
  const DB_USER = 'shop_user';
  const DB_PASS = 'shop_pass';
  const DB_TYPE = 'mysql';

  const APP_DIR = 'c:/xampp/htdocs/shop/';
  const TEMPLATE_DIR_PUBLIC = self::APP_DIR . 'views/public/';
  const TEMPLATE_DIR_ADMIN = self::APP_DIR . 'views/admin/';
  const CACHE_DIR = false;
  const APP_URL = 'http://localhost/shop/';
  const ENTRY_URL = self::APP_URL . 'controllers/';
  public static function loadClass($class)
  {
    $path = str_replace('\\', '/', self::APP_DIR . $class . '.class.php');
    require_once $path;
  }
}

spl_autoload_register([
  'models\Bootstrap',
  'loadClass'
]);