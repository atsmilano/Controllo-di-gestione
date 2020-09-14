ff.ffField.checkgroup = (function () {
	var separator		= [];
	var length			= [];


	var that = { // publics
		__ff : true, // used to recognize ff'objects
		"recalc" : function(control, obj) {
			var hidden = document.getElementById(control);
			var tmp = "";
			var element = null;
				
			for (var i = 0; i < length[control]; i++)
			{
			
				element = document.getElementById(control + "_" + i);
				if (element.checked)
				{
					if (tmp.length)
						tmp = tmp + separator[control];
					tmp = tmp + element.value;
				}
			}
			hidden.value = tmp;
			
			if(jQuery(obj).parent().hasClass('on')) { 
				jQuery(obj).parent().removeClass('on'); 
				jQuery(obj).parent().addClass('off'); 
			} else if(jQuery(obj).parent().hasClass('off')) { 
				jQuery(obj).parent().removeClass('off'); 
				jQuery(obj).parent().addClass('on'); 
			}
		},
		"addValue" : function (params) {
			separator[params.id]			= params.separator;
			length[params.id]				= params.length;
		}
	};

	return that;
	
})();