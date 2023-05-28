<?php

//PDOデータベース

namespace models;

class PDODatabase
{
  private $dbh = null;
  private $db_host = '';
  private $db_user = '';
  private $db_pass = '';
  private $db_name = '';
  private $db_type = '';
  private $order = '';
  private $limit = '';
  private $offset = '';
  private $groupby = '';

  public function __construct($db_host, $db_user, $db_pass, $db_name, $db_type)
  {
    // dbh　データベースハンドラー
    $this->dbh = $this->connectDB($db_host, $db_user, $db_pass, $db_name, $db_type);
    $this->db_host = $db_host;
    $this->db_user = $db_user;
    $this->db_pass = $db_pass;
    $this->db_name = $db_name;

    //SQL関連
    $this->order = '';
    $this->limit = '';
    $this->offset = '';
    $this->groupby = '';
  }

  private function connectDB($db_host, $db_user, $db_pass, $db_name, $db_type)
  {
    try{
      switch($db_type)
      {
        case 'mysql':
          $dsn = 'mysql:host=' . $db_host . ';dbname=' . $db_name;
          
          //PDOはPHPに標準であるクラス
          $dbh = new \PDO($dsn, $db_user, $db_pass);
          //PDOクラスにあるqueryメソッド
          $dbh->query('SET NAMES utf8');
          break;
        
        case 'pgsql':
          $dsn = 'pgsql:dbname=' . $db_name . ' host=' . $db_host . ' port=5432';
          $dbh = new \PDO($dsn, $db_user, $db_pass);
          break;
      }
    }catch(\PDOException $e)
    {
      var_dump($e->getMessage());
      exit();
    }
    return $dbh;
  }

  public function setQuery($query = '', $arrVal = [])
  {
    $stmt = $this->dbh->prepare($query);
    $stmt->execute($arrVal);
  }

  public function select($table, $column = '', $where = '', $arrVal = [])
  {
    // SQL文を作成
    $sql = $this->getSql('select', $table, $where, $column);
    
    //SQLのログを残す
    $this->sqlLogInfo($sql, $arrVal);
    $stmt = $this->dbh->prepare($sql);
    $res = $stmt->execute($arrVal);

    if($res === false)
    {
      
      //errorInfo() 直前に問い合わせたクエリのエラー内容が配列で返されるPDOオブジェクトのメソッド
      //[0] => SQLSTATE [1] => エラーコード [2] => エラー内容 なければNULL
      //エラーの内容を引数で渡す
      $this->catchError($stmt->errorInfo());
    }
    //データを連想配列に格納
    $data = [];
    //$stmtの実行結果をfetchメソッドで取得 fetchはデフォルトFETCH_BOTH
    //FETCH_ASSOCは連想配列だけ取得してくる
    while($result = $stmt->fetch(\PDO::FETCH_ASSOC))
    {
      array_push($data, $result);
    }
    
    return $data;
    
  }

  public function select2($table, $column = '', $where = '', $arrVal = [])
  {
    // SQL文を作成
    $sql = $this->getSql2('select', $table, $where, $column);

    //SQLのログを残す
    $this->sqlLogInfo($sql, $arrVal);
    $stmt = $this->dbh->prepare($sql);
    foreach($arrVal as $key => $val)
    {
      if(is_int($val))
      {
        $stmt->bindvalue($key+1, $val, \PDO::PARAM_INT);
      }elseif(is_string($val))
      {
        $stmt->bindvalue($key+1, $val, \PDO::PARAM_STR);
      }
    }
    $res = $stmt->execute();

    if($res === false)
    {
      
      //errorInfo() 直前に問い合わせたクエリのエラー内容が配列で返されるPDOオブジェクトのメソッド
      //[0] => SQLSTATE [1] => エラーコード [2] => エラー内容 なければNULL
      //エラーの内容を引数で渡す
      $this->catchError($stmt->errorInfo());
    }
    //データを連想配列に格納
    $data = [];
    //$stmtの実行結果をfetchメソッドで取得 fetchはデフォルトFETCH_BOTH
    //FETCH_ASSOCは連想配列だけ取得してくる
    while($result = $stmt->fetch(\PDO::FETCH_ASSOC))
    {
      array_push($data, $result);
    }
    return $data;
    
  }

  public function count($table, $where = '', $arrVal = [])
  {
    //SQL文作成メソッド
    $sql = $this->getSql('count', $table, $where);
    
    $this->sqlLogInfo($sql, $arrVal);
    $stmt = $this->dbh->prepare($sql);

    $res = $stmt->execute($arrVal);

    if($res === false)
    {
      $this->catchError($stmt->errorInfo());
    }
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);

