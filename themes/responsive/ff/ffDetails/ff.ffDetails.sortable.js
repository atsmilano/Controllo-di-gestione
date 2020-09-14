ff.ffDetails.sortable = (function () {

ff.pluginAddInitLoad("ff.ffDetails.sortable", function () {
	ff.addEvent({
		'event_name' : 'onClearComponent'
		, 'func_name' : ff.ffDetails.sortable.detach
	});
}, 'b701d1d0-95b8-4912-b8d1-221491e11af7');

var that = { /* publics */
__ff : true, /* used to recognize ff'objects*/

"inst" : ff.hash(),

"init" : function (params) {
	if (that.inst.get(params.id) === undefined) {
		that.inst.set(params.id, params);

		jQuery("#" + params.id + "_items").sortable({
			items: 		"li:not(.not-draggable)"
			, create:	function (ev, ui) {
				that.reorder(params.id);
			}
			, update:	function (ev, ui) {
				that.reorder(params.id);
			}
		});
	}
},

"detach" : function (component) {
	if (that.inst.get(component) !== undefined) {
		jQuery("#" + component + "_items").sortable("destroy");
		that.inst.unset(component);
	}
},

"reorder" : function (id) {
	jQuery("#" + id + "_items li:not(.not-draggable) input[name^='" + id + "_sort']").each(function (idx, itm) {
		jQuery(itm).val(idx);
	});
}

}; /* publics' end*/

return that;

/* code's end.*/
})();
