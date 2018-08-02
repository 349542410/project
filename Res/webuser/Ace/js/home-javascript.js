$(function(){

	/*  首页图片轮播开始  */
		var index = 0 ;
		var ccr= 0;
		var Swidths = 1920 ;
		var timer = null ;
		var t=1;
		function NextPage()
		{	
			if(index>3)
			{
				index = 0 ;
			}
			switch(index){			
				case 1 :
					$(".btn a.now1").css('background','#d0d1d0');
					$(".btn a.now2").css('background','#cda951');
					$(".btn a.now3").css('background','#d0d1d0');
					$(".btn a.now4").css('background','#d0d1d0');					
					$(".banner_main .pic3").stop().fadeOut(2000);
					$(".banner_main .pic1").stop().fadeOut(2000);
					$(".banner_main .pic4").stop().fadeOut(2000);					
					$(".banner_main .pic2").fadeIn(2000);
					$("#imgs1").css('display','none');	
					$("#imgs3").css('display','none');
					$("#imgs4").css('display','none');	
					$("#imgs2").css('display','block');					
					break;
				case 2 :
					$(".btn a.now1").css('background','#d0d1d0');
					$(".btn a.now2").css('background','#d0d1d0');
					$(".btn a.now3").css('background','#cda951');
					$(".btn a.now4").css('background','#d0d1d0');
					$(".banner_main .pic1").stop().fadeOut(2000);
					$(".banner_main .pic2").stop().fadeOut(2000);
					$(".banner_main .pic4").stop().fadeOut(2000);
					$(".banner_main .pic3").fadeIn(2000);
					$("#imgs1").css('display','none');	
					$("#imgs2").css('display','none');
					$("#imgs4").css('display','none');
					$("#imgs3").css('display','block');							
					break;	
				case 3 :
					$(".btn a.now1").css('background','#d0d1d0');
					$(".btn a.now2").css('background','#d0d1d0');
					$(".btn a.now3").css('background','#d0d1d0');
					$(".btn a.now4").css('background','#cda951');
					$(".banner_main .pic1").stop().fadeOut(2000);
					$(".banner_main .pic2").stop().fadeOut(2000);
					$(".banner_main .pic3").stop().fadeOut(2000);	
					$(".banner_main .pic4").fadeIn(2000);
					$("#imgs1").css('display','none');	
					$("#imgs2").css('display','none');
					$("#imgs3").css('display','none');
					$("#imgs4").css('display','block');							
					break;					
				default:
					$(".btn a.now1").css('background','#cda951');
					$(".btn a.now2").css('background','#d0d1d0');
					$(".btn a.now3").css('background','#d0d1d0');
					$(".btn a.now4").css('background','#d0d1d0');
					$(".banner_main .pic2").stop().fadeOut(2000);
					$(".banner_main .pic3").stop().fadeOut(2000);
					$(".banner_main .pic4").stop().fadeOut(2000);
					$(".banner_main .pic1").fadeIn(2000);
					$("#imgs2").css('display','none');	
					$("#imgs3").css('display','none');
					$("#imgs4").css('display','none');
					$("#imgs1").css('display','block');		 				
					break;	
			}				
		}
			 	
		$('.banner').hover(function(){			
			clearInterval(timer);			
		},function(){	
				timer = setInterval(function(){
					index++ ;
					NextPage();
				},6000);			
		});		
		$(".btn a.now1").click(function(){ 
			index=0;
			NextPage();		
		});
		$(".btn a.now2").click(function(){ 
			index=1;
			NextPage();
		});
		$(".btn a.now3").click(function(){ 
			index=2;
			NextPage();
		});
		$(".btn a.now4").click(function(){ 
			index=3;
			NextPage();
		});
		NextPage();
		timer = setInterval(function(){
			index++ ;
			NextPage();
		},6000);
/**轮播结束*/

/**导航2 开始*/
		var falt=1;
		var ccc=1;
		$("#nav2 li.service").hover(function(){
			
				$("#nav2 li.service p.now1 b.a").css('color','#BA9723');
				$("#nav2 li.service p.now1 b.a").stop().animate({'fontSize':'40px'},"fast",function(){
					$("#nav2 li.service p.now1 b.b").css('color','#BA9723');
					$("#nav2 li.service p.now1 b.b").stop().animate({'fontSize':'40px'},"fast",function(){
						$("#nav2 li.service p.now1 b.c").css('color','#BA9723');
						$("#nav2 li.service p.now1 b.b").animate({'fontSize':'20px'},function(){
							$("#nav2 li.service p.now1 b.b").css('color','#fff');						});
						$("#nav2 li.service p.now1 b.c").stop().animate({'fontSize':'40px'},"fast",function(){
							$("#nav2 li.service p.now1 b.d").css('color','#BA9723');
							$("#nav2 li.service p.now1 b.d").stop().animate({'fontSize':'40px'},"fast",function(){
								$("#nav2 li.service p.now1 b.e").css('color','#BA9723');
								$("#nav2 li.service p.now1 b.e").stop().animate({'fontSize':'40px'},"fast",function(){
									$("#nav2 li.service p.now1 b.f").css('color','#BA9723');
									$("#nav2 li.service p.now1 b.f").stop().animate({'fontSize':'40px'},"fast",function(){
										$("#nav2 li.service p.now1 b.g").css('color','#BA9723');
										$("#nav2 li.service p.now1 b.g").stop().animate({'fontSize':'40px'},"fast",function(){
											$("#nav2 li.service p.now1 b.h").css('color','#BA9723');
											$("#nav2 li.service p.now1 b.h").stop().animate({'fontSize':'40px'},"fast",function(){
												$("#nav2 li.service p.now1 b.i").css('color','#BA9723');
												$("#nav2 li.service p.now1 b.i").stop().animate({'fontSize':'40px'},"fast",function(){
													$("#nav2 li.service p.now1 b.j").css('color','#BA9723');
													$("#nav2 li.service p.now1 b.j").stop().animate({'fontSize':'40px'},"fast",function(){
														$("#nav2 li.service p.now1 b.k").css('color','#BA9723');
														$("#nav2 li.service p.now1 b.k").stop().animate({'fontSize':'40px'},"fast",function(){														
															$("#nav2 li.service p.now1 b.k").animate({'fontSize':'20px'},function(){
																$("#nav2 li.service p.now1 b.k").css('color','#fff');
																});
														});													
														$("#nav2 li.service p.now1 b.j").animate({'fontSize':'20px'},function(){
															$("#nav2 li.service p.now1 b.j").css('color','#fff');
															});
													});
													$("#nav2 li.service p.now1 b.i").animate({'fontSize':'20px'},function(){
														$("#nav2 li.service p.now1 b.i").css('color','#fff');
													});
												});
												$("#nav2 li.service p.now1 b.h").animate({'fontSize':'20px'},function(){
													$("#nav2 li.service p.now1 b.h").css('color','#fff');
												});
											});
											$("#nav2 li.service p.now1 b.g").animate({'fontSize':'20px'},function(){
												$("#nav2 li.service p.now1 b.g").css('color','#fff');
											});
										});
										$("#nav2 li.service p.now1 b.f").animate({'fontSize':'20px'},function(){
											$("#nav2 li.service p.now1 b.f").css('color','#fff');
										});
									});
									$("#nav2 li.service p.now1 b.e").animate({'fontSize':'20px'},function(){
										$("#nav2 li.service p.now1 b.e").css('color','#fff');
									});
								});
								$("#nav2 li.service p.now1 b.d").animate({'fontSize':'20px'},function(){
									$("#nav2 li.service p.now1 b.d").css('color','#fff');
								});
							});
							$("#nav2 li.service p.now1 b.c").animate({'fontSize':'20px'},function(){
								$("#nav2 li.service p.now1 b.c").css('color','#fff');
							});
						});
					});
					$("#nav2 li.service p.now1 b.a").animate({'fontSize':'20px'},function(){
						$("#nav2 li.service p.now1 b.a").css('color','#fff');										

					});
				});			
				$(this).find(".now").stop().animate({'margin-top':'17px'});
				$(this).find(".text").hide();
				
		
		},function(){	
				$("#nav2 li.service p.now1 b").stop().animate({'fontSize':'20px'},function(){
					$(this).css('color','#fff');
				});
				$(this).find(".now").stop().animate({'margin-top':'53px'});				
				$(this).find(".text").css('height','0');			
				$(this).find(".text").show();
				$(this).find(".text"). stop().animate({'height':'53px'});
				
			});

		

		$("#nav2 li.freight").mouseover(function(){
			if(falt==1 || falt==3 || falt==4){
				$("#nav2 li.sit").css('background','none');
				$(this).css('background','#000');			
				$("#nav2 li.sit").width(90);
				$("#nav2 li.query_now").css('display','none');	
				$("#nav2 li.move_now").css('display','none');	
				$("#nav2 li.freight_now").css('display','block');
				$("#nav2 li.freight_now").css('width','328px');	
				$("#nav2 li.freight_now").css('left','-100px');						
				$("#nav2 li.freight_now").stop().animate({'left':'0'});
				doclick_hide();	
				$(this).find('p.a1').css('display','none');
				$(this).find('p.a2').css('display','block');			
				falt=2;				
			}
		});		
		$("#nav2 li.query").mouseover(function(){
			if(falt==1||falt==2||falt==4){
				$("#nav2 li.sit").css('background','none');
				$(this).css('background','#000');
				$("#nav2 li.sit").width(90);						
				$("#nav2 li.freight_now").css('display','none');	
				$("#nav2 li.move_now").css('display','none');
				$("#nav2 li.query_now").css('display','block');
				$("#nav2 li.query_now").css('width','328px');
				$("#nav2 li.query_now").css('left','-100px');				
				$("#nav2 li.query_now").stop().animate({'left':'0'});
				doclick_hide();
				$(this).find('p.a1').css('display','none');
				$(this).find('p.a2').css('display','block');
				falt=3;
			}	
		});		
		$("#nav2 li.move").mouseover(function(){
			if(falt==1||falt==2||falt==3){
				$("#nav2 li.sit").css('background','none');
				$(this).css('background','#000');				
				$("#nav2 li.sit").width(90);				
				$("#nav2 li.freight_now").css('display','none');	
				$("#nav2 li.query_now").css('display','none');	
				$("#nav2 li.move_now").css('display','block');	
				$("#nav2 li.move_now").css('width','328px');
				$("#nav2 li.move_now").css('left','-100px');		
				$("#nav2 li.move_now").stop().animate({'left':'0'});	
				doclick_hide();
				$(this).find('p.a1').css('display','none');
				$(this).find('p.a2').css('display','block');
				falt=4;
			}
		});		
 

		
		// //始发地省市关联
		// $("#nav2 .freight_now .coc .select").click(function(){
		// 	var ttr1="#nav2 .freight_now .coc .select";
		// 	if(ccc==1){				
		// 		$("#nav2 .freight_now .coc .ctt").css('color', '#c58f40');
		// 		$("#nav2 .freight_now .chunk1").css('top','35px');
		// 		doclick_show(ttr1);
		// 		ccc++;
		// 	}else{
		// 		$("#nav2 .freight_now .coc .ctt").css('color', '#000');
		// 		doclick_hide();
		// 		ccc=1;
		// 	}
		// });

		// $("#nav2 .freight_now .coc .ctt").click(function(){
		// 	var ttr1="#nav2 .freight_now .coc .select";
		// 	if(ccc==1){
		// 		$("#nav2 .freight_now .coc .ctt").css('color', '#c58f40');
		// 		$("#nav2 .freight_now .chunk1").css('top','35px');
		// 		doclick_show(ttr1);
		// 		ccc++;
		// 	}else{	
		// 		$("#nav2 .freight_now .coc .ctt").css('color', '#000');			
		// 		doclick_hide();				
		// 	}
		// });
		$("#nav2 .chunk h3 .escs").click(function(){		
			$("#nav2 .chunk1").css('display','none');
			ccc=1;
		});
		//目的地省市关联
		$("#nav2 .freight_now .dest .select").click(function(){	
			var ttr1="#nav2 .freight_now .dest .select";		
			if(ccc==1){
				$("#nav2 .freight_now .dest .ctt").css('color', '#c58f40');
				$("#nav2 .freight_now .chunk1").css('top','75px');
				doclick_show(ttr1);
				ccc++;
			}else{	
				$("#nav2 .freight_now .dest .ctt").css('color', '#000');			
				doclick_hide();				
			}
		});
		$("#nav2 .freight_now .dest .ctt").click(function(){
			var ttr1="#nav2 .freight_now .dest .select";
			if(ccc==1){
				$("#nav2 .freight_now .dest .ctt").css('color', '#c58f40');
				$("#nav2 .freight_now .chunk1").css('top','75px');
				doclick_show(ttr1);
				ccc++;
			}else{	
				$("#nav2 .freight_now .dest .ctt").css('color', '#000');			
				doclick_hide();				
			}
		});
		// //客服查询省市关联
		// $("#nav2 .query_now .insert .select").click(function(){	
		// 	var ttr1="#nav2 .query_now .insert .select";		
		// 	if(ccc==1){
		// 		$("#nav2 .query_now .insert .ctt").css('color', '#c58f40');				
		// 		$("#nav2 .query_now .chunk1").css('display','block');
		// 		doclick_show(ttr1);
		// 		ccc++;
		// 	}else{	
		// 		$("#nav2 .query_now .insert .ctt").css('color', '#000');			
		// 		doclick_hide();				
		// 	}
		// });
		// $("#nav2 .query_now .insert .ctt").click(function(){
		// 	var ttr1="#nav2 .query_now .insert .select";
		// 	if(ccc==1){
		// 		$("#nav2 .query_now .insert .ctt").css('color', '#c58f40');
		// 		$("#nav2 .query_now .chunk1").css('display','block');
		// 		doclick_show(ttr1);
		// 		ccc++;
		// 	}else{	
		// 		$("#nav2 .query_now .insert .ctt").css('color', '#000');			
		// 		doclick_hide();				
		// 	}
		// });

		function doclick_show(d){
			var ttr2=d;			
			dopotion(ttr2);	
			$("#nav2 .freight_now .chunk").css('display','block');
			$("#nav2 .chunk #hot").click(function(){
				dopotion(ttr2);
			});
			$("#nav2 .chunk #potion").click(function(){	
			    var ttr= ttr2;
				$("#nav2 .chunk #hot").removeClass('ccs');
				$("#nav2 .chunk #city").removeClass('ccs');				
				$(this).addClass('ccs');
				var thtml='';				
				for( var i in where){
					thtml+='<a citys="'+where[i].locacity+'">'+where[i].loca+'</a>';
				}
				thtml+='<div class="clear"></div>';
				$("#nav2 .chunk #main2").css('display','none');
				$("#nav2 .chunk #main1").css('display','block');
				$("#nav2 .chunk #main1").html(thtml);
				docity(ttr);
			});
			$("#nav2 .chunk #city").click(function(){
				$("#nav2 .chunk #hot").removeClass('ccs');
				$("#nav2 .chunk #potion").removeClass('ccs');
				$(this).addClass('ccs');
				$("#nav2 .chunk #main1").css('display','none');				
				$("#nav2 .chunk #main2").css('display','block');

			});
		}
		function doclick_hide(){
			$("#nav2 span.ctt").css('color','#000');
			$("#nav2 li.freight").find('p.a1').css('display','block');
			$("#nav2 li.query").find('p.a1').css('display','block');
			$("#nav2 li.move").find('p.a1').css('display','block');	
			$("#nav2 li.freight").find('p.a2').css('display','none');
			$("#nav2 li.query").find('p.a2').css('display','none');
			$("#nav2 li.move").find('p.a2').css('display','none');									
			$("#nav2 .chunk1").css('display','none');
			ccc=1;
		}
		function dopotion(d){
				var ttr=d;
				$("#nav2 .chunk #potion").removeClass('ccs');
				$("#nav2 .chunk #city").removeClass('ccs');
				$("#nav2 .chunk #hot").addClass('ccs');
				var thtml='';
				for( var i=0;i<hot.length;i++){
					var t=hot[i];
					thtml+='<a citys="'+where[t].locacity+'">'+where[t].loca+'</a>';
				}
				thtml+='<div class="clear"></div>';
				$("#nav2 .chunk #main2").css('display','none');
				$("#nav2 .chunk #main1").css('display','block');
				$("#nav2 .chunk #main1").html(thtml);				
				docity(ttr);				
		}
		function docity(da){
			var ttr3= da;			
			$("#nav2  .chunk #main1 a").click(function(){
				$("#nav2 .chunk #hot").removeClass('ccs');
				$("#nav2 .chunk #potion").removeClass('ccs');
				$("#nav2 .chunk #city").addClass('ccs');
				var t =$(this).attr("citys");
				var op=$(this).text();
				t=t.split("|");
				var tht='';
				for(var j in t){
					tht+='<a potion="'+op+'">'+t[j]+'</a>';
				}
				tht+='<div class="clear"></div>';
				$("#nav2 .chunk #main1").css('display','none');
				$("#nav2 .chunk #main2").css('display','block');
				$("#nav2 .chunk #main2").html(tht);
				$("#nav2 .chunk #main2 a").click(function(){
					var pot=$(this).attr('potion')+'-'+$(this).text();
					$(ttr3).val(pot);					
					$("#nav2 .chunk1").css('display','none');
					ccc=1;
				});
			});
		}
/**导航2结束**/

/**输入框焦点事件**/	
	$("#dobtns").click(function(){
		var v=$("#dotext").val();
		var xx =/^(MK)\w{11}$/;
		var zz =/^\w{10,}$/;
		if(!zz.test(v) && !(/^(MK)\w{0,}$/).test(v)){
			$("#hint label").html(MK_no_alert1);
			$("#hint").css({'display':'inline-block','line-height':'24px'});
			return false;
		}

		if(!xx.test(v) && (/^(MK)\w{0,}$/).test(v) && (/^\w{1,}$/).test(v)){
			$("#hint label").html(MK_no_alert2);
			$("#hint").css({'display':'inline-block','line-height':'24px'});
			return false;
		} 		
	})
	$("#dobtns2").click(function(){		
		var v2=$("#money2").val();	
		var rw=1;	
		var v3=$("#money2").attr('moneys');
		v3=v3*1;
		var v=$("#dotext").val();
		var xx =/^(MK)\w{11}$/;
		if(!xx.test(v)){
			$("#hint").css('display','inline');
			return false;
		}else if(v2===''){
			$("#hint2 samp").html('提示:金额必须是数字，且必须余额之内！');
			$("#hint2").css('display','inline');
			return false;
		}else if(!isNaN(v2) && v2>v3){
			$("#hint2 samp").html('您输入的"'+v2+'",大于您的余额'+v3+'!');
			$("#hint2").css('display','inline');
			return false;
		}else if(isNaN(v2) || v2<=0){
			$("#hint2 samp").html('您输入的"'+v2+'",格式不正确！');
			$("#hint2").css('display','inline');
			return false;
		}
		else{
			if(rw==1){
				return true;
				rw++;
			}else{
				return false;
			}			
		}
	});

	// $("#dotext").focus(function(){
	// 	$("#hint,#hint2").css('display','none');		
	// 	// $(this).css('border','1px solid #409DFE');

	// })
	$("#money2").focus(function(){
		$("#hint,#hint2").css('display','none');		
		$(this).css('border','1px solid #409DFE');

	})
	// $("#dotext,#money2").blur(function(){
	// 	 // $(this).css('border','1px solid #C58F40');
	// })
	 // $("#dotext").focus();
/**输入框焦点事件结束**/

/**导航下划线开始**/
	$(".header .nav a").hover(function(){
		$(this).find('p').css('display','block');
		$(this).find('p').stop().animate({'margin-top':'-30px'},"fast");		
	},function(){
		var t=$(this).find('p');
		$(this).find('p').stop().animate({'margin-top':'-20px'},"fast",function(){
			t.css('display','none');
		});		
	});
/*导航下划线结束**/

/**运费查询开始**/
$("#freight_ins").click(function(){
	var ac=$("#selectas").val();
	var c_c= $("#selectas").attr('placeholder');
	// 请选择省市区
	if(ac===""){
		alert_custom(c_c);
	}else{
		// 判断单位千克（磅）
		var kg_lb = $("#kg_lb_z1 .val").attr("cid");
		if(kg_lb == 0){
			var k = $("#fre_kg").val();
		}else{
			var k = $("#fre_lb").val();
		}

		k=Number(k);
		var t='';
		if(k<=1 && k>0){
			t=50;
		}else if(k>1){
			if(k%0.5==0){
				var c = k/0.5;
				t= c*25;
			}else{
				var c = k/0.5;
				c=parseInt(c);
				t= c*25+25;
			}
			// 转美元
			

		}else{
			alert_custom('请输入正确的数字！');
			return false;
		}
		alert_custom("$" + t,"运费金额");
	}
});
/**运费查询结束**/	

// 新版轮播  20161025 伦
	var lunboW = 1920;
	var lunboH = 1080;
	var quan_w = document.body.clientWidth;
	var quan_h = document.body.clientHeight ;	
	var height_c = lunboH/lunboW*quan_w;
	window.show_type = 1;     // 切换方式 1  ：渐隐 ， 2  ：滑动切换
	window.jiange_time = 5000;

	window.timeIDS = null;
	window.timeIDS2 = null;

	if((lunboW/lunboH)>(quan_w/quan_h)){		
		var height_c = lunboH/lunboW*quan_w;
		$(".banner_vz,.banner_vz .pic_vz").css("height",quan_h+'px');
		$(".banner_vz .pic_vz").find('div').css("height",quan_h+'px');
		$(".banner_vz .pic_vz").find('div').css("background-size",'auto 100% ');
	}else{
		$(".banner_vz,.banner_vz .pic_vz").css("height",height_c+'px');
		$(".banner_vz .pic_vz").find('div').css("height",height_c+'px');
		$(".banner_vz .pic_vz").find('div').css("background-size",'100% auto ');

	}

	// 轮播样式2 
	if(show_type == 2){
		var quan_w = document.body.clientWidth;
		var quan_h = document.body.clientHeight ;
		// $("#pic_vz").css('position','absolute');
		// $("#pic_vz").css('width','quan_w');
		// $("#pic_vz").css('top',0);
		// $("#pic_vz").css('left',0);
		$(".pic_vz").css('width',quan_w*10);
		$(".pic_vz").css('position','absolute');
		$(".pic_vz").css('top',0);
		$(".pic_vz").css('left',"0");
		var ccd = $(".pic_vz").find('div');
		ccd.css('position','absolute');
		ccd.css('top',0);
		ccd.css('width',quan_w);
		ccd.css('display','block');

		for(var i = 1; i<ccd.length; i++){
			ccd.eq(i).css('left',i*quan_w + 'px');
		}
	}


	$(".banner_vz img").remove();

	// 自动轮播
	timeIDS=setInterval(lunbo_vz,jiange_time);

	// 点选切换
	$("#show_vz span").click(function(){
		var cid2 = $(this).attr('cid');	
		
		lunbo_click(cid2);
		
	})		


	//客服拖拽
	if($( ".k_service" ).length == 1){
		$( ".k_service" ).draggable();  
	}	

	 //绑定滚动条事件  
   	$(window).scroll(function(){ 
   		var cid = $(".header").eq(0).attr('cid');
        var sTop = $(window).scrollTop();  
       
        // alert(sTop);
        if (sTop < 135) {  
        	if(cid == 1){
               $(".header").fadeIn(300,function(){
               		$(".header").eq(0).css('opacity',1);
               		$(".header").eq(1).css('opacity',0.8);
               });  
               $(".header").eq(0).attr('cid',0);
        	}
			$(".header").unbind('hover');
         
        }else{  
        	if(cid == 0){
               $(".header").stop().fadeOut(1000); 
               $(".header").eq(0).attr('cid',1);

         	}
        }
        
    }); 
	 $(document).mousemove(function(e){
   		var cid = $(".header").eq(0).attr('cid');
   		var sTop = $(window).scrollTop();  
	    if(Number(e.clientY) < 50 && cid == 1 && sTop > 135){	  		
	    	$(".header").stop().fadeIn(1000,function(){
	    		// alert(e.clientY);
	    		$(".header").eq(0).css('opacity',1);
               	$(".header").eq(1).css('opacity',0.8);
	    		$(".header").eq(0).attr('cid',0);
	    		$(".header").eq(0).hover(function(){},function(){
	    			$(".header").stop().fadeOut(1000); 
               		$(".header").eq(0).attr('cid',1);
               		$(this).unbind('hover');
	    		})
	    	});
           
	    }
	  });

	 // 单位转换下拉 

	 $("#kg_lb_z1").click(function(){

	 	// 获取下拉框状态
	 	var sel = $(this).attr('sel');

	 	if(sel == 0){
	 		// 展开状态
	 		$(this).find(".ab_div").css("display","block");
	 		$(this).attr('sel',1);
	 	}else{
	 		// 关闭状态
	 		$(this).find(".ab_div").css("display","none");
	 		$(this).attr('sel',0);
	 	}


	 })

	 // 单位转换下拉 下拉框选中事件
	 $("#kg_lb_z1 .ab_div label").click(function(){
	 	$("#kg_lb_z1 .ab_div label").removeClass("sel");
	 	$(this).addClass("sel");
	 	var cid 	= $(this).attr("cid");
	 	var code	= $(this).html();
	 	$("#kg_lb_z1 .val").attr("cid",cid);
	 	$("#kg_lb_z1 .val").html(code);
	 	// 更改单位
	 	if(cid == 1){
	 		$("#kg_lb_z4").html(home_lb);

	 	}else{
	 		$("#kg_lb_z4").html(home_kg);
	 	}

	 	// 重新换算
	 	the_matrixing();

	 })
	 
	$("#fre_kg").change(function(){
		var val = $(this).val();
		$(this).attr("title",val);
		// 重新换算
	 	the_matrixing();

	 	var val = $("#fre_lb").val();
		$("#fre_lb").attr("title",val);

	});
	


	// 隐藏弹出框
	$("#bj_cu , #submit_cu").click(function(){

		// 清除弹出框内容
		$("body").css({overflow:"auto"});    // 解除禁用滚动条
		$("#alert_custom").hide();

	})



});


