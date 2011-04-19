<?php
require_once '../SolrPhpClient/Apache/Solr/Service.php';
require_once '../Kernel/solrConn.php';
require_once '../Kernel/SearchResult.php';


$solr = new Apache_Solr_Service(
        'localhost',
        '8983',
        '/solr');

function createClusters($response){
    $clustersDisp="";
    $clustersDisp.= "<div class='facetGroup'>Clusters <br/>";

    foreach($response->clusters as $clusterNum=>$cluster){
       $numDisp=0;
       $clusterName=$cluster->labels[0];
       $count=sizeof($cluster->docs);
       if($count!=0){
               $clustersDisp.="<a class='facet' href='foo'>".$clusterName." (".$count.")</a><br/>";
        }
    }
    
    return $clustersDisp."</div>";
}

/*$query=$_GET['filter'].$_GET['searchTerm'];
$startResp = $_GET['startResp'];
$numRows = $_GET['numRows'];
if(isset($_GET['fq']))
    $fq=str_replace("\\","",$_GET['fq']);
else $fq=array();
*/
$options = array(
   'fl'=> '*,score',
   'hl'=> 'on',
   'hl.maxAnalyzedChars'=>-1,
   'hl.snippets'=>3,
   'hl.mergeContiguous'=>'true',
   'hl.fl'=>'attr_stream_name,authors,body,desc,subject,sup_title',
   'facet'=>'true',
   'facet.field'=>'authorFacet',
    'fq'=>'',
    'qt'=>'/clustering',
    'LingoClusteringAlgorithm.desiredClusterCountBase'=>6
);

if($_GET['facetChange']==1){
    $startResp=1;
}
$response = $solr->search('freedom', 0, 10000,$options);
$numResponses=$response->response->numFound;


echo createClusters($response);

?>
