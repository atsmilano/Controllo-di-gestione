ff.ffField.timepicker = (function () {
	var that = { /* publics*/
		__ff : true, /* used to recognize ff'objects*/
		"fill" : function (control_id, control_hour_id, control_minute_id, sel_hour, sel_minute) {
			var control_hour = document.getElementById(control_hour_id);
			var control_minute = document.getElementById(control_minute_id);

			for (var i = 0; i <= 23; i++)
			{
				var option = document.createElement('option');
				if (i < 10)
					option.appendChild(document.createTextNode("0" + i));
				else
					option.appendChild(document.createTextNode(i));
				option.setAttribute('value', i);
				control_hour.appendChild(option);
			}

			for (var i = 0; i <= 59; i++)
			{
				var option = document.createElement('option');
				if (i < 10)
					option.appendChild(document.createTextNode("0" + i));
				else
					option.appendChild(document.createTextNode(i));
				option.setAttribute('value', i);
				control_minute.appendChild(option);
			}

			control_hour.selectedIndex = sel_hour;
			control_minute.selectedIndex = sel_minute;
		}, 
		"change" : function (control_id, control_hour_id, control_minute_id) {
			var control			= document.getElementById(control_id);
			var control_hour	= document.getElementById(control_hour_id);
			var control_minute	= document.getElementById(control_minute_id);

			control.value = control_hour.selectedIndex + ":" + control_minute.selectedIndex;
		}
	};
	
	return that;

})();