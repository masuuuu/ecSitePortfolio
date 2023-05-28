<?php

namespace models;

class InitMaster
{
  public static function getDate()
  {
    $monthArr = [];
    $dayArr = [];

    //date(日付/時刻フォーマット, UNIXタイムスタンプ) 第二引数で指定したUNIXタイムスタンプの日時を第一引数のフォーマットで返す
    //第二引数は指定しなければ現在時刻
    //第一引数フォーマットY は年4桁の数字
    $next_year = date('Y') + 1;

    //年を作成 セレクトタグの中身
    for($i = 1920; $i < $next_year; $i ++)
    {
      //%d 10進数の整数 04は4桁3桁で渡ってきたら0で埋めてくれる
      $year = sprintf("%04d", $i);
      $yearArr[$year] = $year . '年';
    }

    //月を作成
    for($i = 1; $i < 13; $i ++)
    {
      $month = sprintf("%02d", $i);
      $monthArr[$month] = $month . '月';
    }

    //日を作成
    for($i = 1; $i < 32; $i ++)
    {
      $day = sprintf("%02d", $i);
      $dayArr[$day] = $day . '日';
    }
    return[$yearArr, $monthArr, $dayArr];
  }
  
  public static function getSex()
  {
    $sexArr = [
      '1' => '男性',
      '2' => '女性'
    ];
    return $sexArr;
  }
}
