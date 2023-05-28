<?php

//商品登録、更新、削除

namespace models;

class ItemRegist
{
  public $db = null;
  private $dataArr = [];
  private $errArr = [];

  public function __construct($db)
  {
    $this->db = $db;
  }

  public static function getCategory()
  {
    $cateArr = [
      1 => 'OUTER',
      2 => 'TOPS',
      3 => 'BOTTOMS',
      4 => 'DRESS',
      5 => 'SHOES',
      6 => 'BAG',
    ];
    return $cateArr;
  }

  //商品登録エラーチェック
  public function errorCheck($dataArr)
  {
    $this->dataArr = $dataArr;

    $this->createErrorMessage();

    $this->itemNameCheck();
    $this->detailCheck();
    $this->priceCheck();
    $this->imageCheck();
    $this->colorCheck();
    $this->sizeCheck();

    return $this->errArr;

  }

  //商品更新エラーチェック
  public function updateDetailErrorCheck($dataArr)
  {
    $this->dataArr = $dataArr;

    $this->createErrorMessage();

    $this->itemNameCheck();
    $this->detailCheck();
    $this->priceCheck();

    return $this->errArr;

  }
  public function addSkuErrorCheck($dataArr)
  {
    $this->dataArr = $dataArr;

    $this->createErrorMessage();

    $this->colorCheck();
    $this->sizeCheck();

    return $this->errArr;
  }

  private function createErrorMessage()
  {
    foreach($this->dataArr as $key => $val)
    {
      $this->errArr[$key] = '';
    }
  }

  private function itemNameCheck()
  {
    if(trim($this->dataArr['item_name']) === '')
    {
      $this->errArr['item_name'] = '商品名を入力してください';
    }
  }
  private function detailCheck()
  {
    if(trim($this->dataArr['detail']) === '')
    {
      $this->errArr['detail'] = '商品説明を入力してください';
    }
  }
  private function priceCheck()
  {
    if(trim($this->dataArr['unit_price']) === '' || preg_match('/^[0-9]+$/', $this->dataArr['unit_price']) === 0)
    {
      $this->errArr['unit_price'] = '商品価格を半角数字で入力してください';
    }
  }
  private function imageCheck()
  {
    if($_FILES['1-1']['name'] === '')
    {
      $this->errArr['image'] = '画像を1つ以上登録してください';
    }else{
      foreach($_FILES as $key => $val)
      {
        if($val['name'] !== '')
        $tmp_image[$key] = $val;
      }
      $imageError = $this->imageErrorCheck($tmp_image);
      $imageSize = $this->imageSizeCheck($tmp_image);
      if($imageError === true && $imageSize === true)
      {
        foreach($tmp_image as $val)
        {
          if(is_uploaded_file($val['tmp_name']) === true)
          {
            $image_info = getimagesize($val['tmp_name']);
            $image_mime = $image_info['mime'];
            if($val['size'] > 1048576)
            { 
              $this->errArr['image'] = 'アップロードできる画像のサイズは、1MBまでです';
            }elseif(preg_match('/^image\/jpeg$/', $image_mime) === 0)
            { 
              $this->errArr['image'] = 'アップロードできる画像の形式は、JPEG形式だけです';
            }
          }
        }
      }
    }
  }

  private function imageErrorCheck($tmp_image)
  {
    $imageError = true;
    foreach($tmp_image as $val)
    {
      if($val['error'] !== 0)
      {
        $imageError = false;
      }
    }
    return $imageError;
  }

  private function imageSizeCheck($tmp_image)
  {
    $imageSize = true;
    foreach($tmp_image as $val)
    {
      if($val['size'] === 0)
      {
        $imageSize = false;
      }
    }
    return $imageSize;
  }
  private function colorCheck()
  {
    if(trim($this->dataArr['color']) === '' || preg_match('/^[a-zA-Z]+$/sm', $this->dataArr['color']) === 0)
    {
      $this->errArr['color'] = 'カラーを半角英字で入力してください';
    }
  }
  private function sizeCheck()
  {
    if(trim($this->dataArr['size']) === '' || preg_match('/^[a-zA-Z0-9|0-9\.0-9]+$/sm', $this->dataArr['size']) === 0)
    {
      $this->errArr['size'] = 'サイズを半角英字入力してください';
    }
  }

  public function getErrorFlg()
  {
    $errCheck = true;
    foreach($this->errArr as $key => $value)
    {
      if($value !== '')
      {
        $errCheck = false;
      }
    }
    return $errCheck;
  }

  public function insItemImageSkuRegist($dataArr, $colorArr, $sizeArr)
  {
    $this->db->beginTransaction();
    try{
      $tmp_image = $_FILES;
      $this->uploadImage($tmp_image);
      $this->insItemRegist($dataArr);
      
      $item_id = $this->db->getLastId();
      foreach($colorArr as $color)
      {
        foreach($sizeArr as $size)
        {
          $this->insSkuRegist($item_id, $color, $size);
        }
      }
      foreach($tmp_image as $key => $val)
      {
        $this->insImageRegist($item_id, $key, $val['name']);
      }

      $this->db->commit();
      return true;

    }catch(\PDOException $e)
    {
      $this->db->rollback();
      return false;
    }
  }

