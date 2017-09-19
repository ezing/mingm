$(document).ready(function(){
	/*名店推荐*/
	// swiper_container();
	// $(window).resize(function(){
	// 	swiper_container();
	// });
	//setCookie("cancel_geo",0,1);
	if(getCookie("cancel_geo")!=1)
	{
		if(navigator.geolocation)
		{
			var geolocationOptions={timeout:10000,enableHighAccuracy:true,maximumAge:5000};
			navigator.geolocation.getCurrentPosition(getPositionSuccess, getPositionErrorfotkj, geolocationOptions);
		}
	}
	/*名店推荐
	var num= $(".tochscroll ul").children("li").length;
	var ulw=100*num;
	$(".tochscroll .shops").css("width",ulw);
	// console.log(e);
	 //创建一个新的hammer对象并且在初始化时指定要处理的dom元素
	var hammertime = new Hammer(document.getElementById("tochscroll"));
	 //添加事件
	 
	hammertime.on("pan", function (e) {
	    //document.getElementById("result").innerHTML += "X偏移量：【" + e.deltaX + "】，Y偏移量：【" + e.deltaY + "】<br />";
	    // $(".tochscroll .shops").css("width",0);
	    //$(".tochscroll .shops").css("transform","translateX("+3+")");
	   // var val=val+e.deltaX;
	    document.getElementById("shops").style.transform='translateX('+e.deltaX+'px)';
    //transform:-735px);
	    //控制台输出
	 	/*console.log(val);
	 	return val; 
	});*/
 

	//广告图轮播
	// TouchSlide({
	// 	slideCell:"#slideBox2",
	// 	effect:"leftLoop",
	// 	mainCell:"#bd2 ul",
	// 	autoPlay:true ,
	// 	interTime:5000,
	// 	delayTime:500,
	// });
	//改变列表展示
	openclose(".z-nav-down",".changelist");
	changeclass(".z-nav-down",".tuan-ul","tuan-ul-list","tuan-ul-img");

});

/*首页导航条变化*/
$(window).scroll(function(){
	var top=getScroll();
	var opacity=top/100;
	if (opacity>=0.8) {
		$(".headerindex .mark").css("opacity",0.8).css("box-shadow","0 0 0 #d82020");
		$(".headerindex .middle a,.headerindex .right a").css("background-color","rgba(255,255,255,1)");
	}else{
		$(".headerindex .mark").css("opacity",opacity).css("box-shadow","0 2px 3px #d82020");
		$(".headerindex .middle a,.headerindex .right a").css("background-color","rgba(255,255,255,0.5)");
	};
	//console.log(top);
});

//点击切换效果 点击clickon在panel上切换switchA,switchB;
function changeclass(clickon,panel,switchA,switchB){
	$(clickon).click(function(){
		if ($(panel).hasClass(switchA)) {
			$(panel).removeClass(switchA).addClass(switchB);
		}
		else {
			$(panel).removeClass(switchB).addClass(switchA);
		}
	});
}
//点击切换效果 点击clickon打开关闭panel;
function openclose(clickon,panel){
	$(clickon).click(function(){
		if ($(panel).hasClass('close')) {
			$(panel).removeClass('close').addClass('open');
		}
		else {
			$(panel).removeClass('open').addClass('close');
		}
	});
}

/*获取滚动的高度*/
 function getScroll(){  
     var bodyTop = 0;    
     if (typeof window.pageYOffset != 'undefined') {    
         bodyTop = window.pageYOffset;    
     } else if (typeof document.compatMode != 'undefined' && document.compatMode != 'BackCompat') {    
         bodyTop = document.documentElement.scrollTop;    
     }    
     else if (typeof document.body != 'undefined') {    
         bodyTop = document.body.scrollTop;    
     }    
     return bodyTop  
}  


function getPositionSuccess(p){  
	has_location = 1;//定位成功; 
    m_latitude = p.coords.latitude; //纬度
    m_longitude = p.coords.longitude;
	userxypoint(m_latitude, m_longitude);
}
function getPositionErrorfotkj(error) {
	$.getJSON('https://ipinfo.io/geo', function (response) {
		var loc = response.loc.split(',');
		var coords = {
			coords:{
				latitude: loc[0],
				longitude: loc[1]
			}
		};
		getPositionSuccess(coords);
	});
}

function getPositionError(error){
	switch(error.code){
		case error.TIMEOUT:
			alert("定位连接超时，请重试");
			break;
		case error.PERMISSION_DENIED:
			alert("您拒绝了使用位置共享服务，查询已取消");
			break;
		default:
		alert("定位失败");
	}
}


//将坐标返回到服务端;
function userxypoint(latitude,longitude){
	var query = new Object();
	query.m_latitude = latitude;
	query.m_longitude = longitude;
	$.ajax({
		url:geo_url,
		data:query,
		type:"post",
		dataType:"json",
		success:function(data){
			if(data.status==0)
			{
				$.showConfirm("当前城市是["+data.city.name+"],是否切换到"+data.city.name+"站？",function(){
					location.href = city_url+"&city_id="+data.city.id;
				},function(){
					setCookie("cancel_geo",1,1);
				});
			}
		}
		,error:function(){
		}
	});
}
function setCookie(name, value, iDay){

    /* iDay 表示过期时间   

    cookie中 = 号表示添加，不是赋值 */   

    var oDate=new Date();   

    oDate.setDate(oDate.getDate()+iDay);       

    document.cookie=name+'='+value+';expires='+oDate;

}

function getCookie(name){

    /* 获取浏览器所有cookie将其拆分成数组 */   

    var arr=document.cookie.split('; ');  

    

    for(var i=0;i<arr.length;i++)    {

        /* 将cookie名称和值拆分进行判断 */       

        var arr2=arr[i].split('=');               

        if(arr2[0]==name){           

            return arr2[1];       

        }   

    }       

    return '';

}

/*名店推荐*/
// function swiper_container(){
// 	var scw = $('.swiper-container').width();
// 	var ssiw = $('.swiper-slide img').width();
	
// 	$('.swiper-container').width(scw +(ssiw/2));
// 	var scw = $('.swiper-container').width();
// 	var sv = Math.floor( (scw+ssiw) / ssiw);
	
// 	var slideSize = $('.swiper-container .swiper-wrapper .swiper-slide').width();
// 	// 计算图片与右边框距离
// 	var spaceBetween = $('.swiper-slide-outer').width() - $('.swiper-slide-outer img').width();
// 	// 计算列表宽度
// 	$('.swiper-container').width(scw +(ssiw/2));
// 	// 商家推荐触摸滚动
// 	var swiper = new Swiper('.swiper-container', {
//         pagination: '.swiper-pagination',
//         slidesPerView: sv,
//         paginationClickable: true,
//         spaceBetween: 30,
//         slideSize:slideSize,
//         spaceBetween:spaceBetween,
//         loop: true
//     });
// }