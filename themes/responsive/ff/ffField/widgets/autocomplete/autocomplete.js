/**
 * Forms Framework Javascript Handling Object
 *	activecombo fields' plugin namespace
 */

ff.ffField.autocomplete = (function () {
 
/* private vars*/
var innerURL			= (window.location.pathname == "/" ? "" : window.location.pathname) + "/aparsedata" /*+ window.location.search*/;  
var icons				= {};

var data			= ff.hash();

var sources			= ff.hash();
var actualData      = {};
var cache           = { };
var that = { /* publics*/
__ff : true, /* used to recognize ff'objects*/

"init" : function (params) {
	if(params.innerURL)
		innerURL			= params.innerURL;

	icons					= params.icons;
},

"addAutocomplete" : function (params) {
	data.set(params.id, params.data);

	that.doEvent({
		"event_name"	: "onAddAutocomplete",
		"event_params"	: [params.id]
	});
},

"split" : function ( val ) {
	return (val ? val.split( /,\s*/ ) : [])
}, 

"extractLast" : function ( term ) {
    return ff.ffField.autocomplete.split( term ).pop() || "";
}, 

"recalc" : function ( term , control, readOnly, sep) {
    var terms = ff.ffField.autocomplete.split( term );
    var strNewData = '';
    if(!sep)
    	sep = ",";
    terms.each(function(key, value) {
        if(value.length > 0) {
	        if(control && actualData[control][value.replace(/^\s+|\s+$/g,"")] !== undefined) {
				if(strNewData.length > 0)
					strNewData = strNewData + sep;
	           
	            strNewData = strNewData + actualData[control][value.replace(/^\s+|\s+$/g,"")];
	        } else if(!control || !readOnly) {
				if(strNewData.length > 0)
					strNewData = strNewData + sep;

        		strNewData = strNewData + value;
	        }
		}
    });
    /*
	if(!strNewData.length > 0) {
		if(actualData[term.replace(/^\s+|\s+$/g,"")] !== undefined) {
			strNewData = actualData[term.replace(/^\s+|\s+$/g,"")];
		}
	}*/
    return strNewData ;
}, 
 /* da gestire multi off
  image se possibile
  callback on select 
  da gestire compare bene*/
"loadData": function( prefix, control ) {
    var terms = ff.ffField.autocomplete.split( jQuery("#" + prefix + control).val() );
    var termsID = ff.ffField.autocomplete.split( jQuery("#" + control).val() );
	actualData[control] = [];
    terms.each(function(key, value) {
     	actualData[control][value.replace(/^\s+|\s+$/g,"")] = termsID[key];
    });

    return true;
},
"observe" : function (control, params) {
    var control = control;
    var enableCache = params.cache;
    var prefix = '';
    var enableMulti = params.multi;
    var altInnerUrl = params.service;

    var readOnly = params.readonly;

    //if(params.readonly) {
        var prefix = "autocomplete_";
    //}
	cache[ control ] = {};

    if(enableMulti) {
    	ff.load("jquery.plugins.autogrow-textarea", function() {
    		jQuery("#" + prefix + control).autogrow();
    	});
	}        
                             

	if(prefix) {
		ff.ffField.autocomplete.loadData(prefix, control);
	}
	
	jQuery("#" + prefix + control).parent().find(".actex-combo").click(function() {
		jQuery("#" + prefix + control).focus(); 
		//jQuery("#" + prefix + control).autocomplete('search');
	});
	jQuery("#" + prefix + control).parent().find(".actex-search").click(function() {
		var e = jQuery.Event("keydown", { keyCode: 13}); 

		jQuery("#" + prefix + control).trigger(e);		
	});
	jQuery("#" + prefix + control).parent().find(".actex-menu").click(function() {
		jQuery("#" + jQuery(this).attr("rel")).toggleClass("hidden"); 
	});
	
    jQuery("#" + prefix + control)
        .keyup(function(event) {
			if(event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40 ||  event.keyCode == 13)
				return;

            var stripChar = unescape(params.stripChar);
            if(prefix) {
                jQuery("#" + control).val( ff.ffField.autocomplete.recalc( jQuery(this).val(), control, readOnly ) );
            }
            if(stripChar.length > 0) {
                patt = new RegExp(stripChar);
                jQuery(this).val(jQuery(this).val().replace(patt, ''));
            }
			if(!jQuery(this).val()) {
	            //if(!enableMulti) {
	            	jQuery("#suggest_" + control).val("");
	        		//if(jQuery("#" + prefix + control).attr("data-placeholder")) {
	        		//	jQuery("#" + prefix + control).attr("placeholder", jQuery("#" + prefix + control).attr("data-placeholder"));
	        			//jQuery("#" + prefix + control).attr("data-placeholder", "");
	        		//}
				//}
	            jQuery("#" + control).val("");
			} else if(!enableMulti && jQuery("#suggest_" + control).val().toLowerCase().indexOf(jQuery("#" + prefix + control).val().toLowerCase()) !== 0) {
				jQuery("#suggest_" + control).val("");
				//jQuery("#" + control).val("");
			}  

			if(!enableMulti)
				jQuery("#" + control).attr("data-user", jQuery("#" + prefix + control).val());
        })
        .keydown(function(event) {
           /* if ( event.keyCode === jQuery.ui.keyCode.TAB &&
                    jQuery( this ).data( "autocomplete" ).menu.active ) {
                event.preventDefault();
            }*/ 

	        if(enableMulti && event.keyCode == 188) {
				var tmpVal = jQuery("#" + prefix + control).val();
				
				if(tmpVal.trim().charAt(tmpVal.trim().length - 1) == ",")
					jQuery("#" + prefix + control).val(tmpVal.trim().slice(0, -1));
			}

			//jQuery("#" + control).attr("data-user", null);
			if(!enableMulti && !jQuery("#" + prefix + control).val())
        		jQuery("#suggest_" + control).val("");        	
        })
        .blur(function(event) {
        	//if(!enableMulti)
            	jQuery("#suggest_" + control).val("");
			
			//jQuery("#" + control).attr("data-user", null);
			
			//if(!enableMulti)
				//jQuery("#" + prefix + control).val(jQuery("#" + control).val().toLowerCase());
	       /* if(jQuery("#" + control).attr("data-user")) {
	        	jQuery("#" + control).val(jQuery("#" + control).attr("data-user"));
	        }*/
	        if(enableMulti) {
				jQuery("#" + prefix + control).val( ff.ffField.autocomplete.recalc( jQuery("#" + prefix + control).val()) );
			}
	        
        })  
 		.focus(function(event) {
 			if(enableMulti) {
 				if(jQuery("#" + prefix + control).val())
					jQuery("#" + prefix + control).val( ff.ffField.autocomplete.recalc( jQuery("#" + prefix + control).val(), undefined, undefined, ", ") + ",");

            	jQuery("#" + prefix + control).autocomplete('search');
			} else {
            	if(jQuery("#" + prefix + control).val().length >= params.minLength)
            		jQuery("#" + prefix + control).autocomplete('search');
			}
        })
        .autocomplete({
            disabled: params.disabled,
            minLength : params.minLength,
            delay: params.delay, 
            appendTo: jQuery("#" + prefix + control).parent(),
            messages: {
                noResults: '',
                results: function() {}
            },
            open: function(event, ui) {
            	if(!enableMulti && jQuery("#" + control).val().length )
                	jQuery("#" + control).attr("data-user", jQuery("#" + prefix + control).val());

                /*var p = jQuery(this).offset();*/
                
                jQuery(".ui-autocomplete").css({
                	"height"				: "200px"
                	, "overflow-y"			: "scroll"
                	, "overflow-x"			: "hidden"
                	, "position"			: "absolute"
                	, "top"					: ""
                	, "z-index" 			: 3 
					, "background"			: "#fff"
					, "border"				: "1px solid #ccc"
					, "border-top-color"	: "#d9d9d9"
					, "box-shadow"			: "0 2px 4px rgba(0,0,0,0.2)"
					, "cursor"				: "default"
                });
                
                jQuery(".ui-autocomplete .ui-menu-item").css({
                	"padding"				: "0 10px"
                	, "color"				: "#222"
                });
            },
            source: function( request, response ) {
                var strTerm = '';
                var realInnerUrl = "";
                var fatherValue = jQuery("#" + jQuery("#" + prefix + control).data("father")).val() || '';

                if(enableMulti) {
                    strTerm = ff.ffField.autocomplete.extractLast( request.term ).replace(/^\s+|\s+$/g,"");
                } else {
                    strTerm = request.term.replace(/^\s+|\s+$/g,"");
                }

                if(altInnerUrl)
                	realInnerUrl= altInnerUrl;
                else
                	realInnerUrl = innerURL;

                var str_data = "type=autocomplete";
                if (params.data_src)
                    str_data += "&data_src=" + escape(params.data_src);
                /*if (params.compare)
                    str_data += "&compare=" + escape(params.compare);
                if (params.compareH)
                    str_data += "&compareh=" + escape(params.compareH);
                if (params.operation)
                    str_data += "&operation=" + escape(params.operation);*/
                if (fatherValue)
                    str_data += "&fv=" + escape(fatherValue);
                	
                if(enableCache) {
                    if ( strTerm in cache[ control ] ) {
                        response( cache[ control ][ strTerm ] );
                        return;
                    }
					
                    lastXhr = jQuery.getJSON( realInnerUrl + (realInnerUrl.indexOf("?") >= 0 ? "&" : "?") + str_data, {
                        term: strTerm
                    }, function( data, status, xhr ) {
                        cache[ control ][ strTerm ] = data;
                        if ( xhr === lastXhr ) {
                            response( data );
                        }
                    });               
                } else {
                    jQuery.getJSON( realInnerUrl + (realInnerUrl.indexOf("?") >= 0 ? "&" : "?") + str_data, {
                        term: strTerm
                    }, response);               
                }
            },
            search: function(event, ui) { 
                if(prefix) {
                    jQuery("#" + control).val( ff.ffField.autocomplete.recalc( this.value, control, readOnly ) );
                }
                if(!enableMulti) {
                	//jQuery("#" + prefix + control).attr("data-placeholder", jQuery("#" + prefix + control).attr("placeholder"));
                	//jQuery("#" + prefix + control).attr("placeholder", "");
                	//if(jQuery("#suggest_" + control).nextAll("UL.ui-autocomplete").children("li:first").text().toLowerCase().indexOf(jQuery("#suggest_" + control).val().toLowerCase()) === 0)
            			//jQuery("#suggest_" + control).val(jQuery("#suggest_" + control).nextAll("UL.ui-autocomplete").children("li:first").text().toLowerCase());

				}
               /* var term = ff.ffField.autocomplete.extractLast( this.value );
                if ( term.length < params.minLength ) {
                    return false;
                }*/
            },
			response: function(event, ui) { 
				if(!enableMulti) {
            		if(jQuery("#" + prefix + control).val().length && ui.content[0] && ui.content[0].label.toLowerCase().indexOf(jQuery("#" + prefix + control).val().toLowerCase()) === 0) {
		                jQuery("#suggest_" + control).val(ui.content[0].label.toLowerCase());
					} else {
				 		jQuery("#suggest_" + control).val("");
					}
				}
               /* var term = ff.ffField.autocomplete.extractLast( this.value );
                if ( term.length < params.minLength ) {
                    return false;
                }*/
            },          
            close: function(event, ui) {

				//if(!jQuery(event.toElement).closest(".actex-container").length) {
					/*if(!enableMulti) {
						if(event.relatedTarget === null)
							jQuery("#" + control).val(jQuery("#" + control).attr("data-user"));
						jQuery("#" + prefix + control).val(jQuery("#" + control).val().toLowerCase());
						if(prefix) {
		                    jQuery("#" + control).val( ff.ffField.autocomplete.recalc( jQuery("#" + control).val(), control, readOnly ) );
		                }
						
						//jQuery("#" + control).attr("data-user", "");
					}*/
				//}
	            
            },   
            focus: function(event, ui) {
            	if(!enableMulti) {
					if(jQuery("#" + prefix + control).val().length && ui.item && ui.item.label.toLowerCase().indexOf(jQuery("#" + prefix + control).val().toLowerCase()) === 0) {
		                jQuery("#suggest_" + control).val(ui.item.label.toLowerCase());
					} else {
				 		jQuery("#suggest_" + control).val("");
					}
				}

                $(".ui-helper-hidden-accessible").hide();
                event.preventDefault();
/*
                var terms = '';
                var termsID = '';
                var tmpData = '';

                var tmpLabel = ui.item.label.replace(/^\s+|\s+$/g,"").replace(/,/g, "");
                var tmpValue = ui.item.value.replace(/^\s+|\s+$/g,"");

                if(enableMulti) {
                    terms = ff.ffField.autocomplete.split( this.value );
                    termsID = ff.ffField.autocomplete.split( jQuery("#" + control).val() );

                    terms.pop();
                    termsID.pop();

                    var tmpData = terms.join( ", " );

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
                        
                        terms.push( tmpLabel );
                        termsID.push( tmpValue );

                        if(prefix) {
                            this.value = terms.join( ", " );

                            if(this.value.indexOf(",") == -1) { 
                                jQuery(this).selection(tmpData.length, tmpData.length + tmpLabel.length);
                            } else {
                                jQuery(this).selection(tmpData.length + 2, tmpData.length + 2 + tmpLabel.length);
                            }
                        } else {
                            this.value = termsID.join( ", " );

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
                	//return false;
                	if(prefix) {
                		jQuery("#" + prefix + control).val(tmpLabel.toLowerCase());
                    	jQuery("#" + control).val(tmpLabel);
                        
                       // jQuery(this).selection(this.value.indexOf(tmpLabel), this.value.indexOf(tmpLabel) + tmpLabel.length);
                    } else {
                    	jQuery("#" + prefix + control).val(tmpValue.toLowerCase());
                    	jQuery("#" + control).val(tmpValue);
                        
                        //jQuery(this).selection(this.value.indexOf(tmpValue), this.value.indexOf(tmpValue) + tmpValue.length);
                    }
                }
*/
                /* remove the current input*/
                return false;
                
                /* prevent value inserted on focus
                return false;*/
            },
            select: function( event, ui ) {
                var terms = '';
                var termsID = '';
                var tmpData = '';

                var tmpLabel = ui.item.label.replace(/^\s+|\s+$/g,"").replace(/,/g, "");
                var tmpValue = ui.item.value.replace(/^\s+|\s+$/g,"");

                actualData[control][tmpLabel] = tmpValue;

                if(enableMulti) {
                    terms = ff.ffField.autocomplete.split( this.value );
                    termsID = ff.ffField.autocomplete.split( jQuery("#" + control).val() );

                    terms.pop();
                    termsID.pop();
                    
                    tmpData = terms.join( ", " );
                    
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

                        /* add the selected item*/
                        terms.push( tmpLabel );
                        termsID.push( tmpValue );

                        /* add placeholder to get the comma-and-space at the end*/
                        if(prefix) {
                            terms.push( "" );
                        
                            this.value = terms.join( ", " );
                        } else {
                            termsID.push( "" );
                        
                            this.value = termsID.join( ", " );
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
                    jQuery("#" + control).val( ff.ffField.autocomplete.recalc( this.value, control, readOnly ) );
                    if(!enableMulti) {
                    	this.value = tmpLabel.toLowerCase();
                    	jQuery("#" + control).attr("data-user", tmpLabel);
					}
                }
				//jQuery("#suggest_" + control).val(this.value);

//				var e = $.Event("keydown", { keyCode: 13}); //"keydown" if that's what you're doing
//				$("#" + prefix + control).trigger(e);			    

				//jQuery("#" + prefix + control).change();

                return false;
            }
        })
        .data("ui-autocomplete")._renderItem = function (ul, item) {
         return $("<li></li>")
             .data("item.autocomplete", item)
             .append("<a>" + (item.image ? item.image : "") + item.label + "</a>")
             .appendTo(ul);
     };

    
    
}


}; /* publics' end*/

/*if(ff.addEvent !== undefined)
	ff.addEvent({"event_name" : "onClearField", "func_name" : that.onClearField});

if(ff.ajax.addEvent !== undefined)
	ff.ajax.addEvent({"event_name" : "onUpdateField", "func_name" : that.onUpdateField});
*/
return that;

/* code's end.*/
})();


$.fn.selection = function(start, end) {
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