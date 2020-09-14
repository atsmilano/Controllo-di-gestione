/**
 * Forms Framework Javascript Handling Object
 *	ffEvent' namespace
 */

ff.ffEvent = (function () {
//privates

var that = {
// publics
"BREAK_NEVER" 		: 0,
"BREAK_EQUAL" 		: 1,
"BREAK_NOT_EQUAL" 	: 2,
"BREAK_CALLBACK" 	: 3,
"BREAK_DEFAULT" 	: 0,

"PRIORITY_TOPLEVEL" : 0,
"PRIORITY_HIGH"		: 1,
"PRIORITY_NORMAL" 	: 2,
"PRIORITY_LOW"		: 3,
"PRIORITY_FINAL" 	: 4,
"PRIORITY_DEFAULT" 	: 2,

"factory" : function (params) {
	var instance = {
		"event_name"		: params.event_name || console.log("unnamed event"),
		"func_name"			: params.func_name || console.log("invalid func"),
		"priority"			: params.priority,
		"break_when"		: params.break_when,
		"break_value"		: params.break_value,
		"additional_data"	: params.additional_data || [],
		"context"			: undefined,
		"id"				: (params.id === undefined ? ff.getUniqueID() : params.id),

		"checkBreak" : function (result) {
			switch (this.break_when) {
				case ff.ffEvent.BREAK_CALLBACK:
					return this.break_value.apply(this, result);

				case ff.ffEvent.BREAK_EQUAL:
					if (result === this.break_value)
						return true;
					break;

				case ff.ffEvent.BREAK_NOT_EQUAL:
					if (result !== this.break_value)
						return true;
					break;
			}
			return false;
		},
		
		"remove" : function () {
			switch (this.priority) {
				case ff.ffEvent.PRIORITY_TOPLEVEL:
					return (undefined !== this.context.events.get(this.event_name).unset("toplevel"));
					break;

				case ff.ffEvent.PRIORITY_FINAL:
					return (undefined !== this.context.events.get(this.event_name).unset("final"));
					break;

				default:
					return (undefined !== this.context.events.get(this.event_name).get(this.priority).unset(this.id));
					break;
			}
		}
	};
	return instance;
},

"getLast" : function (results){
	if (results.length > 0)
		return results[results.length];
	else
		return undefined;
}

}; // publics' end

return that;

// code's end.
})();
