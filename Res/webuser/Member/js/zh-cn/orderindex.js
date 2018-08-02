//选择收件人按钮
function infoselect(obj){
    layer.open({
        type: 2,
        title: l_cho_rec,
        area : ['800px' , '500px'],
        // offset: '20%',
        closeBtn: 1,
        content: selectRecipient,
    });
}
// 选择收件人数据
function get_addrinfo(addr_id){
    //alert(addr_id);
    $.ajax({
        url: get_addr_info_ajax,
        type:'GET', //GET
        cache: false,    
        data:{
           'addr_id' : addr_id
        },
        dataType:'json',    
        success:function(data){
            // console.log(data);
            if(!data.success){
                layer.msg(data.info, {icon: 5});
            }else{
                $('#tempForm').validationEngine('hideAll');                    
                $('#fullName').val(data.info.name);
                $('#idno').val(data.info.cre_num);
                $('#detaileaddress').val(data.info.address);
                $('#phone').val(data.info.tel);
                $('#zipcode').val(data.info.postal_code);
                $('#province').val(data.info.province);
                $('#city').val(data.info.city);
                $('#town').val(data.info.town);
                $('#hidaddr_id').val(data.info.id);

                // 身份证
                if(data.info.cre_type == 'PASPORT'){
                    $("#Id_tpye").find("option[value='"+data.info.cre_type+"']").attr("selected",true);
                    $('#idno').attr("placeholder",Placeholder_IdPT);
                    $('#idno').attr("maxlength","10");
                    $('#idno').attr("data-validation-engine","validate[required]");
                }else if(data.info.cre_type == 'ID'){
                    $("#Id_tpye").find("option[value='"+data.info.cre_type+"']").attr("selected",true);
                    $('#idno').attr("placeholder",Placeholder_IdNo);
                    $('#idno').attr("maxlength","18");
                    $('#idno').attr("data-validation-engine","validate[required,custom[chinaId]]");
                }

                // 省市区
                var datap = data.info.province;
                var datac = data.info.city;
                var datat = data.info.town;
                if(datap != ''){
                    $("#select2-chosen-1").html(datap);
                    $("#province").attr('data-validation-engine','validate[required]');
                    $("#loc_province").removeAttr('data-validation-engine');
                }else{
                    $("#province").removeAttr('data-validation-engine');
                    $("#loc_province").attr('data-validation-engine','validate[required]');
                }
                if(datac != ''){
                    $("#select2-chosen-2").html(datac);
                    $("#city").attr('data-validation-engine','validate[required]');
                    $("#loc_city").removeAttr('data-validation-engine');
                }else{
                    $("#city").removeAttr('data-validation-engine');
                    $("#loc_city").attr('data-validation-engine','validate[required]');
                }
                if(datat != ''){
                    $("#select2-chosen-3").html(datat);
                    $("#town").attr('data-validation-engine','validate[required]');
                    $("#loc_town").removeAttr('data-validation-engine');
                }else{
                    $("#town").removeAttr('data-validation-engine');
                    $("#loc_town").attr('data-validation-engine','validate[required]');        
                }
                // alert(data.info.id);

                // 线路
                if(data.info.line_id == '0' ){
                    window.w_id = data.info.id;

                    // 修改图片方法
                    var file1 = '<input type="file" name="file_one" id="file" accept="image/jpeg,image/png,image/jpg" atd="1" onclick="fileOnchange(this)" />';
                    var file2 = '<input type="file" name="file_two" id="files" accept="image/jpeg,image/png,image/jpg" atd="2" onclick="fileOnchange(this)" />';
                    $('#obcontid1 input').remove();
                    $('#obcontid2 input').remove();
                    $('#obcontid1 img').after(file1);
                    $('#obcontid2 img').after(file2);

                    $('#fileimage1').attr('src',data.info.id_card_front);
                    $('#fileimage2').attr('src',data.info.id_card_back);
                }else{
                    $('#ul_line li').each(function(k,v){
                        if(data.info.line_id == $(v).children().attr('id')){
                            //alert($(v).children(":checked").val());
                            if($(v).children(":checked").val()!=null){
                                // 已经选中状态

                                window.w_id = data.info.id;

                                // 修改图片方法
                                var file1 = '<input type="file" name="file_one" id="file" accept="image/jpeg,image/png,image/jpg" atd="1" onclick="fileOnchange(this)" />';
                                var file2 = '<input type="file" name="file_two" id="files" accept="image/jpeg,image/png,image/jpg" atd="2" onclick="fileOnchange(this)" />';
                                $('#obcontid1 input').remove();
                                $('#obcontid2 input').remove();
                                $('#obcontid1 img').after(file1);
                                $('#obcontid2 img').after(file2);

                                $('#fileimage1').attr('src',data.info.id_card_front);
                                $('#fileimage2').attr('src',data.info.id_card_back);

                            }else{
                                // 没有选中状态
                                // window.addr_id = data.info.id;
                                $(v).children().val(data.info.line_id).attr('checked',true);
                                window.w_id = data.info.id;
                                $(v).children().click();
                                // var line = document.getElementById(data.info.line_id);
                                // changeline(line,data.info.id);

                            }                     
                        }
                    });
                }
            }
        }
    })
}
// 选择线路特效
function changeline(obj,addr_id){
    var p = $(obj).val();
    if(addr_id==0 && typeof(window.w_id) != "undefined"){
        addr_id = window.w_id;
    }
    $.ajax({
        url: consoleurl,
        dataType:"html",
        data:{"id":p,"addr_id":addr_id},
        type:"POST",
        cache: false,
        beforeSend:function(){
            var index = layer.load(1, {
                shade: [0.1,'#000'], //0.1透明度的白色背景
                offset: '450px',
            });
        },
        success:function(html){
            layer.closeAll('loading'); //关闭加载层
            $('#disno').html(html);
        }
    });
    if(p != ''){
        var h = $(".m"+p).html();
        $("#mimenu").html(ShowSelectlng+"："+h);
    }else{
        $("#mimenu").html('');
    }

}
//选择寄件人按钮
function addsern(obj){
    layer.open({
        type: 2,
        title: l_select_sender,
        area : ['800px' , '500px'],
        // offset: '20%',
        closeBtn: 1,
        content: getSenderInfourl,
    });
}
// 选择寄件人数据
function senderAjax(id){
    $.ajax({
        url: senderAjaxurl,
        type:'POST', 
        cache: false,    
        data:{
           'id' : id
        },
        dataType:'json', 
        success:function(data){
            $('#tempForm').validationEngine('hideAll');   
            $("#MainContent_ddProvince").find("option:selected").removeAttr("selected");
            $("#MainContent_ddCity").find("option:selected").removeAttr("selected");
            $('#postname').val(data.s_name);
            $('#poststreet').val(data.s_street);
            $('#Postcountry').val(data.s_country);
            $('#Postprovince').val(data.s_state);
            $('#Postcity').val(data.s_city);
            $('#postphone').val(data.s_tel);
            $('#postcode').val(data.s_code);
            s_send_addr(data.s_state,data.s_city);
        }
    });
}

