/**
 * Forms Framework Javascript Handling Object
 *	lifesaver plugin
 */

ff.ffPage.lifeSaver = (function () {

var elements = ff.hash();
var observing = false;

//ff.pluginLoad("JSON", "/themes/library/JSON/json2.js", undefined, false);
//var stored_data = (localStorage["ff.ffPage.lifeSaver"] ? ff.hash(JSON.parse(localStorage["ff.ffPage.lifeSaver"])) : stored_data = ff.hash());

function getElementData (component, field_id, field) {
	switch (field.widget) {
		case "ckeditor":
			if (CKEDITOR.instances[component + "_" + field_id]) // cause maybe overlap with unloading object
				CKEDITOR.instances[component + "_" + field_id].updateElement();
			return jQuery("#" + component + "_" + field_id).val();
			break;

		default:
			return jQuery("#" + component + "_" + field_id).val();
	}
}

function initElement (component, field_id, field) {
	switch (field.widget) {
		case "ckeditor":
			elements.get(component).set(field_id, {
				"attrs" : field
				, "ready" : false
				, "ori_value" : null
			});
			
			CKEDITOR.instances[component + "_" + field_id].on("instanceReady", function(){
				elements.get(component).set(field_id, {
					"attrs" : field
					, "ready" : true
					, "ori_value" : getElementData(component, field_id, field)
				});
			});
			break;
			
		default:
			elements.get(component).set(field_id, {
				"attrs" : field
				, "ready" : true
				, "ori_value" : getElementData(component, field_id, field)
			});
	}
}

var that = { // publics
	"polling" : 2000, // in msecs
	
	"lookAt" : function (component, keys, field) {
		if (!elements.isset(component)) {
			elements.set(component, ff.hash());
			elements.get(component).lsComponentKeys = keys;
		}
		
		if (field !== undefined) {
			initElement(component, field, ff.struct.get(component).fields.get(field));
		} else {
			ff.struct.get(component).fields.each(function(field_id, field_data){
				initElement(component, field_id, field_data);
			});
		}
	},
	
	"save" : function () {
		elements.each(function(component, subelements){
			var saved = false;
			
			var prefix = component;
			
			var record_id = ff.history.gup(subelements.lsComponentKeys);
			if (record_id)
				prefix += "_" + record_id;
			
			subelements.each(function(field_id, field){
				var value = getElementData(component, field_id, field.attrs);

				if (field.ori_value != value) {
					localStorage[prefix + "_" + field_id] = value;
					saved = true;
				}
			});
		
			if (saved)
				localStorage[prefix] = saved;
		});
	
		if (observing)
			setTimeout('ff.ffPage.lifeSaver.save();', that.polling);
	},

	"clear" : function () {
		elements.each(function(component, subelements){
			var prefix = component;
			
			var record_id = ff.history.gup(subelements.lsComponentKeys);
			if (record_id)
				prefix += "_" + record_id;
			
			localStorage.removeItem(prefix);
			
			subelements.each(function(field_id, field){
				localStorage.removeItem(prefix + "_" + field_id);
				field.ori_value = getElementData(component, field_id, field);
			});
		});
	},

	"recover" : function () {
		elements.each(function(component, subelements){
			var prefix = component;
			
			var record_id = ff.history.gup(subelements.lsComponentKeys);
			if (record_id)
				prefix += "_" + record_id;
			
			subelements.each(function(field_id, field){
				if (localStorage[prefix + "_" + field_id])
					switch (field.attrs.widget) {
						case "ckeditor":
							jQuery("#" + component + "_" + field_id).val(localStorage[prefix + "_" + field_id]);
							CKEDITOR.instances[component + "_" + field_id].setData(localStorage[prefix + "_" + field_id]);
							CKEDITOR.instances[component + "_" + field_id].updateElement();
							break;

						default:
							jQuery("#" + component + "_" + field_id).val(localStorage[prefix + "_" + field_id]);
					}
			});
		});
	},

	"check" : function (unblockui) {
		var saved = false;
		var ready = true;
		
		elements.each(function(component, subelements){
			var prefix = component;
			
			var record_id = ff.history.gup(subelements.lsComponentKeys);
			if (record_id)
				prefix += "_" + record_id;
			
			if (localStorage[prefix])
				saved = true;
			
			subelements.each(function(field_id, field){
				if (!field.ready)
					ready = false;
			});
		});
		
		if (!ready) {
			if (!unblockui)
				ff.ajax.blockUI();

			setTimeout('ff.ffPage.lifeSaver.check(true);', 1000);
			return;
		}

		if (unblockui)
			ff.ajax.unblockUI();

		if (saved) {
			var answer = confirm("E' stato trovato del contenuto non salvato. Si desidera ripristinarlo?");
			if (answer) {
				that.recover();
			} else {
				that.clear();
			}
		}
		
		//jQuery(window).bind('beforeunload', that.beforeunload);
		
		if (!observing) {
			setTimeout('ff.ffPage.lifeSaver.save();', that.polling);
			observing = true;
		}
	},
	
	"debug" : function () {
		console.log(elements);
		elements.dump();
	},

	"beforeunload" : function () {
		var changed = false;
		
		elements.each(function(component, subelements){
			subelements.each(function(field_id, field){
				var value = getElementData(component, field_id, field.attrs);

				if (field.ori_value != value)
					changed = true;
			});
		});
		
		if (changed) {
			return "Dei dati sono stati modificati, uscendo dalla pagina si perderanno le modifiche";
		}
	}
}; // publics' end

return that;

// code's end.
})();
