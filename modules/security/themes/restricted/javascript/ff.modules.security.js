/**
 * Forms Framework Javascript Handling Object
 *	module security namespace
 */

ff.modules.security = (function () {
// inits

ff.pluginAddInit("ff", function () {
	ff.addEvent({
		"event_name" : "fixPath"
		, "func_name" : function (source, cross_xhr, lastret) {
			var ret = lastret || source;

			if (cross_xhr && ff.modules.security.session.session_name) {
				var session_value = ff.modules.security.session_id();

				if ((session_value && session_value.length > 0)) {
					ret = ff.urlAddParam(ret, ff.modules.security.session.session_name, session_value);
				}
			}

			return ret;
		}
	});
});

ff.pluginAddInit("ff.ajax", function () {
	ff.ajax.addEvent({
		"event_name"	: "onRedirect"
		, "func_name"	: function (url, data, customdata) {
			if (data && data.modules && data.modules.security && data.modules.security.prompt_login) {
				if (customdata && customdata.params && customdata.params.customdata && customdata.params.customdata.caller) {
					var ev = ff.modules.security.addEvent({
						"event_name" : "login"
						, "func_name" : function () {
							customdata.params.customdata.caller.func.apply(this, customdata.params.customdata.caller.args);
							ev.remove();
							return false;
						}
						, "break_when" : ff.ffEvent.BREAK_EQUAL
						, "break_value" : false // break events and stop from redirect
					});
				} else if (customdata && customdata.caller) {
					var ev = ff.modules.security.addEvent({
						"event_name" : "login"
						, "func_name" : function () {
							customdata.caller.func.apply(this, customdata.caller.args);
							ev.remove();
							return false;
						}
						, "break_when" : ff.ffEvent.BREAK_EQUAL
						, "break_value" : false // break events and stop from redirect
					});
				}
				
				ff.modules.security.openLoginDialog(undefined, url, (customdata ? customdata.parsed_url : undefined));
				
				return {"break" : true};
			}
		}
	});
	
	ff.ajax.addEvent({
		"event_name"	: "onSuccess"
		, "func_name"	: function (data, params, injectid) {
			if (data.modules && data.modules.security) {
				// update session data
				ff.modules.security.session.session_name	= data.modules.security.session_name;
				ff.modules.security.session.loggedin		= data.modules.security.loggedin;
				ff.modules.security.session.session_id		= data.modules.security.session_id;
				ff.modules.security.session.UserNID			= data.modules.security.UserNID;
			}
		}
	});
	
	ff.ajax.addEvent({
		"event_name"	: "onRequestDone"
		, "func_name"	: function (data, params, injectid) {
			if (data && data.modules && data.modules.security && data.modules.security.action) {
				var res = ff.modules.security.doEvent({
					"event_name"	: data.modules.security.action,
					"event_params"	: [that, data, params.customdata]
				});
				//return {"break" : true};
			}
		}
	});
});

// privates
var social_window = undefined;
var login_dialog_id = undefined;

var that = { // publics
__ff : true, // used to recognize ff'objects

services : {
	"login" : null
	, "check_session" : null
	, "oauth2" : null
},
session : {
	"session_name" : null
	, loggedin : null
	, "session_id" : null
	, "UserNID" : null
},

"session_id" : function () {
	return (
				ff.modules.security.session.session_id ? ff.modules.security.session.session_id
				: (jQuery.cookie ? jQuery.cookie(ff.modules.security.session.session_name) : undefined)
			);
},

openLoginDialog : function (title, url, ret_url, id) {
	title = title || "Login";
	login_dialog_id = id || "loginDlg";
	url = url || that.services.login + (ret_url ? "?ret_url=" + encodeURIComponent(ret_url) : "");
	
	ff.load("ff.ffPage.dialog", function () {
		ff.ffPage.dialog.addDialog({
			"id" : login_dialog_id,
			"callback" : "",
			"url" : "",
			"resizable" : true,
			"position" : "center",
			"draggable" : true,
			"title" : title,
			"height" : 500,
			"width" : 800
		});

		ff.ffPage.dialog.doOpen(login_dialog_id, url, title);
	});
},
reloadPageBySocialLogin : function(excludePath) {
    if(window.opener.location.href.indexOf(excludePath) >= 0)
        window.opener.location = "/"; 
    else
        window.opener.location.reload();
    
    window.close(); 
},		
social : {
	"requestLogin" : function (title, url) {
		social_window = window.open(
				ff.fixPath(url)
				, title
				, "menubar=no, status=no, height=500, width= 500"
			);
	
		ff.modules.security.social.checkLoginEvent();
	},
	
	"checkLoginEvent" : function () {
		try {
			if (social_window.ff.fn.modsec.login_status) {
				social_window.close();
				social_window = undefined;
				
				if (login_dialog_id) {
					ff.ffPage.dialog.close(login_dialog_id);
					login_dialog_id = undefined;
				}
				
				var doredir = true;
				
				var res = ff.modules.security.doEvent({
					"event_name"	: "login",
					"event_params"	: [that]
				});
				
				if (res !== undefined && res.length) {
					for (var i = 0; i < res.length; i++) {
						if (res[i] === false)
							doredir = false;
					}
				}
				
				if (doredir)
				{
					var ret_url = ff.getURLParameter("ret_url");
					if (ret_url !== null)
						window.location.href = ret_url;
					else if (ff.fixPath(ff.modules.security.services.login).rtrim("/") === window.location.href.parseUri().path.rtrim("/"))
						window.location.href = ff.site_path + "/";
					else
						window.location.reload();
				}
				
				return;
			}
		} catch (e) {
		}

		try {
			if (social_window.window) {
				setTimeout(ff.modules.security.social.checkLoginEvent, 3000);
			}
		} catch (e) {
		}
	}
}
			
}; // publics' end

return that;

// code's end.
})();
