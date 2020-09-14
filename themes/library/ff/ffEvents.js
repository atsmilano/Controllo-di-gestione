/**
 * Forms Framework Javascript Handling Object
 *	ffEvents' namespace
 */

ff.ffEvents = function () {
//privates

var that = {
// publics
events : ff.hash(),

"addEvent" : function (params) {
	var event_name		= params.event_name			|| console.log("event_name required");
	var func_name		= params.func_name			|| console.log("func_name required");
	var priority		= params.priority			=== undefined ? ff.ffEvent.PRIORITY_DEFAULT : params.priority;
	var break_when		= params.break_when;
	var break_value		= params.break_value;
	var additional_data	= params.additional_data	|| [];
	
	var event = ff.ffEvent.factory({
		"event_name"		: event_name
		, "func_name"		: func_name
		, "priority"		: priority
		, "break_when"		: break_when
		, "break_value"		: break_value
		, "additional_data" : additional_data
		, "id"				: params.id
	});
	
	event.context = this;

	if (!that.events.isset(event_name))
		that.events.set(event_name, ff.hash());

	switch (priority) {
		case ff.ffEvent.PRIORITY_TOPLEVEL:
			if (that.events.get(event_name).get("toplevel") !== undefined)
				console.log("A toplevel event already exists");
			else {
				that.events.get(event_name).set("toplevel", event);
			}
			break;

		case ff.ffEvent.PRIORITY_FINAL:
			if (that.events.isset(event_name).get("final"))
				console.log("A final event already exists");
			else {
				that.events.get(event_name).set("final", event);
			}
			break;

		default:
			if (!that.events.get(event_name).isset(priority))
				that.events.get(event_name).set(priority, ff.hash());
			
			that.events.get(event_name).get(priority).set(event.id, event);
			break;
	}
	
	return event;
},

"doEvent" : function (params) {
	var event_name		= params.event_name		|| console.log("event_name required");
	var event_params	= params.event_params	|| [];
	var results			= [];

	var tmp_queue = that.events.get(event_name);
	var tmp_events;
	var event;
	var tmp_args;
	var rc = undefined;

	if (tmp_queue !== undefined) {
		if (tmp_queue.isset("toplevel")) {
			event = tmp_queue.get("toplevel");
			tmp_args = event_params.slice().concat(event.additional_data.slice());
			tmp_args.push(undefined);
			rc = event.func_name.apply(event, tmp_args);
			if (rc !== undefined) results.push(rc);

			if (event.checkBreak(rc))
				return results;
		}

		for (var q = ff.ffEvent.PRIORITY_TOPLEVEL + 1; q < ff.ffEvent.PRIORITY_FINAL; q++) {
			if (undefined !== (tmp_events = tmp_queue.get(q))) {
				var b = false;
				tmp_events.each(function (key, event, i) {
					tmp_args = event_params.slice().concat(event.additional_data.slice());
					tmp_args.push(results[results.length - 1]);
					rc = event.func_name.apply(event, tmp_args);
					if (rc !== undefined) results.push(rc);

					if (event.checkBreak(rc))
						return (b = true);
				});
				if (b) return results;
			}
		}

		if (tmp_queue.isset("final")) {
			event = tmp_queue.get("final");
			tmp_args = event_params.slice().concat(event.additional_data.slice());
			tmp_args.push(results[results.length - 1]);
			rc = event.func_name.apply(event, tmp_args);
			if (rc !== undefined) results.push(rc);

			if (event.checkBreak(rc))
				return results;
		}
	}

	return results;
}
 /* nn viene mai richiamata nel codice. Da errore con l'add e il delete del dettaglio          */
, "clearQueue" : function (event_name, priority) {
	if (priority !== undefined) {
		switch (priority) {
			case ff.ffEvent.PRIORITY_TOPLEVEL:
					that.events.get(event_name).unset("toplevel");
				break;

			case ff.ffEvent.PRIORITY_FINAL:
				that.events.get(event_name).unset("final");
				break;

			default:
				that.events.get(event_name).get(priority).clear();
				break;
		}
	} else {
		that.events.get(event_name).clear();
	}
}

, "getLastRes" : function (res) {
	if (res !== undefined && res[res.length - 1]) {
		return res[res.length - 1];
	} else {
		return undefined;
	}
}

}; // publics' end

return that;

// code's end.
};
