function show_receipt(select){
    $.ajax({
        url:pieurl,
        type:'GET', 
        cache: false,
        dataType:'json',    
        data:{
           'order_id' : id
        },
        success:function(data){
            $(select).attr('src',data.path);
        },
    });
}