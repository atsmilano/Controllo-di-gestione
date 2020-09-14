/**
 * Forms Framework Javascript Handling Object
 *	ffRecord' namespace
 */

ff.ffRecord = (function () {
//privates

var that = { // publics
	__ff : true // used to recognize ff'objects
	, "init" : function(component) {
		jQuery("#" + component + " .ffCheckDep").click();	
		ff.pluginLoad("ff.ajax", "/themes/library/ff/ajax.js", function() {
			ff.ajax.addEvent({
				"event_name"	: "onEmptyQueue",
				"func_name"		: function (data) {
					jQuery("#" + component + " .ffCheckDep").click();	
				}
			});
		});
		
	}
	, "displayFieldSetElem" : function (legend, control) {
		var checkControl = false;
		if(jQuery("#" + control).is("INPUT[type=checkbox]")) {
			checkControl = jQuery("#" + control).is(":checked");
		} else {
			checkControl = jQuery("#" + control).val();
		}
	
		if(checkControl) {
			jQuery(legend).parent().children("*:not(legend)").css({  
				"opacity": ""
				, "pointer-events" : "" 
			});
		} else {
			jQuery(legend).parent().children("*:not(legend)").css({
				"opacity": "0.5"
				, "pointer-events" : "none" 
			});
		}
	}

}; // publics' end

return that;

// code's end.
})();