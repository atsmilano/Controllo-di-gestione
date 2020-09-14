/**
 * Forms Framework Javascript Handling Object
 *    dialog page' plugin namespace
 */
ff.pluginAddInit("jquery-ui", function () {
	jQuery.widget("ui.dialog", jQuery.extend({}, jQuery.ui.dialog.prototype, {
	    _title: function(title) {
	        if (!this.options.title ) {
	            title.html("&#160;");
	        } else {
	            title.html(this.options.title);
	        }
	    }
	}));

	jQuery.ui.dialog.prototype._position = jQuery.noop;
});
ff.ffPage.dialog = (function () {

// inits
//overflow manage
var old_overflow = {
	"dialog" : null
	, "style" : null
};

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
}, "a65c581e-64d9-473d-a166-c1848dab04cb");

/*
jQuery('html, body').on('touchstart touchmove', function(e){ 
	 //prevent native touch activity like scrolling
	 if (old_overflow !== null) {
		e.preventDefault();
	 }
});*/


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
        "callback"		: params.callback,
        "url"			: params.url,
        "title"			: params.title,
        "params"        : params.params || {},
        "height"        : params.height,
        "width"			: params.width,
        "resizable"		: params.resizable,
        "position"		: params.position,
        "draggable"		: params.draggable,
        "doredirects"	: params.doredirects,
        "modal"			: (params.modal === undefined ? true : false),
        "class"			: params.dialogClass,
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
	var scrollHeight = (elem.length > 0
		? elem.get(0).scrollHeight
		: elem.scrollHeight
	);
	
	//console.log(width);

	for(var i=300; i < width; i+=50) {
			elem.width(i);
	//console.log("width " +  elem.innerWidth() + " -/- " +( elem.get(0).scrollWidth  )+ ": " + elem.get(0).scrollHeight + " - " + scrollHeight + " = "  + (elem.get(0).scrollHeight - scrollHeight));

		if(/*elem.get(0).scrollHeight <= scrollHeight &&*/ elem.get(0).scrollWidth <= elem.innerWidth()) {
			width = elem.get(0).scrollWidth + 50;  
			break;
		}
	}


//console.log(width);
	return {
		width: width
		, height: scrollHeight + 20
	}; 
},
"adjSize" : function (id) {
	if(id === undefined) {
		id = dialogs.keys[dialogs.keys.length - 1];
	}
	
	var instance = dialogs.get(id).instance;
	var widget = instance.dialog("widget");
	
	//var wh = jQuery(window).height();
	var ww = jQuery(window).width();
	

	
	if(instance.dialog("option", "width") == "auto") {
		instance.dialog("option", "width", undefined);
		
		//if(!jQuery("#ffWidget_dialog_container_" + id + " TEXTAREA").length)
			instance.dialog("option", "width", that.getDimensions(widget).width);
	}

	//that.getDimensions(jQuery(".ui-widget-content", widget));
	//if (widget.outerHeight(false) > wh * 0.90) {
	//	instance.dialog("option", "height", wh * 0.90);
	//}
	if (widget.outerWidth(false) > ww * 0.90) {
		instance.dialog("option", "width", ww * 0.90);
	}
},

"refresh" : function (id, show) {
	if(id === undefined) {
		id = dialogs.keys[dialogs.keys.length - 1];
	}
	
	var instance = dialogs.get(id).instance;
	
	//widget.position({my : "center center", at : "center center", of : window});
	
	//if(parseInt(jQuery(widget).css("top")) < 0) {
	//	jQuery(widget).css("top", Math.round((jQuery(window).height()) - jQuery(widget).height()) / 2 );
	//}
	//jQuery(widget).css("top", 0);
	//jQuery(widget).css("left", 0);
	
	
	if (!instance.dialog("isOpen")) {
		instance.dialog("open");
	}
	
	that.adjSize(id);
	
	var widget = instance.dialog("widget");
	
//	widget.position();	
	widget.position({my : "center center", at : "center center", of : window});
	if(widget.position().top < 0)
		widget.position({ my: 'top', at: 'top+10', of : window });

	//show = show || !parseInt(jQuery(widget).parent().css("opacity"));
	if(show === true)
		jQuery(widget).parent().fadeTo("fast", 1, function() {
		    that.doEvent({
		        "event_name"    : "onDisplayedDialog",
		        "event_params"    : [id]
		    });		
		});
	else if(show === false)
		jQuery(widget).parent().css("opacity", 0);

	return true;
},

