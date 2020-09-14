(function($) {
	$.extend({
		jTableSort: new function() {
			var parsers = [];

			this.defaults = {
				sortInitialOrder: "asc",
				headers: {},
				headerList: [],
				sortList: [],
				textExtraction: "simple",
				onHeadGetOrdered: null,
				onHeadGetUnordered: null,
				startline: 0
			};

			function getElementText(mode, node) {

				if(!node) return "";

				var t = "";

				if(mode == "simple") {
					while (node.childNodes[0] && node.childNodes[0].hasChildNodes())
					{
						node = node.childNodes[0]
					}
					t = node.innerHTML;
				} else {
					if(typeof(mode) == "function") {
						t = mode(node);
					} else {
						t = jQuery(node).text();
					}
				}
				return t;
			}

			function sortText(a,b) {
				return ((a < b) ? -1 : ((a > b) ? 1 : 0));
			};

			function sortTextDesc(a,b) {
				return ((b < a) ? -1 : ((b > a) ? 1 : 0));
			};

	 		function sortNumeric(a,b) {
				return a-b;
			};

			function sortNumericDesc(a,b) {
				return b-a;
			};

			function formatSortingOrder(v) {

				if(typeof(v) != "Number") {
					i = (v.toLowerCase() == "desc") ? 1 : 0;
				} else {
					i = (v == (0 || 1)) ? v : 0;
				}
				return i;
			}

			this.isDigit = function(s,config) {
				var DECIMAL = '\\' + config.decimal;
				var exp = '/(^[+]?0(' + DECIMAL +'0+)?$)|(^([-+]?[1-9][0-9]*)$)|(^([-+]?((0?|[1-9][0-9]*)' + DECIMAL +'(0*[1-9][0-9]*)))$)|(^[-+]?[1-9]+[0-9]*' + DECIMAL +'0+$)/';
				return RegExp(exp).test($.trim(s));
			};

			this.formatFloat = function(s) {
				var i = parseFloat(s);
				return (isNaN(i)) ? 0 : i;
			};
			this.formatInt = function(s) {
				var i = parseInt(s);
				return (isNaN(i)) ? 0 : i;
			};

			function checkHeaderOptions(table, i) {
				if((table.jTableSort["config"].headers[i]) && (table.jTableSort["config"].headers[i].sorter === false)) { return true; };
				return false;
			}

			function detectParserForColumn(table,node) {
				var l = parsers.length;
				for(var i=1; i < l; i++) {
					if(parsers[i].is($.trim(getElementText(table.jTableSort["config"], node)), table, node)) {
						return parsers[i];
					}
				}
				/* 0 is always the generic parser (text) */
				return parsers[0];
			}

			function getParserById(name) {
				var l = parsers.length;
				for(var i=0; i < l; i++) {
					if(parsers[i].id.toLowerCase() == name.toLowerCase()) {
						return parsers[i];
					}
				}
				return false;
			}

			this.addParser = function(parser) {
				var l = parsers.length, a = true;
				for(var i=0; i < l; i++) {
					if(parsers[i].id.toLowerCase() == parser.id.toLowerCase()) {
						a = false;
					}
				}
				if(a) { parsers.push(parser); };
			};

			/* public methods */
			this.construct = function(settings) {
				return this.each(function() {
					var id = this.id;
					var table = jQuery("TABLE", this).get(0);
					if(table === undefined) 
                        return;

					if(!table.tHead || !table.tBodies) return;

					table.jTableSort = [];
					table.jTableSort["config"] = {};

					config = $.extend(table.jTableSort["config"], $.jTableSort.defaults, settings);

					table.resource_id = table.jTableSort["config"].resource_id;

					tableHeaders = jQuery("thead th", table);

					tableHeaders.each(function(index) {
						th = this;

						th.jTableSort = [];
						th.jTableSort["column"] = index;
						th.jTableSort["count"] = 0;

						th.jTableSort["order"] = formatSortingOrder(table.jTableSort["config"].sortInitialOrder);

						if (/*checkHeaderMetadata(th) || */checkHeaderOptions(table, index))
							th.jTableSort["sortDisabled"] = true;
						else
							jQuery(th).css("cursor", "pointer");

						/* add cell to headerList */
						config.headerList[index] = th;

					});

					/*firstRowCells = jQuery("tbody tr td", table); */

					var rows = table.tBodies[0].rows;

					if(rows[0 + config.startline]) {
						var /*list = [],*/ cells = rows[0 + config.startline].cells, l = cells.length;

						for (var i=0;i < l; i++) {
							var p = false;

							if((config.headers[i] && config.headers[i].sorter)) {
								p = getParserById(config.headers[i].sorter);
							}
							if(!p) {
								p = detectParserForColumn(table, cells[i]);
							}

							config.headerList[i].jTableSort["sorter"] = p;
						}
					}

					/* apply event handling to headers
					 this is to big, perhaps break it out?*/
					tableHeaders.click(function(e) {
						jQuery(table).trigger("sortStart");

						th = this;
						var totalRows = (table.tBodies[0] && table.tBodies[0].rows.length) || 0;

						if(!th.jTableSort["sortDisabled"] && totalRows > 0) {

							/* get current column index */
							var i = th.jTableSort["column"];

							/* get current column sort order */
							th.jTableSort["order"] = th.jTableSort["count"]++ % 2;

							if (typeof(config.onHeadGetUnordered) == "function" && config.sortList.length)
							{
								for (j = 0; j < config.sortList.length; j++)
								{
									config.onHeadGetUnordered(table, config.sortList[j][0]);
								}
							}

							/* user only whants to sort on one column */
							if(!e[config.sortMultiSortKey]) {

								/* flush the sort list */
								config.sortList = [];

								if(config.sortForce != null) {
									var a = config.sortForce;
									for(var j=0; j < a.length; j++) {
										if(a[j][0] != i) {
											config.sortList.push(a[j]);
										}
									}
								}

								/* add column to sort list */
								config.sortList.push([i, th.jTableSort["order"]]);

							/* multi column sorting */
							} else {
								/* the user has clicked on an all ready sortet column. */
								if(isValueInArray(i, config.sortList)) {

									/* revers the sorting direction for all tables. */
									for(var j=0; j < config.sortList.length; j++) {
										var s = config.sortList[j], o = config.headerList[s[0]];
										if(s[0] == i) {
											o.jTableSort["count"] = s[1];
											o.jTableSort["count"]++;
											s[1] = o.jTableSort["count"] % 2;
										}
									}
								} else {
									/* add column to sort list array */
									config.sortList.push([i,th.jTableSort["order"]]);
								}
							};

							if (typeof(config.onHeadGetOrdered) == "function" && config.sortList.length)
							{
								for (j = 0; j < config.sortList.length; j++)
								{
									config.onHeadGetOrdered(table, config.sortList[j][0]);
								}
							}

							setTimeout(function() {
								/*set css for headers
								setHeadersCss($this[0],$headers,config.sortList,sortCSS);*/

								var dynamicExp = "var sortWrapper = function(a,b) {", l = config.sortList.length;

								/* build data */
								var totalRows = (table.tBodies[0] && table.tBodies[0].rows.length) || 0,
									totalCells = (table.tBodies[0].rows[0] && table.tBodies[0].rows[0].cells.length) || 0,
									data = {cache: [], values: []};

								for (var i=0 + config.startline; i < totalRows; ++i) {

									var r = table.tBodies[0].rows[i], ori_cols = [], cols = [];

									for(var c=0; c < totalCells; ++c) {
										cols.push(tableHeaders[c].jTableSort["sorter"].format(getElementText(config.textExtraction, r.cells[c]), table, r.cells[c]));
										ori_cols.push(r.cells[c].innerHTML);
									}

									cols.push(i - config.startline);
									cols.push(table.tBodies[0].rows[i].dragsort_id);
									data.values.push(cols);
									data.cache.push(ori_cols);
									cols = null;
									ori_cols = null;
								};

								for (var i=0; i < l; i++) {

									var c = config.sortList[i][0];
									var order = config.sortList[i][1];
									var s = (tableHeaders[c].jTableSort["sorter"].type == "text") ? ((order == 0) ? "sortText" : "sortTextDesc") : ((order == 0) ? "sortNumeric" : "sortNumericDesc");

									var e = "e" + i;

									dynamicExp += "var " + e + " = " + s + "(a[" + c + "],b[" + c + "]); ";
									dynamicExp += "if(" + e + ") { return " + e + "; } ";
									dynamicExp += "else { ";
								}

								/* if value is the same keep orignal order */
								var orgOrderCol = data.values[0].length - 1;
								dynamicExp += "return a[" + orgOrderCol + "]-b[" + orgOrderCol + "];";

								for(var i=0; i < l; i++) {
									dynamicExp += "}; ";
								}

								dynamicExp += "return 0; ";
								dynamicExp += "}; ";

								eval(dynamicExp);

								data.values.sort(sortWrapper);

								for (var i=0 + config.startline; i < totalRows; ++i) {

									var r = table.tBodies[0].rows[i], cols = [];

									table.tBodies[0].rows[i].dragsort_id = data.values[i - config.startline][totalCells + 1];

									for(var j=0; j < totalCells; ++j) {
										r.cells[j].innerHTML = data.cache[data.values[i - config.startline][totalCells]][j];
									}
								};

								ff.ffGrid.dragsort.reorder(id);
								jQuery(table).trigger("sortEnd");

							},1);
							/* stop normal event by returning false */
							return false;
						}
					/* cancel selection */
					}).mousedown(function() {
						if(config.cancelSelection) {
							this.onselectstart = function() {return false};
							return false;
						}
					});


					return;
				});
			};
		}
	});

	/* extend plugin scope */
	$.fn.extend({
        jTableSort: $.jTableSort.construct
	});

	var ts = $.jTableSort;

	ts.addParser({
		id: "text",
		is: function(s) {
			return true;
		},
		format: function(s) {
			return $.trim(s.toLowerCase());
		},
		type: "text"
	});

	ts.addParser({
		id: "digit",
		is: function(s,table) {
			var c = table.jTableSort["config"];
			return $.jTableSort.isDigit(s,c);
		},
		format: function(s) {
			return $.jTableSort.formatFloat(s);
		},
		type: "numeric"
	});

	ts.addParser({
		id: "currency",
		is: function(s) {
			return /^[£$€?.]/.test(s);
		},
		format: function(s) {
			return $.jTableSort.formatFloat(s.replace(new RegExp(/[^0-9.]/g),""));
		},
		type: "numeric"
	});

	ts.addParser({
		id: "ipAddress",
		is: function(s) {
			return /^\d{2,3}[\.]\d{2,3}[\.]\d{2,3}[\.]\d{2,3}$/.test(s);
		},
		format: function(s) {
			var a = s.split("."), r = "", l = a.length;
			for(var i = 0; i < l; i++) {
				var item = a[i];
			   	if(item.length == 2) {
					r += "0" + item;
			   	} else {
					r += item;
			   	}
			}
			return $.jTableSort.formatFloat(r);
		},
		type: "numeric"
	});

	ts.addParser({
		id: "url",
		is: function(s) {
			return /^(https?|ftp|file):\/\/$/.test(s);
		},
		format: function(s) {
			return jQuery.trim(s.replace(new RegExp(/(https?|ftp|file):\/\//),''));
		},
		type: "text"
	});

	ts.addParser({
		id: "isoDate",
		is: function(s) {
			return /^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/.test(s);
		},
		format: function(s) {
			return $.jTableSort.formatFloat((s != "") ? new Date(s.replace(new RegExp(/-/g),"/")).getTime() : "0");
		},
		type: "numeric"
	});

	ts.addParser({
		id: "percent",
		is: function(s) {
			return /\%$/.test($.trim(s));
		},
		format: function(s) {
			return $.jTableSort.formatFloat(s.replace(new RegExp(/%/g),""));
		},
		type: "numeric"
	});

	ts.addParser({
		id: "usLongDate",
		is: function(s) {
			return s.match(new RegExp(/^[A-Za-z]{3,10}\.? [0-9]{1,2}, ([0-9]{4}|'?[0-9]{2}) (([0-2]?[0-9]:[0-5][0-9])|([0-1]?[0-9]:[0-5][0-9]\s(AM|PM)))$/));
		},
		format: function(s) {
			return $.jTableSort.formatFloat(new Date(s).getTime());
		},
		type: "numeric"
	});

	ts.addParser({
		id: "shortDate",
		is: function(s) {
			return /\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/.test(s);
		},
		format: function(s,table) {
			var c = table.jTableSort["config"];
			s = s.replace(/\-/g,"/");
			if(c.dateFormat == "us") {
				/* reformat the string in ISO format */
				s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/, "$3/$1/$2");
			} else if(c.dateFormat == "uk") {
				/* reformat the string in ISO format */
				s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/, "$3/$2/$1");
			} else if(c.dateFormat == "dd/mm/yy" || c.dateFormat == "dd-mm-yy") {
				s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2})/, "$1/$2/$3");
			}
			return $.jTableSort.formatFloat(new Date(s).getTime());
		},
		type: "numeric"
	});

	ts.addParser({
	    id: "time",
	    is: function(s) {
	        return /^(([0-2]?[0-9]:[0-5][0-9])|([0-1]?[0-9]:[0-5][0-9]\s(am|pm)))$/.test(s);
	    },
	    format: function(s) {
	        return $.jTableSort.formatFloat(new Date("2000/01/01 " + s).getTime());
	    },
	  type: "numeric"
	});


	ts.addParser({
	    id: "metadata",
	    is: function(s) {
	        return false;
	    },
	    format: function(s,table,cell) {
			var c = table.jTableSort["config"], p = (!c.parserMetadataName) ? 'sortValue' : c.parserMetadataName;
	        return jQuery(cell).metadata()[p];
	    },
	  type: "numeric"
	});
})(jQuery);

ff.ffGrid.labelsort = (function () {

var that = { /* publics */
__ff : true, /* used to recognize ff'objects */

"onHeadGetOrdered" : function(table, index){
	for (i = 0; i < table.jTableSort.config["headerList"].length; i++)
	{
		jQuery(table.jTableSort.config["headerList"][i]).removeClass("first-selected").removeClass("last-selected").removeClass("button-selected");
	}

	th = table.jTableSort.config["headerList"][index];
	if (index == 0)
		jQuery(th).addClass("first-selected");
	else if (index == table.jTableSort.config["headerList"].length - 1)
		jQuery(th).addClass("last-selected");
	else
		jQuery(th).addClass("button-selected");
}

}; /* publics' end */

return that;

/* code's end. */
})();
