/**
 * Forms Framework Javascript Handling Object
 *	Ajax' namespace
 */

ff.ajax = (function () {

// privates
var blocked_ui		= 0;

var ctxs = ff.hash();
ctxs.inits_by_wdg = ff.hash();

var that = { // publics
__ff : true, // used to recognize ff'objects

"ctx" : function (id, obj, type) {
	
	var that = {
		"id" : id,
		"need_init" : false,
		"inits" : ff.hash(),
		"obj" : obj,
		"type" : type,
		
		"needInit" : function (value) {
			if (value !== undefined) {
				that.need_init = value;
			}
			return that.need_init;
		},

		"reset" : function () {
			that.inits = ff.hash();
			that.need_init = false;
		},

		"initInspect" : function (component) {
			//var widgets = null;

			/*if (component === undefined) {
				if (ff.struct.page !== undefined && ff.struct.page.widget !== unidefined && ff.struct.page.widget !== "") {
					widgets = ff.struct.page.widget.split(",");
					widgets.each(function (k, widget) {
						dlg_inits.page.set(widget, false);
						if (glb_inits.page.get(widget) === undefined)
							glb_inits.page.set(widget, ff.hash());
						glb_inits.page.get(widget).set(id);
						dlg_inits.need_init = true;
					});
				}

				if (ff.struct.fields.length) ff.struct.fields.each( function (fld_id, field) {
					if (field.widget !== undefined && field.widget !== "") {
						if (dlg_inits.field.get(fld_id) === undefined)
							dlg_inits.field.set(fld_id, ff.hash());

						widgets = field.widget.split(",");
						widgets.each(function (k, widget) {
							dlg_inits.field.get(fld_id).set(widget, false);
							glb_inits.field.set(fld_id, id);
							dlg_inits.need_init = true;
						});
					}
				});
			}*/

			ff.struct.get("comps").each(function (com_id, com, i) {
				if (component !== undefined && com_id !== component)
					return;

				if (com.ctx === that.id) {
					if (com.widgets !== undefined) com.widgets.each(function (k, widget) {
						if (that.inits.get(widget.id) === undefined)
							that.inits.set(widget.id, ff.hash());					
						that.inits.get(widget.id).set(widget.type, false);

						if (ctxs.inits_by_wdg.get(widget.type) === undefined)
							ctxs.inits_by_wdg.set(widget.type, ff.hash());
						ctxs.inits_by_wdg.get(widget.type).set(widget.id, that);

						that.needInit(true);
					});

					if (com.fields.length) com.fields.each( function (fld_id, field) {
						if (field.widgets !== undefined) field.widgets.each(function (k, widget) {
							if (that.inits.get(widget.id) === undefined)
								that.inits.set(widget.id, ff.hash());					
							that.inits.get(widget.id).set(widget.type, false);

							if (ctxs.inits_by_wdg.get(widget.type) === undefined)
								ctxs.inits_by_wdg.set(widget.type, ff.hash());
							ctxs.inits_by_wdg.get(widget.type).set(widget.id, that);

							that.needInit(true);
						});
					});
				}
			});

			return that.needInit();
		},

		"initEvent" : function (widget_id, widget_type) {
			that.inits.get(widget_id).set(widget_type, true);
			ctxs.inits_by_wdg.get(widget_type).unset(widget_id);

			// check if init is done
			var tmp_still_waiting = false;
			that.inits.each(function (k, v) {
				v.each(function(wk, wv) {
					if (!wv)
						tmp_still_waiting = true;
				});
			});

			if (!tmp_still_waiting) {
				var res = ff.ajax.doEvent({
					"event_name"	: "ctxInitDone",
					"event_params"	: [that.id]
				});
				var lastres = ff.getLastRes(res);

				if (!lastres)
					that.needInit(false);
			}

		},
		
		"getInstance" : function () {
			return obj.getInstance(that.id);
		},
		
		"doRequest" : function (params) {
			return obj.doRequest(that.id, params);
		},

		"doAction" : function (action, component, detailaction, action_param, addit_fields) {
			return obj.doAction(that.id, action, component, detailaction, action_param, addit_fields);
		},

		"goToUrl" : function (url) {
			return obj.goToUrl(that.id, url);
		},

		"close" : function () {
			return obj.close(that.id);
		},
		
		"replaceHTML" : function (data) {
			return obj.replaceHTML(that.id, data);
		}
	};
	
	return that;
},

"ctxGet" : function (id) {
	if (id === undefined)
		return ctxs;
	else
		return ctxs.get(id);
},

"ctxAdd" : function (id, obj, type) {
	ctxs.set(id, that.ctx(id, obj, type));
},

"ctxDel" : function (id) {
	ctxs.unset(id);
},

"ctxInitEvent" : function (widget_id, widget_type) {
	var tmp = undefined;
	
	if (ctxs.inits_by_wdg.get(widget_type) !== undefined)
		tmp = ctxs.inits_by_wdg.get(widget_type).get(widget_id);
	
	if (tmp === undefined) return;
	
	tmp.initEvent(widget_id, widget_type);
},

"ctxDoRequest" : function (id, params) {
	return that.ctxGet(id).doRequest(params);
},

"ctxDoAction" : function (id, action, component, detailaction, action_param, addit_fields) {
	return that.ctxGet(id).doAction(action, component, detailaction, action_param, addit_fields);
},

"ctxGoToUrl" : function (id, url) {
	return that.ctxGet(id).goToUrl(url);
},

"ctxClose" : function (id) {
	return that.ctxGet(id).close();
},

"ctxReplaceHTML" : function (id, data) {
	return that.ctxGet(id).replaceHTML(data);
},

"defaults" : {
	"css"			: {
			"padding":	0,
			"margin":		0,
			"top":		'40%',
			"left":		'45%',
			"textAlign":	'center',
			"cursor":		'wait'
		},
	"overlayCSS"	: {},
	"message"		: '<h1 class="block-loader"></h1>',
	"display": true
},

"chainupdate"	: {},

"getblocked_ui" : function () {
	return blocked_ui;
},

"blockUI" : function (chainupdate) {
	blocked_ui++;

	if (blocked_ui > 1 || chainupdate)
		return;

	that.chainupdate.resources = ff.hash();
	that.chainupdate.updated = ff.hash();
	if(that.defaults.display) {
		if(jQuery.blockUI === undefined) { //TOCHECK
			ff.load("jquery.plugins.blockui", function () {
				if (that.defaults.css !== undefined) jQuery.blockUI.defaults.css = that.defaults.css;
				if (that.defaults.overlayCSS !== undefined) jQuery.blockUI.defaults.overlayCSS = that.defaults.overlayCSS;
				jQuery.blockUI(
					(that.defaults.message !== undefined ? {message : that.defaults.message} : {})
				);
			}, undefined, "", false);
		} else {
			if (that.defaults.css !== undefined) jQuery.blockUI.defaults.css = that.defaults.css;
			if (that.defaults.overlayCSS !== undefined) jQuery.blockUI.defaults.overlayCSS = that.defaults.overlayCSS;
			jQuery.blockUI(
				(that.defaults.message !== undefined ? {message : that.defaults.message} : {})
			);
		}
	} else {
		jQuery("body").addClass("pbar");
	}
},

"unblockUI" : function (chainupdate, reset, data) {
	if (reset || blocked_ui === 1) if (chainupdate === undefined && that.chainupdate.resources.length) {
		var chainupdated = false;
		ff.struct.get("comps").each(function (componentid, component) {if (!that.chainupdate.updated.isset(componentid)) {
			var update = false;
			for (var i = 0; i < component.resources.length; i++) {
				if (that.chainupdate.resources.isset(component.resources[i])) {
					update = true;
				}
			}
			if (update) {
				that.chainupdate.updated.set(componentid, true);
				var res = that.doEvent({
					"event_name"	: "onUpdateComponent",
					"event_params"	: [componentid, component, data]
				});
				for (var i = 0; i < res.length; i++) {
					if (res[i]) {
						chainupdated = true; // ?? capire da dove arriva
						break;
					}
				}
			} else {
				component.fields.each(function (fieldid, field) {
					var tmp = componentid + "_" + fieldid;
					if (!that.chainupdate.updated.isset(tmp)) {
						update = false;
						for (var i = 0; i < field.resources.length; i++) {
							if (that.chainupdate.resources.isset(field.resources[i])) {
								update = true;
							}
						}
						if (update) {
							that.chainupdate.updated.set(tmp, true);
							that.doEvent({
								"event_name"	: "onUpdateField",
								"event_params"	: [componentid, fieldid, field, data]
							});
						}
					}
				});
			}
		}});

		ff.struct.get("fields").each(function (fieldid, field) {if (!that.chainupdate.updated.isset(fieldid)) {
			var update = false;
			for (var i = 0; i < field.resources.length; i++) {
				if (that.chainupdate.resources.isset(field.resources[i])) {
					update = true;
				}
			}
			if (update) {
				that.chainupdate.updated.set(fieldid, true);
				that.doEvent({
					"event_name"	: "onUpdateField",
					"event_params"	: [undefined, fieldid, field, data]
				});
			}
		}});
		that.chainupdate.resources.each(function (key, value, index) {
			that.doEvent({
				"event_name"	: "onUpdate",
				"event_params"	: [key, value, data]
			});
		});
		if (!chainupdated) {
			that.doEvent({
				"event_name" : "onEmptyQueue",
				"event_params"	: [data]
			});
			if(that.defaults.display)
				jQuery.unblockUI();
			else {
				setTimeout('jQuery("body").removeClass("pbar");', 700);
			}
		}
	} else {
		if(that.doEvent !== undefined)
			that.doEvent({
				"event_name" : "onEmptyQueue",
				"event_params"	: [data]
			});
			
		if(that.defaults.display) {
			if(jQuery.blockUI !== undefined)
			jQuery.unblockUI();
		} else {
			setTimeout('jQuery("body").removeClass("pbar");', 700);   
		}
			
	}
	
	if (reset)
		blocked_ui = 0;
	else
		blocked_ui--;

	if (blocked_ui > 0) {
		return;
	}
},

/**
 * params (object)
 *	url				l'indirizzo della richiesta. se omesso corrisponde all'url corrente
 *	type			il tipo di richiesta, se GET o POST. di default è POST
 *	component		il componente richiesto
 *	section			la sezione del componente richiesto (richiede che sia settato component)
 *	fields			i campi da passare con la richiesta, se no vengono presi quelli di tutta la pagina
 *	addFields		eventuali campi da aggiungere a quelli standard
 *	callback		la funzione da richiamare su completamento della richiesta
 *	customdata		dati aggiuntivi da passare con la richiesta alla callback
 *	injectid		l'id dell'elemento DOM da sostituire in caso di successo
 *	ctx				se la richiesta è avvenuta in un contesto ristretto (es.: dialog)
 *	action			sovrascrive o aggiunge frmAction
 *	replace			sovrascrive l'item
 *	doredirects		esegue i redirect invece di seguirli con chiamate ajax a cascata
 *  formName		nome alternativo del form default: frmMain
 *  stickycomp		normalmente false, impedisce l'aggiornamento dei componenti
 *  wholePage		normalmente false, richiede l'intera pagina invece del solo contenuto
 *  jsonp			normalmente false, permette di fare chiamate jsonp su richiesta
 *  async			normalmente true, permette di forzare una richiesta sincrona
 *  brandnew		normalmente false, permette di forzare una richiesta nuova
 *  sendstruct		normalmente false, permette di forzare l'invio di ff.struct
 *  blockui			default: true
 */
"doRequest" : function (params) {
	var async	= params.async !== undefined ? params.async : true;
	var form	= (params.formName ? jQuery("#" + params.formName) : jQuery("#frmMain"));

	var url		= (params.url ? params.url : (function () {
		if (params.component) {
			var tmp = ff.struct.get("comps").get(params.component);
			if (tmp !== undefined && tmp.url)
				return tmp.url;
		}
		return document.location.href
	})());

	url = ff.fixPath(url);
	
	if (params.wholePage)
		url = ff.urlAddParam(url, "XHR_GET_FULL");
	
	var fields		= (params.fields === undefined ? (params.brandnew ? [] : ff.getFields(form)) : params.fields);
	var type		= (params.type === undefined ? "POST" : params.type);

	if (params.ctx) {
		/*fields.push(
			{name: "XHR_CTX_ID", value: params.ctx}
		);*/
		url = ff.urlAddParam(url, "XHR_CTX_ID", params.ctx);
		var ctx = that.ctxGet(params.ctx);
		/*fields.push(
			{name: "XHR_CTX_TYPE", value: ctx.type}
		);*/
		url = ff.urlAddParam(url, "XHR_CTX_TYPE", ctx.type);
	}
	
	if (params.addFields) {
		if (Object.prototype.toString.call(fields) == "[object Array]")
			fields = fields.concat(params.addFields);
		else
			fields = fields.add(params.addFields);
	}

	var out_struct = [];
	if (params.component) {
		fields.push({name: "XHR_COMPONENT", value: params.component});

		if (params.section && jQuery("#" + params.component + "_" + params.section).attr("id") !== undefined)
			fields.push({name: "XHR_SECTION", value: params.section});
	}
	
	if (!params.brandnew) {
		var url_parts = url.parseUri();
		
		ff.struct.get("comps").each(function(k, v, i) {
			var tmp_parts = v.url.parseUri();
			if (url_parts.path === tmp_parts.path || params.sendstruct) {
				out_struct.push({
					'id' : k
					, 'type' : v.type
					, 'factory_id' : v.factory_id
					, 'ctx' : v.ctx
				});
			}
		});
		
		if (out_struct.length) {
			//fields.push({name: "XHR_FFSTRUCT", value: JSON.stringify(out_struct)});
			url = ff.urlAddParam(url, "XHR_FFSTRUCT", JSON.stringify(out_struct));
		}
	}
	
	if (params.action) {
		for (var i = 0; i < fields.length; i++) {
			if (fields[i].name == "frmAction") {
				fields.splice([i], 1);
				i--;
			}
		}
		fields.push({name: "frmAction", value: params.action});
	}
	
	if (params.blockui === undefined || params.blockui) {
		ff.ajax.blockUI(params.chainupdate);
	}
	
	var dataType = (params.jsonp !== undefined ? (params.jsonp ? "jsonp json" : "json") : (ff.origin != ff.httpGetOrigin() ? "jsonp json" : "json"));

	jQuery.ajax({
		  "url"			: url
		, "async"		: async
		, "data"		: fields
		, "type"		: type
		, "dataType"	: dataType
		, "jsonp"		: (dataType == "jsonp json" ? "XHR_JSONP" : undefined)
		, "success"		: ff.ajax.onSuccess
		, "error"		: ff.ajax.onError
		, "cache"		: false
		, "mydata"		: {
			"params"		: params,
			"parsed_url"	: url,
			"parsed_fields" : fields,
			"caller" : {
				"func" : ff.ajax.doRequest
				, "args" : ff.argsAsArray(arguments)
			}
		}
	});
},

"jsonpProxy" : function () {
},

"onError" : function (jqXHR, textStatus, errorThrown) {
	if (this.mydata.params.blockui === undefined || this.mydata.params.blockui) {
		that.unblockUI(false, true);
	}
		
	alert("Impossibile connettersi con il server, riprovare più tardi " + errorThrown) ;

	if (this.mydata && this.mydata.params.callback !== undefined) {
		this.mydata.params.callback(null, this.mydata.params.customdata);
	}
	return false;
},

"onSuccess" : function (data) {
	// shortcuts
	var params = this.mydata.params;

	var injectid	= (params.injectid
						? params.injectid 
						: "#" + (params.component 
									? (params.section && jQuery("#" + params.component + "_" + params.section).attr("id") !== undefined
										? params.component + "_" + params.section 
										: params.component
									) 
									: "content"
								)
					);
						
	if (data === null) { 
        //that.onError(null, null, "error");
		if (params.callback !== undefined)
			params.callback(null, params.customdata);
		return false;
	}
	
	var res = that.doEvent({
		"event_name"	: "onSuccess",
		"event_params"	: [data, params, injectid]
	});
	
	injectid = data["injectid"] || injectid;
	var doredirects = (data["doredirects"] !== undefined ? data["doredirects"] === true || data["doredirects"] === "true" : params.doredirects === true || params.doredirects === "true");

	// aggiorno lo stato degli oggetti correlati
	if (params.chainupdate !== true) {
		if (data.resources && (data["insert_id"] !== undefined || data.refresh)) {
			ff.struct.get("comps").each(function (key, value) {
				if (data["url"] !== undefined && value.ctx === params.ctx)
					that.chainupdate.updated.set(key, true);
				else if (params.component !== undefined && key === params.component)
					that.chainupdate.updated.set(key, true);

				if (data.resources.length) {
					for (var c = 0; c < data["resources"].length; c++) {
						that.chainupdate.resources.set(data["resources"][c], (data["insert_id"] !== undefined ? data["insert_id"] : true));
					}
				}
			});
		}
	}

	// elimina gli oggetti implicati nell'azione, in modo che possano essere ricreati
	if (!params.stickycomp) {
		if (data["html"] !== undefined || data["url"] !== undefined) {
			if (params.component !== undefined) { // un unico componente
				if (params.section) { // solo un pezzo
					//console.log("TODO: aggiornamento componente/sezione", params.component, params.section);
				} else { // l'intero componente
					ff.clearComponent(params.component);
				}
			} else { // più di un componente
				if (params.ctx) { // internamente al ctx
					ff.struct.get("comps").each(function (key, value) {
						if (value.ctx === params.ctx)
							ff.clearComponent(key);
					});
				} else { // l'intero contenuto della pagina
					ff.struct.get("comps").each(function (key) {
						if (key !== "fields")
							ff.clearComponent(key);
					});
				}
			}
		}
	}

	if (data["html"] !== undefined) {
		// inserisco il nuovo contenuto
		if (data["headers"] !== undefined) {
			var tmpdiv = ff.getUniqueID();
			//jQuery("body").append('<div id="fftmp' + tmpdiv + '">' + data["headers"] + '<script type="text/javascript">jQuery("#fftmp' + tmpdiv + '").remove();</script></div>');
			jQuery("body").append('<div id="fftmp' + tmpdiv + '">' + data["headers"] + '</div>');
			jQuery(function () {jQuery("#fftmp" + tmpdiv).remove();});
		}
		
		if(params.ctx && ctxs.get(params.ctx) !== undefined) {
			var ctx = that.ctxGet(params.ctx);
			if (params.component === undefined || params.section === undefined) { // TODO: gestire le sezioni
				ctx.initInspect(params.component);
			}
		}

		var res = that.doEvent({
			"event_name" : "onUpdateContent"
			, "event_params" : [params, data, injectid]
		});
		//var lastres = ff.ffEvents.getLastRes(res);

		if (params.component === undefined) {
			if (injectid === "#content") {
				if(params.ctx) {
					ctx.replaceHTML(data);
				} else {
					if(data["injectid"] !== undefined) {
						jQuery("#" + data["injectid"]).html(data["html"]);
					} else {
						jQuery(injectid).html(data["html"]);
					}
				}
			} else {
                if(params.ctx) {
					ctx.replaceHTML(data);
                } else {
                	if (params.replace /*|| jQuery(data["html"]).is(injectid.replace("#", "")) || jQuery(injectid, data["html"])*/) // TOCHECK
					    jQuery(injectid).replaceWith(data["html"]);
				    else
					    jQuery(injectid).html(data["html"]);
                }
			}
		} else {
			if(params.component === null && injectid == "#content" && data["injectid"] !== undefined) {
				jQuery("#" + data["injectid"]).replaceWith(data["html"]);
			} else {
				if (params.replace === undefined || params.replace === true)
					jQuery(injectid).replaceWith(data["html"]); //--errore-- problema con il record
				else
					jQuery(injectid).html(data["html"]); //--errore-- problema con il record
			}

			if (data["hidden"] !== undefined)
				jQuery("#" + params.component + "_hidden").replaceWith(data["hidden"]);
		}

		if (data["footers"] !== undefined) {
			var tmpdiv = ff.getUniqueID();
			//jQuery("body").append('<div id="fftmp' + tmpdiv + '">' + data["footers"] + '<script type="text/javascript">jQuery("#fftmp' + tmpdiv + '").remove();</script></div>');
			jQuery("body").append('<div id="fftmp' + tmpdiv + '">' + data["footers"] + '</div>');
			jQuery(function () {jQuery("#fftmp" + tmpdiv).remove();});
		}
        
		that.doEvent({
			"event_name" : "onUpdatedContent"
			, "event_params" : [params, data]
		});

		if (params.callback !== undefined && !params.chainupdate)
			params.callback(data, params.customdata);
		
		var res = that.doEvent({
			"event_name"	: "onRequestDone",
			"event_params"	: [data, params, injectid]
		});

		if (params.blockui === undefined || params.blockui) {
			ff.ajax.unblockUI(params.chainupdate, false, data);
		}
	} else if (data["url"] !== undefined) {
		if (doredirects) {
			var res = that.doEvent({
				"event_name"	: "onRedirect",
				"event_params"	: [data["url"], data, this.mydata, params]
			});

			if (res !== undefined && res[res.length - 1]) {
				if (params.blockui === undefined || params.blockui) {
					ff.ajax.unblockUI(params.chainupdate, false, data);
				}
				return;
			}

			top.location.href = data["url"];
			return true;
		}

		var res = that.doEvent({
			"event_name"	: "onRedirect",
			"event_params"	: [data["url"], data, this.mydata, params]
		});

		var lastres = null;
		if (res !== undefined && res[res.length - 1]) {
			lastres = res[res.length - 1];
			
			if (lastres['break']) {
				if (params.blockui === undefined || params.blockui) {
					ff.ajax.unblockUI(params.chainupdate, false, data);
				}
				return;
			}
		}
		
		if (params.callback !== undefined && !params.chainupdate) {
			var res = params.callback(data, params.customdata);
			if(res === "return") {
				if (params.blockui === undefined || params.blockui) {
					ff.ajax.unblockUI(params.chainupdate, false, data);
				}
				return true;
			}
		}
		
		if (lastres) {
			params.callback = lastres.callback;
		} /*else {  on redirect se abilitato manda in blocco l'unblock ui .... record modify --> redirect sullo stesso record --> agiorna --> si blocca la ui
			params.callback = undefined;
		}*/
		
		/*if(!params.chainupdate)
			ff.ajax.unblockUI(params.chainupdate, false, data); //se inserito nella if della callback l'activecombo nn triggera e nn seleziona il valore appena inserito

        if (params.callback !== undefined) {
            var res = params.callback(data, params.customdata);
            if(res === "return") {
                return true;
            }
        }*/

		var res = that.doEvent({
			"event_name"	: "onRequestDone",
			"event_params"	: [data, params, injectid]
		});
		if (res !== undefined && res[res.length - 1] && res[res.length - 1]['break']) {
			if (params.blockui === undefined || params.blockui) {
				ff.ajax.unblockUI(params.chainupdate, false, data);
			}
			return;
		}
		
		// do redirect
		var fields = [];
	
		if(data["component"] !== undefined && data["component"]) {
			fields.push({name: "XHR_COMPONENT", value: data["component"]});
			params.component = data["component"];
			
			if (data["section"] && jQuery("#" + params.component + "_" + data["section"]).attr("id") !== undefined) {
				fields.push({name: "XHR_SECTION", value: data["section"]});
				params.section = data["section"];
			}
			
			if(data["injectid"] !== undefined)
				params.injectid = "#" + data["injectid"];
		}
            
		if (params.ctx) {
            fields.push({name: "XHR_CTX_ID", value: params.ctx});
			var ctx = that.ctxGet(params.ctx);
			fields.push(
				{name: "XHR_CTX_TYPE", value: ctx.type}
			);
        }
			
		var dataType = (params.jsonp !== undefined ? (params.jsonp ? "jsonp json" : "json") : (ff.origin != ff.httpGetOrigin() ? "jsonp json" : "json"));

		jQuery.ajax({
			  "url"			: ff.fixPath(data["url"])
			, "cache"		: false
			, "async"		: true
			, "data"		: fields
			, "type"		: "GET"
			, "dataType"	: dataType
			, "jsonp"		: (dataType == "jsonp" ? "XHR_JSONP" : undefined)
			, "success"		: ff.ajax.onSuccess
			, "mydata"		: {
				"params"		: params,
				"parsed_url"	: ff.fixPath(data["url"]),
				"parsed_fields" : fields
			}
		});
	} else {
		if (params.callback !== undefined && !params.chainupdate) {
			if (params.blockui === undefined || params.blockui) {
				ff.ajax.unblockUI(params.chainupdate, false, data);
			}
			params.callback(data, params.customdata);
		}

		var res = that.doEvent({
			"event_name"	: "onRequestDone",
			"event_params"	: [data, params, injectid]
		});
		if (res !== undefined && res[res.length - 1] && res[res.length - 1]['break']) {
			if (params.blockui === undefined || params.blockui) {
				ff.ajax.unblockUI(params.chainupdate, false, data);
			}
			return;
		}
		
		if (doredirects) {
			top.location.reload(true);
			return true;
		}
		
		if (params.callback === undefined) {
			if (params.blockui === undefined || params.blockui) {
				ff.ajax.unblockUI(params.chainupdate, false, data);
			}
		}
	}
	return true;
},

}; // publics' end

ff.pluginAddInit("ff", function () {
	ff.addEvent({
		"event_name"  : "initIFElement"
		, "func_name" : that.ctxInitEvent
	});
});

return that;

// code's end.
})();
