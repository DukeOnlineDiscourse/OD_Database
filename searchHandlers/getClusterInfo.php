<?php
$incPath=$_SERVER['DOCUMENT_ROOT']."/OD_Database";
require_once  $incPath."/SolrPhpClient/Apache/Solr/Service.php";
require_once  $incPath."/Kernel/core.php";
require_once  $incPath."/Kernel/solrConn.php";
require_once  $incPath."/searchHandlers/genSearch.php";

function getClustNum(){
    $clustNum=-1;
    foreach (split("&",$_SERVER['QUERY_STRING']) as $get){
        if(startsWith($get,'clust[',true)){
            $num =split(']',substr($get,6,1));
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
    $clustNum=getClustNum();
    if(sizeof($response->clusters)!=1){
	foreach($response->clusters as $clusterNum=>$cluster){
           $numDisp=0;
           $clusterName=$cluster->labels[0];
           $count=sizeof($cluster->docs);
           $docs="";
           for($i=0;$i<$count;$i++){
               $docs.="clust[".$clustNum.$cluster->labels[0]."][]=".$cluster->docs[$i]."&";
           }
           $docs= substr($docs,0,-1);
           //sloppy regex used below to make up for a greedy * quantifier. any time startresp is over 5 digits long the replace won't work.
           $queryString=preg_replace("/&startResp={.,1,5}&/","startResp=1DJ&",$_SERVER['QUERY_STRING']);
           if($count!=0){
                  $clustersDisp.="<div class='facet'><a href='genSearch?".$queryString."&".$docs."'>".$clusterName." (".$count.")</a></div>";
            }
        }
    }else{
        $clustersDisp.="<div class='facet'>No clusters generated</div>";
    }
    
    return $clustersDisp."";
}
$query=$_GET['searchTerm'];
$fq='';
if(isset ($_GET['auth'])){
    $auth=$_GET['auth'];
    $fq=decipherAuths($auth);
}
if(isset ($_GET['year'])){
    $years=$_GET['year'];
    $fq=decipherYears($years,$fq);
}
if(isset($_GET['clust'])){
    $fq=decipherClusts($_GET['clust'],$fq);
}



$options = array(
   'fl'=> '*,score',
   'facet'=>'true',
   'facet.field'=>array('authorFacet', 'sup_year'),
    'fq'=>$fq,
    'qt'=>'/clustering',
    'LingoClusteringAlgorithm.desiredClusterCountBase'=>6
);
try{
	$response = $solr->search($query, 0, 10000,$options);
	echo createClusters($response,'');
}catch(Exception $e){
	printer($query);
	printer($options);
	throw $e;
}
?>
