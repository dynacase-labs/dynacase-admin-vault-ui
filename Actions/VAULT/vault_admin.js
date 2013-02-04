(function ($, window, document) {
    
    
    viewornot = function viewornot(id) {
	console.log(id);
	$('.form-exclusive').hide();
	var o=document.getElementById(id);
	if (o) {
	    if (o.style.display=='none') $('#'+id).show();
	    else $('#'+id).hide();
	}
    }

    $(document).ready(function () {
	
	$(".ui-button")
	    .on(
		'mouseenter', function() { 
		    $(this).addClass("ui-state-hover"); 
		})
	    .on(
		'mouseleave', function() { 
		    $(this).removeClass("ui-state-hover"); 
		});
    });
    
}($, window, document));
