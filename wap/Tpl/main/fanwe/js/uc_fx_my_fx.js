$(function(){

	$('.share_btn').click(function(){
		$('#box_share').addClass('box_share-ed');


		var dta_url = $(this).attr("data-url");
		var sub_name = $(this).attr("data-name");
		var img_url =  $(this).attr("data-img");
		jiathis_config = {
			 	title:sub_name,
			    url:dta_url,
			    pic:img_url
			};
	});
	
	$('.qrcode_btn').click(function(){
		$('#box_share').addClass('box_share-ed');
		var dta_url = $(this).attr("data-url");
		var sub_name = $(this).attr("data-name");
		var img_url =  $(this).attr("data-img");
		jiathis_config = {
			 	title:sub_name,
			    url:dta_url,
			    pic:img_url
			};
	});
	
	
	
	$('#boxclose_share').click(function(){
		$('#box_share').removeClass('box_share-ed');
	});

	$(window).scroll(function(){
		$('#box_share').removeClass('box_share-ed');
	});
	
	$(".my_mall").bind("click",function(){
		if($(this).attr("init")==1){
			$(this).attr("init",0);
			$(".my_mall_qrcode").addClass('my_mall_qrcode-ed');
		}else{
			
			$(this).attr("init",1);
			$(".my_mall_qrcode").removeClass('my_mall_qrcode-ed');
		}
		
		
	});
	
	
	
});
