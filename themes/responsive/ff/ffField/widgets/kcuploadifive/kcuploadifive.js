ff.ffField.kcuploadifive = (function () {
	var basePath = "/themes/library/plugins/jquery.uploadifive";
	var previewPath = "/cm/showfiles.php";
	var plugins = {
		"fancybox" : {}
	};		

	var basePathKC = "/themes/library/kcfinder";
	var data = {};
	

	var that = { /* publics*/
		__ff : true, /* used to recognize ff'objects*/
		
		"init" : function (params) {
			var component = params.component;
			data[component] = {};
			data[component]["start"]			= params.start;
			data[component]["target"]			= params.target;
			data[component]["tmpname"]			= params.tmpname;
			data[component]["sufdel"]			= params.sufdel;
			data[component]["basePath"]			= ff.site_path + basePath;
			data[component]["previewJs"]		= params.previewJs;
            data[component]["writable"]         = params.writable;
			data[component]["sizeLimit"]		= params.sizeLimit;
			data[component]["fileExt"]			= params.fileExt;
			data[component]["fileNormalize"]	= params.fileNormalize;
			data[component]["multi"]			= params.multi;
			data[component]["showFilePath"]		= params.showFilePath;
			data[component]["showFileDialog"]	= params.showFileDialog;
			data[component]["showFilePlugin"]	= params.showFilePlugin;
			data[component]["showFileSort"]		= params.showFileSort;
			data[component]["modelThumb"]		= params.modelThumb;
			data[component]["showFile"]			= params.showFile;
            data[component]["fullPath"]         = params.fullPath;
            data[component]["dataSrc"]          = params.dataSrc;
            data[component]["folder"]           = params.folder;
            
            data[component]["width"]           	= params.width;
            data[component]["height"]           = params.height;
            
            data[component]["aviary"]			= params.aviary;

            data[component]["icons"]            = params.icons;

			data[component]["previewPath"]		= ff.site_path + previewPath
			data[component]["baseUrlKC"]		= params.baseUrlKC;

			data[component]["startComponent"] 	= document.getElementById(component + params.start);
			data[component]["targetComponent"] 	= document.getElementById(component + params.target);
			data[component]["tmpnameComponent"] = document.getElementById(component + params.tmpname);
			data[component]["deleteComponent"] 	= document.getElementById(component + params.sufdel);
			data[component]["kcFinderComponent"]= document.getElementById("KcFinder_" + component);
			
			data[component]["idComponent"] 		= component.replace(/[^a-zA-Z0-9]+/g,'');

			if(!data[component]["width"])
				data[component]["width"] = 100;
			if(!data[component]["height"])
				data[component]["height"] = 100;
			
            if(data[component]["modelThumb"] == '')
                data[component]["modelThumb"] = data[component]["width"] + "x" + data[component]["height"];  
		
		    var thisData = data[component];

            jQuery(thisData["startComponent"]).attr("id", thisData["idComponent"]);
            
            
            
            /*jQuery(thisData["startComponent"]).parent().find(".preview").prepend('<div class="uploadifiveQueue" id="' + thisData["idComponent"] + 'Queue"></div>');*/

			var previewUrl = '';
			

			jQuery("#" + thisData["idComponent"]).parent().find(".preview").attr("id", "uploadifive_" + thisData["idComponent"]);
			jQuery("#" + thisData["idComponent"]).parent().find(".preview").addClass("uploadifiveQueueItem");
			jQuery("#" + thisData["idComponent"]).parent().find(".preview").find(".cancel").addClass(thisData["icons"]["cancel"]);
			
			jQuery(thisData["kcFinderComponent"]).appendTo(jQuery("#" + thisData["idComponent"]).parent().find("label"));
			
			if(!jQuery("#uploadifive_" + thisData["idComponent"] + "_preview").length) {
				var previewElem = jQuery("#" + thisData["idComponent"]).parent().find(".uploaded-preview");
				if(previewElem.length) {
					previewElem.addClass("uploadifiveQueueItem").attr("id", 'uploadifive_' + thisData["idComponent"] + '_preview');
					var previewThumbElem = jQuery(".uploaded-thumb", previewElem);
					if(thisData["previewJs"] && previewThumbElem.length) {
						previewThumbElem.each(function() {
                            if(jQuery("> a", this).length) {
							    var showFileClass = "";
							    var showFileDetail = "";

							    if(thisData["modelThumb"] == '') {
							        previewUrl = thisData["previewPath"] /*+ relativePath*/;
								} else {
									previewUrl = thisData["previewPath"] + "/" + thisData["modelThumb"] /*+ relativePath*/;
								}
							    if(thisData["showFilePlugin"]) {
								    showFileClass = thisData["showFilePlugin"];
							    } else {
								    showFileClass = "origin-file";
							    }

							  //  jQuery("> a", this).addClass(showFileClass);
/*
							    var descBlock = jQuery("> a", this).attr("title");
							    var fileValue = jQuery("> a img", this).attr("src");
							    var showFileDetail = jQuery("> a", this).attr("href");
							    
							    var fileValueNormalized = fileValue.replace(/[^a-zA-Z0-9]+/g,'');

							    if(showFilePath) {
							    	showFileClass += " fancybox.ajax";
								    showFileDetail = showFilePath + '/' + fileValue.trim("/") + "?XHR_COMPONENT=GalleryModify";
								} else {
								    showFileDetail = thisData["previewPath"] + '/' + fileValue.trim("/");
								}
								var arrFileValue = fileValue.split("/");
								
							    jQuery("> a", this).replaceWith('<a href="' + showFileDetail + '" class="' + showFileClass + '" target="_blank"' + ' title="' + descBlock + '" rel="' + previewUrl + '"><img id="' + fileValueNormalized + '" class="image" src="' + previewUrl + fileValue + '" /></a>');
							    jQuery(this).addClass(fileValueNormalized).data("url", arrFileValue[arrFileValue.length - 1]);
*/
                            } else {
                                jQuery("> span.noimage", this).width(thisData["width"]).height(thisData["height"]);
                                jQuery("> span.noimage", this).css({
                                    "font-size": (thisData["width"] / 2) + "px"
                                    , "line-height": thisData["width"] + "px"
                                });
                            }
						});
					}
				} else {
					jQuery('<div class="uploaded-preview uploadifiveQueueItem" id="uploadifive_' + thisData["idComponent"] + '_preview"></div>').insertAfter("#" + thisData["idComponent"]);
				}
			}

			if(thisData["showFileSort"]) {
				ff.pluginAddInit("jquery-ui", function() {
					jQuery("#uploadifive_" + thisData["idComponent"] + "_preview").sortable({
						"stop": function () {
                            if(thisData["showFileSort"] === true) {
                                var inputThumb = [];
                                jQuery("#uploadifive_" + thisData["idComponent"] + "_preview .uploaded-thumb").each(function () {
                                    inputThumb.push(jQuery(this).data("url"));
                                });

                                jQuery(thisData["targetComponent"]).val(inputThumb.join(","));
                            } else {
								var $container = jQuery("#uploadifive_" + thisData["idComponent"] + "_preview");
								var destFolder = thisData["folder"].replace("/tmp/", "/");
								var toBeSent = [];
								var position = 1;
								$container.find("> DIV").each(function () {
									toBeSent.push({
										"name": "positions[]",
										"value": jQuery(this).data("url").replace(destFolder, '').trim("/")
									});
									position += 1;
								});

								toBeSent.push({name: "resource", value: 'uploadifive'});

								ff.load("ff.ajax", function () {
									ff.ajax.blockUI();
									jQuery.ajax({
										"url": thisData["showFileSort"] + destFolder
										, "async": true
										, "type": "POST"
										, "data": toBeSent
										, "success": function (data) {
											var inputThumb = [];
											jQuery("#uploadifive_" + thisData["idComponent"] + "_preview .uploaded-thumb").each(function () {
												inputThumb.push(jQuery(this).data("url"));
											});

											jQuery(thisData["targetComponent"]).val(inputThumb.join(","));

											ff.ajax.unblockUI();
										}
									});
								});
                            }
						}
					});
				});
			}
			
			if(!jQuery('#uploadifive_' + thisData["idComponent"] + '_queue').length) {
				jQuery('<div id="uploadifive_' + thisData["idComponent"] + '_queue"></div>').insertAfter("#uploadifive_" + thisData["idComponent"] + '_preview');
			}
			
			

			var scriptData = {};
			scriptData['sess'] = thisData["dataSrc"];
			scriptData['folder'] = thisData["folder"];
			if(thisData["fileExt"])
				scriptData['fileExt'] = thisData["fileExt"].replace("|", ",");

			scriptData['fileNormalize'] = (thisData["fileNormalize"] ? "1" : "");
			
			if(thisData["showFilePlugin"]) {
				if(plugins[thisData["showFilePlugin"]]) {
					switch(thisData["showFilePlugin"]) {
						case "fancybox": 
							ff.load("jquery.plugins.fancybox", function() {
								jQuery(".fancybox").fancybox({
									"parent" : (jQuery("#" + thisData["idComponent"]).closest(".ui-widget-overlay").length ? ".ui-widget-overlay:last" : "body")
								});
							});
							break;
						default:
					}
				}
			}
		    jQuery("#" + thisData["idComponent"]).hide().uploadifive({
                'uploadScript'      : thisData["basePath"] + '/uploadifive.php',
                'formData'			: scriptData,
                'buttonText'     	: '', 
                'buttonClass'     	: thisData["icons"]["upload"], 
                'auto'           	: true,
                'multi'          	: thisData["multi"], 
                'fileSizeLimit'  	: thisData["sizeLimit"],
                'fileType'         	: scriptData['fileExt'],
                'removeCompleted' 	: false,
	            'width'				: thisData["width"], 
	            'height'			: thisData["height"], 
                'queueID'        	: 'uploadifive_' + thisData["idComponent"] + "_queue", /* The optional ID of the queue container*/
				onInit				: function() {
					jQuery("#uploadifive-" + thisData["idComponent"]).css({
						"font-size": (thisData["width"] / 2) + "px"
					}).closest(".uploadifive")
					.on("dragover", dragEnter)
	                .on("dragenter", dragEnter)
	                .on("dragleave", dragLeave)
	                .on('drop', dragLeave);
					
					
					function dragEnter(event) {
						dt = event.originalEvent.dataTransfer;
						if (!dt) return
						
						if (dt.types.contains && !dt.types.contains ('Files')) return //FF
						if (dt.types.indexOf && dt.types.indexOf ('Files') == -1) return //Chrome
						//if ($.browser.webkit) dt.dropEffect = 'copy';

						jQuery("#uploadifive-" + thisData["idComponent"]).addClass("dropzone");

					};
					function dragLeave() {
					    jQuery("#uploadifive-" + thisData["idComponent"]).removeClass("dropzone");
					};					
					
				},
				onAddQueueItem : function(file) {
					jQuery("#uploadifive_" + thisData["idComponent"] + "_queue .uploadifive-queue-item").width(thisData["width"]).height(thisData["height"]);
				},
		        onSelect		 	: function(queue) {
					if(!thisData["multi"])
		        		ff.ffField.kcuploadifive.del(component);

		        	jQuery("DIV.actions .activebuttons").attr("disabled", "disabled").css({ "opacity" : "0.6" });
				},
				onCancel : function() {
		        	jQuery("DIV.actions .activebuttons").removeAttr("disabled").css({ "opacity" : "1" });
				},
		        onUploadComplete : function(fileObj, response) {
		        	jQuery("DIV.actions .activebuttons").removeAttr("disabled").css({ "opacity" : "1" });

		        	var previewBlock = '';
		        	var strResponse = '';
		        	var editBlock = '';
		        	var cancelBlock = '';
		        	var actionsBlock = '';
		        	var itemBlock = '';
					
					var response = jQuery.parseJSON(response);
					
					if(response["status"]) {
						ff.ffField.kcuploadifive.setFileField(component, response["fullpath"], response["name"], fileObj);
					} else {
						alert(response["error"]);
					}
				}, 
                onError: function (errorType) {
                	jQuery("DIV.actions .activebuttons").removeAttr("disabled").css({ "opacity" : "1" });
                	
                    alert('The error was: ' + errorType);
                }
		    });		
		},
		"del" : function(component, container) {
			var thisData = data[component];

			if(container) {
				if(jQuery("#uploadifive_" + thisData["idComponent"] + "_preview ." + container).hasClass("tmp")) {
					jQuery.post(thisData["basePath"] + '/uploadifive.php', 'delaction=' + jQuery("#uploadifive_" + thisData["idComponent"] +"_preview ." + container).data("url") + "&" + 'sess' + "=" + thisData["dataSrc"] + "&folder=" + thisData["folder"], function(data) {
					}, "json");
				}
				jQuery("#uploadifive_" + thisData["idComponent"] + "_preview ." + container).remove();			
			} else {
				jQuery("#uploadifive_" + thisData["idComponent"] + "_preview .noimg:first").remove();	
			}

			var inputElem = [];
			var inputTmpElem = [];
			jQuery("#uploadifive_" + thisData["idComponent"] + "_preview .uploaded-thumb").each(function() {
				if(jQuery(this).data("url")) {
					inputElem.push(jQuery(this).data("url"));
					if(jQuery(this).hasClass("tmp"))
						inputTmpElem.push(jQuery(this).data("url"));
				}
			});
			if(inputElem.length) {
				jQuery(thisData["targetComponent"]).val( inputElem.join(",") );
				//jQuery(thisData["tmpnameComponent"]).val( jQuery(thisData["targetComponent"]).val());
				jQuery(thisData["tmpnameComponent"]).val( inputTmpElem.join(",") );
				//jQuery(thisData["deleteComponent"]).val("");
			} else {
				jQuery(thisData["targetComponent"]).val("");
				//jQuery(thisData["tmpnameComponent"]).val( jQuery(thisData["targetComponent"]).val());
				jQuery(thisData["tmpnameComponent"]).val("");
				//jQuery(thisData["deleteComponent"]).val("");
			}

			return ;
		},
		"openKCFinder" : function (component, urlSelect) {
		    window.KCFinder = {
		        callBack: function(url) {
		            window.KCFinder = null;
		            var arrUrl = url.trim("/").split("/");
					if(arrUrl[0] == "uploads")
						arrUrl.shift();

					ff.ffField.kcuploadifive.setFileField(component, "/" + arrUrl.join("/"), arrUrl[arrUrl.length -1]);
		            //kcUploadifiveSetFileField(url, field);
		        }
		    };

		    window.open(ff.base_path + basePathKC + '/browse.php?type=&dir=' + urlSelect, 'kcfinder_textbox',
		        'status=0, toolbar=0, location=0, menubar=0, directories=0, ' +
		        'resizable=1, scrollbars=0, width=800, height=600'
		    );
		},
		"setFileField" : function ( component, fileValue, fileValueOut, fileObj ) {
		    var previewBlock = '';
		    var strResponse = '';
		    var editBlock = '';
		    var cancelBlock = '';
		    var actionsBlock = '';
		    var itemBlock = '';
			
			var fileValueNormalized = fileValueOut.replace(/[^a-zA-Z0-9]+/g,'');
			
			var byteSize = 0;
			var suffix = '';
			
			var thisData = data[component];
			
	        if(fileObj) {
		        byteSize = Math.round(fileObj.size / 1024 * 100) * .01;
		        suffix = 'KB';
		        if (byteSize > 1000) {
		            byteSize = Math.round(byteSize *.001 * 100) * .01;
		            suffix = 'MB';
		        }
		        var sizeParts = byteSize.toString().split('.');
		        if (sizeParts.length > 1) {
		            byteSize = sizeParts[0] + '.' + sizeParts[1].substr(0,2);
		        } else {
		            byteSize = sizeParts[0];
		        }
			}
	        if(thisData["writable"]) {
	            strResponse = '<input type="text" id="' + thisData["targetComponent"] + '" name="' + thisData["targetComponent"] + '" value="' + fileValueOut + '" />';
	        }

			if(thisData["previewJs"]) {
				var showFileClass = "";
				var showFileDetail = "";
		        var descBlock = '';

				if(thisData["showFile"]) {
					if(byteSize)
						descBlock = ' title="' + fileValueOut + ' (' + byteSize + suffix + ')' + '"';
					else
						descBlock = ' title="' + fileValueOut;
				}
				if(thisData["modelThumb"] == '') {
					previewUrl = thisData["previewPath"] /*+ relativePath*/;
				} else {
					previewUrl =thisData["previewPath"] + "/" + thisData["modelThumb"] /*+ relativePath*/;
				}

				if(thisData["showFilePlugin"]) {
					showFileClass = thisData["showFilePlugin"];
				} else {
					showFileClass = "origin-file";
				}
				if(thisData["showFilePath"]) {
					showFileDetail = thisData["showFilePath"] + '/' + fileValue.trim("/tmp/", "").trim("/"); 
					if(thisData["showFileDialog"]) {
						showFileClass = " dialog.ajax";       
                        showFileAjaxDetail = "onclick=\"ff.ffPage.dialog.doOpen('" + thisData["showFileDialog"] + "', '" + showFileDetail + "', undefined, undefined, jQuery(this).closest('.uploaded-thumb'));\"";
                        showFileDetail = "javascript:void(0);";
					}
				} else {
					showFileDetail = thisData["previewPath"] + '/' + fileValue.trim("/");
				}
				previewBlock = '<a href="' + showFileDetail + '" class="' + showFileClass + '" target="_blank"' + descBlock + ' rel="' + previewUrl + '" ' + showFileAjaxDetail + '><img id="' + fileValueNormalized + '" class="image" src="' + previewUrl + fileValue + '" /></a>';
			}
			if(thisData["aviary"])
				editBlock = '<a href="javascript:void(0);" alt="modify" class="' + thisData["icons"]["aviary"] + '" onclick="ff.load(\'ff.ffField.aviary\', function() { ff.ffField.aviary.launch(\'' + fileValueNormalized + '\', \'' + fileValue + '\', \'' + thisData["aviary"]["img_hash"] + '\', \'' + thisData["aviary"]["key"] + '\', \'' + thisData["aviary"]["tools"] + '\', \'' + thisData["aviary"]["theme"] + '\', \'' + thisData["aviary"]["version"] + '\', \'' + thisData["aviary"]["post_url"] + '\'); });"></a>';

			cancelBlock = '<a href="javascript:ff.ffField.kcuploadifive.del(\'' + component + '\', \'' + fileValueNormalized + '\');" alt="delete" class="' + thisData["icons"]["cancel"] + '"></a>';
				
			if(editBlock || cancelBlock)
				actionsBlock = '<div class="icons-abs">' + editBlock + cancelBlock + '</div>';
				
			//fileValueNormalized
			itemBlock = '<div class="uploaded-thumb tmp ' + fileValueNormalized + '" data-url="' + fileValueOut + '">'
							+ actionsBlock
							+ previewBlock
							+ strResponse
						+ '</div>';

			if(fileObj)				
				jQuery(fileObj["queueItem"]).remove();

			if(jQuery("#uploadifive_" + thisData["idComponent"] + "_preview ." + fileValueNormalized).length) {
				var oldDataValue = jQuery("#uploadifive_" + thisData["idComponent"] + "_preview ." + fileValueNormalized).data("url");
				var inputElemOld = jQuery(thisData["targetComponent"]).val().split(",");
				var newInputElem = [];
				inputElemOld.each(function(key, value) {
					if(value && value != oldDataValue) {
						newInputElem.push(value); 									
					}
				});
				jQuery(thisData["targetComponent"]).val(newInputElem.join(","));
			
				jQuery("#uploadifive_" + thisData["idComponent"] + "_preview ." + fileValueNormalized).replaceWith(itemBlock);
			} else {
				if(thisData["multi"])
					jQuery("#uploadifive_" + thisData["idComponent"] + "_preview").append(itemBlock);
				else 
					jQuery("#uploadifive_" + thisData["idComponent"] + "_preview").html(itemBlock);
			}

			var inputElem = [];
			var inputTmpElem = [];
			jQuery("#uploadifive_" + thisData["idComponent"] + "_preview .uploaded-thumb").each(function() {
				if(jQuery(this).data("url")) {
					inputElem.push(jQuery(this).data("url"));
					if(jQuery(this).hasClass("tmp"))
						inputTmpElem.push(jQuery(this).data("url"));
				}
			});
			if(inputElem.length) {
				jQuery(thisData["targetComponent"]).val( inputElem.join(",") );
				//jQuery(thisData["tmpnameComponent"]).val( jQuery(thisData["targetComponent"]).val());
				if(fileObj)
					jQuery(thisData["tmpnameComponent"]).val( inputTmpElem.join(",") );

				jQuery(thisData["deleteComponent"]).val("");
			} else {
				jQuery(thisData["targetComponent"]).val("");
				//jQuery(thisData["tmpnameComponent"]).val( jQuery(thisData["targetComponent"]).val());
				jQuery(thisData["tmpnameComponent"]).val("");
				//jQuery(thisData["deleteComponent"]).val("");
			}
		}
	};
	return that;
	
})();