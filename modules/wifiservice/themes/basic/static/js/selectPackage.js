$(document).ready(function() {
	// 下拉功能
	$("#packageList > li a").on("click",function() {
		var parent = $(this).parent();
		if (parent.hasClass("active")) {
			parent.removeClass("active");
			parent.find("ul").slideUp();
		} else {
			parent.addClass("active");
			parent.find("ul").slideDown();
		}
		return false;
	});

	// 点击选择
	$("#packageList li li").on("click",function() {
		$("#packageList li.selected").removeClass("selected");
		$("#packageList input:checked").prop("checked",false);
		$(this).find("input").prop("checked",true);
		$(this).parents("li").addClass("selected");
	});

	$("#packageList input").on("click",function(ev) {
		$("#packageList li.selected").removeClass("selected");
		$(this).parents("li").addClass("selected");
		ev.stopPropagation();
	})
});