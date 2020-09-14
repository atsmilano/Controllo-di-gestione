/**
 * Forms Framework Javascript Handling Object
 *    navtabs page' plugin namespace
 */

ff.ffPage.navtabs = (function () {

/* privates */
var tabs_nav = undefined;
var tabs_groups	= ff.hash();
var tabs		= ff.hash();
var tabs_params	= ff.hash();

/* inits */
jQuery(function() {
	ff.pluginAddInit("jquery-ui", function () {
		jQuery(".topbar-tabs").tabs({
			"create" : function( event, ui ) {
				tabs_nav = event.target;
				
				jQuery(".navtabs", event.target).each(function () {
					jQuery(this).tabs();
					tabs_groups.set(tabs_groups.length, this);
				});
			}
		});
	});
});

ff.pluginAddInit("ff.ajax", function () {
	ff.ajax.addEvent({
		"event_name" : "onUpdateContent"
		, "func_name" : function (params, data, injectid) {
			if (params.ctx && ff.ffPage.navtabs.exist(params.ctx) && params.component === undefined) {
				if (!ff.ffPage.navtabs.get(params.ctx).instance) 
					ff.ffPage.navtabs.makeInstance(params.ctx);

				//ff.ffPage.navtabs.removeDlgBt(params.ctx, data);
			}
		}
	});
	ff.ajax.addEvent({
		"event_name" : "onRedirect"
		, "func_name" : function (url, data, mydata, params) {
			if (
				(params.ctx && ff.ffPage.navtabs.get(params.ctx) && ff.ffPage.navtabs.getInstance(params.ctx))
				&& (params.doredirects || data["doredirects"] !== undefined)
			) {
				ff.ffPage.navtabs.close(params.ctx);
			}
		}
	});
	ff.ajax.addEvent({
		"event_name" : "ctxInitDone"
		, "func_name" : function (id) {
			if (tabs.get(id) && tabs.get(id).waiting) {
				ff.ajax.unblockUI();
				ff.ffPage.navtabs.refresh(id);
				return true;
			}
		}
	});
}, "73819724-4f5e-43a4-ad76-e7606e029bb7");

var that = { /* publics */
__ff : true, /* used to recognize ff'objects */

"getActiveGroup" : function () {
	return jQuery(tabs_nav).tabs("option", "active");
},

"getGroup" : function (index) {
	if (index === undefined)
		index = that.getActiveGroup();
	return tabs_groups.get(index);
},

"addTab" : function (params) {
	ff.ajax.ctxAdd(params.id, that, "navtabs");
	
	params._group = that.getActiveGroup();
	tabs_params.set(params.id, params);

    that.doEvent({
        "event_name"    : "onAddTab",
        "event_params"    : [params.id]
    });
},

"get" : function (id) {
    return tabs.get(id);
},

"exist" : function (id) {
	return tabs_params.isset(id);
},

"getInstance" : function (id) {
    return tabs.get(id).instance;
},

"replaceHTML" : function (id, data) {
	that.getInstance(id).html(data["html"]);
	//ff.ffPage.navtabs.makeDlgBt(id, data);
},

"param" : function (id, param, value) {
    if (value !== undefined)
        tabs_params.get(id)[param] = value;
    else
        return tabs_params.get(id)[param];
},

"refresh" : function (id) {
	var params = tabs_params.get(id);
	if (!jQuery("#" + id + "_label").length) {
		jQuery(".navtabs_labels", that.getGroup(params._group)).append("<li id='" + id + "_label'><a href='#" + id + "'>" + params.title + "</a><span class='ui-icon ui-icon-close' onclick='ff.ffPage.navtabs.close(\"" + id + "\");'>Remove Tab</span></li>");
		jQuery("#" + id).show();
		jQuery(that.getGroup(params._group)).tabs("refresh");
		jQuery(that.getGroup(params._group)).tabs("option", "active", jQuery(".navtabs_labels li", that.getGroup(params._group)).length - 1);
	}

	return true;
},

"makeInstance" : function (id) {
	var params = tabs_params.get(id);
	var tmp = jQuery("<div id='" + id + "'>&nbsp;</div>").hide();
	jQuery(that.getGroup(params._group)).append(tmp);
	tabs.get(id).instance = tmp;
	return tabs.get(id).instance;
},

"doOpen" : function (id, url, title, preserveIstance) {
	if (id === null) {
		id = "navtab_" + ff.getUniqueID();
	}
		
	if (!that.exist(id)) {
		that.addTab({
			"id" : id
			, "title" : title
			, "url" : url
		});
	}
	
    if (url !== undefined && tabs_params.get(id) !== undefined )
        tabs_params.get(id)["url"] = url;

    if (title !== undefined && title.length > 0 && tabs_params.get(id) !== undefined)
        tabs_params.get(id)["title"] = title;

    if (tabs.get(id) && tabs.get(id).instance) {
        if(tabs.get(id).params.current_url !== url && preserveIstance) {
            ff.ffPage.navtabs.goToUrl(id, url);
        } else {
            //tabs.get(id).instance.dialog("open");
        }
        return;
    }
	
	ff.ajax.ctxGet(id).reset();
	
    tabs.set(id, {
		"instance"	: null,
		"params"	: jQuery.extend(true, {}, tabs_params.get(id)),
	});

    tabs.get(id).params.current_url = tabs.get(id).params.url;
    
    var evres = that.doEvent({"event_name": "doOpen", "event_params" : [that, id, url, title]});
    if (evres !== true) {
        ff.ajax.doRequest({
            "url"			: that.parseUrl(id, tabs.get(id).params.current_url),
            "type"			: "GET",
            "callback"		: that.onSuccess,
            "customdata"	: {
                "id" : id
				, "caller" : {
					"func" : ff.ffPage.navtabs.doOpen
					, "args" : ff.argsAsArray(arguments)
				}
            },
            "injectid"		: tabs.get(id).instance,
            "ctx"			: id,
			"brandnew"		: true,
            "doredirects"	: tabs.get(id).params.doredirects
        });
    }
},

"close" : function (id) {
	var params = tabs_params.get(id);
    if (params.callback) {
        eval(params.callback);
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

    tabs.unset(id);
	ff.ajax.ctxGet(id).reset();

    /*if (params.params && params.params.persistent)
        return;*/

	jQuery("#" + id + "_label").remove();
	jQuery("#" + id).remove();
	jQuery(that.getGroup(params._group)).tabs("refresh");
	
    that.doEvent({
        "event_name": "onClose"
        , "event_params" : [id]
    });
},

"onSuccess" : function (data, customdata) {
    var id = customdata.id;

    if (data === null) {
        if (tabs.get(id).params.params && tabs.get(id).params.params.persistent)
            tabs.get(id).params.params.persistent = false;
        tabs.get(id).instance && that.close(id);
        return false;
    }

    /**
     *    data.close
     *        true = chiude il tab
     *        false = valorizza (o aggiorna) il tab
     *
     *    data.refresh
     *        true = imposta il tab per l'aggiornamento del chiamante su chiusura
     *
     *    data.html
     *        contenuto del tab se aggiornato
     *
     *    data.url
     *        cambia l'url del tab su redirect interno
     */
    
    if (customdata.callback)
        customdata.callback(id, data);

    if (data.callback)
        eval(data.callback);

	var instance = tabs.get(id).instance;
    if (data["close"]) {
        that.close(id);
	} else if (data["url"]) {
		tabs.get(id).params.current_url = data["url"];
    } else {
        if (data["html"]) {
			//if (!ff.ajax.ctxGet(id).needInit() && tabs.get(id) !== undefined && !instance.dialog("isOpen"))
                //instance.dialog("open");
        }

		if (!ff.ajax.ctxGet(id).needInit()) {
			ff.ffPage.navtabs.refresh(id);
		} else {
			tabs.get(id).waiting = true;
			ff.ajax.blockUI();
		}
    }
    return true;
},

"doAction" : function (id, action, component, detailaction, action_param, addit_fields) {
    that.param(id, "lastaction", action);
    
    switch (action) {
        case "close":
            that.close(id);
            break;

        default:
			ff.ajax.ctxGet(id).reset();
            that.doEvent({"event_name": "doAction", "event_params" : [id, action, component, detailaction, action_param]});

            var fields = ff.getFields(tabs.get(id).instance, id);
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
                 "url"                : that.parseUrl(id, tabs.get(id).params.current_url),
                 "type"                : "POST",
                 "fields"            : fields,
                 "callback"            : that.onSuccess,
                 "customdata"        : {
                    "id" : id
					, "caller" : {
						"func" : ff.ffPage.navtabs.doAction
						, "args" : ff.argsAsArray(arguments)
					}
                 },
                 "injectid"            : tabs.get(id).instance,
                 "ctx"				: id,
                 "doredirects"        : tabs.get(id).params.doredirects
            });
            break;
    }
},

"goToUrl" : function (id, url) {
	ff.ajax.ctxGet(id).reset();
	
    tabs.get(id).params.current_url = url;

    ff.ajax.doRequest({
         "url"                : that.parseUrl(id, tabs.get(id).params.current_url),
         "type"                : "GET",
         "callback"            : that.onSuccess,
         "customdata"        : {
            "id" : id
			, "caller" : {
				"func" : ff.ffPage.navtabs.goToUrl
				, "args" : ff.argsAsArray(arguments)
			}
         },
         "injectid"            : tabs.get(id).instance,
         "ctx"				: id,
		 "brandnew"			: true,
         "doredirects"        : tabs.get(id).params.doredirects
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
	
    var fields = (params.fields === undefined ? jQuery(":input", tabs.get(id).instance).not("input:checkbox:not(:checked)").not("input:radio:not(:checked)") : params.fields);

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
        url = tabs.get(id).params.current_url;
        
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
				"func" : ff.ffPage.navtabs.doRequest
				, "args" : ff.argsAsArray(arguments)
			}
         },
         "injectid"            : params.injectid,
         "ctx"				: id,
         "chainupdate"        : params.chainupdate, 
         "doredirects"        : tabs.get(id).params.doredirects
    });
},

"parseUrl" : function (id, url) {
    var parsedurl = url;

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

    if (tabs.get(id).params.params !== undefined)
    {
/*        for (i in tabs.get(id).params.params) {
            parsedurl += "&" + i + "=" + jQuery("#" + tabs.get(id).params.params[i]).val();
        }*/
    }
    return parsedurl;
},

"removeDlgBt" : function(id, data) {
},

"makeDlgBt" : function(id, data) {
},

}; /* publics' end */

return that;

/* code's end. */
})();
