ff.ffField.ckfinder = (function () {
	var basePath = "/ckfinder/";
	var baseUrl = "";
	var previewJs = true;

	var that = { /* publics*/
		__ff : true, /* used to recognize ff'objects*/
		"init" : function (params) {
			this.basePath			= params.basePath;
			this.baseUrl			= params.baseUrl;
			this.previewJs			= params.previewJs;
		},
		"browseServer" : function ( startupPath, functionData ) {
			finder = new CKFinder();
			/* You can use the "CKFinder" class to render CKFinder in a page:*/
			
			
			/* The path for the installation of CKFinder (default = "/ckfinder/").*/
			finder.BasePath = this.basePath ;
			
			/*Startup path in a form: "Type:/path/to/directory/"*/
			finder.StartupPath = startupPath;
			finder.StartupFolderExpanded = true;
			
			/* Name of a function which is called when a file is selected in CKFinder.*/
			finder.SelectFunction = ckFinderSetFileField ;
			
			/* Additional data to be passed to the SelectFunction in a second argument.
			// We'll use this feature to pass the Id of a field that will be updated.*/
			finder.SelectFunctionData = functionData ;
			
			
			/* Name of a function which is called when a thumbnail is selected in CKFinder.*/
			finder.SelectThumbnailFunction = ckFinderShowThumbnails ;

			/* Launch CKFinder*/
			finder.Popup() ;
		}, 
		"setFileField" : function ( fileUrl, data ) {
			document.getElementById(data["selectFunctionData"] ).value = fileUrl.replace(this.baseUrl, "") ;
			if(this.previewJs) {
				var sFileName = decodeURIComponent( fileUrl.replace( /^.*[\/\\]/g, '' ) ) ;

				parentA = document.getElementById(data["selectFunctionData"] + "_img").parentNode;
				if(parentA.rel) {
					document.getElementById(data["selectFunctionData"] + "_img").parentNode.href = parentA.rel.substring(0, parentA.rel.lastIndexOf("/")) + fileUrl.replace(this.baseUrl, "");
					document.getElementById(data["selectFunctionData"] + "_img").src = parentA.rel + fileUrl.replace(this.baseUrl, "");
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
	return ff.ffField.ckfinder.setFileField(fileUrl, data);	
}

		
function ckFinderShowThumbnails( fileUrl, data ) {
	return ff.ffField.ckfinder.showThumbnails(fileUrl, data);	
}
	