// 控制首页 广告
function lunbo_vz(){	
	var show_vz = $("#show_vz");
	// 获取指针
	var d_id = $("#pic_vz .pic_vz").attr('cid');
	d_id++;	
	if(d_id > 4){
		d_id = 1;
	}	
	show_type == 1 ? lunbo_1(d_id): lunbo_2(d_id);		
}


// 下拉按钮
function xiala_vz(){		
	var tops =  $(".content_vz").offset().top;
	$('html, body').animate({scrollTop:tops-20}, 'slow');
}
function xiala_vzs(){		
	var tops =  $(".content").offset().top;
	$('html, body').animate({scrollTop:tops}, 'slow');
}
// 返回至顶部
function top_vz(){
	$('html, body').stop().animate({scrollTop:0}, 'slow');
}
// 轮播指针加 
function add_lunbo(){
	var d_id = $("#pic_vz .pic_vz").attr('cid');
	d_id++;
	if(d_id > 4){
		d_id = 1;
	}
	lunbo_click(d_id);
}

// 轮播指针减
function jian_lunbo(){
	var d_id = $("#pic_vz .pic_vz").attr('cid');
	d_id--;
	if(d_id < 1){
		d_id = 4;
	}
	lunbo_click(d_id);
}

function lunbo_click(cid2){
	var show_vz = $("#show_vz");
	var d_idc = $("#pic_vz .pic_vz").attr('cid');		
	clearTimeout(timeIDS);
	if(cid2 != d_idc){
		show_type == 1 ? lunbo_1(cid2): lunbo_2(cid2);
	}		
	timeIDS=setInterval(lunbo_vz,5000);
}

