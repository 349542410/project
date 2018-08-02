// function cutOutPic(this, face) {
//   debugger
//   console.log(face);
//   uploadAndCheck(face)
// }

console.log('cut out pic js');
// var currentFace = '';

//         function getInputPicData(face) {
//           console.log(face);
//           let box = document.getElementById('cutPicMain');
//           let img = '';
//           $("#cutPicWrap").show()
//           if (face == 'front') {
//             currentFace = 'front';
//             img = document.getElementById('front_id_img');
//           } else if (face == 'back') {
//             currentFace = 'back'
//             img = document.getElementById('back_id_img');
//           }

//           console.log(img);

//           let file = img.files[0];
//           let url = URL.createObjectURL(file);
//           if (!!document.getElementById('cutOutPic')) {
//             document.getElementById('cutOutPic').src = url;
//           } else {
//             var image = document.createElement("img");
//             image.classList.add("cut-pic-img");
//             image.id = 'cutOutPic';
//             image.file = file;
//             image.src = url;
//             box.appendChild(image);
//           }
//           var reader = new FileReader();
//           reader.onload = (function (aImg) {
//             return function (e) {
//               aImg.src = e.target.result;
//             };
//           })(img);
//           reader.readAsDataURL(file);
//         }

//         $("#cutTargetPic").click(function () {
//           console.log('cut pic');
//           let pic = $('#cutOutPic');
//           pic.on({}).cropper({
//             viewMode: 1,
//             dragMode: 'crop',
//             guides: false
//           });
//         })

//         $('createNewPic').click(function () {
//           console.log('create new pic');
//           var pic = $('#cutOutPic');
//           var cas = pic.cropper('getCroppedCanvas');
//           cas.toBlob(function (e) {
//             //生成Blob的图片格式
//             let url = URL.createObjectURL(e);
//             document.getElementById("cutOutPic").src = url;
//           }, "image/jpeg", 1)
//           pic.cropper('destroy');
//         })

//         $("#finishEdit").click(function () {
//           $("#cutPicWrap").hide()
//           console.log(currentFace);
//         })