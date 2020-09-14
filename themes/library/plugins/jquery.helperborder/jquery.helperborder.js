(function($) {
    $.fn.helperBorder = function( options, optionsValue ) {
        var opts = $.extend( {}, $.fn.helperBorder.defaults, options );

		switch(options) {
			case "hide":
				 $.fn.helperBorder.display(opts, true);
				break;
			case "show":
				 $.fn.helperBorder.display(opts, false);
				break;
			default:
		        $.fn.helperBorder.init(opts);

		        return this.each(function() {
		            var $this = $( this );
					switch(options) {
						case "showGuide":
							$.fn.helperBorder.showGuide($this, opts, true);
							break;
						case "hideGuide":
							$.fn.helperBorder.showGuide($this, opts, false);
							break;
						case "borderGuide":
							$.fn.helperBorder.setBorder(opts["guide"]["elemId"], optionsValue);
							break;
						case "showGuideSelected":
							$.fn.helperBorder.showGuideSelected($this, opts, true);
							break;
						case "hideGuideSelected":
							$.fn.helperBorder.showGuideSelected($this, opts, false);
							break;
						case "borderGuideSelected":
							$.fn.helperBorder.setBorder(opts["guide"]["elemId"], optionsValue);
							break;
						default:
							$.fn.helperBorder.loadOutline($this, opts);
					}
		            
		        });
		}
    };

    $.fn.helperBorder.init = function( opts ) {
    	opts["selectedTimer"] = false;       

		if(!$.fn.helperBorder.loaded) {
			$.fn.helperBorder.zIndex = 98;
			$("BODY *").each(function() {
			    var current = parseInt($(this).css("z-index"), 10);
			    if(current && $.fn.helperBorder.zIndex < current) $.fn.helperBorder.zIndex = current;
			});			
    		$.fn.helperBorder.zIndex++;

    		$.fn.helperBorder.loaded = true;
		    	
	        if(opts["guide"]["enable"] && !$(opts["guide"]["elemId"]).is("div")) {
	            var outlineGuide = '<div id="' + opts["guide"]["elemId"].replace("#", "") + '" class="outline guide" style="display:none;" data-margin="' + opts["guide"]["margin"] + '">' +
	                                '<div class="hb-breadcrumbs">' +
	                                    '<div class="hb-inner">' +
	                                    '</div>' +
	                                '</div>';
	            $(opts.container).append(outlineGuide);
	            if(opts["guide"]["border"] !== undefined) {
            		$(opts["guide"]["elemId"]).css({ "border" : (opts["guide"]["border"]["size"] ? opts["guide"]["border"]["size"] + "px " + opts["guide"]["border"]["style"] + " " + opts["guide"]["border"]["color"] : 0)
            									, "box-shadow" : (opts["guide"]["border"]["shadow"] ? " 0 0 " + opts["guide"]["border"]["shadow"] + "px " + opts["guide"]["border"]["color"] : 0)
            								});
				}
	        }
	        if(opts["guideSelected"]["enable"] && !$(opts["guideSelected"]["elemId"]).is("div")) {
	            var outlineSelected = '<div id="' + opts["guideSelected"]["elemId"].replace("#", "") + '" class="outline selected" style="display:none;" data-margin="' + opts["guideSelected"]["margin"] + '">' +
	                                '<div class="hb-breadcrumbs">' +
	                                    '<div class="hb-inner">' +
	                                    '</div>' +
	                                '</div>';
	            $(opts.container).append(outlineSelected);
	            if(opts["guideSelected"]["border"] !== undefined) {
		            $(opts["guideSelected"]["elemId"]).css({ "border" : (opts["guideSelected"]["border"]["size"] ? opts["guideSelected"]["border"]["size"] + "px " + opts["guideSelected"]["border"]["style"] + " " + opts["guideSelected"]["border"]["color"] : 0)
            									, "box-shadow" : (opts["guideSelected"]["border"]["shadow"] ? " 0 0 " + opts["guideSelected"]["border"]["shadow"] + "px " + opts["guideSelected"]["border"]["color"] : 0)
            								});
				}

				//eventi base aggiunti
				$(opts.container).bind("click", function(e) { 
   					if(!$(e.target).closest(".hb-outline-selected").length) {
	         			$.fn.helperBorder.display(opts, true);
					}
		        });            

 				$(window).resize(function() {
 					if($.fn.helperBorder.display(opts)) {
            			$.fn.helperBorder.setPosition($( ".hb-outline-selected"), opts["guideSelected"]["elemId"]);
            			$.fn.helperBorder.setToolbarPosition($(".hb-outline-selected"), opts["guideSelected"]["elemId"]);
					} else {
						;
					}
	            });

	            $(window).scroll(function() {
            		if($.fn.helperBorder.display(opts)) {
            			$.fn.helperBorder.setToolbarPosition($(".hb-outline-selected"), opts["guideSelected"]["elemId"]);
					}
	            });
			}
		}
    }
    
    $.fn.helperBorder.setBorder = function(target, value) {
    	var border = {};

		if(value === undefined) {
			border["border-color"] = $(target).attr("data-bcolor");
			border["border-width"] = $(target).attr("data-bwidth");
			border["border-style"] = $(target).attr("data-bstyle");
			border["box-shadow"] = $(target).attr("data-bshadow");
		} else {
			if(typeof value === "Object") {
    			if(value["color"]) {
					border["border-color"] = value["color"];
					if(!$(target).attr("data-bcolor"))
						$(target).attr("data-bcolor", $(target).css("border-color"));
    			}
    			if(value["size"]) {
					border["border-width"] = value["size"] + "px";
					if(!$(target).attr("data-bwidth"))
						$(target).attr("data-bwidth", $(target).css("border-width"));
    			}
    			if(value["style"]) {
					border["border-style"] = value["style"];
					if(!$(target).attr("data-bstyle"))
						$(target).attr("data-bstyle", $(target).css("border-style"));
    			}
    			if(value["shadow"]) {
					border["box-shadow"] = value["shadow"];
					if(!$(target).attr("data-bshadow"))
						$(target).attr("data-bshadow", $(target).css("box-shadow"));
    			}
			} else if(value) {
				border["border-color"] = value;
				if(!$(target).attr("data-bcolor"))
					$(target).attr("data-bcolor", $(target).css("border-color"));
			}
		}
        $(target).css(border);
    }    
    $.fn.helperBorder.display = function(opts, forceHide) {
		var elemVisible = $(".hb-outline-selected").is(":visible");
		var targetVisible = $(opts["guideSelected"]["elemId"]).is(":visible");

 		if(targetVisible) {
 			if(forceHide) {
 				$(opts.container).find(".hb-outline-selected").removeClass("hb-outline-selected");
			}

 			if(!elemVisible || forceHide) {
	            $(opts["guideSelected"]["elemId"]).hide();
	            $(opts["guide"]["elemId"]).hide();
				targetVisible = false;
	            
	            if(opts.onHide) {
	                if($.isFunction(opts.onHide)) {
	                    opts.onHide();
	                } else {
	                    eval(opts.onHide + '(' + ');');
	                }
	            }
			}
		} else {
			if(!forceHide && elemVisible) {
	            $(opts["guideSelected"]["elemId"]).show();
	            targetVisible = true;
			}
		}
        
        return targetVisible;
    }
    $.fn.helperBorder.setPosition = function(elem, target) {
    	var elemLocalization = undefined;
    	if($(elem).length) {
    		var parentFixed = undefined;
    		if($(elem).attr("hb-fixed")) {
				parentFixed = JSON.parse($(elem).attr("hb-fixed"));
			}
			if(!parentFixed && $(elem).css("position") == "fixed") {
				parentFixed = $(elem).offset();

				if((parentFixed.top - jQuery(window).scrollTop()) == 0 && parentFixed.left == 0 
					&& (parentFixed.bottom == 0 || $(elem).width() >= jQuery(window).innerWidth())
					&& (parentFixed.right == 0 || $(elem).height() >= jQuery(window).innerHeight())
				) {
					parentFixed = true;

					elem = $(elem).children(":last");
				}
			}

			var margin = ($(target).attr("data-margin") > 0 ? parseInt($(target).attr("data-margin")) : 0);

	       // var elemZindex = $(elem).css("z-Index");
            var oldElemStyle = {}

            if($(elem).css("clear") != "none")
            	oldElemStyle["clear"] = $(elem).css("clear");
            if($(elem).css("padding") && parseInt($(elem).css("padding")) > 0)
            	oldElemStyle["padding"] = $(elem).css("padding");
            	
  
			var oldElemAttrStyle = $(elem).attr("style") || "";
            var restoreCssElem = false;
            
			if(!$(elem).height()) {
                $(elem).css({ "clear" : "both"});
                
                restoreCssElem = true;
            }

            if($(elem).css("overflow") != "hidden") {
                $(elem).attr("style", "overflow:hidden!important;" + $(elem).attr("style"));	

                restoreCssElem = true;
            }

            var rect = jQuery(elem)[0].getBoundingClientRect();
            var elemLocalization = {
		        "top" 			:  rect["top"] 	 - margin
		        , "left" 		: (rect["left"] - margin	> 0 ? rect["left"] - margin		: margin) 
		        , "right"		: (rect["right"] 	> 0 ? rect["right"] 	: 0) + margin
		        , "bottom" 		: (rect["bottom"] 	> 0 ? rect["bottom"]	: 0) + margin
		       // , "width" 		: (rect["width"] 	> 0 ? rect["width"] 	: 0) + (rect["left"] 	> 0 ? 0 : rect["left"])	+ (rect["right"] 	> 0 ? 0 : rect["right"]) 	+ (margin * 2)
		       // , "height" 		: (rect["height"] 	> 0 ? rect["height"] 	: 0) - (rect["top"] 	> 0 ? 0 : rect["top"]) 	+ (rect["bottom"] 	> 0 ? 0 : rect["bottom"]) 	+ (margin * 2)
		        , "z-index" 	: $.fn.helperBorder.zIndex //(elemZindex 			? elemZindex 		: $.fn.helperBorder.zIndex)
		    };
			elemLocalization["width"] = elemLocalization["right"] - elemLocalization["left"];
			elemLocalization["height"] = elemLocalization["bottom"] - elemLocalization["top"];
			if(elemLocalization["left"] + elemLocalization["width"] > jQuery("body").innerWidth()) {
		    	elemLocalization["width"] =  jQuery("body").innerWidth()  - elemLocalization["left"] - margin;
			}
            if(parentFixed) {
            	elemLocalization["position"] = "fixed";
			} else {
				elemLocalization["top"] = elemLocalization["top"] + jQuery(window).scrollTop();
				
				elemLocalization["position"] = "absolute";
			}
            
            
            
            
            
            
            
            
            
            /*
            
            
			if(parentFixed === true) {
			    var elemWidth = $(elem).outerWidth(false);
			    var elemHeight = $(elem).outerHeight(false);
				//Fatto: Bisogna solo prendere il margin top e margin left. al momento la procedura recupera equamente i margini						        
				//bufferWidth = ($(elem).outerWidth(true) - $(elem).outerWidth()) / 2;
		        if($(elem).css("margin-left").indexOf("%") > 0) {
					bufferWidth = Math.round(parseInt($(elem).css("margin-left")) * elemWidth / 100, 0);
		        } else if($(elem).css("margin-left").indexOf("px") > 0) {
					bufferWidth = parseInt($(elem).css("margin-left"));
		        }

				//bufferHeight = ($(elem).outerHeight(true) - $(elem).outerHeight());
		        if($(elem).css("margin-top").indexOf("%") > 0) {
					bufferHeight = Math.round(parseInt($(elem).css("margin-top")) * elemHeight / 100, 0);
		        } else if($(elem).css("margin-top").indexOf("px") > 0) {
					bufferHeight = parseInt($(elem).css("margin-top"));
		        }

				if(bufferHeight < 0 )
					bufferHeight = 0;

				if(bufferWidth < 0 )
					bufferWidth = 0;

		        var elemLocalization = {
		            "top" : bufferHeight - margin
		            , "left" : bufferWidth - margin
		            , "right" :  margin
		            , "bottom" : margin
		            , "width" : elemWidth + (margin * 2)
		            , "height" : elemHeight + (margin * 2)
		            , "position" : "fixed"
		            , "z-index" : (elemZindex ? elemZindex : $.fn.helperBorder.zIndex)
		        };
			} else if(parentFixed) {
			    var elemWidth = $(elem).outerWidth(true);
			    var elemHeight = $(elem).outerHeight(true);

				var elemPosition = $(elem).position();
		        var elemLocalization = {
		            "top" : (parentFixed.top === undefined ? elemPosition.top - jQuery(window).scrollTop() : parentFixed.top + elemPosition.top) - margin
		            , "left" : (parentFixed.left === undefined ? elemPosition.left : parentFixed.left + elemPosition.left) - margin
		            , "right" : (parentFixed.right === undefined ? elemPosition.right : parentFixed.right + elemPosition.right) + margin
		            , "bottom" : (parentFixed.bottom === undefined ? elemPosition.bottom : parentFixed.bottom + elemPosition.bottom) + margin
		            , "width" : elemWidth + (margin * 2)
		            , "height" : elemHeight + (margin * 2)
		            , "position" : "fixed"
		            , "z-index" : (elemZindex ? elemZindex : $.fn.helperBorder.zIndex)
		        };
		        if(elemLocalization["top"] < 0)
		        	elemLocalization["top"] = 0;
		        if(elemLocalization["left"] < 0)
		        	elemLocalization["left"] = 0;
		        if(elemLocalization["width"] > jQuery("body").innerWidth())
		        	elemLocalization["width"] = jQuery("body").innerWidth();
		        if(elemLocalization["left"] + elemLocalization["width"] > jQuery("body").innerWidth()) 
		        	elemLocalization["width"] =  jQuery("body").innerWidth()  - elemLocalization["left"];
			} else {
		        var elemHeight = 0;
				var elemWidth = 0;
				var elemPosition = undefined;
				
		        var bufferWidth = 0;
		        var bufferHeight = 0;
				var absPosition = false;

				if(!elemPosition) {
					if(0 && $(elem).attr("hb-relative") && $(elem).css("position") == "absolute") { // nn trovo la casistica in cui serve
						elemPosition = $(elem).position();
					} else {
						elemPosition = $(elem).offset();
						absPosition = true;
					}
				}
		        
		        elemHeight = $(elem).outerHeight(false);
				if(elemHeight < $(elem).height()) //elimina padding negativi
					elemHeight = $(elem).height();
				
				if(!absPosition) {
					//Fatto: Bisogna solo prendere il margin top e margin left. al momento la procedura recupera equamente i margini						        
					//bufferHeight = ($(elem).outerHeight(true) - $(elem).outerHeight()) / 2;
			        if($(elem).css("margin-top").indexOf("%") > 0) {
						bufferHeight = Math.round(parseInt($(elem).css("margin-top")) * elemHeight / 100, 0);
			        } else if($(elem).css("margin-top").indexOf("px") > 0) {
						bufferHeight = parseInt($(elem).css("margin-top"));
			        }
				}
				
				if(oldElemStyle && oldElemStyle["padding"] && parseInt(oldElemStyle["padding"]) > 0) {
	                $(elem).css("padding", 0); 

	                restoreCssElem = true;
				}
	        
				if(restoreCssElem) {
                    //if($(elem).height() > elemHeight)
                     //   elemHeight = $(elem).outerHeight(true);
                        if(!elemHeight) {
                        	$(elem).children().each(function() {
                        		if($(this).css("position") == "absolute") {
							        elemHeight = $(this).outerHeight(false);
									if(elemHeight < $(this).height()) //elimina padding negativi
										elemHeight = $(this).height();

                        			elemWidth = $(this).outerWidth(false);
									if(elemWidth < $(this).width()) //elimina padding negativi
										elemWidth = $(this).width();

                        			elemPosition = $(this).offset();
								}
                        	});
						}
				}

		        if(!elemWidth) {
		        	elemWidth = $(elem).outerWidth(false);
					if(elemWidth < $(elem).width()) //elimina padding negativi
						elemWidth = $(elem).width();
				}		        	

				if(!absPosition) {
					//Fatto: Bisogna solo prendere il margin top e margin left. al momento la procedura recupera equamente i margini						        										
					//bufferWidth = ($(elem).outerWidth(true) - $(elem).outerWidth()) / 2;
			        if($(elem).css("margin-left").indexOf("%") > 0) {
						bufferWidth = Math.round(parseInt($(elem).css("margin-left")) * elemWidth / 100, 0);
			        } else if($(elem).css("margin-left").indexOf("px") > 0) {
						bufferWidth = parseInt($(elem).css("margin-left"));
			        }
				}

				if(bufferHeight < 0 )
					bufferHeight = 0;

				if(bufferWidth < 0 )
					bufferWidth = 0;

		        var elemLocalization = {
		            "top" : elemPosition.top + bufferHeight - margin
		            , "left" : elemPosition.left + bufferWidth - margin
		            , "right" : elemPosition.right + margin
		            , "bottom" : elemPosition.bottom + margin
		            , "width" : elemWidth + (margin * 2)
		            , "height" : elemHeight + (margin * 2)
		            , "position" : "absolute" //era absolute ma da problemi con elementi in fixed e margin top
		            , "z-index" : (elemZindex ? elemZindex : $.fn.helperBorder.zIndex)
		        };

		        if(elemLocalization["top"] < 0)
		        	elemLocalization["top"] = 0;
		        if(elemLocalization["left"] < 0)
		        	elemLocalization["left"] = 0;
		        if(elemLocalization["width"] > jQuery("body").innerWidth())
		        	elemLocalization["width"] = jQuery("body").innerWidth();
		        if(elemLocalization["left"] + elemLocalization["width"] > jQuery("body").innerWidth()) 
		        	elemLocalization["width"] =  jQuery("body").innerWidth()  - elemLocalization["left"];
			}*/
			
 			if(restoreCssElem) {
	            if(oldElemStyle)
	            	$(elem).css(oldElemStyle);
 				
 				if(oldElemAttrStyle)
	            	$(elem).attr("style", oldElemAttrStyle);
	            else 
	            	$(elem).removeAttr("style");	            	
	        }	 

	        $(target).css(elemLocalization);
	        /*
	        if($(target).css("position") == "absolute") {
	        	var diffTop = $(target).show().offset().top;

	        	$(target).css("position", "fixed");
	        	diffTop = diffTop - $(target).offset().top + $(document).scrollTop();
	        	$(target).css("position", "absolute");

	        	if(diffTop)
	        		$(target).css("top", parseInt($(target).css("top")) - diffTop);
	        }*/
		} 
		       
        return elemLocalization;
    }
	$.fn.helperBorder.setToolbarPosition = function(elem, target) {
		if($(elem).length && $(target + " .hb-inner").html()) {
	        var isToolbarBottom = false;
	        var toolbarHeight = $(target + " .hb-breadcrumbs .hb-inner").height();
			var toolbarTop = (parseInt($(target).css("top").replace("px", "")) - $(window).scrollTop());

		    if(toolbarTop <= toolbarHeight ) {
				isToolbarBottom = true;
			}

			if(isToolbarBottom) {
				$(target + " .hb-breadcrumbs").attr("class", "hb-breadcrumbs hb-bottom-crumbs");
			} else {
				$(target + " .hb-breadcrumbs").attr("class", "hb-breadcrumbs hb-top-crumbs");
			}

			$(target + " .hb-inner").removeClass("hb-right-top");
			$(target + " .hb-inner").removeClass("hb-right-bottom");

			if($(target).width() <= ($(target + " .hb-inner").children().width())) { 
				if(isToolbarBottom) {
					$(target + " .hb-inner").addClass("hb-right-bottom");
				} else {
					$(target + " .hb-inner").addClass("hb-right-top");
				}
			}
		}
    }
    $.fn.helperBorder.showGuide = function(elem, opts, showGuide) {
    	if(showGuide === undefined)
			showGuide = true;
        
        elemLocalization = $.fn.helperBorder.setPosition(elem, opts["guide"]["elemId"]);

        if(!opts["guide"]["showInner"]) {
            $(opts["guide"]["elemId"] + " .hb-inner").html("");
		} else {
	        if(opts["guide"]["innerCallback"]) {
                $(opts["guide"]["elemId"] + " .hb-inner").html("");

	            if($.isFunction(opts["guide"]["innerCallback"])) {
	                $(opts["guide"]["elemId"] + " .hb-inner").html(opts["guide"]["innerCallback"](elem, opts["guide"]["innerCallback"] + ' .hb-inner'));
	            } else {
	                $(opts["guide"]["elemId"] + " .hb-inner").html(eval(opts["guide"]["innerCallback"] + '(' + elem + ', ' + opts["guide"]["innerCallback"] + ' .hb-inner' + ');'));
	            }
	        } else {
	            var additInner = "";
	            if($(elem).attr("id")) {
	                 additInner = "#" + $(elem).attr("id");
	            }
	            if(1) {
	                 additInner = additInner + " {" + parseInt(elemLocalization.top) + "," + parseInt(elemLocalization.left) + "}";
	            }
	            if(1) {
	                 additInner = additInner + " [" + parseInt(elemLocalization.width) + "x" + parseInt(elemLocalization.height) + "]";
	            }

	            $(opts["guide"]["elemId"] + " .hb-inner").html($(elem).prop("tagName") + additInner);
	        }
		}
       
        if($(elem).hasClass("hb-exclude")) {
            showGuide = false;
        } else {
            if(opts["guide"]["exclude"]) {
                if($.isArray(opts["guide"]["exclude"])) {
                    for(var i in opts["guide"]["exclude"]) {
                        if($(elem).hasClass(opts["guide"]["exclude"][i])) {
                            showGuide = false;
                            break;
                        }
                    }
                } else {
                    if($(elem).hasClass(opts["guide"]["exclude"][i])) {
                        showGuide = false;
                    }                    
                }
            }
        }            
        if(!showGuide) {
            $(opts["guide"]["elemId"]).hide();
        } else {
            $(opts["guide"]["elemId"]).show();
            
            if(opts["guide"]["showInner"])
                $.fn.helperBorder.setToolbarPosition(elem, opts["guide"]["elemId"]);   

            if($(elem).hasClass("hb-outline-selected") 
                //&& !$(opts.guideSelected).is(":visible") //non carica bene la selected quando si modifica il valore nella text
            ) {
                $.fn.helperBorder.showGuideSelected(elem, opts); 
            } else {
                if(opts["guideSelected"]["timer"] && !$.fn.helperBorder.blockGuideSelected) {
                    opts["selectedTimer"] = setTimeout(function(){  
                    						$.fn.helperBorder.showGuideSelected(elem, opts); 
                    					}, opts["guideSelected"]["timer"]);
                    
                }
            }
        }
        if(opts.onHoverIn) {
            if($.isFunction(opts.onHoverIn)) {
                opts.onHoverIn(elem, $(opts["guide"]["elemId"]).is(":visible"));
            } else {
                eval(opts.onHoverIn + '(' + elem + ',' + $(opts["guide"]["elemId"]).is(":visible") + ');');
            }
        }
	}
	
	$.fn.helperBorder.showGuideSelected = function(elem, opts, showGuideSelected) {
 		if(opts["selectedTimer"]) {
	        clearTimeout(opts["selectedTimer"]);
	    }            

		if(!$.fn.helperBorder.blockGuideSelected)
		{
			$.fn.helperBorder.blockGuideSelected = true;
			setTimeout(function(){ 
						$.fn.helperBorder.blockGuideSelected = false; 
				}, 400);

			if(showGuideSelected === undefined)
				showGuideSelected = true;


	        $(opts.container).find(".hb-outline-selected").removeClass("hb-outline-selected");
	        $(elem).addClass("hb-outline-selected");

	        elemLocalization = $.fn.helperBorder.setPosition(elem, opts["guideSelected"]["elemId"]);
			
			if(!opts["guideSelected"]["showInner"]) {
				$(opts["guideSelected"]["elemId"] + " .hb-inner").html("");
			} else {
		        if(opts["guideSelected"]["innerCallback"]) {
					$(opts["guideSelected"]["elemId"] + " .hb-inner").html("");

		            if($.isFunction(opts["guideSelected"]["innerCallback"])) {
		                $(opts["guideSelected"]["elemId"] + " .hb-inner").html(opts["guideSelected"]["innerCallback"](elem, opts["guideSelected"]["elemId"] + ' .hb-inner'));
		            } else {
		                $(opts["guideSelected"]["elemId"] + " .hb-inner").html(eval(opts["guideSelected"]["innerCallback"] + '(' + elem + ', ' + opts["guideSelected"]["elemId"] + ' .hb-inner' + ');'));
		            }
		        }
			}
						
	        if($(elem).hasClass("hb-exclude")) {
	            showGuideSelected = false;
	        } else {
	            if(opts["guideSelected"]["exclude"]) {
	                if($.isArray(opts["guideSelected"]["exclude"])) {
	                    for(var i in opts["guideSelected"]["exclude"]) {
	                        if($(elem).hasClass(opts["guideSelected"]["exclude"][i])) {
	                            showGuideSelected = false;
	                            break;
	                        }
	                    }
	                } else {
	                    if($(elem).hasClass(opts["guideSelected"]["exclude"][i])) {
	                        showGuideSelected = false;
	                    }                    
	                }
	            }
	        }

	        if(!showGuideSelected) {
	            $(opts["guideSelected"]["elemId"]).hide();
	        } else {
	            $(opts["guideSelected"]["elemId"]).show();
	            
				if(opts["guideSelected"]["showInner"])
					$.fn.helperBorder.setToolbarPosition(elem, opts["guideSelected"]["elemId"]);
			}
	        
	        if(opts.onClick) {
	            if($.isFunction(opts.onClick)) {
	                opts.onClick(elem);
	            } else {
	                eval(opts.onClick + '(' + elem + ');');
	            }
	        }
		}
	}
    $.fn.helperBorder.loadOutline = function(elem, opts) {
        

	    var parents = $(elem).parents();
	    for(var i=0; i<parents.length;i++) {
			if($(parents[i]).css("position") == "fixed") {
				var fixedOffset = $(parents[i]).offset()

				fixedOffset["top"] = fixedOffset["top"] - $(document).scrollTop();

				$(elem).attr("hb-fixed", JSON.stringify(fixedOffset));
				break;
			}
	    }

	    if($(opts.container).css("position") == "relative"
	    	&& $(elem).parent().css("position") != "relative" 
	    ) {
			$(elem).attr("hb-relative", true);
	    } else {
			$(elem).removeAttr("hb-relative");
	    }
	    //$.fn.helperBorder.showGuide(elem, opts);
	    //$.fn.helperBorder.showGuideSelected(elem, opts);
	    
	    
        if(opts["guide"]["enable"]) {
            $(elem).hover(
              function () {
			  	$.fn.helperBorder.showGuide(this, opts);
              },
              function () {
                $(opts["guide"]["elemId"]).hide();

                if(opts["selectedTimer"]) {
                    clearTimeout(opts["selectedTimer"]);
                }

                if(opts.onHoverOut) {
                    if($.isFunction(opts.onHoverOut)) {
                        opts.onHoverOut(this);
                    } else {
                        eval(opts.onHoverOut + '(' + this + ');');
                    }
                }
              }
            );
        } else if(opts["guideSelected"]["enable"] && opts["guideSelected"]["timer"]) {
            $(elem).hover(
              function () {
			  		if(opts["guideSelected"]["timer"] && !$.fn.helperBorder.blockGuideSelected) {              
                  		opts["selectedTimer"] = setTimeout(function(){ 
                  	  					$.fn.helperBorder.showGuideSelected(this, opts); 
                  					}, opts["guideSelected"]["timer"]);
					}
              }
              , function () {
                  if(opts["selectedTimer"]) {
                      clearTimeout(opts["selectedTimer"]);
                  }
              }
            );
            
        }

        if(opts["guideSelected"]["enable"]) {
            $(elem).bind("click", function(e) {
            	if($(e.target).closest("a").length == 0) {
                	//e.stopPropagation(); //nn funziona piu il fancy e altre cose simili al click di un object dom
				

				}
				if(!$.fn.helperBorder.blockGuideSelected) 
					$.fn.helperBorder.showGuideSelected(elem, opts); 
            });
        }
            
        if(opts["guideSelected"]["useDrag"]) {
            $(elem).draggable({ 
                revert: "invalid"
                , snap: true
                , revert: function(valid) {
                    if(valid) {
                        allowSetProperties = true;
                        //Dropped in a valid location
                    }
                    else {
                        allowSetProperties = false;
                        //Dropped in an invalid location
                    }
                    return !valid;
                }
                , start: function( event, ui ) {
                    if(opts["selectedTimer"]) {
                        clearTimeout(opts["selectedTimer"]);
                    }

                    $(this).addClass("hb-exclude");
                    
                    if($(this).hasClass("hb-outline-selected"))
                        $(opts["guideSelected"]["elemId"]).hide();  
                    
                    $(opts["guide"]["elemId"]).hide();
                }
                , stop: function( event, ui ) {
                    $(this).removeClass("hb-exclude");
                    $(this).mouseover();
                    
                    if(opts.onDragged) {
                        if($.isFunction(opts.onDragged)) {
                            opts.onDragged(event, ui, this, allowSetProperties);
                        } else {
                            eval(opts.onDragged + '(' + event + ', ' + ui + ', ' + this + ', ' + allowSetProperties + ');');
                        }
                    }
                } 
            });
        }
        if(opts["guideSelected"]["useResize"]) {
            $(elem).resizable({ 
                containment: "parent" 
                //, helper : "resizable-helper"
                //, hendless : "n, e, s, w"
                //, ghost: true
                , grid: 1 
                , start: function( event, ui ) {
                    if(opts["selectedTimer"]) {
                        clearTimeout(opts["selectedTimer"]);
                    }

                    $(this).addClass("hb-exclude");
                    
                    if($(this).hasClass("hb-outline-selected"))
                        $(opts["guideSelected"]["elemId"]).hide();  
                    
                    $(opts["guide"]["elemId"]).hide();
                }
                , stop: function( event, ui ) {
                    $(this).removeClass("hb-exclude");
                    $(this).mouseover();
                    
                    if(opts.onResized) {
                        if($.isFunction(opts.onResized)) {
                            opts.onResized(event, ui, this);
                        } else {
                            eval(opts.onResized + '(' + event + ', ' + ui + ', ' + this + ');');
                        }
                    }
                } 
                /*
                width:600px; height:600px;background-color: transparent;
                background-size: 60px 60px;
                background-position: 0 0, 30px 30px;
                background-image: -webkit-linear-gradient(45deg, #eeeeee 25%, transparent 25%, transparent 75%, #eeeeee 75%, #eeeeee),
                                  -webkit-linear-gradient(45deg, #eeeeee 25%, transparent 25%, transparent 75%, #eeeeee 75%, #eeeeee);
                */
            });
        }
        
    }

    // Plugin defaults added as a property on our plugin function.
    $.fn.helperBorder.defaults = {
        container: "body",
        guide : {
			enable : true,
			elemId : "#hb-outline-guide",
			exclude : [],
			showInner : true,
			innerCallback : undefined,
			border : undefined,
			margin : 0
        },
        guideSelected : {
			enable : true,
			elemId : "#hb-outline-selected",
			exclude : [],
			timer : 0,
			useDrag : false,
			useResize : false,
			showInner : true,
			innerCallback : undefined,
			border : undefined,
			margin : 0
		},
        onHoverIn : undefined,
        onHoverOut : undefined,
        onClick : undefined,
        onHide : undefined,
        onDragged : undefined, 
        onResized : undefined
    };
})(jQuery);