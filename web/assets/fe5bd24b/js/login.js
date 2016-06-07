$(document).ready(function() {
	$(".tabTitle li").on("click",function() {
		if ($(this).hasClass("active")) {
			return;
		}

		$(this).addClass("active").siblings(".active").removeClass("active");
		$(".tabContent > div").eq($(this).index()).addClass("active").siblings(".active").removeClass("active");
	});
});