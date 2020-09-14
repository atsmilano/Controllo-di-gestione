/**
 * Forms Framework Javascript Handling Object
 *    dialog page' plugin namespace
 */
ff.ffPage.dialog = (function () {

// inits
var firstDialog = null;
var unique = null;
//overflow manage

ff.pluginAddInit("ff.ajax", function () {
	ff.ajax.addEvent({
		"event_name" : "onUpdateContent"
		, "func_name" : function (params, data, injectid) {
			if (params.ctx && ff.ffPage.dialog.exist(params.ctx) && params.component === undefined) {
				if (!ff.ffPage.dialog.get(params.ctx).instance) 
					ff.ffPage.dialog.makeInstance(params.ctx);

				ff.ffPage.dialog.removeDlgBt(params.ctx, data);
			}
		}
	});
	ff.ajax.addEvent({
		"event_name" : "onRedirect"
		, "func_name" : function (url, data, mydata, params) {
			if (
				(params.ctx && ff.ffPage.dialog.get(params.ctx) && ff.ffPage.dialog.getInstance(params.ctx))
				&& (params.doredirects || data["doredirects"] !== undefined)
			) {
				ff.ffPage.dialog.getInstance(params.ctx).dialog("close"); 
			}
		}
	});
	ff.ajax.addEvent({
		"event_name" : "ctxInitDone"
		, "func_name" : function (id) {
			if (dialogs.get(id) && dialogs.get(id).waiting) {
				ff.ajax.unblockUI();
				ff.ffPage.dialog.refresh(id, true);
				return true;
			}
		}
	});
}, "a65c581e-64d9-473d-a166-c1848cas23cb");



/* privates */
var dialogs		= ff.hash();
/*var inits_by_dlg = ff.hash();
var inits_by_wdg = ff.hash();

function initsReset(id) {
	inits_by_dlg.set(id, ff.hash());
	ff.ffPage.dialog.needInit(id, false);
}*/

var that = { /* publics */
__ff : true, /* used to recognize ff'objects */

"dialog_params"        : ff.hash(),
/*"dialog_deps"        : ff.hash(),*/

"addDialog" : function (params) {
	/** mod di alex **/
	if(unique === null && params.unique) {
		unique = params.id;
	}
	unique = null;
	/** fine mod di alex **/

	if (that.dialog_params.isset(params.id))
		return;	
	
    that.dialog_params.set(params.id, {
        "class"			: (params.dialogClass ? " " + params.dialogClass : ""),
        "callback"		: params.callback,
        "url"			: params.url,
        "title"			: params.title,
        "width"			: params.width,
        "params"        : params.params || {},
        "doredirects"	: params.doredirects,
    });
	
	ff.ajax.ctxAdd(params.id, that, "dialog");

    that.doEvent({
        "event_name"    : "onAddDialog",
        "event_params"    : [params.id]
    });
},

"get" : function (id) {
    return dialogs.get(id);
},

"exist" : function (id) {
	return that.dialog_params.isset(id);
},

"getInstance" : function (id) {
    return dialogs.get(id).instance;
},

"replaceHTML" : function (id, data) {
	that.getInstance(id).html(data["html"]);
	ff.ffPage.dialog.makeDlgBt(id, data);
},

"param" : function (id, param, value) {
    if (value !== undefined)
        that.dialog_params.get(id)[param] = value;
    else
        return that.dialog_params.get(id)[param];
},
"getDimensions" : function(elem) {
	var width = elem.outerWidth();
	
	if(elem.get(0).scrollWidth > width)
		return "modal-lg";
},
"adjSize" : function (id) {
	if(id === undefined) {
		id = dialogs.keys[dialogs.keys.length - 1];
	}
	
	var instance = dialogs.get(id).instance;
	var widget = dialogs.get(id).widget;
	var width = dialogs.get(id).params.width;
	var widthClass = "";

	if(width) {
		if(parseInt(width) > 600)
			widthClass = "modal-lg";
		else if(parseInt(width) < 500)
			widthClass = "modal-sm";
	} else {
		widthClass = that.getDimensions(instance);	
	}
	


	if(widthClass)
		jQuery(".modal-dialog", widget).addClass(widthClass);
	
	var wh = jQuery(window).height();
	var height = jQuery(".modal-dialog", widget).height();

	//if(wh > (height * 2)) {
	//	jQuery(".modal-dialog", widget).css("margin-top", ((wh - height) / 2));
	//}
	return true;
},
"refresh" : function (id, show) {
	if(id === undefined) {
		id = dialogs.keys[dialogs.keys.length - 1];
	}

	var widget = dialogs.get(id).widget;
	
	if(!widget.hasClass("in")) {
		widget.addClass("in");
		//widget.modal({backdrop: false});
		widget.show();
 		
 		that.doEvent({
		    "event_name"    : "onDisplayedDialog",
		    "event_params"    : [id]
		});		
	}
	that.adjSize(id);

	return true;
},

"makeInstance" : function (id, data, title) {
	//overflow manage
	if (firstDialog === null) {
		jQuery("body").addClass("modal-open");
		firstDialog = id;
	}

	var tmp_params = that.dialog_params.get(id) || {};

	
	var tmp = '<div id="ffWidget_dialog_' + id + '" class="ff-modal modal fade" role="dialog">'
				 +  '<div class="modal-dialog' + (tmp_params["class"] || "") + '">'
				 +    '<div class="modal-content">'
				 +      '<div class="modal-header">'
				 +        '<button type="button" class="resize"><i class="fa fa-expand"></i></button>'
				 +        '<button type="button" class="close" data-dismiss="modal"><i class="fa fa-times"></i></button>'
				 +        '<div class="modal-title">' + (title || "") + '</div>'
				 +      '</div>'
				 +      '<div id="ffWidget_dialog_container_' + id + '" class="modal-body clearfix">' + (data || "") + '</div>'
				 +      '<div class="modal-footer"></div>'
				 +    '</div>'
				 +  '</div>'
				 + '</div>'; 				 
	
	jQuery("body").append(tmp);
	
	dialogs.get(id).instance = jQuery('#ffWidget_dialog_container_' + id);
	dialogs.get(id).widget = jQuery('#ffWidget_dialog_' + id);
	jQuery(".close", dialogs.get(id).widget).on('click', function () {
		that.onClose(id);
	});
	
	return dialogs.get(id).instance;
},

"doOpen" : function (id, url, title, preserveIstance, elemHighlight) {
//unique = null;
	var html = undefined;
	if(id.trim().substring(0,1) == "<") {
		html = id;
		id = new Date().getTime();
	}

	if(unique && dialogs.keys.length) {
		preserveIstance = true;
		if(!url)
			url = that.dialog_params.get(id)["url"];
		if(!title)
			title = that.dialog_params.get(id)["title"];

		id = unique;
	}
	if(that.dialog_params.get(id) !== undefined) {
        if (url !== undefined)
            that.dialog_params.get(id)["url"] = url;
		else
            url = that.dialog_params.get(id)["url"];

        if (title !== undefined && title.length > 0)
            that.dialog_params.get(id)["title"] = title;
		else
            title = that.dialog_params.get(id)["title"];
    }

	that.updateCursor(id, that.dialog_params.get(id)["url"]);

    if (dialogs.get(id) && dialogs.get(id).instance) {
	    var widget = dialogs.get(id).widget;

        if(dialogs.get(id).params.current_url != url && preserveIstance) {
			var breadCrumbs = dialogs.get(id).breadCrumbs;
        	var actionBack = false;
        	if(breadCrumbs.length > 0) {
        		for(var i=0; i< breadCrumbs.length; i++) {
        			if(breadCrumbs[i]["url"] == url) {
        				breadCrumbs = breadCrumbs.splice(i);
        				actionBack = true;
        				break;
					}
				}
        	}
        	if(!actionBack)
        		breadCrumbs.push({"title":  jQuery(".modal-title", widget).text(), "url" : dialogs.get(id).params.current_url});
				
            ff.ffPage.dialog.goToUrl(id, url);
        } else {
            //widget.modal("show");
            //widget.modal({backdrop: false});
            that.refresh(id);
        }
        return;
    }

 	dialogs.set(id, {
		"instance"	: null,
		"params"	: jQuery.extend(true, {}, that.dialog_params.get(id)), 
	    "elemHighlight" : elemHighlight,
		"breadCrumbs": []
	});	

	if(url) {
		ff.ajax.ctxGet(id).reset();
	    dialogs.get(id).params.current_url = dialogs.get(id).params.url;
	    
	    var evres = that.doEvent({"event_name": "doOpen", "event_params" : [that, id, url, title]});
	    if (evres !== true) {
	        ff.ajax.doRequest({
	            "url"			: that.parseUrl(id, dialogs.get(id).params.current_url),
	            "type"			: "GET",
	            "callback"		: that.onSuccess,
	            "customdata"	: {
	                "id" : id
					, "caller" : {
						"func" : ff.ffPage.dialog.doOpen
						, "args" : ff.argsAsArray(arguments)
					}
	            },
	            "injectid"		: dialogs.get(id).instance,
	            "ctx"			: id,
				"brandnew"		: true,
	            "doredirects"	: dialogs.get(id).params.doredirects
	        });
	    }
	} else {
		ff.ffPage.dialog.makeInstance(id, html || jQuery("#" + id).outerHTML(), title);
		that.refresh(id);
	}
},

"close" : function (id) {
	if(id === undefined) {
		id = dialogs.keys[dialogs.keys.length - 1];
	}

	that.onClose(id);
},

"onSuccess" : function (data, customdata) {
    var id = customdata.id;
	var instance = dialogs.get(id).instance;
	var widget = dialogs.get(id).widget;
    if (data === null) {
        if (dialogs.get(id).params.params && dialogs.get(id).params.params.persistent)
            dialogs.get(id).params.params.persistent = false;
        instance && dialogs.get(id).instance.dialog("close");
        return false;
    }
    
    //jQuery(widget).modal("show");
    
    if(dialogs.get(id).elemHighlight)
        jQuery(dialogs.get(id).elemHighlight).addClass("ff-modal-highlight");

    /**
     *    data.close
     *        true = chiude il dialog
     *        false = valorizza (o aggiorna) il dialog
     *
     *    data.refresh
     *        true = imposta il dialog per l'aggiornamento del chiamante su chiusura
     *
     *    data.html
     *        contenuto del dialog se aggiornato
     *
     *    data.url
     *        cambia l'url del dialog su redirect interno
     */

    if (customdata.callback)
        customdata.callback(id, data);

    if (data.callback)
        eval(data.callback);

    if (data["close"]) {
        that.close(id);
	} else if (data["url"]) {
		dialogs.get(id).params.current_url = data["url"];
		that.updateCursor(id, data["url"]);
	} else if (data["cursor_reload"] && data["cursor_reload"] === id) {
		ff.ffRecord.cursor.reload(id);		
    } else {
		that.makeDlgBt(id, data);

        if (data["html"]) {
			if (!ff.ajax.ctxGet(id).needInit() && dialogs.get(id) !== undefined ) {
				//jQuery(widget).modal({backdrop: false});
				that.refresh(id);
			}
        }

		if (!ff.ajax.ctxGet(id).needInit()) {
            ff.ffPage.dialog.refresh(id, true);
        } else {
			dialogs.get(id).waiting = true;
			ff.ajax.blockUI();
		}
    }
    return true;
},

"onClose" : function (id, hide) {
    if(dialogs.get(id).elemHighlight)
        jQuery(dialogs.get(id).elemHighlight).removeClass("ff-modal-highlight");
    
    if (dialogs.get(id).params.params && dialogs.get(id).params.params.persistent)
        return;

    if (dialogs.get(id).params.callback) {
        eval(dialogs.get(id).params.callback);
    }

    ff.struct.get("comps").each(function (componentid, component) {
        if (component.ctx === id) {
            ff.clearComponent(componentid);
        }
    });

    ff.struct.get("fields").each(function (key, field) {
        if (field.ctx !== undefined && field.ctx === id) {
            ff.doEvent({
                "event_name"    : "onClearField",
                "event_params"    : [undefined, key, field]
            });
            ff.struct.get("fields").unset(key);
        }
    });
	
    dialogs.get(id).widget.remove();
    dialogs.unset(id);

    if(ff.ajax.ctxGet(id))
		ff.ajax.ctxGet(id).reset();
	
	if (id === firstDialog) {
		jQuery("body").removeClass("modal-open");
		firstDialog = null;
	}

    that.doEvent({
        "event_name": "onClose"
        , "event_params" : [id]
    });
},

"doAction" : function (id, action, component, detailaction, action_param, addit_fields) {
    that.param(id, "lastaction", action);
    
    switch (action) {
        case "close":
        	that.close(id);
            break;
        default:
        	var widget = dialogs.get(id).widget;

			ff.ajax.ctxGet(id).reset();
            that.doEvent({"event_name": "doAction", "event_params" : [id, action, component, detailaction, action_param]});

            var fields = ff.getFields(widget, id);
            fields.push(
                {name: "frmAction", value: component + action}
            );
            if (detailaction) {
                fields.push(
                    {name: component + "detailaction", value: detailaction}
                );
            }

            if (action == "detail_delete") {
                fields.push(
                    {name: detailaction + "_delete_row", value: action_param}
                );
            }

            if(addit_fields) {
                for(var i in addit_fields) {
                    fields.push(addit_fields[i]);
                }
            }
            ff.ajax.doRequest({
                 "url"                : that.parseUrl(id, dialogs.get(id).params.current_url),
                 "type"                : "POST",
                 "fields"            : fields,
                 "callback"            : that.onSuccess,
                 "customdata"        : {
                    "id" : id
					, "caller" : {
						"func" : ff.ffPage.dialog.doAction
						, "args" : ff.argsAsArray(arguments)
					}
                 },
                 "injectid"            : dialogs.get(id).instance,
                 "ctx"				: id,
                 "doredirects"        : dialogs.get(id).params.doredirects
            });
            break;
    }
},

"goToUrl" : function (id, url) {
	ff.ajax.ctxGet(id).reset();
	
    dialogs.get(id).params.current_url = url;
	that.updateCursor(id, url);

    ff.ajax.doRequest({
         "url"                : that.parseUrl(id, dialogs.get(id).params.current_url),
         "type"                : "GET",
         "callback"            : that.onSuccess,
         "customdata"        : {
            "id" : id
			, "caller" : {
				"func" : ff.ffPage.dialog.goToUrl
				, "args" : ff.argsAsArray(arguments)
			}
         },
         "injectid"            : dialogs.get(id).instance,
         "ctx"				: id,
		 "brandnew"			: true,
         "doredirects"        : dialogs.get(id).params.doredirects
    });
},

"doRequest" : function (id, params) {
    /**
     * params
     *    url
     *    component
     *    section
     *    injectid
     *    action
     *    detailaction
     *    callback
     *    action_param
     *    fields            i campi da passare con la richiesta, se no vengono presi quelli di tutta la pagina
     */
    var widget = dialogs.get(id).widget;
    
	ff.ajax.ctxGet(id).reset();
	
	var fields = (params.fields === undefined ? jQuery(":input", widget).not("input:checkbox:not(:checked)").not("input:radio:not(:checked)") : params.fields);

    if (params.action) {
        fields.push(
            {"name": "frmAction", "value": params.action}
        );
    }
        
    if (params.detailaction) {
        fields.push(
            {"name": params.detailaction + "detailaction", "value": params.component}
        );
    }

    if (params.action_param !== undefined) {
        fields.push(
            {"name": params.component + "_delete_row", "value": params.action_param}
        );
    }

    var url = (params.url !== undefined ? params.url : null);

    if (!url && params.component && ff.struct.get("comps").get(params.component) !== undefined)
        url = ff.struct.get("comps").get(params.component).url;

    if (!url)
        url = dialogs.get(id).params.current_url;
        
    ff.ajax.doRequest({
         "url"                	: that.parseUrl(id, url),
         "component"        	: params.component,
         "section"            	: params.section,
         "fields"            	: fields,
         "callback"            	: that.onSuccess,
         "customdata"        	: {
            "id"            	: id
            , "callback"    	: params.callback
			, "caller" 			: {
				"func" 			: ff.ffPage.dialog.doRequest
				, "args" 		: ff.argsAsArray(arguments)
			}
         },
         "injectid"            	: params.injectid,
         "ctx"					: id,
         "chainupdate"        	: params.chainupdate, 
         "doredirects"        	: dialogs.get(id).params.doredirects
    });
},

"parseUrl" : function (id, url) {
    var parsedurl = url;

    /*if (parsedurl.indexOf('?') > -1) {
        if (parsedurl.substring(parsedurl.length - 1) != "&")
            parsedurl += "&";
        parsedurl += "XHR_THEME=dialog";
    } else
        parsedurl += "?XHR_THEME=dialog";*/

    var regTags = /\[\[([a-zA-Z0-9_\-\[\](?!\]))]+)\]\]/g;
    var ret;
    while ((ret = regTags.exec(url)) !== null) {
        var tmp = ret[1].replace(/\[/g, "\\[").replace(/\]/g, "\\]");  
        var encodeTmp = false;

        if(tmp.indexOf("_ENCODE") > 0) {
            tmp = tmp.replace("_ENCODE", "");
            encodeTmp = true;
        }

        if(tmp.indexOf("_TEXT") > 0) {
            if(jQuery("#" + tmp.replace("_TEXT", "")).is("select")) {
                parsedurl = parsedurl.replace(ret[0], (encodeTmp ? encodeURIComponent(jQuery("#" + tmp.replace("_TEXT", "") + " option:selected").text()) : jQuery("#" + tmp.replace("_TEXT", "") + " option:selected").text()), "g");                            
            } else {
                parsedurl = parsedurl.replace(ret[0], (encodeTmp ? encodeURIComponent(jQuery("#" + tmp.replace("_TEXT", "")).text()) : jQuery("#" + tmp.replace("_TEXT", "")).text()), "g");                
            }
        } else {
            parsedurl = parsedurl.replace(ret[0], (encodeTmp ? encodeURIComponent(jQuery("#" + tmp).val()) : jQuery("#" + tmp).val()), "g");    
        }
    }

    if (dialogs.get(id).params.params !== undefined)
    {

/*        for (i in dialogs.get(id).params.params) {
            parsedurl += "&" + i + "=" + jQuery("#" + dialogs.get(id).params.params[i]).val();
        }*/
    }
    return parsedurl;
},
"removeDlgBt" : function(id, data) {
	var instance = dialogs.get(id).instance;
	var widget = dialogs.get(id).widget;

	if(data["html"].indexOf("dialogSubTitleTab")) {
		jQuery(".modal-tabs", widget).remove();
	}
	jQuery(".modal-callout", widget).remove();
	//jQuery(".modal-breadcrumb", widget).remove();
	
	if(data["html"].indexOf("dialogActionsPanel top")) {
		jQuery(".dlgTopPanel", widget).remove();
	}

	if(data["html"].indexOf("dialogActionsPanel")) {
		jQuery(".dlgBottomPanel", widget).remove();
	}
},
"makeDlgBt" : function(id, data) {
	var instance = dialogs.get(id).instance;
	var widget = dialogs.get(id).widget;

	var countRecord = 0;
	var countGrid = 0;
	var countDetail = 0;
	if(data["html"]) {
		ff.struct.get("comps").each(function(key, value) {
			switch(value["type"]) {
				case "ffRecord":
					if (value["ctx"] === id)
						countRecord++;
					return;
				case "ffGrid":
					if (value["ctx"] === id)
						countGrid++;
					break;
				case "ffDetails":
					if (value["ctx"] === id)
						countDetail++;
				default:
			}
		});	
/*
		struct.each(function(key, value) {
			switch(value["type"]) {
				case "ffRecord":
					if(data["html"].indexOf('id="' + key + '"') >= 0)
						countRecord++;
					return;
				case "ffGrid":
					if(data["html"].indexOf('id="' + key + '"') >= 0)
						countGrid++;
					break;
				case "ffDetails":
					if(data["html"].indexOf('id="' + key + '"') >= 0)
						countDetail++;
				default:
			}
		});	
*/
	}
	if(countDetail && !countRecord)
		return;

	if(jQuery(".dialogTitle", instance).length) {
		jQuery(".modal-title").addClass(jQuery(".dialogTitle:first", instance).attr("class")).removeClass("dialogTitle");
/*		jQuery(".dialogTitle:first", instance).appendTo(jQuery(".modal-title", widget).empty()).attr("class", "dlgTitle");
*/		
		jQuery(".modal-title", widget).html(jQuery(".dialogTitle:first", instance).html());
		jQuery(".dialogTitle:first", instance).remove();
	}
	
	var calloutTargetClass = "modal-header";
	//tabs groups
	/*if(jQuery(".dialogSubTitleTab", instance).length) { //da eliminare
		var startSel = 0;
		var tabPane = {};
		jQuery(".dialogSubTitleTab", instance).replaceWith(function(i) { 
			jQuery(this).removeClass("dialogSubTitleTab");
			var activeClass = "";
			if(jQuery(this).hasClass("active")) {
				startSel = i;

				jQuery(this).removeClass("active");
			}
			var depClass = jQuery(this).attr("class").replace("dep-", "dlg-");
			var depId = "bs-" + depClass;

			tabPane[depClass] = true;
			//jQuery(this).parent().addClass("dlg-tab " + depClass); 
			jQuery(".dlg-tab." + depClass, instance);//.addClass("tabbable");

			return '<li class="dialogSubTitleTab"><a href="#' + depId + '" data-toggle="tab">' + jQuery(this).html() + '</a></li>';
		});

		jQuery(".dlg-tab", instance).wrapAll('<div class="tab-content" />');

		for(var depClass in tabPane) {
			var depId = "bs-" + depClass;
			jQuery("." + depClass, instance).wrapAll('<div class="tab-pane fade" id="' + depId + '" />')
		
		}

		jQuery("LI.dialogSubTitleTab:eq(" + startSel + ")", widget).addClass("active");
		jQuery(".tab-content .tab-pane:eq(" + startSel + ")", instance).addClass("active in");

		jQuery(".dialogSubTitleTab", instance).insertAfter(jQuery(".modal-header", widget)).removeClass("dialogSubTitleTab").wrapAll('<div class="modal-tabs" />').wrapAll('<ul class="nav nav-tabs" />');
		jQuery(".modal-header", widget).addClass("noborder");
		
		calloutTargetClass = "modal-tabs";
	} else*/ if(jQuery(".ffTab", instance).length) { 	//tabs responsive
		jQuery(".ffTab", instance).insertAfter(jQuery(".modal-header", widget)).wrapAll('<div class="modal-tabs" />');
		jQuery(".modal-header", widget).addClass("noborder");
		
		calloutTargetClass = "modal-tabs";
	}

	if(jQuery(".dialogSubTitle", instance).length) {
		jQuery(".dialogSubTitle", instance).insertAfter(jQuery("." + calloutTargetClass, widget)).removeClass("dialogSubTitle").wrapAll('<div class="modal-callout" />');
	}


	
    var skipForceBt = jQuery(".nav.nav-tabs > LI", widget).length;
	if(jQuery(".dialogActionsPanel.top", instance).length) {
        if(!skipForceBt)
		    jQuery(".dialogActionsPanel.top.force", instance).appendTo(jQuery(".modal-header", widget)).addClass("dlgTopPanel").removeClass("dialogActionsPanel top force");
		if(!countRecord && countGrid == 1)
			jQuery(".dialogActionsPanel.top:not(.force)", instance).appendTo(jQuery(".modal-header", widget)).addClass("dlgTopPanel").removeClass("dialogActionsPanel top");
	}

	if(jQuery(".dialogActionsPanel:not(.top)", instance).length) {
        if(!skipForceBt)
		    jQuery(".dialogActionsPanel.force", instance).appendTo(jQuery(".modal-footer", widget)).addClass("dlgBottomPanel").removeClass("dialogActionsPanel force");

		if(countRecord) {
			jQuery(".dialogActionsPanel:not(.force):last", instance).appendTo(jQuery(".modal-footer", widget)).addClass("dlgBottomPanel").removeClass("dialogActionsPanel");
		} else if(countGrid == 1) {
			jQuery(".dialogActionsPanel:not(.force)", instance).appendTo(jQuery(".modal-footer", widget)).addClass("dlgBottomPanel").removeClass("dialogActionsPanel");
		} else if(!countRecord && !countGrid) {
			jQuery(".dialogActionsPanel", instance).appendTo(jQuery(".modal-footer", widget)).addClass("dlgBottomPanel").removeClass("dialogActionsPanel");
		}
	}
	
	if(dialogs.get(id).breadCrumbs.length) {
		var breadCrumbs = "";
		for(var i=0; i<dialogs.get(id).breadCrumbs.length; i++) {
			var brdUrl = 'ff.ffPage.dialog.doOpen(\'' + id + '\', \'' + dialogs.get(id).breadCrumbs[i]["url"] + '\',\'' + dialogs.get(id).breadCrumbs[i]["title"] + '\', true);';
			breadCrumbs = breadCrumbs + '<li><a href="javascript:void(0);" onClick="' + brdUrl + '">' + dialogs.get(id).breadCrumbs[i]["title"] + '</a></li>';
		}
		if(breadCrumbs) { 
		//<a href="javascript:void(0);" onClick="' + brdUrl + '" class="dlgBrdBack"></a>
			if(!jQuery("ol.breadcrumb", widget).length) {
				var pageTitle = jQuery(".modal-title", widget).text();
				$(".modal-title", widget).contents().filter(function () {
				     return this.nodeType === 3; 
				}).remove();

				jQuery('<ol class="breadcrumb">' + breadCrumbs + '<li>' + pageTitle + '</li>' + '</ol>').appendTo(jQuery(".modal-title", widget));
			}
		}
	}
},
"updateCursor" : function (id, url) {
	var tmp = null;
	if (tmp = ff.getURLParameter("cursor[id]", url)) {
		that.dialog_params.get(id)["cursor"] = {
			"id" : tmp,
			"rrow" : ff.getURLParameter("cursor[rrow]", url),
			"rows" : ff.getURLParameter("cursor[rows]", url),
		};
	}
}

}; /* publics' end */

return that;

/* code's end. */
})();	
	