"makeInstance" : function (id) {
	//overflow manage
	if (old_overflow.dialog === null) {
		jQuery("body").addClass("ff-modal-open");
		//old_overflow.style = jQuery("body").attr("style");
		//jQuery("body").css("overflow", "hidden");
		old_overflow.dialog = id;
	}

	var tmp_params = that.dialog_params.get(id) || {};

	dialogs.get(id).instance = jQuery('<div id="ffWidget_dialog_container_' + id + '"></div>').dialog({
		autoOpen: false
		, dialogClass : tmp_params.class
		, resizable: tmp_params.resizable
		, position: { my: tmp_params.position, at: "center", of: window }
		//, position: tmp_params.position
		, modal: tmp_params.modal
		, draggable: tmp_params.draggable
		, closeText : ''
		, create: function(event, ui ) {
			var widget = jQuery(this).dialog("widget");
			/*var zIndex = 90;
			$("BODY *").each(function() {
				if($(this).css("position") == "fixed") {
					var current = parseInt($(this).css("z-index"), 10);
					if(current < 2000 && zIndex < current) zIndex = current;
				}
			});			
    		zIndex++;*/

			jQuery(widget).wrapAll('<div class="ff-modal"/>').parent().css({"opacity": 0,/* "z-index": zIndex */});	
		}
		, close: function(ev, ui) {
			var widget = jQuery(this).dialog("widget");
			jQuery(widget.parent()).remove();	
			
			that.onClose(id);
			//overflow manage
			if (id === old_overflow.dialog) {
				jQuery("body").removeClass("ff-modal-open");
				//jQuery("body").css("overflow", "");
				//old_overflow.style = null;
				old_overflow.dialog = null;
			}
		}
		, title: tmp_params.title
		, height: tmp_params.height || 'auto'
		, width: tmp_params.width || 'auto'
		/*, maxHeight: jQuery(window).height() * 0.90*/
		/*, maxWidth: jQuery(window).width() * 0.90*/
		, minWidth: tmp_params.params.min_width || 500  
		, hide: {effect: 'fade', duration: 200}
		, open: function() {
			var widget = jQuery(this).dialog("widget");
			
			//widget.appendTo(jQuery(widget).prev());

			//non fa piu ridimensionare le dialog
			/*if (widget.get(0).style.height == "auto")
				widget.height(widget.height());

			if (widget.get(0).style.width == "auto")
				widget.width(widget.width());

			if (jQuery(".ui-dialog-content", widget).get(0).style.height == "auto")
				jQuery(".ui-dialog-content", widget).height(jQuery(".ui-dialog-content", widget).height());*/
	    
        	//jQuery(".ui-dialog-content", widget).css("width", "");
		}
		, drag: function(event, ui) {
			if(ui.position.top < 0)
				ui.position.top = 10;
			
/*			var widget = jQuery(this).dialog("widget");
			console.log(ui);
			console.log(event.offsetX + " => " + ui.offset.left);
			if(parseInt(widget.css("left")) <= ui.position.left)
				ui.position.left = parseInt(widget.css("left")) -1;

			console.log(parseInt(widget.css("left")) + " => " + ui.position.left);
*/
	    }
		, resizeStart: function(event, ui) {
			var widget = jQuery(this).dialog("widget");
			
			//widget.css("position", "relative");
		}
		, resizeStop : function (ev, ui) {
			var diffx = Math.floor(ui.size.width) - Math.floor(jQuery("#ffWidget_dialog_container_" + id).dialog("widget").width());
			if (diffx) jQuery("#ffWidget_dialog_container_" + id).width(jQuery("#ffWidget_dialog_container_" + id).width() - diffx);
			var diffy = Math.floor(ui.size.height) - Math.floor(jQuery("#ffWidget_dialog_container_" + id).dialog("widget").height());
			if (diffy) jQuery("#ffWidget_dialog_container_" + id).height(jQuery("#ffWidget_dialog_container_" + id).height() - diffy);
			ff.ffPage.dialog.doEvent({
				"event_name" : "resize"
				, "event_params" : [id, ui]
			});
		}		
	});

	return dialogs.get(id).instance;
},

"doOpen" : function (id, url, title, preserveIstance, elemHighlight) {
//unique = null;
	if(unique && dialogs.keys.length) {
		preserveIstance = true;
		if(!url)
			url = that.dialog_params.get(id)["url"];
		if(!title)
			title = that.dialog_params.get(id)["title"];

		id = unique;
	}
    if (url !== undefined && that.dialog_params.get(id) !== undefined )
        that.dialog_params.get(id)["url"] = url;

	that.updateCursor(id, that.dialog_params.get(id)["url"]);

    if (title !== undefined && title.length > 0 && that.dialog_params.get(id) !== undefined)
        that.dialog_params.get(id)["title"] = title;

    if (dialogs.get(id) && dialogs.get(id).instance) {
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
        		breadCrumbs.push({"title":  jQuery(".ui-dialog-title > :not(.breadcrumbs)").text(), "url" : dialogs.get(id).params.current_url});
				
            ff.ffPage.dialog.goToUrl(id, url);
        } else {
            dialogs.get(id).instance.dialog("open");
        }
        return;
    }
	
	ff.ajax.ctxGet(id).reset();
	
    dialogs.set(id, {
		"instance"	: null,
		"params"	: jQuery.extend(true, {}, that.dialog_params.get(id)), 
        "elemHighlight" : elemHighlight,
		"breadCrumbs": []
		
	});

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
},

