    // 点击选择文件上传按钮
    $('#antbtn').click(function(){
        $('#formdis').stop(false,true).slideToggle('show');
    });
    // 当点击确认导入时
    $('#ajaxbtn').click(function(){
        $.ajax({
            url : orders_import_t,
            type: "GET",
            dataType: "json",
            beforeSend: function(){
                var index = layer.load(1, {
                    shade: [0.1,'#000'], //0.1透明度的白色背景
                });
            },
            success: function(data){
                if(data.success){
                    layer.closeAll('loading'); //关闭加载层.
                    layer.msg(addsuc_lng);
                    pageAjax(1);
                    $('.fileinput-remove').click();
                    $('#ajaxbtn').hide();
                    $('#closebtn').hide();
                    // $('#fileform').submit();
                }else{
                    layer.closeAll('loading'); //关闭加载层.
                    layer.msg(data.info);
                    // $('.fileinput-remove').click();
                }
            }
        });
    });
    // 当点击取消导入时
    $('#closebtn').click(function(){
        $('.fileinput-remove').click();
        $('.kv-error-close').click();
        $(this).hide();
        $('#ajaxbtn').hide();
        $('#uploadtips').hide();
        $.get(no_import_url);
    });
    // 点击fileupload上传控件
    function showfileup(obj){
        // 显示下载模板状态说明
        var line_id = $(obj).val();
        $.ajax({
            url : view_stat_url,
            type: "GET",
            data:{"line_id":line_id},
            dataType: "json",
            success: function(data){
                if(data['status']){
                    // 需要显示
                    $('#view_stat').children().html(view_stat_tips);
                }else{
                    // 无需显示
                    $('#view_stat').children().html('');
                }
            }
        });
        $('#ajaxbtn').hide();
        var file = '<input id="file_s" name="file_stu" type="file" data-min-file-count="1">';
        $('#fileform').children().remove();
        $('#fileform').append(file);
        var lineid = $(obj).val();
        // var linename = $(obj).next().text();
        // alert(linename);
        // $('#file_s').attr('disabled',false);
        // $('#file_s').show();
        fileupload(lineid);
        $('#download_btn').show();
        // upload_excel(lineid,linename);
    }
    $('#download_btn').click(function(){
        var lineid = $("input[name='TransferLine']:checked").val();
        var linename = $("input[name='TransferLine']:checked").next().text();
        // window.open(uploadexcel_url+'?line_id='+lineid+'&name='+linename);
        window.location.href=uploadexcel_url+'?line_id='+lineid+'&name='+linename;
    });
    function fileupload(lineid){
        $('#file_s').attr('accept','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel');
        $('#file_s').fileinput({
            language: 'zh',
            uploadUrl: importurl,
            showCaption: false,     //是否显示被选文件的简介
            showPreview: false,     //是否显示预览区域
            showRemove: false,      //是否显示移除按钮
            showUpload: false,      //是否显示上传按钮
            uploadExtraData:{'line_id':lineid},
            browseClass: "btn btn_white", 
            browseLabel: file_upload_btn, //默认按钮
            validateInitialCount:false,
            browseIcon:'<i class="glyphicon glyphicon-open"></i>&nbsp;',
            // initialPreview:"text files",
            allowedFileExtensions : ['xls','xlsx'],
        // 当文件上传成功后
        }).on("fileuploaded", function (event, data,previewId,index) {
                layer.closeAll('loading'); //关闭加载层.
                $('.file-preview').remove();
                var res = data.response;
                var str = '';
                str += '<dl class="dl_left">';
                // 如果文件错误直接返回一个错误信息
                if(!res.excel){
                    str += '<dd class="errmsgred">';
                    str += res.info;
                    str += '</dd>';
                }else{
                    var status = true;
                    var count = 0;
                    $.each(res.info,function(k,v){
                        if(v.package_id == ''){
                            v.package_id = 0;
                        }
                        if(!v.success){
                            str += '<dd class="errmsgred">';
                            str += ordnum_lng+'：<span style="display:inline-block;margin-right: 10px;">'+v.package_id+'</span>'+verfa_lng+'   '+v.error;
                            str += '</dd>';
                            status=false;
                        }else{
                            count++;
                            // str += '<dd class="errmsggre">';
                            // str += ordnum_lng+'：<span style="display:inline-block;width:40px;">'+v.package_id+'</span> '+versuc_lng+'！';
                            // str += '</dd>'; 
                        }
                    });

                    if(status){
                        // str += '<dd class="errmsggre">';
                        // str += '<div>'+l_d_ver_suc.replace('{***}',count)+'</div>';
                        // str += '</dd>';
                        // str += '<dd class="dl_right">共导入'+res.statistics.total+'条数据</dd>';
                        str += '<dd class="dl_right">'+l_Imput_total.replace('***',res.statistics.total)+'</dd>';
                        // str += '<dd class="dl_right">正确订单数：'+res.statistics.successful+'条，预计税金'+res.statistics.total_tax+'美元</dd>';
                        str += '<dd class="dl_right">'+l_Order_Number.replace('***',res.statistics.successful)+'，'+l_Duty_Fee_kk.replace('***',res.statistics.total_tax)+'。'+'</dd>';
                        // str += '<dd class="dl_right">不正确订单数：<font color="red">'+(res.statistics.total-res.statistics.successful)+'</font>条</dd>';
                        str += '<dd class="dl_right">'+l_Incorrect_Order_Number.replace('***','<font color="red">'+(res.statistics.total-res.statistics.successful)+'</font>')+'</dd>';
                        //console.log(res.statistics.err_all.length);
                        if(res.statistics.err_all.length != 0){
                            $.each(res.statistics.err_all,function(k,v){
                                // console.log(v[0]);
                                // console.log(v[0].toString());
                                str += '<dd class="dl_right_err">'+l_package+'：'+v[0]+'&nbsp;&nbsp;&nbsp;&nbsp;'+v[1]+'</dd>';
                            });
                        }
                        $('#ajaxbtn').show();
                        $('#closebtn').show();
                        $('#uploadtips').show();
                    }else{
                        str += '<br>';
                        str += '<dd class="errmsgred">';
                        str += '<div>'+l_jjc_err+'</div>';
                        str += '</dd>';
                        $('#ajaxbtn').hide();
                    }
                }
                
                // 左边返回接收数据
                // str += '</dl>';
                // str += '<dl class="dl_right">';
                // str += '<dd>共导入'+res.statistics.total+'条数据</dd>';
                // str += '<dd>正确订单数：'+res.statistics.successful+'条，预计税金'+res.statistics.total_tax+'美元</dd>';
                // str += '<dd>不正确订单数：<font color="red">'+(res.statistics.total-res.statistics.successful)+'</font>条</dd>';
                // str += '</dl>';
                // str += '<div style="clear:both;"></div>';
                errmsg(str);

        // 当文件选择后        
        }).on("filebatchselected",function(event, files){
            // 自动调用上传按钮
            $(this).fileinput("upload");
            // 删除进度条
            $('.kv-upload-progress').remove();
            $('.file-preview').remove();
            var index = layer.load(1, {
                shade: [0.1,'#000'], //0.1透明度的白色背景
            });
            if(files.length == 0){
                errmsg('<p class="errmsgred">'+unsft_lng+'</p>');
            }
            layer.closeAll('loading');
        // 当点击移除后    
        }).on("filecleared",function(){
            $('.file-preview').remove();
            $.get(no_import_url);
            $('#ajaxbtn').hide();
        });
    }


