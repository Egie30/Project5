(function($) {
	$(document).ready(function(){
		$(".tabContaier .tabContents").hide(); // Hide all tab content divs by default
		$(".tabContaier .tabContents").first().show(); // Show the first div of tab content by default
		
		$(".tabContaier ul li a").on("click", function(e){ //Fire the click event
			e.preventDefault(); //prevent page scrolling on tab click
			
			var activeTab = $(this).attr("href"); // Catch the click link
			
			$(".tabContaier ul li a").removeClass("active"); // Remove pre-highlighted link
			$(this).addClass("active"); // set clicked link to highlight state
			
			// hide currently visible tab content div
			$(".tabContaier .tabContents").hide();
			$(activeTab).fadeIn(); // show the target tab content div by matching clicked link.
		});
	});
})(jQuery);