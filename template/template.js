// anchor scroll

$("a").on("click", (event) => {
	
	// var fixed = $("#menu");
	var self = event.currentTarget;

	if (self.hash !== "") {
		
		var hash = self.hash;
		var coords = $(hash).offset().top;
		// var coords = $(hash).offset().top - $(fixed).outerHeight();
		
		history.pushState({}, "", hash);
		$("html, body").animate({scrollTop: coords}, 500);
		
		return false;
	}
});