/**
 * 文件上传错误提示
 */
function errmsg(msg){
    str = '<div class="file-preview"><div class="kv-fileinput-error file-error-message" style="display: block;"></div></div>';
    $('.file-input').append(str);
    $('.kv-fileinput-error').css('display','block');
    $('.kv-fileinput-error').append('<span class="close kv-error-close">×</span><br>');
    $('.kv-fileinput-error').append("<pre>"+msg+"</pre>");
    $('.kv-error-close').click(function(){
        $('.kv-fileinput-error').stop().slideUp(800);
        setTimeout(function(){
            $('.file-preview').remove();
        },850);
    });
}   
function mime_test(mime){
    var list = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-excel'];
    for(var i = 0;i<list.length;i++){
        if(mime == list[i]){
            return true;
        }
    }
    return false;
}
// 删除前的确认框
function del(id,url)
{
    var pageid = $('#pages').find('.current').text();
    layer.confirm(laylang_md, {icon:3,offset: '400px',title: laylang_if,btn: [laylang_btnok, laylang_btncan],skin: 'demo-class'}, function(){
        $.ajax({
            url: url,
            type: "POST",
            data:{"id":id},
            dataType: "json",
            success: function(data){
                if(data.success){
                    layer.msg(data.msg, {icon: 6,offset: '400px'});
                    $.ajax({
                        url : order_excel_ajax,
                        type: "POST",
                        data:{"p":pageid,"where":where},
                        dataType: "json",
                        success: function(data){
                            $('#joinres').children().remove();
                            $('#pages').children().remove();
                            $('#pages').append(data.show);
                            var res=get_tabel(data.list.order_data);
                            if(data.list.order_data.length == 0){
                                $('.content').append('<span class="empty"></span>');
                            }
                            $('#joinres').append(res);
                            // 删除后，更新ajaxpage同时也更新可选状态
                            update_optional_status();
                        }
                    });
                }else{
                    layer.msg(data.msg, {icon: 5,offset: '400px'});
                }
            }
        });
    });
}

