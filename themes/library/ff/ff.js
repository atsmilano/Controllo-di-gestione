/**
 * Forms Framework Javascript Handling Object
 */

// Rewrites
if(!Array.indexOf){
	Array.prototype.indexOf = function(obj){
		for(var i=0; i<this.length; i++){
		    if(this[i]==obj){
		        return i;
		    }
		}
		return -1;
	}
}

Array.prototype.each = function(func){
	var tmp = this.clone();
	for(var i=0; i<tmp.length; i++){
		var rc = func(i, tmp[i]);
		if (rc)
			break;
	}
	return i;
}

Array.prototype.unbufferedEach = function(func){
	for(var i=0; i<this.length; i++){
		var rc = func(i, this[i]);
		if (rc)
			break;
	}
	return i;
}

Array.prototype.unset = function(i){
	this.splice(i, 1);
	return this;
}

Array.prototype.clone = function(){
	return this.slice();
}

String.prototype.trim = function (c) {
	if (c === undefined)
		return this.replace(/^\s+|\s+$/g, "");
	else
		return this.replace(new RegExp("^" + c.escapeRegExp()  + "+|" + c.escapeRegExp() + "+$", "g"), "");
}

String.prototype.rtrim = function (c) {
	if (c === undefined)
		return this.replace(/\s+$/g, "");
	else
		return this.replace(new RegExp(c.escapeRegExp() + "+$", "g"), "");
}

String.prototype.ltrim = function (c) {
	if (c === undefined)
		return this.replace(/^\s+/, "");
	else
        return this.replace(new RegExp("^" + c.escapeRegExp() + "+", "g"), "");
}

String.prototype.capitalize = function() {
    return this.replace( /(^|\s)([a-z])/g , function(m,p1,p2){
		return p1+p2.toUpperCase();
    });
};

String.prototype.escapeRegExp = function() {
    return this.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
};
String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.split(search).join(replacement);
};
String.prototype.parseUri = function() {
		// parseUri 1.2.2
		// (c) Steven Levithan <stevenlevithan.com>
		// MIT License

        var     o   = String.prototype.parseUri.options,
                m   = o.parser[o.strictMode ? "strict" : "loose"].exec(this),
                uri = {},
                i   = 14;

        while (i--) uri[o.key[i]] = m[i] || "";

        uri[o.q.name] = {};
        uri[o.key[12]].replace(o.q.parser, function ($0, $1, $2) {
                if ($1) uri[o.q.name][$1] = $2;
        });

        return uri;
};

String.prototype.parseUri.options = {
        strictMode: false,
        key: ["source","protocol","authority","userInfo","user","password","host","port","relative","path","directory","file","query","anchor"],
        q:   {
                name:   "queryKey",
                parser: /(?:^|&)([^&=]*)=?([^&]*)/g
        },
        parser: {
                strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
                loose:  /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*)(?::([^:@]*))?)?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
        }
};

String.prototype.escapeHtml = function() {
        var     o   = String.prototype.escapeHtml.options,
                m   = o.entityMap,
                re =  o.regexp;

    return String(this).replace(re, function (s) {
      return m[s];
    });
};

