/**
 * Forms Framework Javascript Handling Object
 *	activecombo fields' plugin namespace
 **/

ff.ffField.activecomboex = (function () {

/* privates */
var innerURL            = (window.location.pathname == "/" ? "" : window.location.pathname) + "/parsedata" /*+ window.location.search*/;  
var icons            	= {};

var instances            = ff.hash();

var sources                = ff.hash();
var controls_waiting    = ff.hash();

var initialized = false;

var activecombo = function(params) {
    /* privates */
    var __id = params.id;
    var filled = false;
    var first_fill = true;
    var buttons = ff.hash();
    buttons.set("add", true);
    buttons.set("delete", true);
    buttons.set("edit", true);
    
    var that = { /* publics */
        "father"                    : params.father
        , "childs"                  : params.childs
        , "data"                    : params.data
        , "value"                   : params.selected_value
        , "options" : {
            "name"                  : params.options.name
            , "service"             : params.options.service
            , "properties"          : params.options.properties
            , "select_one"          : params.options.select_one
            , "select_one_val"      : params.options.select_one_val
            , "select_one_label"    : params.options.select_one_label
            , "select_noone"        : params.options.select_noone
            , "select_noone_val"    : params.options.select_noone_val
            , "select_noone_label"  : params.options.select_noone_label
            , "data_src"            : params.options.data_src
            , "class"               : params.options.class
            , "limit_select"        : params.options.limit_select
            , "disabled"            : params.options.disabled
            , "control_type"        : params.options.control_type
            , "separator"           : params.options.separator
            , "addPlus"             : params.options.addPlus
            , "hideEmpty"           : params.options.hideEmpty
            , "use_cache"           : params.options.use_cache
            , "plugin"              : params.options.plugin
        }
        , "insert_mode"             : false
        
        , "getID" : function () {
            return __id;
        }

        , "getNode"  : function () {
            return jQuery.fn.escapeGet("activecomboex_" + __id).get(0);
        }
        , "getService" : function () {
            if (that.options.service !== null)
                return that.options.service;
            else
                return innerURL;
        }

        , "getCacheDataSrc" : function () {
            return (that.options.data_src 
            		? that.options.data_src 
            		: (that.options.service 
                        ? that.options.name
                        : (innerURL 
                            ? innerURL 
                            : __id
                        )
                    ));
        }
        
        , "getFather" : function() {
            if (that.father)
                return ff.ffField.activecomboex.getInstance(that.father);
            else
                return undefined;
        }
        
        , "getFatherValue" : function() {
            if (that.father)
                return ff.ffField.activecomboex.getInstance(that.father).value;
            else
                return null;
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
        , "getButton" : function(button) {
            return buttons.get(button);
        }
        
        , "change" : function (reset_childs, value) {
            var old_value = that.value;
            
            if (value === undefined)
                that.value = jQuery.fn.escapeGet(__id).val();
            else
                that.value = value;

            var res = that.doEvent({
                "event_name"    : "change",
                "event_params"  : [that, old_value]
            });
        
            if (res !== undefined && res[res.length - 1])
                return;

            res = ff.ffField.activecomboex.doEvent({
                "event_name"    : "change",
                "event_params"  : [that, old_value]
            });
    
            if (res !== undefined && res[res.length - 1])
                return;

            updatebt();

            if (that.childs.length) {
                that.childs.each(function (a, child) {
                    ff.ffField.activecomboex.getInstance(child).refill(reset_childs ? null : undefined);
                });
            }
        }
        
        , "resetChildsPreRedir" : function (value) {
            if (that.childs.length) {
                that.childs.each(function (a, child) {
                    var tmp = ff.ffField.activecomboex.getInstance(child);
                    tmp.value = (value === undefined ? null : value);
                    jQuery.fn.escapeGet(child).val((value === undefined ? "" : value));
                    tmp.resetChildsPreRedir(value);
                });
            }
        }

        , "update" : function (new_value, reset_childs) {
            var old_value = that.value;
            //var old_index = jQuery.fn.comboGetSelIndex(__id);

            if (new_value === undefined)
                new_value = old_value;

            //var new_index = jQuery.fn.comboGetIndexByVal(__id, new_value);

            //jQuery("#" + __id + " option:eq(0)").prop('selected', true);
            //jQuery("#" + __id).prop("selectedIndex");
            //jQuery("#" + __id + " option[value=3]").index("option");    

            if (jQuery.fn.comboGetIndexByVal(__id, new_value) < 0) {
                new_value = null;
                //new_index = 0;
            } else {
                jQuery.fn.escapeGet(__id).val(new_value);
            }

            that.value = new_value;

            /*if (new_index !== old_index) {
                jQuery.fn.escapeGet(__id).val(new_value);
            }*/

            if (that.value != old_value) {
                var res = that.doEvent({
                    "event_name"    : "updatebt",
                    "event_params"  : [that, old_value]
                });

                if (res !== undefined && res[res.length - 1])
                    return;

                res = ff.ffField.activecomboex.doEvent({
                    "event_name"    : "updatebt",
                    "event_params"    : [that, old_value]
                });

                if (res !== undefined && res[res.length - 1])
                    return;
            }
            
            updatebt();
            
            if (first_fill) {
                first_fill = false;
                ff.doEvent({
                    "event_name" : "initIFElement"
                    , "event_params" : [__id, "activecomboex"]
                });
            }
            
            if (that.childs.length) {
                that.childs.each(function (a, child) {
                    child = ff.ffField.activecomboex.getInstance(child);
                    if (!child.isFilled() || that.value != old_value/*new_index !== old_index*/ || reset_childs)
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

        , "recalc" : function (countCheck, separator) {
            var hidden = jQuery.fn.escapeGet(__id).get(0);
            var tmp = "";
            var element = null;

			jQuery(".checkgroup .line", that.getNode()).each(function() {
				if (jQuery("input", this).attr("checked")) {
					if (tmp.length)
						tmp = tmp + separator;

					tmp = tmp + jQuery("input", this).val();
				}
			});

            hidden.value = tmp;
        }

        , "refill" : function (new_value, father_value, force_refresh) {
            var node = that.getNode();

            if (new_value === undefined)
                new_value = that.value;

            father_value = that.getFatherValue();

            /* like activecombo, preloaded in page*/
            if (that.options.data_src == "" && that.options.service === null) {
                var found_value = false;
                var count_value = 0;
                var buffer = parseheader();

                if(that.options.control_type == "input") {
                    that.data.each(function (a, tmp_data){
                        if ((father_value == null && that.father == null) || tmp_data[0] == father_value) {
                            if (tmp_data[0] == new_value)
                                found_value = true;

                            if (
                                    !that.options.limit_select
                                    || (that.options.limit_select && tmp_data[0] == new_value)
                                ) {
                                buffer += ' value="' + tmp_data[0] + '" ';
                                return true;
                            }
                            
                            count_value++;
                        }
                    });
                    buffer += ' />';
                    node.innerHTML = buffer;
                } else if(that.options.control_type == "label") {
                    that.data.each(function (a, tmp_data){
                        if ((father_value == null && that.father == null) || tmp_data[0] == father_value) {
                            if (tmp_data[0] == new_value)
                                found_value = true;

                            if (
                                    !that.options.limit_select
                                    || (that.options.limit_select && tmp_data[1] == new_value)
                                ) {
                                buffer += tmp_data[1];
                                return true;
                            }
                            
                            count_value++;
                        }
                    });
                    buffer += '</span>';
                    node.innerHTML = buffer;
                } else if(that.options.control_type == "checkbox") {
                    var separator = that.options.separator;
                    if(!separator.length)
                        separator = ",";

                    var arrSelectedValue = new_value.split(separator);
                    that.data.each(function (a, tmp_data){
                        if ((father_value == null && that.father == null) || tmp_data[0] == father_value) {
                            if (tmp_data[1] == new_value)
                                found_value = true;

                            if (
                                    !that.options.limit_select
                                    || (that.options.limit_select && tmp_data[1] == new_value)
                                ) {
                                buffer += '<div class="line"><label for="' + __id + '_' + a + '"><input type="checkbox" id="' + __id + '_' + a + '" value="' + tmp_data[1] + '" ';
                                for (var x = 0; x < arrSelectedValue.length; x++) {
                                    if (tmp_data[1] == arrSelectedValue[x])
                                        buffer += 'checked="checked" ';
                                }

                                buffer += ' onChange="';
                                buffer += 'ff.ffField.activecomboex.recalc(\'' + __id + '\',\'' + that.data.length + '\',\'' + separator + '\');" ';
                                buffer += ' />' + tmp_data[2] + '</label></div>';
                                
                                count_value++;
                            }
                        }
                    }); 
                    buffer += '</div>';
                    node.innerHTML = buffer; 
                } else {
                    if(that.data) {
                        that.data.each(function (a, tmp_data){
                            if ((father_value == null && that.father == null) || tmp_data[0] == father_value) {
                                if (
                                        !that.options.limit_select
                                        || (that.options.limit_select && tmp_data[1] == new_value)
                                    ) {
                                    buffer += '<option value="' + tmp_data[1] + '" ';
                                    if (tmp_data[1] == new_value) {
                                        buffer += 'selected ';
                                        found_value = true;
                                    }
                                    buffer += '>' + tmp_data[2] + '</option>';
                                    
                                    count_value++;
                                }
                            }
                        });
                        buffer += '</select>';
                        if(that.options.addPlus) {
                            buffer += '<a class="' + icons.plus + '" href="javascript:void(0);" onClick="jQuery(this).prev(\'select\').children(\'option:selected\').val(parseInt(jQuery(this).prev(\'select\').children(\'option:selected\').val()) + 1); jQuery(this).prev(\'select\').children(\'option:selected\').text(jQuery(this).prev(\'select\').children(\'option:selected\').val());"></a><a class="' + icons.minus + '" href="javascript:void(0);" onClick="if(parseInt(jQuery(this).prev().prev(\'select\').children(\'option:selected\').val()) >= 2) { jQuery(this).prev().prev(\'select\').children(\'option:selected\').val(parseInt(jQuery(this).prev().prev(\'select\').children(\'option:selected\').val()) - 1); jQuery(this).prev().prev(\'select\').children(\'option:selected\').text(jQuery(this).prev().prev(\'select\').children(\'option:selected\').val()); }"></a>';
                        }
                        node.innerHTML = buffer; 
                    }
                }
                
                filled = true;

                if (count_value) {
					if(that.options.hideEmpty) {
	                    if(that.options.hideEmpty === true)
	                        jQuery(node).show();
	                    else if(that.options.hideEmpty == "all")
	                        jQuery(node).closest("DIV.activecomboex").show();
	                    else 
	                        jQuery(node).closest(that.options.hideEmpty).show();
	                } 
				} else {
					if(that.options.hideEmpty) {
	                    if(that.options.hideEmpty === true)
	                        jQuery(node).hide();
	                    else if(that.options.hideEmpty == "all")
	                        jQuery(node).closest("DIV.activecomboex").hide();
	                    else 
	                        jQuery(node).closest(that.options.hideEmpty).hide();
	                }                     
				}
				
 				if (!found_value)
                    new_value = null;

                that.update(new_value);

                that.doEvent({
                    "event_name"    : "refill",
                    "event_params"    : [node]
                });
                
                ff.ffField.activecomboex.doEvent({
                    "event_name"    : "refill",
                    "event_params"    : [__id, node]
                });
    
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
                if (new_value)
                    str_data += "&sel_val=" + escape(new_value);

                /* get ancestor's data */
                var ancest_data = "";
                var tmp_father = that.getFather()
                while (tmp_father) {
                    ancest_data = "&ffActex_parent_data[" + ff.doubleEncodeURIComponent(tmp_father.getID()) + "]=" + ff.encodeURIComponent(tmp_father.value) + ancest_data;
                    tmp_father = tmp_father.getFather();
                }
                str_data = str_data + ancest_data;

                delayed_request(str_data, new_value, force_refresh);
            }
        }

        , "child_error_display" : function () {
            var node = that.getNode();
            if (node) {
                node.innerHTML = 'Impossibile connettersi con il server, riprovare più tardi.';
                if(!that.options.hideEmpty || that.options.hideEmpty === true)
                    jQuery(node).show();
                else if(that.options.hideEmpty == "all")
                    jQuery(node).closest("DIV.activecomboex").show();
                else 
                    jQuery(node).closest(that.options.hideEmpty).show();

                if (that.childs.length) {
                    that.childs.each(function (a, child) {
                        ff.ffField.activecomboex.getInstance(child).child_error_display();
                    });
                }
            }
        }

        , "async_refill" : function (retData, selected_value) {
            var node = that.getNode();

            var loadPlugin = function(params) {
                /* prototype
                    plugin {
                        name : [plugin func name]
                        path : [plutin relative path from /library/plugins]  no
                        css : [css file name] 								no
                        js : [js file name] 								no
                        params : [object JSON of params]
                    }

                */
                if(params) {
                    var setPlugin = function(params) {
                        ff.load("jquery.plugins." + params["name"], function() {
                            try {
                                eval("jQuery.fn.escapeGet('" + __id +"')." + params["name"] + "(" + JSON.stringify(params["params"]) + ");");
                            } catch(e) {
                                console.log(e + " " + "jQuery.fn.escapeGet('" + __id +"')." + params["name"] + "(" + JSON.stringify(params["params"]) + ");");
                            }                            
                        });
                    }
                    setPlugin(params);
/*
                    if(params["css"]) {
                        ff.injectCSS(params["css"], "/themes/library/plugins/jquery." + params["path"] + "/jquery." + params["css"] + ".css", function() {    
                            setPlugin(params);
                        });
                    } else {
                        setPlugin(params);
                    }
*/
                }
            }

            var buffer = "";

            if (retData === null) {
                that.child_error_display();
                return;
            }

            buffer = parseheader();

            var opt_value = "";
            var opt_text = "";
            var found_value = false;

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
                    found_value |= (opt_value == selected_value);
                    if (
                            !that.options.limit_select
                            || (that.options.limit_select && opt_value == selected_value)
                        ) {
                        buffer += '<div class="line"><label for="' + __id + '_' + i + '"><input type ="checkbox" id="' + __id + '_' + i + '" value="' + opt_value  + '" ';
                        for (var x = 0; x < arrSelectedValue.length; x++) {
                            if (opt_value == arrSelectedValue[x])
                                buffer += 'checked="checked" ';
                        }

                        buffer += ' onChange="';
                        buffer += 'ff.ffField.activecomboex.recalc(\'' + __id + '\',\'' + retData.length + '\',\'' + separator + '\');" ';
                        buffer += ' />' + opt_text + '</label></div>'; 
                    }
                }
                buffer += '</div>'; 
                node.innerHTML = buffer; 
            } else {
                var bufferAttr = "";
                var bufferGroup = ff.hash();
                var bufferOption = "";

                for (var i = 0; i < retData.length; i++) {
                    found_value |= (retData[i].value == selected_value);

                    if (!that.options.limit_select || (that.options.limit_select && retData[i].value == selected_value)) {
                        bufferAttr = "";
                        if(retData[i].attr) {
                            for(var x in retData[i].attr) {
                                bufferAttr += " " + x + "=" + '"' + retData[i].attr[x] + '" ';
                            }
                        }

                        bufferOption = '<option '
                            + bufferAttr
                            + 'value="' + retData[i].value + '" ' 
                            + '>' + retData[i].desc + '</option>';

                        if(retData[i]["group"]) {
                            var tmp = (bufferGroup.get(retData[i]["group"]) === undefined ? "" : bufferGroup.get(retData[i]["group"])) + bufferOption;
                            bufferGroup.set(retData[i]["group"], tmp);
                        } else {
                            buffer += bufferOption;
                        }
                    }
                }

                bufferGroup.each(function (key, value) {
                    buffer += '<optgroup label="' + key + '">' + value + '</optgroup>';    
                });

                buffer += '</select>';
                if(that.options.addPlus) {
                    buffer += '<a class="' + icons.plus + '" href="javascript:void(0);" onClick="jQuery(this).prev(\'select\').children(\'option:selected\').val(parseInt(jQuery(this).prev(\'select\').children(\'option:selected\').val()) + 1); jQuery(this).prev(\'select\').children(\'option:selected\').text(jQuery(this).prev(\'select\').children(\'option:selected\').val());"></a><a class="' + icons.minus + '" href="javascript:void(0);" onClick="if(parseInt(jQuery(this).prev().prev(\'select\').children(\'option:selected\').val()) >= 2) { jQuery(this).prev().prev(\'select\').children(\'option:selected\').val(parseInt(jQuery(this).prev().prev(\'select\').children(\'option:selected\').val()) - 1); jQuery(this).prev().prev(\'select\').children(\'option:selected\').text(jQuery(this).prev().prev(\'select\').children(\'option:selected\').val()); }"></a>';
                }
                node.innerHTML = buffer;

                loadPlugin(that.options.plugin);
            }

            filled = true;

            if(that.options.hideEmpty && (!retData || !retData.length)) {
                if(that.options.hideEmpty === true)
                    jQuery(node).hide();
                else if(that.options.hideEmpty == "all")
                    jQuery(node).closest("DIV.activecomboex").hide();
                else 
                    jQuery(node).closest(that.options.hideEmpty).hide();
            }  

            that.update(selected_value);

            parent_enable();

            ff.ajax.unblockUI();

            that.doEvent({
                "event_name"    : "refill",
                "event_params"  : [node]
            });
                
            ff.ffField.activecomboex.doEvent({
                "event_name"    : "refill",
                "event_params"  : [__id, node]
            });
    }

    }; /* public's end */
    
    function delayed_request (str_data, selected_value, force_refresh) {
        ff.ajax.blockUI();

        if(that.options.use_cache && !force_refresh) {
            var id_data_src = that.getCacheDataSrc();
            var father_value = that.getFatherValue();

            if (sources.isset(id_data_src) && sources.get(id_data_src).isset(father_value)) {
                if (sources.get(id_data_src).get(father_value) === true) {
                    controls_waiting.set(__id, selected_value);
                    return;
                }

                that.async_refill(sources.get(id_data_src).get(father_value), selected_value);
                return;
            }

            if (!sources.isset(id_data_src))
                sources.set(id_data_src, ff.hash());

            sources.get(id_data_src).set(father_value, true);
        }

        var dataType = (ff.origin != ff.httpGetOrigin() ? "jsonp json" : "json");
        var mydata = {
            "selected_value" : selected_value
        };

        jQuery.ajax({
              url   	: ff.fixPath(that.getService())
            , async     : true
            , data      : str_data
            , type      : "GET"
            , dataType  : dataType
            , jsonp     : (dataType == "jsonp json" ? "XHR_JSONP" : undefined)
            , success   : async_success
            , error     : async_error
            , mydata    : mydata
        });
    };
    
    function async_error() {
        that.child_error_display();
        ff.ajax.unblockUI();
    };

    function async_success (retData, textStatus) {
        var selected_value = this.mydata.selected_value;
        var id_data_src = that.getCacheDataSrc();
        var id_source = (that.options.data_src
            ? id_data_src
            : ""
        );        

        if (!(retData.widget && retData.widget.actex && retData.widget.actex["D" + id_source]))
            return async_error();
            
        var father_value = that.getFatherValue();           
        retData = father_value ? retData.widget.actex["D" + id_source]["F" + father_value] : retData.widget.actex["D" + id_source];

        that.async_refill(retData, selected_value);

        if (that.options.use_cache) {
            sources.get(id_data_src).set(father_value, retData);
            controls_waiting.each(function (k, v) {
                var tmp = ff.ffField.activecomboex.getInstance(k);
                if (tmp.getCacheDataSrc() === id_data_src && tmp.getFatherValue() === father_value) {
                    tmp.async_refill(retData, v);
                    controls_waiting.unset(k);
                }
            });
       }
    };

    function parent_enable (inst) {
		if (inst === undefined)
			inst = that;
		
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
				
				var res = ff.ffField.activecomboex.doEvent({
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
        
        var node = inst.getNode();

        if (node && node.firstChild)
            node.firstChild.disabled = true;

        if (moveup && inst.father) {
            cascade_disable(true, inst.getFather());
        } else if (!moveup && inst.childs.length) {
            for (var a = 0; a < inst.childs.length; a++) {
                cascade_disable(false, ff.ffField.activecomboex.getInstance(inst.childs[a]));
            }
        }
    };

    function display_loading () {
        filled = false;
        var node = jQuery.fn.escapeGet("activecomboex_" + __id).get(0);
        var buffer = "";
        if(that.value != "") {
            buffer = '<input type="hidden" id="' + __id + '" name="' + __id + '" value="' + that.value + '" />';
        }
        node.innerHTML = '<span class="activecomboex">' + icons.loader + '&nbsp;Loading...</span>' + buffer;
        if(!that.options.hideEmpty || that.options.hideEmpty === true)
            jQuery(node).show();
        else if(that.options.hideEmpty == "all")
            jQuery(node).closest("DIV.activecomboex").show();
        else 
            jQuery(node).closest(that.options.hideEmpty).show();
    };

    function updatebt () {
        if (!jQuery.fn.escapeGet("activecomboex_" + __id + "_dialogaddlink").length)
            return;

        var res = that.doEvent({
            "event_name"    : "updatebt",
            "event_params"  : [that]
        });
        
        if (res !== undefined && res[res.length - 1])
            return;

        res = ff.ffField.activecomboex.doEvent({
            "event_name"    : "updatebt",
            "event_params"  : [that]
        });
            
        if (res !== undefined && res[res.length - 1])
            return;

        ff.pluginAddInit("ff.ffPage.dialog", function () {
            drawDialogButtons();
        });
    };

    function drawDialogButtons () {
        if (ff.ffPage.dialog.dialog_params.get("actex_dlg_" + __id)) {
            if (!buttons.get("add") || that.options.limit_select) {
                jQuery.fn.escapeGet("activecomboex_" + __id + "_dialogaddlink").hide();
            } else {
                jQuery.fn.escapeGet("activecomboex_" + __id + "_dialogaddlink").show();
            }
        }

        if (ff.ffPage.dialog.dialog_params.get("actex_dlg_edit_" + __id)) {
            if (!buttons.get("edit")) {
                jQuery.fn.escapeGet("activecomboex_" + __id + "_dialogeditlink").hide();
            } else if (that.options.select_one && that.options.select_one_val == ff.coalesce(that.value, "")) {
                jQuery.fn.escapeGet("activecomboex_" + __id + "_dialogeditlink").hide();
            } else if (that.options.select_noone && that.options.select_noone_val == ff.coalesce(that.value, "")) {
                jQuery.fn.escapeGet("activecomboex_" + __id + "_dialogeditlink").hide();
            } else {
                jQuery.fn.escapeGet("activecomboex_" + __id + "_dialogeditlink").show();
            }
        }

        if (ff.ffPage.dialog.dialog_params.get("actex_dlg_delete_" + __id)) { 
            if (!buttons.get("delete")) {
                jQuery.fn.escapeGet("activecomboex_" + __id + "_dialogdeletelink").hide();
            } else if (that.options.select_one && that.options.select_one_val == ff.coalesce(that.value, "")) {
                jQuery.fn.escapeGet("activecomboex_" + __id + "_dialogdeletelink").hide();
            } else if (that.options.select_noone && that.options.select_noone_val == ff.coalesce(that.value, "")) {
                jQuery.fn.escapeGet("activecomboex_" + __id + "_dialogdeletelink").hide();
            } else {
                jQuery.fn.escapeGet("activecomboex_" + __id + "_dialogdeletelink").show();
            }
        }
    };

    function parseheader () {
        var buffer = '';
        if (that.options.control_type == "input") { 
            buffer = '<input id="' + __id + '" name="' + __id + '" class="'  + (that.options.class.length ? that.options.class + ' ' : '') + 'input" ';
            buffer += ' ' + that.options.properties + ' ';
        } else if (that.options.control_type == "label") {
            buffer = '<input type="hidden" id="' + __id + '" name="' + __id + '" value="' + that.value + '" />';
            buffer += '<span class="readonly" ';
            buffer += ' ' + that.options.properties + ' >';
        } else if(that.options.control_type == "checkbox") {
            buffer = '<input type="hidden" id="' + __id + '" name="' + __id + '" value="' + that.value + '" />';
            buffer += '<div class="checkgroup draggable">';
        } else {
            buffer = '<select';
            buffer += ' class="' + (that.options.class.length ? that.options.class + ' ' : '') + 'select"';
            buffer += ' name="' + __id + '" id="' + __id + '" ' + that.options.properties;
            buffer += ' onChange="ff.ffField.activecomboex.change(\'' + __id +'\');" >';

            if (that.options.select_one) {
                buffer += '<option value="' + that.options.select_one_val + '"';
                if (that.options.select_one_val == that.value)
                    buffer += 'selected ';
                buffer += '>' + that.options.select_one_label + '</option>';
            }

            if (that.options.select_noone) {
                buffer += '<option value="' + that.options.select_noone_val + '"';
                if (that.options.select_noone_val == that.value)
                    buffer += 'selected ';
                buffer += '>' + that.options.select_noone_label + '</option>';
            }
        }

        return buffer;
    };

    /* constructor */
    
    return that;
}; // activecomboex object end

var that = { /* publics */
__ff : true, /* used to recognize ff'objects*/

"init" : function (params) {
    if (!initialized) {
        initialized = true;
		if(params.innerURL)
        	innerURL            = params.innerURL;

        icons        		= params.icons;
			
        /* inits*/
        ff.addEvent({"event_name" : "onClearField", "func_name" : that.onClearField});
        ff.pluginAddInit("ff.ajax", function () {
            ff.ajax.addEvent({"event_name" : "onUpdateField", "func_name" : that.onUpdateField});
        });
    }
},

"getInstance" : function (id) {
    var tmp = instances.get(id);
    if (tmp === undefined)
        throw "ff.ffField.activecomboex - instance does not exists [" + id + "]";
    
    return tmp;
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
    if (!that.exists(params.id)) { // prima era 1 ||
        var tmp = activecombo(params);
        jQuery.extend(true, tmp, ff.ffEvents());
        instances.set(params.id, tmp);

        that.doEvent({
            "event_name"    : "factory",
            "event_params"  : [tmp]
        });
        
        return tmp;
    }
},

"deleteCombo" : function (id) {  
    var inst = that.getInstance(id);
    sources.unset(inst.getCacheDataSrc());
    instances.unset(id);
},

"change" : function (id, reset_childs) {
    that.getInstance(id).change(reset_childs);
},

"update" : function (id, new_value, reset_childs) {
    that.getInstance(id).update(new_value, reset_childs);
},

"recalc" : function (id, countCheck, separator) {
    that.getInstance(id).recalc(countCheck, separator);
},

"refill" : function (id, selected_value, father_value, force_refresh) {
    that.getInstance(id).refill(selected_value, father_value, force_refresh);
},

"onClearField" : function (component, key, field) {
    /*ADDED-ATS*/
    /***********/
    if (typeof key !== 'undefined'){   
    /***********/
	if (field.widget == "activecomboex") {
		if (component !== undefined) {
			switch (ff.struct.get("comps").get(component).type) {
				case "ffDetails":
					if (key.indexOf("_recordset[") >= 0) {
						ff.ffField.activecomboex.deleteCombo(key);
					} else {
						var rows = parseInt(jQuery.fn.escapeGet(component + "_rows").val());
						for (var i = 0; i < rows; i++) {
							ff.ffField.activecomboex.deleteCombo(component + "_recordset[" + i + "][" + key + "]");
						}
					}
					break;

				default:
					ff.ffField.activecomboex.deleteCombo(component + "_" + key);
			}
		} else {
			ff.ffField.activecomboex.deleteCombo(key);
		}
	}
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
    if (field.widget != "activecomboex")
        return;

    if (component !== undefined) {
        switch (ff.struct.get("comps").get(component).type) {
            case "ffDetails":
                var rows = parseInt(jQuery.fn.escapeGet(component + "_rows").val());
                for (var r = 0; r < rows; r++) {
                    ajaxUpdate(component + "_recordset[" + r + "][" + key + "]", retData);
                }
                break;

            default:
                ajaxUpdate(component + "_" + key, retData);
        } 
    } else {
        ajaxUpdate(key, retData);
    }
},

/* Retrocompatibility, don't use it */
"addCombo" : function (params) {
    var tmp = ff.ffField.activecomboex.factory({
        "id"            : params.id
        , "father"      : params.father
        , "childs"      : params.attr.child
        , "data"        : params.data
        , "selected_value" : params.selected_value 
        , "options"     : {
            "name"                      : params.attr.name
            , "service"                 : params.attr.service
            , "properties"              : params.attr.onchange
            , "select_one"              : params.attr.select_one
            , "select_one_val"          : params.attr.select_one_val
            , "select_one_label"        : params.attr.select_one_label
            , "select_noone"            : params.attr.select_noone
            , "select_noone_val"        : params.attr.select_noone_val
            , "select_noone_label"      : params.attr.select_noone_label
            , "data_src"                : params.attr.data_src
            , "class"                   : params.attr.class
            , "limit_select"            : params.attr.limit_select
            , "disabled"                : params.attr.disabled
            , "control_type"            : params.attr.control_type
            , "separator"               : params.attr.separator
            , "addPlus"                 : params.attr.addPlus
            , "hideEmpty"               : params.attr.hideEmpty
            , "use_cache"               : params.attr.use_cache || true
            , "plugin"                  : params.attr.plugin
        }
    });
    
    if (params.attr.onUpdateBT) {
        tmp.addEvent({
            "event_name"    : "updatebt"
            , "func_name"   : params.attr.onUpdateBT
        });
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
