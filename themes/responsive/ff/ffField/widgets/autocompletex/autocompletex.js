/**
 * Forms Framework Javascript Handling Object
 *	autocomplete fields' plugin namespace
 */

ff.ffField.autocompletex = (function () {
 
/* private vars*/
var innerURL	= (window.location.pathname == "/" ? "" : window.location.pathname) + "/aparsedatax" + window.location.search; 
var icons		= {};

var pParams		= ff.hash();
var pData		= ff.hash();
var pCache		= ff.hash();

var that = { /* publics*/
__ff : true, /* used to recognize ff'objects*/

"init" : function (params) {
	if(params.innerURL)
		innerURL	= params.innerURL;
	
	icons	= params.icons;

},

"add" : function (params) {
	pParams.set(params.id, params);
	pData.set(params.id, ff.hash());
	pCache.set(params.id, ff.hash());

	that.doEvent({
		"event_name"	: "onAddAutocomplete",
		"event_params"	: [params.id]
	});

	that.observe(params.id);
},

"split" : function (val) {
	return val.split(/,\s*/);
}, 

"extractLast" : function (term) {
    return that.split(term).pop();
}, 

"recalc" : function (control, term) {
	var loadedData = pData.get(control);
    var terms = that.split(term);
    var strNewData = '';

    for (var a = 0; a < terms.length; a++) {  
        if(terms[a].length > 0 && loadedData.isset(terms[a].replace(/^\s+|\s+$/g,"")) !== undefined) {
			if(strNewData.length > 0)
				strNewData = strNewData + ",";
           
            strNewData = strNewData + loadedData.get(terms[a].replace(/^\s+|\s+$/g,""));
        }
    }
	if(!strNewData.length > 0) {
		if(loadedData.isset(term.replace(/^\s+|\s+$/g,"")) !== undefined) {
			strNewData = loadedData.get(term.replace(/^\s+|\s+$/g,""));
		}
	}

    return strNewData;
}, 
	
 /* da gestire multi off
  image se possibile
  callback on select */
"loadData": function(prefix, control) {
    var terms = that.split( jQuery("#" + prefix + control).val() );
    var termsID = that.split( jQuery("#" + control).val() );

	var loadedData = pData.get(control);
    for (var a = 0; a < terms.length; a++) {  
        loadedData.set(terms[a].replace(/^\s+|\s+$/g,""), termsID[a]);
    }

    return true;
},
	
"getService" : function (control) {
	var tmp = pParams.get(control).service;
	if (tmp !== null)
		return tmp;
	else
		return innerURL;
},
	
"observe" : function (control) {
	var params	= pParams.get(control);
	var cache	= pCache.get(control);
    var prefix	= '';
    
    if(params.readonly) {
        var prefix = "autocompletex_";
    }
                             
	if(prefix) {
		ff.ffField.autocompletex.loadData(prefix, control);
	}
	
	jQuery(".autocompletex-combo").click(function() {
		jQuery(this).prevAll(".autocompletex").focus();
		jQuery(this).prevAll(".autocompletex").autocomplete('search');
	});

    jQuery("#" + prefix + control)
        .bind( "keydown", function(event) {
            if ( event.keyCode === jQuery.ui.keyCode.TAB &&
                    jQuery(this).data("autocompletex").menu.active) {
                event.preventDefault();
            }
        })
        .keyup(function(event) {
            var stripChar = unescape(params.stripChar);

            if(stripChar.length > 0) {
                var patt = new RegExp(stripChar);
                jQuery(this).val(jQuery(this).val().replace(patt, ''));
            }
        })
        .autocomplete({
            "disabled"	: params.disabled,
            "minLength" : params.minLength,
            "delay"		: params.delay, 
            "appendTo"	: jQuery("#" + prefix + control).parent(),
            "open"		: function(event, ui) {
                jQuery(".ui-autocomplete").css("height", "200px").css("overflow-y", "scroll").css("overflow-x", "hidden");
            },
            "source"	: function(request, response) {
                var strTerm = '';

                if(params.multi) {
                    strTerm = ff.ffField.autocompletex.extractLast( request.term ).replace(/^\s+|\s+$/g,"");
                } else {
                    strTerm = request.term.replace(/^\s+|\s+$/g,"");
                }

                if(params.cache) {
					if (cache.isset(strTerm)) {
                        response(cache.get(strTerm));
                        return;
					}
				
                    var lastXhr = jQuery.getJSON((params.data_src === "" ? that.getService(control) : ff.urlAddParam(that.getService(control), "data_src", params.data_src)), {
                        "term" : strTerm
                    }, function(data, status, xhr) {
						cache.set(strTerm, data);
                        if (xhr === lastXhr) {
                            response(data);
                        }
                    });               
                } else {
                    jQuery.getJSON((params.data_src === "" ? that.getService(control) : ff.urlAddParam(that.getService(control), "data_src", params.data_src)), {
                        "term": strTerm
                    }, response);               
                }
            },
            "search":	function() {
                if(prefix) {
                    jQuery("#" + control).val(that.recalc(control, this.value));
                }
               /* var term = ff.ffField.autocompletex.extractLast( this.value );
                if ( term.length < params.minLength ) {
                    return false;
                }*/
            },
            "focus":	function(event, ui) {
                var terms = '';
                var termsID = '';
                var tmpData = '';

                var tmpLabel = ui.item.label.replace(/^\s+|\s+$/g,"");
                var tmpValue = ui.item.value.replace(/^\s+|\s+$/g,"");
                /*loadedData[tmpLabel] = ui.item.value;*/

                if(params.multi) {
                    terms = that.split(this.value);
                    termsID = that.split(jQuery("#" + control).val());

                    terms.pop();
                    termsID.pop();

                    var tmpData = terms.join(", ");

                    if(this.value.indexOf(",") == -1 
                        || 
                        (
                            (
                                tmpData.replace(/,\s+/g,",").indexOf(',' + tmpLabel) == -1
                                &&  
                                tmpData.replace(/\s+,/g,",").indexOf(tmpLabel + ',') == -1
                                && 
                                tmpData.replace(/^\s+|\s+$/g,"") != tmpLabel
                            )
                         && this.value.indexOf(",") >= 0 
                        )
                    ) {
                        /* add the selected item*/
                        terms.push(tmpLabel);
                        termsID.push(tmpValue);

                        /* add placeholder to get the comma-and-space at the end
                        terms.push( "" );
                        termsID.push( "" );*/
                        if(prefix) {
                            this.value = terms.join(", ");

                            if(this.value.indexOf(",") == -1) { 
                                jQuery(this).selection(tmpData.length, tmpData.length + tmpLabel.length);
                            } else {
                                jQuery(this).selection(tmpData.length + 2, tmpData.length + 2 + tmpLabel.length);
                            }
                        } else {
                            this.value = termsID.join(", ");

                            if(this.value.indexOf(",") == -1) { 
                                jQuery(this).selection(tmpData.length, tmpData.length + tmpValue.length);
                            } else {
                                jQuery(this).selection(tmpData.length + 2, tmpData.length + 2 + tmpValue.length);
                            }
                        }
                    } else {
                        if(prefix) {  
                            jQuery(this).selection(this.value.indexOf(tmpLabel), this.value.indexOf(tmpLabel) + tmpLabel.length);
                        } else {
                            jQuery(this).selection(this.value.indexOf(tmpValue), this.value.indexOf(tmpValue) + tmpValue.length);
                        }
                    }
                } else {
                	return false;
                	if(prefix) {
                    	this.value = tmpLabel;
                        
                        jQuery(this).selection(this.value.indexOf(tmpLabel), this.value.indexOf(tmpLabel) + tmpLabel.length);
                    } else {
                    	this.value = tmpValue;
                        
                        jQuery(this).selection(this.value.indexOf(tmpValue), this.value.indexOf(tmpValue) + tmpValue.length);
                    }
/*                    terms = this.value;
                    termsID = jQuery("#" + control).val();*/
                    
                }
                /* remove the current input*/
                return false;
                
                /* prevent value inserted on focus
                return false;*/
            },
            select: function( event, ui ) {
                var terms = '';
                var termsID = '';
                var tmpData = '';

                var tmpLabel = ui.item.label.replace(/^\s+|\s+$/g,"");
                var tmpValue = ui.item.value.replace(/^\s+|\s+$/g,"");
				
				var loadedData = pData.get(control);

                loadedData.set(tmpLabel, tmpValue);

                if(params.multi) {
                    terms = ff.ffField.autocompletex.split(this.value);
                    termsID = ff.ffField.autocompletex.split(jQuery("#" + control).val());

                    terms.pop();
                    termsID.pop();
                    
                    tmpData = terms.join(", ");
                    
                    /* remove the current input*/
                    if(this.value.indexOf(",") == -1 
                        || 
                        (
                            (
                                tmpData.replace(/,\s+/g,",").indexOf(',' + tmpLabel) == -1
                                &&  
                                tmpData.replace(/\s+,/g,",").indexOf(tmpLabel + ',') == -1
                                && 
                                tmpData.replace(/^\s+|\s+$/g,"") != tmpLabel
                            )
                         && this.value.indexOf(",") >= 0 
                        )
                    ) {
                        // add the selected item
                        terms.push( tmpLabel );
                        termsID.push( tmpValue );

                        /* add placeholder to get the comma-and-space at the end*/
                        if(prefix) {
                            terms.push("");
                        
                            this.value = terms.join(", ");
                        } else {
                            termsID.push("");
                        
                            this.value = termsID.join(", ");
                        }
                    }  

                } else {
                   /* terms = this.value;
                    termsID = jQuery("#" + control).val();
                    
                    tmpData = terms;  */
                    if(prefix)
                    	this.value = tmpLabel;
                    else 
                    	this.value = tmpValue;
                }


                if(prefix) {
                    jQuery("#" + control).val(that.recalc(control, this.value));
                }
				
				jQuery("#" + prefix + control).change();

                return false;
            }
        });
}

}; /* publics' end*/

/*if(ff.addEvent !== undefined)
	ff.addEvent({"event_name" : "onClearField", "func_name" : that.onClearField});

if(ff.ajax.addEvent !== undefined)
	ff.ajax.addEvent({"event_name" : "onUpdateField", "func_name" : that.onUpdateField});*/

return that;

/* code's end.*/
})();


jQuery.fn.selection = function(start, end) {
    if (start !== undefined) {
        return this.each(function() {
            if( this.createTextRange ){
                var selRange = this.createTextRange();
                if (end === undefined || start == end) {
                    selRange.move("character", start);
                    selRange.select();
                } else {
                    selRange.collapse(true);
                    selRange.moveStart("character", start);
                    selRange.moveEnd("character", end);
                    selRange.select();
                }
            } else if( this.setSelectionRange ){
                this.setSelectionRange(start, end);
            } else if( this.selectionStart ){
                this.selectionStart = start;
                this.selectionEnd = end;
            }
        });
    }
    var field = this[0];
    if ( field.createTextRange ) {
        var range = document.selection.createRange(),
            orig = field.value,
            teststring = "<->",
            textLength = range.text.length;
        range.text = teststring;
        var caretAt = field.value.indexOf(teststring);
        field.value = orig;
        this.selection(caretAt, caretAt + textLength);
        return {
            start: caretAt,
            end: caretAt + textLength
        }
    } else if( field.selectionStart !== undefined ){
        return {
            start: field.selectionStart,
            end: field.selectionEnd
        }
    }
};