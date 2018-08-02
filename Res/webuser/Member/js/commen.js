/**
 * 删除前的确认框
 *
 * @method del
 * @param {Number,string} id , url
 * @return {json}
 * @author gary
 */
function del(id,url)
{
    var pageid = $('#pages').find('.current').text();
    layer.confirm(laylang_md, {icon:3,offset: '400px',title: laylang_if,btn: [laylang_btnok, laylang_btncan]}, function(){
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
                        data:{"p":pageid},
                        dataType: "json",
                        success: function(data){
                            $('#joinres').children().remove();
                            $('#pages').children().remove();
                            // console.log(data.list.order_data);
                            $('#pages').append(data.show);
                            var res=get_tabel(data.list.order_data);
                            $('#joinres').append(res);
                        }
                    });
                }else{
                    layer.msg(data.msg, {icon: 5,offset: '400px'});
                }
            }
        });
    });
}
/**
 * 测试是否为空
 *
 * @method empty
 * @param {string} str
 */
function _empty__(str){
    var i = 0;
    if(str===undefined){
        return true;
    }

    if(typeof str === 'object'){
        if(str!==[]&&str!=={}){
            return false;
        }
        return true;
    }

    if(typeof str === 'string'){
        if(str.length==0){
            return true;
        }
        for(;i<str.length;i++){
            if(str.charAt(i)!=' '){
                return false;
            }
        }
    }
    
    return false;
}
/**
 * 文档加载完成
 *
 * @method 验证成功
 * @param {string,string} check_new_ann_url , type
 */
function load_success(check_new_ann_url,type){
    $.get(check_new_ann_url,function(res){
        // alert(res);
        if(res != 0){
            $('#an_show_i').text(res);
            $('.an_show_i').css('display','inline-block');
        }
    });
}

/**
 * 判断整行是否有值
 *
 * @param {dom} line 
 *  
 */
function is_empty_line(line){

    var inp = line.children().find('input');
    var sele = line.children().find('select');

    var stop = true;

    $.each(inp,function(k,v){
        if($(v).attr('class') != 'clzhong' && $(v).attr('type') != 'checkbox'){
            if($(v).val() != ''){
                stop = false;
                return false;
            }
        }
        
    });
    return stop;
}
/**
 * 根据label值取无限极分类（针对element-ui Cascader 级联选择器）
 *
 * @param l_val  {json}  需找分类的第一层数据
 * @param l_find {json}  对应父类的子类数据
 */
function loop_cascader(l_val, l_find){
    var res = [];
    $.each(l_val,function(k,v){
        if(v.label == l_find){
            res = [v.value,v.children];
            return false;
        }
    });

    return res;
}


function empty_session(url){

    $.get(url,function(res){
        //console.log(res);
        if(res.status){
            location.reload();
        }
    });
}
