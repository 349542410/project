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


// 需要加载两个js文件
//    <script src="__PUBLIC__/js/jquery-1.9.1.min.js"></script>
//   <script src="__JS__/login.js"></script>