//缓存寄件人信息
window.send_buf = [];

// window.search_area = [];

// var loc = new Location();

// window.search_area = myformat(loc.items);

// console.log(window.search_area = loc.items);
// console.log(loc.find('0,1,2'));

//预加载时缓存寄件人信息
$.ajax({
    url:real_time_search_ajax,
    dataType:"json",
    cache: false,
    success:function(data){
        // console.log(data);
        window.send_buf = myformat(data);
        // console.log(window.send_buf);
    }
});

// 定义一个锁
window.lock = true; 
$("#postname").focus(function(){
    if(window.lock){
        window.lock = false;
        $.ajax({
            url:real_time_search_ajax,
            dataType:"json",
            cache: false,
            success:function(data){
                window.send_buf = myformat(data);
                setTimeout(function(){
                    window.lock = true;
                },2000)
            }
        });
    }
});
// 格式化数据
function myformat(obj){
    var list = [];
    var list_2 = [];
    $.each(obj,function(k,v){
        list_2.push(k);
        list_2.push(v);
        list.push(list_2);
        list_2 = [];
    });
    list = mysort(list);
    return list;
}
// JQ 字典排序
function mysort(arr){
    arr.sort(function(a,b){
        return a[1].localeCompare(b[1]);
    });
    return arr;
}
    
$("#postname").bind("input propertychange",function(){

    var list = [];
    var inp_value = $(this).val();
    if(_empty__(inp_value)){
        list = [];
    }else{
        var i = 0;
        $.each(window.send_buf,function(k,v){
            if(v[1].indexOf(inp_value)==0){
                if(i<30){
                    list.push(v);
                }
                i++;
            }
        })
    }
    // 数组为空
    if(list.length==0){
        $('#dlbox dd').remove();
        $('#hiddenbox').hide();
    }else{
        var str = '';
        var no = 0;     
        $.each(list,function(k,v){
            str += ( '<dd no="'+ no +'" send_id="' + v[0] + '" onclick="send_click(this)">' + v[1] + '</dd>' );
            no++;
        });
        $('#hiddenbox').show(); 
        $('#dlbox dd').remove();
        $('#dlbox').append(str);
    }
});
// 键盘事件
$('#postname').keyup(function(event){
    // 键盘上键
    if(event.keyCode==38){    //每按一次上下键都会发送一次请求，所以要先
        
        var send = $('#dlbox').find('.sendercolor');

        if(send.length == 0 ){
            $('#dlbox dd:last').addClass('sendercolor');
        }else{
            send.prev().addClass('sendercolor');
            send.removeClass('sendercolor');
        }

        var sendno = send.attr('no');
        var sendh = $('#dlbox').height() - (sendno-4)*20;
        if(isNaN(sendh)){
            $('#hiddenbox').scrollTop($('#dlbox').height());
        }else{
            if(sendh<$('#dlbox').height()){
                var h = $('#dlbox').height() - sendh;
                $('#hiddenbox').scrollTop(h);
            }else{
                var h = 0;
                $('#hiddenbox').scrollTop(h);
            }
        }
        

    //键盘下键   
    }else if(event.keyCode==40){
        var send = $('#dlbox').find('.sendercolor');

        if(send.length == 0 ){
            $('#dlbox dd:eq(0)').addClass('sendercolor');
        }else{
            send.next().addClass('sendercolor');
            send.removeClass('sendercolor');
        }

        var sendno = send.attr('no');
        var sendh = (sendno-3)*20;
        if(sendh>0){
            var h = $('#dlbox').height() - ($('#dlbox').height()-sendh);
            $('#hiddenbox').scrollTop(h);
        }else{
            var h = 0;
            $('#hiddenbox').scrollTop(h);
        }
        
    }else if(event.keyCode==13){

    }
});

// 数据获取
function send_click(obj){
    var dd_sendid = $(obj).attr('send_id');
    $.ajax({
        url: senderAjaxurl,
        data: {'id':dd_sendid},
        type:'POST',
        dataType:'json', 
        cache: false,
        beforeSend:function(){
            $('#hiddenbox').hide();
            $('#dlbox dd').remove();
            var index = layer.load(1, {
                shade: [0.1,'#000'], //0.1透明度的白色背景
                offset: '450px',
            });
        },
        success:function(data){
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
    });
}
$('#postname').keyup(function(event){
    var dd_sendid = $('#dlbox').find('.sendercolor').attr('send_id');
    // 键盘回车键
    if(event.keyCode == 13){
        if(!_empty__(dd_sendid)){
            $.ajax({
                url: senderAjaxurl,
                data: {'id':dd_sendid},
                type:'POST',
                dataType:'json', 
                cache: false,
                beforeSend:function(){
                    $('#hiddenbox').hide();
                    $('#dlbox dd').remove();
                    var index = layer.load(1, {
                        shade: [0.1,'#000'], //0.1透明度的白色背景
                        offset: '450px',
                    });
                },
                success:function(data){
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
            });
        }
    }
});
// 获取焦点触发改变事件
$('#postname').focus(function(){
    $(this).trigger('propertychange');
});

// 判断:当前元素是否是被筛选元素的子元素
$.fn.isChildAndSelfOf = function(b){
    return (this.closest(b).length > 0);
};
// 点击空白时关闭实时搜索功能
$(document).click(function(event){
    if($(event.target).isChildAndSelfOf('#hiddenbox')|| $(event.target).isChildAndSelfOf('dd')|| $(event.target).isChildAndSelfOf('#postname')){
    }else{
        $('#hiddenbox').hide();
        $('#dlbox dd').remove();   
    }
});