"close" : function (id) {
	dialogs.get(id).instance.dialog("close");
},

"onSuccess" : function (data, customdata) {
    var id = customdata.id;

    if (data === null) {
        if (dialogs.get(id).params.params && dialogs.get(id).params.params.persistent)
            dialogs.get(id).params.params.persistent = false;
        dialogs.get(id).instance && dialogs.get(id).instance.dialog("close");
        return false;
    }
    
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

	var instance = dialogs.get(id).instance;
    if (data["close"]) {
        instance.dialog("close");
	} else if (data["url"]) {
		dialogs.get(id).params.current_url = data["url"];
		that.updateCursor(id, data["url"]);
	} else if (data["cursor_reload"] && data["cursor_reload"] === id) {
		ff.ffRecord.cursor.reload(id);		
    } else {
		that.makeDlgBt(id, data);
		
        if (data["html"]) {
			if (!ff.ajax.ctxGet(id).needInit() && dialogs.get(id) !== undefined && !instance.dialog("isOpen"))
                instance.dialog("open");
        }
		
		if (!ff.ajax.ctxGet(id).needInit())
			ff.ffPage.dialog.refresh(id, true);
		else {
			dialogs.get(id).waiting = true;
			ff.ajax.blockUI();
		}

		//instance.dialog("widget").hide();
        //instance.dialog("widget").fadeIn();
    }
    return true;
},

"onClose" : function (id, hide) {
/*    if (that.dialog_deps[id] !== undefined)
        delete that.dialog_deps[id];*/
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

    dialogs.get(id).instance.remove();
    dialogs.unset(id);
	ff.ajax.ctxGet(id).reset();

    that.doEvent({
        "event_name": "onClose"
        , "event_params" : [id]
    });
},

