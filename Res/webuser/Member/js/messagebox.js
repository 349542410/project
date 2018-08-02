// id 删除的id
// url 处理删除方法的地址
function toDel(id,url,pub){

    ds.dialog({
       title : '操作确认',
       content : '删除后不可恢复，确定吗?',
       yesText : '确定',
       onyes:function(){

          $.ajax({
            url:url,
            type:'post',
            data:{
              'id':id,
            },
            dataType:'json',
            beforeSend:loading(),
            success:function(result){

              if(result.state == 'no'){   //删除失败

                //提示信息
                ds.dialog({
                   title : '消息提示',
                   content : result.msg,
                   icon : pub+"/images/error.png",
                });
                
              }else{                   //删除成功

                //提示信息
                ds.dialog({
                   title : '消息提示',
                   content : result.msg,
                   icon : pub+"/images/success.png",
                   onyes:function(){
                    $("#tr_"+id).fadeOut();
                     // $("#tr_"+id).fadeOut('slow',function(){
                     //  window.location=window.location;
                     // });
                   }
                });

              }
              $('#loaded').html('');
            }
          });
       },
       noText : '取消',
       onno : function(){
         this.close();
       },
       icon : pub+"/images/question.gif",
    });

function loading(){

  $('#loaded').html('<img src="'+pub+'/images/loading.gif" alt="" width="20" height="20">');

}

  // if(confirm("删除后不可恢复，确定吗?")){
  //   $.ajax({
  //     url:url,
  //     type:'post',
  //     data:{
  //       'id':id,
  //     },
  //     dataType:'json',
  //     success:function(result){
        
  //       $('#messagebox').css("visibility","visible");

  //       if(result.state == 'no'){   //删除失败

  //         //提示信息
  //         // $('#messagebox').html('<font style="color:red;font-size:16px;">'+result.msg+'</font>').fadeIn(300).delay(1000).fadeOut(300,function(){
  //         //   // window.location=window.location;
  //         // });
  //         jError(result.msg,{
  //           clickOverlay : true,
  //           autoHide : false,
  //           LongTrip : 100,
  //           VerticalPosition : 'top',
  //           HorizontalPosition : 'center'
  //         });
  //         // alert(result.msg);
          
  //       }else{                    //删除成功

  //         //提示信息
  //         // $('#messagebox').html('<font style="color:black;font-size:16px;">'+result.msg+'</font>').fadeIn(300).delay(1000).fadeOut(300);

  //         // $("#tr_"+id).fadeOut();

  //         // $("#tr_"+id+"_2").fadeOut();
          
  //         jSuccess(result.msg, {
  //           TimeShown : 800,
  //               VerticalPosition : 'top',
  //               HorizontalPosition : 'center'
  //         });
  //         $("#tr_"+id).fadeOut();
  //         // $('#tt')
  //       }
        
  //     }
  //   });
  // }

}