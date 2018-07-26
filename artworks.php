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


$url = "https://api.data.adamlink.nl/datasets/AdamNet/all/services/endpoint/sparql?default-graph-uri=&query=" . urlencode($sparqlquery) . "&format=application%2Fsparql-results%2Bjson&timeout=120000&debug=on";

$querylink = "https://data.adamlink.nl/AdamNet/all/services/endpoint#query=" . urlencode($sparqlquery) . "&contentTypeConstruct=text%2Fturtle&contentTypeSelect=application%2Fsparql-results%2Bjson&endpoint=https%3A%2F%2Fdata.adamlink.nl%2F_api%2Fdatasets%2Fmenno%2Falles%2Fservices%2Falles%2Fsparql&requestMethod=POST&tabTitle=Query&headers=%7B%7D&outputFormat=table";



$json = file_get_contents($url);

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


