ff.ffField.datechooser = (function () {
	var that = { /* publics*/
		__ff : true, /* used to recognize ff'objects*/
		"fill" : function (control_id, control_day_id, control_month_id, control_year_id, sel_day, sel_month, sel_year, typeDate) {
			var control_day = document.getElementById(control_day_id);
			var control_month = document.getElementById(control_month_id);
			var control_year = document.getElementById(control_year_id);
		
			if(control_day) {
				for (var i = 1; i <= 31; i++)
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
					control_day.appendChild(option);
					if(i == sel_day)
						control_day.selectedIndex = i;
				}
			}
			if(control_month) {
				for (var i = 1; i <= 12; i++)
				{
					var option = document.createElement('option');
					if (i < 10) {
						option.setAttribute('value',"0" + i);
						option.appendChild(document.createTextNode("0" + i));
					} else {
						option.setAttribute('value', i);
						option.appendChild(document.createTextNode(i));
					}
					control_month.appendChild(option);
					if(i == sel_month)
						control_month.selectedIndex = i;
				}
			}
			if(control_year) {
				var minVal = 0;
				var maxVal = 0;
				var inv = undefined;
				var currentYear = new Date().getFullYear();
				
				switch(typeDate) {
					case "booking":
						maxVal = 20;
						break;
					case "age":
						minVal = -100;
						break;
					case "mixed":
						minVal = -10;
						maxVal = 20;
						break;
					case "mixedInv":
						minVal = -10;
						maxVal = 20;
						inv = 30;
						break;
					default:
						minVal = typeDate["min"] - currentYear;
						maxVal = typeDate["max"] - currentYear;
				}
				
				
				var n = 1;  

				if(inv !== undefined)
					inv = inv + 2;

				for (var i = minVal; i <= maxVal; i++) 
				{
					var option = document.createElement('option');
					var newVal = currentYear + i;					
					if(inv !== undefined) {
						inv = inv - 2;
						newVal = newVal + inv; 
					}
					option.setAttribute('value', newVal);
					option.appendChild(document.createTextNode(newVal));
					control_year.appendChild(option);
					if((currentYear + i) == sel_year)
						control_year.selectedIndex = n;
						
					n++;
				}
			}
		}, 
		"change" : function (control_id, control_day_id, control_month_id, control_year_id) {
			var control			= document.getElementById(control_id);
			var control_day		= document.getElementById(control_day_id);
			var control_month	= document.getElementById(control_month_id);
			var control_year	= document.getElementById(control_year_id);

			if(control) {
				control.value = control_day.value + "/" + control_month.value + "/" +  control_year.value; 
			}
		}
	};
	
	return that;

})();