ff.ffField.codemirror = (function () {
    var that = { /* publics*/
            __ff : true, /* used to recognize ff'objects*/
            "init" : function(id, syntax, lang, writable) {
                ff.addEvent({"event_name" : "getFields", "func_name" : that.onUpdateDialog});
				if(!syntax)
					syntax = "javascript";
					
				var hint = syntax;
				if(syntax == "html") {
					syntax = "text/html";
					hint = "html";
				}
                CodeMirror.commands.autocomplete = function(cm) {
                    CodeMirror.showHint(cm, CodeMirror.hint[hint], {"container":document.getElementById(id).parentNode});
                };

                var elem = document.getElementById(id);
                var editor = CodeMirror.fromTextArea(elem
				            , { 
				                    mode: syntax,
				                    lineNumbers: true,
				                    lineWrapping: true,
				                    extraKeys: {"Ctrl-Space": "autocomplete"},
				                    styleActiveLine: true,
				                    autoCloseBrackets: true
				            });
				/*setTimeout(function() {
                    editor.refresh();
                }, 10);  */
				jQuery(elem).data("codeMirrorInstance", editor);
				ff.pluginAddInit("ff.ffPage.tabs", function () {
					ff.ffPage.tabs.addEvent({
						"event_name"	: "onActivate",
						"func_name"		: function (id, event, ui) {
							jQuery(ui.newPanel).find(".ffWidget_codemirror").each(function(i, el){
								jQuery(this).data("codeMirrorInstance").refresh();
							});
						}
					});
				});
				that.doEvent({
					"event_name"	: "onCreate",
					"event_params"	: [elem, editor]
                });
                ff.doEvent({
					"event_name" : "initIFElement"
					, "event_params" : [id, "codemirror"]
				});
                editor.on("blur", function() {editor.save()});
            }, 
			"onUpdateDialog" : function (id, ctx) {
				ff.struct.get("comps").each(function (component_id, component) {
					if ((ctx !== undefined && component.ctx === ctx) || (ctx === undefined && component.ctx === undefined)) {
						component.fields.each(function (field_id, field) {
							if (field.widget == "codemirror") {
								switch (component.type) {
									case "ffDetails":
										var rows = parseInt(jQuery("#" + component_id + "_rows").val());
										for (var i = 0; i < rows; i++) {
											jQuery("#" + component_id + "_recordset\\[" + i + "\\]\\[" + field_id + "\\]").data("codeMirrorInstance").save();
										}
										break;

									default:
										jQuery("#" + component_id + "_" + field_id).data("codeMirrorInstance").save();
								}
							}
						});
					}
				});
			}
	    
	}; /* publics' end */

	return that;
})();