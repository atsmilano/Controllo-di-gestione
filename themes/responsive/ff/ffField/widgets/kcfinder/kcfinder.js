ff.ffField.kcfinder = (function () {
	var idComponent = undefined;
	var basePath = "/themes/library/kcfinder";
	var baseUrl = "";
	var previewPath = "/cm/showfiles.php";
	var previewJs = true;
	
    var start = "";
    var target = "";
    var tmpname = "";
    var sufdel = "";
	
	var aviary = null;

	var that = { /* publics*/
		__ff : true, /* used to recognize ff'objects*/
		"init" : function (params) {
			this.component			= params.component;
            this.start              = params.start;
            this.target             = params.target;
            this.tmpname            = params.tmpname;
            this.sufdel            	= params.sufdel;

			this.basePath			= ff.base_path + basePath;
			this.baseUrl			= params.baseUrl;
			this.previewJs			= params.previewJs;
			this.viewUrl			= params.viewUrl;
			this.previewUrl			= params.previewUrl;
			this.previewPath		= ff.site_path + "/cm/showfiles.php";
			this.writable           = params.writable;
			this.modelThumb			= params.modelThumb;
			this.showFile			= params.showFile;
			this.aviary				= params.aviary;
            this.icons                = params.icons;
		},
		"openKCFinder" : function (urlSelect, field) {
			var field = document.getElementById(field);
			var previewJs = this.previewJs;
		    window.KCFinder = {
		        callBack: function(url) {
		            window.KCFinder = null;
		            field.value = url;

		            kcFinderSetFileField(url, field);
		        }
		    };

		    window.open(this.basePath + '/browse.php?type=&dir=' + urlSelect, 'kcfinder_textbox',
		        'status=0, toolbar=0, location=0, menubar=0, directories=0, ' +
		        'resizable=1, scrollbars=0, width=800, height=600'
		    );
		},
		/*"setFileField" : function ( fileUrl, field ) {
			var aviary = this.aviary;
			var previewJs = this.previewJs;
			
			document.getElementById(field.id ).value = fileUrl.replace(ff.site_path + this.baseUrl, "");
			
			if(previewJs) {
				var sFileName = decodeURIComponent( fileUrl.replace( /^.*[\/\\]/g, '' ) ) ;

				if(document.getElementById(field.id + "_img") == null) {

				} else {
					parentA = document.getElementById(field.id + "_img").parentNode;
					if(parentA.rel) {
						document.getElementById(field.id + "_img").parentNode.href = parentA.rel.substring(0, parentA.rel.lastIndexOf("/")) + fileUrl.replace(ff.site_path + this.baseUrl, "");
						document.getElementById(field.id + "_img").src = parentA.rel + fileUrl.replace(ff.site_path + this.baseUrl, "");
						document.getElementById(field.id + "_img").title = sFileName;
					} else {
						document.getElementById(field.id + "_img").parentNode.href = "#";
						document.getElementById(field.id + "_img").src = "#";
						document.getElementById(field.id + "_img").title = "";
					}
					
				}
			}
		}*/
		"setFileField" : function ( fileUrl, field ) {
			var showFile = this.showFile;
			var aviary = this.aviary;
            var icons = this.icons;
			var previewJs = this.previewJs;

			var previewBlock = '';
		    var descBlock = '';
		    var editBlock = '';
		    var cancelBlock = '';			

			document.getElementById(field.id ).value = fileUrl.replace(ff.site_path + this.baseUrl, "") ;

			var component = field.id.replace(this.target, '');
			var idComponent = field.id.replace(this.target, '').replace(/[^A-Za-z0-9 ]/g, '');
			
	        var startComponent = document.getElementById(component + this.start);
	        var targetComponent = document.getElementById(component + this.target);
	        var tmpnameComponent = document.getElementById(component + this.tmpname);
	        var deleteComponent = document.getElementById(component + this.sufdel);

			var sFileName = decodeURIComponent( fileUrl.replace( /^.*[\/\\]/g, '' ) );
			var viewUrl = this.viewUrl;
			var previewUrl = this.previewUrl;
			var writable = this.writable;
		   /* var relativePath = this.relativePath;*/
			var modelThumb = this.modelThumb;

	        var byteSize = '';
	        var suffix = '';
		

			
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

			var viewFullUrl = previewUrl.substring(0, previewUrl.lastIndexOf("/")) + fileUrl.replace(ff.site_path + this.baseUrl, "");
			var previewFullUrl = previewUrl + fileUrl.replace(ff.site_path + this.baseUrl, "");
			var viewName = viewFullUrl.substring(viewFullUrl.lastIndexOf("/") + 1);


	        if(writable) {
	            var strResponse = '<input type="text" id="' + field.id + '" name="' + field.id + '" value="' + fileUrl.replace(ff.site_path + this.baseUrl, "") + '" />';
	        } else {
	            /*var strResponse = '<input type="hidden" id="' + component + target + '" name="' + component + target + '" value="' + fileValueOut + '" />' + response["name"];*/
	            var strResponse = viewName;
	        }	

			if(previewJs) {
				/*if(document.getElementById(field.id + "_img") == null) {*/
				previewBlock = '<a href="javascript:void(0);" rel="' + previewUrl + '" target="_blank"><img id="' + field.id + '_img' + '" class="image" /></a>';

				if(showFile)
					descBlock = '<span class="file_name">' + strResponse + ' ' + byteSize + suffix + '' + '</span>';  

				if(aviary)
					editBlock = '<div class="edit"><a href="javascript:void(0);" alt="modify" class="' + icons["aviary"] + '" onclick="ff.load(\'ff.ffField.aviary\', function() { ff.ffField.aviary.launch(\'' + field.id + '_img' + '\', \'' + viewFullUrl + '\', \'' + aviary.img_hash + '\', \'' + aviary.key + '\', \'' + aviary.tools + '\', \'' + aviary.theme + '\', \'' + aviary.version + '\', \'' + aviary.post_url + '\'); });"></a></div>';

				cancelBlock = '<div class="cancel"><a href="javascript:ff.ffField.uploadifive.del(\'' + component + '\');" alt="delete" class="' + icons["cancel"] + '"></a></div>';

				jQuery("#uploadifive_" + idComponent).html('<span class="top">' + previewBlock + editBlock + cancelBlock + '</span>' + descBlock);
				/*} */


				
				var parentA = document.getElementById(field.id + "_img").parentNode;
				if(parentA.rel) {
					document.getElementById(field.id + "_img").parentNode.href = viewFullUrl;
					document.getElementById(field.id + "_img").src = previewFullUrl;
					document.getElementById(field.id + "_img").title = sFileName;
				} else {
					document.getElementById(field.id + "_img").parentNode.href = "#";
					document.getElementById(field.id + "_img").src = "#";
					document.getElementById(field.id + "_img").title = "";
					
					/*jQuery(parentA).parent().find('.cancel').children('a').click();	*/
				}

				jQuery(tmpnameComponent).val("");

				jQuery("#uploadifive_" + idComponent).show();
			}
		}
	};
	return that;
	
})();


		
function kcFinderSetFileField( fileUrl, field ) {
	return ff.ffField.kcfinder.setFileField(fileUrl, field);	
}