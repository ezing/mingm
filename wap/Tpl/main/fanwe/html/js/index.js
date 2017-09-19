$(function() {
var mySwiper = new Swiper('.swiper-container', {
	resistanceRatio:0,
})

var mySwiper1 = new Swiper('.swiper-container1', {
	pagination : '.swiper-pagination',
	resistanceRatio:0,
})
$('.clk_two li').eq(3).click(function(){
mySwiper1.slideNext();
})
var mySwiper2 = new Swiper('.swiper-container2', {
freeMode : true,
freeModeMomentum :true,
	resistanceRatio:0,
})

})