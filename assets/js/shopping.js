$(function(){

  $(document).on('change', '.preview-uploader', function(){
    let elem = this; //操作された要素を取得
    $(elem).parents('.app').find('.delete').css('display', 'block');
    $(elem).parents('.app').find('label').css('display', 'none');
    let fileReader = new FileReader();//ファイルを読み取るオブジェクトを生成
    fileReader.readAsDataURL(elem.files[0]);//ファイルを読み取る
    fileReader.onload = (function () { //ファイル読み取りが完了したら
        let imgTag = `<img src='${fileReader.result}'>`//img要素を生成
        $(elem).parents('.app').find('.preview').html(imgTag)//画像をプレビュー
    });
  })

  $(document).on('click', '.delete', function(){
    let elem = this;
    $(elem).next('.preview').find('img').remove();
    $(elem).css('display', 'none');
    $(elem).nextAll('label').css('display', 'block');
    $(elem).nextAll('label').find('.preview-uploader').val('');
  });

  $(document).on('click', '.delete.up', function(){
    let elem = this;
    let val = $(elem).nextAll('label').find("input[type='hidden']").val();
    $(elem).nextAll('label').css('display', 'block');
    $(elem).nextAll('label').append('<input type="file" name="' + val + '" class="preview-uploader">ファイルを選択');
  });

  $('#selectChart').change(function () {
    //選択したoptionのvalueを取得
    let val = $('option:selected').val();
    //先頭に#を付けてvalueの値をidに変換
    let selectChartId = '#' + val;
    $(selectChartId).removeClass('hide');
    $('canvas').not(selectChartId).addClass('hide');
  });

  $(".menu-trigger").on("click", function(){
    $(".sp-nav").slideToggle();
  });


  $(".sp-nav a").on("click", function(){
    $(".sp-nav").slideToggle();
  });

  $("#slider").slick({

  });


});



