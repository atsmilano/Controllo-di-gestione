ff.ffField.ckeditor = (function () {
	/* privates*/
	
	var that = { /* publics*/
		__ff : true, /* used to recognize ff'objects*/
		"init" : function(id, brmode, theme, skin, lang, toolbar, custom_config, isDialog) {
			var config = {};
			config.allowedContent                           = true;
			config.enterMode                                = CKEDITOR.ENTER_P;
			config.forcePasteAsPlainText 			= true;
			config.dialog_backgroundCoverColor 		= '#000'; /*The color of the dialog background cover. It should be a valid CSS color string. */
			config.dialog_backgroundCoverOpacity	= 0.7; /*The opacity of the dialog background cover. It should be a number within the range [0.0, 1.0]. */
			config.dialog_magnetDistance			= 30; /*The distance of magnetic borders used in moving and resizing dialogs, measured in pixels. */
			config.defaultLanguage					= lang; /* The language to be used if CKEDITOR.language is left empty and it's not possible to localize the editor to the user language. */
            config.language							= lang;
			config.disableNativeSpellChecker		= true; /*Disables the built-in spell checker while typing natively available in the browser (currently Firefox and Safari only).*/
			config.scayt_autoStartup				= false;
			config.removePlugins					= 'scayt';
			config.disableObjectResizing			= false; /*Disables the ability of resize objects (image and tables) in the editing area.*/
			config.colorButton_colors				= '000,800000,8B4513,2F4F4F,008080,000080,4B0082,696969,B22222,A52A2A,DAA520,006400,40E0D0,0000CD,800080,808080,F00,FF8C00,FFD700,008000,0FF,00F,EE82EE,A9A9A9,FFA07A,FFA500,FFFF00,00FF00,AFEEEE,ADD8E6,DDA0DD,D3D3D3,FFF0F5,FAEBD7,FFFFE0,F0FFF0,F0FFFF,F0F8FF,E6E6FA,FFF'; /*Defines the colors to be displayed in the color selectors. It's a string containing the hexadecimal notation for HTML colors, without the "#" prefix. */
			config.colorButton_enableMore			= false; /*Whether to enable the "More Colors..." button in the color selectors. */
			config.contentsLangDirection			= 'ltr'; /* The writting direction of the language used to write the editor contents. Allowed values are 'ltr' for Left-To-Right language (like English), or 'rtl' for Right-To-Left languages (like Arabic). */
			config.editingBlock						= true; /*Whether to render or not the editing block area in the editor interface. */
			config.entities_greek					= false; /*Whether to convert some symbols, mathematical symbols, and Greek letters to HTML entities. */
			config.entities_latin					= false; /*Whether to convert some Latin characters (Latin alphabet No. 1, ISO 8859-1) to HTML entities.*/
			config.entities_processNumerical		= true; /*Whether to convert all remaining characters, not comprised in the ASCII character table, to their relative numeric representation of HTML entity.*/
			config.extraPlugins 					= 'pagebreak,youtube,tableresize,oembed,widget,justify,videodetector';
			config.oembed_maxWidth = '560';
                        config.oembed_maxHeight = '315';
                        config.oembed_WrapperClass = 'embededContent';
                        config.youtube_width = '640';
			config.youtube_height = '480';
			config.youtube_related = true;
			config.youtube_older = false;
			config.youtube_privacy = false;
			config.find_highlight 					= { 
				element : 'span',
				styles : { 
					'background-color' : '#ff0',
					'color' : '#000'
				}
			}; /*Defines the style to be used to highlight results with the find dialog. */
			config.font_defaultLabel				= 'Arial'; /*The text to be displayed in the Font combo is none of the available values matches the current cursor position or text selection. */
			config.font_names						= 'Arial/Arial, Helvetica, sans-serif;' +
				'Times New Roman/Times New Roman, Times, serif;' +
				'Verdana'; /*The list of fonts names to be displayed in the Font combo in the toolbar. Entries are separated by semi-colons (;),*/
			config.fontSize_sizes					= '8/8px;9/9px;10/10px;11/11px;12/12px;14/14px;16/16px;18/18px;20/20px;22/22px;24/24px;26/26px;28/28px;36/36px;48/48px;72/72px'; /*The list of fonts size to be displayed in the Font Size combo in the toolbar.*/
			config.format_tags              = 'p;h1;h2;h3;h4;h5;h6;pre;address;div';
			config.format_h1						= { element : 'h1', attributes : { 'class' : 'contentTitle1' } }; /*The style definition to be used to apply the "Heading 1" format. */
			config.format_h2						= { element : 'h2', attributes : { 'class' : 'contentTitle2' } }; /*The style definition to be used to apply the "Heading 2" format. */
			config.format_h3						= { element : 'h3', attributes : { 'class' : 'contentTitle3' } }; /*The style definition to be used to apply the "Heading 3" format.*/
			config.format_h4						= { element : 'h4', attributes : { 'class' : 'contentTitle4' } }; /*The style definition to be used to apply the "Heading 4" format.*/
			config.format_h5						= { element : 'h5', attributes : { 'class' : 'contentTitle5' } }; /*The style definition to be used to apply the "Heading 5" format.*/
			config.format_h6						= { element : 'h6', attributes : { 'class' : 'contentTitle6' } }; /*The style definition to be used to apply the "Heading 6" format.*/
			config.format_p							= { element : 'p', attributes : { 'class' : 'normalPara' } }; /*The style definition to be used to apply the "Normal" format. */
			config.format_pre						= { element : 'pre', attributes : { 'class' : 'code' } }; /*The style definition to be used to apply the "Formatted" format. */
			config.format_tags						= 'p;h1;h2;h3;h4;h5;pre'; /*A list of semi colon separated style names (by default tags) representing the style definition for each entry to be displayed in the Format combo in the toolbar. Each entry must have its relative definition configuration in a setting named "format_(tagName)". For example, the "p" entry has its definition taken from format_p.*/
			config.height							= 300; /* The height of editing area( content ), in relative or absolute, e.g. 30px, 5em. Note: Percentage unit is not supported yet. e.g. 30%. */
			config.image_removeLinkByEmptyURL		= false; /*Whether to remove links when emptying the link URL field in the image dialog. */
			config.menu_groups						= 'clipboard,table,anchor,link,image'; /*A comma separated list of items group names to be displayed in the context menu. The items order will reflect the order in this list if no priority has been definted in the groups. */
			config.menu_subMenuDelay				= 0; /*The amount of time, in milliseconds, the editor waits before showing submenu options when moving the mouse over options that contains submenus, like the "Cell Properties" entry for tables. */
			config.newpage_html						= ''; /*The HTML to load in the editor when the "new page" command is executed. */
			config.pasteFromWordIgnoreFontFace		= true; /*Whether the "Ignore font face definitions" checkbox is enabled by default in the Paste from Word dialog. */
			config.pasteFromWordKeepsStructure		= true; /*Whether to keep structure markup (<h1>, <h2>, etc.) or replace it with elements that create more similar pasting results when pasting content from Microsoft Word into the Paste from Word dialog. */
			config.pasteFromWordRemoveStyle			= true; /*Whether the "Remove styles definitions" checkbox is enabled by default in the Paste from Word dialog. */
			if(isDialog) {
				config.resize_enabled					= false;
				config.extraPlugins						+= ",autogrow";
				config.height							= "auto";
			} else {
				config.resize_enabled					= true; /*Whether to enable the resizing feature. If disabed the resize handler will not be visible. */
				config.resize_maxHeight					= 400; /*The maximum editor height, in pixels, when resizing it with the resize handle. */
				config.resize_maxWidth					= 1400; /*The maximum editor width, in pixels, when resizing it with the resize handle. */
				config.resize_minHeight					= 300; /*The minimum editor height, in pixels, when resizing it with the resize handle. */
			}
			config.skin = (skin || "bootstrapck"); // The skin to load. It may be the name of the skin folder inside the editor installation path, or the name and the path separated by a comma. */
			config.smiley_descriptions				= [
				':)', ':(', ';)', ':D', ':/', ':P',
				'', '', '', '', '', '',
				'', ';(', '', '', '', '',
				'', ':kiss', '' 
			]; /*The description to be used for each of the smileys defined in the CKEDITOR.smiley_images setting. Each entry in this array list must match its relative pair in the CKEDITOR.smiley_images setting. */
			config.smiley_images					= [
				'regular_smile.gif','sad_smile.gif','wink_smile.gif','teeth_smile.gif','confused_smile.gif','tounge_smile.gif',
				'embaressed_smile.gif','omg_smile.gif','whatchutalkingabout_smile.gif','angry_smile.gif','angel_smile.gif','shades_smile.gif',
				'devil_smile.gif','cry_smile.gif','lightbulb.gif','thumbs_down.gif','thumbs_up.gif','heart.gif',
				'broken_heart.gif','kiss.gif','envelope.gif'
			]; /*The file names for the smileys to be displayed. These files must be contained inside the URL path defined with the CKEDITOR.smiley_path setting. */
			config.smiley_path						= '/images/smileys/'; /*The base path used to build the URL for the smiley images. It must end with a slash. */
			config.startupFocus						= false; /*Sets whether the editor should have the focus when the page loads. */
			config.startupMode						= 'wysiwyg'; /*The mode to load at the editor startup. It depends on the plugins loaded. By default, the "wysiwyg" and "source" modes are available. */
			config.theme							= theme; /* The theme to be used to build the UI. */
			config.toolbar							= 
                                [
                                            { name: 'document', items : [ 'mode', 'document', 'doctools' ] },
                                            { name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','-','Undo','Redo' ] },
                                            { name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','Scayt' ] },
                                            { name: 'links', items : [ 'Link','Unlink','Anchor', '-','Image', 'Youtube', 'VideoDetector', 'oembed' ] }, 
                                            { name: 'insert', items : [ 'Table','HorizontalRule','SpecialChar','PageBreak'] }, 
                                            { name: 'tools', items : [ 'Maximize', '-','Source', '-', 'Preview',  'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                                            { name: 'basicstyles', items : [ 'Bold','Italic','Strike','-','RemoveFormat' ] },
                                            { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','Align' ] },
                                            { name: 'styles', items : [ 'Styles','Format' ] },
                                            { name: 'bootstrap', items : [ 'WidgetTemplateMenu' ]}
                                ],
                        
                         
			config.toolbar_default 					= toolbar,
			config.toolbarCanCollapse				= false; /*Whether the toolbar can be collapsed by the user. If disabled, the collapser button will not be displayed. */
			config.toolbarLocation					= 'top'; /*The "theme space" to which rendering the toolbar. For the default theme, the recommended options are "top" and "bottom". */
			config.toolbarStartupExpanded			= true; /*Whether the toolbar must start expanded when the editor is loaded. */
			config.undoStackSize					= 20; /*The number of undo steps to be saved. The higher this setting value the more memory is used for it. */
			config.filebrowserBrowseUrl 			= ff.base_path + '/themes/library/kcfinder/browse.php?type:';
			config.filebrowserImageBrowseUrl 		= ff.base_path + '/themes/library/kcfinder/browse.php?type:';
			config.filebrowserFlashBrowseUrl 		= ff.base_path + '/themes/library/kcfinder/browse.php?type:';
			config.filebrowserUploadUrl 			= ff.base_path + '/themes/library/kcfinder/upload.php?type:';
			config.filebrowserImageUploadUrl 		= ff.base_path + '/themes/library/kcfinder/upload.php?type:';
			config.filebrowserFlashUploadUrl 		= ff.base_path + '/themes/library/kcfinder/upload.php?type:';
                        
			/*config.width							= '100%'; /* The editor width in CSS size format or pixel integer.*/
			
			if(0 && brmode)
				config.enterMode 					= CKEDITOR.ENTER_BR;
					
			if (custom_config === undefined)
				custom_config = {};
			
			jQuery.extend(config, custom_config);
                        
            CKEDITOR.dtd.$removeEmpty['a'] = false;
            CKEDITOR.dtd.$removeEmpty['div'] = false;
            CKEDITOR.dtd.$removeEmpty['i'] = false;

			jQuery.fn.escapeGet(id).each(function(index, textarea) {
				if (CKEDITOR.instances[textarea.id])
					return;

				CKEDITOR.replace(textarea, config);
			});
		}, 
		"onClearField" : function (component, field_id, field_data, inst_id) {
			if (field_data.widget !== "ckeditor")
				return;

			if (CKEDITOR.instances[inst_id] !== undefined) {
				CKEDITOR.remove(inst_id);
				delete CKEDITOR.instances[inst_id];
			}
		},
		"onUpdate" : function (container, ctx) {
			ff.struct.get("comps").each(function (component_id, component) {
				if ((ctx !== undefined && component.ctx === ctx) || (ctx === undefined && component.ctx === undefined)) {
					component.fields.each(function (field_id, field) {
						if (field.widget == "ckeditor") { // in dialog/dettaglio nn e valorizzato il widget. cosi nn dovrebbe andare con || 1 andava
							switch (component.type) {
								case "ffDetails":
									var rows = parseInt(jQuery("#" + component_id + "_rows").val());
									for (var i = 0; i < rows; i++) {
										if(CKEDITOR.instances[component_id + "_recordset[" + i + "][" + field_id + "]"] !== undefined)
											CKEDITOR.instances[component_id + "_recordset[" + i + "][" + field_id + "]"].updateElement();
									}
									break;

								default:
									if(CKEDITOR.instances[component_id + "_" + field_id] !== undefined)
										CKEDITOR.instances[component_id + "_" + field_id].updateElement();
							}
						}
					});
				}
			});
		}
		
	}; /* publics' end*/

	ff.pluginAddInitLoad("ff.ffField.ckeditor", function () {
		ff.addEvent({"event_name" : "onClearField", "func_name" : that.onClearField});
		ff.addEvent({"event_name" : "getFields", "func_name" : that.onUpdate});
		
		CKEDITOR.on("instanceReady", function(event) {
			var id = event.editor.name;
			CKEDITOR.instances[id].instReady = true;
			that.doEvent({
				"event_name"	: "onCreate",
				"event_params"	: [document.getElementById(event.editor.element.getId()), id]
			});
			ff.doEvent({
				"event_name" : "initIFElement"
				, "event_params" : [id, "ckeditor"]
			});
		});
	});
	
	return that;
})();