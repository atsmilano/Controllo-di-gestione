/**
 * Forms Framework Javascript Handling Object
 *	lifesaver plugin
 */

ff.ffPage.lifeSaver = (function () {

var ctxs = ff.hash();
var elements = ff.hash();
var observing = false;
var willToLeave = false;

function getPrefix(el, keys) {
	var prefix = el.id;
	
	if (el.component) {
		var record_id = ff.history.gup(keys);
		if (record_id)
			prefix += "_" + record_id;
	}
	
	return prefix;
}

function getFieldData (field_id, field) {
	switch (field.widget) {
		case "ckeditor":
			if (CKEDITOR.instances[field_id]) // cause maybe overlap with unloading object
				CKEDITOR.instances[field_id].updateElement();
			return jQuery.fn.escapeGet(field_id).val();
			break;

		default:
			switch (jQuery.fn.escapeGet(field_id).attr("type")) {
				case "checkbox":
					return jQuery.fn.escapeGet(field_id).prop('checked').toString();
				default:
					return jQuery.fn.escapeGet(field_id).val();
			}
	}
}

function setFieldData (value, field_id, field, wait_init) {
	switch (field.widget) {
		case "actex":
			if (wait_init) {
				ff.pluginAddInit("ff.ffField.actex", function () {
					var tmp_actex = ff.ffField.actex.getInstance(field_id, true);
					if (tmp_actex !== undefined && tmp_actex.isFilled()) {
						jQuery.fn.escapeGet(field_id).val(value);
					} else {
						var tmpev = ff.ffField.actex.addEvent({
							"event_name" : "refill"
							, "func_name" : function (id) {
								if (id === field_id) {
									jQuery.fn.escapeGet(field_id).val(value);
									tmpev.remove();
								}
							}
						});
					}
				});
			} else {
				jQuery.fn.escapeGet(field_id).val(value);
			}
			break;
			
		case "ckeditor":
			if (wait_init) {
				ff.pluginAddInit("ff.ffField.ckeditor", function () {
					if (CKEDITOR.instances[field_id] && CKEDITOR.instances[field_id].instReady) {
						jQuery.fn.escapeGet(field_id).val(value);
						CKEDITOR.instances[field_id].setData(value);
						CKEDITOR.instances[field_id].updateElement();
					} else {
						var tmpev = ff.ffField.ckeditor.addEvent({
							"event_name" : "onCreate"
							, "func_name" : function (element, id) {
								if (id === field_id) {
									jQuery.fn.escapeGet(field_id).val(value);
									CKEDITOR.instances[field_id].setData(value);
									CKEDITOR.instances[field_id].updateElement();
									tmpev.remove();
								}
							}
						});
					}
				});
				
			} else {
				jQuery.fn.escapeGet(field_id).val(value);
				CKEDITOR.instances[field_id].setData(value);
				CKEDITOR.instances[field_id].updateElement();
			}
			break;

		default:
			if (jQuery.fn.escapeGet(field_id).attr('type') === "checkbox")
				jQuery.fn.escapeGet(field_id).prop("checked", (value === "true" ? true : false));
			else
				jQuery.fn.escapeGet(field_id).val(value);
	}
}

function initField (el, field_id, field, skip_widget) {
	switch (skip_widget ? null : field.widget) {
		case "actex":
			el.data.set(field_id, {
				"attrs" : field
				, "ori_value" : null
			});
			ff.pluginAddInit("ff.ffField.actex", function () {
				var tmp = el.data.get(field_id);
				var tmp_actex = ff.ffField.actex.getInstance(field_id, true);
				if (tmp_actex !== undefined && tmp_actex.isFilled()) {
					tmp.ori_value = getFieldData(field_id, field);
					el.waiting--;
				} else {
					var tmpev = ff.ffField.actex.addEvent({
						"event_name" : "refill"
						, "func_name" : function (id) {
							if (id === field_id) {
								tmp.ori_value = getFieldData(field_id, field);
								el.waiting--;
								tmpev.remove();
							}
						}
					});
				}
			});
			return;
			
		case "ckeditor":
			el.data.set(field_id, {
				"attrs" : field
				, "ori_value" : null
			});
			
			/*if (CKEDITOR.instances[component + "_" + field_id] !== undefined) {
				CKEDITOR.instances[component + "_" + field_id].on("instanceReady", function(){
				});
			}*/
			
			ff.pluginAddInit("ff.ffField.ckeditor", function () {
				var tmp = el.data.get(field_id);
				if (CKEDITOR.instances[field_id] && CKEDITOR.instances[field_id].instReady) {
					tmp.ori_value = getFieldData(field_id, field);
					el.waiting--;
				} else {
					var tmpev = ff.ffField.ckeditor.addEvent({
						"event_name" : "onCreate"
						, "func_name" : function (element, id) {
							if (id === field_id) {
								tmp.ori_value = getFieldData(field_id, field);
								el.waiting--;
								tmpev.remove();
							}
						}
					});
				}
			});
			return;
			
		default:
			el.data.set(field_id, {
				"attrs" : field
				, "ori_value" : getFieldData(field_id, field)
			});
			el.waiting--;
			return;
	}
}

function getDOMID(comp, field) {
	if (field !== undefined) {
		return (comp ? comp + "_" + field : field);
	} else {
		return comp;
	}
	
}

function ctx (params) {
	var id = params.id;

	var ready = false;
	
	function readySet() {
		ready = true;
		/*if (!observing) {
			observing = true;
			ff.ffPage.lifeSaver.save();
		}*/
		ff.ffPage.lifeSaver.check(that);
	}
		
	that = {
		"elements" : params.elements,
		"keys" : params.keys,
		"path" : params.path,
	
		"lookAt" : function () {
			var ready = true;
			
			for (var i = 0; i < that.elements.length; i++) {
				if (that.elements[i].field !== undefined) {
					if (
							!ff.getField(that.elements[i].field, that.elements[i].comp)
							|| !jQuery.fn.escapeGet(getDOMID(that.elements[i].comp, that.elements[i].field)).length
						) {
						ready = false;
					}
				} else if (
						!ff.getComp(that.elements[i].comp)
						|| !jQuery.fn.escapeGet(that.elements[i].comp).length
					) {
					ready = false;
				}
			}
			
			if (!ready) {
				setTimeout(that.lookAt, ff.ffPage.lifeSaver.polling);
			} else {
				ready = true;
				
				for (var i = 0; i < that.elements.length; i++) {
					var rc = ff.ffPage.lifeSaver.initElement(that, that.elements[i].comp, that.elements[i].field);
					if (rc !== 0) {
						ready = false;
					}
				}
				
				if (ready) {
					readySet();
				} else {
					setTimeout(that.readyCheck, ff.ffPage.lifeSaver.polling);
				}
			}
		},
		
		"readyCheck" : function () {
			ready = true;

			for (var i = 0; i < that.elements.length; i++) {
				if (elements.get(getDOMID(that.elements[i].comp, that.elements[i].field)).waiting != 0) {
					ready = false;
				}
				
			}

			if (ready) {
				readySet();
			} else {
				setTimeout(that.readyCheck, ff.ffPage.lifeSaver.polling);
			}
		},
		
		"readyGet" : function () {
			return ready;
		},
		
		"getParams" : function () {
			return params;
		}
	};

	return that;
}
	
var that = { // publics
	"polling" : 2000, // in msecs
	
	"_TYPE_FIELD" : 1,
	"_TYPE_COMP" : 2,
	
	"ctxAdd" : function (params) {
		if (ctxs.isset(params.id))
			return;
		
		var tmp = ctx(params);
		ctxs.set(params.id, tmp);
		
		tmp.lookAt();
	},
	
	"ctxGet" : function (id) {
		return ctxs.get(id);
	},
	
	"initElement" : function (ctx, component, field) {
		var obj_data = {
			"type" : null
			, "component" : component
			, "field" : field
			, "ctx" : ctx
			, "struct" : null
			, "id" : null
			, "dtl_rows" : null
			, "data" : ff.hash()
			, "waiting" : 0
		};
		
		if (field !== undefined) {
			obj_data.type = ff.ffPage.lifeSaver._TYPE_FIELD;
			obj_data.id = (component ? component + "_" + field : field);
			obj_data.struct = (component ?
					ff.struct.get("comps").get(component).fields.get(field)
					: ff.struct.get("fields").get(field)
				);
		} else {
			obj_data.type = ff.ffPage.lifeSaver._TYPE_COMP;
			obj_data.id = component;
			obj_data.struct = ff.struct.get("comps").get(component);
		}
		
		if (elements.isset(obj_data.id))
			return;
		
		elements.set(obj_data.id, obj_data);
		
		// INIT
		
		switch (obj_data.type) {
			case ff.ffPage.lifeSaver._TYPE_FIELD:
				obj_data.waiting++;
				initField(obj_data, obj_data.id, obj_data.struct);
				break;
				
			case ff.ffPage.lifeSaver._TYPE_COMP:
				switch (obj_data.struct.type) {
					case "ffDetails":
						obj_data.dtl_rows = parseInt("0" + jQuery.fn.escapeGet(component + "_rows").val());
						obj_data.struct.fields.each(function(field_id, field_data){
							for (var i = 0; i < obj_data.dtl_rows; i++) {
								obj_data.waiting += 2;
								initField(obj_data, component + "_recordset_ori[" + i + "][" + field_id + "]", field_data, true);
								initField(obj_data, component + "_recordset[" + i + "][" + field_id + "]", field_data);
							}
						});
						break;
						
					case "ffRecord":
						obj_data.struct.fields.each(function(field_id, field_data){
							if (field_data.type === "key")
								return;
							
							obj_data.waiting++;
							initField(obj_data, component + "_" + field_id, field_data);
						});
						break;
						
					default:
						throw "ff.ffPage.lifeSaver - unhandled content";
				}
				break;
		}
		
		return obj_data.waiting;
	},
	
	"save" : function () {
		/*if (ff.ajax.getblocked_ui()) {
			if (observing) {
				setTimeout('ff.ffPage.lifeSaver.save();', ff.ffPage.lifeSaver.polling);
			}
			return;
		}*/
		
		ctxs.each(function(ctx_id, ctx){
			if (!ctx.readyGet())
				return;
			
			var ctx_saved = false;
			
			for (var ct = 0; ct < ctx.elements.length; ct++) {
				var id = getDOMID(ctx.elements[ct].comp, ctx.elements[ct].field);
				var el = elements.get(id);
				
				var saved = false;
				var prefix = getPrefix(el, ctx.keys);

				switch (el.type) {
					case ff.ffPage.lifeSaver._TYPE_FIELD:
						var value = getFieldData(el.id, el.struct);

						if (el.data.get(el.id).ori_value != value) {
							localStorage[prefix] = value;
							el.data.get(el.id).ori_value = value;
							saved = true;
						}
						break;

					case ff.ffPage.lifeSaver._TYPE_COMP:
						switch (el.struct.type) {
							case "ffDetails":
								var changed = false;

								var rows = parseInt("0" + jQuery.fn.escapeGet(el.id + "_rows").val());
								if (el.dtl_rows != rows) {
									changed = true;
									el.dtl_rows = rows;
								}

								var dtl_rows = [];
								for (var i = 0; i < rows; i++) {
									var dtl_row = ff.hash();
									el.struct.fields.each(function(field_id, field_data){
										var dtl_data = {
											"ori_value" : getFieldData(el.id + "_recordset_ori[" + i + "][" + field_id + "]", field_data)
											, "value" : getFieldData(el.id + "_recordset[" + i + "][" + field_id + "]", field_data)
										};
										dtl_row.set(field_id, dtl_data);

										if (!changed) {
											if (
													el.data.get(el.id + "_recordset_ori[" + i + "][" + field_id + "]").ori_value !== dtl_data.ori_value
													|| el.data.get(el.id + "_recordset[" + i + "][" + field_id + "]").ori_value !== dtl_data.value
											) {
												changed = true;
												el.data.get(el.id + "_recordset_ori[" + i + "][" + field_id + "]").ori_value = dtl_data.ori_value;
												el.data.get(el.id + "_recordset[" + i + "][" + field_id + "]").ori_value = dtl_data.value;
											}
										}
									});
									dtl_rows.push(dtl_row);
								}

								if (changed) {
									//ff.lsArrayStore(prefix + "_" + field_id, dtl_data, ["ori_value", "value"]);
									localStorage[prefix] = JSON.stringify(dtl_rows);
									saved = true;
									for (var i = 0; i < dtl_rows.length; i++) {
										el.struct.fields.each(function(field_id, field_data){
											if (dtl_rows[i].isset(field_id)) {
												el.data.set(el.id + "_recordset_ori[" + i + "][" + field_id + "]", {
													"attrs" : field_data
													, "ori_value" : dtl_rows[i].get(field_id).ori_value
												});
												el.data.set(el.id + "_recordset[" + i + "][" + field_id + "]", {
													"attrs" : field_data
													, "ori_value" : dtl_rows[i].get(field_id).value
												});
											}
										});
									}
								}
								break;

							case "ffRecord":
								var changed = false;
								var el_data = ff.hash();

								el.struct.fields.each(function(field_id, field_data){
									if (field_data.type === "key")
										return;

									var value = getFieldData(el.id + "_" + field_id, field_data);
									el_data.set(field_id, value);

									if (el.data.get(el.id + "_" + field_id).ori_value != value) {
										changed = true;
									}
								});

								if (changed) {
									localStorage[prefix] = JSON.stringify(el_data);
									saved = true;
									el.struct.fields.each(function(field_id, field_data){
										if (el_data.isset(field_id)) {
											el.data.set(el.id + "_" + field_id, {
												"attrs" : field_data
												, "ori_value" : getFieldData(el.id + "_" + field_id, field_data)
											});
										}
									});
								}
								break;
						}
						break;
				}
				
				if (saved) {
					ctx_saved = true;
				}
			}
						
			if (ctx_saved) {
				var lsobj = null;
				if (localStorage["lifeSaver"]) {
					lsobj = JSON.parse(localStorage["lifeSaver"]);
					lsobj.ctxs = ff.hash(lsobj.ctxs);
				} else {
					lsobj = {
						"ctxs" : ff.hash()
					};
				}
				if (!lsobj.ctxs.isset(ctx_id)) {
					lsobj.ctxs.set(ctx_id, ctx.getParams());
					localStorage["lifeSaver"] = JSON.stringify(lsobj);
				}
			}

		});
		
		if (observing)
			setTimeout('ff.ffPage.lifeSaver.save();', ff.ffPage.lifeSaver.polling);
	},

	"clear" : function (ctx, suffix) {
		if (typeof(ctx) === "string") {
			var tmp = ctxs.get(ctx);
			if (tmp === undefined) {
				if (localStorage["lifeSaver"]) {
					var lsobj = JSON.parse(localStorage["lifeSaver"]);
					lsobj.ctxs = ff.hash(lsobj.ctxs);
					tmp = lsobj.ctxs.get(ctx);
					/*if (tmp !== undefined) {
						lsobj.ctxs.unset(ctx);
						localStorage["lifeSaver"] = JSON.stringify(lsobj);
					}*/
				}
			}
			if (tmp === undefined) {
				throw "lifeSaver: Unknown ctx";
			}
			ctx = tmp;
		}
		
		if (ctx === undefined) {
			elements.each(function(id, el){
				var prefix = getPrefix(el, el.ctx.keys);

				localStorage.removeItem(prefix);
			});
		} else {
			for (var ct = 0; ct < ctx.elements.length; ct++) {
				var id = getDOMID(ctx.elements[ct].comp, ctx.elements[ct].field);
				if (suffix === undefined) {
					var el = elements.get(id);
					var prefix = getPrefix(el, ctx.keys);
					localStorage.removeItem(prefix);
				} else {
					localStorage.removeItem(id + suffix);
				}
			}
		}
	},

	"recover" : function (ctx) {
		for (var ct = 0; ct < ctx.elements.length; ct++) {
			var id = getDOMID(ctx.elements[ct].comp, ctx.elements[ct].field);
			var el = elements.get(id);
			
			var prefix = getPrefix(el, ctx.keys);
			
			if (localStorage[prefix] === undefined)
				continue;
			
			switch (el.type) {
				case ff.ffPage.lifeSaver._TYPE_FIELD:
					setFieldData(localStorage[prefix], el.id, el.struct);
					el.data.get(el.id).ori_value = localStorage[prefix];
					break;

				case ff.ffPage.lifeSaver._TYPE_COMP:
					switch (el.struct.type) {
						case "ffDetails":
							var el_data = JSON.parse(localStorage[prefix]);
							var rows = parseInt("0" + jQuery.fn.escapeGet(el.id + "_rows").val());
							
							if (!el_data.length) {
								el.data = ff.hash();
								el.dtl_rows = 0;
								//jQuery.fn.escapeGet(el.id + "_rows").val(0)
								if (rows) {
									var tmp_fields = [];
									
									for (var i = 0; i < rows; i++) {
										el.struct.fields.each(function(field_id, field_data){
											var suffix = "";
											switch (field_data.type) {
												case "key":
													suffix = "keys";
													break;
													
												default:
													suffix = "values";
											}
											tmp_fields.push({
												"name" : el.id + "_deleted_" + suffix + "[" + i + "][" + field_id + "]"
												, "value" : getFieldData(el.id + "_recordset_ori[" + i + "][" + field_id + "]", field_data)
											});
										});
									}
									
									ff.ajax.doRequest({"component" : el.id, "fields" : tmp_fields, "action" : "__test__"});
								}
								return;
							}
							
							for (var i = 0; i < el_data.length; i++) {
								el_data[i] = ff.hash(el_data[i]);
							}
							
							//var diff = el_data.length - rows;
							//if (diff !== 0) {
								el.dtl_rows = el_data.length;
								//jQuery.fn.escapeGet(el.id + "_rows").val(el.dtl_rows);
								
								var tmp_fields = undefined;
								var tmp_action = undefined;
								/*if (diff > 0) {
									document.getElementById(el.struct.main_record + '_detailaction').value = el.id; 
									document.getElementById('frmAction').value = el.struct.main_record + '_detail_addrows';
									document.getElementById(el.id + '_rowstoadd').value = diff;
								} else {*/
									tmp_fields = [
										{"name": el.struct.main_record + "_detailaction", "value" : el.id}
										, {"name" : el.id + "_rowstoadd", "value" : el_data.length}
									];
									tmp_action = el.struct.main_record  + "_detail_addrows";
								//}
								
								var deleted = 0;

								for (var i = 0; i < rows; i++) {
									var row_found = true;
									el.struct.fields.each(function(field_id, field_data){
										switch (field_data.type) {
											case "key":
												var tmp_value = getFieldData(el.id + "_recordset_ori[" + i + "][" + field_id + "]", field_data);
												var key_found = false;
												for (var t = 0; t < el_data.length; t++) {
													if (el_data[t].get(field_id).ori_value === tmp_value) {
														key_found = true;
														break;
													}
												}
												if (!key_found) {
													row_found = false;
													return true;
												}
												break;
										}
									});
									
									if (!row_found) {
										el.struct.fields.each(function(field_id, field_data){
											var suffix = "";
											switch (field_data.type) {
												case "key":
													suffix = "keys";
													break;
													
												default:
													suffix = "values";
											}
											tmp_fields.push({
												"name" : el.id + "_deleted_" + suffix + "[" + deleted + "][" + field_id + "]"
												, "value" : getFieldData(el.id + "_recordset_ori[" + i + "][" + field_id + "]", field_data)
											});
										});
										deleted++;
									}
								}
								
								ff.ajax.doRequest({
									'component' : el.id
									, 'fields' : tmp_fields
									, 'action' : tmp_action
									, "callback" : function () {
										for (var i = 0; i < el_data.length; i++) {
											el.struct.fields.each(function(field_id, field_data){
												if (el_data[i].isset(field_id)) {
													setFieldData(el_data[i].get(field_id).ori_value, el.id + "_recordset_ori[" + i + "][" + field_id + "]", field_data, field_data);
													setFieldData(el_data[i].get(field_id).value, el.id + "_recordset[" + i + "][" + field_id + "]", field_data, field_data, true);
													el.data.set(el.id + "_recordset_ori[" + i + "][" + field_id + "]", {
														"attrs" : field_data
														, "ori_value" : el_data[i].get(field_id).ori_value
													});
													el.data.set(el.id + "_recordset[" + i + "][" + field_id + "]", {
														"attrs" : field_data
														, "ori_value" : el_data[i].get(field_id).value
													});
												}
											});
										}
									}
								});
							/*} else if (diff === 0) {
								for (var i = 0; i < el_data.length; i++) {
									el.struct.fields.each(function(field_id, field_data){
										if (el_data[i].isset(field_id)) {
											setFieldData(el_data[i].get(field_id).ori_value, el.id + "_recordset_ori[" + i + "][" + field_id + "]", field_data, field_data);
											setFieldData(el_data[i].get(field_id).value, el.id + "_recordset[" + i + "][" + field_id + "]", field_data, field_data, true);
											el.data.set(el.id + "_recordset_ori[" + i + "][" + field_id + "]", {
												"attrs" : field_data
												, "ori_value" : el_data[i].get(field_id).ori_value
											});
											el.data.set(el.id + "_recordset[" + i + "][" + field_id + "]", {
												"attrs" : field_data
												, "ori_value" : el_data[i].get(field_id).value
											});
										}
									});
								}
							}*/
							break;

						case "ffRecord":
							var el_data = ff.hash(JSON.parse(localStorage[prefix]));
							
							el.struct.fields.each(function(field_id, field_data){
								if (el_data.isset(field_id)) {
									setFieldData(el_data.get(field_id), el.id + "_" + field_id, field_data);
									el.data.get(el.id + "_" + field_id).ori_value = el_data.get(field_id);
								}
							});
							break;
					}
					break;
			}
		}
	},

	"check" : function (ctx, unblockui) {
		/*if (ff.ajax.getblocked_ui() > 1) {
			setTimeout('ff.ffPage.lifeSaver.check("' + id + '", ' + unblockui + ');', 1000);
			return;
		}*/
		
		var saved = false;
		var ready = true;
		
		for (var ct = 0; ct < ctx.elements.length; ct++) {
			var id = getDOMID(ctx.elements[ct].comp, ctx.elements[ct].field);
			var el = elements.get(id);
			
			var prefix = getPrefix(el, ctx.keys);
			
			if (localStorage[prefix])
				saved = true;

			if (el.waiting !== 0)
				ready = false;
		};
		
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
				ff.ffPage.lifeSaver.recover(ctx);
			} else {
				ff.ffPage.lifeSaver.clear(ctx);
			}
		}
		
		//jQuery(window).bind('beforeunload', ff.ffPage.lifeSaver.beforeunload);
		
		if (!observing) {
			observing = true;
			setTimeout('ff.ffPage.lifeSaver.save();', ff.ffPage.lifeSaver.polling);
		}
	},
	
	"debug" : function () {
		console.log(elements);
		elements.dump();
	},
	
	"wantsToLeave" : function () {
		willToLeave = true;
	},

	"beforeunload" : function () {
		if (willToLeave) {
			that.clear();
			return undefined;
		} else {
			var saved = false;

			elements.each(function(id, el){
				var prefix = getPrefix(el);

				if (localStorage[prefix])
					saved = true;
			});

			if (saved) {
				return "Dei dati sono stati modificati, uscendo dalla pagina si perderanno le modifiche";
			}
		}
	}
}; // publics' end

return that;

// code's end.
})();