    return intval($result['NUM']);
  }

  public function delete($table, $where = '', $arrVal = [])
  {
    //SQL文作成メソッド
    $sql = ' delete FROM ' . $table . ' WHERE ' . $where;
    
    $this->sqlLogInfo($sql, $arrVal);
    $stmt = $this->dbh->prepare($sql);
    $res = $stmt->execute($arrVal);

    if($res === false)
    {

      $this->catchError($stmt->errorInfo());
    }
    return $res;
  }


  public function setOrder($order = '')
  {
    if($order !== '')
    {
      $this->order = ' ORDER BY ' . $order;
    }
  }

  public function setLimitOff($limit = '', $offset = '')
  {
    if($limit !== "")
    {
      $this->limit = " LIMIT " . $limit;
    }
    if($offset !== "")
    {
      $this->offset = " OFFSET " . $offset;
    }
  }

  public function setGroupBy($groupby)
  {
    if($groupby !== "")
    {
      $this->groupby = ' GROUP BY ' . $groupby;
    }
  }

  private function getSql($type, $table, $where = '', $column = '')
  {
    switch($type)
    {
      case 'select':
        $columnKey = ($column !== '') ? $column : "*";
        break;

      case 'count':
        $columnKey = ' COUNT(*) AS NUM ';
        break;
      
      default:
        break;
    }

    $whereSQL = ($where !== '') ? ' WHERE ' . $where : '';

    //sql文の作成
    $sql = "select" . $columnKey . "from" . $table . $whereSQL ;
    return $sql;
  }

  private function getSql2($type, $table, $where = '', $column = '')
  {
    switch($type)
    {
      case 'select':
        $columnKey = ($column !== '') ? $column : "*";
        break;

      case 'count':
        $columnKey = 'COUNT(*) AS NUM';
        break;
      
      default:
        break;
    }

    $whereSQL = ($where !== '') ? ' WHERE  ' . $where : '';
    $other = $this->groupby . "  " . $this->order . "  " . $this->limit . "  " . $this->offset;

    //sql文の作成
    $sql = "select" . $columnKey . "from" . $table . $whereSQL . $other;
    return $sql;
  }

  public function insert($table, $insData = [])
  {
    $insDataKey = [];
    $insDataVal = [];
    $preCnt = [];

    $columns = '';
    $preSt = '';

    foreach($insData as $col => $val)
    {
      $insDataKey[] = $col;
      $insDataVal[] = $val;
      $preCnt[] = '?';
    }

    $columns = implode(",", $insDataKey);
    $preSt = implode(",", $preCnt);

    $sql = "insert into"
            . $table
            . "("
            . $columns
            . ") values ("
            . $preSt
            . ") ";
    

    //SQLのエラーログを残す
    $this->sqlLogInfo($sql, $insDataVal);
    $stmt = $this->dbh->prepare($sql);
    $res = $stmt->execute($insDataVal);

    if($res === false)
    {
      $this->catchError($stmt->errorInfo());
    }
    return $res;
  }

  public function update($table, $insData = [], $where, $arrWhereVal = [])
  {
    $arrPreSt = [];
    
    foreach($insData as $col => $val)
    {
      $arrPreSt[] = $col . " = ? ";
    }

    $preSt = implode(',', $arrPreSt);

    //SQL文の作成
    $sql = "update"
          . $table
          . " SET "
          . $preSt
          . "where"
          . $where;

    //array_merge 複数の配列を結合する関数
    //array_values()insDataのバリュー値だけ取得する
    $updateData = array_merge(array_values($insData), $arrWhereVal);

    $this->sqlLogInfo($sql, $updateData);
    $stmt = $this->dbh->prepare($sql);
    $res = $stmt->execute($updateData);

    if($res === false)
    {

      $this->catchError($stmt->errorInfo());
    }
    return $res;
  }

  public function getLastId()
  {
    //PDO lastInsertId() 最後のINSERTで挿入された行の連番IDを取得する機能
    return $this->dbh->lastInsertId();
  }

  public function beginTransaction()
  {
    return $this->dbh->beginTransaction();
  }

  public function commit()
  {
    return $this->dbh->commit();
  }

  public function rollback()
  {
    return $this->dbh->rollback();
  }

  private function catchError($errArr = [])
  {

    $errMsg = (!empty($errArr[2]))? $errArr[2]:"";
    //die 文字列を表示して終了させる関数
    die("SQLエラーが発生しました。" . $errArr[2]);
  }
  //エラーログファイルの確認
  private function makeLogFile()
  {
    $logDir = dirname(__DIR__) . "/logs";

    //file_exists関数 ファイルがあるか調べる
    //$logDirのフォルダがあるか確認
    if(!file_exists($logDir))
    {
      // mkdirディレクトリ作成 0777はファイルに関する権限付与している
      mkdir($logDir, 0777);
    }

    $logPath = $logDir . '/shopping.log';
    //ファイルがあるか確認
    if(!file_exists($logPath))
    {
      //ファイルを作成
      touch($logPath);
    }
    return $logPath;
  }

  //$srtはSQL文 $arrValはバインドする値
  private function sqlLogInfo($str, $arrVal = [])
  {
    //ファイルの存在確認
    $logPath = $this->makeLogFile();
    // $arrValはバインドする値
    $logData = sprintf("[SQL_LOG:%s]: %s [%s]\n", date('Y-m-d H:i:s'), $str, implode(",", $arrVal));
    //error_log 指定したファイルにエラーの内容を送信する関数 第一引数エラーメッセージ 第二引数メッセージタイプ 第三引数 ファイル
    error_log($logData, 3, $logPath);
  }

}