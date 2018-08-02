$(function(){               
  $("#loc_province").change(function(){
    var sit= $(this).children('option:selected').html();
    if(sit==="省份"){             
      $("#province").attr("value","");
      $("#city").attr("value","");
      $("#town").attr("value","");
    }else{
      $("#province").attr("value",sit);
    } 
    fz_value();           
  });

  $("#loc_city").change(function(){
    var sit= $(this).children('option:selected').html();
    if(sit==="地级市"){
      $("#city").attr("value","");
      $("#town").attr("value","");
    }else{
      $("#city").attr("value",sit);
    }
    fz_value();             
  });
  $("#loc_town").change(function(){
    var sit= $(this).children('option:selected').html();
    if(sit==="市、县、区"){
      $("#town").attr("value","");
    }else{
      $("#town").attr("value",sit);
    }
    fz_value();
  });
  $("#RecAddress").change(function(){
    fz_value();
  });
  $("#loc_province").css({'display':'none','border':'none','margin':'0','position':'absolute','left':'10px','top':'15px','z-index':'-1'});
  $("#loc_city").css({'display':'none','border':'none','margin':'0','position':'absolute','left':'145px','top':'15px','z-index':'-1'});
  $("#loc_town").css({'display':'none','border':'none','margin':'0','position':'absolute','left':'280px','top':'15px','z-index':'-1'});

  function fz_value(){
    var pro=$("#province").attr("value");
    var cit=$("#city").attr("value");
    var tow=$("#town").attr("value");
    // alert(pro+cit+tow);
      $("#cpp_s").html(pro+cit+tow);
      var span_w=$("#cpp_s").width();
      $("#RecAddress").width(474-span_w);

      // var tte= pro+cit+tow;
      // var tte2=$("#RecAddress1").val();        
      // $("#RecAddress2").attr("value",tte+tte2);
  }

  fz_value();
});


$(function(){               
  $("#loc_province1").change(function(){
    var sit= $(this).children('option:selected').html();
    if(sit==="U.S.A"){             
      $("#province1").attr("value","");
      $("#city1").attr("value","");
      $("#town").attr("value","");
    }else{
      $("#province1").attr("value",sit);
    } 
    fz_value1();           
  });

  $("#loc_city1").change(function(){
    var sit= $(this).children('option:selected').html();
    if(sit==="州"){
      $("#city1").attr("value","");
      $("#town1").attr("value","");
    }else{
      $("#city1").attr("value",sit);
    }
    fz_value1();             
  });
  $("#loc_town1").change(function(){
    var sit= $(this).children('option:selected').html();
    if(sit==="市"){
      $("#town1").attr("value","");
    }else{
      $("#town1").attr("value",sit);
    }
    fz_value1();
  });
  $("#PostAddress").change(function(){
    fz_value1();
  });
  $("#loc_province1").css({'display':'none','border':'none','margin':'0','position':'absolute','left':'10px','top':'15px','z-index':'-1'});
  $("#loc_city1").css({'display':'none','border':'none','margin':'0','position':'absolute','left':'145px','top':'15px','z-index':'-1'});
  $("#loc_town1").css({'display':'none','border':'none','margin':'0','position':'absolute','left':'280px','top':'15px','z-index':'-1'});

  function fz_value1(){
    var pro=$("#province1").attr("value");
    var cit=$("#city1").attr("value");
    var tow=$("#town1").attr("value");
    // alert(pro+cit+tow);
      $("#cpp_s1").html(pro+cit+tow);
      var span_w=$("#cpp_s1").width();
      $("#PostAddress").width(474-span_w);

      // var tte= pro+cit+tow;
      // var tte2=$("#RecAddress1").val();        
      // $("#RecAddress2").attr("value",tte+tte2);
  }

  fz_value1();
});