<html lang="en-US" xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="author" content="Joey Babcock" />
    	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin=""/>
    	<script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js" integrity="sha512-GffPMF3RvMeYyc1LWMHtK8EbPv0iNZ8/oTtHPx9/cc2ILxQ+u905qIwdpULaqDkyBKgOaB57QTMg7ztg8Jm2Og==" crossorigin=""></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-ajax/2.1.0/leaflet.ajax.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="https://unpkg.com/osmtogeojson@2.2.12/osmtogeojson.js"></script>
    	<title>Scooter Locator</title>
        <style>
			body
			{
				margin:0px;	
			}
			.info 
			{ 
				padding: 6px 8px; 
				font: 1em/24px Arial, Helvetica, sans-serif; 
				background: white; 
				background: rgba(255,255,255,0.8); 
				box-shadow: 0 0 15px rgba(0,0,0,0.2); 
				border-radius: 5px; 
			} 		
			.info h4 
			{ 
				margin: 0 0 5px; 
				color: #777; 
			}
			.legend 
			{ 
				text-align: left; 
				line-height: 20px; 
				color: #555; 
			} 
			.legend i 
			{ 
				width: 18px; 
				height: 17px; 
				float: left; 
				margin-right: 8px; 
				opacity: 0.7; 
			}
		</style>
    </head>
    <body>
   	<div id="map" style="height: 100%;"></div>
        <script>
	        var scooterData;
	        var mapBuildings;
			var buildingData;
			let scooters = [];
			var info;
			var legend;
			var map;
			let stations = [];
			
			var station1 = 'B6E350CC';
			var station2 = '02A4AE30';
			
			var building1coords = [33.12902, -117.26039];
			var building2coords = [33.12822, -117.25725];
			var defaultCoords   = [33.12862, -117.25932];
			
			function getColor(station)
			{
				if(stations[station] != undefined && parseInt(stations[station].howlong) > 500)
				{
					return "#ff0000";
				}
				else
				{
					return "#00ff00";
				}
			}
			
			function style(feature) {
				numCases = feature.properties.name;
				return {
					fillColor: getColor(feature.properties.station),
					weight: 2,
					opacity: 1,
					color: 'white',
					dashArray: '3',
					fillOpacity: 0.7
				};
			}
			
			function highlightFeature(e) {
				var layer = e.target;
		
				layer.setStyle({
					weight: 5,
					color: '#666',
					dashArray: '',
					fillOpacity: 0.7
				});
		
				if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
					layer.bringToFront();
				}
		
				info.update(layer.feature.properties);
			}
			
			function resetHighlight(e) {
				mapBuildings.resetStyle(e.target);
				info.update();
			}
			
			function getCountScooters(stationid)
			{
				return stations[stationid].count;
			}
		
			function zoomToFeature(e) {
				map.fitBounds(e.target.getBounds());
			}
		
			function onEachFeature(feature, layer) {
				layer.bindPopup("<b>"+feature.properties.name+"</b><br />"+feature.properties['addr:housenumber']+" " + feature.properties['addr:street'] +".<br/>Station ID: "+feature.properties.station).openPopup();
				layer.on({
					mouseover: highlightFeature,
					mouseout: resetHighlight,
					click: zoomToFeature
				});
				stations[feature.properties.station]['popup'] = layer._popup;
				stations[feature.properties.station]['feature'] = feature;
			}
			
			buildingData = $.ajax({
				url: "buildings.geojson",
				dataType: "json",
				success: console.log("Buildings successfully loaded."),
				error: function(xhr) {
					alert(xhr.statusText)
				}
			});
			
			L.icon = function (options) {
			    return new L.Icon(options);
			};
			
			var ScooterIcon = L.Icon.extend({
			    options: {
			        iconSize:     [32,32]
			    }
			});
			
			var scootIcon1 = new ScooterIcon({iconUrl: 'icons/icon-1.png'});
			var scootIcon2 = new ScooterIcon({iconUrl: 'icons/icon-2.png'});
			var scootIcon3 = new ScooterIcon({iconUrl: 'icons/icon-3.png'});
			var scootIcon4 = new ScooterIcon({iconUrl: 'icons/icon-4.png'});
			var scootIcon = new ScooterIcon({iconUrl: 'icons/scooter-icon.png'});
			var scootIconOld = new ScooterIcon({iconUrl: 'icons/scooter-icon-old.png'});
			
			$.when(buildingData).done(function() {
				map = L.map('map', {
					center: [33.12837, -117.25860],
					minZoom: 17,
					maxZoom: 19,
					zoom: 18,
					fullscreenControl: true
				});
				
				googleSat = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}',{
				    maxZoom: 20,
				    subdomains:['mt0','mt1','mt2','mt3']
				});
				
				googleSat.addTo(map);
				
				// control that shows state info on hover
				info = L.control();
				
				info.onAdd = function(map) {
					this._div = L.DomUtil.create('div', 'info');
					console.log("added");
					this.update();
					return this._div;
				};
			
				info.update = function(props) {
					this._div.innerHTML = '<h4>Scooters</h4>' +  (props ?
						'<b>' + props.name + '</b><br />' +getCountScooters(props.station) + ' scooters<br/>'+props['addr:housenumber']+" " + props['addr:street'] +".<br/>Station ID: "+props.station
						: 'Hover over a building');
				};
				
				info.addTo(map);
				
				var ts = new Date().getTime();
				
				scooterData = $.getJSON("getscooters.php?_ts="+ts, function( data ) {
					$.each(data.stations, function(i, item) {
						stations[item.station_id] = item;
						stations[item.station_id].count = 0;
					});
					$.each(data.scooters, function(i, item) {
						var old = true;
						var show = false;
						if(item.howlong < 3600)
						{
							old = false;
						}
						
						if(item.howlong < 86400)
						{
							scooters[item.mac] = item;
							scooters[item.mac]['last_station_id'] = item.last_station_id;
							scooters[item.mac]['last_checked_in'] = item.last_checked_in;
							scooters[item.mac]['lastseen'] = item.lastseen;
							scooters[item.mac]['howlong'] = item.howlong;
							if(item.number == 1)
						    {
							    var sicon = scootIcon1;
							    if(old)
							    {
								    sicon = scootIconOld;
							    }
							    switch (item.last_station_id) {
								  case station1:
								    scooters[item.mac].marker = L.marker(building1coords, {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station1].count++;
								    break;
								  case station2:
								    scooters[item.mac].marker = L.marker(building2coords, {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station2].count++;
								    break;
								  default:
								  	scooters[item.mac].marker = L.marker(defaultCoords, {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								}
						    }
						    else if(item.number == 2)
						    {
							    var sicon = scootIcon2;
							    if(old)
							    {
								    sicon = scootIconOld;
							    }
							    switch (item.last_station_id) {
								  case station1:
								    scooters[item.mac].marker = L.marker(building1coords.map(x => x + 0.0002), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station1].count++;
								    break;
								  case station2:
								    scooters[item.mac].marker = L.marker(building2coords.map(x => x + 0.0002), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station2].count++;
								    break;
								  default:
								  	scooters[item.mac].marker = L.marker(defaultCoords.map(x => x + 0.0002), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								} 
						    }
						    else if(item.number == 3)
						    {
							    var sicon = scootIcon3;
							    if(old)
							    {
								    sicon = scootIconOld;
							    }
							    switch (item.last_station_id) {
								  case station1:
								    scooters[item.mac].marker = L.marker(building1coords.map(x => x + 0.0004), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station1].count++;
								    break;
								  case station2:
								    scooters[item.mac].marker = L.marker(building2coords.map(x => x + 0.0004), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station2].count++;
								    break;
								  default:
								  	scooters[item.mac].marker = L.marker(defaultCoords.map(x => x + 0.0004), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								} 
						    }
						    else if(item.number == 4)
						    {
							    var sicon = scootIcon4;
							    if(old)
							    {
								    sicon = scootIconOld;
							    }
							    switch (item.last_station_id) {
								  case station1:
								    scooters[item.mac].marker = L.marker(building1coords.map(x => x + 0.0006), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station1].count++;
								    break;
								  case station2:
								    scooters[item.mac].marker = L.marker(building2coords.map(x => x + 0.0006), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station2].count++;
								    break;
								  default:
								  	scooters[item.mac].marker = L.marker(defaultCoords.map(x => x + 0.0006), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								} 
						    }
						    else
						    {
							    var sicon = scootIcon;
							    if(old)
							    {
								    sicon = scootIconOld;
							    }
							    switch (item.last_station_id) {
								  case station1:
								    scooters[item.mac].marker = L.marker(building1coords.map(x => x + 0.0008), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station1].count++;
								    break;
								  case station2:
								    scooters[item.mac].marker = L.marker(building2coords.map(x => x + 0.0008), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station2].count++;
								    break;
								  default:
								  	scooters[item.mac].marker = L.marker(defaultCoords.map(x => x + 0.0008), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								} 
						    }
						}
					});
				});
				$.when(scooterData).done(function() {
					mapBuildings = L.geoJSON(buildingData.responseJSON, 
					{
						style: style, 
						onEachFeature: onEachFeature, 
						filter: function(feature, layer) 
						{
							stations[feature.properties.station].feature = feature;
							return feature.geometry.type != "Point";
						}
					})
					.addTo(map);
				});
				
				map.attributionControl.addAttribution('&copy; <a href="https://joeybabcock.me">Joeybabcock.me</a>');

			});
		</script>
		<script>
			
			function updateScooters()
			{
				stations[station1].count = 0;
				stations[station2].count = 0;
				var ts = new Date().getTime();
				var newAjax = $.getJSON("getscooters.php?_ts="+ts, function( data ) {
					$.each(data.stations, function(i, item) {
						var oldPopup = stations[item.station_id].popup;
						var oldFeature = stations[item.station_id].feature;
						stations[item.station_id] = item;
						stations[item.station_id].count = 0;
						stations[item.station_id].popup = oldPopup;
						stations[item.station_id].feature = oldFeature;
						stations[item.station_id].popup._content = "<b>"+stations[item.station_id].feature.properties.name+"</b><br />"+stations[item.station_id].feature.properties['addr:housenumber']+" " + stations[item.station_id].feature.properties['addr:street'] +".<br/>"+"Station ID: "+item.station_id;
					});
					$.each(data.scooters, function(i, item) {
						var old = true;
						var show = false;
						if(item.howlong < 3600)
						{
							old = false;
						}
						
						if(item.howlong < 86400)
						{
							map.removeLayer(scooters[item.mac].marker);
							scooters[item.mac] = item;
							scooters[item.mac]['last_station_id'] = item.last_station_id;
							scooters[item.mac]['last_checked_in'] = item.last_checked_in;
							scooters[item.mac]['lastseen'] = item.lastseen;
							scooters[item.mac]['howlong'] = item.howlong;
							
							if(item.number == 1)
						    {
							    var sicon = scootIcon1;
							    if(old)
							    {
								    sicon = scootIconOld;
							    }
							    switch (item.last_station_id) {
								  case station1:
								    scooters[item.mac].marker = L.marker(building1coords, {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station1].count++;
								    break;
								  case station2:
								    scooters[item.mac].marker = L.marker(building2coords, {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station2].count++;
								    break;
								  default:
								  	scooters[item.mac].marker = L.marker(defaultCoords, {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								}
						    }
						    else if(item.number == 2)
						    {
							    var sicon = scootIcon2;
							    if(old)
							    {
								    sicon = scootIconOld;
							    }
							    switch (item.last_station_id) {
								  case station1:
								    scooters[item.mac].marker = L.marker(building1coords.map(x => x + 0.0002), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station1].count++;
								    break;
								  case station2:
								    scooters[item.mac].marker = L.marker(building2coords.map(x => x + 0.0002), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station2].count++;
								    break;
								  default:
								  	scooters[item.mac].marker = L.marker(defaultCoords.map(x => x + 0.0002), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								} 
						    }
						    else if(item.number == 3)
						    {
							    var sicon = scootIcon3;
							    if(old)
							    {
								    sicon = scootIconOld;
							    }
							    switch (item.last_station_id) {
								  case station1:
								    scooters[item.mac].marker = L.marker(building1coords.map(x => x + 0.0004), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station1].count++;
								    break;
								  case station2:
								    scooters[item.mac].marker = L.marker(building2coords.map(x => x + 0.0004), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station2].count++;
								    break;
								  default:
								  	scooters[item.mac].marker = L.marker(defaultCoords.map(x => x + 0.0004), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								} 
						    }
						    else if(item.number == 4)
						    {
							    var sicon = scootIcon4;
							    if(old)
							    {
								    sicon = scootIconOld;
							    }
							    switch (item.last_station_id) {
								  case station1:
								    scooters[item.mac].marker = L.marker(building1coords.map(x => x + 0.0006), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station1].count++;
								    break;
								  case station2:
								    scooters[item.mac].marker = L.marker(building2coords.map(x => x + 0.0006), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station2].count++;
								    break;
								  default:
								  	scooters[item.mac].marker = L.marker(defaultCoords.map(x => x + 0.0006), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								} 
						    }
						    else
						    {
							    var sicon = scootIcon;
							    if(old)
							    {
								    sicon = scootIconOld;
							    }
							    switch (item.last_station_id) {
								  case station1:
								    scooters[item.mac].marker = L.marker(building1coords.map(x => x + 0.0008), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station1].count++;
								    break;
								  case station2:
								    scooters[item.mac].marker = L.marker(building2coords.map(x => x + 0.0008), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								    stations[station2].count++;
								    break;
								  default:
								  	scooters[item.mac].marker = L.marker(defaultCoords.map(x => x + 0.0008), {icon: sicon}).addTo(map).bindPopup("<b>"+item.name+"</b><br />"+item.lastseen+".");
								} 
						    }
						}
					});
				});
			}
			
			var timer = setInterval(updateScooters, 5000);
        </script>
        <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" media="all">
        <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
		<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />
    </body>
</html>