"doAction" : function (id, action, component, detailaction, action_param, addit_fields) {
    that.param(id, "lastaction", action);
    
    switch (action) {
        case "close":
            dialogs.get(id).instance.dialog("close");
            break;

        default:
			ff.ajax.ctxGet(id).reset();
            that.doEvent({"event_name": "doAction", "event_params" : [id, action, component, detailaction, action_param]});

            var fields = ff.getFields(dialogs.get(id).instance.closest(".ui-dialog"), id);
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
    
	ff.ajax.ctxGet(id).reset();
	
	var fields = (params.fields === undefined ? jQuery(":input", dialogs.get(id).instance.closest(".ui-dialog")).not("input:checkbox:not(:checked)").not("input:radio:not(:checked)") : params.fields);

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
         "url"                : that.parseUrl(id, url),
         "component"        : params.component,
         "section"            : params.section,
         "fields"            : fields,
         "callback"            : that.onSuccess,
         "customdata"        : {
            "id"            : id
            , "callback"    : params.callback
			, "caller" : {
				"func" : ff.ffPage.dialog.doRequest
				, "args" : ff.argsAsArray(arguments)
			}
         },
         "injectid"            : params.injectid,
         "ctx"				: id,
         "chainupdate"        : params.chainupdate, 
         "doredirects"        : dialogs.get(id).params.doredirects
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
	var widget = instance.dialog("widget");
	

	if(data["html"].indexOf("dialogSubTitleTab")) {
		jQuery(".dlgTab", widget).remove();
	}
	jQuery(".dlgSubTitle", widget).remove();
	
	if(data["html"].indexOf("dialogActionsPanel top")) {
		jQuery(".dlgTopPanel", widget).remove();
	}

	if(data["html"].indexOf("dialogActionsPanel")) {
		jQuery(".dlgBottomPanel", widget).remove();
	}
},
"makeDlgBt" : function(id, data) {
	var instance = dialogs.get(id).instance;
	var widget = instance.dialog("widget");

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
		jQuery(".ui-dialog-title").addClass(jQuery(".dialogTitle:first", instance).attr("class")).removeClass("dialogTitle");
		jQuery(".dialogTitle:first", instance).appendTo(jQuery(".ui-dialog-title", widget).empty()).attr("class", "dlgTitle");
	}
	/*
	if(jQuery(".dialogSubTitleTab", instance).length) {
		var startSel = 0;
		jQuery(".dialogSubTitleTab", instance).replaceWith(function(i) { 
			jQuery(this).removeClass("dialogSubTitleTab");

			if(jQuery(this).hasClass("selected")) {
				startSel = i;
			
				jQuery(this).removeClass("selected");
			}
			var depClass = jQuery(this).attr("class").replace("dep-", "dlg-");
			
			return '<a class="dialogSubTitleTab" href="javascript:void(0);" rel="' + depClass + '">' + jQuery(this).html() + '</a>';
		});
		jQuery(".dialogSubTitleTab", instance).appendTo(jQuery(".ui-dialog-title", widget)).removeClass("dialogSubTitleTab").wrapAll('<div class="dlgTab" />');
		jQuery(".dlgTab a", widget).click(function() {
			var rel = jQuery(this).attr("rel");
			if(rel) {
				jQuery(".dlg-tab", instance).hide();

				jQuery("." + rel, instance).show();
				
				ff.ffPage.dialog.refresh(id);

				jQuery(".dlgTab a", widget).removeClass("selected");
				jQuery(this).addClass("selected");
			}
		});

		jQuery(".dlgTab a:eq(" + startSel + ")", widget).click();
	}*/
	
	if(jQuery(".ffTab", instance).length) {
		jQuery(".ffTab", instance).replaceWith(function(i) { 
			var dlgClass = "dialogSubTitleTab";

			if(jQuery(this).is(".active, .selected, .current")) 
				dlgClass += " selected";

			jQuery("a", this).addClass(dlgClass);
			return jQuery(this).html();
		});
		jQuery(".dialogSubTitleTab", instance).appendTo(jQuery(".ui-dialog-title", widget)).removeClass("dialogSubTitleTab").wrapAll('<div class="dlgTab" />');
		jQuery(".dlgTab a", widget).click(function() {
			jQuery(".dlgTab a", widget).removeClass("selected");
			jQuery(this).addClass("selected");
		});
	}
	
	if(jQuery(".dialogSubTitle", instance).length) {
		jQuery(".dialogSubTitle", instance).insertAfter(jQuery(".ui-dialog-titlebar", widget)).addClass("dlgSubTitle").removeClass("dialogSubTitle");
	}

    var skipForceBt = jQuery(".dlgTab").length;
	if(jQuery(".dialogActionsPanel.top", instance).length) {
        if(!skipForceBt)
		    jQuery(".dialogActionsPanel.top.force", instance).insertBefore(jQuery(".ui-dialog-content", widget)).addClass("dlgTopPanel").removeClass("dialogActionsPanel top force");
		if(!countRecord && countGrid == 1)
			jQuery(".dialogActionsPanel.top:not(.force)", instance).insertBefore(jQuery(".ui-dialog-content", widget)).addClass("dlgTopPanel").removeClass("dialogActionsPanel top");
	}

	if(jQuery(".dialogActionsPanel:not(.top)", instance).length) {
        if(!skipForceBt)
		    jQuery(".dialogActionsPanel.force", instance).insertAfter(jQuery(".ui-dialog-content", widget)).addClass("dlgBottomPanel").removeClass("dialogActionsPanel force");

		if(countRecord) {
			jQuery(".dialogActionsPanel:not(.force):last", instance).insertAfter(jQuery(".ui-dialog-content", widget)).addClass("dlgBottomPanel").removeClass("dialogActionsPanel");
		} else if(countGrid == 1) {
			jQuery(".dialogActionsPanel:not(.force)", instance).insertAfter(jQuery(".ui-dialog-content", widget)).addClass("dlgBottomPanel").removeClass("dialogActionsPanel");
		} else if(!countRecord && !countGrid) {
			jQuery(".dialogActionsPanel", instance).insertAfter(jQuery(".ui-dialog-content", widget)).addClass("dlgBottomPanel").removeClass("dialogActionsPanel");
		}
	}
	
	if(dialogs.get(id).breadCrumbs.length) {
		var breadCrumbs = "";
		for(var i=0; i<dialogs.get(id).breadCrumbs.length; i++) {
			var brdUrl = 'ff.ffPage.dialog.doOpen(\'' + id + '\', \'' + dialogs.get(id).breadCrumbs[i]["url"] + '\',\'' + dialogs.get(id).breadCrumbs[i]["title"] + '\', true);';
			breadCrumbs = breadCrumbs + '<li><a href="javascript:void(0);" onClick="' + brdUrl + '">' + dialogs.get(id).breadCrumbs[i]["title"] + '</a></li>';
		}
		if(breadCrumbs) {
			if(!jQuery(".dlgBrdCrumbs", widget).length)
				jQuery('<span class="dlgBrdCrumbs"><a href="javascript:void(0);" onClick="' + brdUrl + '" class="dlgBrdBack"></a><ul>' + breadCrumbs + '</ul></span>').prependTo(jQuery(".ui-dialog-title", widget));
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
