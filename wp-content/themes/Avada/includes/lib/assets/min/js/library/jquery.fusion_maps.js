!function(a,b,c,d){"use strict";function e(b,c){this.element=b,this.settings=a.extend({},g,c),this._defaults=g,this._name=f,this.geocoder=new google.maps.Geocoder,this.next_address=0,this.infowindow=new google.maps.InfoWindow,this.markers=[],this.query_sent=!1,this.last_cache_index="none",this.bounds=new google.maps.LatLngBounds,this.init()}var f="fusion_maps",g={addresses:{},address_pin:!0,animations:!0,delay:10,infobox_background_color:!1,infobox_styling:"default",infobox_text_color:!1,map_style:"default",map_type:"roadmap",marker_icon:!1,overlay_color:!1,overlay_color_hsl:{},pan_control:!0,show_address:!0,scale_control:!0,scrollwheel:!0,zoom:9,zoom_control:!0};a.extend(e.prototype,{init:function(){var a,b,c,d={zoom:this.settings.zoom,mapTypeId:this.settings.map_type,scrollwheel:this.settings.scrollwheel,scaleControl:this.settings.scale_control,panControl:this.settings.pan_control,zoomControl:this.settings.zoom_control},e=this;this.settings.scrollwheel||(d.gestureHandling="cooperative",delete d.scrollwheel),this.settings.address_pin||(this.settings.addresses=[this.settings.addresses[0]]),jQuery.each(this.settings.addresses,function(a){!1===this.cache&&(e.last_cache_index=a)}),this.settings.addresses[0].coordinates&&(a=new google.maps.LatLng(this.settings.addresses[0].latitude,this.settings.addresses[0].longitude),d.center=a),this.map=new google.maps.Map(this.element,d),this.settings.overlay_color&&"custom"===this.settings.map_style&&(b=[{stylers:[{hue:this.settings.overlay_color},{lightness:2*this.settings.overlay_color_hsl.lum-100},{saturation:2*this.settings.overlay_color_hsl.sat-100}]},{featureType:"road",elementType:"geometry",stylers:[{visibility:"simplified"}]},{featureType:"road",elementType:"labels"}],this.map.setOptions({styles:b})),c=google.maps.event.addListener(this.map,"boundsChanged",function(){var a=new google.maps.LatLng(e.settings.addresses[0].latitude,e.settings.addresses[0].longitude);e.map.setZoom(e.settings.zoom),e.map.setCenter(a),google.maps.event.removeListener(c)}),this.next_geocode_request()},geocode_address:function(a,b){var c,d,e,f,g,h=this,i=!0;"object"==typeof a&&!1===a.cache?(i=!1,!0===a.coordinates?(c=new google.maps.LatLng(a.latitude,a.longitude),d={latLng:c}):d={address:a.address},this.geocoder.geocode(d,function(d,e){var f,g,j,k;e===google.maps.GeocoderStatus.OK?(!0===a.coordinates?(j=c,f=jQuery.trim(a.latitude),g=jQuery.trim(a.longitude)):(j=d[0].geometry.location,f=j.lat(),g=j.lng()),h.settings.addresses[b].latitude=f,h.settings.addresses[b].longitude=g,!0===a.coordinates&&""===a.infobox_content&&(a.geocoded_address=d[0].formatted_address),1!==h.next_address&&"1"!==h.next_address&&!0!==h.next_address||a.coordinates||h.map.setCenter(j),h.settings.address_pin&&h.create_marker(a,f,g,b),0!==h.next_address&&"0"!==h.next_address&&!1!==h.next_address||h.map.setCenter(j)):e===google.maps.GeocoderStatus.OVER_QUERY_LIMIT&&(h.next_address--,h.settings.delay++),!1===i&&!1===h.query_sent&&h.last_cache_index===b&&"undefined"!=typeof fusionMapNonce&&(k={action:"fusion_cache_map",addresses:h.settings.addresses,security:fusionMapNonce},jQuery.post(fusionMapsVars.admin_ajax,k),h.query_sent=!0),h.next_geocode_request()})):"object"==typeof a&&!0===a.cache&&(e=jQuery.trim(a.latitude),f=jQuery.trim(a.longitude),g=new google.maps.LatLng(e,f),!0===a.coordinates&&""===a.infobox_content&&(a.geocoded_address=a.geocoded_address),h.settings.address_pin&&h.create_marker(a,e,f,b),0!==h.next_address&&"0"!==h.next_address&&!1!==h.next_address||h.map.setCenter(g),h.next_geocode_request())},create_marker:function(a,b,c,d){var e,f,g={position:new google.maps.LatLng(b,c),map:this.map};this.bounds.extend(g.position),a.infobox_content?e=a.infobox_content:(e=a.address,!0===a.coordinates&&a.geocoded_address&&(e=a.geocoded_address)),this.settings.animations&&(g.animation=google.maps.Animation.DROP),"custom"===this.settings.map_style&&"theme"===this.settings.marker_icon?g.icon=new google.maps.MarkerImage(a.marker,null,null,null,new google.maps.Size(37,55)):"custom"===this.settings.map_style&&a.marker&&(g.icon=a.marker),f=new google.maps.Marker(g),this.markers.push(f),this.create_infowindow(e,f),d+1>=this.settings.addresses.length&&this.map.setCenter(this.bounds.getCenter()),this.map.setZoom(this.settings.zoom)},create_infowindow:function(a,b){var d,e,f,g=this;"custom"===this.settings.infobox_styling&&"custom"===this.settings.map_style?(e=c.createElement("div"),f={content:e,disableAutoPan:!0,maxWidth:150,pixelOffset:new google.maps.Size(-125,10),zIndex:null,boxStyle:{background:"none",opacity:1,width:"250px"},closeBoxMargin:"2px 2px 2px 2px",closeBoxURL:"//www.google.com/intl/en_us/mapfiles/close.gif",infoBoxClearance:new google.maps.Size(1,1)},e.className="fusion-info-box",e.style.cssText="background-color:"+this.settings.infobox_background_color+";color:"+this.settings.infobox_text_color+";",e.innerHTML=a,d=new InfoBox(f),this.settings.show_address&&d.open(this.map,b),google.maps.event.addListener(b,"click",function(){var a=d.getMap();null===a||void 0===a?d.open(g.map,this):d.close(g.map,this)})):(d=new google.maps.InfoWindow({disableAutoPan:!0,content:a}),this.settings.show_address&&(d.show=!0,d.open(this.map,b)),google.maps.event.addListener(b,"click",function(){var a=d.getMap();null===a||void 0===a?d.open(g.map,this):d.close(g.map,this)}))},next_geocode_request:function(){var a=this;a.next_address<a.settings.addresses.length&&setTimeout(function(){a.geocode_address(a.settings.addresses[a.next_address],a.next_address),a.next_address++},a.settings.delay)}}),a.fn[f]=function(b){return this.each(function(){a.data(this,"plugin_"+f)||a.data(this,"plugin_"+f,new e(this,b))}),this}}(jQuery,window,document);