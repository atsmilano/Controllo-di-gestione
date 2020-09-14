ff.ffField.aviary = (function () {
	var aviaryUrl = "http://feather.aviary.com/js/feather.js";
	var defaultTools = "all";
	var defaultTheme = "light";
	var defaultVersion = 3;
	var defaultPostUrl = "/themes/responsive/ff/ffField/widgets/aviary/save.php";
	var featherEditor = null;
	var previewUrl = "/cm/showfiles.php"

	var that = { /* publics*/
		__ff : true, /* used to recognize ff'objects*/
		"init" : function (imgElem, imgPath, imgHash, key, tools, theme, version, postUrl) {
			var tools = (tools ? tools : defaultTools);
			var theme = (theme ? theme : defaultTheme);
			var version = (version ? version : defaultVersion);
			var postUrl = (postUrl ? postUrl : defaultPostUrl);
			
			ff.pluginLoad('Aviary', 'http://feather.aviary.com/js/feather.js', function() {
				featherEditor = new Aviary.Feather({
					apiKey: key, /* {aviary_api_key}',*/
	       			apiVersion: version,
	       			theme: theme, /* Check out our new 'light' and 'dark' themes!*/
	       			tools: tools, /*['draw', 'stickers'],*/
	       			appendTo: '',
	       			onLoad : function() {
    					featherEditor.launch({
							image: imgElem,
							url: window.location.protocol + '//' + window.location.hostname + previewUrl + imgPath
						}); 
	       			},
					onReady : function() {
						featherEditor.showWaitIndicator();
					},
					onSave: function(imageID, newURL) {
						var postData = {};
						postData["url"] = newURL;
						postData["img"] = imgPath;
						//postData[ff.modules.security.session.session_name] = ff.modules.security.session_id();
						postData["ref"] = imgHash;

						jQuery.post(postUrl, postData, function() {
							ff.ffField.aviary.reloadImg(imageID);
							ff.ffField.aviary.reloadImg(imageID.replace("-", ""));
						});
					  
						featherEditor.close();
					},
	       			onError: function(errorObj) {
	           			alert(errorObj.message);
	       			}
				});
			});
		},
		"launch" : function(imgElem, imgPath, imgHash, key, tools, theme, version, postUrl) {
			if(featherEditor === null) {
				ff.ffField.aviary.init(imgElem, imgPath, imgHash, key, tools, theme, version, postUrl);
			} else {
				featherEditor.launch({
					image: imgElem,
					url: window.location.protocol + '//' + window.location.hostname + imgPath
				}); 
			}

			return false; 
		},
		"reloadImg" : function (id) {
		   var obj = document.getElementById(id);
		   var src = obj.src;
		   var pos = src.indexOf('?');
		   if (pos >= 0) {
		      src = src.substr(0, pos);
		   }
		   var date = new Date();
		   obj.src = src + '?_=' + date.getTime();
		   return false;
		}		
	};
	return that;
	
})();		
