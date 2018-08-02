/**
 * [整个表单ajax方式提交]
 * 要确保项目已经加载jq文件和jquery.form.js文件才可使用
 */
//将form转为AJAX提交
function ajaxSubmit(frm, fn) {
    var dataPara = getFormJson(frm);
    $.ajax({
        url: frm.action,
        type: frm.method,
        data: dataPara,
        beforeSend:function(){
            layer.load(0, {shade: [0.1,'#000']}); 
        },
        success: fn
    });
}

//将form中的值转换为键值对。
function getFormJson(frm) {
    var o = {};
    var a = $(frm).serializeArray();
    $.each(a, function () {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });

    return o;
}

//添加或修改时调用
$(document).ready(function(){
    

    $('#tempForm').bind('submit', function(){

        var type = $('#tempForm').attr('name');

        if(type == 'operator'){
            var b=document.getElementsByName('usertype');
            var sum;
            for(var i=0;i<b.length;i++){
              if(b[i].checked==true){
                sum=1;
              };
            }
            if(!sum){
              alert("请必须选择一个权限类型");
              return false;
            }
        }else if(type == 'express'){
            var fixed = document.getElementById('fixed').value;
            var start = document.getElementById('start').value;
            var end = document.getElementById('end').value;
            var ll = document.getElementById('ll').value;
            
            if(confirm('本次将要新增单号的形式为：'+fixed+'******，编号从'+start+'到'+end+'，每个单号的总长度均为 '+ll+' 个字节，确定吗？')){

            }else{
              return false;
            }
        }else if(type == "pause"){
            if(confirm('确定要执行暂停吗?')){

            }else{
              return false;
            }
        }else if(type == 'recover'){
            if(confirm('确定要执行恢复吗?')){

            }else{
              return false;
            }
        }else if(type == 'edit'){
            if(confirm('确定要执行修改吗?')){

            }else{
              return false;
            }
        }else if(type == 'delete'){
            if(confirm('确定要执行删除吗?')){

            }else{
              return false;
            }
        }else if(type == 'addNew'){
            if(confirm('确定添加吗?')){

            }else{
              return false;
            }
        }
        if(!$("#tempForm").validationEngine("validate")) return ; 
        ajaxSubmit(this, function(result){

            if(result.auth_prompt == 'yes'){
              alert(result.msg);exit;
            }

            $("#lines").attr("value","");//清除隐藏域中的值
            
            if (result.state == 'yes') {
                //提示信息
                $('#messagebox').html('<font style="color:green;font-size:16px;">'+result.msg+'</font>').fadeIn(300).delay(1000).fadeOut(300,function(){
                    layer.closeAll('loading');
                    $('#beClosed').click();                //模拟用户点击关闭
                    window.location=window.location;       //刷新页面
                });
                // alert(result.msg);

            }
            else {
                $('#messagebox').html('<font style="color:red;font-size:16px;">'+result.msg+'</font>').fadeIn(1000);
                layer.closeAll('loading');
                // alert(result.msg);
            }

        }); 
        return false;
    });
});