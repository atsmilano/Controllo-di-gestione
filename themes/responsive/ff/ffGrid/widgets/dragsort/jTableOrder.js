(function(jQuery) {
	jQuery.extend({
		jTableOrder: new function() {
			var lastY = 0;

			var need_select_workaround = typeof document.onselectstart != undefined;

			/* public methods */
			this.construct = function(id) {
				return this.each(function() {

					var $table = jQuery("TABLE", this);
					var sort_params = ff.ffGrid.dragsort.inst.get(id);
					
					if(!$table.length) 
                        return;
					$table.find("> thead > tr > :first").attr("colspan", 2);
					$table.find("> tbody > tr").not("tr tr").each(function (index) {
						jQuery(this).prepend('<td></td>');
                        if(sort_params.data[index] !== undefined)
						    jQuery(this).data("sort_id", sort_params.data[index].toString());
					});

					$table.find("> tbody > tr :first-child").not("tr tr td").bind("mousedown.ff.dragsort", function (e) {
                        var target = e.target || e.srcElement;
                        if (jQuery(target).is("a")
                            || jQuery(target).closest("a").length
                            || jQuery(target).is("input")
                            || jQuery(target).is("select")
                            || jQuery(target).is("textarea")
                            || jQuery(target).is("[onclick]")
                            || jQuery(target).parent().is("[onclick]")
                        )
                            return;

						var $tr = jQuery(this).parent();
						lastY = e.clientY;

						//if(jQuery(e.target).is("input,select,a") || jQuery(e.target).closest("a").length)
						//	return;

						jQuery(this).addClass("dragging");
						/*jQuery(this).children("td").css("cursor", "all-scroll");
						 This is just for flashiness. It fades the TR element out to an opacity of 0.2 while it is being moved.*/
						$tr.data("startDragging", true);
						$tr.bind("mousemove.ff.dragsort", function(e) {
							if ($tr.data("startDragging") && !$table.data("isDragging"))	{
								$tr.fadeTo("fast", 0.2);
								$table.data("isDragging", true);
							}
						});

						jQuery("> tr", $tr.parent()).not(this).bind("mouseenter.ff.dragsort", function(e){
							if (e.clientY > lastY) {
								jQuery(this).after($tr);
							} else {
								jQuery(this).before($tr);
							}
							lastY = e.clientY;
						});

						jQuery("body").bind("mouseup.ff.dragsort", function () {
							/*Fade the TR element back to full opacity */
							$tr.data("startDragging", false);
							
							jQuery("> tr", $tr.parent()).unbind('mouseenter.ff.dragsort');
							jQuery("body").unbind('mouseup.ff.dragsort');

							/* Make text selectable for IE again with
							 The workaround for IE based browsers*/
							if (need_select_workaround)
								jQuery(document).unbind("selectstart.ff.dragsort");

							if ($table.data("isDragging")) {
								$tr.fadeTo("fast", 1, function() {
									$table.data("isDragging", false);
								});
								ff.ffGrid.dragsort.reorder(id);
							}
						});

						/* This part if important. Preventing the default action and returning false will
						 Stop any text in the table from being highlighted (this can cause problems when dragging elements)*/
						e.preventDefault();

						/* The workaround for IE based browers */
						if (need_select_workaround)
							jQuery(document).bind("selectstart.ff.dragsort", function () {return false;});
						
						return false;
					});
					$table.find("> tbody > tr :first-child").not("tr tr td").bind("mouseup.ff.dragsort", function (e) {
                        var target = e.target || e.srcElement;
                        if (jQuery(target).is("a")
                            || jQuery(target).closest("a").length
                            || jQuery(target).is("input")
                            || jQuery(target).is("select")
                            || jQuery(target).is("textarea")
                            || jQuery(target).is("[onclick]")
                            || jQuery(target).parent().is("[onclick]")
                        )
                            return;

                        var $tr = jQuery(this).parent();
						lastY = e.clientY;
						//if(jQuery(e.target).is("input,select,a"))
						//	return;
						
						jQuery(this).removeClass("dragging");
							
						//jQuery(this).css("cursor", jQuery(this).attr("data-cur"));
					});
				});
			}
		}
	});

	jQuery.fn.extend({
		jTableOrder: jQuery.jTableOrder.construct
	});

})(jQuery);