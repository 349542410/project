// id 删除的id
// url 处理删除方法的地址
function toDel(id,url){
  if(confirm('确定删除?')){
    $.ajax({
      url:url,
      type:'post',
      data:{
        'id':id,
      },
      dataType:'json',
      success:function(result){
        
        $('#messagebox').css("visibility","visible");

        if(result.auth_prompt == 'yes'){
          alert(result.msg);exit;
        }

        if(result.state == 'no'){   //删除失败

          //提示信息
          $('#messagebox').html('<font style="color:red;font-size:16px;">'+result.msg+'</font>').fadeIn(300).delay(1000).fadeOut(300,function(){
            // window.location=window.location;
          });

          // alert(result.msg);
          
        }else{                    //删除成功

          //提示信息
          $('#messagebox').html('<font style="color:black;font-size:16px;">'+result.msg+'</font>').fadeIn(300).delay(1000).fadeOut(300);

          $("#tr_"+id).fadeOut();

          $("#tr_"+id+"_2").fadeOut();
          // $('#tt')
        }
        
      }
    });
  }

}

//================= 暂时没用 =======
//订单暂停
function toPause(id,mkno,url){
    if(confirm('确定要暂停此单号吗?')){

      $.ajax({
        url:url,
        type:'post',
        data:{
          'id':id,
          'mkno':mkno,
        },
        dataType:'json',
        success:function(result){
          
          $('#messagebox').css("visibility","visible");

          if(result.state == 'no'){   //暂停失败

            //提示信息
            $('#messagebox').html('<font style="color:red;font-size:16px;">'+result.msg+'</font>').fadeIn(300).delay(1000).fadeOut(300,function(){
              // window.location=window.location;
            });

            // alert(result.msg);
            
          }else{                    //暂停成功

            //提示信息
            $('#messagebox').html('<font style="color:black;font-size:16px;">'+result.msg+'</font>').fadeIn(300).delay(1000).fadeOut(300,function(){
              $('#beClosed').click();                //模拟用户点击关闭
              window.location=window.location;
            });

          }
          
        }
      });
    }
}

//订单恢复
function toRecover(id,mkno,url){
    if(confirm('确定要恢复此单号吗?')){

      $.ajax({
        url:url,
        type:'post',
        data:{
          'id':id,
          'mkno':mkno,
        },
        dataType:'json',
        success:function(result){
                                //能见度     //可见
          $('#messagebox').css("visibility","visible");

          if(result.state == 'no'){   //恢复失败

            //提示信息
            $('#messagebox').html('<font style="color:red;font-size:16px;">'+result.msg+'</font>').fadeIn(300).delay(1000).fadeOut(300,function(){
              // window.location=window.location;
            });

            // alert(result.msg);
            
          }else{                    //恢复成功

            //提示信息
            $('#messagebox').html('<font style="color:black;font-size:16px;">'+result.msg+'</font>').fadeIn(300).delay(1000).fadeOut(300,function(){
              $('#beClosed').click();                //模拟用户点击关闭
              window.location=window.location;
            });

          }
          
        }
      });
    }
}