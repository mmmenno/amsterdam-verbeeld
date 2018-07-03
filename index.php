<!DOCTYPE html>
<html>
<head>
	
	<title>De Verbeelde Stad</title>

	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link href="https://fonts.googleapis.com/css?family=Nunito:300,700" rel="stylesheet">

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

	<script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.1.0/dist/leaflet.css" integrity="sha512-wcw6ts8Anuw10Mzh9Ytw4pylW8+NAD4ch3lqm9lzAsTxg0GFeJgoAtxuCLREZSC5lUXdVyo/7yfsqFjQ4S+aKw==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.1.0/dist/leaflet.js" integrity="sha512-mNqn2Wg7tSToJhvHcqfzLMU6J4mkOImSPTxVZAdo+lcPlk+GhZmYgACEe0x35K7YzW1zJ7XyJV/TT1MrdXvMcA==" crossorigin=""></script>


	<style>
		html, body{
			height: 100%;
			margin:0;
			font-family: 'Nunito', sans-serif;
		}
		#parameterbar{
			background-color: #000;
			padding-top: 40px;
			padding-bottom: 20px;
			color: #fff;
		}
		h1{
			margin-top: 0;
		}
		#map {
			width: 100%;
			height: 250px;
			margin-bottom: 20px;
		}
		.leaflet-left .leaflet-control{
			margin-top: 10px;
			margin-left: 10px;
		}
		.leaflet-container .leaflet-control-attribution{
			color: #000;
		}
		.leaflet-control-attribution a{
			color: #000;
		}
		.leaflet-touch .leaflet-control-layers, .leaflet-touch .leaflet-bar{
			border: 2px solid #000;
		}
		.btn-primary{
			margin-top: 20px;
			background-color: #fff;
			color: #000;
			font-size: 20px;
			border-color: #fff;
		}
		.btn-primary:hover{
			background-color: #BC000C;
			color: #fff;
			border-color: #fff;
		}
		button:focus, input:focus, .btn-primary:focus {
			outline:0;
			background-color: #fff;
			color: #000;
			border-color: #fff;
		}
		label{
			font-weight: 300;
			margin-left: 16px;
		}
		a, a:visited, a:hover, select{
			text-decoration: none;
			color: #000;
		}
		a:hover{
			text-decoration: underline;
		}
		#album img{
			width: 100%;
			margin-top: 20px;
		}
	</style>

	
</head>
<body>



<div class="container-fluid" id="parameterbar">
	<div class="col-md-4">
		<h1>De Verbeelde Stad</h1>
		<p>
			Zoom uit of in op een stukje stad om er afbeeldingen bij op te halen. Standaard krijg je alle typen afbeeldingen door elkaar, maar je kan ook alleen schilderijen, tekeningen, etc. opvragen.
		</p>
		<p>
			We geven maximaal 100 resultaten weer, in dat geval kan je altijd verder inzoomen.
		</p>
	</div>
	<div class="col-md-4">
		<div id='map'></div>
	</div>
	<div class="col-md-4">

		<?php /*
		<input checked="checked" type="radio" value="300033973" name="techniek" id="tekeningen" /> 
		<label for="tekeningen">tekeningen</label><br />

		<input type="radio" value="300177435" name="techniek" id="schilderijen" /> 
		<label for="schilderijen">schilderijen</label><br />

		<input type="radio" value="300127131" name="techniek" id="kabinetfoto" /> 
		<label for="kabinetfoto">kabinetfoto's</label><br />

		<input type="radio" value="300027221" name="techniek" id="affiches" /> 
		<label for="affiches">affiches</label><br />

		<input type="radio" value="300041273" name="techniek" id="prenten" /> 
		<label for="prenten">prenten</label><br />

		<input type="radio" value="300041340" name="techniek" id="gravures" /> 
		<label for="gravures">gravures</label><br />

		<input type="radio" value="300162872" name="techniek" id="stereo" /> 
		<label for="stereo">stereofoto's</label><br />

		<input type="radio" value="300026819" name="techniek" id="ansichtkaarten" /> 
		<label for="ansichtkaarten">ansichtkaarten</label><br />

		<input type="radio" value="300015578" name="techniek" id="illustraties" /> 
		<label for="illustraties">illustraties</label><br />

		<input type="radio" value="300028051" name="techniek" id="boeken" /> 
		<label for="boeken">boeken</label><br />

		

		<a id="locate" style="color: #fff; cursor: pointer;">breng kaart naar mijn positie</a><br /><br />
		*/ ?>
		
		<select name="typering" class="form-control">
			<option value="all">alle typen afbeeldingen</option>
			<option value="300033973">tekeningen</option>
			<option value="300177435">schilderijen</option>
			<option value="300127131">kabinetfoto's</option>
			<option value="300027221">affiches</option>
			<option value="300041273">prenten</option>
			<option value="300041340">gravures</option>
			<option value="300162872">stereofoto's</option>
			<option value="300026819">ansichtkaarten</option>
			<option value="300015578">illustraties</option>
			<option value="300028051">boeken</option>		
		</select>


		
		<button id="knop" class="btn btn-primary">zoek</button>
	</div>
