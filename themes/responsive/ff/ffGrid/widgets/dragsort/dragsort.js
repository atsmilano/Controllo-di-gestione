ff.ffGrid.dragsort = (function () {

var that = { /* publics */
__ff : true, /* used to recognize ff'objects*/

"inst" : ff.hash(),

"init" : function (params) {
	that.inst.set(params.id, params);
	jQuery("#" + params.id).jTableOrder(params.id);
	//jQuery("#" + params.id + " table.ffGrid > tbody > tr").each(function() { 
	//	jQuery(this).children("td:first:not(.norec_cont)").css("background", "url(" + ff.site_path + "/themes/responsive/ff/ffGrid/widgets/dragsort/grippy.png) 4px no-repeat")/*.css("padding", "0 0 0 14px")*/;
	//});
},

"reorder" : function (id) {
	var params = that.inst.get(id);
	var $table = jQuery("TABLE", jQuery("#" + id));
	
	var toBeSent = [];
	var position = 1;
	if(params.service_path) 
	{
		$table.find("> tbody > tr").not("tr tr").each(function () {
			toBeSent.push({"name" : "positions[]", "value" : jQuery(this).data("sort_id")});
			position += 1;
		});

		toBeSent.push({name: "resource", value: params.resource_id});

        if(params.service_params) {
            var serviceParams = params.service_params.split("&");
            serviceParams.each(function(key, value) {
                var arrValue = value.split("=");
				if(arrValue.length === 2) {
                    toBeSent.push({"name": arrValue[0], "value": arrValue[1]});
                }
            })
        }


		ff.load("ff.ajax", function() {
			ff.ajax.blockUI();
			jQuery.ajax({
				  "url"		: params.service_path
				, "async"	: true
				, "type"	: "POST"
				, "data"	: toBeSent
				, "success" : function (data) {ff.ajax.unblockUI();}
				, "mydata"	: id
			});
		});
	}
}

}; /* publics' end*/

return that;

/* code's end.*/
})();
