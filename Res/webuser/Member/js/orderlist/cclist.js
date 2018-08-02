/**
 * 更新锁定状态
 *
 * @method   update
 * @param   {Number} num  增加的数值
 * @time    20180202
 * @author  gan
 */
function clock_row(num) {

    var obj = $('#copy_btn');
    if (num >= 30) {
        layer.open({
            title: "{:L('LAY_SystemMessage')}",
            offset: '300px',
            icon: 0,
            skin: 'demo-class', //自定义样式demo-class
            content: "{:L('LAY_AddRow')}",
        });
        $(obj).addClass('disabled');
        $(obj).attr('disabled', 'disabled');
        return false;
    } else {
        $(obj).removeClass('disabled');
        $(obj).removeAttr('disabled', 'disabled');
        return true;
    }
}

/**
 * 添加行
 *
 * @method   add row
 * @param   {object} info 设置默认值
 * @time    20180202
 * @author  gan
 */
function copy_row(info, tax_method) {
    // 设置默认值
    if (info == undefined) {
        info = {
            category_one: '',
            category_two: '',
            brand: '',
            detail: '',
            catname: '',
            spec_unit: '',
            amount: '',
            is_suit: '',
            price: '',
            remark: ''
        };
    }
    // 控制行数的值
    var num = $("#num").val();
    // 只加的值
    var max_row = $('#max_row').val();
    if (clock_row(num)) {
        max_row++;
        $('#max_row').val(max_row);
        num++;

        var str = '';
        str += '<tr class="stta" cid="' + max_row + '">';

        // <!-- 复制行<a onclick="copy_row(this)" href="javascript:void(0)">增加</a> -->
        str += '<td class="copy_r"><a onclick="del_row(this)" href="javascript:void(0)">' + l_del_lng + '</a></td>';
        str += '<input type="hidden" name="oid_' + max_row + '" value=""/>';

        // <!-- 货品列表 -->
        str += '<td class="seleopt goods_width">';
        str += '<div id="app_category_' + max_row + '">';
        str += '<div class="block" id="selec_box">';
        str += '<el-cascader v-model="selectedOptions" placeholder="' + categorys_lng + '" :options="options" filterable @change="goryChange"></el-cascader>';
        str += '<input type="hidden" name="category_one_' + max_row + '" id="cate_one" value="">';
        str += '<input type="hidden" name="category_two_' + max_row + '" id="cate_two" value="">';
        str += '<input type="hidden" id="cate_pirce" value="">';
        str += '</div';
        str += '</div>';
        str += '</td>';

        //<!-- 货品名称(中文) -->
        str += '<td class="detail cnlist_width">';
        str += '<div id="app_detail_' + max_row + '">';
        str += '<el-row class="demo-autocomplete">';
        str += '<el-col :span="20">';
        str += '<el-autocomplete class="inline-input" clearable v-model="state1" :fetch-suggestions="querySearch" placeholder="' + input_content_lng + '" name="detail_' + max_row + '" @select="handleSelect"></el-autocomplete>';
        str += '</el-col>';
        str += '</el-row>';
        str += '</div>';
        str += '</td>';

        // <!-- 英文品牌 -->
        str += '<td class="brand enlist_width">';
        str += '<div id="app_brand_' + max_row + '">';
        str += '<el-row class="demo-autocomplete">';
        str += '<el-col :span="20">';
        str += '<el-autocomplete class="inline-input" clearable v-model="state1" :fetch-suggestions="querySearch" placeholder="' + input_content_lng + '" name="brand_' + max_row + '" @select="handleSelect"></el-autocomplete>';
        str += '</el-col>';
        str += '</el-row>';
        str += '</div>';
        str += '</td>';


        //<!-- 单价 -->
        str += '<td class="price pirce_width">';
        str += '<input type="text" name="price_' + max_row + '" class="form-control" style="width: 83px;" value="' + info.price + '" maxlength="9" onblur="priper(this)">';
        str += '</td>';

        //<!-- 规格/容量 -->
        str += '<td class="catname sunit_width">';
        str += '<input type="text" name="catname_' + max_row + '" value="' + info.catname + '" style="width: 60px;display: inline-block;    margin-top: -32px;margin-right:5px;" class="form-control">';
        str += '<div id="app_sunit_' + max_row + '" style="display: inline-block;width: 106px;">';
        str += '<el-row class="demo-autocomplete">';
        str += '<el-col :span="50">';
        str += '<el-autocomplete class="inline-input" clearable v-model="state1" :fetch-suggestions="querySearch" placeholder="' + l_plan_unit + '" name="spec_unit_' + max_row + '" @select=""></el-autocomplete>';
        str += '</el-col>';
        str += '</el-row>';
        str += '</div>';
        //str += '<select name="spec_unit_'+max_row+'" class="form-control spec_unit'+max_row+'" id="spec_unit" style="margin-left:4px;">';
        //    str += '<option value="">'+unit_lng+'</option>';
        //str += '</select>';
        str += '</td>';

        //<!-- 数量 -->
        str += '<td class="amount amount_width">';
        str += '<input type="text" name="amount_' + max_row + '" value="' + info.amount + '" class="form-control" style="width: 60px;" maxlength="9" onblur="amper(this)">';
        //str += '<select name="num_unit_'+max_row+'" class="form-control num_unit'+max_row+'" id="num_unit" style="margin-left:4px;">';
        //str += '<option value="">'+unit_lng+'</option>';
        //str += '</select>';
        str += '</td>';

        //<!-- 是否套装 -->
        str += '<td class="isall_width">';
        str += '<div class="checkbox">';
        str += '<label><input type="checkbox" name="is_suit_' + max_row + '" value="1" style="margin:0;vertical-align: middle;margin-top: -2px;cursor: pointer;" /></label>';
        str += '</div>';
        str += '</td>';

        //<!-- 税金 -->
        str += '<td class="chuijin tax_width"><label>0.00</label></td>';

        //<!-- 产地 -->
        str += '<td style="display: none">';
        str += '<input type="text" name="source_area_' + max_row + '" value="美国" class="clzhong" maxlength="9">';
        str += '</td>';

        //<!-- 货币 -->
        str += '<input type="hidden" name="coin_' + max_row + '" value="USD">';

        //<!-- 备注 -->
        str += '<td class="reamk_width">';
        str += '<input type="text" value="' + info.remark + '" name="remark_' + max_row + '" style="width: 145px;" placeholder="' + order_info_lng + '" class="form-control">';
        str += '</td>';

        str += '</tr>';


        $("#table").append(str);
        $("#num").val(num);
        unit_element_input(max_row, info.spec_unit);

        ele_cascader(max_row, [info.category_one, info.category_two], tax_method);
        ele_brand_select(max_row, info.brand);
        ele_input(max_row, info.detail);


        var is_suit_l = info.is_suit;
        if (is_suit_l == 1) {
            $('input[name="is_suit_' + max_row + '"]').eq(0).prop('checked', true);
        }
        //$(".spec_unit"+max_row).find("option[value='"+info.spec_unit+"']").attr("selected",true);
        //$(".num_unit"+max_row).find("option[value='"+info.num_unit+"']").attr("selected",true);
    }
}