  private function uploadImage($tmp_image)
  {
    foreach($tmp_image as $val)
    {
      move_uploaded_file($val['tmp_name'], 'c:/xampp/htdocs/shop/assets/images/' . $val['name']);
    }
  }

  private function insItemRegist($dataArr)
  {
    unset($dataArr['color'], $dataArr['size']);
    
    $table = ' items ';
    $dataArr['regist_date'] = date('Y-m-d H:i:s');
    $this->db->insert($table, $dataArr);
  }

  private function insSkuRegist($item_id, $color, $size)
  {
    $table = ' skus ';
    $dataArr['sku_code'] = $item_id .'_'. $color .'_'. $size;
    $dataArr['item_id'] = $item_id;
    $dataArr['color'] = $color;
    $dataArr['size'] = $size;
    $dataArr['regist_date'] = date('Y-m-d H:i:s');
    $this->db->insert($table, $dataArr);
  }

  private function insImageRegist($item_id, $item_image_id, $image)
  {
    $table = ' images ';
    $dataArr['item_id'] = $item_id;
    $dataArr['item_image_id'] = $item_image_id;
    $dataArr['image'] = $image;
    $dataArr['regist_date'] = date('Y-m-d H:i:s');
    $this->db->insert($table, $dataArr);
  }

  //商品詳細の更新
  public function updateDetail($item_id, $dataArr)
  {
    $table = ' items ';
    $where = ' item_id = ? ';
    $arrVal = [$item_id];
    $dataArr['update_date'] = date('Y-m-d H:i:s');
    return $this->db->update($table, $dataArr, $where, $arrVal);
  }

  //skuの削除
  public function deleteSku($sku_code)
  {
    $table = ' skus ';
    $where = ' sku_code = ? ';
    $arrVal = [$sku_code];
    $dataArr['state'] = 1;
    $dataArr['delete_date'] = date('Y-m-d H:i:s');
    return $this->db->update($table, $dataArr, $where, $arrVal);
  }

  public function addSkuRegist($item_id, $colorArr, $sizeArr)
  {
    $this->db->beginTransaction();
    try
    {
      foreach($colorArr as $color)
      {
        foreach($sizeArr as $size)
        {
          $res[] = $this->insSkuRegist($item_id, $color, $size);
        }
      }
    $this->db->commit();
    return true;

    }catch(\PDOException $e)
    {
      $this->db->rollback();
      return false;
    }
  }
  //登録画像更新時エラーチェック
  public function updateimageCheck()
  {
    foreach($_FILES as $key => $val)
    {
      if($val['name'] !== '')
      $tmp_image[$key] = $val;
    }
    if(isset($tmp_image))
    {
      $imageError = $this->imageErrorCheck($tmp_image);
      $imageSize = $this->imageSizeCheck($tmp_image);
      if($imageError === true && $imageSize === true)
      {
        foreach($tmp_image as $val)
        {
          if(is_uploaded_file($val['tmp_name']) === true)
          {
            $image_info = getimagesize($val['tmp_name']);
            $image_mime = $image_info['mime'];
            if($val['size'] > 1048576)
            { 
              $this->errArr['image'] = 'アップロードできる画像のサイズは、1MBまでです';
            }elseif(preg_match('/^image\/jpeg$/', $image_mime) === 0)
            { 
              $this->errArr['image'] = 'アップロードできる画像の形式は、JPEG形式だけです';
            }
          }
        }
      }
      return $this->errArr;
    }else{
      return $this->errArr;
    }
    
  }
  public function updateImageTransaction($item_id)
  {
    $this->db->beginTransaction();
    try{
      $tmp_image = $_FILES;
      $this->uploadImage($tmp_image);

      foreach($tmp_image as $key => $val)
      {
        $this->updateImage($item_id, $key, $val['name']);
      }

      $this->db->commit();
      return true;

    }catch(\PDOException $e)
    {
      $this->db->rollback();
      return false;
    }
  }
  private function updateImage($item_id, $item_image_id, $image)
  {
    $table = ' images ';
    $insData['image'] = $image;
    $insData['update_date'] = date('Y-m-d H:i:s');
    $where = ' item_id = ? AND item_image_id = ? ';
    $arrWhereVal = [$item_id, $item_image_id];
    $this->db->update($table, $insData, $where, $arrWhereVal);
  }
  public function deleteImage($imageArr)
  {
    for($i = 0; $i < count($imageArr); $i++)
    {
      $imgPath = "c:/xampp/htdocs/shop/assets/images/" . $imageArr[$i];
      if(file_exists($imgPath))
      {
        unlink($imgPath);
      }
    }
  }
}