function pageAjax(p){

    var where = window.where;

    $.ajax({
        url : order_excel_ajax,
        type: "POST",
        data:{"p":p,"where":where}, 
        dataType: "json",
        beforeSend: function(){
            var index = layer.load(1, {
                shade: [0.1,'#000'], //0.1透明度的白色背景
            });
        },
        success: function(data){
            layer.closeAll('loading'); //关闭加载层.
            $('#joinres').children().remove();
            $('#pages').children().remove();
            $('.empty').remove();
            $('#pages').append(data.show);
            var res=get_tabel(data.list.order_data);
            $('#joinres').append(res);
            // 更新页码时，ajaxpage同时也更新可选状态
            update_optional_status();
        }
    });
}


function get_tabel(data){
    // if(_empty__(data)){
    //     $('.content').append('<span class="empty"><img src="__MEMBER__/images/imp_emptyicon.png"></span>');
    // }
    str = '';
    var i = 1;
    $.each(data,function(k,v){
        str+='<tr>';


        str+='<td class="clicktr">';
        str+='<input type="checkbox" name="checkbox_print" value="'+v.id+'"  onclick="update_optional_status()">';
        str+='</td>';


        str+='<td class="clicktr" onclick="clicktd(this)">';
        str+= i;
        str+='</td>';

        if(v.export_count == 0){
            str+='<td style="color:red" class="clicktr" id="export_count" coid="'+v.export_count+'" onclick="clicktd(this)">';
                str+=l_Not_exported;
            str+='</td>';
        }else{
            str+='<td style="color:#000" class="clicktr" id="export_count" coid="'+v.export_count+'" onclick="clicktd(this)">';
                // str += '已导出'+v.export_count+'次';
                str += l_Exported_for.replace('***',v.export_count);
            str+='</td>';
            
        }


        str+='<td class="clicktr" onclick="clicktd(this)">';
        str+= v.sender;
        str+= '<br>';
        str+= v.sendTel;
        str+='</td>';

        str+='<td class="clicktr" onclick="clicktd(this)">';
        str+= v.receiver;
        str+= '<br>';
        str+= v.reTel;
        str+='</td>';


        str+='<td class="clicktr" onclick="clicktd(this)">';
        str+= v.line_name;
        str+='</td>';

        str+='<td class="clicktr" onclick="clicktd(this)">';
        str+= v.create_time;
        str+='</td>';

        str+= operation(v.id);


        str+='</tr>';
        i++;

    });

    return str;

}

function operation(id){
    return "<td style='text-align:center; position: relative;'><a href='"+orderimportorder+"?id="+id+"' >"+l_View+"</a><span style='vertical-align:text-middle;'>&nbsp;|&nbsp;</span><a href='javascript:void(0);' onclick='"+'del("'+id+'",ords_del)'+"'>"+del_msg+"</a></td>"
}



function ccc(obj){
    // window.where.line_name = $('#linename').val();
    window.where.addr_name = $('#addrname').val();
    window.where.addr_tel = $('#addrtel').val();
    
    var startTime=document.getElementById("createStarttime").value;
    var endTime=document.getElementById("createEndtime").value;
    if(startTime>endTime){
        layer.msg(l_time_start_eq_end);
        return false;
    }else{
        if(startTime != ''){
            var startback = transdate(startTime);
            document.getElementById("starttime").value=startback;
        }else{
            $('#starttime').val('');
        }

        if(endTime != ''){
            var endback = transdate(endTime);
            document.getElementById("endtime").value=endback;
        }else{
            $('#endtime').val('');
        }
        window.where.stat_time = $('#starttime').val();
        window.where.end_time = $('#endtime').val();
        window.where.line_id = $('#line_name').find(':selected').val();
        pageAjax(1);
    }

}
function transdate(startTime){
    var date=new Date();
    date.setFullYear(startTime.substring(0,4));
    date.setMonth(startTime.substring(5,7)-1);
    date.setDate(startTime.substring(8,10));
    date.setHours(startTime.substring(11,13));
    date.setMinutes(startTime.substring(14,16));
    date.setSeconds(startTime.substring(17,19));
    return Date.parse(date)/1000;
}
function transdate(endTime){
    var date=new Date();
    date.setFullYear(endTime.substring(0,4));
    date.setMonth(endTime.substring(5,7)-1);
    date.setDate(endTime.substring(8,10));
    date.setHours(endTime.substring(11,13));
    date.setMinutes(endTime.substring(14,16));
    date.setSeconds(endTime.substring(17,19));
    return Date.parse(date)/1000;
}

