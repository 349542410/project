

//选择收件人按钮
function infoselect(obj){
    layer.open({
        type: 2,
        title: l_cho_rec,
        skin: 'demo-class',//自定义样式demo-class
        area : ['800px' , '600px'],
        // offset: '20%',
        closeBtn: 1,
        content: selectRecipient,
    });
}
// 选择收件人数据
function get_addrinfo(addr_id,info){
    if(addr_id == undefined){
        layer.msg('请选择收件人', {icon: 5});
        return false;
    }else if(addr_id != ''){
        if(info == ''){
            $.ajax({
                url: get_addr_info_ajax,
                type:'GET', //GET
                cache: false,    
                data:{
                   'addr_id' : addr_id
                },
                dataType:'json', 
                beforeSend:function(){
                    var index = layer.load(1, {
                        shade: [0.1,'#000'], //0.1透明度的白色背景
                        offset: '450px'
                    });
                },   
                success:function(data){
                    Redata(data);
                }
            });
        }else{
            Redata(info);
        }
    }
    
    
}
// 选择线路特效
function changeline(obj,addr_id){
    var p = $(obj).val();
    if(addr_id==0 && typeof(window.w_id) != "undefined"){
        addr_id = window.w_id;
    }
    // 获取选中线路ID
    var t_line = $("input[name='TransferLine']:checked").val();
    // 调用中国地址函数
    get_aere(t_line,1);
    window.selec_gory = {options:[category_list[t_line]]};

    // console.log(selec_gory);
    $('#ordbody [id^="line_pice"]').text('');
    //$.ajax({
    //     url: lienpic_url,
    //     dataType:'json',
    //     type:"POST",
    //     data:{"line_id":p},
    //     beforeSend:function(){
    //         var index = layer.load(1, {
    //             shade: [0.1,'#000'], //0.1透明度的白色背景
    //             offset: '450px',
    //         });
    //     },
    //     success:function(data){
    //         var info = '';
    //         if(data.status){
    //             info = l_first_weight_lng+ parseInt(data.data.weight_first) + 'lb:$'+data.data.fee_first+'；'+l_addi_weight_lng+'$'+data.data.fee_next;
    //             $('#line_pice_'+p).text(info);
    //         }else{
    //
    //         }
    //     }
    //});
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
    var iupid = $(obj).attr('iupid');
    if(iupid == 0){
        $('#id_card').hide();
        window.objctor.state1 = '';
        $('.text_bg').css('bottom','120px');
    }else if(iupid == 1){
        $('#id_card').show();
        $('.text_bg').css('bottom','54px');
    }
}
//选择寄件人按钮
function addsern(obj){
    layer.open({
        type: 2,
        title: l_select_sender,
        skin: 'demo-class',//自定义样式demo-class
        area : ['800px' , '600px'],
        // offset: '20%',
        closeBtn: 1,
        content: getSenderInfourl,
    });
}
// 选择寄件人数据
function senderAjax(id,info){
    if(id == undefined){
        layer.msg('请选择寄件人', {icon: 5});
        return false;
    }else if(id != ''){
        if(info == ''){
            $.ajax({
                url: senderAjaxurl,
                type:'POST', 
                cache: false,    
                data:{
                   'id' : id
                },
                dataType:'json', 
                beforeSend:function(){
                    var index = layer.load(1, {
                        shade: [0.1,'#000'], //0.1透明度的白色背景
                        offset: '450px',
                    });
                },  
                success:function(data){
                    Sendata(data);
                }
            });
        }else{
            Sendata(info);
        }
    }
}

