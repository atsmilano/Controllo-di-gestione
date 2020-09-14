ff.ffField.timechooser = (function () {
	var that = { /* publics*/
		__ff : true, /* used to recognize ff'objects*/
		"fill" : function (control_id, control_hours_id, control_mins_id, sel_hours, sel_mins) {
			var control_hours = document.getElementById(control_hours_id);
			var control_mins = document.getElementById(control_mins_id);

			for (var i = 0; i < 12; i++)
			{
				var option = document.createElement('option');
				option.setAttribute('value', i);
				if (i < 10) {
					option.setAttribute('value',"0" + i);
					option.appendChild(document.createTextNode("0" + i));
				} else {
					option.setAttribute('value', i);
					option.appendChild(document.createTextNode(i));
				}
				control_hours.appendChild(option);
				if(i == sel_hours)
					control_hours.selectedIndex = i;
			}

			for (var i = 0; i < 60; i++)
			{
				var option = document.createElement('option');
				if (i < 10) {
					option.setAttribute('value',"0" + i);
					option.appendChild(document.createTextNode("0" + i));
				} else {
					option.setAttribute('value', i);
					option.appendChild(document.createTextNode(i));
				}
				control_mins.appendChild(option);
				if(i == sel_mins)
					control_mins.selectedIndex = i;
			}
		}, 
		"change" : function (control_id, control_hours_id, control_mins_id) {
			var control			= document.getElementById(control_id);
			var control_hours	= document.getElementById(control_hours_id);
			var control_mins	= document.getElementById(control_mins_id);

			control.value = control_hours.value + ":" + control_mins.value;
		}
	};
	
	return that;

})();