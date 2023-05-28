<?php

//売り上げに関するプログラムのクラスファイル

namespace models;
class Sales
{
  public $db = null;

  public function __construct($db = null)
  {
    $this->db = $db;
  }

  public function getMonth()
  {
    $month_labels = [];
    //月を作成
    for($i = 1; $i < 13; $i ++)
    {
      $month = sprintf("%02d", $i);
      $month_labels[] .= $month . '月';
    }
    return $month_labels;
  }

  public function getDay()
  {
    $day_labels = [];
    //日を作成
    for($i = 1; $i <= date('t'); $i ++)
    {
      $day = sprintf("%02d", $i);
      $day_labels[] .= $day . '日';
    }
    return $day_labels;
  }

  public function getMonthSales()
  {
    $table = ' orders ';
    $column = " DATE_FORMAT(order_date, '%Y%m') AS month, SUM(total_payment_amount) AS sum ";
    $groupby = " DATE_FORMAT(order_date, '%Y%m') ";
    $where = " DATE_FORMAT(order_date, '%Y') = ? ";
    $arrVal = [date('Y')];
    $this->db->setGroupBy($groupby);
    return $this->db->select2($table, $column, $where, $arrVal);
  }

  
  public function setMonthSalesArr($month_sales)
  {
    $month_sales_arr = [];
    //月を作成
    for($i = 1; $i < 13; $i ++)
    {
      $month = date('Y') . sprintf("%02d", $i);
      $month_sales_arr[$month] = '0';
    }

    foreach($month_sales_arr as $key => $val)
    {
      foreach($month_sales as $sales)
      {
        if($key == $sales['month'])
        {
          $month_sales_arr[$key] = $sales['sum'];
        }
      }
    }
    return $month_sales_arr;
  }

  public function getDaySales()
  {
    $table = ' orders ';
    $column = " DATE_FORMAT(order_date, '%Y%m%d') AS day, SUM(total_payment_amount) AS sum ";
    $groupby = " DATE_FORMAT(order_date, '%Y%m%d') ";
    $where = " DATE_FORMAT(order_date, '%Y%m') = ? ";
    $arrVal = [date('Ym')];
    $this->db->setGroupBy($groupby);
    return $this->db->select2($table, $column, $where, $arrVal);
  }

  public function setDaySalesArr($day_sales)
  {
    $day_sales_arr = [];
    //月を作成
    for($i = 1; $i <= date('t'); $i ++)
    {
      $day = date('Ym') . sprintf("%02d", $i);
      $day_sales_arr[$day] = '0';
    }

    foreach($day_sales_arr as $key => $val)
    {
      foreach($day_sales as $sales)
      {
        if($key == $sales['day'])
        {
          $day_sales_arr[$key] = $sales['sum'];
        }
      }
    }
    return $day_sales_arr;
  }

}