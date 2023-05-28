<?php

//管理者

namespace models;

class Admin
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

  //アイテムリストを取得
  public function getItemList($category_id)
  {
    //カテゴリーによって表示させるアイテムを変える
    $table = ' items ';
    $col = ' item_id, item_name, price, image, category_id ';
    $where = ($category_id !== '') ? '  category_id = ? ' : '';
    $arrVal = ($category_id !== '') ? [$category_id] : [];
    $res = $this->db->select($table, $col, $where, $arrVal);

    //$resにselect結果がある場合と$resに1件以上結果がある場合
    return ($res !== false && count($res) !== 0) ? $res : false;
  }

  //商品の詳細情報を取得する
  public function getItemDetailData($item_id)
  {
    $table = ' items ';
    $col = ' item_id, item_name, detail, price, image, item_id ';

    $where = ($item_id !== '') ? '  item_id = ? ' : '';
    //カテゴリーによって表示させるアイテムをかえる
    $arrVal = ($item_id !== '') ? [$item_id] : [];

    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }

  //キーワード該当商品を取得する
  public function getItemKeywordList($item_keyword)
  {
    $item_keyword = "%".$item_keyword."%";
    $table = ' items ';
    $col = ' item_id, item_name, detail, price, image, item_id  ';
    $where = " item_name like binary ? or detail like binary ? ";
    $arrVal = [$item_keyword, $item_keyword];

    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }
}