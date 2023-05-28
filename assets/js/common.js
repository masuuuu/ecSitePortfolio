$(function(){
  $('#address_search').click(function(){
    var zip1 = $('#zip1').val();
    var zip2 = $('#zip2').val();

    var entry_url = $('#entry_url').val();

    if(zip1.match(/[0-9]{3}/) === null || zip2.match(/[0-9]{4}/) === null)
    {
      alert('正確な郵便番号を入力してください');
      return false; //ページ遷移をしない
    }else{
      // ajax()が実行されたら実行、後続関数
      $.ajax({
        type : "get",//通信方法しますよ宣言
        url : entry_url + "public/postcode_search.php?zip1=" + escape(zip1) + "&zip2=" + escape(zip2), //URL先に送る?以降の内容GET通信で
        //escape エスケープ処理 無害化して16進数にエンコーディング（変換）した文字列を返す
        //ブラウザの差異が大きく、非推奨のため取り消し線が入ってる
      }).then(
        //dataに受け取ったデータが入ってくる
        //ajax()内のdataとはまた別のものなので注意

        //resolved（通信が成功）のとき
        function(data)
        {
          if(data == 'no' || data == '')
          {
            alert('該当する郵便番号がありません');
          }else{
            $('#address').val(data);
          }
        },
        // rejected（通信が失敗）のとき
        function(data)
        {
          alert('読み込みに失敗しました')
        },
      );
    }
  });
});
