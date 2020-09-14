ff.ffField.kcuploadify = (function () {
	var basePathUF = "/themes/library/plugins/jquery.uploadify";
	var baseUrlUF = "/";
	var previewJs = true;
	var viewUrl = "";
	var previewUrl = "";
	var previewPath = "/cm/showfiles.php";
	/*var relativePath = '';*/
	var idComponent = undefined;

	var basePathKC = "/themes/library/kcfinder";
	var baseUrlKC = "";
	
    var start = "";
    var target = "";
    var tmpname = "";
    var sufdel = "";
	var dataSrc = "";
	
	var that = { /* publics*/
		__ff : true, /* used to recognize ff'objects*/
		"init" : function (params) {
			this.component			= params.component;
            this.start              = params.start;
            this.target             = params.target;
            this.tmpname            = params.tmpname;
            this.sufdel            	= params.sufdel;
			this.basePathUF			= ff.site_path + params.basePathUF;
			/*this.baseUrlUF			= params.baseUrlUF;*/
			this.previewJs			= params.previewJs;
			this.viewUrl			= params.viewUrl;
			this.previewUrl			= params.previewUrl;
            this.writable           = params.writable;
			this.sizeLimit			= params.sizeLimit;
			this.fileExt			= params.fileExt;
			this.model				= params.model;
			this.modelThumb			= params.modelThumb;
			this.showFile			= params.showFile;
            this.fullPath           = params.fullPath;
            this.dataSrc			= params.dataSrc;
            this.icons              = params.icons;

			this.previewPath		= ff.site_path + "/cm/showfiles.php";
			/*this.relativePath		= params.relativePath;*/

			this.basePathKC			= ff.site_path + basePathKC;
			this.baseUrlKC			= params.baseUrlKC;
			
            var startComponent = document.getElementById(this.component + this.start);
            var targetComponent = document.getElementById(this.component + this.target);
            var tmpnameComponent = document.getElementById(this.component + this.tmpname);
            var deleteComponent = document.getElementById(this.component + this.sufdel);

            var idComponent = this.component.replace(/[^a-zA-Z0-9]+/g,'');
            
            this.idComponent = idComponent;
            
            jQuery(startComponent).attr("id", idComponent);
            
            /*jQuery(startComponent).parent().find(".preview").prepend('<div class="uploadifyQueue" id="' + idComponent + 'Queue"></div>');*/
            var component = this.component; 
            var previewJs = this.previewJs;
            var viewUrl = this.viewUrl;
            var previewUrl = this.previewUrl;
            var writable = this.writable;
            var previewPath = this.previewPath;
            /*var baseUrlUF = this.baseUrlUF;*/
            var basePathUF = this.basePathUF;
            var target = this.target;
			var model = this.model;
			var modelThumb = this.modelThumb;
			var showFile = this.showFile;
            var fullPath = this.fullPath;
            var dataSrc = this.dataSrc;
            var icons = this.icons;
            
            /*var relativePath = this.relativePath;*/

            if(model == 'default' && modelThumb == '')
                modelThumb = "thumb";
            
            jQuery("#" + idComponent).parent().find(".preview").attr("id", "uploadify_" + idComponent);
            jQuery("#" + idComponent).parent().find(".preview").addClass("uploadifyQueueItem");
            jQuery("#" + idComponent).parent().find(".preview").addClass(model);
            jQuery("#" + idComponent).parent().find(".preview").find(".cancel a img").attr('src', (this.basePathUF + "/cancel.png"));
            
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

				if(model == "vertical") {
					/*jQuery("#" + idComponent).insertAfter(jQuery("#uploadify_" + idComponent).append());*/
					jQuery("#KcFinder_" + component).insertAfter(jQuery("#uploadify_" + idComponent).append());
				} else if(model == "horizzontal") {
					/*jQuery("#" + idComponent).insertAfter(jQuery("#uploadify_" + idComponent).append());*/
					jQuery("#KcFinder_" + component).insertAfter(jQuery("#uploadify_" + idComponent).append());
				}
			}

			var scriptData = {};
			scriptData[ff.modules.security.session.session_name] = ff.modules.security.session_id();
			
            jQuery("#" + idComponent).uploadify({
		        'uploader'       : this.basePathUF + '/uploadify.swf',
		        'script'         : this.basePathUF + '/uploadify.php',
		        'cancelImg'      : this.basePathUF + '/cancel.png',
		        'folder'         : this.dataSrc,
		        'scriptData' 	 : scriptData,
                'buttonText'     : 'Sfoglia', 
                'wmode'          : (model == 'default' ? 'opaque' : 'transparent'),
                'hideButton'     : (model == 'default' ? false : true),
                'width'     	 : (model == 'default' ? "120" : "400"),
                'height'     	 : (model == 'default' ? "30" : "400"),
                /*'buttonImg'      : this.basePath + '/browse.png',*/
		        'auto'           : true,
		        'multi'          : false, 
		        'sizeLimit'      : this.sizeLimit,
		        'fileExt'		 : this.fileExt,
		        'fileDesc'    	 : (this.fileExt == null ? null : 'File: (' + this.fileExt + ')'),
                'queueID'        : false, /* The optional ID of the queue container*/
                
		        onSelect		 : function() {
		        	ff.ffField.kcuploadify.del(component);
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
                        if(baseUrlUF == '/') {
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

						if(previewJs)
							previewBlock = '<a href="' + viewUrl + '/' + fileValue + '" target="_blank" rel="' + previewUrl + '"><img id="' + component + target + '_img" class="image" src="' + previewUrl + fileValue + '" /></a>';

						if(showFile)
							descBlock = '<span class="file_name">' + strResponse + ' (' + byteSize + suffix + ')' + '</span>';

                        if(model == "vertical") {
                        	jQuery("#uploadify_" + idComponent).html(previewBlock + '<span class="top"><div class="cancel"><a href="javascript:ff.ffField.kcuploadify.del(\'' + component + '\');" alt="delete" class="' + icons.cancel + '"></a></div></span>' + descBlock);
						} else if(model == "horizzontal") {
							jQuery("#uploadify_" + idComponent).html(previewBlock + '<span class="top"><div class="cancel"><a href="javascript:ff.ffField.kcuploadify.del(\'' + component + '\');" alt="delete" class="' + icons.cancel + '"></a></div></span>' + descBlock);
						} else {
							jQuery("#uploadify_" + idComponent).html(previewBlock + '<span class="top"><div class="cancel"><a href="javascript:ff.ffField.kcuploadify.del(\'' + component + '\');" alt="delete" class="' + icons.cancel + '"></a></div></span>' + descBlock);
						}

                        jQuery("#uploadify_" + idComponent).show();

                        jQuery(targetComponent).val( response["name"] );
                        jQuery(tmpnameComponent).val( response["name"] );
                        jQuery(deleteComponent).val("");

		        	/*alert(	event + "  " + queueID + "  " + fileObj + "  " + response["name"] + "  " + data);*/
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
		"del" : function(component, remove) {
            var targetComponent = document.getElementById(component + this.target);
            var tmpnameComponent = document.getElementById(component + this.tmpname);

            var idComponent = component.replace(/[^a-zA-Z0-9]+/g,'');

			if(jQuery(tmpnameComponent).val()) {
				/*if(this.baseUrlUF == '/')
					fileDelete = this.baseUrlUF + jQuery(tmpnameComponent).val();
				else
					fileDelete = this.baseUrlUF + '/' + jQuery(tmpnameComponent).val();
				*/
				fileDelete = jQuery(tmpnameComponent).val();
				
				jQuery.post(this.basePathUF + '/uploadify.php', 'delaction=' + fileDelete + "&" + ff.modules.security.session.session_name + "=" + ff.modules.security.session_id() + "&folder=" + this.dataSrc, function(data) {
				}, "json");
				

			} 
			if(jQuery(targetComponent).val() && remove) {
				/*if(this.baseUrlUF == '/')
					fileDelete = this.baseUrlUF + jQuery(tmpnameComponent).val();
				else
					fileDelete = this.baseUrlUF + '/' + jQuery(tmpnameComponent).val();
				*/
				fileDelete = jQuery(targetComponent).val();
				
				jQuery.post(this.basePathUF + '/uploadify.php', 'delaction=' + fileDelete + "&" + ff.modules.security.session.session_name + "=" + ff.modules.security.session_id() + "&folder=" + this.dataSrc, function(data) {
				}, "json");
				

			} 
			
			jQuery(tmpnameComponent).val("");
			jQuery(targetComponent).val("");
			/*alert(jQuery(targetComponent).parent().text());*/
			jQuery("#uploadify_" + idComponent).hide();
            return ;
		},
		"openKCFinder" : function (urlSelect, field) {
			var field = document.getElementById(field);
			var previewJs = this.previewJs;
		    window.KCFinder = {
		        callBack: function(url) {
		            window.KCFinder = null;
		            field.value = url;

		            kcUploadifySetFileField(url, field);
		        }
		    };

		    window.open(this.basePathKC + '/browse.php?type=&dir=' + urlSelect, 'kcfinder_textbox',
		        'status=0, toolbar=0, location=0, menubar=0, directories=0, ' +
		        'resizable=1, scrollbars=0, width=800, height=600'
		    );
		},
		"setFileField" : function ( fileUrl, field ) {
			document.getElementById(field.id ).value = fileUrl.replace(ff.site_path + this.baseUrlKC, "") ;
			if(this.previewJs) {
				var component = field.id.replace(this.target, '');
				var idComponent = field.id.replace(this.target, '').replace(/[^A-Za-z0-9 ]/g, '');
				var sFileName = decodeURIComponent( fileUrl.replace( /^.*[\/\\]/g, '' ) );
				if(document.getElementById(field.id + "_img") == null) {
			        var viewUrl = this.viewUrl;
			        var previewUrl = this.previewUrl;
			        /*var relativePath = this.relativePath;*/
					var model = this.model;
					var modelThumb = this.modelThumb;

		            if(model == 'default' && modelThumb == '')
		                modelThumb = "thumb";
					
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

					var previewBlock = '<a href="javascript:void(0);" rel="' + previewUrl + '" target="_blank"><img id="' + field.id + '_img' + '" /></a>';

					if(model == "vertical") {
                        jQuery("#uploadify_" + idComponent + " .top").html(previewBlock + '<div class="cancel"><a href="javascript:ff.ffField.kcuploadify.del(\'' + component + '\');" alt="delete" class="' + icons.cancel + '"></a></div>');
					} else if(model == "horizzontal") {
						jQuery("#uploadify_" + idComponent + " .top").html(previewBlock + '<div class="cancel"><a href="javascript:ff.ffField.kcuploadify.del(\'' + component + '\');" alt="delete" class="' + icons.cancel + '"></a></div>');
					} else {
						jQuery("#uploadify_" + idComponent + " .top").html(previewBlock + '<div class="cancel"><a href="javascript:ff.ffField.kcuploadify.del(\'' + component + '\');" alt="delete" class="' + icons.cancel + '"></a></div>');
					}
				} 

				var parentA = document.getElementById(field.id + "_img").parentNode;
				if(parentA.rel) {
					document.getElementById(field.id + "_img").parentNode.href = parentA.rel.substring(0, parentA.rel.lastIndexOf("/")) + fileUrl.replace(ff.site_path + this.baseUrlKC, "");
					document.getElementById(field.id + "_img").src = parentA.rel + fileUrl.replace(ff.site_path + this.baseUrlKC, "");
					document.getElementById(field.id + "_img").title = sFileName;
				} else {
					document.getElementById(field.id + "_img").parentNode.href = "#";
					document.getElementById(field.id + "_img").src = "#";
					document.getElementById(field.id + "_img").title = "";
					
					/*jQuery(parentA).parent().find('.cancel').children('a').click();	*/
				}

				jQuery("#uploadify_" + idComponent).show();
			}
		}
	};
	return that;
	
})();

function kcUploadifySetFileField( fileUrl, field ) {
	return ff.ffField.kcuploadify.setFileField(fileUrl, field);	
}
