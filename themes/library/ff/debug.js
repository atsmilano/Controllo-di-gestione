/**
 * Forms Framework Javascript Handling Object
 *	debug namespace
 */

ff.debug = (function () {
//privates

ff.addEvent({
	"event_name" : "fixPath"
	, "func_name" : function (source, cross_xhr, lastret) {
		var ret = lastret || source;
		
		if (window.location.href.indexOf("XDEBUG_SESSION_START=netbeans-xdebug"))
			ret = ff.urlAddParam(ret, "XDEBUG_SESSION_START", "netbeans-xdebug");
		return ret;
	}
});

var that = {
// publics

}; // publics' end

return that;

// code's end.
})();
