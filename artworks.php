<?php

$sparqlquery = '
PREFIX dct: <http://purl.org/dc/terms/>
PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
PREFIX hg: <http://rdf.histograph.io/>
PREFIX geo: <http://www.opengis.net/ont/geosparql#>
PREFIX dc: <http://purl.org/dc/elements/1.1/>
PREFIX sem: <http://semanticweb.cs.vu.nl/2009/11/sem/>
PREFIX void: <http://rdfs.org/ns/void#>
SELECT 	DISTINCT ?cho 
		(SAMPLE(?img) AS ?img) 
		(SAMPLE(?spatial) AS ?spatial) 
		(SAMPLE(?spatialname) AS ?spatialname) 
		(SAMPLE(?dataset) AS ?dataset) 
		(SAMPLE(?title) AS ?title) 
		(SAMPLE(?realbegin) AS ?begin) 
		(SAMPLE(?maker) AS ?maker) 
		(SAMPLE(?makeruri) AS ?makeruri) 
		WHERE {';
if($_GET['type']!="all"){
$sparqlquery .= '
  ?cho dc:type <http://vocab.getty.edu/aat/' . $_GET['type'] . '> .';
}
$sparqlquery .= '
  ?cho void:inDataset ?dataset .
  MINUS { ?cho void:inDataset <https://data.adamlink.nl/am/amcollect/> .}
  MINUS { ?cho void:inDataset <https://data.adamlink.nl/oba/amcat/> .}
  ?cho dct:spatial ?spatial .
  OPTIONAL {
  	?cho dc:title ?title .
  }
  OPTIONAL {
  	?cho dc:creator ?makeruri .
  	?makeruri skos:prefLabel ?maker .
  }
  ?cho foaf:depiction ?img .
  OPTIONAL{
  	?cho sem:hasBeginTimeStamp ?begin .
  	BIND(IF(COALESCE(xsd:datetime(str(?begin)), \'!\') != \'!\',
 		year(xsd:dateTime(str(?begin))),year("1005-01-01"^^xsd:dateTime)) AS ?realbegin ) .
  }
  ?spatial skos:prefLabel ?spatialname .
  FILTER (!REGEX(?wktspatial,"\\\\),\\\\("))
  ?spatial geo:hasGeometry/geo:asWKT ?wktspatial .
  bind (bif:st_geomfromtext(?wktspatial) as ?x)
  bind (bif:st_geomfromtext("POLYGON((' . $_GET['bbox'] . '))") as ?y)
  FILTER (bif:st_intersects(?x, ?y))
} 
GROUP BY ?cho ?realbegin
ORDER BY ?realbegin
LIMIT 100';

//echo $sparqlquery;


$url = "https://api.druid.datalegend.net/datasets/adamnet/all/services/endpoint/sparql?query=" . urlencode($sparqlquery) . "";

$querylink = "https://druid.datalegend.net/AdamNet/all/sparql/endpoint#query=" . urlencode($sparqlquery) . "&endpoint=https%3A%2F%2Fdruid.datalegend.net%2F_api%2Fdatasets%2FAdamNet%2Fall%2Fservices%2Fendpoint%2Fsparql&requestMethod=POST&outputFormat=table";


// Druid does not like url parameters, send accept header instead
/*
$opts = [
    "http" => [
        "method" => "GET",
        "header" => "Accept: application/sparql-results+json\r\n"
    ]
];

$context = stream_context_create($opts);

// Open the file using the HTTP headers set above
$json = file_get_contents($url, false, $context);
*/


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch,CURLOPT_USERAGENT,'RotterdamsPubliek');
$headers = [
  'Accept: application/sparql-results+json'
];
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$json = curl_exec ($ch);
curl_close ($ch);

	//var_dump($response);


$data = json_decode($json,true);

$i = 0;

$datasets=array(
	"https://data.adamlink.nl/iisg/beeldbank/" => "IISG",
	"https://data.adamlink.nl/am/amcollect/" => "AM",
	"https://data.adamlink.nl/uva/maps" => "UvA",
	"https://data.adamlink.nl/saa/beeldbank/" => "SAA",
	"https://data.adamlink.nl/rma/beeldbank/" => "RMA",
	"https://data.adamlink.nl/nharchief/beeldbank/" => "NHA",
	"https://data.adamlink.nl/oba/amcat/" => "OBA"
);

$names = array();
$checknames = array();
$checkimgs = array();
$imgs = array();

echo "<h2>" . count($data['results']['bindings']) . " resultaten</h2>";
echo '<div class="row">';

foreach ($data['results']['bindings'] as $row) { 
	$i++;
	if($i%2!=0 && $i>1){
		echo "</div>\n";
		echo '<div class="row">';
	}
	?>

	<div class="col-md-6">
		<img src="<?= $row['img']['value'] ?>">
		<strong><?= $row['title']['value'] ?></strong><br />
		<a target="_blank" href="<?= $row['spatial']['value'] ?>"><strong><?= $row['spatialname']['value'] ?></strong></a> 
		<?php if($row['begin']['value']>1005){ ?>
			omstreeks <strong><?= $row['begin']['value'] ?></strong> 
		<?php } ?>
		<?php if(isset($row['maker']['value'])){ ?>
			door <a href="<?= $row['makeruri']['value'] ?>"><strong><?= $row['maker']['value'] ?></strong></a>
		<?php } ?>
		<a target="_blank" href="<?= $row['cho']['value'] ?>">bekijk bij <strong><?= $datasets[$row['dataset']['value']] ?></strong></a>
	</div>

	<?php 
} 

if($i%2!=0){
	echo '<div class="col-md-6"></div></div>';
}else{
	echo '</div>';
}

?>

<a target="_blank" style="font-size:36px; margin: 40px 0 40px 0; display: block;" href="<?= $querylink ?>">SPARQL it yourself</a>


