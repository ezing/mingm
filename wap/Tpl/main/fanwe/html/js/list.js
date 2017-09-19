$(function() {
	// $('.pulldown').load('introduce/pull_down.html');
	// $('.footer').load('introduce/footer.html');
	$(document).scroll(function(e) {
		var x = $(document).scrollTop();
		if(x >= 63.5) {
			$(".classify_region").css({
				"top": "58px",
				"background": "#fff",
				"opacity": "1",
				"z-index": "9"
			});
			$(".list_classify").css({
				"top": "101px"
			});
			$(".city_classify").css({
				"top": "101px"
			});
			$(".synthesis_subnav").css({
				"top": "101px"
			});

		} else {
			$(".classify_region").css({
				"top": "58px",
				"background": "#fff",
				"opacity": "1",
				"z-index": "9"
			});
			$(".list_classify").css({
				"top": 101 - x + "px"
			});
			$(".city_classify").css({
				"top": 101 - x + "px"
			});
			$(".synthesis_subnav").css({
				"top": 101 - x + "px"
			});

		}

//		console.log($(".classify_region").offset().top)
	})
})