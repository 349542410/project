/**
 * 获取中国地址
 **/
function get_aere(line_id, type) {
    console.log(line_id);
    console.log(type);

    $.ajax({
        url: get_addr_url,
        type: "GET",
        data: {
            "line_id": line_id
        },
        dataType: "json",
        success: function (data) {
            console.log(data);
            window.re_area.options = data;
            if (type == 1) {
                window.setPCT();
            }
        }
    });
}


/**
 * 设置默认中国地址
 **/
function set_aere_def(def) {
    window.re_area.selectedOptions = def;
}

/**
 * 获取中国地址
 **/
function main_addr() {
    var Main = {
        data: function () {
            return {
                options: [],
                selectedOptions: []
            };
        },
        methods: {
            handleChange: function (val) {
                var pro_val = String(val[0]);
                var cit_val = String(val[1]);
                var tow_val = String(val[2]);

                $('#province').val(pro_val);
                $('#city').val(cit_val);
                $('#town').val(tow_val);
                // 找到对应值，跳出循环
                var stop = false;
                // 遍历取出邮编
                $.each(this.options,function(k,v){
                    //if(v.value == val[0]){
                    if(v.value.indexOf(val[0]) != -1){
                        $.each(v.children,function(k1,v1){
                            if(v1.value.indexOf(val[1]) != -1){
                                $('#zipcode').val(v1.zipcode);
                                $.each(v1.children,function(k2,v2){
                                    if(v2.value.indexOf(val[2]) != -1){
                                        // 把级联选择器中第三个分类赋值到 邮编地址
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
        }
    };
    var Ctor = Vue.extend(Main);
    window.re_area = new Ctor().$mount('#re_area');
}
/**
 * Cascader 级联选择器
 *
 * @method  渲染Cascader
 * @param   {Number} id  所需传入渲染的ID
 * @param   {array}  def 级联选择器的默认选项（传入[]，则清空选择）
 * @time    201180418
 * @author  gan
 */
function ele_cascader(id, def, typ) {

    var arr_box = {};
    arr_box.options = window.globa_selec_gory.options;
    // 添加此对象
    arr_box.selectedOptions = def;
    // console.log(arr_box);
    var Main = {
        data: function () {
            return arr_box;
        },
        methods: {
            goryChange: function (val) {
                // 数据赋值   PS：this.$el是Vue.js的转DOM操作
                var thisdom = $(this.$el);
                // console.log(thisdom);
                thisdom.find('#cate_one').val(val[0]);
                thisdom.find('#cate_two').val(val[1]);
                // 找到对应值，跳出循环
                var stop = false;
                // 遍历取出税金
                $.each(this.options, function (k, v) {
                    if (v.value == val[0]) {
                        $.each(v.children, function (k1, v1) {
                            if (v1.value == val[1]) {
                                // 快件信息=>《数量》生成单位
                                //if(v1.num_unit != '' && v1.num_unit != null && v1.num_unit){
                                //        // 如果数据不为空白
                                //        thisdom.parent().parent().find('#num_unit').children().remove();
                                //        thisdom.parent().parent().find('#num_unit').append('<option value="">'+unit_lng+'</option>');
                                //        $.each(num_unit,function(k2,v2){
                                //            thisdom.parent().parent().find('#num_unit').append('<option value="'+v2+'">'+v2+'</option>');
                                //        });
                                //}else{
                                //    // 如果数据为空，则还原状态
                                //    thisdom.parent().parent().find('#num_unit').children().remove();
                                //    thisdom.parent().parent().find('#num_unit').append('<option value="">'+unit_lng+'</option>');
                                //}
                                //console.log(v1);
                                // 快件信息=>《规格/容量》生成单位
                                if (v1.spec_unit !== '' && v1.spec_unit != null && v1.spec_unit) {
                                    var spec_unit = v1.spec_unit.split(',');
                                    var spec_unit_box = [];
                                    // 数据装载成json
                                    var spec_unit_tmp = {};
                                    $.each(spec_unit, function (k, v) {
                                        spec_unit_tmp.value = v;
                                        spec_unit_box.push(spec_unit_tmp);
                                        spec_unit_tmp = {};
                                    });
                                    window.sunit[id].restaurants = spec_unit_box;
                                    //console.log(window.sunit[id]);
                                    // 如果数据不为空白
                                    //var spec_unit = v1.spec_unit.split(',');
                                    //thisdom.parent().parent().find('#spec_unit').children().remove();
                                    //thisdom.parent().parent().find('#spec_unit').append('<option value="">' + unit_lng + '</option>');
                                    //$.each(spec_unit, function (k2, v2) {
                                    //    thisdom.parent().parent().find('#spec_unit').append('<option value="' + v2 + '">' + v2 + '</option>');
                                    //});
                                } else {
                                    window.sunit[id].restaurants = [];
                                    // 如果数据为空，则还原状态
                                    //thisdom.parent().parent().find('#spec_unit').children().remove();
                                    //thisdom.parent().parent().find('#spec_unit').append('<option value="">'+unit_lng+'</option>');
                                }
                                // console.log(v1);
                                thisdom.find('#cate_pirce').val(v1.price);
                                thisdom.parent().parent().find('.chuijin').children().html(v1.price);
                                stop = true;
                                return false;
                            }
                        });
                        if (stop) {
                            return false;
                        }
                    }
                });
                if (typ == 0) {
                    // 调用计算税金函数 百分比算法(税金*数量*单价)/100
                    sumtax(thisdom);
                } else {
                    // 调用计算税金函数 固定值算法(税金*数量)
                    on_sumtax(thisdom);
                }
            }
        }
    };
    var Ctor = Vue.extend(Main);
    var obj_ctor = new Ctor().$mount('#app_category_' + id);
    // 调用内部函数
    obj_ctor.goryChange(obj_ctor.selectedOptions);
}

/**
 * Cascader BC级联选择器（ALL）
 *
 * @method  渲染Cascader
 * @param   {Number} value 传入所需渲染的个数
 * @time    20180418
 * @author  gan
 */
function bc_ele_cascader_all(value, typ) {
    var arr_box = [];
    for (var i = 0; i <= value; i++) {
        // 每一个数据都转变为对象
        arr_box[i] = {};
        // 复制对象（防止对象重复导致BUG）
        arr_box[i].options = window.globa_selec_gory.options;
        // 添加此对象
        arr_box[i].selectedOptions = [];
        // element-ui所需的数据类型
        var Main = {
            data: function data() {
                return arr_box[i];
            },

            methods: {
                goryChange: function goryChange(val) {
                    // console.log(this);
                    // 数据赋值   PS：this.$el是Vue.js的转DOM操作
                    var thisdom = $(this.$el);
                    thisdom.find('#cate_one').val(val[0]);
                    thisdom.find('#cate_two').val(val[1]);
                    thisdom.find('#product_t').val(val[2]);
                    // 找到对应值，跳出循环
                    var stop = false;
                    // 遍历取出税金
                    $.each(this.options, function (k, v) {
                        if (v.value == val[0]) {
                            // 把级联选择器中第一个分类赋值到 品牌
                            thisdom.parent().parent().find('.brand').children().val(v.label);
                            // thisdom.parent().parent().find('.brand').show();
                            $.each(v.children, function (k1, v1) {
                                // 把级联选择器中第二个分类赋值到 货品名称
                                thisdom.parent().parent().find('.detail').children().val(v1.label);
                                // thisdom.parent().parent().find('.detail').show();
                                if (v1.value == val[1]) {
                                    thisdom.find('#cate_pirce').val(v1.price);
                                    thisdom.parent().parent().find('.chuijin').children().html(v1.price);
                                    $.each(v1.children, function (k2, v2) {
                                        // console.log(v2.label);
                                        // 把级联选择器中第三个分类赋值到 货品类别
                                        thisdom.parent().parent().find('.catname').children().val(v2.label);
                                        // thisdom.parent().parent().find('.catname').show();
                                        stop = true;
                                        return false;
                                    });
                                    if (stop) {
                                        return false;
                                    }
                                }
                            });
                            if (stop) {
                                return false;
                            }
                        }
                    });
                    if (typ == 0) {
                        // alert('1');
                        // 调用计算税金函数 百分比算法(税金*数量*单价)/100
                        sumtax(thisdom);
                    } else {
                        // alert('2');
                        // 调用计算税金函数 固定值算法(税金*数量)
                        on_sumtax(thisdom);
                    }
                }
            }
        };
        var Ctor = Vue.extend(Main);
        new Ctor().$mount('#app_category_' + i);
    }
}


/**
 * Cascader BC级联选择器
 *
 * @method  渲染Cascader
 * @param   {Number} id  所需传入渲染的ID
 * @param   {array}  def 级联选择器的默认选项（传入[]，则清空选择）
 * @time    201180418
 * @author  gan
 */
function bc_ele_cascader(id, def, typ) {
    var arr_box = {};
    arr_box.options = window.globa_selec_gory.options;
    // 添加此对象
    arr_box.selectedOptions = def;
    var Main = {
        data: function data() {
            return arr_box;
        },

        methods: {
            goryChange: function goryChange(val) {
                // 数据赋值   PS：this.$el是Vue.js的转DOM操作
                var thisdom = $(this.$el);
                // console.log(thisdom);
                thisdom.find('#cate_one').val(val[0]);
                thisdom.find('#cate_two').val(val[1]);
                thisdom.find('#product_t').val(val[2]);
                // console.log(val[2]);
                // 找到对应值，跳出循环
                var stop = false;
                // 遍历取出税金
                $.each(this.options, function (k, v) {
                    if (v.value == val[0]) {
                        // 把级联选择器中第一个分类赋值到 品牌
                        thisdom.parent().parent().find('.brand').children().val(v.label);
                        $.each(v.children, function (k1, v1) {
                            // 把级联选择器中第二个分类赋值到 货品名称
                            thisdom.parent().parent().find('.detail').children().val(v1.label);
                            if (v1.value == val[1]) {
                                thisdom.find('#cate_pirce').val(v1.price);
                                thisdom.parent().parent().find('.chuijin').children().html(v1.price);
                                $.each(v1.children, function (k2, v2) {
                                    // console.log(v2.label);
                                    // 把级联选择器中第三个分类赋值到 货品类别
                                    thisdom.parent().parent().find('.catname').children().val(v2.label);
                                    stop = true;
                                    return false;
                                });
                                if (stop) {
                                    return false;
                                }
                            }
                        });
                        if (stop) {
                            return false;
                        }
                    }
                });
                if (typ == 0) {
                    // 调用计算税金函数 百分比算法(税金*数量*单价)/100
                    sumtax(thisdom);
                } else {
                    // 调用计算税金函数 固定值算法(税金*数量)
                    on_sumtax(thisdom);
                }
            }
        }
    };
    var Ctor = Vue.extend(Main);
    var obj_ctor = new Ctor().$mount('#app_category_' + id);
    // 调用内部函数
    obj_ctor.goryChange(obj_ctor.selectedOptions);
}

/**
 *  选择器
 *
 * @method
 * @param   {Number} id  所需传入渲染的ID
 * @time    201180428
 * @author  gan
 */
function ele_brand_select(id, def) {
    var bran_box = [];
    // 数据装载成json
    var bran_tmp = {};
    $.each(brand_list, function (k, v) {
        bran_tmp.value = v.brand_name;
        bran_tmp.label = v.brand_name;
        bran_box.push(bran_tmp);
        bran_tmp = {};
    });

    var Main_brand = {
        data: function data() {
            return {
                restaurants: [],
                state1: def
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
                return bran_box;
            },
            handleSelect: function handleSelect(item) {}
        },
        mounted: function mounted() {
            this.restaurants = this.loadAll();
        }
    };
    var Ctor = Vue.extend(Main_brand);
    new Ctor().$mount('#app_brand_' + id);
}

/**
 *  带搜索input框
 *
 * @method
 * @param   {Number} number  所需传入渲染的ID
 * @time    201180428
 * @author  gan
 */
function ele_input(id, def) {
    var goods_box = [];
    // 数据装载成json
    var goods_tmp = {};
    $.each(user_goods_name, function (k, v) {
        goods_tmp.value = v;
        goods_box.push(goods_tmp);
        goods_tmp = {};
    });
    var Main = {
        data: function data() {
            return {
                restaurants: [],
                state1: def
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
                return goods_box;
            },
            handleSelect: function handleSelect(item) {}
        },
        mounted: function mounted() {
            this.restaurants = this.loadAll();
        }
    };
    var Ctor = Vue.extend(Main);
    new Ctor().$mount('#app_detail_' + id);

}
/**
 * 计算税金（固定）
 *
 * @method  sum
 * @param   {} thisdom 所需的dom
 * @time    20180418
 * @author  gan
 *
 */
function on_sumtax(thisdom) {
    // 税金的位置
    var showglod = thisdom.parent().parent().find('.chuijin').children();
    // 数量
    var aminput = thisdom.parent().parent().find('.amount').children().val();
    // 选择中的税金
    var tax = thisdom.find('#cate_pirce').val();
    if (aminput != '') {
        // 税金计算方法：数量*选中的金额*单价/100
        var upri = aminput * tax;
        var percen = upri.toFixed(2);
        if (isNaN(percen)) {
            $(showglod).html('0.00');
        } else {
            $(showglod).html(percen);
        }
    }
}

/**
 * 计算税金（百分比）
 *
 * @method  sum
 * @param   {} thisdom 所需的dom
 * @time    20180418
 * @author  gan
 *
 */
function sumtax(thisdom) {
    //################ 计算税金 ################ SATA
    // 税金的位置
    var showglod = thisdom.parent().parent().find('.chuijin').children();
    // 数量
    var aminput = thisdom.parent().parent().find('.amount').children().val();
    // 单价
    var uprice = thisdom.parent().parent().find('.price').children().val();
    // 选择中的税金
    var tax = thisdom.find('#cate_pirce').val();
    if (aminput != '' && uprice != '') {
        // 税金计算方法：数量*选中的金额*单价/100
        var upri = (aminput * tax * uprice) / 100;
        var percen = upri.toFixed(2);
        if (isNaN(percen)) {
            $(showglod).html('0.00');
        } else {
            $(showglod).html(percen);
        }
        getsum();
    }
    //################ 计算税金 ################ END
}
/**
 * 计算总价
 *
 * @method   sum
 * @time    20180202
 * @author  gan
 */
var textbox = 0;

function getsum() {
    textbox = 0;
    $('.chuijin').each(function (index, item) {
        textbox += parseFloat($(item).text()) || 0;
    });
    var freed = parseFloat($('#free_duty').val());

    if ((textbox.toFixed(2)) > freed) {
        $('#contper').html(Total_Tax + "（$）：" + textbox.toFixed(2));
    } else {
        $('#contper').html(Total_Tax + "（$）：<span style='color:red;'>" + Duty_Free + "</span>");
    }
}

/**
 *  带搜索input框（单位）
 *
 * @method
 * @param   {Number} number  所需传入渲染的ID
 * @time    201180606
 * @author  gan
 */
function unit_element_input(id, def) {
    var Main = {
        data: function data() {
            return {
                restaurants: [],
                state1: def
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
                return;
            },
            handleSelect: function handleSelect(item) {

            }
        },
        mounted: function mounted() {
            this.restaurants = this.loadAll();
        }
    };
    var Ctor = Vue.extend(Main);

    window.sunit[id] = new Ctor().$mount('#app_sunit_' + id);
}

//限制只能保留两位小数点
function pirce_is_num_val(obj) {
    obj.value = obj.value.replace(/[^\d.]/g, ""); //清除"数字"和"."以外的字符
    obj.value = obj.value.replace(/^\./g, ""); //验证第一个字符是数字
    obj.value = obj.value.replace(/\.{2,}/g, "."); //只保留第一个, 清除多余的
    obj.value = obj.value.replace(".", "$#$").replace(/\./g, "").replace("$#$", ".");
    obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/, '$1$2.$3'); //只能输入两个小数
}

function is_num_val(obj) {
    obj.value = obj.value.replace(/\D/gi, "");
}

//function pirce_is_num_val_t(obj){
//    obj.value = obj.value.replace(/[^\d.+()*-/<>]/g,""); //清除"数字"和"."以外的字符
//    obj.value = obj.value.replace(/^\./g,""); //验证第一个字符是数字
//    obj.value = obj.value.replace(/\.{2,}/g,"."); //只保留第一个, 清除多余的
//    obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
//    obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3'); //只能输入两个小数
//}