jQuery.fn.selectText = function(){
	var doc = document, element = this[0], range, selection;
	if (doc.body.createTextRange) {
		range = document.body.createTextRange();
		range.moveToElementText(element);
		range.select();
	} else if (window.getSelection) {
		selection = window.getSelection();        
		range = document.createRange();
		range.selectNodeContents(element);
		selection.removeAllRanges();
		selection.addRange(range);
	}
};

ff.modules.restricted = (function () {
    const MOBILE_WIDTH 		= 768;
    const SCROLL_PARAMS		= {
							  cursorcolor:"#1976d2",
							  cursorborder:"0",
							  cursorwidth:"5px"
							};
	var tscroll;
  	var dragSelector 		= ".-draggable";
  	var dropSelector 		= ".-droppable";
  	var menuSelector 		= ".sidemenu > UL";
  	var menuActiveSelector 	= ".active";
    var menuToggleSelector 	= ".mainmenu_toggle";
    var scrollSelector 		= ".-scrollable";
    var contentSelector     = "#content";


  	

	var that = { // publics
		__ff : true, // used to recognize ff'objects
		"init" : function(params) {
			if(params) {
				if(params["menu"]) 		menuSelector = params["menu"];
				if(params["active"]) 	menuActiveSelector = params["active"];
				if(params["toggle"]) 	menuToggleSelector = params["toggle"];
				if(params["drag"]) 		dragSelector = params["drag"];
				if(params["drop"]) 		dropSelector = params["drop"];
				if(params["scroll"]) 	scrollSelector = params["scroll"];
				if(params["content"]) 	contentSelector = params["content"];
			}
			this.menu();
			this.scroll();
		},
		"drag" : function(callback) {
			jQuery(dragSelector).mousedown(function() {
				jQuery(this).selectText();
			}); 
			jQuery(dragSelector).on("dragstart", function(e) {
			  /*  var img = document.createElement("img");
			    img.src = "http://kryogenix.org/images/hackergotchi-simpler.png";
			    e.originalEvent.dataTransfer.setDragImage(img, 0, 0);*/
			    
			    jQuery(dropSelector).addClass("-dragging");
			    
			    if(jQuery(this).attr("rel"))
			   		e.originalEvent.dataTransfer.setData("Text", jQuery(this).attr("rel"));
				
				if(callback)
					callback("ondragstart", e);
			});		
			jQuery(dragSelector).on("dragend", function(e) {
				jQuery(dropSelector).removeClass("-dragging");
				jQuery(dropSelector).removeClass("-dragover");
				
				if(callback)
					callback("ondragend", e);
			});
			
			jQuery(dropSelector).on("dragover", function(e) {
				e.originalEvent.preventDefault();
				var dropZone = (jQuery(e.originalEvent.target).hasClass(dropSelector)
					? jQuery(e.originalEvent.target)
					: jQuery(e.originalEvent.target).closest(dropSelector)
				);
				if(!dropZone.hasClass("-dragover")) {
					dropZone.addClass("-dragover");
					if(callback)
						callback("ondragover", e, dropZone);
				}
			});		
			jQuery(dropSelector).on("dragleave", function(e) {
				var dropZone = (jQuery(e.originalEvent.target).hasClass(dropSelector)
					? jQuery(e.originalEvent.target)
					: jQuery(e.originalEvent.target).closest(dropSelector)
				);
				if(dropZone.hasClass("-dragover")) {
					dropZone.removeClass("-dragover");
				    if(callback) {
                        callback("ondragleave", e, dropZone);
                    }
				}
			});		
			jQuery(dropSelector).on("drop", function(e) {
				var dropZone = (jQuery(e.originalEvent.target).hasClass(dropSelector)
					? jQuery(e.originalEvent.target)
					: jQuery(e.originalEvent.target).closest(dropSelector)
				);

			    if(callback)
			    	callback("ondrop", e, dropZone);
			});					

						
			
		},
		"menu" : function () {
            var that = this;
			jQuery(menuSelector + " .-floating").each(function() {
				jQuery(this).appendTo(jQuery(this).closest("UL").parent()).fadeIn();
			});
		
			jQuery(menuSelector + " a[data-toggle='collapse']").click(function() {
                jQuery(this).parent().find(".menu-caret").toggleClass("fa-rotate-90");

                if(!jQuery(jQuery(this).attr("href")).hasClass("in")) {
                    jQuery(menuSelector + " a[aria-expanded='true']").click();
                    //jQuery(menuSelector + " a[data-toggle='collapse']").addClass("collapsed").attr("aria-expanded", 'false');
                    //jQuery(menuSelector + " .fa-rotate-90").removeClass("fa-rotate-90");


                }

            });

			jQuery(menuToggleSelector).click(function() {
			    that.menuToggle(this);
            })

		},
		"menuToggle": function(elem) {
		    var active = menuActiveSelector.ltrim(".");

            jQuery(menuSelector + ', ' + contentSelector).toggleClass(active);
            jQuery(elem).toggleClass(active);

            this.scrollDisplay(scrollSelector);
		},
		"scroll" : function() {
			var that = this;
            jQuery(scrollSelector).niceScroll(SCROLL_PARAMS);


            jQuery(scrollSelector + " a[data-toggle='collapse']").click(function() {
				that.scrollResize(scrollSelector);
            });
            jQuery(window).on('resize', function() {
            	that.scrollResize(scrollSelector);
			});
		},
		"scrollResize" : function(elem) {
			clearTimeout(tscroll);
			tscroll = setTimeout(function() {
				elem = elem || scrollSelector;

                jQuery(elem).getNiceScroll().resize();
                jQuery(elem).getNiceScroll().show();
				jQuery(elem).one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", function(event) {
					jQuery(elem).getNiceScroll().resize();
				});
            }, 100);
		},
        "scrollDisplay" : function(elem) {
            var active = menuActiveSelector.ltrim(".");

            elem = elem || scrollSelector;
            jQuery(elem).getNiceScroll().hide();
            if(jQuery(window).width() > MOBILE_WIDTH && jQuery(elem).hasClass(active)) {
                console.log("diaplay hide1");
                jQuery(elem).one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", function (event) {
                    jQuery(elem).getNiceScroll().hide();
                });
            } else if(jQuery(window).width() <= MOBILE_WIDTH && !jQuery(elem).hasClass(active)) {
                console.log("diaplay hide2");
                jQuery(elem).one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", function (event) {
                    jQuery(elem).getNiceScroll().hide();
                });
            } else {
            	console.log("diaplay show");
                that.scrollResize(elem);
            }
        }
	};
	
	jQuery(function() {
		that.init();
	});
	
	return that;

})();