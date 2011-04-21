<div id="header">
    <form method="get" action="genSearch" id="headerSearch" onsubmit="return validateForm()">
        <a href="/ODDemo"><label for="searchTerm"><h1>Online Discourse</h1></label></a><input type="text" name="searchTerm" id="searchBox"/>
        <input type="hidden" name="startResp" value="1"/>
        <input type="hidden" name="numRows" value="5"/>
        <input type="submit" name="Search" value="Search" id="searchButton"/>
</form>
</div>

<?php

function printer($arr){
       echo"<pre>";
       print_r($arr);
       echo"</pre>";
}

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
   return str_replace("facetChange=1","",$protocol."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
}

function createPageLinks($startResp,$numRows,$numResponses){

    $curURL= getCurURL();
    //searchTerm=id%3A69&filter=&startResp=0&numRows=5&Search=Search

    $totalPages=ceil($numResponses/$numRows);
    $curPage = ceil($startResp/$numRows);

    $maxPagesToLinkBefore=3;
    $maxPagesToLinkAfter=4;
    $curPageLinks="";

for($pageNum=$curPage-$maxPagesToLinkBefore;$pageNum<($curPage+$maxPagesToLinkAfter);$pageNum++){
    if($pageNum<=0){
        continue;
    }else if($pageNum>$totalPages){
        break;
    }

    if($pageNum==$curPage){
        $class='curPage';
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
    return $curPageLinks;
}

function createFacets($response,$auths){
    $curURL=getCurURL();
    $numFacets=sizeof($fq);
    $chosenFacets=array();
    foreach ($auths as $existFacet){
        $chosenFacets[]=$existFacet;
    }

    $facetsDisp="<div id='facets'>";
    $hiddenContent="<div id='hiddenFacets' style='display:none'>";
    foreach(get_object_vars($response->facet_counts->facet_fields) as $facetName=>$facets){
        $numDisp=0;
       $facetsDisp.= "<div class='facetGroup'>".$facetName."<br/>";
       $hiddenContent.="<div class='facetGroup'>".$facetName."<br/>";
       foreach(get_object_vars($facets) as $facet=>$count){
           if(!in_array($facet,$chosenFacets)){
               if($count!=0){
                   if ($numDisp<5){
                       $facetsDisp.="<a class='facet' href='".$curURL."&auth[]=".$facet."&facetChange=1'/>".$facet." (".$count.")</a><br/>";
                       $numDisp++;
                       $hiddenContent.="<a class='facet' href='".$curURL."&auth[]=".$facet."&facetChange=1'/>".$facet." (".$count.")</a><br/>";
                   }else {
                         $hiddenContent.="<a class='facet' href='".$curURL."&auth[]=".$facet."&facetChange=1'/>".$facet." (".$count.")</a><br/>";
                  }
               }
           }
       }
       $facetsDisp.="</div>";
       $hiddenContent.="</div>";
    }
     $facetsDisp.="<a href=\"#TB_inline?height=155&width=300&inlineId=hiddenFacets\" class=\"thickbox\">
    Show all facets</a></div>";
    return $facetsDisp.$hiddenContent."</div>";
}

require_once 'SolrPhpClient/Apache/Solr/Service.php';
require_once 'Kernel/solrConn.php';
require_once 'Kernel/SearchResult.php';


 $solr = new Apache_Solr_Service(
        'localhost',
        '8983',
        '/solr');

$query=$_GET['filter'].$_GET['searchTerm'];
$startResp = $_GET['startResp'];
$numRows = $_GET['numRows'];

$auth=array();
if(isset($_GET['auth'])){
    $first=true;
    $auth=$_GET['auth'];
    $fq='';
    for($i=0;$i<sizeof($auth);$i++){
        if(!$first){
            $fq=$fq." AND "."authorFacet:\"".$auth[$i]."\"";
        }else{
            $fq.="authorFacet:\"".$auth[$i]."\"";
            $first=false;
        }
    }
}

if(isset($_GET['clust'])){
    $clust = split($_GET['clust'], ',');
    for($i=0;$i<sizeof($clust);$i++){
        $fq[] = "id:".$clust[$i];
    }
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

if($_GET['facetChange']==1){
    $startResp=1;
}
$response = $solr->search($query, $startResp-1, $numRows,$options);
$numResponses=$response->response->numFound;
$endResp=min($startResp-1+$numRows,$numResponses);
if($numResponses==0){
    echo "No responses found";
}else{
    echo "<div id='pageNums'> Showing responses ".($startResp)."-".$endResp." of ".$numResponses."   ".
   createPageLinks($startResp,$numRows,$numResponses).
    "</div>";

   echo createFacets($response,$auth);
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
    foreach($responses as $resp){
       echo $resp->format();
    }
    echo "</div>";
}


?>

