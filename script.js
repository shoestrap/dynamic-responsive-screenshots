$(document).ready(function() {
	var a = 3;
	$('.desktop,.laptop,.tablet,.mobile').draggable({
		start: function(event, ui) { $(this).css("z-index", a++); }
	});
	$('.display div').click(function() {
		$(this).addClass('top').removeClass('bottom');
		$(this).siblings().removeClass('top').addClass('bottom');
		$(this).css("z-index", a++);
	});
});

function getUrlVars() {
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
		vars[key] = value;
	});
	return vars;
}

var first = getUrlVars()["url"];
var first = decodeURIComponent(first);
var first = first.replace(/\#$/, '');

if(first === "undefined") {
	// don't do anything.
} else {
	//  take the url variable and update the iframes and input field
	$("iframe").attr('src',(first));
	$('#url').attr('value',(first));
}