</div>

<div id="album" class="container-fluid">

</div>



<script>

	var params = window.location.hash.substr(1);
	qstring = decodeURIComponent( params );
	var parsed = function( qstring ) {
	    var params = {}, queries, temp, i, l;
	    // Split into key/value pairs
	    queries = qstring.split("&");
	    // Convert the array of strings into an object
	    for ( i = 0, l = queries.length; i < l; i++ ) {
	        temp = queries[i].split('=');
	        params[temp[0]] = temp[1];
	    }
	    return params;
	};
	params = parsed(qstring);

	if(params!=""){
		if(typeof(params['center']) != "undefined") {
			var str = params['center'].replace('LatLng(','').replace(')','').replace(' ','');
			var latlon = str.split(",");
		    var center = [parseFloat(latlon[0]),parseFloat(latlon[1])];
		}else{
			var center = [52.359716,4.900029];
		}
		if(typeof(params['zoom']) != "undefined") {
			var zoomlevel = parseInt(params['zoom']);
		}else{
			var zoomlevel = 15;
		}
		if(typeof(params['type']) != "undefined") {
			$("select[name='typering']").val(params['type']);
		}

	}

	var map = L.map('map', {
        center: center,
        zoom: zoomlevel,
        minZoom: 6,
        maxZoom: 20,
        scrollWheelZoom: false
    });

	L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/toner/{z}/{x}/{y}{r}.{ext}', {
	    attribution: 'Tiles <a href="http://stamen.com">Stamen Design</a> - Data &copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
	    subdomains: 'abcd',
		minZoom: 0,
		maxZoom: 20,
		ext: 'png'
	}).addTo(map);

	L.control.attribution({position: 'bottomleft'}).addTo(map);

	$('#knop').click(function(){
		getimages();
	});

	function sethash(){
		var hash = {};
		hash['type'] = $("select[name='typering']").val();
		hash['center'] = map.getCenter();
		hash['zoom'] = map.getZoom();
		location.hash = $.param(hash,true);
	}

	function getimages(){
		var parameters = {};
		var bounds = map.getBounds();
		parameters['bbox'] = bounds['_southWest']['lng'] + ' ' + bounds['_northEast']['lat'];
		parameters['bbox'] += ',' + bounds['_northEast']['lng'] + ' ' + bounds['_northEast']['lat'];
		parameters['bbox'] += ',' + bounds['_northEast']['lng'] + ' ' + bounds['_southWest']['lat'];
		parameters['bbox'] += ',' + bounds['_southWest']['lng'] + ' ' + bounds['_southWest']['lat'];
		parameters['bbox'] += ',' + bounds['_southWest']['lng'] + ' ' + bounds['_northEast']['lat'];
		
		//parameters['type'] = $("input[name='techniek']:checked").val();
		parameters['type'] = $("select[name='typering']").val();
		//console.log(parameters);

		sethash();

		var params = $.param(parameters,true);
		var worksurl = 'artworks.php?' + params;
		
		$('#album').load(worksurl);
	}

	$('#locate').click(function() {
      	map.locate({setView: true, maxZoom: 17});
    })

	$(document).ready(function(){
		var check = window.location.hash.substr(1);
		if(check!=""){
			getimages();
		}
		
	});

</script>



</body>
</html>
