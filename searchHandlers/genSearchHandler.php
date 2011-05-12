<div id="header">
    <form method="get" action="genSearch" id="headerSearch" onsubmit="return validateForm()">
        <a href="/"><label for="searchTerm"><h1>Online Discourse</h1></label></a><input type="text" name="searchTerm" id="searchBox"/>
        <input type="hidden" name="startResp" value="1"/>
        <input type="hidden" name="numRows" value="5"/>
        <input type="submit" name="Search" value="Search" id="searchButton"/>
</form>
</div>

<?php
require_once  $_SERVER['DOCUMENT_ROOT']."/searchHandlers/genSearch.php";
require_once  $_SERVER['DOCUMENT_ROOT']."/Kernel/core.php";
require_once  $_SERVER['DOCUMENT_ROOT']."/Kernel/solrConn.php";
require_once  $_SERVER['DOCUMENT_ROOT']."/Kernel/SearchResult.php";

function getHighlightedSnippets($response){
    $highlights =array();
    foreach(get_object_vars($response->highlighting) as $id=>$resp){
           //echo"ID: ";
        $hlFields=get_object_vars($resp);
        $filledInFields=array();

        foreach($hlFields as $hlField){
            $filledInFields[]=$hlField;
        }

        $highlights[$id]=$filledInFields;
    }
    return $highlights;
}

