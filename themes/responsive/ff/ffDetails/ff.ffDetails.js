/**
 * Forms Framework Javascript Handling Object
 *	ffDetails' namespace
 */
$.fn.replaceAttr = function(aName, rxString, repString) {
	return this.attr(aName, function() {
		return $(this).attr(aName).replace(rxString, repString);
	});
};
ff.ffDetails = (function () {
	
// inits
ff.pluginAddInit("ff.ajax", function () {
	ff.ajax.addEvent({
		"event_name"	: "onUpdateComponent",
		"func_name"		: function (componentid, component) {
			if (component.type == "ffDetails") {
				if (component.ctx) {
					var oCtx = ff.ajax.ctxGet(component.ctx);
					oCtx.doRequest({
						"action"        : "refresh",
						"component"        : componentid,
						"chainupdate"    : true
					});
				} else {
					ff.ajax.doRequest({
							"component"    : componentid,
							"chainupdate"    : true
					});
				}
				return true;
			}			
		}
	});
});

//privates
var toggles = ff.hash();
var toggles_rows = ff.hash();
var toggle_all = 0;

var that = { // publics
__ff : true, // used to recognize ff'objects

"turnToggle" : function (component, nome) {
	var re = new RegExp("^" + component + "_recordset\\[\\d+\\]\\[" + nome + "\\]$");
	
	var value = toggles.get(nome);

	if(value > 0) {
		jQuery("input").filter(function() {
			return this.id.match(re);
		}).prop("checked", false).trigger("change");
		
		toggles.set(nome, 0);
	} else {
		jQuery("input").filter(function() {
			return this.id.match(re);
		}).prop("checked", true).trigger("change");

		toggles.set(nome, 1);
	}
},

"turnToggleRow" : function (component, row) {
	var re = new RegExp("^" + component + "_recordset\\[" + row + "\\]\\[[^\\]]+\\]$");

	var value = toggles_rows.get(row);
	
	if(value > 0) {
		jQuery("input").filter(function() {
			return this.id.match(re);
		}).prop("checked", false).trigger("change");
		
		toggles_rows.set(row, 0);
	} else {
		jQuery("input").filter(function() {
			return this.id.match(re);
		}).prop("checked", true).trigger("change");

		toggles_rows.set(row, 1);
	}
},

"turnToggleAll" : function (component, row) {
	var re = new RegExp("^" + component + "_recordset\\[\\d+\\]\\[[^\\]]+\\]$");

	if(toggle_all > 0) {
		jQuery("input").filter(function() {
			return this.id.match(re);
		}).prop("checked", false).trigger("change");
		
		toggle_all = 0;
	} else {
		jQuery("input").filter(function() {
			return this.id.match(re);
		}).prop("checked", true).trigger("change");

		toggle_all = 1;
	}
},

"turnON" : function (component, nome) {
	var re = new RegExp("^" + component + "_recordset\\[\\d+\\]\\[" + nome + "\\]$");
	jQuery("input").filter(function() {
		return this.id.match(re);
	}).prop("checked", true).trigger("change");
},

"turnOFF" : function (component, nome) {
	var re = new RegExp("^" + component + "_recordset\\[\\d+\\]\\[" + nome + "\\]$");
	jQuery("input").filter(function() {
		return this.id.match(re);
	}).prop("checked", false).trigger("change");
},

"turnONRow" : function (component, row) {
	var re = new RegExp("^" + component + "_recordset\\[" + row + "\\]\\[[^\\]]+\\]$");
	jQuery("input").filter(function() {
		return this.id.match(re);
	}).prop("checked", true).trigger("change");
},

"turnOFFRow" : function (component, row) {
	var re = new RegExp("^" + component + "_recordset\\[" + row + "\\]\\[[^\\]]+\\]$");
	jQuery("input").filter(function() {
		return this.id.match(re);
	}).prop("checked", false).trigger("change");
},

"turnONAll" : function (component) {
	var re = new RegExp("^" + component + "_recordset\\[\\d+\\]\\[[^\\]]+\\]$");
	jQuery("input").filter(function() {
		return this.id.match(re);
	}).prop("checked", true).trigger("change");
},

"turnOFFAll" : function (component) {
	var re = new RegExp("^" + component + "_recordset\\[\\d+\\]\\[[^\\]]+\\]$");
	jQuery("input").filter(function() {
		return this.id.match(re);
	}).prop("checked", false).trigger("change");
},
"init" : function() {
	jQuery(".ffGrp").each(function() {
		jQuery(".required:first input", this).keyup(function() {
			var legendElem = jQuery(this).closest(".ffGrp").find("h4");
			if(legendElem.length) {
				var childrenLegend = legendElem.children();
				legendElem.text(jQuery(this).val());
				legendElem.append(childrenLegend); 	
			}	
		});
	
	});
}
, "onClearComponent" : function (component) {
	var pComp = ff.struct.get("comps").get(component);
	if (!pComp)
		return;

	if (pComp.type !== "ffDetails")
		return;
	
	var rows = parseInt(jQuery.fn.escapeGet(component + "_rows").val());
	
	// reset per ogni singolo campo
	pComp.fields.each( function (key, field) {
		// per ogni singola riga
		for (var i = 0; i < rows; i++) {
			var tmp_id = component + "_recordset[" + i + "][" + key + "]";
			
			ff.doEvent({
				"event_name"	: "onClearField",
				"event_params"	: [component, key, field, tmp_id]
			});
		}
	});
}

}; // publics' end

ff.pluginAddInitLoad("ff.ffDetails", function () {
	ff.addEvent({"event_name" : "onClearComponent", "func_name" : that.onClearComponent});
});

return that;

// code's end.
})();

jQuery(function() {
	ff.ffDetails.init();
});
