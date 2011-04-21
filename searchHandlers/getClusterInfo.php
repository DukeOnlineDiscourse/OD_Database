<?php
require_once '../SolrPhpClient/Apache/Solr/Service.php';
require_once '../Kernel/solrConn.php';
require_once '../Kernel/SearchResult.php';


$solr = new Apache_Solr_Service(
        'localhost',
        '8983',
        '/solr');
function printer($arr){
       echo"<pre>";
       print_r($arr);
       echo"</pre>";
}

function getCurURL(){
        $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https')
                    === FALSE ? 'http' : 'https'; //http://www.phpf1.com/tutorial/get-current-page-url.html
   return str_replace("facetChange=1","",$protocol."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
}

function createClusters($response,$url){
    $clustersDisp="";
    $clustersDisp.= "<div class='facetGroup'>Clusters <br/>";
      // echo $_SERVER['QUERY_STRING'];

    foreach($response->clusters as $clusterNum=>$cluster){
       $numDisp=0;
       $clusterName=$cluster->labels[0];

       $count=sizeof($cluster->docs);
       $docs="";
       for($i=0;$i<$count;$i++){
           $docs.=$cluster->docs[$i].",";
       }
       $docs= substr($docs,0,-1);

       if($count!=0){
               $clustersDisp.="<a class='facet' href='http://localhost:8888/ODDemo/genSearch?".$_SERVER['QUERY_STRING']."&clust=".$docs."'>".$clusterName." (".$count.")</a><br/>";
        }
    }
    
    return $clustersDisp."</div>";
}
$query=$_GET['searchTerm'];
echo $query;
if(isset($_GET['fq']))
    $fq=str_replace("\\","",$_GET['fq']);
else $fq=array();

$options = array(
   'fl'=> '*,score',
   'facet'=>'true',
   'facet.field'=>'authorFacet',
    'fq'=>$fq,
    'qt'=>'/clustering',
    'LingoClusteringAlgorithm.desiredClusterCountBase'=>6
);

if($_GET['facetChange']==1){
    $startResp=1;
}
$response = $solr->search($query, 0, 10000,$options);


echo createClusters($response,'');

?>