function getCurURL(){
        $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https')
                    === FALSE ? 'http' : 'https'; //http://www.phpf1.com/tutorial/get-current-page-url.html
   return $protocol."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}

function createPageLinks($startResp,$numRows,$numResponses){

    $curURL= getCurURL();
    //searchTerm=id%3A69&filter=&startResp=0&numRows=5&Search=Search

    $totalPages=ceil($numResponses/$numRows);
    $curPage = ceil($startResp/$numRows);

    $maxPagesToLinkBefore=3;
    $maxPagesToLinkAfter=4;
    $curPageLinks="";

    $first=true;
for($pageNum=$curPage-$maxPagesToLinkBefore;$pageNum<($curPage+$maxPagesToLinkAfter);$pageNum++){
    if($pageNum<=0){
        continue;
    }else if($pageNum>$totalPages){
        break;
    }
    if($first){
        $first=false;
        if($pageNum>1){
            $curPageLinks.="... ";
        }
    }
    if($pageNum==$curPage){
        $class='curPage\' onclick="return false"';
    }else{
        $class="";
    } 
        $patterns=array();
        $replacements=array();

        $patterns[]="/startResp\=(\d+)/";
        $startResp=($pageNum-1)*($numRows)+1;
    //    echo "<br> Start Resp page".$startResp;
        $replacements[]="startResp=".$startResp;
    //    echo "<br>".$curURL;
        $curURL= preg_replace($patterns, $replacements, $curURL);
        $curPageLinks.="<a href='".$curURL."' class='".$class."'>".$pageNum." </a> ";
    }
    
    if($pageNum<$totalPages){
        $curPageLinks.=" ...";
    }
    return $curPageLinks;
}

function createFacets($response,$auths){
    $facetDislayNames=array('authorFacet'=>"Authors");
    $curURL=getCurURL();
    $curURL=preg_replace("/startResp\=(\d+)/","startResp=1&",$curURL);
    $chosenFacets=array();
    foreach ($auths as $existFacet){
        $chosenFacets[]=$existFacet;
    }

    $facetsDisp="<div id='facets'>";
    foreach(get_object_vars($response->facet_counts->facet_fields) as $facetName=>$facets){
       $hiddenContent="<div id='hidden".$facetName."' style='display:none'>";

       $numDisp=0;
       $facetsDisp.= "<div class=\"facetGroup \"><div class='facetTitle yellow'>".$facetDislayNames[$facetName]."</div>";
       $hiddenContent.="<div class=\"facetGroup\">".$facetDislayNames[$facetName]."<br/>";
       foreach(get_object_vars($facets) as $facet=>$count){
           if(!in_array($facet,$chosenFacets)){
               if($count!=0){
                   if ($numDisp<5){
                       $facetsDisp.="<div class='facet'><a  href='".$curURL."&auth[]=".$facet."'/>".$facet." (".$count.")</a></div>";
                       $numDisp++;
                       $hiddenContent.="<div class='facet'><a  href='".$curURL."&auth[]=".$facet."'/>".$facet." (".$count.")</a></div>";
                   }else {
                         $hiddenContent.="<div class='facet'><a  href='".$curURL."&auth[]=".$facet."'/>".$facet." (".$count.")</a></div>";
                  }
               }
           }
       }
      if($numDisp>=5)
        $facetsDisp.="<div class='facet'>><a href=\"#TB_inline?height=155&width=300&inlineId=hidden".$facetName."\" class=\"thickbox\">
             Show all ".$facetDislayNames[$facetName]."</a></div>";
      if($numDisp==0){
        $facetsDisp.="<div class='facet'>No more ".$facetDislayNames[$facetName]."</div>";
      }
        $facetsDisp.="</div>";
       $hiddenContent.="</div></div>";
    }
    return $facetsDisp.$hiddenContent;
}

function getBCClust($clusters){
    $clustNames=array();
    $count=0;
    foreach($clusters as $clusterName=>$docs){
        $numDigits=floor($count/10)+1;
        $clustNames[]=substr($clusterName,$numDigits);
        $count++;
    }
    return $clustNames;
}

function createBreadCrumb($bcFac,$bcClust){
    $bc= "<div id=\"breadCrumb\">";

    if(sizeof($bcFac)>0)
    $bc.="<span class='yellow'>Authors: </span>";
    for($i=0;$i<sizeof($bcFac);$i++){
        $crumb=$bcFac[$i];
        $url=str_replace("auth[]=".str_replace(" ","%20",$crumb),"",getCurURL());
        $url=preg_replace("/&+/","&",$url);
        $bc.="<span class=\"crumb yellow\">".$crumb."<a href=\"".$url."\"><span class=\"removeBox\">x</span></a></span>";
    }
    $bc.="";

    if(sizeof($bcClust)>0){
        $bc.="<span class='blue'>Clusters: </span>";
    }
    for($i=0;$i<sizeof($bcClust);$i++){
        $crumb=$bcClust[$i];
        $class="crumb blue";
        if($i!=sizeof($bcClust)-1){
            $class="bcCrumb crumb blue";
        }

        $url=preg_replace("/clust\[\d+".str_replace(" ","%20",$crumb)."\]\[\]=\d+/","",getCurURL());
        $url=preg_replace("/&+/","&",$url);
        $bc.="<span class=\"".$class."\">".$crumb."<a href=\"".$url."\"><span class=\"removeBox\">x</span>
                </a></span>
                    <div class=\"tooltip\">Please note that all subsequent clusters contain only a subset of the documents in this one.
                        Thus, removing this facet will not alter search results.</div>";
    }
    $bc.="</div>";
    return $bc;
}
$filter='';$searchTerm='';$fq='';
if (isset($_GET['filter'])){
	$filter=$_GET['filter'];
}
if(isset($_GET['searchTerm'])){
	$searchTerm=$_GET['searchTerm'];
}
$query=$filter.$searchTerm;
$startResp = $_GET['startResp'];
$numRows = $_GET['numRows'];

$auth=array();
$breadCrumbFac=array();
if(isset ($_GET['auth'])){
    $auth=$_GET['auth'];
    $fq=decipherAuths($auth);
    foreach($auth as $author){
        $breadCrumbFac[]=$author;
    }
}

$bcClust=array();
if(isset($_GET['clust'])){
    $fq=decipherClusts($_GET['clust'],$fq);
    $bcClust=getBCClust($_GET['clust']);

}

$options = array(
   'fl'=> '*,score',
   'hl'=> 'on',
   'hl.maxAnalyzedChars'=>-1,
   'hl.snippets'=>3,
   'hl.mergeContiguous'=>'true',
   'hl.fl'=>'attr_stream_name,authors,body,desc,subject,sup_title',
   'facet'=>'true',
   'facet.field'=>'authorFacet',
    'fq'=>$fq
 );

try{
    $response = $solr->search($query, $startResp-1, $numRows,$options);
$numResponses=$response->response->numFound;
$endResp=min($startResp-1+$numRows,$numResponses);
if($numResponses==0){
    echo "No responses found";
}else{
    echo '<div id="secondHeader">';
        echo "<span id='searchTerm'>Searched for: ".$query."</span>";
        echo "<span id='pageNums'> Showing responses ".($startResp)."-".$endResp." of ".$numResponses.":   ".
            createPageLinks($startResp,$numRows,$numResponses)."</span>";
        echo createBreadCrumb($breadCrumbFac,$bcClust);
    echo "</div>";

   echo createFacets($response,$auth);
   echo "<div id='clusters' class='facetGroup'><div class='facetTitle blue'>Dynamically Created Clusters</div><div class='facet' id='loading'> <img src='/searchHandlers/loading.gif'> </div></div></div>";
    $responses = array();
    $highlights= array();
    $highlights = getHighlightedSnippets($response);
//Create a SearchResult Object out of each response
    foreach ($response->response->docs as $docNum =>$doc){
        $author=$doc->getField('authors');
        $body=$doc->getField('body');
        $title=$doc->getField('sup_title');
        $id=$doc->getField('id');
        $name=$doc->getField('attr_stream_name');
        $snippets=$highlights[$id['value']];
        $desc=$doc->getField('desc');
        $resp = new SearchResult($snippets,$author['value'],$body['value'],$title['value'],$name['value'],$desc['value'],$id['value']);
        $responses[]=$resp;
    }

//print out the responses
    echo "<div id='responses'>";
        for($i=0;$i<sizeof($responses);$i++){
            if($i!=sizeof($responses)-1)
                echo $responses[$i]->format();
            else
                echo $responses[$i]->format("last");
        }
    echo "</div>";
}}
catch(Exception $e){
    echo "Error in search syntax: ".$query;
}


?>

