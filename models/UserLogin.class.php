<?php

//ユーザーログイン、ログアウト

namespace models;

class UserLogin
{
  public $db = null;
  private $dataArr = [];
  private $errArr = [];

  public function __construct($db)
  {
    session_start();

    $this->db = $db;
  }

  //ログイン時エラーチェック
  public function errCheck($dataArr)
  {
    $this->dataArr = $dataArr;
    $this->mailCheck();
    $this->passwordCheck();

    return $this->errArr;
  }

  private function mailCheck()
  {
    if($this->dataArr['email'] === '')
    {
      $this->errArr['email'] = 'メールアドレスを入力してください';
    }
  }
  private function passwordCheck()
  {
    if(preg_match('/^\w{8,16}$/', $this->dataArr['password']) === 0)
    {
      $this->errArr['password'] = 'パスワードは8～16文字の半角英数字で入力してください';
    }
  }

  //入力されたemailから一致するemail,passを取得
  public function getUserData()
  {
    $table = ' users ';
    $col = ' user_id, email, password ';
    $where = ' email = ? AND state = ?';
    $arrVal = [$this->dataArr['email'], 0];

    $userData = $this->db->select($table, $col, $where, $arrVal);
    return $userData;
  }

  //ログインのチェック
  public function loginCheck($userData)
  {
    if(count($userData) !== 0) //入力されたemail,passを照合して登録情報から取得してこれた場合
    {
      //パスワードが正しいか判定
      if(password_verify($this->dataArr['password'], $userData[0]['password']) === false)
      {
        $this->errArr['password'] = '正しいパスワードを入力してください';
      }
    }else
      {
        //登録情報と一致するemailが取得できなかった場合
        $this->errArr['email'] = '正しいメールアドレスを入力してください';
      }

      return $this->errArr;
  }
  
  //リキャプチャーのチェック
  public function recapchaCheck($recaptchaResponse, $userData)
  {
    if (isset($recaptchaResponse) && !empty($recaptchaResponse))
    {
      $secret = "6Lc11D4lAAAAAPvATmCwWBnAJWdMAgTUStZaK-jL";
      $verifyResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$recaptchaResponse);
      $reCAPTCHA = json_decode($verifyResponse);
      if ($reCAPTCHA->success)
      {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userData[0]['user_id'];
        //ログイン成功時はショップページへ
        header('Location:http://localhost/shop/controllers/public/itemList.php');
        exit;
      }
      else
      {
        $this->errArr['recaptcha'] = "認証エラー";
      }
    }
    else
    {
      $this->errArr['recaptcha'] = "認証エラー";
    }
    return $this->errArr;
  }

  //ログアウト時のメソッド
  public function logout()
  {
    $_SESSION = [];
    session_destroy();
  }

  //トークン作成
  public function createToken()
  {
    $token = bin2hex(random_bytes(32));
    $_SESSION['token'] = $token;
    return $token;
  }

}
