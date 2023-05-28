<?php

namespace models;

class Contact
{
  public $db = null;
  private $dataArr = [];
  private $errArr = [];

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function createErrorArr($dataArr)
  {
    $this->dataArr = $dataArr;
    $this->createErrorMessage();
    return $this->errArr;
  }

  public function contactErrorCheck($dataArr)
  {
    $this->dataArr = $dataArr;
    $this->createErrorMessage();
    $this->nameCheck();
    $this->mailCheck();
    $this->contactCheck();

    return $this->errArr;
  }

  private function createErrorMessage()
  {
    foreach($this->dataArr as $key => $val)
    {
      $this->errArr[$key] = '';
    }
  }
  public function getErrorFlg()
  {
    $err_check = true;
    foreach($this->errArr as $key => $value)
    {
      if($value !== '')
      {
        $err_check = false;
      }
    }
    return $err_check;
  }
  private function nameCheck()
  {
    if(trim($this->dataArr['name']) === '')
    {
      $this->errArr['name'] = 'お名前を入力してください';
    }
  }
  private function mailCheck()
  {
    if(preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+[a-zA-Z0-9\._-]+$/', $this->dataArr['email']) === 0)
    {
      $this->errArr['email'] = 'メールアドレスを入力してください';
    }
  }
  //問い合わせフォームエラーチェック
  private function contactCheck()
  {
    if(trim($this->dataArr['content']) === '')
    {
      $this->errArr['content'] = 'お問い合わせ内容を入力してください';
    }
  }
  
  //お問い合わせ内容データベースに挿入
  public function insContactData($dataArr)
  {
    $table = ' contacts ';
    $dataArr['regist_date'] = date('Y-m-d H:i:s');
    return $this->db->insert($table, $dataArr);
  }

  //お問い合わせの内容取得
  public function getContactData($contact_id)
  {
    $table = ' contacts ';
    $col = ' contact_id, name, email, content, regist_date, state ';
    $where = ' contact_id = ? AND (state = ? OR state = ?) ';
    $arrVal = [$contact_id, 0, 1];
    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }
  //contactを取得
  public function getContactList()
  {
    $table = ' contacts ';
    $col = ' contact_id, name, email, content, regist_date, state ';
    $where = ' state = ? OR state = ?';
    $arrVal = [0, 1];
    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }

  //お問い合わせのメール送信
  public function mailSend($contactData)
  {
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");


    $to = $contactData[0]['email']; // 送信先のアドレス
    $subject = "【自動送信】お問い合わせが完了いたしました。"; // 件名
$message = <<< EOD
お問い合わせありがとうございます。
以下の内容を送信いたしました。
必ずご返信いたしますのでしばらくお待ちください。
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

お名前
{$contactData[0]['name']}

お問い合わせ内容
{$contactData[0]['content']}

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
E-mail: email@email.com
サイト運営者：増田

EOD;

    $result = mb_send_mail($to, $subject, $message) ? true : false;
    return $result;
  }
  public function countContactList()
  {
    $table = ' contacts ';
    $where = ' state = ? OR state = ? ';
    $arrVal = [0, 1];

    $res = $this->db->count($table, $where, $arrVal);
    return $res;
  }
  //現ページ商品取得
  public function getSortContactList($order, $limit, $now_page)
  {
    if($now_page === 1)
    {
      $arrVal = [0, 1, $now_page-1, $limit];

    }else
    {
      $arrVal = [0, 1, ($now_page-1)*$limit, $limit];
    }
    $table = ' contacts ';
    $col = ' contact_id, name, email, content, regist_date, state ';
    $where = ' state = ? OR state = ? ';
    $order = $order;
    $limit = ' ?, ? ';
    $this->db->setOrder($order);
    $this->db->setLimitOff($limit);

    $res = $this->db->select2($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }

  public function updateState($contact_id)
  {
    $table = ' contacts ';
    $insData['update_date'] = date('Y-m-d H:i:s');
    $insData['state'] = 1;
    $where = ' contact_id = ? AND state = ? ';
    $arrVal = [$contact_id, 0];
    $this->db->update($table, $insData, $where, $arrVal);
  }

}