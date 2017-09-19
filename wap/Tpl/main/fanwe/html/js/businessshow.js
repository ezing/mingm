$(function() {
	$(".ka_city_right").click(function() {
		if($(".hotshow_title_down").is(":hidden")) {
			$(".hotshow_title_down").slideDown();
		} else {
			$(".hotshow_title_down").slideUp();
		}
	});
	 var top = 0;
	$(".all_classify").click(function() {					  
		if($(".list_classify").is(":hidden")) {
			top = $(window).scrollTop();
			$('body').css("top",-top+"px");
			$('body').css("position","fixed");
			$(".list_classify").fadeIn();
			$(".city_classify").fadeOut();
			$(".synthesis_subnav").fadeOut();
			$(".synthesis_classify img").attr("src","./Tpl/main/fanwe/html/img/hotshow/icon/down.png")
			$(".allcity_classify img").attr("src","./Tpl/main/fanwe/html/img/hotshow/icon/down.png")
			$(".all_classify img").attr("src","./Tpl/main/fanwe/html/img/hotshow/icon/fenlei3.png")
		} else {
			$('body').css("position","relative");
			$(window).scrollTop(top);
			$(".list_classify").fadeOut();
			$(".all_classify img").attr("src","./Tpl/main/fanwe/html/img/hotshow/icon/down.png")
			
		}
	})
	
		$(".allcity_classify").click(function() {
		if($(".city_classify").is(":hidden")) {
				top = $(window).scrollTop();
			$('body').css("top",-top+"px");
			$('body').css("position","fixed");
			$(".list_classify").fadeOut();
			$(".city_classify").fadeIn();
			$(".synthesis_subnav").fadeOut();
			$(".synthesis_classify img").attr("src","./Tpl/main/fanwe/html/img/hotshow/icon/down.png")
			$(".allcity_classify img").attr("src","./Tpl/main/fanwe/html/img/hotshow/icon/fenlei3.png")
			$(".all_classify img").attr("src","./Tpl/main/fanwe/html/img/hotshow/icon/down.png")
		} else {
			$('body').css("position","relative");
			$(window).scrollTop(top);
			$(".city_classify").fadeOut();
			$(".allcity_classify img").attr("src","./Tpl/main/fanwe/html/img/hotshow/icon/down.png")
			
		}
	})
	$(".synthesis_classify").click(function() {
			top = $(window).scrollTop();
			$('body').css("top",-top+"px");
			$('body').css("position","fixed");
		if($(".synthesis_subnav").is(":hidden")) {
			$(".list_classify").fadeOut();
			$(".city_classify").fadeOut();
			$(".synthesis_subnav").fadeIn();
			$(".synthesis_classify img").attr("src","./Tpl/main/fanwe/html/img/hotshow/icon/fenlei3.png")
			$(".allcity_classify img").attr("src","./Tpl/main/fanwe/html/img/hotshow/icon/down.png")
			$(".all_classify img").attr("src","./Tpl/main/fanwe/html/img/hotshow/icon/down.png")
		} else {
			$('body').css("position","relative");
			$(window).scrollTop(top);
			$(".synthesis_subnav").fadeOut();
			$(".synthesis_classify img").attr("src","./Tpl/main/fanwe/html/img/hotshow/icon/down.png")
			
		}
	})
	var navLi = $(".list_classify_nav ul li");
	var subBox = $(".list_classify_subnav");
	subBox.eq(0).addClass("show");
	navLi.click(function() {
		subBox.eq($(this).index()).addClass("show").siblings().removeClass("show");
		$(this).css("background","#eee").siblings().css("background","#fff");
	});
	
	var cityLi = $(".city_classify_nav ul li");
	var cityBox = $(".city_classify_subnav");
		cityLi.click(function() {
		cityBox.eq($(this).index()).addClass("show").siblings().removeClass("show");
		$(this).css("background","#eee").siblings().css("background","#fff");
	});
})