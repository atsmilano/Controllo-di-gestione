ff.ffField.gmap3 = (function () {
	var googleData	=  {};
	var geocoder;
	var marker;
	var infowindow;
    var mapID;
	var arrAddress = {};
	var responses = {};
	
	var that = { 
		/* publics*/
		__ff : true, /* used to recognize ff'objects*/

		"init" : function (key, sensor, region)
		{
			ff.ffField.gmap3.loadMaps(true); 
		},
		"addData": function(data) 
		{
			if(!googleData[data.id])
				googleData[data.id] = { "data" : data, "loaded" : false};
		},
		"getInst" : function (id) {
			return googleData[id];
		},		
		/*"pinGeocodePosition" : function (pos) {
			geocoder.geocode({
			  "location": pos
			}, function(responses) {
			  if (responses && responses.length > 0) {
				responses[0].formatted_address;
			  } else {
				'Cannot determine address at this location.';
			  }
			});
		  },*/
		"addPin" : function (idmap, lat, lng, draggable, image, info, icon) {
			if (googleData[idmap]["pins"] === undefined) {
				googleData[idmap]["pins"] = ff.hash();
			}
			
			var idmarker = lat.toString() + "|" + lng.toString();
			
			if (!googleData[idmap]["pins"].isset(idmarker)) {
				var marker_data = {
						"map": googleData[idmap]["map"],
						"position": new google.maps.LatLng(lat, lng),
						"draggable": draggable
					};

				if (image !== undefined) {
					marker_data.image = image;
				}
				
				if (icon !== undefined) {
					marker_data.icon = icon;
				}
				
				var pin = new google.maps.Marker(marker_data);
				var rc = googleData[idmap]["pins"].set(idmarker, pin);
		
				if (info) {
					var infow = new google.maps.InfoWindow({
						"content": info
					});

					google.maps.event.addListener(pin, 'click', function() {
						infow.open(googleData[idmap]["map"], pin);
					});
				}
				
				return rc;
			}
		},
		"loadMaps" : function(reload) {
			for(var i in googleData) {
				if(reload || !googleData[i]["loaded"]) {
					if(!jQuery("#" + i + "\\[map\\]").height() < 100) {
						jQuery("#" + i + "\\[map\\]").height(350);
					}

					googleData[i]["id"] = i + "[map]";	
									
					if(googleData[i]["data"]["personalized_style"])
						mapID = 'custom_style';
					else
						 mapID = google.maps.MapTypeId[googleData[i]["data"]["mapTypeId"]];

					googleData[i]["map"] = new google.maps.Map(document.getElementById(i + "[map]"), { 
						zoom		: googleData[i]["data"]["zoom"] ,
						center		: new google.maps.LatLng(googleData[i]["data"]["lat"], googleData[i]["data"]["lng"]) ,
						mapTypeId	: mapID,
						image		: googleData[i]["data"]["image"] ,
						zoomControl	: googleData[i]["data"]["zoom_control"] ,
						zoomControlOptions: {
							style: google.maps.ZoomControlStyle[googleData[i]["data"]["zoom_control_options"]["style"]],
							position: google.maps.ControlPosition[googleData[i]["data"]["zoom_control_options"]["position"]] 
						},
						mapTypeControl: googleData[i]["data"]["map_type_control"],
						mapTypeControlOptions: {
							style: google.maps.MapTypeControlStyle[googleData[i]["data"]["map_type_control_options"]["style"]]
						},	 
						panControl: googleData[i]["data"]["pan_control"] ,
						panControlOptions: {
							position: google.maps.ControlPosition[googleData[i]["data"]["pan_control_options"]["position"]]
						},
						scaleControl: googleData[i]["data"]["scale_control"] ,
						scaleControlOptions: {
							position: google.maps.ControlPosition.LEFT_CENTER /*[googleData[i]["data"]["scale_control_options"]["position"]]*/
						},
						streetViewControl: googleData[i]["data"]["street_view_control"] ,
						streetViewControlOptions: {
							position: google.maps.ControlPosition[googleData[i]["data"]["street_view_control_options"]["position"]]
						}
					});
					if(googleData[i]["data"]["personalized_style"])
					{
					   var styledMapOptions = {
						   name: 'Custom Style'
					   };
					   var customMapType = new google.maps.StyledMapType(googleData[i]["data"]["text_style"], styledMapOptions);
					   googleData[i]["map"].mapTypes.set(mapID, customMapType);
					}
					if(googleData[i]["data"]["image"]) {
						googleData[i]["marker"] = new google.maps.Marker({
							map: googleData[i]["map"],
							position: new google.maps.LatLng(googleData[i]["data"]["lat"], googleData[i]["data"]["lng"]),
							draggable:true,
							icon: image
						});
					} else if(!googleData[i]["data"]["nomarker"]) {
						googleData[i]["marker"] = new google.maps.Marker({
							map: googleData[i]["map"],
							position: new google.maps.LatLng(googleData[i]["data"]["lat"], googleData[i]["data"]["lng"]),
							draggable:true
						});
					}

					googleData[i]["loaded"] = true;
					geocoder = new google.maps.Geocoder();

					if(googleData[i]["data"]["prefixClass"]) {
						googleData[i]["domPrefix"] = googleData[i]["data"]["prefixClass"];
					}
					if(googleData[i]["data"]["updateClass"]) 
					{
						var arrUpdateClass = googleData[i]["data"]["updateClass"].split(",");
						if(arrUpdateClass.length > 0) {
							arrUpdateClass.each(function(j, classValue) {
								var target = "";
								var targetAction = "";

								if($("." + classValue).is("input")) {
									target = "." . classValue;
									targetAction = "blur";
								} else {
									if($("." + classValue + " input").length) {
										target = "." + classValue + " input";
										targetAction = "blur";
									} else if($("." + classValue + " select").length) {
										target = "." + classValue + " select";
										targetAction = "change";
									} else {
										if($("." + classValue).length > 0) {
											target = "." + classValue + " select";
											targetAction = "change";
										}
									}
								}

								if(target) {
									$(document).on(targetAction, target,function (e){
										if($(e.target).val()) {
											$(e.target).addClass("gmap-exclude");
											var newAddress = ff.ffField.gmap3.getAddressByClass(googleData[i]["data"]["updateClass"]);
											ff.ffField.gmap3.codeAddress(newAddress, googleData[i]);


										}
									});
								}
							});

/*							if(newAddress
								&& document.getElementById(data.id + "[search]").value != newAddress
							) {
								data.forceSearch = true;
								document.getElementById(data.id + "[search]").value = newAddress;
							}
*/						}
					}
					
					if (googleData[i]["marker"] !== undefined) {
						infowindow = new google.maps.InfoWindow({
							content: googleData[i]["marker"].getPosition().formatted_address
						});

						google.maps.event.addListener(googleData[i]["marker"], 'dragend', function() {
							var id = jQuery(this.map.getDiv()).attr("id").replace("[map]", "");
							ff.ffField.gmap3.geocodePosition(id, this, googleData[id]);					
						});
						google.maps.event.addListener(googleData[i]["map"], 'zoom_changed', function() {
							var id = jQuery(this.getDiv()).attr("id").replace("[map]", "");
							ff.ffField.gmap3.geocodePosition(id, googleData[id]["marker"], googleData[id]);						
							//ff.ffField.gmap3.geocodePosition(i, marker, googleData[i]);
						});
						google.maps.event.addListener(googleData[i]["map"], 'mouseover', function() {
							var id = jQuery(this.getDiv()).attr("id").replace("[map]", "");
							if(1 || !googleData[id]["centered"]) {
								google.maps.event.trigger(this, 'resize'); 
								/*googleData[i]["map"].setZoom( googleData[i]["map"].getZoom());*/
								this.setCenter(googleData[id]["marker"].getPosition());
								googleData[id]["centered"] = true;
							}
						});
					}
					
/*					google.maps.event.addListener(marker, 'click', function() 
					{
						infowindow.open(googleData[i]["map"], marker);
					});
*/				
					that.doEvent({
						"event_name" : "loadMap",
						"event_params"	: [i, googleData[i]]
					});
					/*
					ff.pluginAddInit("ff.ffPage.dialog", function () {
						ff.ffPage.dialog.addEvent({
							"event_name"	: "onDisplayedDialog",
							"func_name"	: function(id) {
								if(document.getElementById(i + '[search]'))
									ff.ffField.gmap3.codeAddress(document.getElementById(i + '[search]').value, i + '[map]', true);
							}
						});					
					});
					
					ff.pluginAddInit("ff.ffPage.tabs", function () {
						ff.ffPage.tabs.addEvent({
							"event_name"	: "onActivate",
							"func_name"		: function (id, event, ui) {
								if(document.getElementById(i + '[search]'))
									ff.ffField.gmap3.codeAddress(document.getElementById(i + '[search]').value, i + '[map]', true);
							}
						});
					});	*/				
				}
			}
		},
		"codeAddress": function(address, map, skip) {
			if((map["domPrefix"] !== undefined && map["domPrefix"].length > 0) || skip !== undefined) {
				for(var i in googleData) {
                    if(document.getElementById(i + "[search]"))
						document.getElementById(i + "[search]").value = address;
                    if(document.getElementById(i + "[title]"))
						document.getElementById(i + "[title]").value = address;

					if(!googleData[i]["centered"]) {
						google.maps.event.trigger(googleData[i]["map"], 'resize'); 
						googleData[i]["centered"] = true;
					}				
					
					geocoder.geocode({ 'address': address}, function(results, status) {
						if (status == google.maps.GeocoderStatus.OK) {
							googleData[i]["marker"].setPosition(results[0].geometry.location);
							ff.ffField.gmap3.geocodePosition(i, googleData[i]["marker"], map);
						}
					});
				}
			}
		},
		"getAddressByClass": function(updateClass) {
			var arrUpdateClass = updateClass.split(",");
			var newAddress = "";

			arrUpdateClass.each(function(i, classValue) {
				var targetValue = "";

				if($("." + classValue).is("input")) {
					targetValue = $("." + classValue).val();
				} else {
					if($("." + classValue + " input").length) {
						targetValue = $("." + classValue + " input").val();
					} else if($("." + classValue + " select").length && $("." + classValue + " select option:selected").val()) {
						targetValue = $("." + classValue + " select option:selected").text();
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
		"setInfo" : function (id, title, lat, lng, zoom) {
			
			document.getElementById(id + "[lat]").value = lat.toFixed(6);
			document.getElementById(id + "[lng]").value = lng.toFixed(6);
			document.getElementById(id + "[title]").value = title;
			document.getElementById(id + "[zoom]").value = zoom;
		},
		"geocodePosition": function (id, marker, map) {
			
			geocoder.geocode({latLng: marker.getPosition()}, function(responses) 
			{
				if (responses && responses.length > 0) 
				{
					
					arrAddress = { "address" : ""
										, "cap" : ""
										, "town" : ""
										, "province" : ""
										, "state" : ""
									};
					var route = "";
					responses[0]["address_components"].each(function(i, addressComponent) {
						
						switch(addressComponent["types"][0])
						{
							case "street_number":
								route = addressComponent["long_name"];
								break;
							case "route":
								route = addressComponent["long_name"] + " " + route;
								break;
							case "locality":
								arrAddress["town"] = addressComponent["long_name"];
								break;
							case "administrative_area_level_3":
								arrAddress["town"] = addressComponent["long_name"];
								break;
							case "administrative_area_level_2":
								arrAddress["province"] = addressComponent["short_name"];
								break;
							case "administrative_area_level_1":
								break;
							case "country":
								arrAddress["state"] = addressComponent["short_name"];
						/*		$("select option").filter(function() {
									return $(this).text().indexOf('(' + responses[0]["address_components"][i]["short_name"] + ')') !== -1; 
								}).prop('selected', true);
						*/		
								break;
							case "postal_code":
								arrAddress["cap"] = addressComponent["long_name"];
								break;
						}
					});
					arrAddress["address"] = route;
					
				/*	arrAddress["state"] = $("select option").find('option:selected').text();*/
					var address_formatted = responses[0].formatted_address;
					
					document.getElementById(id + "[search]").value = address_formatted;
					
					ff.ffField.gmap3.searchResult(id, arrAddress, marker, map, address_formatted);
					
					
				}
			});
		},
		"searchResult": function(id, arrAddress, marker, map, address_formatted){
			var address = "";
			for(var i in arrAddress) 
			{
				var targetValue = arrAddress[i];
				
				if(address && targetValue)
					address += ",";
				if($("." + map["domPrefix"] + i).is("input")) {
					
					if($("." + map["domPrefix"] + i).hasClass("gmap-exclude")) {
						$("." + map["domPrefix"] + i).removeClass("gmap-exclude");
					} else {
						$("." + map["domPrefix"] + i).val(targetValue);
					}
					address += $("." + map["domPrefix"] + i).val();
				} else {
					if($("." + map["domPrefix"] + i + " input").length) {
						if($("." + map["domPrefix"] + i + " input").hasClass("gmap-exclude")) {
							$("." + map["domPrefix"] + i + " input").removeClass("gmap-exclude");
						} else {
							$("." + map["domPrefix"] + i + " input").val(targetValue);
						}
						address += $("." + map["domPrefix"] + i + " input").val();
					} else if($("." + map["domPrefix"] + i + " select").length && $("." + map["domPrefix"] + i + " select option:contains('" + targetValue + "')").val()) {
						if($("." + map["domPrefix"] + i + " select").hasClass("gmap-exclude")) {
							$("." + map["domPrefix"] + i + " select").removeClass("gmap-exclude");
						} else {
							$("." + map["domPrefix"] + i + " select option").removeAttr("selected");
							$("." + map["domPrefix"] + i + " select option:contains('" + targetValue + "')").attr("selected", "selected");
						}
						address += $("." + map["domPrefix"] + i + " select option:selected").text();
					} 
				}
			}
			
			if(address === "")
			{
				address = address_formatted;
			}

			if(!googleData[id]["centered"]) {
				google.maps.event.trigger(googleData[id]["map"], 'resize'); /*suitable for V3*/
				googleData[id]["centered"] = true;
			}
			
			marker.setTitle(address);
			marker.getMap().setCenter(marker.getPosition());
			
			ff.ffField.gmap3.setInfo(id, address, marker.getPosition().lat(), marker.getPosition().lng(), marker.getMap().getZoom());
		}
	};
	return that;
})();
