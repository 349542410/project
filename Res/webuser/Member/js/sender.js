// 美国地址
function s_send_addr(sendstate,sendcity){
  $('#MainContent_ddProvince').change(
      (function(Cr_Select){
        return function(Cr_Select){
          var sit = $(this).children('option:selected').html();
          if(sit===Cr_Select){             
            $("#Postprovince").attr("value","");
            $("#PostCity").attr("value","");
          }else{
            $("#Postprovince").attr("value",sit);
          } 
      };
    })(Cr_Select)
  );
  if(sendstate != ''){
      $("#MainContent_ddProvince").find("option[value='"+sendstate+"']").prop("selected",true).trigger('change');
  }
  //else{
  //    //$("#MainContent_ddProvince").children('option:selected').html(sendstate);
  //}
  //$("#MainContent_ddCity").change(
  //    (function(Cr_Select){
  //      return function(Cr_Select){
  //        var sit = $(this).children('option:selected').html();
  //        if(sit===Cr_Select){
  //          $("#Postprovince").attr("value","");
  //          $("#Postcity").attr("value","");
  //        }else{
  //          $("#Postcity").attr("value",sit);
  //        }
  //    }
  //  })(Cr_Select)
  //);
  //if(sendcity != ''){
  //    $("#MainContent_ddCity").find("option:contains('"+sendcity+"')").prop("selected",true);
  //    $("#Postcity").attr("value",sendcity);
  //}else{
  //    $("#MainContent_ddCity").children('option:selected').html(sendcity);
  //}
}
// 通过JQform提交
function Senderform(obj){
    $(window).unbind('beforeunload');
    if(!$("#tempForm").validationEngine("validate")) return ;
    $(obj).ajaxForm({
        dataType: 'json',
        beforeSend:function(){
            var index = layer.load(1, {
                shade: [0.1,'#000'], //0.1透明度的白色背景
                offset: '200px',
            });
        },
        success: function(data){
            //console.log(data);
            if(data.success == false){
                layer.closeAll('loading'); //关闭加载层
                layer.open({
                    type: 0,
                    offset: '300px',
                    skin: 'demo-class',//自定义样式demo-class
                    shadeClose: true,
                    title: LAY_SystemMessage,
                    content : data.info,
                    icon : 5,
                });
                return false;
            }else{
                layer.closeAll('loading'); //关闭加载层.
                window.parent.location.href=data.url; //刷新父页面
                var index = parent.layer.getFrameIndex(window.name);  
                parent.layer.close(index);
            }
        }
    });
}

// 查看详细地址
$(".txt").hover(function (){  
    $(this).find('.prompts-02').show();  
},function (){  
    $(this).find('.prompts-02').hide();  
});  
// 添加寄件人信息
function showinfo(obj){
var ta1 = layer.open({
          type: 2,
          shadeClose: false,
          skin: 'demo-class',//自定义样式demo-class
          title: addsentit,
          area : ['800px' , '450px'],
          scrollbar: false,
          resize: false,
          offset: '30%',
          content: addsenderurl,
            success: function(layero, index){
                this.enterEsc = function(event){
                    if(event.keyCode === 13){
                        layer.closeAll();
                        return false; //阻止系统默认回车事件
                    }
                };
                $(document).on('keydown', this.enterEsc);
                //监听键盘事件，关闭层
            },
    });
}
// 删除前的确认框
function del(id,url)
{
    layer.confirm(LAY_MesDel, {icon:3,offset: '400px',title: LAY_Information,btn: [LAY_BtnOK, LAY_BtnCancel],skin: "layermsg"}, function(){
        $.ajax({
            url: url,
            type: "POST",
            data:{"id":id},
            dataType: "json",
            success: function(data){
                if(data.state=='yes'){
                    layer.msg(data.msg, {icon: 6,offset: '400px'});
                    location.href = location.href;
                }else{
                    layer.msg(data.msg, {icon: 5,offset: '400px'});
                }
            }
        });
    });
}

// 编辑收件人信息
function saveinfo(obj){
    var id = obj.id;
    layer.open({
          type: 2,
          shadeClose: false,
          title: l_edit_ri,
          area : ['800px' , '450px'],
          scrollbar: false,
          skin: 'demo-class',//自定义样式demo-class
          resize: false,
          offset: '30%',
          content: savesenderurl+'?id='+id,
            success: function(layero, index){
                this.enterEsc = function(event){
                    if(event.keyCode === 13){
                        layer.closeAll();
                        return false; //阻止系统默认回车事件
                    }
                };
                $(document).on('keydown', this.enterEsc);
                //监听键盘事件，关闭层
            }
    });
}

// 调用 validationEngine
function validatione_init(sele){
  $(sele).validationEngine('attach', {
      promptPosition: 'topRight',
      scroll: false,
      addPromptClass:'formError-white',
      autoPositionUpdate:true,
      'custom_error_messages':{
          '#TransferLine':{
              'required': {
                  'message': valvalEng_Msg_frelng,
              }
          },
          '#loc_province':{
              'required':{
                  'message': valEng_Msg_frelng,
              }
          },
          '#loc_city':{
              'required':{
                  'message': valEng_Msg_frelng,
              }
          },
          '#loc_town':{
              'required':{
                  'message': valEng_Msg_frelng,
              }
          },
      }
  });
}
function empty_shipper(){
    $('#postname').val("");
    $('#poststreet').val("");
    $('#postphone').val("");
    $('#postcode').val("");
    $('#Postcity').val("");
    $("#MainContent_ddProvince").find("option:contains('"+Cr_Select+"')").prop("selected",true).change();
  // $("#MainContent_ddCity").find("option:contains('"+Cr_Select+"')").prop("selected",true);
}