<?php

//カートに関するプログラムのクラスファイル

namespace models;
class Cart
{
  private $db = null;

  public function __construct($db = null)
  {
    $this->db = $db;
  }
  //$user_idのカートに$item_idがあれば取得
  public function getCartItem($user_id, $sku_code)
  {
    $table = ' carts ';
    $column = ' sku_code, quantity ';
    $where = ' user_id = ? AND sku_code = ? AND state = ? ';
    $arrVal = [$user_id, $sku_code, 0];
    return $this->db->select($table, $column, $where, $arrVal);
  }
  //$user_idのカートに$item_idの数量を1足す
  public function updateItemQuantity($quantity, $user_id, $sku_code)
  {
    $table = ' carts ';
    $quantity = $quantity + 1;
    $insData = ['quantity' => $quantity];
    $where = ' user_id = ? AND sku_code = ? AND state = ? ';
    $arrWhereVal = [$user_id, $sku_code, 0];
    
    return $this->db->update($table, $insData, $where, $arrWhereVal);
  }
  //数量を変更する
  public function updateItemQuantity2($quantity, $user_id, $sku_code)
  {
    $table = ' carts ';
    $insData = ['quantity' => $quantity];
    $where = ' user_id = ? AND sku_code = ? AND state = ? ';
    $arrWhereVal = [$user_id, $sku_code, 0];
    
    return $this->db->update($table, $insData, $where, $arrWhereVal);
  }

  //カートに登録する
  public function insCartData($user_id, $item_id, $sku_code)
  {
    $table = ' carts ';
    $insData = [
      'user_id' => $user_id,
      'item_id' => $item_id,
      'sku_code' => $sku_code
    ];
    return $this->db->insert($table, $insData);
  }

  //カートの情報を取得する
  public function getCartData($user_id)
  {
    $table = ' carts c LEFT JOIN skus s ON c.sku_code = s.sku_code LEFT JOIN items it ON s.item_id = it.item_id LEFT JOIN images im ON it.item_id = im.item_id ';
    $column = ' c.crt_id, c.quantity, c.sku_code, s.item_id, s.color, s.size, it.item_name, it.unit_price, im.image ';
    $where = ' c.user_id = ? AND c.state =  ? AND im.item_image_id = ? ';
    $arrVal = [$user_id, 0, '1-1'];

    return $this->db->select($table, $column, $where, $arrVal);
  }

  //カート情報を削除
  public function deleteCartData($crt_id)
  {
    $table = ' carts ';
    $insData = ['state' => 1];
    $where = ' crt_id = ? ';
    $arrWhereVal = [$crt_id];

    return $this->db->update($table, $insData, $where, $arrWhereVal);
  }

  //アイテム数と金額を取得する
  public function getItemQuantityAndPrice($user_id)
  {
    $table = " carts c  LEFT JOIN items it ON c.item_id = it.item_id ";
    $column = " it.unit_price, c.quantity ";
    $where = ' c.user_id  = ? AND c.state = ?';
    $arrWhereVal = [$user_id, 0];
    $res = $this->db->select($table, $column, $where, $arrWhereVal);
    return $res = ($res !== false && count($res) !== 0) ? $res : 0;

  }
  //アイテム数と金額の合計の計算
  public function totalQuantityAndPrice($res)
  {
    $totalAmount = 0;
    $totalQuantity = 0;
    if($res !== 0)
    {
      foreach($res as $val)
      {
        $totalAmount += intval($val['unit_price']) * $val['quantity'];
        $totalQuantity += $val['quantity'];
      }
    }
    return [$totalAmount, $totalQuantity];
  }

  //カートの個数表示
  public function getCartQuantity()
  {
    for($i = 1; $i < 10; $i ++)
    {
      $quantityArr[] = $i;
    }
    return $quantityArr;
  }

  //user_idのカート情報を削除
  public function deleteUserCartData($user_id)
  {
    $table = ' carts ';
    $insData = ['state' => 1];
    $where = ' user_id = ? ';
    $arrWhereVal = [$user_id];
    $this->db->update($table, $insData, $where, $arrWhereVal);
  }
  
}