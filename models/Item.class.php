<?php

//商品に関するプログラムのクラスファイル

namespace models;

class Item
{
  public $cateArr = [];
  public $db = null;

  public function __construct($db)
  {
    $this->db = $db;
  }

  //アイテムカテゴリーリストの取得
  public function getItemCategoryList()
  {
    $table = ' categorys ';
    $col = ' category_id, category_name ';
    $res = $this->db->select($table, $col);
    return $res;
  }

  //商品取得
  public function getItemList($category_id)
  {
    $table = ' items ';
    $col = ' item_id, item_name, detail, unit_price ';
    $where = ($category_id !== '') ? '  category_id = ? AND state = ? ' : ' state = ? ';
    $arrVal = ($category_id !== '') ? [$category_id, 0] : [0];

    $res = $this->db->select($table,  $col, $where, $arrVal);
    return $res;
  }

  //商品数取得
  public function countItemList($category_id)
  {
    $table = ' items ';
    $where = ($category_id !== '') ? '  category_id = ? AND state = ? ' : ' state = ? ';
    $arrVal = ($category_id !== '') ? [$category_id, 0] : [0];

    $res = $this->db->count($table, $where, $arrVal);
    return $res;
  }

  //現ページ商品取得
  public function getSortItemList($order, $limit, $category_id, $now_page)
  {
    if($category_id !== '')
    {
      $arrVal[] = $category_id;
      $arrVal[] = '1-1';
      $arrVal[] = 0;
    }else
    {
      $arrVal[] = '1-1';
      $arrVal[] = 0;
    }
    if($now_page === 1)
    {
      $arrVal[] = $now_page-1;
      $arrVal[] = $limit;

    }else
    {
      $arrVal[] = ($now_page-1)*$limit;
      $arrVal[] = $limit;
    }

    $table = ' items it JOIN images im ON it.item_id = im.item_id ';
    $col = ' it.item_id, it.item_name, it.detail, it.unit_price, im.image ';
    $where = ($category_id !== '') ? ' it.category_id = ? AND im.item_image_id = ? AND it.state = ? ' : ' im.item_image_id = ? AND it.state = ? ';
    $order = $order;
    $limit = ' ?, ? ';
    $this->db->setOrder($order);
    $this->db->setLimitOff($limit);

    $res = $this->db->select2($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }

  //キーワードのSQL文作成
  public function getKeyword($user_keyword)
  {
    //受け取ったキーワードの全角スペースを半角スペースに変換する
    $user_keyword = str_replace("　", " ", $user_keyword);

    //キーワードを空白で分割する
    $arrKeyword = explode(" ", $user_keyword);

    $where = '';
    $arrVal = [];

    for($i = 0; $i < count($arrKeyword); $i++)
    {
      $where .= ' (item_name like ? or detail like ?) ';
      $arrVal[] = "%$arrKeyword[$i]%";
      $arrVal[] = "%$arrKeyword[$i]%";

      if ($i < count($arrKeyword) -1)
      {
        $where .= ' AND ';
      }
    }
    return [$where, $arrVal];
  }

  //キーワード該当商品数取得
  public function countItemKeywordList($where, $arrVal)
  {
    $table = ' items ';
    $res = $this->db->count($table, $where, $arrVal);
    return $res;
  }

  //キーワード該当商品取得
  public function getItemKeywordList($where, $arrVal, $order, $limit, $now_page)
  {
    if($now_page === 1)
    {
      $arrVal[] = '1-1';
      $arrVal[] = 0;
      $arrVal[] = $now_page-1;
      $arrVal[] = $limit;

    }else
    {
      $arrVal[] = '1-1';
      $arrVal[] = 0;
      $arrVal[] = ($now_page-1)*$limit;
      $arrVal[] = $limit;
    }

    $table = ' items it JOIN images im ON it.item_id = im.item_id ';
    $col = ' it.item_id, it.item_name, it.detail, it.unit_price, im.image ';
    $where .= ' AND im.item_image_id = ? AND it.state = ? ';
    $order = $order;
    $limit = ' ?, ? ';
    $this->db->setOrder($order);
    $this->db->setLimitOff($limit);
    $res = $this->db->select2($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }

  //商品の詳細情報を取得する
  public function getItemDetailData($item_id)
  {
    $table = ' items ';
    $col = ' item_id, item_name, detail, unit_price ';
    $where = ($item_id !== '') ? ' item_id = ? AND state = ? ' : ' state = ? ';
    //カテゴリーによって表示させるアイテムをかえる
    $arrVal = ($item_id !== '') ? [$item_id, 0] : [0];

    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }

  //商品の詳細情報を取得する
  public function getItemAdminDetailData($item_id)
  {
    $table = ' items ';
    $col = ' item_id, item_name, detail, unit_price, category_id ';
    $where = ' item_id = ? AND state = ? ';
    $arrVal = [$item_id, 0];

    return $this->db->select($table, $col, $where, $arrVal);
  }

  //skuの情報を取得
   public function getskuData($item_id)
   {
    $table = ' skus ';
    $col = ' sku_code, item_id, size, color ';
    $where = ' item_id = ? AND state = ? ';
    $arrVal = [$item_id, 0];

    return $this->db->select($table, $col, $where, $arrVal);
   }

   //商品画像取得
   public function getImageData($item_id)
   {
    $table = ' images ';
    $col = ' item_image_id, image ';
    $where = ($item_id !== '') ? '  item_id = ? ' : '';
    $arrVal = ($item_id !== '') ? [$item_id] : [];

    return $this->db->select($table, $col, $where, $arrVal);
   }
  

}
