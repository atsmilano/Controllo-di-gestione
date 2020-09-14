ff.ffField.uploadify = (function () {
	var basePath = "/themes/library/plugins/jquery.uploadify";
	var baseUrl = "/";
	var previewJs = true;
	var viewUrl = "";
	var previewUrl = "";
	var previewPath = "/cm/showfiles.php";
	/*var relativePath = '';*/

	var start = "";
	var target = "";
	var tmpname = "";
	var dataSrc = "";

	var that = { /* publics*/
		__ff : true, /* used to recognize ff'objects*/
		"init" : function (params) {
			basePath 				= ff.site_path + basePath;
			//previewPath				= ff.site_path + previewPath;
			
			this.component			= params.component;
			this.start				= params.start;
			this.target				= params.target;
			this.tmpname			= params.tmpname;
			this.sufdel				= params.sufdel;
			this.basePath			= params.basePath;
			/*this.baseUrl			= params.baseUrl;*/
			this.previewJs			= params.previewJs;
			this.viewUrl			= params.viewUrl;
			this.previewUrl			= params.previewUrl;
            this.writable           = params.writable;
			this.sizeLimit			= params.sizeLimit;
			this.fileExt			= params.fileExt;
			this.fileDesc			= params.fileDesc;
			this.model				= params.model;
			this.modelThumb			= params.modelThumb;
			this.showFile			= params.showFile;
			this.showLink			= params.showLink;
            this.fullPath           = params.fullPath;
            this.dataSrc           	= params.dataSrc;
            this.preview_qs			= params.preview_qs;
            this.icons              = params.icons;

			this.previewPath		= ff.site_path + "/cm/showfiles.php";
			/*this.relativePath		= params.relativePath;*/
			
            var startComponent      = document.getElementById(this.component + this.start);
            var targetComponent     = document.getElementById(this.component + this.target);
            var tmpnameComponent    = document.getElementById(this.component + this.tmpname);
            var deleteComponent     = document.getElementById(this.component + this.sufdel);

			var idComponent         = this.component.replace(/[^a-zA-Z0-9]+/g,'');
            jQuery(startComponent).attr("id", idComponent);
            
			var component = this.component;
			var previewJs = this.previewJs;
            var viewUrl = this.viewUrl;
            var previewUrl = this.previewUrl;
            var writable = this.writable;
            var previewPath = this.previewPath;
            var preview_qs = this.preview_qs;
			/*var baseUrl = this.baseUrl;*/
			var basePath = this.basePath;
			var target = this.target;
			var model = this.model;
			var modelThumb = this.modelThumb;
			var showFile = this.showFile;
			var showLink = this.showLink;
            var fullPath = this.fullPath;
            var dataSrc = this.dataSrc;
            var icons = this.icons;
            /*var relativePath = this.relativePath;*/
			
            if(model == 'default' && modelThumb == '')
                modelThumb = "thumb";

            jQuery("#" + idComponent).parent().find(".preview").attr("id", "uploadify_" + idComponent);
            jQuery("#" + idComponent).parent().find(".preview").addClass("uploadifyQueueItem");
            jQuery("#" + idComponent).parent().find(".preview").addClass(model);
            jQuery("#" + idComponent).parent().find(".preview").find(".cancel a img").attr('src', (this.basePath + "/cancel.png"));

            if(jQuery("#uploadify_" + idComponent).attr("id") === undefined) {
                if(model == "vertical") {
                    jQuery("#" + idComponent).parent().prepend('<div class="uploadifyQueueItem ' + model + '" id="uploadify_' + idComponent + '"></div>');
                } else if(model == "horizzontal") {
                    jQuery("#" + idComponent).parent().prepend('<div class="uploadifyQueueItem ' + model + '" id="uploadify_' + idComponent + '"></div>');				
                } else {
                    jQuery("#" + idComponent).parent().append('<div class="uploadifyQueueItem ' + model + '" id="uploadify_' + idComponent + '"></div>');
                }
                jQuery("#uploadify_" + idComponent).hide(); 
            } else {
                if(previewUrl == '') {
	                if(modelThumb == '') {
	                    previewUrl = previewPath /*+ relativePath*/;
					} else {
						previewUrl = previewPath + "/" + modelThumb /*+ relativePath*/;
					}
                }

				if(modelThumb != '' && jQuery("#uploadify_" + idComponent + " IMG.image").attr("src") !== undefined) {
					jQuery("#uploadify_" + idComponent + " IMG.image").attr("src", jQuery("#uploadify_" + idComponent + " IMG.image").attr("src").replace(previewPath + "/thumb" /*+ relativePath*/, previewUrl));
				}

				jQuery("#uploadify_" + idComponent + " IMG.image").parent().attr("rel", previewUrl); 

				/*if(model == "vertical") {
					jQuery("#" + idComponent).insertAfter(jQuery("#uploadify_" + idComponent).append());
				} else if(model == "horizzontal") {
					jQuery("#" + idComponent).insertAfter(jQuery("#uploadify_" + idComponent).append());
				}*/
            }

			var scriptData = {};
			scriptData[ff.modules.security.session.session_name] = ff.modules.security.session_id();

		    jQuery("#" + idComponent).uploadify({
                'uploader'       : this.basePath + '/uploadify.swf',
                'script'         : this.basePath + '/uploadify.php',
                'cancelImg'      : '',
                'folder'         : this.dataSrc,
                'scriptData' 	 : scriptData,
                'buttonText'     : 'Sfoglia', 
                'wmode'          : (model == 'default' ? 'opaque' : 'transparent'),
                'hideButton'     : (model == 'default' ? false : true),
                'width'          : (model == 'default' ? "120" : "400"),
                'height'         : (model == 'default' ? "30" : "400"),
                /*'buttonImg'      : this.basePath + '/browse.png',*/
                'auto'           : true,
                'multi'          : false, 
                'sizeLimit'      : this.sizeLimit,
                'fileExt'        : this.fileExt,
                'fileDesc'       : this.fileDesc,
                'queueID'        : false, /* The optional ID of the queue container */
		        onSelect		 : function() {
		        	ff.ffField.uploadify.del(component);
		        	jQuery("DIV.actions INPUT[type=button]").attr("disabled", "disabled").css({ "opacity" : "0.6" });
				},
				onCancel : function() {
		        	jQuery("DIV.actions INPUT[type=button]").removeAttr("disabled").css({ "opacity" : "1" });
				},
		        onComplete		 : function(event, queueID, fileObj, response, data) {
		        	jQuery("DIV.actions INPUT[type=button]").removeAttr("disabled").css({ "opacity" : "1" });

                    var previewBlock = '';
                    var descBlock = '';

                    var response = jQuery.parseJSON(response);
		        	
                    if(response["status"]) {
						/*
                        if(baseUrl == '/') {
                            fileValue = '/' + response;
                        } else {
                            fileValue = fileObj.filePath.replace(fileObj.name, '') + response;
                        }

                        if(fullPath) {
                            fileValueOut = fileValue;
                        } else {
                            fileValueOut = response;
                        }

                        if(fileValue.indexOf("/uploads") == 0) {
                            fileValue = fileValue.substr(8);
                        }
                        if(fileValue.indexOf("/themes") == 0) {
                            fileValue = fileValue.substr(7);
                        }
						*/
						
                        if(fullPath) {
                            fileValue = response["fullpath"];
                        } else {
                            fileValue = response["name"];
                        }

						if (preview_qs.length) {
							fileValue += "?" + preview_qs;
						}

                        fileValueOut = response["name"];
						
                        var byteSize = Math.round(fileObj.size / 1024 * 100) * .01;
                        var suffix = 'KB';
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

                        if(writable) {
                            var strResponse = '<input type="text" id="' + component + target + '" name="' + component + target + '" value="' + fileValueOut + '" />';
                        } else {
                            /*var strResponse = '<input type="hidden" id="' + component + target + '" name="' + component + target + '" value="' + fileValueOut + '" />' + response["name"];*/
                            var strResponse = response["name"];
                        }

                        if(previewUrl == '') {
                            if(modelThumb == '') {
                                previewUrl = previewPath /*+ relativePath*/;
                            } else {
                                previewUrl = previewPath + "/" + modelThumb /*+ relativePath*/;
                            }
                        }

                        if(viewUrl == '') {
                        	viewUrl = previewPath /*+ relativePath*/;
                        }

                        if(viewUrl.length && !fullPath) {
							viewUrl += "/";
						}
						
                        if(previewJs)
                            previewBlock = '<a href="' + viewUrl + '/' + fileValue + '" target="_blank" rel="' + previewUrl + '"><img id="' + component + target + '_img" class="image" src="' + previewUrl + "/" + fileValue + '" /></a>';

                        if(showFile)
                            descBlock = '<span class="fileName">' + strResponse + ' (' + byteSize + suffix + ')' + '</span>';

                        if(showLink)
                            descBlock = '<a href="' + viewUrl + fileValue + '" rel="' + previewUrl + '" target="_blank">' + strResponse + ' (' + byteSize + suffix + ')' + '</a>';
                            
                        if(model == "vertical") {
                            jQuery("#uploadify_" + idComponent).html('<span class="top">' + previewBlock + '<div class="cancel"><a href="javascript:ff.ffField.uploadify.del(\'' + component + '\');" alt="delete" class="' + icons.cancel + '"></a></div></span>' + descBlock);
                        } else if(model == "horizzontal") {
                            jQuery("#uploadify_" + idComponent).html('<span class="top">' + previewBlock + '<div class="cancel"><a href="javascript:ff.ffField.uploadify.del(\'' + component + '\');" alt="delete" class="' + icons.cancel + '"></a></div></span>' + descBlock);
                        } else {
                            jQuery("#uploadify_" + idComponent).html('<span class="top">' + previewBlock + '<div class="cancel"><a href="javascript:ff.ffField.uploadify.del(\'' + component + '\');" alt="delete" class="' + icons.cancel + '"></a></div></span>' + descBlock);
                        }

                        jQuery("#uploadify_" + idComponent).show(); 

                        jQuery(targetComponent).val( response["name"] );
                        jQuery(tmpnameComponent).val( response["name"] );
                        jQuery(deleteComponent).val("");

		        	
                    } else {
                        return false;
                    }
				}, 
                onError: function (a, b, c, d) {
                	jQuery("DIV.actions INPUT[type=button]").removeAttr("disabled").css({ "opacity" : "1" });
                	
                    if (d.status == 404)
                        alert('Could not find upload script. Use a path relative to: '+'<?= getcwd() ?>');
                    else if (d.type === "HTTP")
                        alert('error '+d.type+": "+d.status);
                    else if (d.type ==="File Size")
                        alert(c.name+' '+d.type+' Limit: '+Math.round(d.sizeLimit/1024)+'KB');
                    else
                        alert('error '+d.type+": "+d.text);
                }
		    });			
		},
		"del" : function(component) { 
			var targetComponent = document.getElementById(component + this.target);
			var tmpnameComponent = document.getElementById(component + this.tmpname);

			var idComponent = component.replace(/[^a-zA-Z0-9]+/g,'');

			if(jQuery(tmpnameComponent).val()) {
				/*
				if(this.baseUrl == '/')
					fileDelete = this.baseUrl + jQuery(tmpnameComponent).val();
				else
					fileDelete = this.baseUrl + '/' + jQuery(tmpnameComponent).val();
				*/
				fileDelete = jQuery(tmpnameComponent).val();
					
				jQuery.post(this.basePath + '/uploadify.php', 'delaction=' + fileDelete + "&" + ff.modules.security.session.session_name + "=" + ff.modules.security.session_id() + "&folder=" + this.dataSrc, function(data) {
				}, "json");
				
				jQuery(tmpnameComponent).val("");
				jQuery(targetComponent).val("");
                
				jQuery("#uploadify_" + idComponent).hide();
			} 
			
			return ;
		}
	};
	return that;
	
})();