/**
 * 删除行
 *
 * @method   del
 * @param   {object} obj 当前对象
 * @time    20180202
 * @author  gan     
 */
function del_row(obj) {
    var num = $("#num").val();
    num--;
    $("#num").val(num);
    // console.log(window.acname);
    if (window.acname == 'edit') {
        var ttc = $(obj).parent().parent().find('.ttc').attr('value');

        layer.confirm(lay_del, {
            icon: 3,
            offset: '300px',
            btn: [lay_btnok, lay_btncancel],
            title: lay_information
        }, function () {
            if (ttc) {
                $.ajax({
                    url: orderdel_url,
                    type: "POST",
                    data: {
                        'ttc': ttc,
                    },
                    dataType: "json",
                    success: function (result) {
                        if (result.state == '1') {
                            layer.msg(result.msg, {
                                icon: 6,
                                offset: '350px'
                            });
                            $(obj).parent().parent('tr').remove();
                            clock_row(num);
                        } else {
                            layer.open({
                                type: 0,
                                title: lay_systemmessage,
                                content: result.msg,
                                icon: 5,
                                skin: 'demo-class', //自定义样式demo-class
                                offset: '300px',
                                btn: lay_btnok,
                            });
                        }
                    }
                });
            } else {
                $(obj).parent().parent().remove();
                layer.msg( {
                    icon: 6,
                    offset: '350px'
                });
                clock_row(num);
            }
        });


    } else {
        $(obj).parent().parent('tr').remove();
        clock_row(num);
    }
}