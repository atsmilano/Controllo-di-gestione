ff.ffField.gmap = (function () {
	var googleData	=  {};

	var gmap_data	=  new Array();

	var that = { /* publics*/
		__ff : true, /* used to recognize ff'objects*/

		"init" : function (key, region) {
			if(window["google"] === undefined) {
				window.onload = ff.ffField.gmap.loadScript(key, region);
			} else {
				ff.ffField.gmap.loadMaps(true); 
			}
		}, 
		"loadScript" : function(key, region) {
			var script = document.createElement("script");
			script.type = "text/javascript";
			var url_data = window.location.href.parseUri();
			script.src = url_data.protocol + "://maps.google.com/maps?file=api&v=2&key=" + key + (region ? "&" + region : "") +"&callback=ff.ffField.gmap.loadMaps";
			document.body.appendChild(script);
		},
		"addData": function(data) 
		{
			if(!googleData[data.id])
				googleData[data.id] = { "data" : data, "loaded" : false};
		},		
		"loadMaps" : function(reload) {
			for(var i in googleData) {
				if(reload || !googleData[i]["loaded"]) {
					if(!jQuery("#" + i + "\\[map\\]").height()) {
						jQuery("#" + i + "\\[map\\]").height(300);
					}

					if(googleData[i]["data"].prefixClass) {
						googleData[i]["domPrefix"] = googleData[i]["data"].prefixClass;
					}
					if(googleData[i]["data"].updateClass) {
						var arrUpdateClass = googleData[i]["data"].updateClass.split(",");
						if(arrUpdateClass.length > 0) {
							arrUpdateClass.each(function(i, updateClass) {
								var target = "";
								var targetAction = "";
								
								if($("." + updateClass).is("input")) {
									target = "." . updateClass;
									targetAction = "blur";
								} else {
									if($("." + updateClass + " input").length) {
										target = "." + updateClass + " input";
										targetAction = "blur";
									} else if($("." + updateClass + " select").length) {
										target = "." + updateClass + " select";
										targetAction = "change";
									} else {
										if($("." + updateClass).length > 0) {
											target = "." + updateClass + " select";
											targetAction = "change";
										}
									}
								}
								
								if(target) {
									$(document).on(targetAction, target,function (e){
										if($(e.target).val()) {
											var newAddress = ff.ffField.gmap.getAddressByClass(googleData[i]["data"].updateClass);

											document.getElementById(i + "[search]").value = newAddress;
											$(e.target).addClass("gmap-exclude");
											ff.ffField.gmap.searchAddress(i, newAddress, googleData[i]["data"].startZoom);
										}
									});
								}
							});
						
							var newAddress = ff.ffField.gmap.getAddressByClass(googleData[i]["data"].updateClass);
							if(newAddress
								&& document.getElementById(i + "[search]").value != newAddress
							) {
								googleData[i]["forceSearch"] = true;
								document.getElementById(i + "[search]").value = newAddress;
							}
						}
					}
					
					googleData[i]["draggable"] = googleData[i]["data"].draggable;
					

					googleData[i]["map"] = new GMap2(document.getElementById(i + "[map]"));
					googleData[i]["map"].addControl(new GLargeMapControl3D());
					googleData[i]["map"].addMapType(G_PHYSICAL_MAP);

					GEvent.addListener(googleData[i]["map"], 'click', function(overlay, point) {
							if (overlay) {
								ff.ffField.gmap.removeMarker(i);
							} else if (point) {
								ff.ffField.gmap.setMarker(i, point, "", false);
							}
						});

					GEvent.addListener(googleData[i]["map"], 'zoomend', function(oldLevel, newLevel) {
							ff.ffField.gmap.setInfo(i);
						});

					googleData[i]["geocoder"] = new GClientGeocoder() ;
					googleData[i]["icon"] = new GIcon();
					googleData[i]["icon"].image = "http://labs.google.com/ridefinder/images/mm_20_red.png";
					googleData[i]["icon"].shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png";
					googleData[i]["icon"].iconSize = new GSize(12, 20);
					googleData[i]["icon"].shadowSize = new GSize(22, 20);
					googleData[i]["icon"].iconAnchor = new GPoint(6, 20);

					
					if(googleData[i]["forceSearch"]) {
						ff.ffField.gmap.searchAddress(i, document.getElementById(i + '[search]').value, googleData[i]["data"].startZoom);
					} else {
						googleData[i]["map"].setCenter(new GLatLng(googleData[i]["data"].startLat, googleData[i]["data"].startLng), googleData[i]["data"].startZoom);

						if(googleData[i]["data"].setMarker) {
							ff.ffField.gmap.setMarker(i, new GLatLng(googleData[i]["data"].startLat, googleData[i]["data"].startLng), googleData[i]["data"].title, true)
						}
					}

					ff.ffField.gmap.searchAddress(i, document.getElementById(i + '[search]').value);				
				}
			}
		},
		"getAddressByClass": function(updateClass) {
			var arrUpdateClass = updateClass.split(",");
			var newAddress = "";

			arrUpdateClass.each(function(i, updateClass) {
				var targetValue = "";

				if($("." + updateClass).is("input")) {
					targetValue = $("." + updateClass).val();
				} else {
					if($("." + updateClass + " input").length) {
						targetValue = $("." + updateClass + " input").val();
					} else if($("." + updateClass + " select").length && $("." + updateClass + " select option:selected").val()) {
						targetValue = $("." + updateClass + " select option:selected").text();
					}
				}
				if(targetValue) {
					if(newAddress)
						newAddress = newAddress + ",";

					newAddress = newAddress + targetValue;
				}
			});
			
			return newAddress;
		},
		"removeMarker" : function (id)	{
			if (googleData[id]["marker"])
				googleData[id]["map"].removeOverlay(googleData[id]["marker"]);

			document.getElementById(id + "[lat]").value = "";
			document.getElementById(id + "[lng]").value = "";
			document.getElementById(id + "[zoom]").value = "";

			googleData[id]["marker"] = null;
		}, 

		"setMarker" : function (id, point, sTitle, center, zoom) {
			ff.ffField.gmap.removeMarker(id);

			if (center)
			{
				if(zoom)
					googleData[id]["map"].setCenter(point, zoom);	
				else 
					googleData[id]["map"].setCenter(point);	
				
				
			}

			googleData[id]["marker"] = new GMarker(point, {icon: googleData[id]["icon"], draggable: googleData[id]["draggable"], title: sTitle});

			if (googleData[id]["draggable"])
			{
				/*googleData[id]["marker"].enableDragging();*/
				GEvent.addListener(googleData[id]["marker"], 'dragend', function() {ff.ffField.gmap.newPoint(id)});
			}

			googleData[id]["map"].addOverlay(googleData[id]["marker"]);

			ff.ffField.gmap.setInfo(id);
		}, 
		"searchAddress" : function (id, address, zoom) {
			googleData[id]["geocoder"].getLatLng( address, function(point) {
				if (!point) {
					/*alert(address + " not found");*/
				} else {
					if(googleData[id]["domPrefix"] !== undefined && googleData[id]["domPrefix"].length > 0) {
						googleData[id]["geocoder"].getLocations(point, function(data) {
							var arrAddress = {};
							if(data.Placemark !== undefined
								&& data.Placemark[0]["AddressDetails"] !== undefined
								&& data.Placemark[0]["AddressDetails"]["Country"] !== undefined
								&& data.Placemark[0]["AddressDetails"]["Country"]["AdministrativeArea"] !== undefined
								&& data.Placemark[0]["AddressDetails"]["Country"]["AdministrativeArea"]["SubAdministrativeArea"] !== undefined
								&& data.Placemark[0]["AddressDetails"]["Country"]["AdministrativeArea"]["SubAdministrativeArea"]["Locality"] !== undefined
								&& data.Placemark[0]["AddressDetails"]["Country"]["AdministrativeArea"]["SubAdministrativeArea"]["Locality"]["PostalCode"] !== undefined
							) {
								arrAddress["address"] = data.Placemark[0]["AddressDetails"]["Country"]["AdministrativeArea"]["SubAdministrativeArea"]["Locality"]["Thoroughfare"]["ThoroughfareName"];
								arrAddress["cap"] = data.Placemark[0]["AddressDetails"]["Country"]["AdministrativeArea"]["SubAdministrativeArea"]["Locality"]["PostalCode"]["PostalCodeNumber"];
								arrAddress["town"] = data.Placemark[0]["AddressDetails"]["Country"]["AdministrativeArea"]["SubAdministrativeArea"]["Locality"]["LocalityName"]; 
								arrAddress["province"] = data.Placemark[0]["AddressDetails"]["Country"]["AdministrativeArea"]["SubAdministrativeArea"]["SubAdministrativeAreaName"];
								arrAddress["state"] = data.Placemark[0]["AddressDetails"]["Country"]["CountryNameCode"];

								for(var i in arrAddress) {
									var targetValue = arrAddress[i];

/*									console.log(arrAddress[i] + "  " + targetValue);	*/
									
									if($("." + googleData[id]["domPrefix"] + i).is("input")) {
										if($("." + googleData[id]["domPrefix"] + i).hasClass("gmap-exclude")) {
											$("." + googleData[id]["domPrefix"] + i).removeClass("gmap-exclude");
										} else {
											$("." + googleData[id]["domPrefix"] + i).val(targetValue);
										}
									} else {
										if($("." + googleData[id]["domPrefix"] + i + " input").length) {
											if($("." + googleData[id]["domPrefix"] + i + " input").hasClass("gmap-exclude")) {
												$("." + googleData[id]["domPrefix"] + i + " input").removeClass("gmap-exclude");
											} else {
												$("." + googleData[id]["domPrefix"] + i + " input").val(targetValue);
											}
										} else if($("." + googleData[id]["domPrefix"] + i + " select").length && $("." + googleData[id]["domPrefix"] + i + " select option:contains('" + targetValue + "')").val()) {
											if($("." + googleData[id]["domPrefix"] + i + " select").hasClass("gmap-exclude")) {
												$("." + googleData[id]["domPrefix"] + i + " select").removeClass("gmap-exclude");
											} else {
												$("." + googleData[id]["domPrefix"] + i + " select option").removeAttr("selected");
												$("." + googleData[id]["domPrefix"] + i + " select option:contains('" + targetValue + "')").attr("selected", "selected");
											}
										}
									}
								}
							}	
							document.getElementById(id + "[search]").value = data.Placemark[0].address;						
							ff.ffField.gmap.setMarker(id, point, data.Placemark[0].address, true, zoom);

						});
					} else {
						ff.ffField.gmap.setMarker(id, point, address, true, zoom);
					}
				}
			});
		}, 
		"newPoint" : function (id) {
			var point = googleData[id]["marker"].getPoint();
			googleData[id]["map"].setCenter(point);
			if(document.getElementById(id + "[lat]").value != point.lat().toFixed(6)
				|| document.getElementById(id + "[lng]").value != point.lng().toFixed(6)
			) {
				googleData[id]["geocoder"].getLocations(point, function(data) {
					document.getElementById(id + "[search]").value = data.Placemark[0].address;
					ff.ffField.gmap.setInfo(id);
				});
			} else {
				ff.ffField.gmap.setInfo(id);
			}
		}, 
		"setInfo" : function (id) {
			if (googleData[id]["marker"]) {
				point = googleData[id]["marker"].getPoint();
				document.getElementById(id + "[lat]").value = point.lat().toFixed(6);
				document.getElementById(id + "[lng]").value = point.lng().toFixed(6);
		        document.getElementById(id + "[title]").value = document.getElementById(id + "[search]").value;
				document.getElementById(id + "[zoom]").value = googleData[id]["map"].getZoom();
			}
		}, 
		"searchViaEnter" : function (evt, id, address) {
		    evt = (evt) ? evt : event;
		    var charCode = (evt.charCode) ? evt.charCode :
		        ((evt.which) ? evt.which : evt.keyCode);
		    if (charCode == 13) {
		        ff.ffField.gmap.searchAddress(id, address);
		        return false;
		    }
		    return true;
		}
	};

	return that;
	
})();