// 证件类型
$(document).ready(function(){
    var chid= $('#Id_tpye').val();
    if(chid == 'PASPORT'){
        $('#idno').attr("placeholder",Placeholder_IdPT);
        $('#idno').attr("maxlength","10");
        $('#idno').attr("data-validation-engine","validate[required]");
    }else if(chid == 'ID'){
        $('#idno').attr("placeholder",Placeholder_IdNo);
        $('#idno').attr("maxlength","18");
        $('#idno').attr("data-validation-engine","validate[required,custom[chinaId]]");
    }       
    // 证件类型
    $("#Id_tpye").change(function(){
        var chid= $('#Id_tpye').val();
        if(chid == 'PASPORT'){
            $('#idno').attr("placeholder",Placeholder_IdPT);
            $('#idno').attr("maxlength","10");
            $('#idno').attr("data-validation-engine","validate[required]");
        }else if(chid == 'ID'){
            $('#idno').attr("placeholder",Placeholder_IdNo);
            $('#idno').attr("maxlength","18");
            $('#idno').attr("data-validation-engine","validate[required,custom[chinaId]]");
        }
    });
});
// 通过JQform提交
function tempform(obj){
    $(window).unbind('beforeunload');
    if(!$("#tempForm").validationEngine("validate")) return ;
    $(obj).ajaxForm({
        dataType: 'json',
        beforeSend:function(){
            var index = layer.load(1, {
                shade: [0.1,'#000'], //0.1透明度的白色背景
                offset: '450px',
            });
        },
        success: function(data){
            if(data.state == 'no'){
                layer.closeAll('loading'); //关闭加载层
                layer.open({
                    type: 0,
                    offset: '300px',
                    shadeClose: true,
                    title: "{:L('LAY_SystemMessage')}",
                    content : data.msg,
                    icon : 5,
                });
                return false;
            }else{
                layer.closeAll('loading'); //关闭加载层
                window.location.href=data.url;
            }
        },
    });
}