// 全选特效
$("#check_all").click(function(){   
    if(this.checked){   
        // 选中
        var joinres_cb = $("#joinres :checkbox");
        if(joinres_cb.length != 0){
            $("#joinres :checkbox").prop("checked", true); 
            $('.option_status').removeClass('btnstyle_un');
            $('.option_status').addClass('btnstyle'); 
            $('#del_all_id').bind("click",function(){
                del_all(delete_all_url);
            });
            $('#btns_export').bind("click",function(){

                download_all(download_all_url);
            });
        }
    }else{ 
        // 未选中
        $("#joinres :checkbox").prop("checked", false);
        $('.option_status').removeClass('btnstyle');
        $('.option_status').addClass('btnstyle_un');
        $('#del_all_id').unbind("click");
        $('#btns_export').unbind("click");
    }  
});


// 批量删除
function del_all(url){
    var pageid = $('#pages').find('.current').text();
    var arrbox = [];
    $('input[name="checkbox_print"]:checked').each(function(){
        arrbox.push($(this).val());
    });
    layer.confirm(laylang_md, {icon:3,offset: '400px',title: laylang_if,btn: [laylang_btnok, laylang_btncan],skin: 'demo-class'}, function(){
        $.ajax({
            url: url,
            type: "POST",
            data:{"ids":arrbox.join(',')},
            dataType: "json",
            beforeSend: function(){
                var index = layer.load(1, {
                    shade: [0.1,'#000'], //0.1透明度的白色背景
                });
            },
            success: function(data){
                if(data.success){
                    layer.msg(data.msg, {icon: 6,offset: '400px'});

                    $.ajax({
                        url : order_excel_ajax,
                        type: "POST",
                        data:{"p":pageid,"where":where},
                        dataType: "json",
                        success: function(data){
                            $('#joinres').children().remove();
                            $('#pages').children().remove();
                            $('#pages').append(data.show);
                            var res=get_tabel(data.list.order_data);
                            if(data.list.order_data.length == 0){
                                $('.content').append('<span class="empty"></span>');
                            }
                            $('#joinres').append(res);
                            // 删除后，更新ajaxpage同时也更新可选状态
                            update_optional_status();
                            // 设置关闭层
                            setInterval(function(){ 
                                layer.closeAll('loading'); //关闭加载层.
                            },1000);
                            $('#check_all').attr('checked',false);
                        }
                    });
                }else{
                    layer.msg(data.msg, {icon: 5,offset: '400px'});
                    setInterval(function(){ 
                        layer.closeAll('loading'); //关闭加载层.
                    },1000);
                }
            }
        });
    });
}
// 更新批量下单打印状态
function bing_dl(){
    $('input[name="checkbox_print"]:checked').each(function(){
        var excount = parseInt($(this).parent().parent().find('#export_count').attr('coid'))+1;
        var attr_excount = $(this).parent().parent().find('#export_count').attr('coid',excount);
        var findattr = $(this).parent().parent().find('#export_count').attr('coid');
        // $(this).parent().parent().find('#export_count').css('color','#000').text('已导出'+findattr+'次');
        $(this).parent().parent().find('#export_count').css('color','#000').text(l_Exported_for.replace('***',findattr));
    });
}
// 调用上传地址
function download_all(url){
    var arrbox = [];
    $('input[name="checkbox_print"]:checked').each(function(){
        arrbox.push($(this).val());
    });
    var url_id = arrbox.join(',');
    window.location.href=url+'?order_id='+url_id;
}

// 更新可选状态
function update_optional_status(){
    var sta = $('input[name="checkbox_print"]:checked')[0];
    if(sta == undefined){
        // 不可选
        $('.option_status').removeClass('btnstyle');
        $('.option_status').addClass('btnstyle_un');
        $('#del_all_id').unbind("click");
        $('#btns_export').unbind("click");
    }else{
        // 可选
        $('.option_status').removeClass('btnstyle_un');
        $('.option_status').addClass('btnstyle');
        $('#del_all_id').bind("click",function(){
            del_all(delete_all_url);
        });
        $('#btns_export').bind("click",function(){
            download_all(download_all_url);
        });
    }
    
}


// 点击tr即选中
// $(".clicktr").click(function(e){
//     var check = $(this).find("input[type='checkbox']");
//     if(check){ 
//         // 非选中时 
//         var flag = check[0].checked;
//         if(flag){  
//             check[0].checked = false; 
//             update_optional_status();
//         }else{  
//             // 选中时
//             check[0].checked = true; 
//             update_optional_status(); 
//         }  
//     }  
// });   
// $(".clicktr").click(function(e){


// });
function clicktd(obj){
    var check = $(obj).parent().find("input[type='checkbox']");
    if(check){ 
        // 非选中时 
        var flag = check[0].checked;
        if(flag){  
            check[0].checked = false; 
            update_optional_status();
        }else{  
            // 选中时
            check[0].checked = true; 
            update_optional_status(); 
        }  
    } 
}
$("input[type='checkbox']").click(function(e){  
    e.stopPropagation();
});  