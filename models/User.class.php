<?php

//ユーザー登録、更新、削除

namespace models;

class User
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

  //会員登録のエラーチェック
  public function errorCheck($dataArr)
  {
    $this->dataArr = $dataArr;
    $this->createErrorMessage();

    $this->fullNameCheck();
    $this->fullNameKanaCheck();
    $this->sexCheck();
    $this->birthCheck();
    $this->zipCheck();
    $this->addCheck();
    $this->phoneNumberCheck();
    $this->mailCheck();
    $this->passwordCheck();

    return $this->errArr;

  }

  //会員登録修正のエラーチェック
  public function errorCheck2($dataArr)
  {
    $this->dataArr = $dataArr;
    $this->createErrorMessage();

    $this->fullNameCheck();
    $this->fullNameKanaCheck();
    $this->sexCheck();
    $this->birthCheck();
    $this->zipCheck();
    $this->addCheck();
    $this->phoneNumberCheck();
    $this->mailCheck2();

    return $this->errArr;

  }

  private function createErrorMessage()
  {
    foreach($this->dataArr as $key => $val)
    {
      $this->errArr[$key] = '';
    }
  }

  private function fullNameCheck()
  {
    if(trim($this->dataArr['full_name']) === '')
    {
      $this->errArr['full_name'] = 'お名前を入力してください';
    }
  }
  private function fullNameKanaCheck()
  {
    if(trim($this->dataArr['full_name_kana']) === '')
    {
      $this->errArr['full_name_kana'] = 'お名前（カナ）を入力してください';
    }
  }
  private function sexCheck()
  {
    if($this->dataArr['sex'] === '')
    {
      $this->errArr['sex'] = '性別を選択してください';
    }
  }
  private function birthCheck()
  {
    if($this->dataArr['year'] === '')
    {
      $this->errArr['year'] = '生年月日の年を選択してください';
    }
    if($this->dataArr['month'] === '')
    {
      $this->errArr['month'] = '生年月日の月を選択してください';
    }
    if($this->dataArr['day'] === '')
    {
      $this->errArr['day'] = '生年月日の日を選択してください';
    }

    //生年月日の整合性チェック
    if(checkdate($this->dataArr['month'], $this->dataArr['day'], $this->dataArr['year']) === false)
    {
      $this->errArr['year'] = '正しい日付を入力してください';
    }

    //未来日付だった場合のチェック
    if(strtotime($this->dataArr['year'] . '-' . $this->dataArr['month'] . '-' . $this->dataArr['day']) - strtotime('now') > 0)
    {
      $this->errArr['year'] = '正しい日付を入力してください';
    }
  }

  private function zipCheck()
  {
    if(preg_match('/^[0-9]{3}$/', $this->dataArr['zip1']) === 0)
    {
      $this->errArr['zip1'] = '郵便番号の上は半角数字3桁で入力してください';
    }
    if(preg_match('/^[0-9]{4}$/', $this->dataArr['zip2']) === 0)
    {
      $this->errArr['zip2'] = '郵便番号の下は半角数字4桁で入力してください';
    }
  }

  private function addCheck()
  {
    if($this->dataArr['address'] === '')
    {
      $this->errArr['address'] = '住所を入力してください';
    }
  }

  private function mailCheck()
  {
    $res = $this->getMailList();
    if($res === true)
    {
      $this->errArr['email'] = '入力されたメールアドレスは既に登録されています';
    }
    if(preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+[a-zA-Z0-9\._-]+$/', $this->dataArr['email']) === 0)
    {
      $this->errArr['email'] = 'メールアドレスを入力してください';
    }
  }
  
  //会員登録修正用のメールアドレスエラーチェック
  private function mailCheck2()
  {
    $res = $this->getMailList2();
    foreach($res as $key => $val)
    {
      if($val['email'] === $this->dataArr['email'])
      {
        $this->errArr['email'] = '入力されたメールアドレスは既に登録されています';
      }
    }
    if(preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+[a-zA-Z0-9\._-]+$/', $this->dataArr['email']) === 0)
    {
      $this->errArr['email'] = 'メールアドレスを正しい形式で入力してください';
    }
  }
  private function phoneNumberCheck()
  {
    if(preg_match('/^\d{1,11}$/', $this->dataArr['phone_number']) === 0 ||
      strlen($this->dataArr['phone_number']) >= 12)
      {
        $this->errArr['phone_number'] = '電話番号は、半角数字で11桁以内で入力してください';
      }
  }

  //会員登録時のパスワードエラーチェック
  private function passwordCheck()
  {
    if(preg_match('/^\w{8,16}$/', $this->dataArr['password']) === 0)
      {
        $this->errArr['password'] = 'パスワードは8～16文字の半角英数字で入力してください';
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

  //パスワード再設定のエラーチェック
  public function passwordErrorCheck($dataArr)
  {
    $this->dataArr = $dataArr;
    $this->resetPasswordCheck();
    return $this->errArr;
  }

  //パスワード再設定用のエラーチェック
  private function resetPasswordCheck()
  {
    if(preg_match('/^\w{8,16}$/', $this->dataArr['old_password']) === 0)
      {
        $this->errArr['old_password'] = 'パスワードは8～16文字の半角英数字で入力してください';
      }
    if(preg_match('/^\w{8,16}$/', $this->dataArr['new_password']) === 0)
      {
        $this->errArr['new_password'] = 'パスワードは8～16文字の半角英数字で入力してください';
      }
    if($this->dataArr['old_password'] === $this->dataArr['new_password'])
      {
        $this->errArr['new_password'] = '現在のパスワードとは違うパスワードで入力してください';
      }
  }

  //入力されたemaliがデータベースに登録されいたら取得
  private function getMailList()
  {
    $table = ' users ';
    $col = ' email ';
    $where = ' email = ? AND state = ?';
    $arrVal = [$this->dataArr['email'], 0];
    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? true : false;
  }
  //現在登録しているemail以外をデータベースから取得
  private function getMailList2()
  {
    $table = ' users ';
    $col = ' email ';
    $where = ' user_id != ? AND state = ?';
    $arrVal = [$this->dataArr['user_id'], 0];
    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }

  //郵便番号の呼び出し
  public function getPostcode($zip1, $zip2)
  {
  $table = ' postcode ';
  $col = ' pref, city, town ';
  $where = ' zip = ? ';
  $arrVal = [$zip1.$zip2];

  $this->db->setLimitOff(1);
  $res = $this->db->select($table, $col, $where, $arrVal);
  return ($res !== false && count($res) !== 0) ? $res : false;

  }

  //会員登録情報をデータベースに挿入
  public function insRegistData($dataArr)
  {
    $table = ' users ';
    $dataArr['password'] = password_hash($dataArr['password'], PASSWORD_DEFAULT);
    $dataArr['regist_date'] = date('Y-m-d H:i:s');
    return $this->db->insert($table, $dataArr);
  }

  //$user_idの会員登録情報呼び出し
  public function getUserData($user_id)
  {
    $table = ' users ';
    $col = ' * ';
    $where = ' user_id = ? AND state = ?';
    $arrVal = [$user_id, 0];
    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }

  //会員登録の修正内容をデータベースに挿入
  public function insUpdateData($dataArr)
  {
    $table = ' users ';
    $dataArr['update_date'] = date('Y-m-d H:i:s');
    $where = ' user_id = ? AND state = ?';
    $arrVal = [$dataArr['user_id'], 0];
    return $this->db->update($table, $dataArr, $where, $arrVal);
  }

  //user_idのパスワードを取得
  public function getPassword($user_id)
  {
    $table = ' users ';
    $col = ' password ';
    $where = ' user_id = ? AND state = ?';
    $arrVal = [$user_id, 0];

    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }

  //old_passと入力されたパスワードのチェック
  public function oldPasswordCheck($dataArr, $oldPassword)
  {
    if(password_verify($dataArr['old_password'], $oldPassword[0]['password']))
    {
      return true;
    }else{
      return false;
    }
  }
  //パスワードをアップデート
  public function updatePassword($dataArr)
  {
    $table = ' users ';
    $insData['password'] = password_hash($dataArr['new_password'], PASSWORD_DEFAULT);
    $dataArr['update_date'] = date('Y-m-d H:i:s');
    $where = ' user_id = ? AND state = ? ';
    $arrVal = [$dataArr['user_id'], 0];
    return $this->db->update($table, $insData, $where, $arrVal);
  }
  //会員登録情報の削除
  public function deleteMember($user_id)
  {
    $table = ' users';
    $insData = ['delete_date' => date('Y-m-d H:i:s'), 'state' => 1];
    $where = ' user_id = ? AND state = ?';
    $arrVal = [$user_id, 0];
    return $this->db->update($table, $insData, $where, $arrVal);
  }
  //ユーザーの検索キーワード
  public function getKeyword($arrKeyword)
  {
    $where = '';
    $arrVal = [];
    $i = 0;
    foreach($arrKeyword as $key => $val)
    {
      if($key === 'full_name' || $key === 'full_name_kana')
      {
        $where .= ' ' . $key . ' like ? ';
        $arrVal[] = "%$val%";
      }else{
        $where .= ' ' . $key . ' = ? ';
        $arrVal[] = $val;
      }
      if ($i < count($arrKeyword) -1)
      {
        $where .= ' AND ';
      }
      $i++;
    }
    
    return [$where, $arrVal];
  }

  //キーワード該当ユーザー取得
  public function getUserKeywordList($where, $arrVal, $order, $limit, $now_page)
  {
    if($now_page === 1)
    {
      $arrVal[] = 0;
      $arrVal[] = $now_page-1;
      $arrVal[] = $limit;

    }else
    {
      $arrVal[] = 0;
      $arrVal[] = ($now_page-1)*$limit;
      $arrVal[] = $limit;
    }
    $table = ' users ';
    $col = ' * ';
    $where .= ($where !== '') ? ' AND state = ? ' : ' state = ? ';
    $order = $order;
    $limit = ' ?, ? ';
    $this->db->setOrder($order);
    $this->db->setLimitOff($limit);
    $res = $this->db->select2($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }

  //キーワード該当ユーザー数取得
  public function countUserKeywordList($where, $arrVal)
  {
    $table = ' users ';
    $where = ' state = ? ';
    $arrVal = [0];
    $res = $this->db->count($table, $where, $arrVal);
    return $res;
  }

  //登録ユーザー数取得
  public function countUserList()
  {
    $table = ' users ';
    $where = ' state = ? ';
    $arrVal = [0];

    $res = $this->db->count($table, $where, $arrVal);
    return $res;
  }

  //現ページユーザー取得
  public function getSortUserList($order, $limit, $now_page)
  {
    if($now_page === 1)
    {
      $arrVal = [0, $now_page-1, $limit];

    }else
    {
      $arrVal = [0, ($now_page-1)*$limit, $limit];
    }
    $table = ' users ';
    $col = ' * ';
    $where = ' state = ? ';
    $order = $order;
    $limit = ' ?, ? ';
    $this->db->setOrder($order);
    $this->db->setLimitOff($limit);

    $res = $this->db->select2($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }

  //パスワード再設定送信メールアドレスエラーチェック
  public function resetPasswordMailErrorCheck($email)
  {
    if(preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+[a-zA-Z0-9\._-]+$/', $email) === 0)
    {
      $error = 'メールアドレスを入力してください';
      return $error;
    }
  }

  //送信された$emailがuserテーブルに登録されているか確認
  public function getMailList3($email)
  {
    $table = ' users ';
    $col = ' email ';
    $where = ' email = ? AND state = ?';
    $arrVal = [$email, 0];
    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($email !== false && count($res) !== 0) ? $res : false;
  }

  //$emailがpassword_resetsテーブルにあるか確認
  public function resetPasswordUserCheck($email)
  {
    $table = ' password_resets ';
    $col = ' email ';
    $where = ' email = ? ';
    $arrVal = [$email];
    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($email !== false && count($res) !== 0) ? $res : false;
  }

  //再設定用のメールアドレスを送信する
  private function resetPasswordMail($email, $token)
  {

    // 以下、mail関数でパスワードリセット用メールを送信
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");

    $url = "http://localhost/shop/controllers/public/passwordResetForm.php?token={$token}";

    $subject =  'パスワードリセット用URLをお送りします';

    $body = <<<EOD
24時間以内に下記URLへアクセスし、パスワードの変更を完了してください。
{$url}
EOD;

    // mb_send_mailは成功したらtrue、失敗したらfalseを返す
    return $isSent = mb_send_mail($email, $subject, $body);
    
  }

  //password_resetsテーブルに挿入する
  private function insResetPasswordUser($email, $token)
  {
    $table = ' password_resets ';
    $insData['email'] = $email;
    $insData['token'] = $token;
    $insData['token_sent_at'] = date('Y-m-d H:i:s');
    $this->db->insert($table, $insData);
  }

  //password_resetsテーブルに同じ$emailがあれば$tokenを上書き
  private function updateResetPasswordUser($email, $token)
  {
    $table = ' password_resets ';
    $insData['token'] = $token;
    $insData['token_sent_at'] = date('Y-m-d H:i:s');
    $where = ' email = ? ';
    $arrWhereVal = [$email];
    $this->db->update($table, $insData, $where, $arrWhereVal);
  }

  //password_resetsテーブルに$emailがなければ挿入
  public function insResetPasswordRequestTransaction($email)
  {
    $this->db->beginTransaction();
    try{
      $token = bin2hex(random_bytes(32));
      //password_resetsテーブルに同じ$emailがあれば$tokenを上書き
      $this->insResetPasswordUser($email, $token);
      //再設定用のメールアドレスを送信する
      $isSent = $this->resetPasswordMail($email, $token);
      if (!$isSent) throw new \Exception('メール送信に失敗しました。');
      $this->db->commit();
      
    }catch(\PDOException $e)
    {
      $this->db->rollback();
      exit($e->getMessage());
    }
  }
  //password_resetsテーブルに$emailがあればアップデート
  public function updateResetPasswordRequestTransaction($email)
  {
    $this->db->beginTransaction();
    try{
      $token = bin2hex(random_bytes(32));
      $this->updateResetPasswordUser($email, $token);
      $isSent = $this->resetPasswordMail($email, $token);
      if (!$isSent) throw new \Exception('メール送信に失敗しました。');
      $this->db->commit();
    }catch(\PDOException $e)
    {
      $this->db->rollback();
      exit($e->getMessage());
    }
  }

  //password_resetsテーブルに$passwordResetTokenと一致するトークンがあれば取得
  public function passwordResetUser($passwordResetToken)
  {
    $table = ' password_resets ';
    $col = ' token, token_sent_at ';
    $where = ' token = ? ';
    $arrVal = [$passwordResetToken];
    return $this->db->select($table, $col, $where, $arrVal);
  }

  //パスワード再設定用のパスワードエラーチェック
  public function resetPasswordErrorCheck($password, $password_con)
  {
    if(preg_match('/^\w{8,16}$/', $password) === 0)
    {
      $this->errArr['password'] = 'パスワードは8～16文字の半角英数字で入力してください';
    }elseif($password !== $password_con)
    {
      $this->errArr['password'] = '確認用パスワードと同じパスワードを入力してください';
    }
    if(preg_match('/^\w{8,16}$/', $password_con) === 0)
    {
      $this->errArr['password_con'] = 'パスワードは8～16文字の半角英数字で入力してください';
    }
    return $this->errArr;
  }

  //パスワードのアップデート
  public function updateResetPasswordTransaction($password, $passwordResetToken)
  {
    
    $this->db->beginTransaction();
    try{
      //メールアドレス取得
      $res = $this->getResetPasswordUserEmail($passwordResetToken);
      //パスワードのアップデート
      $this->updateResetPassword($password, $res[0]['email']);
      //password_resetsからレコード削除
      $this->detelePasswordResetUser($res[0]['email']);
      $this->db->commit();
    }catch(\PDOException $e)
    {
      $this->db->rollback();
      exit('更新に失敗しました');
    }
  }

  //password_resetsテーブルの$passwordResetTokenと一致するレコードのメールアドレス取得
  private function getResetPasswordUserEmail($passwordResetToken)
  {
    $table = ' password_resets ';
    $col = ' email ';
    $where = ' token = ? ';
    $arrVal = [$passwordResetToken];
    return $this->db->select($table, $col, $where, $arrVal);
  }

  //usersテーブルの$emailと一致するレコードのパスワードのアップデート
  private function updateResetPassword($password, $email)
  {
    $table = ' users ';
    $insData['password'] = password_hash($password, PASSWORD_DEFAULT);
    $insData['update_date'] = date('Y-m-d H:i:s');
    $where = ' email = ? ';
    $arrWhereVal = [$email];
    $this->db->update($table, $insData, $where, $arrWhereVal);
  }

  //password_resetsテーブルの$emaiと一致するレコード削除
  private function detelePasswordResetUser($email)
  {
    $table = ' password_resets ';
    $where = ' email = ? ';
    $arrVal = [$email];
    return $this->db->delete($table, $where, $arrVal);
  }

  //全てのユーザーリストを取得
  public function getAllUserList()
  {
    $table = ' users ';
    $col = ' * ';
    $where = ' state = ? ';
    $arrVal = [0];
    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }
  //$user_idの住所を取得
  public function getUserDeliveryAddress($user_id)
  {
    $table = ' users ';
    $col = ' full_name, zip1, zip2, address, phone_number ';
    $where = ' user_id = ? AND state = ? ';
    $arrVal = [$user_id, 0];
    $res = $this->db->select($table, $col, $where, $arrVal);
    return ($res !== false && count($res) !== 0) ? $res : false;
  }
}