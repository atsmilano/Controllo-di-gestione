ff.tplService = (function () { 
    var srvCache = {};
	var debug = false;
	var keySearchToSkip = ["ret_url"];

    var that = { // publics
        __ff : true, // used to recognize ff'objects
        "debug" : function(mode) {
        	debug = mode || true;
        },
        "load" : function(path, config, plugin) {
        	if(config === undefined)
        		config = {};

	        var srvPath = path.split("/");
            var srvType = srvPath.pop();
            var srvId = config.name || (srvPath.pop() + "-" + srvType);

			if(!srvCache[srvId]) {
	            srvCache[srvId] = {
	                "name" : srvId.replace("-" + srvType, "")
	                , "type" : srvType
	                , "plugin" : plugin
	                , "path" : path
					, "actionUrl" : config.actionUrl
	                , "excludeParams" : []
	                , "limit": {}
					, "tpl" : []
					, "instance" : {}
	            };
			}
			
			
			var container =  config.container || jQuery("." + srvId);
			var template =  config.template || jQuery("." + srvId);
			var params = config.params || {};
			var dep = config.dep || {};
			var userParams = {};
			var service = {
				"path" : ""	
	            , "url" : ""
	            , "params" : ""
	            , "location": ""
		        , "limit": {}			
			}
			
			config.container = "";
			config.template = "";

			var hash = JSON.stringify(config);
			if(!srvCache[srvId]["instance"][hash]) {
				srvCache[srvId]["instance"][hash] = {
					"type" : srvType
					, "prefix" : config.prefix || "tpl-"
					, "callback" : config.callback
					, "container" : container
					, "request" : {}
					, "template" : ""
				};

				if(srvType == "detail") {
		            var locationParams = {
		                "compact" : {"default" : true, "location" : null}
		                , "service" : {"default" : null, "location" : null}
		                , "key" : {"default" : "ID", "location" : null}
		                , "value" : {"default" : 0, "location" : null}
		            }

		            if(params.compact !== undefined) {
		                 locationParams["compact"]["default"] = params.compact;
		            }
		            if(params.service !== undefined) {
		                 locationParams["service"]["default"] = params.service;
		            }

		            if(params.key !== undefined) {
		                 locationParams["key"]["default"] = params.key;
		            }
		            if(params.value !== undefined) {
		                 locationParams["value"]["default"] = params.value;
		            }
		            
		            if(window.location.search.replace("?", "").length > 0) {
		                var locationSearch = window.location.search.replace("?", "").split("&");    
		                if(locationSearch.length > 0) {
                    		locationSearch.each(function(key, searchParam) {
                    			var keySearch = searchParam.split("=")[0];
                    			if(!keySearch || keySearchToSkip.indexOf(keySearch) < 0) {
                    				var valueSearch = searchParam.split("=")[1];
			                        if(params.sourceKey && keySearch == params.sourceKey
			                            || keySearch == "keys[" + params.sourceKey + "]"
			                        ) {
			                            srvCache[srvId]["excludeParams"].push(params.sourceKey);
			                            locationParams["value"]["default"] = valueSearch;
			                        } else if(keySearch == locationParams["key"]["default"] 
			                            || keySearch == "keys[" + locationParams["key"]["default"] + "]"
			                        ) {
			                            srvCache[srvId]["excludeParams"].push(keySearch);
			                            locationParams["value"]["default"] = valueSearch;
			                        } else if(locationParams[keySearch] !== undefined) {
			                            locationParams[keySearch]["location"] = valueSearch;
			                        } else {
			                            userParams[keySearch] = valueSearch;
			                        }
								}
                    		});

		                    if(locationParams["compact"]["location"] !== null) {
		                         locationParams["compact"]["default"] = locationParams["compact"]["location"];
		                    }
		                    if(locationParams["service"]["location"] !== null) {
		                         locationParams["service"]["default"] = locationParams["service"]["location"];
		                    }

		                    if(locationParams["key"] !== undefined && locationParams["key"]["location"] !== null) {
		                         locationParams["key"]["default"] = locationParams["key"]["location"];
		                    }
		                    if(locationParams["value"] !== undefined && locationParams["value"]["location"] !== null) {
		                         locationParams["value"]["default"] = locationParams["value"]["location"];
		                    }
		                }
		            }
		            
		            service["limit"]["key"] = locationParams["key"]["default"];
		            service["limit"]["value"] = locationParams["value"]["default"];
		            
		            srvCache[srvId]["limit"] = service["limit"];
					
					service["path"] = srvCache[srvId]["path"];
		            
		            service["url"] = "srvid=" + srvId
		                                + "&compact=" + (debug ? "0" : locationParams["compact"]["default"])
		                                    + (locationParams["service"]["default"] !== null 
		                                        ? "&service=" + locationParams["service"]["default"]  
		                                        : ""
		                                    )
		                                    + (service["limit"]["key"]
		                                        ? "&limit[key]=" + service["limit"]["key"]
		                                        : ""
		                                    )
		                                    + (service["limit"]["value"] 
		                                        ? "&limit[value]=" + service["limit"]["value"]  
		                                        : ""
		                                    );
					
					service["params"] = "";
		            for(n in userParams) {
		                if(n.length > 0) {
		                    service["params"] += "&" + "params[" + n + "]" + "=" + userParams[n];
		                }
		            }	 
				} else {
		            var locationParams = {
		                "compact" : {"default" : true, "location" : null}
		                , "service" : {"default" : null, "location" : null}
		                , "page" : {"default" : 0, "location" : null}
		                , "rows" : {"default" : 10, "location" : null}
		                , "sort" : {"default" : null, "location" : null}
		                , "sortDir" : {"default" : "asc", "location" : null}
		                , "search" : {"default" : "", "location" : null}
		            }

		            if(dep) {
                		if(dep.rel && dep.name) {
				            service["limit"]["key"] = (dep.rel
				                                        ? dep.rel
				                                        : "ID_" + srvCache[dep.name]["limit"]["name"]
				                                    );
				            service["limit"]["value"] = srvCache[dep.name]["limit"]["value"];
                           
                           
				            if(srvCache[dep.name]["excludeParams"] !== undefined) {
				                srvCache[srvId]["excludeParams"] = srvCache[srvId]["excludeParams"].concat(srvCache[dep.name]["excludeParams"]);
				            }
						} else if(dep.key && dep.value) {
						
				            service["limit"]["key"] = dep.key;
				            service["limit"]["value"] = dep.value;
						}
					}                    

		            if(params.compact !== undefined) {
		                 locationParams["compact"]["default"] = params.compact;
		            }
		            if(params.service !== undefined) {
		                 locationParams["service"]["default"] = params.service;
		            }

		            if(params.rows !== undefined) {
		                 locationParams["rows"]["default"] = parseInt(params.rows);
		            }
		            if(params.page !== undefined && params.rows !== undefined) {
		                 locationParams["page"]["default"] = parseInt((params.page -1) * params.rows);
		            }
		            if(params.sort !== undefined) {
		                 locationParams["sort"]["default"] = params.sort;
		            }
		            if(params.sortDir !== undefined) {
		                 locationParams["sortDir"]["default"] = params.sortDir;
		            }
		            if(params.search !== undefined) {
		                 locationParams["search"]["default"] = params.search;
		            }

		            if(window.location.search.replace("?", "").length > 0) {
		                var locationSearch = window.location.search.replace("?", "").split("&");
		                if(locationSearch.length > 0) {
							locationSearch.each(function(key, searchParam) {
                    			var keySearch = searchParam.split("=")[0];
                    			if(!keySearch || keySearchToSkip.indexOf(keySearch) < 0) {
                    				var valueSearch = searchParam.split("=")[1];
			                        if(params.sourceKey && keySearch == params.sourceKey
			                            || keySearch == "keys[" + params.sourceKey + "]"
			                        ) {
			                            srvCache[srvId]["excludeParams"].push(params.sourceKey);
			                            locationParams["value"]["default"] = valueSearch;
			                        } else if(locationParams[keySearch] !== undefined) {
			                            locationParams[keySearch]["location"] = valueSearch;
			                        } else if(srvCache[srvId]["excludeParams"].indexOf(keySearch) < 0) {
			                            userParams[keySearch] = valueSearch;
			                        }
								}
							});

		                    if(locationParams["compact"]["location"] !== null) {
		                         locationParams["compact"]["default"] = locationParams["compact"]["location"];
		                    }
		                    if(locationParams["service"]["location"] !== null) {
		                         locationParams["service"]["default"] = locationParams["service"]["location"];
		                    }

		                    if(locationParams["rows"] !== undefined && locationParams["rows"]["location"] !== null) {
		                         locationParams["rows"]["default"] = parseInt(locationParams["rows"]["location"]);
		                    }
		                    if(locationParams["page"] !== undefined && locationParams["page"]["location"] !== null) {
		                         locationParams["page"]["default"] = parseInt((locationParams["page"]["location"] -1) * locationParams["rows"]["default"]);
		                    }
		                    if(locationParams["sort"] !== undefined && locationParams["sort"]["location"] !== null) {
		                         locationParams["sort"]["default"] = locationParams["sort"]["location"];
		                    }
		                    if(locationParams["sortDir"] !== undefined && locationParams["sortDir"]["location"] !== null) {
		                         locationParams["sortDir"]["default"] = locationParams["sortDir"]["location"];
		                    }
		                    if(locationParams["search"] !== undefined && locationParams["search"]["location"] !== null) {
		                         locationParams["search"]["default"] = locationParams["search"]["location"];
		                    }
		                }
		            }
		            service["path"] = srvCache[srvId]["path"] 
		            						+ (srvCache[srvId]["actionUrl"] !== undefined && srvCache[srvId]["actionUrl"].length > 0
		                        				? window.location.pathname.replace(srvCache[srvId]["actionUrl"], "")
		                        				: ""
		                        			);
		            service["url"] = "srvid=" + srvId
		                                + "&compact=" + (debug ? "0" : locationParams["compact"]["default"]) 
		                                    + (locationParams["service"]["default"] !== null 
		                                        ? "&service=" + locationParams["service"]["default"]  
		                                        : ""
		                                    )  
		                                    + (service["limit"]["key"]
		                                        ? "&limit[key]=" + service["limit"]["key"]
		                                        : ""
		                                    )
		                                    + (service["limit"]["value"] 
		                                        ? "&limit[value]=" + service["limit"]["value"]  
		                                        : ""
		                                    )
		                                    + (locationParams["rows"]["default"] !== null 
		                                        ? "&rows=" + locationParams["rows"]["default"]  
		                                        : ""
		                                    )
		                                    + (locationParams["page"]["default"] !== null 
		                                        ? "&page=" + locationParams["page"]["default"]  
		                                        : ""
		                                    )
		                                    + (locationParams["sort"]["default"] !== null 
		                                        ? "&sort=" + locationParams["sort"]["default"]  
		                                        : ""
		                                    )
		                                    + (locationParams["sortDir"]["default"] !== null 
		                                        ? "&sortDir=" + locationParams["sortDir"]["default"]  
		                                        : ""
		                                    )
		                                    + (locationParams["search"]["default"] !== null 
		                                        ? "&search=" + locationParams["search"]["default"]  
		                                        : ""
		                                    );  
					
					service["params"] = "";
		            for(n in userParams) {
		                if(n.length > 0) {
		                    service["params"] += "&" + "params[" + n + "]" + "=" + userParams[n];
		                }
		            }
					if(params && params.category !== undefined)
			            service["params"] += "&cat=" + params.category 
				}

				service["location"] = locationParams;
				
				var tpl = that.loadTpl(srvId, template, srvCache[srvId]["instance"][hash]["prefix"]);
				if(tpl["obj"]) {
					service["tpl"] = tpl["obj"]["params"];
					service["fields"] = tpl["obj"]["fields"];
				}

				srvCache[srvId]["instance"][hash]["request"] = service;
				srvCache[srvId]["instance"][hash]["template"] = srvCache[srvId]["tpl"][tpl["key"]];
			}			

            return srvCache[srvId]["instance"][hash];
            //return srvId;
        },
        "loadTpl" : function(srvId, elem, prefix) {
			var strError = "";
			var tpl = undefined;
			var template = undefined;
			var tplKey = srvCache[srvId]["tpl"].length;

			if(!jQuery(elem).length) {
            	strError = "The " + "." + srvId + " is undefined. The template can not be load. Try to add this class (" + "." + srvId + ") to your Template";
			} else {
				template = jQuery(elem).html().trim();
			}
			
			if(template) {
        		if(!jQuery(elem).hasClass(prefix + "loaded") && srvCache[srvId]["tpl"].length > 0) {
        			srvCache[srvId]["tpl"].each(function(key, value) {
						if(value["html"] == template) {
							tplKey = key;
							tpl = value;
							return true;
						}
        			});
        		}
        			
    			if(!tpl) {
    				if(prefix === undefined)
    					prefix = "";
    			
					if(srvCache[srvId]["type"] == "list" && !jQuery("." + prefix + "repeat", elem).length) {
						strError = "The " + "." + prefix + "repeat" + " is undefined. The template can not be load. Try to add this class (" + "." + prefix + "repeat" + ") to your Template";
					}

        			if(jQuery("." + prefix + "repeat", elem).length > 0) {
				        if(!strError && !jQuery("." + prefix + "repeat:first", elem).length) {
            				strError = "." + prefix + "repeat is undefined. The template require this class to recognize the recordset";
						} else {
							//jQuery("tbody > tr." + prefix + "repeat", elem).hide();
						}
				        //if(!strError && !jQuery("thead > th", elem).length) {
							//strError = "DataTable Require that the TABLE have THEAD with one or more TH";
						//}

						if(!strError) {
        					var tpl = {
        						"data" : { 
        							"params" : []
        							, "columns" : []
								}
								, "params" : []
								, "fields" : template.replace(new RegExp(jQuery("." + prefix + "repeat", elem).outerHTML().escapeRegExp(), "i"), "").match(/\[([^\[])*\]/gi)
								, "html" : template
        					};
							//tpl["params"].push({"key" : srvId, "fields" : template.match(/\[([^\[])*\]/gi)});

        					jQuery("." + prefix + "repeat > *", elem).each(function(i) {
        						var tplField = jQuery(this);
        						var classField = jQuery(this).attr("class");
        						
        						keyField = "cell" + i;
        						
        						tpl["params"].push({"key" : keyField, "fields" : tplField.html().match(/\[([^\[])*\]/gi)});
        						tpl["data"]["params"].push({"key" : keyField, "fieldTpl" : tplField.html()});
        						tpl["data"]["columns"].push({"mData" : keyField, "sClass": "col" + i + (classField !== undefined && classField.length > 0 ? " " + classField : "") });
							});
							
							//tpl["params"].push({"key" : srvId, "fields" : template.replace(new RegExp(jQuery("." + prefix + "repeat", elem).outerHTML().escapeRegExp(), "i"), "").match(/\[([^\[])*\]/gi) });

	/*
							jQuery("tbody:first > tr > td", elem).each(function(i) {
								if(jQuery("tbody > tr", elem).hasClass(prefix + "repeat")) {
									var tplField = jQuery("tbody > tr." + prefix + "repeat td:eq(" + i + ")", elem);
									var classField = jQuery("tbody > tr." + prefix + "repeat td:eq(" + i + ")", elem).attr("class");
									
									keyField = "cell" + i;
									
									tpl["data"]["params"].push({"fieldKey" : keyField, "fieldTpl" : tplField.html()});
								} else {
									keyField = jQuery(this).attr("class").replace(/[^a-zA-Z 0-9\.]+/g, "");
								}

								if(keyField.length > 0) {
									tpl["data"]["columns"].push({"mData" : keyField, "sClass": "col" + i + (classField !== undefined && classField.length > 0 ? " " + classField : "") });
								}
							});*/
						}        	
        			} else {
        				if(!strError) {
        					var tpl = { 
        						"data" : { 
									"params" : []
								}
								, "params" : []
								, "fields" : template.match(/\[([^\[])*\]/gi)
								, "html" : template
        					};
        					
        					tpl["params"].push({"key" : srvId, "fields" : template.match(/\[([^\[])*\]/gi)});
        					tpl["data"]["params"].push({"key" : srvId, "fieldTpl" : template});
						}
        			}

					srvCache[srvId]["tpl"].push(tpl);
					
					jQuery(elem).addClass(prefix + "loaded").hide();
				} 
			}

        	if(strError)
				console.error(strError);			

			return {"key" : tplKey, "obj" : tpl};
        },
        "refresh": function(srvId) {
        	
        },
      /*  "parse" : function(path, config) {
        	var instance = that.load(path, config);
        	//var srvId = that.load(path, config);

        	//var hash = JSON.stringify(config);
        	if(instance["response"]) {
        		that.drawInstance(instance);
        	} else {
	            jQuery.post(ff.site_path + "/srv" + instance["request"]["path"] + "?" + instance["request"]["url"] + instance["request"]["params"]
                	, {
						tpl_params : JSON.stringify(instance["request"]["tpl"])	
                	} 
                	, function(data) {
                		instance["response"] = data;
                		that.drawInstance(instance);
                	}
                	, "json"
	            );
			}        
        },*/
        "draw": function(services, altConfig, altPlugin) {
        	var post = undefined;

        	if(services) {
        		if(jQuery.isArray(services)) {
        			services.each(function(key, value) {
        				if(typeof value == "object") {
        					that.load(value["path"], value["config"], value["plugin"]);
						} else {
							that.load(value);
						}
        			});
        		} else {
        			if(typeof services == "object") {
        				that.load(services["path"], services["config"], services["plugin"]);
					} else {
						that.load(services, altConfig, altPlugin);
					}
        		}
        	}

        	if(srvCache) {
        		for(var srvName in srvCache) {
        			switch(srvCache[srvName]["plugin"]) {
						case "dataTable":
							if(srvCache[srvName]["instance"]) {
        						for(var instanceName in srvCache[srvName]["instance"]) {
        							parseDataTable(srvCache[srvName]["instance"][instanceName]);
								}
							}
							break;
        				default:	
        					if(srvCache[srvName]["instance"]) {
        						for(var instanceName in srvCache[srvName]["instance"]) {
        							if(srvCache[srvName]["instance"][instanceName]["response"] === undefined) {
        								if(!post)
        									post = {};
        								if(!post[srvName])
        									post[srvName] = [];

        								post[srvName].push({
        									"instance" : instanceName
        									, "path" : srvCache[srvName]["instance"][instanceName]["request"]["path"]
        									, "query" : srvCache[srvName]["instance"][instanceName]["request"]["url"] + srvCache[srvName]["instance"][instanceName]["request"]["params"]
        									, "tpl" : srvCache[srvName]["instance"][instanceName]["request"]["tpl"]
        									, "fields" : srvCache[srvName]["instance"][instanceName]["request"]["fields"]
										});
        							}
        						}
        					}
					}
        		}
        	}
        	
 			if(post) {
 				jQuery.post(ff.site_path + "/srv"
                	, {
						data : JSON.stringify(post)	
                	} 
                	, function(data) {
                		if(typeof data === "object") {
							if(debug)
								console.info(data);

        					for(var srvName in data) {
        						for(var instanceName in data[srvName]) {
        							if(srvCache[srvName]["instance"][instanceName]) {
        								srvCache[srvName]["instance"][instanceName]["response"] = data[srvName][instanceName];

        								that.drawInstance(srvCache[srvName]["instance"][instanceName]);
        							}
								}
							}
						} else {
							console.error(data);
						}
                	}
                	, "json"
	            );        	
			}
        },
        "drawInstance": function(instance) {
        	//jQuery(params["container"]).hide();
        	var tpl = instance["template"]["html"];

			if(instance["response"]["obj"]) {
				var parsedRows = "";

				for(var srvName in instance["response"]["obj"]) {
					if(jQuery(instance["template"]["html"]).find("." + srvName) && instance["response"]["obj"][srvName]) {
						if(instance["response"]["obj"][srvName]) {
							if(jQuery(tpl).find("." + instance["prefix"] + "repeat").length > 0) {
								var tplRow = jQuery(tpl).find("." + instance["prefix"] + "repeat").outerHTML();

								instance["response"]["obj"][srvName].each(function(key, value) {
									parsedRows = parsedRows + drawRow(srvName, tplRow, value);
								});

								tpl = tpl.replace(new RegExp(tplRow.escapeRegExp(), "gi"), parsedRows);
							}
							
							for(var srvField in instance["response"]["obj"][srvName]) {
								var regexp = "[" + srvField + "]";

								tpl = tpl.replace(new RegExp(regexp.escapeRegExp(), "gi"), instance["response"]["obj"][srvName][srvField]);
							}									
							
						}
					}
				}
			} else {
				tpl = tpl.replace(new RegExp(jQuery(tpl).find("." + instance["prefix"] + "repeat").outerHTML().escapeRegExp(), "gi"), "");
			}

			if(instance["response"]["data"]) {
				for(var action in instance["response"]["data"]) {
					var regexp = "[" + action + "]";
					tpl = tpl.replace(new RegExp(regexp.escapeRegExp(), "gi"), instance["response"]["data"][action]);
				}
			}
			
			if(!debug) {
				var tagField = instance["template"]["html"].match(/\[([^\[])*\]/gi);
				if(tagField) {
					tagField.each(function(key, value) {
						tpl = tpl.replace(new RegExp(value.escapeRegExp(), "gi"), "");
					});
				}
			}
			//tpl = tpl.replace(new RegExp("\\[.*\\]", "gi"), "");
			
			jQuery(instance["container"]).replaceWith(tpl);
			
			//ff.tplService.parseDataAction(instance);
			
			jQuery("a[href='javascript:void(0);']", instance["container"]).hide();

			if(jQuery.isFunction(instance["callback"])) {
				instance.callback(instance);
			}

        }, 
        "fnCallback" : function (srvId) {
            var targetTable = "";
            if(jQuery("." + srvId).is("TABLE")) {
                targetTable = "." + srvId;
            } else {
                targetTable = "." + srvId + " TABLE";
            }
			if(jQuery(targetTable).hasClass("dataTable"))
            	jQuery(targetTable).dataTable().fnDraw(false);
            else {
            	ff.tplService.refresh(srvId);
            	//da fare il refresh dei data con il parse classico
			}
        },
        "parseAction" : function(srvId, url, ret_params, modal, action) {
        	if(url !== undefined && url.length > 0
        	) {
        		if(action == "back") {
        			if(window.location.search.indexOf("ret_url") >= 0) {
						window.location.href = ff.getURLParameter("ret_url");
        			} else {
						window.history.back();
        			}
        			return false;
				} else if(action == "export") {
        			var processedUrl = url;
				} else {
        			var ret_url = window.location.pathname + (ret_params ? "?" + ret_params : "");
        			var processedUrl = url + "&ret_url=" + encodeURIComponent(ret_url);
				}
        		if(url.substring(0,1) != "/") {  
					if(srvCache[srvId]["actionUrl"] !== undefined && srvCache[srvId]["actionUrl"].length > 0) {	
						processedUrl = srvCache[srvId]["actionUrl"] + "/" + processedUrl;
					} else {
						processedUrl = "/" + window.location.pathname.trim("/") + "/" + processedUrl;
					}
        		} 
        		//PARTE NON FUNZIONANTE DA SISTEMARE
				if(srvCache[srvId].setting !== undefined) {
					if(srvCache[srvId].setting.concatActionPath !== undefined
						&& srvCache[srvId].customParams !== undefined 
					) {
						for (var i in srvCache[srvId].setting.concatActionPath) {
							if(typeof srvCache[srvId].setting.concatActionPath[i] == "object") {
								for (var n in srvCache[srvId].setting.concatActionPath[i]) {
									if(srvCache[srvId]["customParams"][srvCache[srvId].setting.concatActionPath[i][n]]
										&& processedUrl.indexOf(n + "?") >= 0
									) {
										processedUrl = processedUrl.replace(n + "?", n + "-" + srvCache[srvId]["customParams"][srvCache[srvId].setting.concatActionPath[i][n]] + "?");
										break;
									}
								}
							} else {
								if(srvCache[srvId]["customParams"][srvCache[srvId].setting.concatActionPath[i]]) {
									processedUrl = processedUrl.replace("?", "-" + srvCache[srvId]["customParams"][srvCache[srvId].setting.concatActionPath[i]] + "?"); 
									break;
								}
							}
						}
					}
				}        		
        		switch(modal) {
					case "dialog":
        				switch(action) {
        					case "confirmdelete":
								processedUrl = window.location.pathname + "/dialog?message=[_label_delete_record]&cancelurl=[CLOSEDIALOG]&confirmurl=" + encodeURIComponent(processedUrl);
        						break;
        					default:
        				}

        				ff.load(["ff.ajax", "jquery-ui", "ff.ffPage.dialog"], function() {
							ff.ffPage.dialog.addDialog({
								"id" : "tplServiceDialog",
								"callback" : "ff.tplService.fnCallback('" + srvId + "')",
								"url" : processedUrl,
								"resizable" : true,
								"position" : "center",
								"draggable" : true,
								"title" : ""
								, "doredirects" : false
							});
							ff.ffPage.dialog.doOpen('tplServiceDialog');        				
        				});
        				/*
						ff.pluginLoad("ff.ajax", "/themes/library/ff/ajax.js", function() {
							ff.pluginLoad("jquery.ui", "/themes/library/jquery.ui/jquery.ui.js", function() {
								ff.pluginLoad("ff.ffPage.dialog", "/themes/responsive/ff/ffPage/widgets/dialog/dialog.js", function() {
								});
							});
						});

						ff.pluginAddInit("ff.ffPage.dialog", function () {
							ff.ffPage.dialog.addDialog({
								"id" : "tplServiceDialog",
								"callback" : "ff.tplService.fnCallback('" + srvId + "')",
								"url" : processedUrl,
								"resizable" : true,
								"position" : "center",
								"draggable" : true,
								"title" : ""
								, "doredirects" : false
							});
							ff.ffPage.dialog.doOpen('tplServiceDialog');
						});*/
						
						break;
					case "request":
        				switch(action) {
        					case "confirmdelete":
								processedUrl = window.location.pathname + "/dialog?message=[_label_delete_record]&cancelurl=[CLOSEDIALOG]&confirmurl=" + encodeURIComponent(processedUrl);
        						break;
        					default:
        				}
                                   
						ff.pluginLoad("ff.ajax", "/themes/library/ff/ajax.js", function() {
							ff.ajax.doRequest({
								'action': action
								, 'fields': []
								, "callback" : function() { ff.tplService.fnCallback(srvId); return "return"; }
								, 'url' : processedUrl
							});
						});
                        //, 'injectid' : "." + srvCache[srvId]["actionUrl"] 
						break;
					default:
        				switch(action) {
        					case "confirmdelete":
								processedUrl = window.location.pathname + "/dialog?message=[_label_delete_record]&cancelurl=" + encodeURIComponent(ret_url) + "&confirmurl=" + encodeURIComponent(processedUrl);
        						break;
        					default:
        				}
					
						window.location.href = processedUrl; 
        		}
			}				
        }
    };
    
	var parseDataTable = function(instance) {
	    //var instance = that.load(path, config);
	    //var srvId = that.load(path, config);
		
		if(instance["request"]["tpl"]) {
		    ff.pluginLoad("jquery.fn.DataTable", "/themes/library/datatables/media/js/jquery.dataTables.min.js", function() {
	            //ff.pluginLoad("jquery.fn.ZeroClipboard", "/themes/library/datatables/extras/TableTools/media/js/ZeroClipboard.js", function() {
            		ff.pluginLoad("jquery.fn.TableTools", "/themes/library/datatables/extras/TableTools/media/js/TableTools.min.js", function() { 
		                //ff.pluginLoad("jquery.fn.dataTableExt.oApi.fnReloadAjax", "/themes/library/datatables/fnReloadAjax.js", function() {
				            var activeJQueryUI = false;

		                    if(instance["template"]["data"]["columns"].length > 0) {
			                    //jQuery("." + srvCache[srvId].name).fadeIn();
			                    jQuery("." + instance["prefix"] + "repeat", instance["container"]).remove();
			                    instance["container"].show();

					            (jQuery(instance["container"]).is("TABLE")
					                ? jQuery(instance["container"])
					                : jQuery("TABLE", instance["container"])
					            ).dataTable({
		                			"bStateSave": true
					                , "bProcessing" : true
					                , "bServerSide" : true
					                , "bDestroy" : true //activeJQueryUI
					                , "iDisplayLength" : instance["request"]["location"]["rows"]["default"]
					                , "iDisplayStart" : instance["request"]["location"]["page"]["default"]
					                , "oSearch": {"sSearch": instance["request"]["location"]["search"]["default"] }
					                , "aaSorting": (instance["request"]["location"]["sort"]["default"] === null ? [[0,instance["request"]["location"]["sortDir"]["default"]]] : [[instance["request"]["location"]["sort"]["default"],instance["request"]["location"]["sortDir"]["default"]]])
					                , "bDeferRender": true
					                , "bJQueryUI": activeJQueryUI
			                        /*
			                        , "bFilter": false
			                        , "bPaginate": false 
			                        , "bInfo": true 
			                        */
			                        , "fnPreDrawCallback": function( oSettings ) {
			                            if(!oSettings["initDone"]) {
			                                oSettings["initDone"] = true;

			                                jQuery('#' + oSettings.sTableId + '_wrapper .dataTables_filter').hide();
			                                jQuery('#' + oSettings.sTableId + '_wrapper .dataTables_info').hide();
			                                jQuery('#' + oSettings.sTableId + '_wrapper .dataTables_paginate').hide();
			                            }
			                        }
					                , "sAjaxSource": ff.site_path + "/srv" + instance["request"]["path"]
			                                            +"?out=datatable" 
			                                            + "&" + instance["request"]["url"]
			                                            + "&tpl_params=" + JSON.stringify(instance["template"]["data"]["params"]) 
			                                            + "&tpl_fields=" + JSON.stringify(instance["template"]["fields"]) 
			                                            + instance["request"]["params"]
					                , "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
					                    
					                    //jQuery(nRow).find("a[href='javascript:void(0);']").hide();
					                      // Bold the grade for all 'A' grade browsers
					                }
			                        , "fnServerData": function ( sSource, aoData, fnCallback, oSettings ) {
			                            var tableId = oSettings.sTableId;
			                            
			                            oSettings.jqXHR = $.ajax({
			                                "dataType": 'json',
			                                "type": "POST",
			                                "url": sSource,
			                                "data": aoData,
			                                "cache": true,
			                                "success": function(data) {
			                                	if(typeof data == "object") {
				                                    if (data["aaData"].length < data["iTotalRecords"]) {
				                                        jQuery('#' + tableId + '_wrapper .dataTables_filter').show();
				                                        jQuery('#' + tableId + '_wrapper .dataTables_info').show();
				                                        jQuery('#' + tableId + '_wrapper .dataTables_paginate').show();
				                                    }                                            

				                                    instance["response"] = data;
				                                    if(debug)
			                                    		console.info(data["data"]);

													if(instance["response"]["data"]) {
														var tpl = jQuery(".addnew", instance["container"]).clone().wrap('<div />').parent().html();
														if(tpl) {
															for(var action in instance["response"]["data"]) {
																var regexp = "[" + action + "]";
																tpl = tpl.replace(new RegExp(regexp.escapeRegExp(), "gi"), instance["response"]["data"][action]);
															}
															jQuery(".addnew", instance["container"]).replaceWith(tpl);
														}
													}
													jQuery("a[href='javascript:void(0);']", instance["container"]).hide();

				                                    //setTimeout("ff.tplService.parseDataAction('" + srvId + "')", 1000);
				                                    fnCallback(data);
				                                    
				                                    if(jQuery.isFunction(instance["callback"])) {
														instance.callback();
				                                    }
												} else {
													console.error(data);
												}
			                                },
			                                "error": function(data) {
			                                	if(typeof data == "object") {
			                                		console.error(data["responseText"]);
												} else {
													console.error(data);
												}
			                                
			                                }
			                            });
			                        }
			                        , "fnDrawCallback": function(oSettings) {
			                        }                                
					                , "aoColumns" : instance["template"]["data"]["columns"]
					                , "sPaginationType": "full_numbers" 
					                , "sDom": (activeJQueryUI ? '<"H"Tlfr>t<"F"ip>'  : '<"top"Tlfr>t<"bottom"ip><"clear">')
					                , "oTableTools": {
		                        		"sSwfPath": "/themes/library/datatables/extras/TableTools/media/swf/copy_csv_xls_pdf.swf",
					                    "aButtons": [
					                        "copy",
					                        "csv",
					                        "xls",
					                        {
					                            "sExtends": "pdf",
					                            "sPdfOrientation": "landscape",
					                            "sPdfMessage": ""
					                        }
					                    ]
					                }
					            }); 
							}
		                //});
				    });
				//});
			});
		}
	};

	var drawRow = function(srvName, tplRow, data) {
        if(data["fields"]) {
			for(var field in data["fields"]) {
				var regexp = "[" + field + "]";
				tplRow = tplRow.replace(new RegExp(regexp.escapeRegExp(), "gi"), data["fields"][field]);

				var regexp = "[" + srvName + "." + field + "]";
				tplRow = tplRow.replace(new RegExp(regexp.escapeRegExp(), "gi"), data["fields"][field]);
			}
		}
		if(data["actions"]) {
			for(var action in data["actions"]) {
				var regexp = "[action." + action + "]";
				tplRow = tplRow.replace(new RegExp(regexp.escapeRegExp(), "gi"), data["actions"][action]);
			}
		}
        return tplRow;
    };

    return that;
    
})();  