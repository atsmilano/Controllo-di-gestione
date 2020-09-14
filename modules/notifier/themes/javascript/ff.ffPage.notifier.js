/**
 * Forms Framework Javascript Handling Object
 *	notifier page' plugin namespace
 */

ff.ffPage.notifier = (function () {

/*  privates */

ff.pluginAddInit("ff.ajax", function () {
	ff.ajax.addEvent({
		"event_name"	: "onSuccess"
		, "func_name"	: function (data, params, injectid) {
			if (data.modules && data.modules.notifier) {
				if (data.modules.notifier.queues) {
					// load messages
					for (var queue in data.modules.notifier.queues) if (data.modules.notifier.queues.hasOwnProperty(queue)) {
						ff.ffPage.notifier.loadMessages(queue, data.modules.notifier.queues[queue]);
					}
				}
				if (data.modules.notifier.deferred) data.modules.notifier.deferred.each(function (i, queue) {
					if (!deferred_queues.isset(queue) && queues.isset(queue))
					{
						deferred_queues.set(queue, true);
						var ev = ff.ajax.addEvent({
							"event_name"	: "onEmptyQueue",
							"func_name"		: function () {
								ev.remove();
								deferred_queues.unset(queue);
								jQuery.ajax({
									  "url"		: ff.fixPath(queues.get(queue) + (queue !== "" ? "?queue=" + encodeURIComponent(queue) : ""))
									, "async"	: true
									, "type"	: "GET"
									, "dataType": "json"
									, "success"	: function (data) {
										if (!data || !data.modules || !data.modules.notifier || !data.modules.notifier.messages) 
											return;

										that.loadMessages(queue, data.modules.notifier.messages);
										that.processQueue(queue);
									}
								});
							}
						});
					}
				});
			}
		}
	});
	ff.ajax.addEvent({
		"event_name"	: "onEmptyQueue"
		, "func_name"	: function (data) {
			// parse messages
			queues.each(function (k, v, i) {
				ff.ffPage.notifier.processQueue(k);
				if (deferred_queues.isset(k)) {
					console.log("got deferred", k);
				}
			});
		}
	});
});

function getRealQueue(queue) {
	if (queue === undefined || queue === null)
		queue = "";
	
	return queue.indexOf("mod_notifier_message_queue") === 0 ? queue : "mod_notifier_message_queue" + (queue !== "" ? "_" + queue : "");
}

function getQueueName(queue) {
	if (queue === undefined || queue === null)
		queue = "";
	
	if (queue !== "") {
		queue = queue.replace("mod_notifier_message_queue_", "");
		queue = queue.replace("mod_notifier_message_queue", "");
	}
	
	return queue;
}

function getQueueSelector(queue) {
	var queue_name = getRealQueue(queue);
	
	return ".mod-notifier-messages" + (queue_name !== "" ? "." + queue_name : "");
}

var queues = ff.hash();
var deferred_queues = ff.hash();
var messages = ff.hash();

var that = { /* publics */
__ff : true, /* used to recognize ff'objects */

"initListener" : function (queue, path) {
	var real_queue = getRealQueue(queue);
	
	if (!queues.isset(real_queue)) {
		queues.set(real_queue, path);
	}
}

, "loadMessages" : function (queue, messages) {
	if (messages.length !== undefined) {
		messages.each(function (i, message) {
			that.addMessage(queue, message);
		});
	}
}

, "unsetListener" : function (queue) {
	var real_queue = getRealQueue(queue);
	
	if (queues.isset(real_queue)) {
		//queues.get(real_queue).remove();
		queues.unset(real_queue);
	}
}

, "addMessage" : function (queue, message) {
	var real_queue = getRealQueue(queue);
	
	if (!messages.isset(real_queue))
		messages.set(real_queue, ff.hash());
	
	message.options.url			= message.options.url			!== undefined ? message.options.url			: null;
	message.options.url_target	= message.options.url_target	!== undefined ? message.options.url_target	: "_self";
	message.options.url_hide	= message.options.url_hide		!== undefined ? message.options.url_hide	: false;
	message.options.close_bt	= message.options.close_bt		!== undefined ? message.options.close_bt	: true;
	
	var id = ff.getUniqueID();
	messages.get(real_queue).set(id, message);
	
	return id;
}

, "processQueue" : function (queue) {
	var real_queue = getRealQueue(queue);
	
	if (messages.isset(real_queue)) {
		messages.get(real_queue).each(function (key, value, idx){
			jQuery(getQueueSelector(queue)).each(function () {
				var newdiv = jQuery(value.content);
				jQuery(this).append(newdiv);
				ff.ffPage.notifier.initElement(newdiv.fadeOut(0).fadeIn(), value.options);
			});
		});
		messages.get(real_queue).clear();
	}
}

/* OBSOLETO, USARE processQueue
"initAllElements" : function (queue) {
	jQuery(function () {
		jQuery(".mod-notifier-messages" + (queue !== "" ? "." + queue : "") + " .message").each(that.initElement);
	});
}
*/

, "initElement" : function (element, options) {
	if (!jQuery.data(element.get(0), "ff.ffPage.notifier")) {
		jQuery.data(element.get(0), "ff.ffPage.notifier", true);
		
		if (options.close_bt)
			jQuery(".hide", element)
				.click(function () {
					element
						.fadeOut()
						.queue(function(){
							element.remove();
						});
				})
				.css("cursor", "pointer");
	
		if (options.auto_hide)
			setTimeout(function () {
				element
					.fadeOut()
					.queue(function(){
						element.remove();
					});
			}, options.auto_hide);
		
		if (options.url)
			element.on("click", function(e){
				if (e.target !== element.get(0))
					return;
				
				window.open(options.url, options.url_target);
				if (options.url_hide)
					element
						.fadeOut()
						.queue(function(){
							element.remove();
						});
			}).css("cursor", "pointer");
	}
}

} /* publics' end */

return that;

/* code's end.*/
})();
