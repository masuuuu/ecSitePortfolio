<?php

//注文に関するプログラムのクラスファイル

namespace models;
class Order
{
  public $db = null;

  public function __construct($db = null)
  {
    $this->db = $db;
  }
  //お支払い方法の取得
  public function getPaymentList()
  {
    $table = ' payments ';
    $column = ' * ';
    return $this->db->select($table, $column);
  }
  //order_Detailsテーブルに挿入の内容を作成
  public function createOrderDetailDataArr($sku_code, $quantity, $unit_price)
  {
    for($i = 0; $i < count($sku_code); $i++)
    {
      $orderDetailDataArr[$i] = [];
      $orderDetailDataArr[$i][] .=  $sku_code[$i];
      $orderDetailDataArr[$i][] .=  $quantity [$i];
      $orderDetailDataArr[$i][] .=  $unit_price[$i];
    }
    return $orderDetailDataArr;
  }
  //order_no作成
  public function createOrderNumber()
  {
    $order_no = date('Ymd') . '-' . mt_rand(1000000000, 9999999999); 
    return $order_no;
  }
  //orderテーブルに注文内容挿入
  public function insOrder($order_no, $user_id, $payment_id, $total_quantity, $total_amount, $total_payment_amount)
  {
    $table = ' orders ';
    $insData['order_no'] = $order_no;
    $insData['user_id'] = $user_id;
    $insData['payment_id'] = $payment_id;
    $insData['total_quantity'] = $total_quantity;
    $insData['total_amount'] = $total_amount;
    $insData['total_payment_amount'] = $total_payment_amount;
    $insData['order_date'] = date('Y-m-d H:i:s');
    $this->db->insert($table, $insData);
  }
  //order_detailテーブルに注文詳細挿入
  public function insOrderDetail($order_no, $orderDetailDataArr)
  {
    foreach($orderDetailDataArr as $val)
    {
      $table = ' order_details ';
      $insData['order_no'] = $order_no;
      $insData['sku_code'] = $val[0];
      $insData['quantity'] = $val[1];
      $insData['unit_price'] = $val[2];
      $this->db->insert($table, $insData);
    }
  }
  //user_idの注文件数取得
  public function countOrderList($user_id)
  {
    $table = ' orders ';
    $where = ' user_id = ? AND state = ? ';
    $arrVal = [$user_id, 0];
    return $this->db->count($table, $where, $arrVal);
  }
  //user_idの注文を取得
  public function getSortOrderList($user_id, $order, $limit, $now_page)
  {
    if($now_page === 1)
    {
      $arrVal = [0, $user_id, $now_page-1, $limit];
    }else{
      $arrVal = [0, $user_id, ($now_page-1)*$limit, $limit];
    }
    $table = ' orders ';
    $column = ' order_no, total_payment_amount, order_date ';
    $where = ' state = ? AND user_id = ? ';
    $order = $order;
    $limit = ' ?, ? ';
    $this->db->setOrder($order);
    $this->db->setLimitOff($limit);
    return $this->db->select2($table, $column, $where, $arrVal);
  }
  //$order_noの支払い方法の取得
  public function getOrderData($order_no)
  {
    $table = '  orders us JOIN payments py ON us.payment_id = py.payment_id ';
    $column = ' * ';
    $where = ' us.order_no = ? AND us.state = ? ';
    $arrVal = [$order_no, 0];
    return $this->db->select($table, $column, $where, $arrVal);
  }
  //$order_noの注文商品の取得
  public function getOrderDetailData($order_no)
  {
    $table = ' order_details od LEFT JOIN skus sk ON od.sku_code = sk.sku_code LEFT JOIN items it ON sk.item_id = it.item_id ';
    $column = ' od.quantity, od.unit_price, sk.color, sk.size, it.item_name ';
    $where = ' od.order_no = ? AND od.state = ? ';
    $arrVal = [$order_no, 0];
    return $this->db->select($table, $column, $where, $arrVal);
  }
  //全ての注文件数取得
  public function countAllOrderList()
  {
    $table = ' orders ';
    $where = ' state = ? ';
    $arrVal = [0];
    return $this->db->count($table, $where, $arrVal);
  }
  //全ての注文を取得
  public function getSortAllOrderList($order, $limit, $now_page)
  {
    if($now_page === 1)
    {
      $arrVal = [0, $now_page-1, $limit];
    }else{
      $arrVal = [0, ($now_page-1)*$limit, $limit];
    }
    $table = ' orders od LEFT JOIN users us ON od.user_id = us.user_id ';
    $column = ' order_no, total_payment_amount, order_date, full_name ';
    $where = ' od.state = ? ';
    $order = $order;
    $limit = ' ?, ? ';
    $this->db->setOrder($order);
    $this->db->setLimitOff($limit);
    return $this->db->select2($table, $column, $where, $arrVal);
  }
  //期間指定の注文件数取得
  public function getSpecifiedPeriodOrderList($date_start, $date_end)
  {
    $date_start .= ' 00:00:00'; 
    $date_end .= ' 23:59:59'; 
    $table = ' orders od LEFT JOIN users us ON od.user_id = us.user_id ';
    $column = ' order_no, total_payment_amount, order_date, full_name ';
    $where = ' od.state = ? AND order_date BETWEEN ? AND ? ';
    $arrVal = [0, $date_start, $date_end];
    return $this->db->select($table, $column, $where, $arrVal);
  }
}