<?php
require_once  $_SERVER['DOCUMENT_ROOT']."/ODDemo/SolrPhpClient/Apache/Solr/Service.php";
require_once  $_SERVER['DOCUMENT_ROOT']."/ODDemo/Kernel/core.php";
require_once  $_SERVER['DOCUMENT_ROOT']."/ODDemo/Kernel/solrConn.php";

require_once 'genSearch.php';


$solr = new Apache_Solr_Service(
            'localhost',
            '8983',
            '/solr'
        );



function getClustNum(){
    $clustNum=-1;
    foreach (split("&",$_SERVER['QUERY_STRING']) as $get){
        if(startsWith($get,'clust[',true)){
            $num =split(']',substr($get,6));
            if(intval($num[0])>$clustNum){
                $clustNum=intval($num[0]);
            }
        }
    }
    $clustNum=$clustNum+1;
    return $clustNum;
}

function createClusters($response,$url){
    $clustersDisp="";
    $clustersDisp.= "<div class='facetGroup'>Clusters<br/>";
    $clustNum=getClustNum();
    foreach($response->clusters as $clusterNum=>$cluster){
       $numDisp=0;
       $clusterName=$cluster->labels[0];

       $count=sizeof($cluster->docs);
       $docs="";
       for($i=0;$i<$count;$i++){
           $docs.="clust[".$clustNum."][]=".$cluster->docs[$i]."&";
       }
       $docs= substr($docs,0,-1);

       if($count!=0){
              $clustersDisp.="<a class='facet' href='http://localhost:8888/ODDemo/genSearch?".$_SERVER['QUERY_STRING']."&".$docs."'>".$clusterName." (".$count.")</a><br/>";
        }
    }
    
    return $clustersDisp."</div>";
}
$query=$_GET['searchTerm'];

if(isset ($_GET['auth'])){
    $auth=$_GET['auth'];
    $fq=decipherAuths($auth);
}
if(isset($_GET['clust'])){
    $fq=decipherClusts($_GET['clust'],$fq);
}


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
