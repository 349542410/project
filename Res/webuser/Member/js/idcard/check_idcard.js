/**
 * 检测身份证号码和身份证库对应函数
 * @param c_val
 */
function check_idcard(c_val,c_id){
    $("input[name='IdNo']").bind('input propertychange',function(){
        var myval = $(this).val();
        // 如果身份证号码和身份证库对应，不允许上传身份证照
        if(c_val == myval){
            $('#fileimage1').attr('src',member_url+'/images/notupload_f.png');
            $('#fileimage2').attr('src',member_url+'/images/notupload_b.png');
            $('#card_id').val(c_id);
        }else{
            // 如果身份证号码和身份证库不对应，允许上传新身份证照
            $('#card_id').val('0');
            $('#fileimage1').attr('src',member_url+'/images/pho_front.png');
            $('#fileimage2').attr('src',member_url+'/images/pho_back.png');
        }
    });
}
/**
 *  身份证带搜索input框
 *
 * @method  使用element渲染input框（提供身份证查询操作）
 * @time    20180602
 * @author  gan
 */
function set_element_idcard(){
    var Main = {
        data: function data() {
            return {
                restaurants: [],
                state1: ''
            };
        },

        methods: {
            querySearch: function querySearch(queryString, cb) {
                var restaurants = this.restaurants;
                var results = queryString ? restaurants.filter(this.createFilter(queryString)) : restaurants;
                // 调用 callback 返回建议列表的数据
                cb(results);
            },
            createFilter: function createFilter(queryString) {
                return function (restaurant) {
                    return restaurant.value.toLowerCase().indexOf(queryString.toLowerCase()) === 0;
                };
            },
            loadAll: function loadAll() {
                return ;
            },
            handleSelect: function handleSelect(item) {
                $('#fileimage1').attr('src',member_url+'/images/notupload_f.png');
                $('#fileimage2').attr('src',member_url+'/images/notupload_b.png');
                var this_val = this.state1;
                var this_id = item.id;
                $('#card_id').val(this_id);
                $('#old_idno').val(item.idcard_old);
                check_idcard(this_val,this_id);

            }
        },
        mounted: function mounted() {
            this.restaurants = this.loadAll();
        }
    };
    var Ctor = Vue.extend(Main);
    window.objctor = new Ctor().$mount('#idno');
}
/**
 * 获取身份证信息
 */
//function get_idno_data(){
//    objctor.state1='';
//    // 获取需要查找的数据
//    var full_name = $('#fullName').val();
//    var rel_phone = $('#phone').val();
//    if(!_empty__(full_name) && !_empty__(rel_phone)){
//        $.ajax({
//            url: find_indo_data,
//            type:'GET',
//            cache: false,
//            data:{
//                'true_name' : full_name,
//                'tel' : rel_phone
//            },
//            dataType:'json',
//            success:function(data){
//                console.log(data);
//                // 当查到对应数据
//                //if(data.status == true){
//                //    // 审核通过只有条数据
//                //    if(data.data.length == 1){
//                //        objctor.state1 = data.data[0].value;
//                //        $('#card_id').val(data.data[0].id);
//                //        // 图片处理
//                //        //######审核通过只有一条数据，用户改变身份证号码时
//                //        $('#fileimage1').attr('src',member_url+'/image/idnotupload.png');
//                //        $('#fileimage2').attr('src',member_url+'/image/idnotupload.png');
//                //        var inthisval = data.data[0].value;
//                //        var inthisid = data.data[0].id;
//                //        // 调用检测身份证号码和身份证库对应函数
//                //        check_idcard(inthisval,inthisid);
//                //        // 审核通过有多条数据
//                //    }else{
//                //        objctor.restaurants = data.data;
//                //    }
//                //    if(data.data.length > 1){
//                //        $("input[name='IdNo']").click();
//                //        $("input[name='IdNo']").focus();
//                //    }
//                //    // 当没查对应数据
//                //}else{
//                //    objctor.restaurants = [];
//                //}
//            }
//        });
//    }else{
//        return false;
//    }
//}

function get_idno_data(){
    var full_name = $('#fullName').val();
    var rel_phone = $('#phone').val();
    var id_no = $('input[name="IdNo"]').val();
    var old_idno = $('#old_idno').val();
    var card_id = $('#card_id').val();
    if(!_empty__(full_name) && !_empty__(rel_phone) && !_empty__(id_no)){
            $.ajax({
                url: find_indo_data,
                type:'GET',
                cache: false,
                data:{
                    'true_name' : full_name,
                    'tel' : rel_phone,
                    'idno' : old_idno
                },
                dataType:'json',
                success:function(data){
                    // 当查到对应数据
                    if(data.status == true){
                        // 如果身份证号码和身份证库对应
                        $('#card_id').val(data.data[0].id);
                        $('#addr_pro_img').val('');
                        $('#addr_bak_img').val('');
                        $('#fileimage1').attr('src',member_url+'/images/notupload_f.png');
                        $('#fileimage2').attr('src',member_url+'/images/notupload_b.png');
                    }else{
                        // 如果身份证号码和身份证库不对应
                        $('#card_id').val('0');
                        $('#fileimage1').attr('src',member_url+'/images/pho_front.png');
                        $('#fileimage2').attr('src',member_url+'/images/pho_back.png');
                    }
                }
            });
    }else if(!_empty__(full_name) && !_empty__(rel_phone)){
        objctor.state1='';
        $.ajax({
            url: find_indo_data,
            type:'GET',
            cache: false,
            data:{
                'true_name' : full_name,
                'tel' : rel_phone
            },
            dataType:'json',
            success:function(data){
                // 当查到对应数据
                if(data.status == true){
                    // 审核通过只有条数据
                    if(data.data.length == 1){
                        objctor.state1 = data.data[0].value;
                        $('#card_id').val(data.data[0].id);
                        $('#addr_pro_img').val('');
                        $('#addr_bak_img').val('');
                        $('#old_idno').val(data.data[0].idcard_old);
                        // 图片处理
                        //######审核通过只有一条数据，用户改变身份证号码时
                        $('#fileimage1').attr('src',member_url+'/images/notupload_f.png');
                        $('#fileimage2').attr('src',member_url+'/images/notupload_b.png');
                        var inthisval = data.data[0].value;
                        var inthisid = data.data[0].id;
                        // 调用检测身份证号码和身份证库对应函数
                        check_idcard(inthisval,inthisid);
                        // 审核通过有多条数据
                    }else{
                        objctor.restaurants = data.data;
                    }
                    if(data.data.length > 1){
                        $("input[name='IdNo']").click();
                        $("input[name='IdNo']").focus();
                    }
                    // 当没查对应数据
                }else{
                    $('#card_id').val('0');
                    objctor.restaurants = [];
                }
            }
        });
    }else{
        $('#card_id').val('0');
        $('#addr_pro_img').val('');
        $('#addr_bak_img').val('');
        objctor.restaurants = [];
        objctor.state1='';
    }
}
