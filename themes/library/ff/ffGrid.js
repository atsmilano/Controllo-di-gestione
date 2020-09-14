/**
 * Forms Framework Javascript Handling Object
 *	ffGrid' namespace
 */

ff.ffGrid = (function () {
// inits
ff.pluginAddInit("ff.ajax", function () {
	ff.ajax.addEvent({
		"event_name"	: "onUpdateComponent",
		"func_name"		: function (componentid, component, data) {
			if (component.type == "ffGrid") {
				if (component.dialog) {
					ff.ffPage.dialog.doRequest(component.dialog, {
						"action"		: "refresh",
						"component"		: componentid,
						"section"		: (ff.struct.get(componentid).update_all || data.update_all ? undefined : "GridData"),
						"chainupdate"	: true
					});
				} else {
					ff.ajax.doRequest({
							"component"	: componentid,
							"section"		: (ff.struct.get(componentid).update_all || data.update_all ? undefined : "GridData"),
							"chainupdate"	: true
					});
				}
				return true;
			}
		}
	});
});

// privates

var that = { // publics
__ff : true, // used to recognize ff'objects
"icons" : {
	"sort" : "fa-sort"
	, "sortAsc" : "fa-sort-asc"
	, "sortDesc" : "fa-sort-desc"
},
"class" : {
	"current" : "active"
},
"ajaxOrder" : function (elem, id, field, dir, dialog) {
	jQuery("#frmAction").val(id + "_order"); 
	if (dir === undefined) {
		if (jQuery("#" + id + "_order").val() != field || jQuery("#" + id + "_direction").val() == "DESC")
			jQuery("#" + id + "_direction").val("ASC");
		else
			jQuery("#" + id + "_direction").val("DESC");
	} else {
		jQuery("#" + id + "_direction").val(dir);
	}
    jQuery("#" + id + "_order").val(field);  

    jQuery("#" + id + " .ff-sort").parent().removeClass(this.class.current);
    jQuery("#" + id + " .ff-sort i").removeClass(this.icons.sortAsc + " " + this.icons.sortDesc).addClass(this.icons.sort);
    jQuery(elem).parent().addClass(this.class.current);
    jQuery("i", elem).removeClass(this.icons.sort + " " + this.icons.sortAsc + " " + this.icons.sortDesc).addClass(jQuery("#" + id + "_direction").val() == "ASC" ? this.icons.sortAsc : this.icons.sortDesc);

	if (dialog === undefined) {
	    ff.pluginLoad("ff.ajax", "/themes/library/ff/ajax.js", function() {
			ff.ajax.doRequest({
				 "component"		: id
				 , "section"			: "GridData"
			});
		});
	} else {
		ff.ffPage.dialog.doRequest(dialog, {
			"action"	: "order"
			, "component" : id
			, "section"	: "GridData"
		});
		//ff.ffPage.dialog.doRequest(dialog, "order", id, undefined, undefined, id + "_GridData");
	}
},

"turnToggle" : function (component, nome, value) {
	var re = new RegExp("^" + component + "_recordset_values\\[\\d+\\]\\[" + nome + "\\]$");

	if(value > 0) {
		jQuery("input").filter(function() {
			return this.id.match(re);
		}).prop("checked", false).trigger("change");
		
		return 0;
	} else {
		jQuery("input").filter(function() {
			return this.id.match(re);
		}).prop("checked", true).trigger("change");

		return 1;
	}
},

"turnON" : function (component, nome) {
	var re = new RegExp("^" + component + "_recordset_values\\[\\d+\\]\\[" + nome + "\\]$");
	jQuery("input").filter(function() {
		return this.id.match(re);
	}).prop("checked", true).trigger("change");
},

"turnOFF" : function (component, nome) {
	var re = new RegExp("^" + component + "_recordset_values\\[\\d+\\]\\[" + nome + "\\]$");
	jQuery("input").filter(function() {
		return this.id.match(re);
	}).prop("checked", false).trigger("change");
},
"advsearchHide" : function(component) {
	jQuery('#' + component + ' .adv-search').addClass('hidden'); 

//	jQuery('#' + component + '_search').removeClass('hidden');
//	jQuery('#' + component + '_searchadv').addClass('hidden');
},
"advsearchShow" : function(component) {
	jQuery('#' + component + ' .adv-search').removeClass('hidden');
	
//	jQuery('#' + component + '_search').addClass('hidden');
//	jQuery('#' + component + '_searchadv').removeClass('hidden');
},
"advsearchToggle" : function(component, visibility) {
	if(visibility === undefined) {
		if(!jQuery('#' + component + ' .adv-search').hasClass('hidden')) { 
			ff.ffGrid.advsearchHide(component);
		} else { 
			ff.ffGrid.advsearchShow(component);
		}
	} else {
		if(visibility == "hidden") {
			ff.ffGrid.advsearchHide(component);
		} else {
			ff.ffGrid.advsearchShow(component);
		}
	}
},
"dropdownSort" : function(component, id) {
	jQuery("#" + id + " ul.sort").appendTo(jQuery("#" + id + " .dropdown-sort-data"));
	jQuery("#" + id + " ul.sort a.GridSortIcons").hide();

	if(!jQuery("#" + id + " .sort-block").hasClass("loaded")) {
		jQuery("#" + id + " .dropdown-sort").click(function() {
			if(jQuery("#" + id + " ul.sort").is(":visible")) {
				jQuery("#" + id + " ul.sort").slideUp();
			} else {
				jQuery("#" + id + " ul.sort").slideDown();
			}
		});

		jQuery("#" + id + " ul.sort a.FormsGridLabel").bind("click", function() {
			jQuery("#" + id + " ul.sort li").removeClass("current");
			jQuery(this).parent().addClass("current");
			jQuery("#" + id + " .dropdown-sort").html(jQuery(this).text() + '<span class="arrow"></span>');
			jQuery("#" + id + " .dropdown-sort-dir").html(jQuery(this).parent().children(".GridSortIcons").children('span').parent());
			jQuery("#" + id + " .dropdown-sort-dir > *").hide();
			jQuery("#" + id + " .dropdown-sort-dir span." + (jQuery("#" + component + "direction").val() == 'ASC' ? 'descending' : 'ascending')).parent().show();

			jQuery("#" + id + " ul.sort").hide();
		});
		jQuery("#" + id + " .dropdown-sort-dir").bind("click", function() {
			jQuery(this).children("a").hide();
			jQuery("#" + id + " .dropdown-sort-dir span." + (jQuery("#" + component + "direction").val() == 'ASC' ? 'descending' : 'ascending')).parent().show();
		});
		jQuery("#" + id + " .sort-block").addClass("loaded");
	}
},
"dialogOpen" : function(record_id, url, title, elemHighlight) {
	ff.ffPage.dialog.doOpen(record_id, url, title, undefined, elemHighlight);
	
}

}; // publics' end

return that;

// code's end.
})();
