$(document).ready(function() {
	$("#packageList li").on("click",function() {
		$(this).find("input").prop("checked",true);
	});
	
	$("#packageList li input").on("click",function(ev) {
		ev.stopPropagation();
	})
});