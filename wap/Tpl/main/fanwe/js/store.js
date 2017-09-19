$(function(){
	$(".J_item_more").click(function(){
		
        $(this).parent().find(".business_display").toggleClass("business_blank");
    });
	
	
	$('.toggle-deal-location-list').toggle( 
			 function () {
				 $('.deal-locationo-list-set-display').show('400');
				 $('.deal-location-show').hide();
				 $('.deal-location-hide').show();
				 $('.close-span-location').removeClass('rotate_0').addClass("rotate_set").addClass("rotate_180");
				 
			  },
			  function () {
				  $('.deal-locationo-list-set-display').hide('400');
				  $('.deal-location-show').show();
				  $('.deal-location-hide').hide();
				  $('.close-span-location').removeClass('rotate_180').addClass("rotate_set").addClass("rotate_0");
			  }
	);	
	
});