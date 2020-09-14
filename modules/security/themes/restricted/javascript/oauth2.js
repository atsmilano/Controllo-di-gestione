ff.modules.security.oauth2 = (function () {
	
// privates

function randomString(length, chars) {
    var result = '';
    for (var i = length; i > 0; --i) result += chars[Math.floor(Math.random() * chars.length)];
    return result;
}


var that = { // publics
__ff : true, // used to recognize ff'objects

"revokeApp" : function (client_id) {
	var r = confirm("Are you sure to revoke app permission?");
	if (r) {
		ff.ajax.doRequest({
			"url"		: ff.urlAddParam(ff.modules.security.services.oauth2 + "/revokeapp", "client_id", client_id),
			"callback"	: function () {
				//alert("ok!");
			}
		});
	}
},

"refreshSecret" : function (client_id) {
	var r = confirm("Are you sure to reset password?");
	if (r) {
		ff.ajax.doRequest({
			"url"		: ff.urlAddParam(ff.modules.security.services.oauth2 + "/randomsecret", "client_id", client_id),
			"callback"	: function (data) {
				jQuery("#oauth-clients-modify_client_secret_label").html(data["secret"]);
			}
		});
	}
},
	
"emptySecret" : function (client_id) {
	var r = confirm("Are you sure to empty password? This will make this API public");
	if (r) {
		ff.ajax.doRequest({
			"url"		: ff.urlAddParam(ff.modules.security.services.oauth2 + "/emptysecret", "client_id", client_id),
			"callback"	: function (data) {
				jQuery("#oauth-clients-modify_client_secret_label").html("");
			}
		});
	}
}
	
}; // publics' end

return that;

// code's end.
	
})();