function lunbo_1(cid2){
	var show_vz = $("#show_vz");
	cid2 = cid2*1;						
	if(cid2 == 1){				
		$("#pic_vz .pic_vz").attr('cid',cid2);
		show_vz.attr('class','pic1');
		$("#pic_vz .pic_vz div").stop().fadeOut(1000);
		$("#pic_vz .pic_vz .swipe_1").stop().fadeIn(600,function(){
       		$(this).css('opacity',1);
       	});			
	}else if(cid2 == 2){
		$("#pic_vz .pic_vz").attr('cid',cid2);
		show_vz.attr('class','pic2');
		$("#pic_vz .pic_vz div").stop().fadeOut(1000);
		$("#pic_vz .pic_vz .swipe_2").stop().fadeIn(600,function(){
       		$(this).css('opacity',1);
       	});			
	}else if(cid2 == 3){
		$("#pic_vz .pic_vz").attr('cid',cid2);
		$("#pic_vz .pic_vz div").stop().fadeOut(1000);
		$("#pic_vz .pic_vz .swipe_3").stop().fadeIn(600,function(){
       		$(this).css('opacity',1);
       	});
		show_vz.attr('class','pic3');				
	}else if(cid2 == 4){
		$("#pic_vz .pic_vz").attr('cid',cid2);
		show_vz.attr('class','pic4');
		$("#pic_vz .pic_vz div").stop().fadeOut(1000);
		$("#pic_vz .pic_vz .swipe_4").stop().fadeIn(600,function(){
       		$(this).css('opacity',1);
       	});					
	}
}
function lunbo_2(cid2){
	var show_vz = $("#show_vz");
	var quan_w = document.body.clientWidth;
	cid2 = cid2*1;	
	$("#pic_vz .pic_vz").attr('cid',cid2);
	show_vz.attr('class','pic'+cid2);
	$(".pic_vz").stop().animate({'left':-quan_w*(cid2-1)},400);
	
}

// 重新换算
function the_matrixing(){
	// 获取参数
	var val_code = $("#fre_kg").val(); // 数量
	var unit 	 = $("#kg_lb_z1 .val").attr("cid"); // 1 kg 2 lb
	var bl_rate  = 2.2046226;  	// 1千克
	var kg_rate  = 0.4535924;  	// 1磅
	var values	 = 0; 			//结果值
	
	
	if( unit == 1 	){
		// 千克转磅
		values = (val_code * bl_rate).toFixed(1); 			// 精确到一位小数
	}else{
		// 磅转千克
		values = (val_code * kg_rate).toFixed(1); 			// 精确到一位小数
	}
	$("#fre_lb").val(values);



}


// 运费弹框函数 a(内容)，b(标题)
function alert_custom(a , b){
	
	var title 	= b;
	var content = a;

	if(!a) return false;
	if(!b) title = alert_title;

	$("#alert_custom .title").html(title);
	$("#alert_custom .cont").html(content);
	$("body").css({overflow:"hidden"});    //禁用滚动条
	$("#alert_custom").css("display","block");

}