// 证件类型
$(document).ready(function(){
    var chid= $('#Id_tpye').val();
    if(chid == 'PASPORT'){
        // $('#idno').attr("placeholder",Placeholder_IdPT);
        // $('#idno').attr("maxlength","10");
        // $('#idno').attr("data-validation-engine","validate[required]");
    }else if(chid == 'ID'){
        // $('#idno').attr("placeholder",Placeholder_IdNo);
        $('#idno').attr("maxlength","18");
        // $('#idno').attr("data-validation-engine","validate[required,custom[chinaId]]");
    }       
    // 证件类型
    $("#Id_tpye").change(function(){
        var chid= $('#Id_tpye').val();
        if(chid == 'PASPORT'){
            // $('#idno').attr("placeholder",Placeholder_IdPT);
            // $('#idno').attr("maxlength","10");
            // $('#idno').attr("data-validation-engine","validate[required]");
        }else if(chid == 'ID'){
            // $('#idno').attr("placeholder",Placeholder_IdNo);
            $('#idno').attr("maxlength","18");
            // $('#idno').attr("data-validation-engine","validate[required,custom[chinaId]]");
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
                offset: '450px'
            });
        },
        success: function(data){
            if(data.state == 'no'){
                var msg;
                if(!_empty__(data.no)){
                    msg = '<font color="red">['+l_the_lng+data.no+l_line_lng+']</font>'+data.msg;
                }else{
                    msg = data.msg;
                }
                layer.closeAll('loading'); //关闭加载层
                layer.open({
                    type: 0,
                    offset: '300px',
                    skin: 'demo-class',//自定义样式demo-class
                    shadeClose: true,
                    title: lay_ms,
                    content : msg,
                    icon : 5
                });
                return false;
            }else{
                layer.closeAll('loading'); //关闭加载层
                window.location.href=data.url;
            }
        }
    });
}
// 收件人返回数据
function Redata(data){
    if(!data.success){
        layer.msg(data.info, {icon: 5});
        layer.closeAll('loading'); //关闭加载层
    }else{
        //console.log(data);
        $('#tempForm').validationEngine('hideAll');                    
        $('#fullName').val(data.info.name);
        //$('#idno').val(data.info.cre_num);
        $("input[name='IdNo']").val(data.info.cre_num);
        objctor.state1 = data.info.cre_num;
        $('#detaileaddress').val(data.info.address);
        $('#phone').val(data.info.tel);
        $('#zipcode').val(data.info.postal_code);
        $('#province').val(data.info.province);
        $('#city').val(data.info.city);
        $('#town').val(data.info.town);
        $('#hidaddr_id').val(data.info.id);

        $('#old_idno').val(data.info.idcard_old);

        // 是否存在身份证库
        if(data.info.addr_img == 2 || data.info.addr_img == 3){
            $('#card_id').val(data.info.lib_idcard);
            $('#addr_pro_img').val('');
            $('#addr_bak_img').val('');
            $('#addr_pro_img_sm').val('');
            $('#addr_bak_img_sm').val('');
        }else if(data.info.addr_img == 1){
            $('#card_id').val('0');
            $('#addr_pro_img').val(data.info.id_card_front_old);
            $('#addr_bak_img').val(data.info.id_card_back_old);
            $('#addr_pro_img_sm').val(data.info.id_card_front_small);
            $('#addr_bak_img_sm').val(data.info.id_card_back_small);
        }
        // 身份证
        if(data.info.cre_type == 'PASPORT'){

        }else if(data.info.cre_type == 'ID'){
            $("#Id_tpye").find("option[value='"+data.info.cre_type+"']").attr("selected",true);
            $('#idno').attr("placeholder",Placeholder_IdNo);
            $('#idno').attr("maxlength","18");
        }
        // 省市区
        var datap = data.info.province;
        var datac = data.info.city;
        var datat = data.info.town;
        var stop = false;
        $.each(window.re_area.options,function(k,v){
            if(v.value.indexOf(datap) != -1){
                set_aere_def([v.value]);
                $.each(v.children,function(k1,v1){
                    if(v1.value.indexOf(datac) != -1){
                        set_aere_def([v.value,v1.value]);
                        $.each(v1.children,function(k2,v2){
                            if(v2.value.indexOf(datat) != -1){
                                set_aere_def([v.value,v1.value,v2.value]);
                                $('#zipcode').val(v2.zipcode);
                                stop = true;
                                return false;
                            }
                        });
                        if(stop){
                            return false;
                        }
                    }
                });
                if(stop){
                    return false;
                }
            }
        });
        window.setPCT = (function(datap,datac,datat){
            return function(){
                // 省市区
                var stop = false;
                $.each(window.re_area.options,function(k,v){
                    if(v.value.indexOf(datap) != -1){
                        set_aere_def([v.value]);
                        $.each(v.children,function(k1,v1){
                            if(v1.value.indexOf(datac) != -1){
                                set_aere_def([v.value,v1.value]);
                                $.each(v1.children,function(k2,v2){
                                    if(v2.value.indexOf(datat) != -1){
                                        set_aere_def([v.value,v1.value,v2.value]);
                                        $('#zipcode').val(v2.zipcode);
                                        stop = true;
                                        return false;
                                    }
                                });
                                if(stop){
                                    return false;
                                }
                            }
                        });
                        if(stop){
                            return false;
                        }
                    }
                });
            }
        })(data.info.province,data.info.city,data.info.town);


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
                    if($(v).children(":checked").val()!=null){
                        // 已经选中状态
                        window.w_id = data.info.id;

                        // 修改图片方法
                        var file1 = '<input type="file" name="file_one" id="file" accept="image/jpeg,image/png,image/jpg" atd="1" onclick="fileOnchange(this,'+"#fileimage1"+')"/>';
                        var file2 = '<input type="file" name="file_two" id="files" accept="image/jpeg,image/png,image/jpg" atd="2" onclick="fileOnchange(this,'+"#fileimage2"+')"/>';
                        $('#obcontid1 input').remove();
                        $('#obcontid2 input').remove();
                        $('#obcontid1 img').after(file1);
                        $('#obcontid2 img').after(file2);

                        if(data.info.lib_idcard != 0) {
                            $('#fileimage1').attr('src',member_url+'/images/notupload_f.png');
                            $('#fileimage2').attr('src',member_url+'/images/notupload_b.png');
                        }else{
                            $('#fileimage1').attr('src', data.info.id_card_front);
                            $('#fileimage2').attr('src', data.info.id_card_back);
                        }

                    }else{
                        // 没有选中状态
                        $(v).children().val(data.info.line_id).attr('checked',true);
                        window.w_id = data.info.id;
                        $(v).children().click();

                    }                     
                }
            });
        }



        layer.closeAll('loading'); //关闭加载层
    }
}

// 寄件人返回数据
function Sendata(data){
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
    layer.closeAll('loading'); //关闭加载层
}