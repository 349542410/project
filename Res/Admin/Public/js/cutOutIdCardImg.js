var box = document.getElementById('cutOutIdCardMain');
$(document).ready(function () {
  $('#cutOutIdCardWrap').hide();
});

function cutOutIdCardImg() {
  $('#cutOutIdCardWrap').show();
  var pic = document.getElementById("pic");
  let file = pic.files[0];
  let url = URL.createObjectURL(file);
  if (!!document.getElementById('cutOutIdCardImg')) {
    $('#cutOutIdCardImg').src = url;
    $('#cutOutIdCardImg').file = file;
  } else {
    let image = document.createElement("img");
    image.classList.add("cut-out-idcard-img");
    image.id = 'cutOutIdCardImg';
    image.file = file;
    image.src = url;
    document.getElementById('cutOutIdCardMain').appendChild(image);
  }
  var reader = new FileReader();
  reader.onload = (function (aImg) {
    return function (e) {
      aImg.src = e.target.result;
    };
  })(pic);
  reader.readAsDataURL(file);
}

function cutIdcardPic() {
  let img = $('#cutOutIdCardImg')
  img.on({}).cropper({
    viewMode: 3,
    dragMode: 'crop',
    guides: false
  });
}

function createNewImg() {
  let img = $('#cutOutIdCardImg')
  var cas = img.cropper('getCroppedCanvas');
  cas.toBlob(function (e) {
    //生成Blob的图片格式
    if (!!document.getElementById('newImg')) {
      document.getElementById('newImg').src = URL.createObjectURL(e);
    } else {
      var newImg = document.createElement("img");
      let url = URL.createObjectURL(e);
      document.getElementById("cutOutIdCardImg").src = url;
      newImg.src = url;
      newImg.id = 'newImg';
      newImg.classList.add("image");
    }
  }, "image/jpeg", 1)
  img.cropper('destroy');
}

function finishEditImg() {
  $('#cutOutIdCardWrap').hide();
  document.getElementById('cutOutIdCardMain').removeChild(document.getElementById('newImg'))
}