String.prototype.escapeHtml.options = {
        "entityMap": {
			"&": "&amp;",
			"<": "&lt;",
			">": "&gt;",
			'"': '&quot;',
			"'": '&#39;',
			"/": '&#x2F;'
		  },
        "regexp": /[&<>"'\/]/g,
};

(function(arr,i,name) {
  while(name = arr[i++]) {
    Math["$"+name] = Function("a","b","return Math."+name+"(a*(b=Math.pow(10,b||0)))/b");
  }
})(["floor","ceil","round"],0);

// jQuery addons

jQuery.fn.outerHTML = function(s) {
    return s
        ? this.before(s).remove()
        : jQuery("<p>").append(this.eq(0).clone()).html();
};

jQuery.fn.escape = function (str) {
	if (typeof str === "number")
		str = str.toString();
	if (typeof str !== "string")
		throw  "FF - Invalid param for jQuery.fn.escape";
	else
		return str.replace(/([;&,\.\+\*\~':"\!\^#$%@\[\]\(\)=>\|\/])/g, '\\$1');
};

jQuery.fn.escapeGet = function (str) {
	return jQuery("#" + jQuery.fn.escape(str));
};

jQuery.fn.comboGetSelIndex = function (id) {
	if (jQuery("#" + jQuery.fn.escape(id) + " optgroup").length)
		return jQuery("#" + jQuery.fn.escape(id) + " option:selected").index("option");
	else
		return jQuery("#" + jQuery.fn.escape(id) + " option:selected").index();
};

jQuery.fn.comboGetIndexByVal = function (id, value) {
	if (jQuery("#" + jQuery.fn.escape(id) + " optgroup").length)
		return jQuery("#" + jQuery.fn.escape(id) + " option[value='" + jQuery.fn.escape(ff.coalesce(value, "")) + "']").index("option");
	else
		return jQuery("#" + jQuery.fn.escape(id) + " option[value='" + jQuery.fn.escape(ff.coalesce(value, "")) + "']").index();
};

/* http://andreaslagerkvist.com/jquery/center/#jquery-plugin-source */
jQuery.fn.center = function (absolute) {
    return this.each(function () {
        var t = jQuery(this);

        t.css({
            position:    absolute ? 'absolute' : 'fixed', 
            left:        '50%', 
            top:        '50%'
            /*zIndex:        '99'*/
        }).css({
            marginLeft:    '-' + (t.outerWidth() / 2) + 'px', 
            marginTop:    '-' + (t.outerHeight() / 2) + 'px'
        });

        if (absolute) {
            t.css({
                marginTop:    parseInt(t.css('marginTop'), 10) + jQuery(window).scrollTop(), 
                marginLeft:    parseInt(t.css('marginLeft'), 10) + jQuery(window).scrollLeft()
            });
        }
    });
};

// Forms Framework Main Object
var ff = (function () {

// private vars
var inits_uuid		= undefined;

var unique_ids		= 0;

var libs = undefined;
var libs_deps = undefined;
//var libs_init = undefined;
var libs_rev_deps = undefined;
var plugins_loads	= undefined;
var plugins_inits	= undefined;

var getlibs_loads = undefined;

function initEvents(key) {
	var getType = {};
	var parts = key.split(".");
	if (parts.length > 1 && parts[0] == "ff") {
		// skip ff, because it's initialized during initFF' time
		var path = "ff";
		var ref = ff;
		for (var i = 1; i < parts.length; i++) {
			path += "." + parts[i];
			if (ref[parts[i]] === undefined)
				ref[parts[i]] = {};
			ref = ref[parts[i]];
			
			if (getType.toString.call(ref) === '[object Object]' && ref.events === undefined && ref.__ff === true) {
				jQuery.extend(ref, ff.ffEvents());
				extendLibs(ref, path);
			}
		}
	}
}

function extendLibs(ref, path) {
	var getType = {};
	for (var property in ref) {
		if (getType.toString.call(ref[property]) === '[object Object]' && ref[property].__ff === true) {
			var newpath = path + "." + property;
				if (libs.get("js").get(newpath) === undefined) {
					(function(p){ // needed for lambda style calling
						libs.get("js").set(p, {
							"loaded" : false
							, "source" : undefined
							, "callback" : function () {that.pluginInitLoad(p);}
							, "async" : undefined
							, "media" : undefined
							, "deps" : undefined
						});
					})(newpath);
					that.libLoaded("js", newpath, true);
					extendLibs(ref[property], newpath);
				}
		}
	}
}

var that = { // publics

"site_path"		: undefined,
"base_path"		: undefined,
"theme"			: undefined,
"theme_ui"		: undefined,
"page_path"		: undefined,
"language" 		: undefined, 
"layer" 		: undefined,
"frameworkCss"	: undefined,
"fontIcon"		: undefined,
"showfiles"     : undefined,
"group"         : undefined, 
"origin"		: undefined,
"domain"		: undefined,
"js_path"		: undefined,
"struct"		: undefined,
"getlibs"		: undefined,
"fn"			: {},
"modules"		: {
	"__ff" : true // provoca l'estensione con ffEvents
},

"initFF" : function (params) {
    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
    }
    that.site_path				= (params.site_path === undefined ? "/" : params.site_path);
    that.base_path				= (params.base_path === undefined ? that.site_path : params.base_path);
	that.theme					= (params.theme === undefined ? "default" : params.theme);
	that.theme_ui				= (params.theme_ui === undefined ? "" : params.theme_ui);
	
	if(that.site_path.length > 0 && that.site_path != '/') {
		that.page_path 			= window.location.pathname.substr(that.site_path.length);
	} else {
		that.page_path 			= window.location.pathname;
	}
	that.origin					= (params.origin !== undefined ? params.origin : that.httpGetOrigin());
	that.domain 				= window.location.hostname;
	that.language 				= getCookie("lang") || (params.language === undefined ? "ITA" : params.language);
	that.locale 				= (params.locale === undefined ? "it_IT" : params.locale);
	that.layer 					= (params.layer === undefined ? "empty" : params.layer);
    that.showfiles 				= (params.showfiles === undefined ? "/cm/showfiles.php" : params.showfiles);
	that.frameworkCss 			= params.frameworkCss;
	that.fontIcon				= params.fontIcon;
    that.group              	= getCookie("group") || (params.group === undefined ? "" : params.group);
	that.js_path				= (params.js_path === undefined ? that.site_path + '/themes/' + that.theme + '/javascript' : params.js_path);
	that.getlibs				= (params.getlibs === undefined ? that.site_path + '/services/getlibs' : params.getlibs);

	if(params.lazyImg) {
            jQuery(function() {
                ff.lazyImg();
            });
	}

	if (params.struct !== undefined)
		that.struct = params.struct;
	else {
		that.struct	= that.hash();
	}
	if (!that.struct.isset("fields"))
		that.struct.set("fields", that.hash())
	if (!that.struct.isset("comps"))
		that.struct.set("comps", that.hash());
	
	// used to avoid cache bug
	that.struct.get("comps").each(function (key, value, index) {
		value.url = window.location.href;
	});
	
	// ------------------------------------------------------------------------------------------------------
	//  Libraries 

	// add static (head) libs
	if (params.libs !== undefined) {
		params.libs.each(function (i, v) {
			if (libs.get(v.type) === undefined) libs.set(v.type, ff.hash());
			if (v.source === undefined) { // preloaded library, simply add it without loading, it will be initialized by ff.ffEvents initLoad (below)
				libs.get(v.type).set(v.id, {
					"loaded" : false
					, "source" : undefined
					, "callback" : (v.type === "js" ? function () {that.pluginInitLoad(v.id);} : undefined)
					, "async" : undefined
					, "media" : undefined
					, "deps" : v.deps
				});
				that.libDeps(v.type, v.id, v.deps, true);
			}
		});
	}

	// prepare to init static libs
	that.pluginAddInitLoad("ff.ffEvents", function () {
		jQuery.extend(ff, ff.ffEvents());
		jQuery.extend(ff.fn, ff.ffEvents());
		
		// ready to init every library
		if (params.libs !== undefined) {
			params.libs.each(function (i, v) {
				if (v.source === undefined && v.deps === undefined) {
					that.libLoaded(v.type, v.id, true);
				} else { // to load library
					if (v.type === "js")
						that.pluginLoad(v.id, v.source, v.callback, v.async, v.deps);
					else if (v.type === "css")
						that.injectCSS(v.id, v.source, v.callback, v.media);
					else
						throw "unhandled lib type: " + v.type;
				}
			});
		}
	});
	
	// ------------------------------------------------------------------------------------------------------
	// force main infrastructure loading at start (when not already present), because nothing will work without it
	
	if (jQuery === undefined)
		throw "Forms Framework require jQuery to work";
	
	if (libs.get("js") === undefined) libs.set("js", ff.hash());
	if (libs.get("css") === undefined) libs.set("css", ff.hash());
	
	if (!libs.get("js").isset("jquery")) {
		libs.get("js").set("jquery", {
			"loaded" : false
			, "source" : undefined
			, "callback" : function () {that.pluginInitLoad("jquery");}
			, "async" : undefined
			, "media" : undefined
			, "deps" : undefined
		});
		that.libDeps("js", "jquery", undefined, true);
	}
	
	var chk_ff = false;
	var chk_ev = false;
	var chk_evs = false;
	
	if (libs.isset("js")) {
		if (libs.get("js").isset("ff"))
			chk_ff = true;
		if (libs.get("js").isset("ff.ffEvent"))
			chk_ev = true;
		if (libs.get("js").isset("ff.ffEvents"))
			chk_evs = true;
	}
	
	if (!chk_ff) {
		libs.get("js").set("ff", {
			"loaded" : false
			, "source" : undefined
			, "callback" : function () {that.pluginInitLoad("ff");}
			, "async" : undefined
			, "media" : undefined
			, "deps" : ff.hash([{"id" : "js", "value" : ["ff.ffEvents"]}])
		});
		that.libDeps("js", "ff", ff.hash([{"id" : "js", "value" : ["ff.ffEvents"]}]), true);
	}
	if (!chk_ev)
		that.pluginLoad("ff.ffEvent",		"/themes/library/ff/ffEvent.js",	undefined, false);
	else
		that.libLoaded("js", "ff.ffEvent", true);
	if (!chk_evs)
		that.pluginLoad("ff.ffEvents",		"/themes/library/ff/ffEvents.js",	undefined, false);
	else
		that.libLoaded("js", "ff.ffEvents", true);
},

"getComp" : function (id) {
	return that.struct.get("comps").get(id);
},

"getField" : function (id, comp) {
	if (comp !== undefined) {
		var tmp = that.getComp(comp);
		if (tmp === undefined)
			return;
		
		return tmp.fields.get(id);
	} else {
		return that.struct.get("fields").get(id);
	}
},

"libLoadStatic" : function (type, id, deps)
{
	if (libs.get(type) === undefined) libs.set(type, ff.hash());
	libs.get(type).set(id, {
		"loaded" : false
		, "source" : undefined
		, "callback" : (type === "js" ? function () {that.pluginInitLoad(id);} : undefined)
		, "async" : undefined
		, "media" : undefined
		, "deps" : deps
	});
	if (that.libDeps(type, id, deps)) {
		that.libLoaded(type, id, true);
	}
},

"libSet" : function(type, id, source, callback, async, media, deps) {
	libs.get(type).set(id, {
		"loaded" : true
		, "source" : source
		, "callback" : callback
		, "async" : async
		, "media" : media
		, "deps" : deps
	});
},
"libSets" : function(type, ids, source, callback, async, media, deps) {
    for (var i = 0; i < ids.length; i++) {
        libs.get(type).set(ids[i], {
            "loaded": true
            , "source": source
            , "callback": callback
            , "async": async
            , "media": media
            , "deps": deps
        });
    }
},
"libLoad" : function (type, id, source, callback, async, media, deps) {
	var getType = {};
	if (getType.toString.call(type) === '[object Object]') {
		id = type["id"] || ff.getUniqueID();
		source = type["source"] || function() {throw "ff.libLoad: source is required"};
		callback = type["callback"];
		async = type["async"];
		media = type["media"];
		if (type["deps_js"] !== undefined || type["deps_css"] !== undefined) {
			deps = ff.hash();
			if (type["deps_js"] !== undefined)
				deps.set("js", type["deps_js"]);
			if (type["deps_css"] !== undefined)
				deps.set("css", type["deps_css"]);
		} else
			deps = type["deps"];
		
		type = type["type"] || function() {throw "ff.libLoad: type is required"};
	}
	
	if (libs.get(type) !== undefined && libs.get(type).get(id) !== undefined) {
		if (libs.get(type).get(id).loaded === true && callback !== undefined) // loaded true avviene quando è pronta
			callback(true); // vedere se spostare fuori (prima)
		return;
	}
	
	// prepara per il caricamento. loaded = false impedisce due caricamenti concorrenti
	if (libs.get(type) === undefined) libs.set(type, ff.hash());
	if (libs.get(type).get(id) === undefined) libs.get(type).set(id, {
		"loaded" : false
		, "source" : source
		, "callback" : callback
		, "async" : async
		, "media" : media
		, "deps" : deps
	});
	
	var ready = that.libDeps(type, id, deps);
	
	/*if (callback) {
		if (libs_init.get(type) === undefined) libs_init.set(type, ff.hash());
		if (libs_init.get(type).get(id) === undefined) libs_init.get(type).set(id, []);
		libs_init.get(type).get(id).push(callback);
	}*/
	
	if (ready) {
		that.libLoadFile(type, id, source, async);
	} else {
		// wait for, handled by that.libLoaded
	}
},

"libDeps" : function (type, id, deps, force) {
	var ready = true;
	if (deps !== undefined) {
		if (libs_deps.get(type) === undefined) libs_deps.set(type, ff.hash());
		if (libs_deps.get(type).get(id) === undefined) libs_deps.get(type).set(id, ff.hash());
		var tmp_deps = libs_deps.get(type).get(id);

		deps.each(function(k, v, i){
			if (libs.get(k) === undefined) libs.set(k, ff.hash());
			var tmp = libs.get(k);
			for (var c = 0; c < v.length; c++) {
				if (tmp.get(v[c]) === undefined || !tmp.get(v[c]).loaded || force) {
					ready = false;
					if (tmp_deps.get(k) === undefined) tmp_deps.set(k, ff.hash());
					tmp_deps.get(k).set(v[c], true);
					
					if (libs_rev_deps.get(k) === undefined) libs_rev_deps.set(k, ff.hash());
					if (libs_rev_deps.get(k).get(v[c]) === undefined) libs_rev_deps.get(k).set(v[c], ff.hash());
					if (libs_rev_deps.get(k).get(v[c]).get(type) === undefined) libs_rev_deps.get(k).get(v[c]).set(type, ff.hash());
					libs_rev_deps.get(k).get(v[c]).get(type).set(id, true);
				}
			}
		});
		
		if (!libs_deps.get(type).get(id).length) {
			libs_deps.get(type).unset(id);
			if (!libs_deps.get(type).length) {
				libs_deps.unset(type);
			}
		}
	}
	return ready;
},

"libDump" : function () {
	libs.dump();
	libs_deps.dump();
	libs_rev_deps.dump();
},

"libToString" : function (type) {
	return JSON.stringify(libs.get(type).keys);
},

"libIsLoaded" : function (type, id) {
	var tmp = libs.get(type).get(id);
	if (tmp) {
		return tmp.loaded;
	} else {
		return false;
	}
},

"libLoaded" : function (type, id, force) {
	var tmp = libs.get(type).get(id);
	if (tmp.loaded)
		return;
	
	tmp.loaded = true;
	if (tmp.callback !== undefined)
		tmp.callback(false);
	
	if (libs_rev_deps.get(type) === undefined || libs_rev_deps.get(type).get(id) === undefined) {
		return;
	}
	
	libs_rev_deps.get(type).get(id).each(function (t1, e1) {
		e1.each(function(t2, e2) {
			//clean rev
			if (libs_rev_deps.get(type) !== undefined && libs_rev_deps.get(type).get(id) !== undefined && libs_rev_deps.get(type).get(id).get(t1) !== undefined) {
				libs_rev_deps.get(type).get(id).get(t1).unset(t2);
				if (!libs_rev_deps.get(type).get(id).get(t1).length) libs_rev_deps.get(type).get(id).unset(t1);
				if (!libs_rev_deps.get(type).get(id).length) libs_rev_deps.get(type).unset(id);
				if (!libs_rev_deps.get(type).length) libs_rev_deps.unset(type);
			}
			
			if (libs_deps.get(t1) !== undefined && libs_deps.get(t1).get(t2) !== undefined) {
				libs_deps.get(t1).get(t2).get(type).unset(id);
				if (!libs_deps.get(t1).get(t2).get(type).length) libs_deps.get(t1).get(t2).unset(type);
				if (!libs_deps.get(t1).get(t2).length) {
					libs_deps.get(t1).unset(t2);
					if (!libs_deps.get(t1).length) libs_deps.unset(t1);
					var tmp = libs.get(t1).get(t2);
					if (force || tmp.source === undefined) {
						ff.libLoaded(t1, t2, force);
					} else if (!tmp.loaded) {
						ff.libLoadFile(t1, t2, tmp.source, tmp.async, tmp.media);
					}
				}
			}
		});
	});
	//that.libDump();
},

"libLoadFile" : function (type, id, source, async, media) {
	if (type === "js") {
		var getType = {};
		if (getType.toString.call(source) === '[object Function]') {
			source();
			ff.libLoaded(type, id);
		} else {
			jQuery.ajax({
				"async": async,
				"url": that.fixPath(source),
				"dataType": "script", //TOCHECK, cosi facendo tutte le request non vengono mai cachate perche jquery aggiunge ?_[microtime] . E voluto?
				//"cache": false,
				"success": function (data) {
					//jQuery("head").append('<script type="text/javascript">ff.libLoaded("' + type + '", "' + id + '");</script>');
					jQuery(function () {ff.libLoaded(type, id)});
				}
			});
		}
		/*
		if (async || async === undefined) {
			jQuery.getScript(
				that.fixPath(source),
				function (data) {
					jQuery("head").append('<script type="text/javascript">ff.libLoaded("' + type + '", "' + id + '");</script>');
				}
			);
		} else {
			jQuery("head").append('<script type="text/javascript" src="' + that.fixPath(source) + '"></script>');
			jQuery("head").append('<script type="text/javascript">jQuery(function() {ff.libLoaded("' + type + '", "' + id + '");});</script>');
		}*/
	} else if (type === "css") {
		if (async) {
			that.CSSload(that.fixPath(source), media, function () {ff.libLoaded(type, id);}); // indispensabile per via della callback
		} else {
			if ('\v' == 'v') /* ie only */ {
				var css = document.createStyleSheet(that.fixPath(source));

				if(media !== undefined) 
					css.media = media;
			} else {
				var css = jQuery('<link rel="stylesheet" href="' + that.fixPath(source) + '" ' + attrMedia + ' />');
				jQuery('head').append(css);
			}
			ff.libLoaded(type, id);
		}
			
		/*
		if(media !== undefined) {
			var attrMedia = 'media="' + media + '"';
		}

		var css = jQuery('<link rel="stylesheet" href="' + that.fixPath(source) + '" ' + attrMedia + ' />');
		jQuery('head').append(css);

		CSSload(css.get(0), function () {ff.libLoaded(type, id);});

		var async = true;
		var css = document.createElement('link');
		css.setAttribute('rel', 'stylesheet');
		css.setAttribute('href', source);
		css.addEventListener('load', function() { alert('foo'); }, false);
		document.getElementsByTagName('head')[0].appendChild(css);

		jQuery.ajax({
				"async": async,
				"url": source,
				"dataType": "text",
				"success": function (data) {
	//					jQuery("head").append('<style type="text/css">' + data + '</style>');
					if (callback !== undefined)
						callback();
				}
			});*/
	} else {
		throw "Unhandled lib type: " + type;
	}
},

"CSSload" : function (src, media, handler, nonblocking) {
	/*Based on https://github.com/filamentgroup/loadCSS/blob/master/src/loadCSS.js */

	var nonblocking = (nonblocking !== undefined ? nonblocking : true);

	var doc = window.document;
	var ss = doc.createElement( "link" );
	var refs = ( doc.body || doc.getElementsByTagName( "head" )[ 0 ] ).childNodes;
	var ref = refs[ refs.length - 1];

	var sheets = doc.styleSheets;
	ss.rel = "stylesheet";
	ss.href = src;
	// temporarily set media to something inapplicable to ensure it'll fetch without blocking render
	ss.media = nonblocking ? "only x" : media;

	// wait until body is defined before injecting link. This ensures a non-blocking load in IE11.
	function ready( cb ){
		if( doc.body ){
			return cb();
		}
		setTimeout(function(){
			ready( cb );
		});
	}
	// Inject link
	// Note: `insertBefore` is used instead of `appendChild`, for safety re: http://www.paulirish.com/2011/surefire-dom-element-insertion/
	ready( function(){
		ref.parentNode.insertBefore( ss, ref.nextSibling );
	});
	// A method (exposed on return object for external use) that mimics onload by polling document.styleSheets until it includes the new sheet.
	var onloadcssdefined = function( cb ){
		var resolvedHref = ss.href;
		var i = sheets.length;
		while( i-- ){
			if( sheets[ i ].href === resolvedHref ){
				return cb();
			}
		}
		setTimeout(function() {
			onloadcssdefined( cb );
		});
	};

	function loadCB(){
		if( ss.addEventListener ){
			ss.removeEventListener( "load", loadCB );
		}
		if (nonblocking) { 
			ss.media = media || "all"; 
		}
		handler();
	}

	// once loaded, set link's media back to `all` so that the stylesheet applies once it loads
	if( ss.addEventListener ){
		ss.addEventListener( "load", loadCB);
	}
	ss.onloadcssdefined = onloadcssdefined;
	onloadcssdefined( loadCB );
},

"pluginLoad" : function (id, source, callback, async, deps) {
	if (callback !== undefined)
		that.pluginAddInitLoad(id, callback);
	
	that.libLoad("js", id, source, function(already_loaded) {
		if (!already_loaded) {
			ff.pluginInitLoad(id);
		}
	}, async, undefined, deps);
},

"pluginAddInitLoad" : function (id, callback, uuid) {
	if (uuid !== undefined) {
		if (inits_uuid.isset(uuid))
			return;
		else
			inits_uuid.set(uuid);
	}
	
	if (libs.get("js") === undefined || libs.get("js").get(id) === undefined || !libs.get("js").get(id).loaded) {
		if (!plugins_loads.isset(id))
			plugins_loads.set(id, []);
		
		plugins_loads.get(id).push(callback);
	} else
		callback();
},

"pluginAddInit" : function (id, callback, uuid) {
	if (uuid !== undefined) {
		if (inits_uuid.isset(uuid))
			return;
		else
			inits_uuid.set(uuid);
	}
	
	if (libs.get("js") === undefined || libs.get("js").get(id) === undefined || !libs.get("js").get(id).loaded) {
		if (!plugins_inits.isset(id))
			plugins_inits.set(id, []);

		plugins_inits.get(id).push(callback);
	} else
		callback();
},

"pluginInitLoad" : function (id) {
	if (id !== "ff.ffEvent" && id !== "ff.ffEvents")
		initEvents(id);

	var loads;
	if ((loads = plugins_loads.get(id)) !== undefined) {
		for (var i = 0; i < loads.length; i++) {
			loads[i]();
		}
		plugins_loads.set(id, []);
	}
	that.pluginInit(id);
},

"pluginInit" : function (id) {
	var inits ;

	if ((inits = plugins_inits.get(id)) !== undefined) {
		for (var i = 0; i < inits.length; i++) {
			inits[i]();
		}
		plugins_inits.set(id, []);
	}
},

"injectCSS" : function (id, source, callback, media, async) {
	that.libLoad("css", id, source, function(already_loaded) {
		if (callback !== undefined) 
			callback();
	}, async, media, undefined);
},

"getLibs" : function (name, object, theme, type) {
	var uid = (name || "") + (object || "") + (theme || "") + (type || "");
	if (getlibs_loads.isset(uid))
		return false;
		
	getlibs_loads.set(uid, true);

    var res = ff.doEvent({
        "event_name"	: "getLibs",
        "event_params"	: [name, object, theme, type]
    });

    if (!res || !res[res.length - 1]) {
        var tmp_name = name;
        var tmp_obj = object;

        if (object === undefined) {
            var tmp = name.replace("ff.", "").split(".");
            if (tmp.length > 1) {
                tmp_name = tmp[tmp.length - 1];
                tmp_obj = tmp[0];
            } else {
                tmp_obj = "ffPage";
            }
        }

        var tmp_url = that.getlibs + "?";
        if (type == undefined)
            tmp_url += "widgets";
        else
            tmp_url += type;
        tmp_url += "=";
        if (tmp_obj) {
            tmp_url += tmp_obj + "/";
        }
        tmp_url += tmp_name;

        if (theme)
            tmp_url += "&theme=" + theme;

        var tmpdiv = ff.getUniqueID();
        jQuery("body").append('<div id="fftmp' + tmpdiv + '"></div>');

        ff.ajax.doRequest({
            "url": tmp_url
            , "injectid": "#" + tmpdiv
            , "stickycomp": true
            , "callback": function () {
                jQuery("#fftmp" + tmpdiv).remove();
            }
        });
    }

	return true;
},
"load" : function(plugin, callback, object, uuid) {
	var getType = {};
	object = object || "";
	if(getType.toString.call(plugin) === '[object Object]') {
		for(var i = 0; i < plugin.length; i++) {
			if (!ff.libIsLoaded("js", plugin[i]))
				ff.getLibs(plugin[i], object);		
		}
	} else {
		if (!ff.libIsLoaded("js", plugin))
			ff.getLibs(plugin, object);
	}
	if(callback)
		ff.pluginAddInit(plugin, callback, uuid);
},
"extend" : function (object) {
	jQuery.extend(ff, object);
},

"coalesce" : function (value, ifnull) {
	if (value === null)
		return ifnull;
	return value;
},
	
"getUniqueID" : function() {
	return unique_ids++;
},

"httpGetOrigin" : function(url) {
	if (url === undefined)
		return window.location.protocol + "//" + window.location.hostname;
	
	var tmp = url.parseUri();
	if (tmp.host === "")
		return window.location.protocol + "//" + window.location.hostname;
	
	return tmp.protocol + "://" + tmp.host;
},

"getURLParameter" : function(name, url) {
	var haystack = location.search;
	if (url !== undefined) {
		haystack = url.parseUri().query;
	}
    var tmp = (RegExp(name.replace(/\[/g, "\\[").replace(/\]/g, "\\]") + '=' + '(.+?)(&|$)').exec(haystack)||[,null])[1];
    if (tmp !== null) {
		return decodeURIComponent(tmp);
	} else {
		return null;
	}
},

"encodeURI" : function (str) {
	/*
	 * follow the more recent RFC3986 for URL's, making square brackets reserved 
	 * (for IPv6) and thus not encoded 
	 * when forming something which could be part of a URL (such as a host)
	 * 
	 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/encodeURIComponent
	 */
    return encodeURI(str).replace(/%5B/g, '[').replace(/%5D/g, ']');
},

"encodeURIComponent" : function (str) {
	/*
	 * more stringent in adhering to RFC 3986 (which reserves !, ', (, ), and *)
	 * even though these characters have no formalized URI delimiting uses
	 * 
	 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/encodeURI
	 */
    return encodeURIComponent(str).
        // Note that although RFC3986 reserves "!", RFC5987 does not,
        // so we do not need to escape it
        replace(/['()]/g, escape). // i.e., %27 %28 %29
        replace(/\*/g, '%2A').
            // The following are not required for percent-encoding per RFC5987, 
            //  so we can allow for a little better readability over the wire: |`^
            replace(/%(?:7C|60|5E)/g, unescape);
},

"doubleEncodeURIComponent" : function (str) {
	return encodeURIComponent(ff.encodeURIComponent(str));
},

"urlAddParam" : function(url, name, value) {
	var parts = url.split("#");

	var url = parts[0];
	var anchor = parts[1];

	parts = url.split("?");
	url = parts[0];
	var query = parts[1];

	url += "?" + name;
	if (value !== undefined)
		url += "=" + ff.encodeURIComponent(value);
	url += "&";
	
	if (query) {
		parts = query.trim("&").split("&");
		var newquery = "";
		for (var i = 0; i < parts.length; i++) {
			var subparts = parts[i].split("=");
			if (subparts[0] != name) {
				newquery = newquery + subparts[0];

				if (subparts[1] !== undefined)
					newquery += "=" + subparts[1];

				newquery += "&";
			}
		}
		
		url = url + newquery;
	}

	if (anchor !== undefined)
		url = url + "#" + anchor;
	
	return url;
},

"argsAsArray" : function (args) {
	var arrArgs = [];
	for (p in args) {
		if (args.hasOwnProperty(p))
			arrArgs.push(args[p]);
	}
	return arrArgs;
},

"fixPath" : function(source) {
	if(that.site_path !== undefined && that.site_path.length > 0 && source.indexOf("/") == 0 && source.indexOf(that.site_path) != 0
		&& source.indexOf(that.base_path) != 0
	)
		source = that.site_path + source;
	
	var req_origin = null;
	var ret = source.parseUri();
	if (ret.host != "")
		req_origin = (ret.protocol == "" ? "http" : ret.protocol) + "://" + ret.host;
	else if(that.origin !== undefined && that.origin.length > 0)
		req_origin = that.origin;

	var cross_xhr = false;
	if(req_origin !== null  && that.httpGetOrigin() != req_origin) {
		var cross_xhr = true;
		
		if (source.indexOf(req_origin) != 0)
			source = req_origin + source;
		
		source = ff.urlAddParam(source, "__FORCE_XHR__");
	}
	
	if (xdbg = that.getURLParameter("XDEBUG_SESSION_START")) {
		source = ff.urlAddParam(source, "XDEBUG_SESSION_START", xdbg);
	}
	
	if (source.indexOf("/ff/ffEvents.js") !== -1 && source.indexOf("/ff/ffEvent.js") !== -1) {
		var res = ff.doEvent({
			"event_name"	: "fixPath",
			"event_params"	: [source, cross_xhr]
		});
		
		if (res !== undefined && res[res.length - 1]) {
			source = res[res.length - 1];
		}
	}
	
	return source;
},

"addLoadEvent" : function (func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			if (oldonload) {
				oldonload();
			}
			func();
		}
	}
},

"submitProcessKey" : function (e, button) {
    if (null == e)
        e = window.event;
    if (e.keyCode == 13)  {
    	if(typeof button == "object") { // semplicistico
    		jQuery(button).focus();
	        jQuery(button).click();
    	} else {
    		document.getElementById(button).focus();
	        document.getElementById(button).click();
    	}
        return false;
    }
},

"clearComponent" : function (component) {
	var pComp = ff.struct.get("comps").get(component);
	if (!pComp)
		return;

	ff.doEvent({
		"event_name"	: "onClearComponent",
		"event_params"	: [component]
	});

	// reset delle restanti widgets processate (NB: potrebbe essere ridondante rispetto al ciclo precedente, occorre tenerne presente nella gestione dell'evento)
	if (pComp.widgets && pComp.widgets.length) pComp.widgets.each(function (i, v) {
		ff.doEvent({
			"event_name"	: "onClearField",
			"event_params"	: [component, undefined, {"widget" : v.type}, v.id]
		});
	});

	ff.struct.get("comps").unset(component);
},

"utf8" : {
	"encode" : function(s) {
	  return unescape(encodeURIComponent(s));
	},

	"decode" : function(s) {
	  return decodeURIComponent(escape(s));
	}
},
"encodeEntities" : function(s) {
	return jQuery("<div/>").text(s).html();
},
"decodeEntities" : function(s) {
	return jQuery("<div/>").html(s).text(); 
},
"getFields" : function (container, ctx) {
	that.doEvent({"event_name": "getFields", "event_params" : [container, ctx]});

	if (container !== undefined)
		return jQuery(':input[name][name!=""]', container).not("input:checkbox:not(:checked)").not("input:radio:not(:checked)");
	else
		return jQuery(':input[name][name!=""]').not("input:checkbox:not(:checked)").not("input:radio:not(:checked)");

},
"numberToCurrency" : function(number, decimalSeparator, thousandsSeparator, nDecimalDigits){
    //default values
	decimalSeparator = decimalSeparator === undefined ? "," : decimalSeparator;
	thousandsSeparator = thousandsSeparator === undefined ? "." : thousandsSeparator;
    nDecimalDigits = nDecimalDigits === undefined ? 2 : nDecimalDigits;

    var fixed = number.toFixed(nDecimalDigits), //limit/add decimal digits
        parts = new RegExp('^(-?\\d{1,3})((?:\\d{3})+)(\\.(\\d{'+ nDecimalDigits +'}))?$').exec( fixed ); //separate begin [$1], middle [$2] and decimal digits [$4]

    if(parts){ //number >= 1000 || number <= -1000
        return parts[1] + parts[2].replace(/\d{3}/g, thousandsSeparator + '$&') + (parts[4] ? decimalSeparator + parts[4] : '');
    }else{
        return fixed.replace('.', decimalSeparator);
     }
},
"isMobile" : function(type) {
	var res = false;
	
    switch(type) {
		case "Android":
			res = navigator.userAgent.match(/Android/i);
		case "BlackBerry":
			res = navigator.userAgent.match(/BlackBerry/i);
		case "iOS":
			res = navigator.userAgent.match(/iPhone|iPad|iPod/i);
		case "iPad":
			res = navigator.userAgent.match(/iPad/i);
		case "Opera":
			res = navigator.userAgent.match(/Opera Mini/i);
		case "Windows":
			res = navigator.userAgent.match(/IEMobile/i);
		default:		
			res = navigator.userAgent.match(/Android|BlackBerry|iPhone|iPad|iPod|Opera Mini|IEMobile/i);	
    }
    return res;
},
"browser" : function ( ua ) {
  var matched, browser;

  if(ua === undefined)
  	ua = nagivator.userAgent;

    ua = ua.toLowerCase();

    matched = ff.getBrowser( ua );
    browser = {};

    if ( matched.browser ) {
        browser[ matched.browser ] = true;
        browser.version = matched.version;
    }

    // Chrome is Webkit, but Webkit is also Safari.
    if ( browser.chrome ) {
        browser.webkit = true;
    } else if ( browser.webkit ) {
        browser.safari = true;
    }

    return browser;
},
"getBrowser" : function( ua ) {
    // Use of jQuery.browser is frowned upon.
    // More details: http://api.jquery.com/jQuery.browser
    ua = ua.toLowerCase();

    var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
        /(webkit)[ \/]([\w.]+)/.exec( ua ) ||
        /(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
        /(msie) ([\w.]+)/.exec( ua ) ||
        ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
        [];

    return {
        browser: match[ 1 ] || "",
        version: match[ 2 ] || "0"
    };
},
"slug" : function(slugcontent) {
    slugcontent = slugcontent.toLowerCase();
    
    slugcontent = slugcontent.replace(/[\xC0-\xC5\xE0-\xE5]/g, 'a'); /* accentate tradotte nella versione normale*/
    slugcontent = slugcontent.replace(/[\xC8-\xCB\xE8-\xEB]/g, 'e');
    slugcontent = slugcontent.replace(/[\xCC-\xCF\xEC-\xEF]/g, 'i');
    slugcontent = slugcontent.replace(/[\xD2-\xD6\xF2-\xF6]/g, 'o');
    slugcontent = slugcontent.replace(/[\xD9-\xDC\xF9-\xFC]/g, 'u');
    slugcontent = slugcontent.replace(/[\x9F\xDD\xFD\xFF]/g, 'y');
    slugcontent = slugcontent.replace(/[\x8E\x9E]/g, 'z');
    slugcontent = slugcontent.replace(/[\x8A\x9A]/g, 's');
    slugcontent = slugcontent.replace(/[\xD1\xF1]/g, 'n');

    slugcontent = slugcontent.replace(/[^a-z0-9]/g, '-'); /* tutti i non-alfanumerici con uno trattino*/
    slugcontent = slugcontent.replace(/\-+/g, '-'); /* piÃƒÂ¹ di un trattino con un solo trattino*/
    slugcontent = slugcontent.replace(/^\-/g,''); /* rimuove eventuali trattini iniziali*/
    slugcontent = slugcontent.replace(/\-$/g,''); /* rimuove eventuali trattini finali*/ 
    
    return slugcontent;
},
"inView" : function(el, margin) {
 if(!margin)
	margin = 1;

 if(typeof el == "string")
 	el = jQuery(el)[0];
 
 if(typeof el == "object") {
	 var rect = el.getBoundingClientRect();
         var wh = parseInt(window.innerHeight || document.documentElement.clientHeight);

	 if(margin)
	 	margin = wh * parseFloat(margin); 

     return (
	    (rect.top + rect.height + margin >= 0
                && rect.top <= wh + margin 
            )
           /* && (rect.left >= 0
                || rect.left + margin <= (window.innerWidth || document.documentElement.clientWidth)
            )*/
	 )
 }
},
"lazyImg" : function(){
    var images = jQuery('img.lazy, iframe.lazy');

    jQuery(window).unbind("scroll.lazyImg");
    jQuery(images).load(function() {
        var placeholder = jQuery(this).prev(".lazyloader").get(0) || jQuery(this).parent().prev(".lazyloader").get(0);
        if(placeholder)
            jQuery(placeholder).remove();
    });

    function loadImage (el, fn) {
        var src = el.getAttribute('data-src');
		if(src) {
            el.setAttribute("src", src);
            el.removeAttribute('data-src');

            fn ? fn() : null;
		}
    };

    var processScroll = function(){
      for (var i = 0; i < images.length; i++) {
        var placeholder = jQuery(images[i]).prevAll(".lazyloader").get(0) || jQuery(images[i]).parent().prevAll(".lazyloader").get(0);
        
        if (/*jQuery(images[i]).is(":hidden") ||*/ ff.inView(placeholder || images[i])) {
          loadImage(images[i], function () { 
            jQuery(images[i]).prevAll("source").each(function() {
                var srcset = jQuery(this).attr('data-srcset');
                jQuery(this).attr("srcset", srcset);
		jQuery(this).removeAttr('data-srcset');
            });
            jQuery(images[i]).removeClass("lazy");

            //if(jQuery(images[i]).is(":hidden") && placeholder)
             //   placeholder.remove();
                
            images.splice(i, 1);
            i--;
          });
        }
      }

      if(!images.length) 
      	jQuery(window).unbind("scroll.lazyImg");
    };
    
    if(images.length) {
        jQuery(window).bind("scroll.lazyImg", processScroll); 
        processScroll();
    }
},

// *******************************
//  Hash Table - version 1.8.1

"hash" : function (initdata) {
	// privates
	
	var that = {
	// publics
	"__class__"	: "hash",

	"keys"		: [],
	"values"	: [],
	"length"	: 0,

	//functions
	"set" : function (key, value) {
		var index = that.keys.indexOf(key);
		if (index > -1) {
			that.values[index] = value;
			return index;
		} else {
			that.keys.push(key);
			that.values.push(value);
			that.length++;
			return that.length - 1;
		}
	},

	"indexset" : function (index, value) {
		if (index > -1 && index < that.length) {
			that.values[index] = value;
			return true;
		} else {
			return undefined;
		}
	},

	"get" : function (key) {
		var index = that.keys.indexOf(key);
		if (index > -1)
			return that.values[index];
		else
			return undefined;
	},

	"getindex" : function (key) {
		var index = that.keys.indexOf(key);
		if (index > -1)
			return index;
		else
			return undefined;
	},
	
	"indexget" : function (index) {
		if (index > -1 && index < that.length)
			return that.values[index];
		else
			return undefined;
	},

	"indexgetkey" : function (index) {
		if (index > -1 && index < that.length)
			return that.keys[index];
		else
			return undefined;
	},

	"isset" : function (key) {
		if (that.keys.indexOf(key) > -1)
			return true;
		else
			return false;
	},

	"find" : function (value, offset) {
		var index = that.values.indexOf.apply(that.values, arguments);
		if (index > -1) {
			return {"index" : index, "key" : that.keys[index]};
		} else {
			return undefined;
		}
	},

	"keyfind" : function (value, startingkey) {
		var index;

		if (startingkey === undefined)
			index = that.values.indexOf(value);
		else {
			index = that.keys.indexOf(startingkey);
			if (index > -1)
				index = that.values.indexOf(value, index);
			else
				return undefined;
		}

		if (index > -1) {
			return {"index" : index, "key" : that.keys[index]};
		} else {
			return undefined;
		}
	},

	"unset" : function (key) {
		var index = that.keys.indexOf(key);
		if (index > -1) {
			that.keys.splice(index, 1);
			that.values.splice(index, 1);
			that.length--;
			return that.length;
		} else {
			return undefined;
		}
	},

	"indexunset" : function (index) {
		if (index > 0 && index < that.length)
			return undefined;
		else {
			that.keys.splice(index, 1);
			that.values.splice(index, 1);
			that.length--;
			return that.length;
		}
	},

	"each" : function (func) {
		var tmp_keys	= that.keys.clone();
		var tmp_values	= that.values.clone();
		var l = tmp_keys.length;

		for (var i = 0; i < l; i++) {
			$rc = func(tmp_keys[i], tmp_values[i], i);
			if ($rc)
				break;
		}

		return l;
	},

	"flip" : function () {
		var tmp = ff.hash();

		for (var i = 0; i < that.length; i++) {
			tmp.set(that.values[i], that.keys[i]);
		}
		
		that.clear();
		
		that.keys = tmp.keys.clone();
		that.values = tmp.values.clone();
		that.length = tmp.length;
		
		tmp.clear();
		
		return that;
	},

	"clear" : function () {
		that.keys.length = 0;
		that.values.length = 0;
		that.length = 0;
		return that;
	},

	"merge" : function (obj) {
		obj.each(function (key, value, index) {
			if (typeof(value) == "object" && value.__class__ == "hash")
			{
				var tmp = that.get(key);
				if (typeof(tmp) == "object" && tmp.__class__ == "hash")
					tmp.merge(value);
				else
					that.set(key, value);
			}
			else
				that.set(key, value);
		});
		return that;
	},
	
	"dump" : function (prefix, document) {
		prefix = (prefix === undefined ? "" : prefix);
		that.each(function (key, value, index) {
			if (prefix) {
				if (document)
					window.jQuery("body").append(prefix + "&gt; " + index + " " + key + " " + (Object.prototype.toString.call(value) == "[object Object]" ? (value.__class__ == "hash" ? value : JSON.stringify(value)) : value) + "<br />");
				else
					console.log(prefix + ">", index, key, value);
			} else {
				if (document)
					window.jQuery("body").append(index + " " + key + " " + (Object.prototype.toString.call(value) == "[object Object]" ? (value.__class__ == "hash" ? value : JSON.stringify(value)) : value) + "<br />");
				else
					console.log(index, key, value);
			}
			if (Object.prototype.toString.call(value) == "[object Object]" && value.__class__ == "hash")
				value.dump(prefix + "--", document);
		});
	},
	
	"export" : function () {
		var export_pairs = [];
		
		that.each(function (key, value, index) {
			if (Object.prototype.toString.call(value) == "[object Object]" && value.__class__ == "hash")
				value = value.export();
			
			export_pairs.push({
				"name" : key,
				"value" : value
			});
		});
		
		return export_pairs;
	},
	
	};
	
// constructor
if (Object.prototype.toString.call(initdata) == "[object Object]") {
	if (initdata.__class__ == "hash") {
		that.keys	= initdata.keys;
		that.length = initdata.length;

		for (var i = 0; i < initdata.values.length; i++) {
			if (Object.prototype.toString.call(initdata.values[i]) == "[object Object]" && initdata.values[i].__class__ == "hash")
				that.values[i] = ff.hash(initdata.values[i]);
			else
				that.values[i] = initdata.values[i];
		}
	}
} else if (Object.prototype.toString.call( initdata ) === '[object Array]') {
	for (i = 0; i < initdata.length; i++) {
		if (typeof(initdata[i]) === "object") {
			if (initdata[i].__class__ === "hash")
				that.set(that.length, ff.hash(initdata[i]));
			else if (initdata[i].id !== undefined)
				that.set(initdata[i].id, initdata[i].value);
			else if (initdata[i].name !== undefined)
				that.set(initdata[i].name, initdata[i].value);
			else
				that.set(that.length, initdata[i]);
		} else {
			that.set(that.length, initdata[i]);
		}
	};
}

return that;
}
// ********* Hash Table *********

}; // publics' end

// hash initializations
plugins_loads	= that.hash();
plugins_inits	= that.hash();
inits_uuid		= that.hash();
libs			= that.hash();
libs_deps		= that.hash();
//libs_init		= that.hash();
libs_rev_deps	= that.hash();
getlibs_loads	= that.hash();

return that;

// code' end.
})();
