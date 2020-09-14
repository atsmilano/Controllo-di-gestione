/**
 * Forms Framework Javascript Handling Object
 *	ffPageNavigator' namespace
 */

ff.ffPageNavigator = (function () {


// inits
ff.pluginAddInit("ff.ajax", function () {
	ff.ajax.addEvent({
		"event_name"	: "onUpdatedContent",
		"func_name"		: function (params, data) {
			if (params.component !== undefined && params.section == "GridData" && ff.struct.get(params.component).type == "ffGrid") {
				ff.ffPageNavigator.updateButtons(params.component, data["rows"], data["page"]);
			}
		}
	});
});

// privates
var navigators = [];

function calcPage(id) {
	// adjust values
    if(navigators[id].page_per_frame > 0) {
	    navigators[id].half_frame		= Math.floor(navigators[id].page_per_frame / 2);
	    navigators[id].page_per_frame	= (navigators[id].half_frame * 2) + 1;
    }

	navigators[id].totpage = Math.ceil(navigators[id].totrec / navigators[id].rec_per_page);
	navigators[id].totframe = Math.ceil(navigators[id].totpage / navigators[id].page_per_frame);

	if (navigators[id].page > navigators[id].totpage)
		navigators[id].page = navigators[id].totpage;
	else if (navigators[id].page < 1)
		navigators[id].page = 1;

	if (navigators[id].totpage > navigators[id].page_per_frame) {
		navigators[id].start_page = navigators[id].page - navigators[id].half_frame;
		if (navigators[id].start_page < 1)
			navigators[id].start_page = 1;

		navigators[id].end_page = navigators[id].start_page + navigators[id].page_per_frame - 1;
		if (navigators[id].end_page > navigators[id].totpage)
			navigators[id].end_page = navigators[id].totpage;

		navigators[id].start_page = navigators[id].end_page - navigators[id].page_per_frame + 1;
	} else {
		navigators[id].start_page = 1;
		navigators[id].end_page = navigators[id].totpage;
	}
}

function drawButtons(id) {
    var processed_code_template = '';
	var component = navigators[id]["component"];
	
    jQuery("." + component).find(".totpage").html(navigators[id].totpage);
	if(navigators[id].totpage == 1) {
		jQuery("." + component + " .pages LI").addClass("hidden");
		jQuery("." + component + " .page").parent().remove();
        jQuery("." + component + " .choice").addClass("hidden");
        jQuery("." + component).each(function () {    
			if(parseInt(jQuery(".perPage li:first a", this).attr("rel")) >= navigators[id].totrec) {
				jQuery(".perPage", this).addClass("hidden");
			} else {
				jQuery(".perPage", this).removeClass("hidden");
				jQuery(".perPage li", this).removeClass(navigators[id].currentClass);
				jQuery(".perPage a", this).each(function() {
					var recPerPage = parseInt(jQuery(this).attr("rel"));
				
					if(recPerPage > navigators[id].totrec) {
						jQuery(this).parent().addClass("hidden");
					} else {
						if(recPerPage == navigators[id].totrec && jQuery(".perPage a.rec-all", this).length && !jQuery(this).hasClass("rec-all"))
							jQuery(this).parent().addClass("hidden");
						else
							jQuery(this).parent().removeClass("hidden");
						if(recPerPage == navigators[id].rec_per_page)
							jQuery(this).parent().addClass(navigators[id].currentClass);
					}
				});
			}
            
        });
	} else {
		jQuery("." + component + " .pages").addClass("hidden");
        for (var i = navigators[id].start_page; i <= navigators[id].end_page; i++) {
            if(i > navigators[id].page) {
                tplProperties = ' rel="next"';
                tplAddClass = '';
            } else if(i < navigators[id].page) {
                tplProperties = ' rel="prev"';        
                tplAddClass = '';
            } else {
                tplProperties = '';
                tplAddClass = ' class="' + navigators[id].currentClass + '"';
            }
            processed_code_template += '<li' + tplAddClass + '><a class="page" href="' + ff.ffPageNavigator.updateUriParams("page", (i > 1 ? i : "")) + '" data-page="' + i + '"' + tplProperties + '>' + i + '</a></li>';
        }
        jQuery("." + component).each(function () {    
            jQuery(".page", this).parent().remove();
            jQuery(".pinject", this).before(processed_code_template);    

            jQuery(".pages", this).removeClass("hidden");
            jQuery(".choice", this).removeClass("hidden");

			if(parseInt(jQuery(".perPage li:first a", this).attr("rel")) >= navigators[id].totrec) {
				jQuery(".perPage", this).addClass("hidden");
			} else {
				jQuery(".perPage", this).removeClass("hidden");
				jQuery(".perPage li", this).removeClass(navigators[id].currentClass);
				jQuery(".perPage a", this).each(function() {
					var recPerPage = parseInt(jQuery(this).attr("rel"));
				
					if(recPerPage > navigators[id].totrec) {
						jQuery(this).parent().addClass("hidden");
					} else {
						if(recPerPage == navigators[id].totrec && jQuery(".perPage a.rec-all", this).length && !jQuery(this).hasClass("rec-all"))
							jQuery(this).parent().addClass("hidden");
						else
							jQuery(this).parent().removeClass("hidden");
						if(recPerPage == navigators[id].rec_per_page)
							jQuery(this).parent().addClass(navigators[id].currentClass);
					}
				});
			}
        });
	}

	jQuery("." + component).find(".currentpage").val(navigators[id].page);
	jQuery("." + component).find(".totelem").children("span").text(navigators[id].totrec);

    return false;   
}

function eventButtons(id) {
	var component = navigators[id]["component"];
    jQuery("." + component).each(function () { 
        jQuery("*", this).unbind(".ff.ffPageNavigator");

        if(navigators[id].doAjax) {
            jQuery(".page", this).bind("click.ff.ffPageNavigator", {"id" : id}, that.goPage, true);

            if (navigators[id].totpage > 1) {
                //jQuery(".prev", this).removeClass("disabled");
                //jQuery(".next", this).removeClass("disabled");
                jQuery(".prev", this).parent().removeClass("hidden");
                jQuery(".next", this).parent().removeClass("hidden");
                jQuery(".prev", this).bind("click.ff.ffPageNavigator", {"id" : id}, that.prevPage);
                jQuery(".next", this).bind("click.ff.ffPageNavigator", {"id" : id}, that.nextPage);
            } else {
                //jQuery(".prev", this).addClass("disabled");
                //jQuery(".next", this).addClass("disabled");
                jQuery(".prev", this).parent().addClass("hidden");
                jQuery(".next", this).parent().addClass("hidden");
                jQuery(".prev", this).unbind(".ff.ffPageNavigator");
                jQuery(".next", this).unbind(".ff.ffPageNavigator");
            }
        

            if (navigators[id].page > 2) {
                jQuery(".first", this).bind("click.ff.ffPageNavigator", {"id" : id}, that.firstPage);
                jQuery(".first", this).parent().removeClass("hidden");
                //jQuery(".first", this).removeClass("disabled");
            } else {
                jQuery(".first", this).parent().addClass("hidden"); 
               // jQuery(".first", this).addClass("disabled");
            }

            if (navigators[id].totpage - navigators[id].page > 1 ) {
                jQuery(".last", this).bind("click.ff.ffPageNavigator", {"id" : id}, that.lastPage);
                jQuery(".last", this).parent().removeClass("hidden");
               // jQuery(".last", this).removeClass("disabled");
            } else {
                jQuery(".last", this).parent().addClass("hidden");
                //jQuery(".last", this).addClass("disabled");
            }  

            jQuery(".rec-page, .rec-all", this).bind("click.ff.ffPageNavigator", {"id" : id}, that.changeRecPerPage);
        }

        jQuery(".currentpage", this).bind("keydown.ff.ffPageNavigator", {"id" : id}, that.goPage);
        
        if (navigators[id].start_page > 1) { 
            jQuery(".prev-frame", this).bind("click.ff.ffPageNavigator", {"id" : id}, that.prevFrame);
            jQuery(".prev-frame", this).parent().removeClass("hidden");
            //jQuery(".prev-frame", this).removeClass("disabled");
        } else {
            jQuery(".prev-frame", this).parent().addClass("hidden");
            //jQuery(".prev-frame", this).addClass("disabled");
        }

        if (navigators[id].end_page < navigators[id].totpage) {
            jQuery(".next-frame", this).bind("click.ff.ffPageNavigator", {"id" : id}, that.nextFrame);
            jQuery(".next-frame", this).parent().removeClass("hidden");
            //jQuery(".next-frame", this).removeClass("disabled");
        } else {
            jQuery(".next-frame", this).parent().addClass("hidden");
            //jQuery(".next-frame", this).addClass("disabled");
        }
    });
}

var that = { // publics
__ff : true, // used to recognize ff'objects

"addNavigator" : function (params) {
	//if (navigators[params.id] === undefined) { 
    var prefix = (params.prefix !== undefined 
                    ? params.prefix 
                    : params.id + "_"
                );

	navigators[params.id] = {
	      "component"					: params.id + "-pn"
		, "page"						: params.page || jQuery("." + params.id + "-pn:first").data("page")
		, "totrec"						: params.totrec || jQuery("." + params.id + "-pn:first").data("totrec")
		, "page_parname"				: prefix + params.page_parname
		, "rec_per_page_parname"		: prefix + params.rec_per_page_parname
		, "rec_per_page"				: params.rec_per_page
		, "page_per_frame"				: params.page_per_frame
		, "doAjax"						: params.doAjax
        , "infinite"                    : params.infinite
		, "currentClass"				: params.currentClass || "current"
		, "loaderClass"					: params.loaderClass
        , "callback"                    : params.callback
        , "callbackParams"              : params.callbackParams || {}
	}

	calcPage(params.id);
   // drawButtons(params.id);
	
	that.doEvent({
		"event_name"	: "addNavigator",
		"event_params"	: [params.id, navigators]
	});
   // }
    if(navigators[params.id].infinite) {
        navigators[params.id]["component"] += "-" + params.page;
		jQuery(window).bind("scroll.ff.ffPageNavigator", {"id": params.id}, this.infiniteScroll); 
        if(navigators[params.id].infinite == "prev")
            jQuery("." + navigators[params.id]["component"] + " .prev").bind("click.ff.ffPageNavigator", {"id" : params.id}, this.prevPage);
		else
            jQuery("." + navigators[params.id]["component"] + " .next").bind("click.ff.ffPageNavigator", {"id" : params.id}, this.nextPage);
    } else {
        eventButtons(params.id);            
    }
},
"deleteNavigator" : function (id) {
	if (navigators[id] !== undefined)
		delete navigators[id];
},

"updateButtons" : function(id, newTotPage, page) {
	if(navigators[id] === undefined)
		return;
	
	if(page !== undefined) {
		navigators[id].page = page;
    }

	if (newTotPage !== undefined && navigators[id].totrec !== newTotPage) {
		navigators[id].totrec = newTotPage;
		calcPage(id);
        drawButtons(id); 
	}

	if(!navigators[id].infinite) {
    	eventButtons(id);

		that.doEvent({
			"event_name"	: "onUpdateButtons",
			"event_params"	: [id, navigators]
		});
	}
},

"goToPage" : function (ev, page, records_per_page, doAjax) {
    var that = this;
    var id = ev.data.id;
	var component = navigators[id]["component"];
	
    if (page !== null) {
        navigators[id].page = page;
        jQuery("#" + navigators[id].page_parname).val(page);
    }

    if(doAjax !== false) {
        doAjax = navigators[id].doAjax;
    }
	if (records_per_page !== undefined) {
		if(records_per_page > navigators[id].totrec)
			records_per_page = navigators[id].totrec;

		navigators[id].rec_per_page = records_per_page;
		jQuery("#" + navigators[id].rec_per_page_parname).val(records_per_page);
	}
	if(!navigators[ev.data.id].infinite) {
		calcPage(id);
		drawButtons(id);
	}
	//that.updateButtons(id);

	jQuery("#frmAction").val(id + "_nav");
        jQuery("." + component).each(function() {
            var nextUrl = jQuery(".next", this);
            var prevUrl = jQuery(".prev", this);
            
            if(nextUrl.length)
                nextUrl.attr("href", that.updateUriParams("page", (navigators[id].page == navigators[id].totpage ? "" : navigators[id].page + 1), nextUrl.attr("href")));
            if(prevUrl.length)
                prevUrl.attr("href", that.updateUriParams("page", (navigators[id].page == 1 ? navigators[id].totpage : navigators[id].page - 1), prevUrl.attr("href")));
        });
                
	if (doAjax) {
            ff.pluginLoad("ff.ajax", "/themes/library/ff/ajax.js", function() {

		    var widget = ff.struct.get(id); 
		    if (widget && widget.dialog) {
			    ff.ffPage.dialog.doRequest(widget.dialog, {
				    "component" : id,
				    "section"	: "GridData",
				    "callback"	: function () {
					    jQuery("a", ff.ffPage.dialog.get(widget.dialog).instance).each(function () {
						    //var ret_url = ff.history.gup("ret_url", this.href);
						    //if (ret_url.length)
						    //	this.href = this.href.replace(/ret_url=[^&#]*/, "ret_url=" + ff.ffPageNavigator.fixUrl(ret_url, id, records_per_page));
					    });
					    that.doEvent({
						    "event_name"	: "onGoToPage",
						    "event_params"	: [id, page, records_per_page]
					    });
				    }
			    });
		    } else {
			    ff.ajax.doRequest({
				    "component" : id,
				    "section"	: "GridData",
				    "callback"	: function () {
					    jQuery("a").each(function () {
						    //var ret_url = ff.history.gup("ret_url", this.href);
						    //if (ret_url.length)
						    //	this.href = this.href.replace(/ret_url=[^&#]*/, "ret_url=" + ff.ffPageNavigator.fixUrl(ret_url, id, records_per_page));
					    });
					    that.doEvent({
						    "event_name"	: "onGoToPage",
						    "event_params"	: [id, page, records_per_page]
					    });
				    }
			    });
		    }
            });
	} else {
            window.location.href = that.updateUriParams(navigators[id].page_parname, navigators[id].page);
            /*var query = window.location.search.substring(1);
            query = query.split("&");
            var newQuery = [];
            query.each(function(i, value) {
                if(value) {
                    switch(value.split("=")[0]) {
                        case navigators[id].page_parname:
                        case navigators[id].rec_per_page_parname:
                            break;
                        default:
                            newQuery.push(value);
                    }
                }
            });

            newQuery.push(navigators[id].page_parname + "=" + navigators[id].page); 
            if(records_per_page || jQuery("." + component + " .perPage .rec-all").length) 
                newQuery.push(navigators[id].rec_per_page_parname + "=" + navigators[id].rec_per_page);

            window.location.href = window.location.pathname + "?" + newQuery.join("&") + window.location.hash;
            */
	}
},
"infiniteScroll" : function(ev) {
	var id = ev.data.id;
	var component = navigators[id]["component"];
	var hidePageNav = function() {
		jQuery("." + component).hide();
	}
	
	var pageNavElem = jQuery("." + component).get(0);
    if(ff.inView(pageNavElem)) {
        jQuery(window).unbind("scroll.ff.ffPageNavigator");

	    if(jQuery(pageNavElem).hasClass("prev"))
    		that.prevPage(ev, hidePageNav);
	    else
    		that.nextPage(ev, hidePageNav);

//da gestire nelle requesst ajax js l'apend e il prepend
       // if(navigators[ev.data.id].page < navigators[ev.data.id].totpage)
            
    }
}, 
"changeRecPerPage" : function(ev) {
    ev.preventDefault();
    var recPerPage = parseInt(jQuery(ev.currentTarget).attr("rel"));
    
    if(recPerPage) {
        navigators[ev.data.id].rec_per_page = recPerPage;

        if(navigators[ev.data.id].doAjax && navigators[ev.data.id].callback) {
			calcPage(ev.data.id);
	        drawButtons(ev.data.id);         

	        navigators[ev.data.id].callbackParams["count"] = navigators[ev.data.id].rec_per_page;
            navigators[ev.data.id].callback(ev.data.id, navigators[ev.data.id].callbackParams);
		} else {
            that.goToPage(ev, null, navigators[ev.data.id].rec_per_page);
		}
    }
},
"goPage" : function (ev) {
    var page = 0;

    if(jQuery(ev.currentTarget).is("INPUT")) {
    	if(ev.keyCode == 13)
        	page = parseInt(jQuery(ev.currentTarget).val());
    } else {
        page = parseInt(jQuery(ev.currentTarget).attr("data-page"));
    }
    if(page) {
		ev.preventDefault(); 

        navigators[ev.data.id].page = page;

        if(navigators[ev.data.id].doAjax && navigators[ev.data.id].callback) {
        	if(!navigators[ev.data.id].infinite) {
				calcPage(ev.data.id);
				drawButtons(ev.data.id);
			}
        	navigators[ev.data.id].callbackParams["infinite"] = navigators[ev.data.id].infinite;
        	navigators[ev.data.id].callbackParams["page"] = navigators[ev.data.id].page;
            navigators[ev.data.id].callback(ev.data.id, navigators[ev.data.id].callbackParams);
		} else {
            that.goToPage(ev, navigators[ev.data.id].page);
		}
    }
},
"prevPage" : function (ev, callback) {
    ev.preventDefault();
	navigators[ev.data.id].page--;

	if (!navigators[ev.data.id].infinite && navigators[ev.data.id].page < 1)
		navigators[ev.data.id].page = navigators[ev.data.id].totpage;

	if(navigators[ev.data.id].page) {
		if(navigators[ev.data.id].doAjax && navigators[ev.data.id].callback) {
			if(!navigators[ev.data.id].infinite) {
				calcPage(ev.data.id);
				drawButtons(ev.data.id);
			}
			navigators[ev.data.id].callbackParams["callback"] = callback;
			
			navigators[ev.data.id].callbackParams["infinite"] = navigators[ev.data.id].infinite;
			navigators[ev.data.id].callbackParams["page"] = navigators[ev.data.id].page;
		    navigators[ev.data.id].callback(ev.data.id, navigators[ev.data.id].callbackParams);
		} else {
			that.goToPage(ev, navigators[ev.data.id].page);
		}
	}
},

"nextPage" : function (ev, callback) {
    ev.preventDefault();
	navigators[ev.data.id].page++;

	if (!navigators[ev.data.id].infinite && navigators[ev.data.id].page > navigators[ev.data.id].totpage)
		navigators[ev.data.id].page = 1; 

    if(navigators[ev.data.id].doAjax && navigators[ev.data.id].callback) {
		if(!navigators[ev.data.id].infinite) {
			calcPage(ev.data.id);
			drawButtons(ev.data.id);
		}
		navigators[ev.data.id].callbackParams["callback"] = callback;
		
    	navigators[ev.data.id].callbackParams["infinite"] = navigators[ev.data.id].infinite;
        navigators[ev.data.id].callbackParams["page"] = navigators[ev.data.id].page;
        navigators[ev.data.id].callback(ev.data.id, navigators[ev.data.id].callbackParams);
	} else {
	    that.goToPage(ev, navigators[ev.data.id].page);
	}
},

"prevFrame" : function (ev) {
    ev.preventDefault();
	navigators[ev.data.id].start_page -= navigators[ev.data.id].page_per_frame;
	if (navigators[ev.data.id].start_page < 1)
		navigators[ev.data.id].start_page = 1;
	navigators[ev.data.id].end_page = navigators[ev.data.id].start_page + navigators[ev.data.id].page_per_frame - 1;
	if (navigators[ev.data.id].end_page > navigators[ev.data.id].totpage)
		navigators[ev.data.id].end_page = navigators[ev.data.id].totpage;
           
    drawButtons(ev.data.id); 
	that.updateButtons(ev.data.id);
},

"nextFrame" : function (ev) {
    ev.preventDefault();
	navigators[ev.data.id].end_page += navigators[ev.data.id].page_per_frame;
	if (navigators[ev.data.id].end_page > navigators[ev.data.id].totpage)
		navigators[ev.data.id].end_page = navigators[ev.data.id].totpage;
	navigators[ev.data.id].start_page = navigators[ev.data.id].end_page - navigators[ev.data.id].page_per_frame + 1;
	if (navigators[ev.data.id].start_page < 1)
		navigators[ev.data.id].start_page = 1;
	
    drawButtons(ev.data.id); 
	that.updateButtons(ev.data.id);
},

"firstPage" : function (ev) {
    ev.preventDefault();
	navigators[ev.data.id].page = 1;

    if(navigators[ev.data.id].doAjax && navigators[ev.data.id].callback) {
		calcPage(ev.data.id);
		drawButtons(ev.data.id);

    	navigators[ev.data.id].callbackParams["page"] = navigators[ev.data.id].page;
        navigators[ev.data.id].callback(ev.data.id, navigators[ev.data.id].callbackParams);
	} else {
	    that.goToPage(ev, navigators[ev.data.id].page);
	}
	that.doEvent({
		"event_name"	: "firstPage",
		"event_params"	: [ev.data.id, navigators]
	});
},

"lastPage" : function (ev) {
    ev.preventDefault();
	navigators[ev.data.id].page = navigators[ev.data.id].totpage;

    if(navigators[ev.data.id].doAjax && navigators[ev.data.id].callback) {
		calcPage(ev.data.id);
		drawButtons(ev.data.id);

    	navigators[ev.data.id].callbackParams["page"] = navigators[ev.data.id].totpage;
        navigators[ev.data.id].callback(ev.data.id, navigators[ev.data.id].callbackParams);
	} else {
	    that.goToPage(ev, navigators[ev.data.id].page);
	}
	that.doEvent({
		"event_name"	: "lastPage",
		"event_params"	: [ev.data.id, navigators]
	});
},
"updateQueryString" : function (uri, key, value, st) {
    if(!st)
          st = "?";

    var re = new RegExp("([" + st + "&])" + key + "=.*?(&|#|$)", "i");
    if (uri.match(re)) {
        if(value) {
              uri = uri.replace(re, '$1' + key + "=" + value + '$2');
        } else {
              uri = uri.replace(re, '$1' + '$2').replace("&&", "&").replace("?&", "?").trim("&");
        }
    } else {
        if(value) {
                  var separator = uri.indexOf(st) !== -1 ? "&" : st;	  	
              uri = uri + (uri.substr(uri.length - 1) == separator ? "" : separator) + key + "=" + value;   
        } else {
          uri = uri.trim("&");
        }
    }
    return (uri == st ? "" : uri);
},
"updateUriParams" : function(key, value, uri, searchIn) {
    var parser = document.createElement('a');
    parser.href = uri || window.location.href;

    var pathname = parser.pathname;
    var search = parser.search;
    var hash = parser.hash;

    switch(searchIn) {
        case "path":
                break;
        case "hash":
                hash = this.updateQueryString(hash, key, value, "#");
                break;
        case "search":
        default:
                search = this.updateQueryString(search, key, value);
    }
    return pathname + search + hash;
}/*,
"fixUrl" : function (ret_url, id, records_per_page) {
	ret_url = unescape(ret_url);

	var params = [];
	var hash_params = ff.hash();

	var new_ret_url	= "";

	if (ret_url.indexOf("?") >= 0)
	{
		new_ret_url	= ret_url.substring(0, ret_url.indexOf("?") + 1);

		ret_url = ret_url.substring(ret_url.indexOf("?") + 1);
		if (ret_url.length) {
			params	= ret_url.split("&");

			for (var i = 0; i < params.length; i++) {
				if (params[i].indexOf("=") >= 0) {
					var parts = params[i].split("=");
					hash_params.set(parts[0], parts[1]);
				}
			}
		}
	}
	else
	{
		new_ret_url	= ret_url + "?";
	}

	hash_params.set(navigators[id].page_parname, navigators[id].page);
	if (records_per_page !== undefined)
		hash_params.set(navigators[id].rec_per_page_parname, navigators[id].rec_per_page);

	hash_params.each(function (key, value) {
		new_ret_url += key + "=" + value + "&";
	});

	return escaped_newurl = escape(new_ret_url).replace(/\//g, "%2F");
}*/

}; // publics' end

return that;

// code's end.
})();
