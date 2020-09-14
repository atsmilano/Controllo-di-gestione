/**
 * Forms Framework Javascript Handling Object
 *	activecombo fields' plugin namespace
 **/

ff.ffField.actex = (function () {

// jQuery Override (patch)

 
/* privates */
var innerURL			= (window.location.pathname == "/" ? "" : window.location.pathname) + "/actexparse" /*+ window.location.search*/;  
var icons				= {};
var frameworkCSS		= {};
var loading_markup		= "";

var instances			= ff.hash();

var sources				= ff.hash();
var controls_waiting	= ff.hash();

var initialized = false;

function normalizeVal(value) {
	if (value === null || value === undefined)
		return value;
	if (typeof value === "number")
		value = value.toString();
	if (typeof value !== "string")
		throw  "ff.ffField.actex - Invalid value";
	
	return value;
}

var activecombo = function(params) {
	// inits
	
	/* privates */
	var __id = params.id;
	var component = params.component;
	
	var multi = params.multi;
	var filled = false;
	var first_fill = true;
	var displayed_value = normalizeVal(params.selected_label);
	var old_father_value = undefined;
	var buttons = ff.hash();
	buttons.set("add", true);
	buttons.set("delete", true);
	buttons.set("edit", true);
	
	/* privates' end*/
	
	var that = { /* publics */
		__ff : true /* used to recognize ff'objects*/
		, "father"					: params.father
		, "childs"					: params.childs
		, "data"					: params.data || []
		, "value_ori"				: {}
		, "value"					: normalizeVal(params.selected_value)
		, "options" 				: params.options
		, "insert_mode"				: false
		, "has_focus"				: false
		, "loading_markup"			: undefined
		
		, "getID" : function () {
			return __id;
		}

		, "getNode"  : function () {
			return jQuery.fn.escapeGet("actex_" + __id).get(0);
		}
		, "getService" : function () {
			var tmp;
			if (that.options.service !== null)
				tmp = that.options.service;
			else
				tmp = innerURL;
			
			if (component === "" || component === undefined)
				return tmp;
			
			var srv_uri_parts = tmp.parseUri();
			if (srv_uri_parts.host !== "")
				return tmp;
			
			var cmp_uri_parts = ff.struct.get("comps").get(component).url.parseUri();
			if (cmp_uri_parts.host === "")
				return tmp;

            var port = (cmp_uri_parts.port && cmp_uri_parts.port != 80
                    ? ":" + cmp_uri_parts.port
                    : ""
            );

            return cmp_uri_parts.protocol + "://" + cmp_uri_parts.host + port + "/" + tmp.ltrim("/");
		}

		, "getCacheDataSrc" : function (key) {
            return (that.options.data_src 
            		? that.options.data_src 
            		: (that.options.service
                        ? that.options.name + (key ? "-" + key : "")
                        : (innerURL 
                            ? innerURL 
                            : __id
                        )
                    ));
		}
		, "setValue" : function (value) {
			that.value = normalizeVal(value);
		}
		
		, "getFather" : function() {
			if (that.father)
				return ff.ffField.actex.getInstance(that.father);
			else
				return undefined;
		}
		
		, "getFatherValue" : function() {
			if (that.father)
				return ff.ffField.actex.getInstance(that.father).value;
			else
				return null;
		}
		
		, "getOldFatherValue" : function() {
			if (that.father)
				return old_father_value;
			else
				return undefined;
		}
		
		, "isFilled"  : function () {
			return filled;
		}
		
		, "buttonToggle" : function(button, status) {
			if (status !== undefined) {
				buttons.set(button, status);
			} else {
				buttons.set(button, !buttons.get(button));
			}
		}
		
		, "change" : function (reset_childs, value, desc) {
			value = normalizeVal(value);
			var old_value = that.value;
			
			if (value === undefined)
				that.value = jQuery.fn.escapeGet(__id).val();
			else
				that.value = value;
			
			if(desc) {
				that.value_ori = {
					"value" : value,
					"desc" : desc
				}; 
			}			

			if (that.options.autocomplete.enable || that.options.control_type == "checkbox")
				jQuery.fn.escapeGet(__id).val(that.value);

			var res = that.doEvent({
				"event_name"	: "change",
				"event_params"	: [that, old_value, 'change']
			});
		
			if (res !== undefined && res[res.length - 1])
				return;

			res = ff.ffField.actex.doEvent({
				"event_name"	: "change",
				"event_params"	: [that, old_value, 'change']
			});
	
			if (res !== undefined && res[res.length - 1])
				return res[res.length - 1];

			updatebt();

			if (that.childs.length) {
				var rc = true;
				that.childs.each(function (a, child) {
					rc &= ff.ffField.actex.getInstance(child).refill(reset_childs ? null : undefined);
				});
				return rc;
			} else {
				return true
			}
		}
		
		, "resetChildsPreRedir" : function (value) {
			if (that.childs.length) {
				that.childs.each(function (a, child) {
					var tmp = ff.ffField.actex.getInstance(child);
					tmp.value = (value === undefined ? null : value);
					jQuery.fn.escapeGet(child).val((value === undefined ? "" : value));
					tmp.resetChildsPreRedir(value);
				});
			}
		}

		, "update" : function (new_value, reset_childs) {
			new_value = normalizeVal(new_value);
			reset_childs = reset_childs === undefined ? that.options.reset_childs : reset_childs;
			
			//var old_value = (displayed_value === undefined ? that.value : displayed_value);
			var old_value = that.value;
			if (new_value === undefined)
				new_value = that.value;

			if(new_value === null) {
				if(that.options.select_one && that.options.select_one_val) {
					displayed_value = that.options.select_one_label;
					new_value = that.options.select_one_val;  
				} else if(that.options.select_noone && that.options.select_noone_val === new_value) {
					displayed_value = that.options.select_noone_label;
					new_value = that.options.select_noone_val;  
				} else if(that.options.control_type == "combo" && jQuery.fn.escapeGet(__id).children().length) { 
					new_value = jQuery.fn.escapeGet(__id).first().val();
				}
			}
			if(!multi && !jQuery.fn.escapeGet(__id + "_label").val() && new_value && displayed_value)							
				jQuery.fn.escapeGet(__id + "_label").val(displayed_value);

			jQuery.fn.escapeGet(__id).val(new_value);
			
			// visual update
			old_father_value = that.getFatherValue();
			if(new_value) {
				that.value_ori = {
					"value" : new_value,
					"desc" : displayed_value
				}; 			
			}			
			that.value = new_value;

			if (new_value !== old_value) {
				if (that.options.autocomplete.enable)
					jQuery.fn.escapeGet(__id).val(that.value);

				var res = that.doEvent({
					"event_name"	: "change",
					"event_params"	: [that, old_value, 'load']
				});

				if (res !== undefined && res[res.length - 1])
					return;

				res = ff.ffField.actex.doEvent({
					"event_name"	: "change",
					"event_params"	: [that, old_value, 'load']
				});

				if (res !== undefined && res[res.length - 1])
					return;
			}

			updatebt();
			
			if (first_fill) {
				first_fill = false;
				ff.doEvent({
					"event_name" : "initIFElement"
					, "event_params" : [__id, "actex"]
				});
			}
			
			if (that.childs.length) {
				that.childs.each(function (a, child) {
					child = ff.ffField.actex.getInstance(child);
					if (!child.isFilled() || reset_childs || child.getOldFatherValue() !== new_value)
						child.refill(reset_childs ? null : undefined);
					else {
						var node = child.getNode();
						if (node && node.firstChild)
							node.firstChild.disabled = false;
						child.update();
					}
				});
			}

            if(that.options.control_type == "checkbox") {
                if(jQuery(".draggable", that.getNode()).length)
                    jQuery(".draggable", that.getNode()).sortable();

                if(jQuery.fn.escapeGet(__id).closest(".ui-dialog").length)
                    ff.ffPage.dialog.adjSize();
            }            
		}

		, "recalc" : function () {
			var data = {"value" : [], "label" : []};
			var sep = that.options.separator;
			var $node = jQuery(that.getNode());

            jQuery(".checkgroup .line", $node).each(function() {
                if (jQuery("input", this).is("checked")) {
                	data["value"].push(jQuery("input", this).val());
                	data["label"].push(jQuery("input", this).val());
                }
            });

			that.change(false, data["value"].join(sep), data["label"].join(sep));
		}
		, "del" : function(id) {
			var data = {"value" : [], "label" : []};
			var sep = that.options.separator;
			var $node = jQuery(that.getNode());

			$node.parent().next(".actex-multi").find("li[data-id='" + id + "']").remove();
			$node.parent().next(".actex-multi").children().each(function() {
				data["value"].push(jQuery(this).data("id"));
				data["label"].push(jQuery(this).text());
			});
			
			that.change(false, data["value"].join(sep), data["label"].join(sep));
		}
		, "add" : function(item) {
			var data = {"value" : [], "label" : []};
			var $node = jQuery(that.getNode());
			var sep = that.options.separator;
			if(!$node.parent().next(".actex-multi").find("li[data-id='" + item.id + "']").length) {
				$node.parent().next(".actex-multi").append('<li class="' + frameworkCSS["item"] + '" data-id="' + item.id + '"><mark>' + (item.url ? '<a href="' + item.url + '" target="_blank">' + (item.image ? '<img src="' + item.image + '" />' : "") + item.label + "</a>" : (item.image ? '<img src="' + item.image + '" />' : "") + item.label) + '</mark><a href="javascript:ff.ffField.actex.del(\'' + __id + '\', \'' + item.id + '\');" class="' + frameworkCSS["badge"] + '"><i class="' + icons["delete"] + '"></i></a></li>');
				$node.parent().next(".actex-multi").children().each(function() {
					data["value"].push(jQuery(this).data("id"));
					data["label"].push(jQuery(this).text());
				});
			
				that.change(false, data["value"].join(sep), data["label"].join(sep));
				jQuery.fn.escapeGet(__id + "_label").val("");
			}
		}

		, "refill" : function (new_value, father_value, force_refresh) {
			var node = that.getNode();
			new_value = normalizeVal(new_value);
			father_value = normalizeVal(father_value);

			if (new_value === undefined)
				new_value = that.value;

			if (father_value === undefined)
				father_value = that.getFatherValue();
			
			/* like activecombo, preloaded in page*/
			if (that.options.data_src == "" && that.options.service === null) {
				var buffer = parseheader(new_value);

				var opt_value = "";
				var opt_text = "";
				var found_value = false;
				//displayed_value = null;
				
				if(that.options.control_type == "input") {
					that.data.each(function (a, tmp_data){
						opt_value = tmp_data[0];
						opt_text = tmp_data[1];
						found_value |= (opt_value == new_value);
						if ((father_value == null && that.father == null) || tmp_data[0] == father_value) {
							if (
									!that.options.limit_select
									|| (that.options.limit_select && opt_value == new_value)
								) {
								buffer += ' value="' + opt_value + '" ';
								return true;
							}
						}
					});
					buffer += ' />';
					node.innerHTML = buffer;
				} else if(that.options.control_type == "label") {
					that.data.each(function (a, tmp_data){
						opt_value = tmp_data[0];
						opt_text = tmp_data[1];
						found_value |= (opt_value == new_value);
						if ((father_value == null && that.father == null) || tmp_data[0] == father_value) {
							if (!that.options.limit_select || (that.options.limit_select && opt_value == new_value)) {
								buffer += opt_text;
								return true;
							}
						}
					});
					buffer += '</span>';
					node.innerHTML = buffer;
				} else if(that.options.control_type == "checkbox") {
					var separator = that.options.separator;
					if(!separator.length)
						separator = ",";

					var arrSelectedValue = ff.coalesce(new_value, '').split(separator);
					that.data.each(function (i, tmp_data){
						opt_value = tmp_data[1];
						opt_text = tmp_data[2];
						if ((father_value == null && that.father == null) || tmp_data[0] == father_value) {
							if (!that.options.limit_select || (that.options.limit_select && opt_value == new_value)) {
								buffer += '<div class="line"><label for="' + __id + '_' + i + '"><input type="checkbox" id="' + __id + '_' + i + '" value="' + opt_value + '" ';
								for (var x = 0; x < arrSelectedValue.length; x++) {
									if (opt_value == arrSelectedValue[x])
										buffer += 'checked="checked" ';
								}

								buffer += ' onChange="';
								buffer += 'ff.ffField.actex.recalc(\'' + __id + '\');" ';
								buffer += ' />' + opt_text + '</label></div>';
							}
						}
						if(arrSelectedValue.indexOf(opt_value) >= 0) {
							displayed_value = opt_text;
							found_value = true;
						}						
					});
					buffer += '</div>';
					node.innerHTML = buffer; 
				} else if (that.options.autocomplete.enable) {
					var dataAutocomplete = [];

					/*if (that.options.select_one) {
						tmp_autocomplete.push({"label" : that.options.select_one_label, "id" : that.options.select_one_val});
						if (that.options.select_one_val == new_value) {
							found_value = true;
							buffer += ' value="' + that.options.select_one_label + '" ';
						}
					}*/
					if (that.options.select_noone) {
						dataAutocomplete.push({"label" : that.options.select_noone_label, "id" : that.options.select_noone_val});
					}
					if(that.data.length) {
						that.data.sort(function(a, b) {
							return a.desc.trim().toLowerCase().indexOf(new_value) - b.desc.trim().toLowerCase().indexOf(new_value);
						});
						that.data.each(function (i, tmp_data) {
							opt_value = tmp_data[1];
							opt_text = tmp_data[2];
							opt_grp = tmp_data[5]; 
							if ((father_value == null && that.father == null) || tmp_data[0] == father_value) {
								if (!that.options.limit_select || (that.options.limit_select && opt_value == new_value)) {
									dataAutocomplete.push({"label" : opt_text, "id" : opt_value, "url" : tmp_data[3], "image" : tmp_data[4]});
								}
							}
							if(opt_value == new_value) {
								displayed_value = opt_text;
								found_value = true;
							}

							if(dataAutocomplete.length > 100
								|| (!new_value && i > 100)
							)
							return;

						});

						dataAutocomplete.sort(function(a, b) {
						    return new_value && a.label.toLowerCase().indexOf(new_value) === 0 ? -1 : a.label.trim().toLowerCase().localeCompare(b.label.trim().toLowerCase());
						});						
					}
					if(!jQuery.fn.escapeGet(__id).length) {
						//jQuery.fn.escapeGet(__id + "_label").autocomplete("destroy");
						buffer += ' />';
						buffer += '<input type="hidden" id="' + __id + '" name="' + __id + '" value="' + new_value + '" />';

						node.innerHTML = buffer;
						
						autocomplete(father_value, dataAutocomplete);
					}
				} else {
					var bufferAttr = "";
					var bufferGroup = ff.hash();
					var bufferOption = "";

					that.data.each(function (i, tmp_data){
						opt_value = tmp_data[1];
						opt_text = tmp_data[2];
						if ((father_value == null && that.father == null) || tmp_data[0] == father_value) {
							if (!that.options.limit_select || (that.options.limit_select && opt_value == new_value)) {
								bufferAttr = "";
								if(tmp_data[3]) {
									for(var x in tmp_data[3]) {
										bufferAttr += " " + x + "=" + '"' + tmp_data[3][x] + '" ';
									}
								}

								bufferOption = '<option '
									+ bufferAttr
									+ 'value="' + opt_value + '" ' 
									+ '>' + opt_text + '</option>';

								if(tmp_data[4]) {
									var tmp = (bufferGroup.get(tmp_data[4]) === undefined ? "" : bufferGroup.get(tmp_data[4])) + bufferOption;
									bufferGroup.set(tmp_data[4], tmp);
								} else {
									buffer += bufferOption;
								}
							}
							if(opt_value == new_value) {
								displayed_value = opt_text;
								found_value = true;
							}
						}
					});
					
					bufferGroup.each(function (key, value) {
						buffer += '<optgroup label="' + key + '">' + value + '</optgroup>';	
					});
					
					buffer += '</select>';
					if(that.options.addPlus) {
						buffer += '<a class="' + icons.plus + '" href="javascript:void(0);" onClick="jQuery(this).prev(\'select\').children(\'option:selected\').val(parseInt(jQuery(this).prev(\'select\').children(\'option:selected\').val()) + 1); jQuery(this).prev(\'select\').children(\'option:selected\').text(jQuery(this).prev(\'select\').children(\'option:selected\').val());"></a><a class="' + icons.minus + '" href="javascript:void(0);" onClick="if(parseInt(jQuery(this).prev().prev(\'select\').children(\'option:selected\').val()) >= 2) { jQuery(this).prev().prev(\'select\').children(\'option:selected\').val(parseInt(jQuery(this).prev().prev(\'select\').children(\'option:selected\').val()) - 1); jQuery(this).prev().prev(\'select\').children(\'option:selected\').text(jQuery(this).prev().prev(\'select\').children(\'option:selected\').val()); }"></a>';
					}
					node.innerHTML = buffer; 
				}
				
				filled = true;

				if(loading_markup)
					jQuery(node).next(".actex-actions").children("i:first").remove();
				if(that.options.hideEmpty && !that.data.length) {
					if(that.options.hideEmpty === true)
	                        jQuery(node).show();
	                    else if(that.options.hideEmpty == "all")
	                        jQuery(node).closest("DIV.actex").css("opacity", "");
	                    else 
	                        jQuery(node).closest(that.options.hideEmpty).show();
				}            

				if (found_value === false)
					new_value = undefined; /*cambiandolo si perde i dati on actex multi on dom ready*/
				
				if(!displayed_value)
					displayed_value = new_value;

				that.update(new_value);

				that.doEvent({
					"event_name"	: "refill",
					"event_params"	: [node]
				});
				
				ff.ffField.actex.doEvent({
					"event_name"	: "refill",
					"event_params"	: [__id, node]
				});

				return true;
			} else {
				/* async loading*/
				cascade_disable(true);  /* disable fathers*/
				cascade_disable(false); /* disable childs*/
				display_loading();

				var str_data = "type=actex";
				if(father_value)
                	str_data += "&father_value=" + escape(father_value);
                if (that.options.data_src)
                    str_data += "&data_src=" + escape(that.options.data_src);
                //if (new_value)
                  //  str_data += "&sel_val=" + escape(new_value);
				
				/* get ancestor's data */
				var ancest_data = "";
				var tmp_father = that.getFather()
				while (tmp_father) {
					ancest_data = "&ffActex_parent_data[" + ff.doubleEncodeURIComponent(tmp_father.getID()) + "]=" + ff.encodeURIComponent(tmp_father.value) + ancest_data;
					tmp_father = tmp_father.getFather();
				}
				str_data = str_data + ancest_data;

				delayed_request(str_data, new_value, father_value, force_refresh);

				return false;
			}
		}

		, "child_error_display" : function () {
			var node = that.getNode();
			if (node) {
				node.innerHTML = 'Impossibile connettersi con il server, riprovare più tardi.';
				if(!that.options.hideEmpty || that.options.hideEmpty === true)
                    jQuery(node).show();
                else if(that.options.hideEmpty == "all")
                    jQuery(node).closest("DIV.actex").css("opacity", "");
                else 
                    jQuery(node).closest(that.options.hideEmpty).show();

				if (that.childs.length) {
					that.childs.each(function (a, child) {
						ff.ffField.actex.getInstance(child).child_error_display();
					});
				}
			}
		}

		, "async_refill" : function (retData, selected_value, father_value, response, fullsearch) {
			var node = that.getNode();
			var buffer = "";

			if (retData === null) {
				that.child_error_display();
				return;
			}

			buffer = parseheader(selected_value);

			var opt_value = "";
			var opt_text = "";
			var found_value = false;
			var new_value = selected_value;
			//displayed_value = null;
			
			if(that.options.control_type == "input") {
				for (var i = 0; i < retData.length; i++) {
					opt_value = retData[i].value;
					opt_text = retData[i].desc;
					found_value |= (opt_value == selected_value);
					if (!that.options.limit_select || (that.options.limit_select && opt_value == selected_value)) {
						buffer += ' value="' + opt_value + '" ';
						break;
					}
				}
				buffer += ' />';
				node.innerHTML = buffer;
			} else if(that.options.control_type == "label") {
				for (var i = 0; i < retData.length; i++) {
					opt_value = retData[i].value;
					opt_text = retData[i].desc;
					found_value |= (opt_value == selected_value);
					if (!that.options.limit_select || (that.options.limit_select && opt_value == selected_value)) {
						buffer += opt_text;
						break;
					}
				}
				buffer += '</span>';
				node.innerHTML = buffer;
			} else if(that.options.control_type == "checkbox") {
				var separator = that.options.separator;
				if(!(separator.length))
					separator = ",";

				var arrSelectedValue = ff.coalesce(selected_value, '').split(separator);
				for (var i = 0; i < retData.length; i++) {
					opt_value = retData[i].value;
					opt_text = retData[i].desc;
					if (!that.options.limit_select || (that.options.limit_select && opt_value == selected_value)) {
						buffer += '<div class="line"><label for="' + __id + '_' + i + '"><input type ="checkbox" id="' + __id + '_' + i + '" value="' + opt_value  + '" ';
						for (var x = 0; x < arrSelectedValue.length; x++) {
							if (opt_value == arrSelectedValue[x])
								buffer += 'checked="checked" ';
						}

						buffer += ' onChange="';
						buffer += 'ff.ffField.actex.recalc(\'' + __id + '\');" ';
						buffer += ' />' + opt_text + '</label></div>';
					}
					if(arrSelectedValue.indexOf(opt_value) >= 0) {
						displayed_value = opt_text;
						found_value = true;
					}
				}
				buffer += '</div>'; 
				node.innerHTML = buffer;
			} else if (that.options.autocomplete.enable) {
				var dataAutocomplete = [];
				that.data = [];

				/*if (that.options.select_one) {
					tmp_autocomplete.push({"label" : that.options.select_one_label, "id" : that.options.select_one_val});
					if (that.options.select_one_val == selected_value) {
						found_value = true;
						buffer += ' value="' + that.options.select_one_label + '" ';
					}
				}*/ 
				if (that.options.select_noone) {
					dataAutocomplete.push({"label" : that.options.select_noone_label, "id" : that.options.select_noone_val});
				}
				if(retData.length) {
					retData.sort(function(a, b) {
						return a.desc.trim().toLowerCase().indexOf(selected_value) - b.desc.trim().toLowerCase().indexOf(selected_value);
					});
								
					for (var i = 0; i < retData.length; i++) {
						opt_value = retData[i].value;
						opt_text = retData[i].desc;
						opt_grp = retData[i].group; /*da gestire*/
						if (!that.options.limit_select || (that.options.limit_select && opt_value == selected_value)) {
							var found = true;
							if(selected_value && !fullsearch) {
								var patt = new RegExp(selected_value.replace(/\s/g, ".*"), "i");
								found = patt.test(opt_text);
							}
							if(found)
								dataAutocomplete.push({"label" : opt_text, "id" : opt_value, "url" : retData[i]["permalink"], "image" : retData[i]["image"]});

							that.data.push([father_value, opt_value, opt_text, retData[i]["permalink"], retData[i]["image"], opt_grp]);
						}

						if((response ? opt_text : opt_value) == selected_value) {
							new_value = opt_value;
							displayed_value = opt_text;
							found_value = true;
						}

						if(dataAutocomplete.length > 100
							|| (!selected_value && i > 100)
						)
							break;
						
					}
				
					if(dataAutocomplete.length) {
						dataAutocomplete.sort(function(a, b) {
						    return selected_value && a.label.trim().toLowerCase().indexOf(selected_value) === 0 ? -1 : a.label.trim().toLowerCase().localeCompare(b.label.trim().toLowerCase());
						});				

					}
				}
                if(response) {
                    response(dataAutocomplete);
                }

				if(!jQuery.fn.escapeGet(__id).length) {
					//jQuery.fn.escapeGet(__id + "_label").autocomplete("destroy");
					buffer += ' />';
					buffer += '<input type="hidden" id="' + __id + '" name="' + __id + '" value="' + selected_value + '" />';
					node.innerHTML = buffer;
					
					autocomplete(father_value);
				}
			} else {
				var bufferAttr = "";
				var bufferGroup = ff.hash();
				var bufferOption = "";

				for (var i = 0; i < retData.length; i++) {
					opt_value = retData[i].value;
					opt_text = retData[i].desc;
					if (!that.options.limit_select || (that.options.limit_select && opt_value == selected_value)) {
						bufferAttr = "";
						if(retData[i].attr) {
							for(var x in retData[i].attr) {
								bufferAttr += " " + x + "=" + '"' + retData[i].attr[x] + '" ';
							}
						}

						bufferOption = '<option '
							+ bufferAttr
							+ 'value="' + opt_value + '" ' 
							+ '>' + opt_text + '</option>';

						if(retData[i]["group"]) {
							var tmp = (bufferGroup.get(retData[i]["group"]) === undefined ? "" : bufferGroup.get(retData[i]["group"])) + bufferOption;
							bufferGroup.set(retData[i]["group"], tmp);
						} else {
							buffer += bufferOption;
						}
					}
					if(opt_value == selected_value) {
						displayed_value = opt_text;
						found_value = true;
					}
				}

				bufferGroup.each(function (key, value) {
					buffer += '<optgroup label="' + key + '">' + value + '</optgroup>';	
				});

				buffer += '</select>';
				if(that.options.addPlus) {
					buffer += '<a class="qta-piu" href="javascript:void(0);" onClick="jQuery(this).prev(\'select\').children(\'option:selected\').val(parseInt(jQuery(this).prev(\'select\').children(\'option:selected\').val()) + 1); jQuery(this).prev(\'select\').children(\'option:selected\').text(jQuery(this).prev(\'select\').children(\'option:selected\').val());">+</a><a class="qta-meno" href="javascript:void(0);" onClick="if(parseInt(jQuery(this).prev().prev(\'select\').children(\'option:selected\').val()) >= 2) { jQuery(this).prev().prev(\'select\').children(\'option:selected\').val(parseInt(jQuery(this).prev().prev(\'select\').children(\'option:selected\').val()) - 1); jQuery(this).prev().prev(\'select\').children(\'option:selected\').text(jQuery(this).prev().prev(\'select\').children(\'option:selected\').val()); }">-</a>';
				}
				node.innerHTML = buffer;
			}

			filled = true;
			
			if(loading_markup)
				jQuery(node).next(".actex-actions").children("i:first").remove();
			if(that.options.hideEmpty && !retData.length) {
				if(that.options.hideEmpty === true)
                    jQuery(node).hide();
                else if(that.options.hideEmpty == "all")
                    jQuery(node).closest("DIV.actex").css("opacity", "0");
                else 
                    jQuery(node).closest(that.options.hideEmpty).hide();
			}

			if (found_value === false)
				new_value = undefined; /*cambiandolo si perde i dati on actex multi on dom ready*/

			if(!displayed_value)
				displayed_value = selected_value;				

			that.update(new_value);

			parent_enable();

			//ff.ajax.unblockUI();

			that.doEvent({
				"event_name"	: "refill",
				"event_params"	: [node]
			});
				
			ff.ffField.actex.doEvent({
				"event_name"	: "refill",
				"event_params"	: [__id, node]
			});
		}
	}; /* public's end */

	function delayed_request (str_data, selected_value, father_value, force_refresh, response, fullsearch) {
		//ff.ajax.blockUI();

		if(that.options.use_cache && !force_refresh) {
			var fValue = father_value || "null";
			var id_data_src = that.getCacheDataSrc(selected_value);

			if (sources.isset(id_data_src) && sources.get(id_data_src).isset(fValue)) {
				if (sources.get(id_data_src).get(fValue) === true) {
					if (!controls_waiting.isset(id_data_src))
						controls_waiting.set(id_data_src, ff.hash());
					controls_waiting.get(id_data_src).set(ff.getUniqueID(), {"id" : __id, "selected_value" : selected_value, "father_value" : father_value});
					return;
				}

				that.async_refill(sources.get(id_data_src).get(fValue), selected_value, father_value, response, fullsearch);
				return;
			}

			if (!sources.isset(id_data_src))
				sources.set(id_data_src, ff.hash());

			sources.get(id_data_src).set(fValue, true);
		}
		
		cascade_disable(true);  /* disable fathers*/
		cascade_disable(false); /* disable childs*/
		
		var url = ff.fixPath(that.getService());
		var dataType = (ff.origin != ff.httpGetOrigin() ? "jsonp json" : "json");
		var mydata = {
			"selected_value" : selected_value
			, "father_value" : fValue
			, "fullsearch" : fullsearch
			, "response" : response
		};

//alert("S");
		jQuery.ajax({
			  url		: url
			, async		: true
			, data		: str_data
			, type		: "GET"
			, dataType	: dataType
			, jsonp		: (dataType == "jsonp json" ? "XHR_JSONP" : undefined)
			, success	: async_success
			, error		: async_error
			, mydata	: mydata
		});
	};
	
	function async_error() {
		that.child_error_display();
		//ff.ajax.unblockUI();
	};

	function async_success (retData, textStatus) {
		var selected_value = this.mydata.selected_value;
		var response = this.mydata.response;
		var fullsearch = this.mydata.fullsearch;
		var fValue = this.mydata.father_value;
		var id_data_src = that.getCacheDataSrc(selected_value);
        var id_source = (that.options.data_src
            ? id_data_src
            : ""
        );        
		//if(!that.options.data_src)
		//	console.log("non dovrebbe essere vuoto. da verificare");

		if (!(retData.widget && retData.widget.actex && retData.widget.actex["D" + id_source]))
			return async_error();
			
		var father_value = that.getFatherValue();
		retData = father_value ? retData.widget.actex["D" + id_source]["F" + father_value] : retData.widget.actex["D" + id_source];

		that.async_refill(retData, selected_value, father_value, response, fullsearch);
		
		if (that.options.use_cache) {
			sources.get(id_data_src).set(fValue, retData);
			if (controls_waiting.isset(id_data_src)) 
				controls_waiting.get(id_data_src).each(function (k, v) {
				var tmp = ff.ffField.actex.getInstance(v.id);
				if (v.father_value === father_value) {
					tmp.async_refill(retData, v.selected_value, v.father_value, response, fullsearch);
					controls_waiting.get(id_data_src).unset(k);
				}
			});
	   }
	};

	function parent_enable (inst) {
		if (inst === undefined)
			inst = that;

		if (inst.has_focus) {
			if (!inst.options.autocomplete.enable)
				jQuery.fn.escapeGet(inst.getID()).focus();
			else
				jQuery.fn.escapeGet(inst.getID() + "_label").focus();

			inst.has_focus = false;
		}
		
		if (inst.father) {
			var father = inst.getFather();
			var node = father.getNode();
			if (node && node.firstChild && node.firstChild.disabled)
			{
				node.firstChild.disabled = false;
				var res = father.doEvent({
					"event_name"	: "enable",
					"event_params"	: [father]
				});
				
				var res = ff.ffField.actex.doEvent({
					"event_name"	: "enable",
					"event_params"	: [father]
				});
			}

			parent_enable(father);
		}
	};

	function cascade_disable (moveup, inst) {
		if (inst === undefined)
			inst = that;
		
		if (!inst.options.autocomplete.enable)
			inst.has_focus = jQuery.fn.escapeGet(inst.getID()).is(":focus");
		
		var node = inst.getNode();

		//if (node && node.firstChild) da sistemare 
		//	node.firstChild.disabled = true;

		if (moveup && inst.father) {
			cascade_disable(true, inst.getFather());
		} else if (!moveup && inst.childs.length) {
			for (var a = 0; a < inst.childs.length; a++) {
				cascade_disable(false, ff.ffField.actex.getInstance(inst.childs[a]));
			}
		}
	};

	function display_loading () {
		filled = false;
		var node = that.getNode();
		
		//if (that.options.autocomplete.enable) {
		//	jQuery.fn.escapeGet(__id + "_label").autocomplete("destroy");
		//}
		
		//node.innerHTML = that.loading_markup || loading_markup;
		
		if(loading_markup)
			jQuery(node).next(".actex-actions").prepend(loading_markup);
		if(!that.options.hideEmpty || that.options.hideEmpty === true)
            jQuery(node).show();
        else if(that.options.hideEmpty == "all")
            jQuery(node).closest("DIV.actex").css("opacity", "1");
        else 
            jQuery(node).closest(that.options.hideEmpty).show();

	};

	function updatebt () {
		if (!jQuery.fn.escapeGet("actex_" + __id + "_dialogaddlink").length)
			return;

		var res = that.doEvent({
			"event_name"	: "updatebt",
			"event_params"	: [that]
		});
		
		if (res !== undefined && res[res.length - 1])
			return;

		res = ff.ffField.actex.doEvent({
			"event_name"	: "updatebt",
			"event_params"	: [that]
		});
			
		if (res !== undefined && res[res.length - 1])
			return;

		ff.pluginAddInit("ff.ffPage.dialog", function () {
			drawDialogButtons();
		});
	};

	function autocomplete(father_value, data) {
		if (father_value === undefined)
			father_value = that.getFatherValue();

		var autocomp_fullsearch = false;

	//jQuery("#calendar-modify_ID_customer_label").data("ui-autocomplete")._trigger("change");	
		jQuery.fn.escapeGet(__id + "_label").autocomplete({
			source: function( request, response ) {
                if(data) {
					response(data);
				} else {
					/*cascade_disable(true);   disable fathers*/
					/*cascade_disable(false);  disable childs*/
					display_loading();
					/* get ancestor's data */
					var ancest_data = "";
					var tmp_father = that.getFather();
					while (tmp_father) {
						father_value = tmp_father.value;
						ancest_data = "&ffActex_parent_data[" + ff.doubleEncodeURIComponent(tmp_father.getID()) + "]=" + ff.encodeURIComponent(father_value) + ancest_data;
						tmp_father = tmp_father.getFather();
					}
					
					
					var str_data = "type=actex";
					if(father_value)
                		str_data += "&father_value=" + escape(father_value);
	                if (that.options.data_src)
	                    str_data += "&data_src=" + escape(that.options.data_src);
	                //if (that.value)
	                    //str_data += "&sel_val=" + escape(that.value);
	                if (request.term)
	                    str_data += "&term=" + escape(request.term);
					
					str_data = str_data + ancest_data;

					delayed_request(str_data, request.term, father_value, false, response, autocomp_fullsearch);
					autocomp_fullsearch = false;
				}
			}
			, minLength: 0
			, select: function( event, ui ) {
				if(multi) {
					that.add(ui.item);
					//jQuery.fn.escapeGet(__id + "_label").
					jQuery(this).blur();
					return false;
				} else {
					that.change(false, ui.item.id, ui.item.label);
				//var $menu = jQuery(this).autocomplete("widget").menu();
				}
			}
			, open: function( event, ui ) {
				var dialogTop = 0;
				
				$this = jQuery(this);
				$widget = $this.autocomplete("widget");
				$menu = $widget.menu();
				
				$dialog = $this.closest(".ui-dialog");
				if($dialog.length)
					dialogTop = $dialog.offset().top;
				$menu
					.css({
                		"overflow-y"			: "auto"
                		, "overflow-x"			: "hidden"
                		, "position"			: "absolute"
                		, "z-index" 			: 5 
						, "background"			: "#fff"
						, "border"				: "1px solid #ccc"
						, "border-top-color"	: "#d9d9d9"
						, "box-shadow"			: "0 2px 4px rgba(0,0,0,0.2)"
						, "cursor"				: "pointer"
						, "max-height"			: "300px"
						, "top"					: $this.offset().top + $this.outerHeight() - dialogTop
						, "width"				: $this.outerWidth()
					});

 				jQuery(".ui-menu-item", $menu).css({
                	"padding"				: "0 10px"
                	, "color"				: "#222"
                });
			}
			, search : function(event, ui) {
				if (event !== undefined && (
						event.keyCode == jQuery.ui.keyCode.PAGE_UP
						|| event.keyCode == jQuery.ui.keyCode.PAGE_DOWN
						|| event.keyCode == jQuery.ui.keyCode.UP
						|| event.keyCode == jQuery.ui.keyCode.DOWN
						|| event.keyCode == jQuery.ui.keyCode.HOME
						|| event.keyCode == jQuery.ui.keyCode.END
					)) {
					//jQuery(this).autocomplete('search');
					//return false;
				}
			}
		}).keydown(function (event) {
			if (event !== undefined && (
					event.keyCode !== jQuery.ui.keyCode.PAGE_UP
					&& event.keyCode !== jQuery.ui.keyCode.PAGE_DOWN
					&& event.keyCode !== jQuery.ui.keyCode.UP
					&& event.keyCode !== jQuery.ui.keyCode.DOWN
					&& event.keyCode !== jQuery.ui.keyCode.HOME
					&& event.keyCode !== jQuery.ui.keyCode.END
					&& event.keyCode !== jQuery.ui.keyCode.ENTER
					&& event.keyCode !== jQuery.ui.keyCode.ESCAPE
					&& event.keyCode !== jQuery.ui.keyCode.LEFT
					&& event.keyCode !== jQuery.ui.keyCode.RIGHT
					&& event.keyCode !== jQuery.ui.keyCode.TAB
				)) {
				}
		}).focus(function(event) {
		 	if(!jQuery(this).autocomplete("widget").menu().is(":visible")) {
				//autocomp_fullsearch = true;
				jQuery.fn.escapeGet(__id + "_label").autocomplete("search");
				jQuery(".ui-helper-hidden-accessible").hide();
			}
		}).blur(function(event) {
			if (event.relatedTarget) {
				var related = event.relatedTarget.id.replace("_label", "");
				if (ff.ffField.actex.exists(related)) 
					ff.ffField.actex.getInstance(related).has_focus = true; // verificare se dopo il passaggio a blur ha ancora senso
			}

			if(!multi) {
				$this = jQuery(this);
				
				var itm_found = null;
				var tmp_compare = $this.val().toLowerCase();

				if (/*ui.item === null &&*/ tmp_compare !== "") {
					// find right value
					that.data.each(function (a, tmp_data){ 
						if ((father_value == null && that.father == null) || tmp_data[0] == father_value) {
							var opt_value = tmp_data[1];
							var opt_text = tmp_data[2];  

							if (opt_text.toLowerCase() === tmp_compare) {
								itm_found = {"desc" : opt_text, "value" : opt_value};
								return true;
							}
						}
					});

					if (itm_found !== null) {
						$this.val(itm_found["desc"]);
						that.change(false, itm_found["value"]);
					} else if (that.value_ori["desc"]) {
						$this.val(that.value_ori["desc"]);
						that.change(false, that.value_ori["value"]);
					} else {
						$this.val("");
						tmp_compare = "";
					}
				}

				if (tmp_compare === "") {
					if (that.options.select_one) {
						that.change(false, that.options.select_one_val);
					} else if (that.options.select_noone) {
						$this.val(that.options.select_noone_label);
						that.change(false, that.options.select_noone_val);
					} else {
						that.data.each(function (a, tmp_data){
							if ((father_value == null && that.father == null) || tmp_data[0] == father_value) {
								var opt_value = tmp_data[1];
								var opt_text = tmp_data[1];
								$this.val(opt_text);
								that.change(false, opt_value);
								return true;
							}
						});
					}
				}
			}
		}).data("ui-autocomplete")._renderItem = function (ul, item) {
	         return $("<li></li>")
	             .data("item.autocomplete", item)
	             .append('<a href="javascript:void(0);">' + (item.image ? '<img src="' + item.image + '" />' : "") + item.label + "</a>")
	             .appendTo(ul);
	    };
		
		/*if (!that.options.autocomplete.ajax) {
			jQuery.fn.escapeGet(__id + "_label")
				.css("margin-right", 0)
				.after(
					jQuery( "<a>" )
						.addClass( icons.caret )
						.css({"pointer-events" : "auto"})
						.click(function() {
							jQuery.fn.escapeGet(__id + "_label").focus();

							// Pass empty string as value to search for, displaying all results
							jQuery.fn.escapeGet(__id + "_label").autocomplete("search");
						})
				);
		}*/
	}

	function drawDialogButtons () {
		if (ff.ffPage.dialog.dialog_params.get("actex_dlg_" + __id)) {
			if (!buttons.get("add") || that.options.limit_select) {
				jQuery.fn.escapeGet("actex_" + __id + "_dialogaddlink").addClass("hidden");
			} else {
				jQuery.fn.escapeGet("actex_" + __id + "_dialogaddlink").removeClass("hidden");
			}
		}

		if (ff.ffPage.dialog.dialog_params.get("actex_dlg_edit_" + __id)) {
			if (!buttons.get("edit")) {
				jQuery.fn.escapeGet("actex_" + __id + "_dialogeditlink").addClass("hidden");
			} else if (that.options.select_one && that.options.select_one_val == ff.coalesce(that.value, "")) {
				jQuery.fn.escapeGet("actex_" + __id + "_dialogeditlink").addClass("hidden");
			} else if (that.options.select_noone && that.options.select_noone_val == ff.coalesce(that.value, "")) {
				jQuery.fn.escapeGet("actex_" + __id + "_dialogeditlink").addClass("hidden");
			} else {
				jQuery.fn.escapeGet("actex_" + __id + "_dialogeditlink").removeClass("hidden");
			}
		}

		if (ff.ffPage.dialog.dialog_params.get("actex_dlg_delete_" + __id)) {
			if (!buttons.get("delete")) {
				jQuery.fn.escapeGet("actex_" + __id + "_dialogdeletelink").addClass("hidden");
			} else if (that.options.select_one && that.options.select_one_val == ff.coalesce(that.value, "")) {
				jQuery.fn.escapeGet("actex_" + __id + "_dialogdeletelink").addClass("hidden");
			} else if (that.options.select_noone && that.options.select_noone_val == ff.coalesce(that.value, "")) {
				jQuery.fn.escapeGet("actex_" + __id + "_dialogdeletelink").addClass("hidden");
			} else {
				jQuery.fn.escapeGet("actex_" + __id + "_dialogdeletelink").removeClass("hidden");
			}
		}
	};

	function parseheader (value) {
		if (value === undefined)
			value = that.value;
		
		var buffer = '';
		if (that.options.control_type == "input") { 
			buffer = '<input id="' + __id + '" name="' + __id + '" class="'  + (that.options.class.length ? that.options.class + ' ' : '') + 'input" ';
			buffer += ' ' + that.options.properties + ' ';
		} else if (that.options.control_type == "label") {
			buffer = '<input type="hidden" id="' + __id + '" name="' + __id + '" value="' + value + '" />';
			buffer += '<span class="'  + (that.options.class.length ? that.options.class + ' ' : '') + 'label" ';
			buffer += ' ' + that.options.properties + ' >';
		} else if(that.options.control_type == "checkbox") {
			buffer = '<input type="hidden" id="' + __id + '" name="' + __id + '" value="' + value + '" />';
			buffer += '<div class="' /*+ (that.options.class.length ? that.options.class + ' ' : '')*/ + 'checkgroup draggable">';
		} else if (that.options.autocomplete.enable) {
			buffer = '<input id="' + __id + '_label" name="' + __id + '_label" class="'  + (that.options.class.length ? that.options.class + ' ' : '') + 'input" ';
			buffer += ' ' + that.options.properties + ' ';
			if (that.options.select_one) {
				buffer += 'placeholder="' + that.options.select_one_label + '" ';
			}
		} else {
			buffer = '<select';
			buffer += ' class="' + (that.options.class.length ? that.options.class + ' ' : '') + 'select"';
			buffer += ' name="' + __id + '" id="' + __id + '" ' + that.options.properties;
			buffer += ' onChange="ff.ffField.actex.change(\'' + __id +'\', ' + that.options.reset_childs + ');" >';

			if (that.options.select_one) {
				buffer += '<option value="' + that.options.select_one_val + '"';
				if (that.options.select_one_val == value)
					buffer += 'selected ';
				buffer += '>' + that.options.select_one_label + '</option>';
			}

			if (that.options.select_noone) {
				buffer += '<option value="' + that.options.select_noone_val + '"';
				if (that.options.select_noone_val == value)
					buffer += 'selected ';
				buffer += '>' + that.options.select_noone_label + '</option>';
			}
		}

		return buffer;
	};
	
	function constructor() { // NB: called below publics
		jQuery.fn.escapeGet("actex_" + __id + "_combo").click(function() {
			jQuery.fn.escapeGet(__id + "_label").trigger("focus");
			return false;
		});	

		jQuery.extend(true, that, ff.ffEvents());
	}
	
	constructor();
	return that;
}; // actex object end

var that = { /* publics */
__ff : true, /* used to recognize ff'objects*/

"init" : function (params) {
	if (!initialized) {
		initialized = true;
		if(params.innerURL)
			innerURL			= params.innerURL;
		
		icons					= params.icons;
		frameworkCSS			= params.frameworkCSS;

		loading_markup		= params.loading_markup || '<i>' + icons.loader + '</i>';

		/* inits*/
		ff.addEvent({"event_name" : "onClearField", "func_name" : that.onClearField});
		ff.pluginAddInit("ff.ajax", function () {
			ff.ajax.addEvent({"event_name" : "onUpdateField", "func_name" : that.onUpdateField});
		});
	}
},

"setLoadingMarkup" : function (markup) {
	loading_markup = markup;
	return that;
},

"getInstance" : function (id, avoid_undef) {
	var tmp = instances.get(id);
	if (tmp === undefined && !avoid_undef)
		throw "ff.ffField.actex - instance does not exists [" + id + "]";
	
	return tmp;
},
"debug" : function () {
	return instances;
},

"exists" : function (id) {
	if (instances.get(id) === undefined)
		return false
	else
		return true;
},

"resetCache" : function (id) {
	var inst = that.getInstance(id);
	var id_data_src = inst.getCacheDataSrc();
	
	if (!sources.isset(id_data_src))
		return;
			
	var father_value = inst.getFatherValue();
	sources.get(id_data_src).unset(father_value);
},

"factory" : function (params) {
	if (that.exists(params.id))
		return;

	var tmp = activecombo(params);
	instances.set(params.id, tmp);

	that.doEvent({
		"event_name"	: "factory",
		"event_params"	: [tmp]
	});
	
	return tmp;
},

"deleteCombo" : function (id) {
	var inst = that.getInstance(id);
	
	sources.unset(inst.getCacheDataSrc());

	instances.unset(id);
},

"change" : function (id, reset_childs) {
	return that.getInstance(id).change(reset_childs);
},

"update" : function (id, new_value, reset_childs) {
	that.getInstance(id).update(new_value, reset_childs);
},

"recalc" : function (id) {
	that.getInstance(id).recalc();
},

"del" : function (id, key) {
	that.getInstance(id).del(key);
},

"refill" : function (id, selected_value, father_value, force_refresh) {
	that.getInstance(id).refill(selected_value, father_value, force_refresh);
},

"onClearField" : function (component, field_id, field_data, inst_id) {
	if (field_data.widget !== "actex")
		return;

	if (that.exists(inst_id)) {
		ff.ffField.actex.deleteCombo(inst_id);
	}
},

"insertModeOn" : function (id, dialog) {
	var inst = that.getInstance(id);
	
	inst.insert_mode = true;
	
	if (dialog !== undefined) {
		inst.insert_mode = ff.ffPage.dialog.addEvent({
			"event_name" : "onClose"
			, "func_name" : function (dialog_id) {
				if (dialog_id === dialog) {
					that.insertModeOff(id);
				}
			}
		});
	}
},

"insertModeOff" : function (id) {
	var inst = that.getInstance(id);
	
	if (inst.insert_mode !== false && inst.insert_mode !== true) {
		inst.insert_mode.remove();
	}
	
	inst.insert_mode = false;
},

"onUpdateField" : function (component, key, field, retData) {
	if (field.widget != "actex")
		return;

	if (component !== undefined) {
		switch (ff.struct.get("comps").get(component).type) {
			case "ffDetails":
				var rows = parseInt(jQuery.fn.escapeGet(component + "_rows").val());
				for (var r = 0; r < rows; r++) {
					var tmp_id = component + "_recordset[" + r + "][" + key + "]";
					if (that.exists(tmp_id)) ajaxUpdate(tmp_id, retData);
				}
				break;

			default:
				if (that.exists(component + "_" + key)) ajaxUpdate(component + "_" + key, retData);
		} 
	} else {
		if (that.exists(key)) ajaxUpdate(key, retData);
	}
}

}; /* publics' end*/

/* privates */

function ajaxUpdate (id, retData) {
	var inst = that.getInstance(id);

	ajaxChainBlock(inst);
	inst.refill((inst.insert_mode ? retData.insert_id : undefined), undefined, true);
	
	if (inst.insert_mode !== false)
		that.insertModeOff(id);
};

function ajaxChainBlock (inst) {
	if (inst.childs.length) {
		inst.childs.each(function (a, child) {
			ff.ajax.chainupdate.updated.set(child);
			ajaxChainBlock(that.getInstance(child));
		});
	}
};

return that;

/* code's end.*/
})();
