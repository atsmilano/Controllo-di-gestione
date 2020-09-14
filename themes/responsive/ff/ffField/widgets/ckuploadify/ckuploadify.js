ff.ffField.ckuploadify = (function () {
	var basePathUF = ff.site_path + "/themes/library/plugins/jquery.uploadify";
	var baseUrlUF = "/";
	var previewJs = true;
	var viewUrl = "";
	var previewUrl = "";
	var previewPath = ff.site_path + "/cm/showfiles.php";
	var relativePath = '';
	var idComponent = undefined;

	var basePathCK = ff.base_path + "/themes/library/ckfinder";
	var baseUrlCK = "";
	
    var start = "";
    var target = "";
    var tmpname = "";
    this.dataSrc = "";

	var that = { /* publics*/
		__ff : true, /* used to recognize ff'objects*/
		"init" : function (params) {
			this.component			= params.component;
            this.start              = params.start;
            this.target             = params.target;
            this.tmpname            = params.tmpname;
            this.sufdel            	= params.sufdel;
			this.basePathUF			= params.basePathUF;
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
			this.relativePath		= params.relativePath;

			this.basePathCK			= basePathCK;
			this.baseUrlCK			= params.baseUrlCK;
			
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
			var relativePath = this.relativePath;
            var icons = this.icons;
			
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
	                    previewUrl = previewPath + relativePath;
					} else {
						previewUrl = previewPath + "/" + modelThumb + relativePath;
					}
				}

				if(modelThumb != '' && jQuery("#uploadify_" + idComponent + " IMG.image").attr("src") !== undefined) {
					jQuery("#uploadify_" + idComponent + " IMG.image").attr("src", jQuery("#uploadify_" + idComponent + " IMG.image").attr("src").replace(previewPath + "/thumb" + relativePath, previewUrl));
				}

				jQuery("#uploadify_" + idComponent + " IMG.image").parent().attr("rel", previewUrl);

				if(model == "vertical") {
					/*jQuery("#" + idComponent).insertAfter(jQuery("#uploadify_" + idComponent).append());*/
					jQuery("#CkFinder_" + component).insertAfter(jQuery("#uploadify_" + idComponent).append());
				} else if(model == "horizzontal") {
					/*jQuery("#" + idComponent).insertAfter(jQuery("#uploadify_" + idComponent).append());*/
					jQuery("#CkFinder_" + component).insertAfter(jQuery("#uploadify_" + idComponent).append());
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
		        onSelect		 : function() {
		        	ff.ffField.ckuploadify.del(component);
		        	jQuery("DIV.actions INPUT[type=button]").attr("disabled", "disabled").css({ "opacity" : "0.6" });
				},
				onCancel : function() {
		        	jQuery("DIV.actions INPUT[type=button]").removeAttr("disabled").css({ "opacity" : "1" });
				},
		        onComplete		 : function(event, queueID, fileObj, response, data) {
		        	jQuery("DIV.actions INPUT[type=button]").removeAttr("disabled").css({ "opacity" : "1" });
		        	
		        	var previewBlock = '';
		        	var descBlock = '';

					if(response) {
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
                            /*var strResponse = '<input type="text" id="' + component + target + '" name="' + component + target + '" value="' + fileValueOut + '" />' + response;*/
                            var strResponse = response;
                        }
                            
		                if(previewUrl == '') {
			                if(modelThumb == '') {
			                    previewUrl = previewPath + relativePath;
							} else {
								previewUrl = previewPath + "/" + modelThumb + relativePath;
							}
						}
						if(viewUrl == '') {
							viewUrl = previewPath + relativePath;
						}

						if(previewJs)
							previewBlock = '<a href="' + viewUrl + '/' + fileValue + '" target="_blank" rel="' + previewUrl + '"><img id="' + component + target + '_img" class="image" src="' + previewUrl + fileValue + '" /></a>';

						if(showFile)
							descBlock = '<span class="fileName">' + strResponse + ' (' + byteSize + suffix + ')' + '</span>';

                        if(model == "vertical") {
                        	jQuery("#uploadify_" + idComponent).html(previewBlock + '<span class="top"><div class="cancel"><a href="javascript:ff.ffField.ckuploadify.del(\'' + component + '\');" alt="delete" class="' + icons.cancel + '"></a></div></span>' + descBlock);
						} else if(model == "horizzontal") {
							jQuery("#uploadify_" + idComponent).html(previewBlock + '<span class="top"><div class="cancel"><a href="javascript:ff.ffField.ckuploadify.del(\'' + component + '\');" alt="delete" class="' + icons.cancel + '"></a></div></span>' + descBlock);
						} else {
							jQuery("#uploadify_" + idComponent).html(previewBlock + '<span class="top"><div class="cancel"><a href="javascript:ff.ffField.ckuploadify.del(\'' + component + '\');" alt="delete" class="' + icons.cancel + '"></a></div></span>' + descBlock);
						}

                        jQuery("#uploadify_" + idComponent).show();

                        jQuery(targetComponent).val( response );
                        jQuery(tmpnameComponent).val( response );
                        jQuery(deleteComponent).val("");

		        	/*alert(	event + "  " + queueID + "  " + fileObj + "  " + response + "  " + data);*/
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
				if(this.baseUrl == '/')
					fileDelete = this.baseUrl + jQuery(tmpnameComponent).val();
				else
					fileDelete = this.baseUrl + '/' + jQuery(tmpnameComponent).val();
					
				jQuery.post(this.basePathUF + '/uploadify.php', 'delaction=' + fileDelete, function(data) {
				}, "json");
				
				jQuery(tmpnameComponent).val("");
				jQuery(targetComponent).val("");
				jQuery("#uploadify_" + idComponent).hide();
			} 

            return ;
		},
		"browseServer" : function ( startupPath, functionData ) {
			finder = new CKFinder();
			/* You can use the "CKFinder" class to render CKFinder in a page:*/
			
			
			/* The path for the installation of CKFinder (default = "/ckfinder/").*/
			finder.BasePath = this.basePathCK + "/";
			
			/*Startup path in a form: "Type:/path/to/directory/"*/
			finder.StartupPath = startupPath;
			finder.StartupFolderExpanded = true;
			
			/* Name of a function which is called when a file is selected in CKFinder.*/
			finder.SelectFunction = ckFinderSetFileField ;
			
			/* Additional data to be passed to the SelectFunction in a second argument.
			 We'll use this feature to pass the Id of a field that will be updated.*/
			finder.SelectFunctionData = functionData ;
			
			
			/* Name of a function which is called when a thumbnail is selected in CKFinder.*/
			finder.SelectThumbnailFunction = ckFinderShowThumbnails ;

			/* Launch CKFinder*/
			finder.Popup() ;
		}, 
		"setFileField" : function ( fileUrl, data ) {
			document.getElementById(data["selectFunctionData"] ).value = fileUrl.replace(this.baseUrlCK, "") ;
			if(this.previewJs) {
				var sFileName = decodeURIComponent( fileUrl.replace( /^.*[\/\\]/g, '' ) ) ;

				if(document.getElementById(data["selectFunctionData"] + "_img") == null) {
			        var viewUrl = this.viewUrl;
			        var previewUrl = this.previewUrl;
					var relativePath = this.relativePath;
					var model = this.model;
					var modelThumb = this.modelThumb;
					
		            if(model == 'default' && modelThumb == '')
		                modelThumb = "thumb";
					
		            if(previewUrl == '') {
			            if(modelThumb == '') {
			                previewUrl = previewPath + relativePath;
						} else {
							previewUrl = previewPath + "/" + modelThumb + relativePath;
						}
					}
					if(viewUrl == '') {
						viewUrl = previewPath + relativePath;
					}
						
					var previewBlock = '<a href="#" rel="' + previewUrl + '" target="_blank"><img id="' + data["selectFunctionData"] + '_img' + '" /></a>';

					jQuery("#uploadify_" + this.idComponent).prepend(previewBlock);
				} 

				var parentA = document.getElementById(data["selectFunctionData"] + "_img").parentNode;
				if(parentA.rel) {
					document.getElementById(data["selectFunctionData"] + "_img").parentNode.href = parentA.rel.substring(0, parentA.rel.lastIndexOf("/")) + fileUrl.replace(this.baseUrlCK, "");
					document.getElementById(data["selectFunctionData"] + "_img").src = parentA.rel + fileUrl.replace(this.baseUrlCK, "");
					document.getElementById(data["selectFunctionData"] + "_img").title = sFileName + '(' + data["fileSize"] + 'KB)';
				} else {
					document.getElementById(data["selectFunctionData"] + "_img").parentNode.href = "#";
					document.getElementById(data["selectFunctionData"] + "_img").src = "#";
					document.getElementById(data["selectFunctionData"] + "_img").title = "";
				}
			}
		}, 
		/* This is a sample function which is called when a thumbnail is selected in CKFinder.*/
		"showThumbnails": function ( fileUrl, data ) {
			var sFileName = decodeURIComponent( fileUrl.replace( /^.*[\/\\]/g, '' ) ) ;
			document.getElementById('preview').innerHTML = 
					'<div class="thumb">' +
						'<img src="' + fileUrl + '" />' +
						'<div class="caption">' +
							'<a href="' + data["fileUrl"] + '" target="_blank">' + sFileName + '</a> (' + data["fileSize"] + 'KB)' +
						'</div>' +
					'</div>' ;

			document.getElementById( 'preview' ).style.display = "";
			/* It is not required to return any value.
			 When false is returned, CKFinder will not close automatically.*/
			return false;
		}
	};
	return that;
	
})();

function ckFinderSetFileField( fileUrl, data ) {
	return ff.ffField.ckuploadify.setFileField(fileUrl, data);	
}

		
function ckFinderShowThumbnails( fileUrl, data ) {
	return ff.ffField.ckuploadify.showThumbnails(fileUrl, data);	
}
