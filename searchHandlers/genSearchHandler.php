<div id="header">
    <form method="get" action="genSearch" id="headerSearch" onsubmit="return validateForm()">
        <a href="/"><label for="searchTerm"><h1>Online Discourse</h1></label></a><input type="text" name="searchTerm" id="searchBox"/>
        <input type="hidden" name="startResp" value="1"/>
        <input type="hidden" name="numRows" value="5"/>
        <input type="submit" id="searchButton"/>
</form>
</div>

<?php

$incPath = $_SERVER['DOCUMENT_ROOT']."";
$facetDislayNames=array('authorFacet'=>array("Authors",'yellow','auth[]','auth'),'sup_year'=>array("Year",'green','year[]','year'));
$reverseNames =array('auth'=>'authorFacet','year'=>'sup_year');
require_once  $incPath."/searchHandlers/genSearch.php";
require_once  $incPath."/Kernel/core.php";
require_once  $incPath."/Kernel/solrConn.php";
require_once  $incPath."/Kernel/SearchResult.php";

/**
 * Parses a SOLR response and associates the hits with the id of the document they're associated with.
 * A hit is the portion of an document which the search query matches.
 *
 */
function getHighlightedSnippets($response){
    $highlights =array();
    foreach(get_object_vars($response->highlighting) as $id=>$resp){
        $hlFields=get_object_vars($resp);
        $filledInFields=array();

        foreach($hlFields as $hlField){
            $filledInFields[]=$hlField;
        }

        $highlights[$id]=$filledInFields;
    }
    return $highlights;
}

/*
 * Returns the exact URL bing requested by the client.
 * Used to create links for next apge, facets, etc.
 *
 */
function getCurURL(){
        $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https')
                    === FALSE ? 'http' : 'https'; //http://www.phpf1.com/tutorial/get-current-page-url.html
   return $protocol."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}

/*
 * Creates the page links to be displayed on the top right of the page.
 * Returns a string with a span containing all of the info.
 */
function createPageLinks($startResp,$numRows,$numResponses,$endResp){
    $curPageLinks="<span id='pageNums'> Showing responses ".($startResp)."-".$endResp." of ".$numResponses.":   ";

    $curURL= getCurURL();
    $totalPages=ceil($numResponses/$numRows);
    $curPage = ceil($startResp/$numRows);
    $first=true;

    $maxPagesToLinkBefore=3;
    $maxPagesToLinkAfter=4;

    $pageNum=max($curPage-$maxPagesToLinkBefore,1);
    $endPage=min($curPage+$maxPagesToLinkAfter,$totalPages+1);

	for($pageNum;$pageNum<$endPage;$pageNum++){
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
		$startResp=($pageNum-1)*($numRows)+1;
		$curURL= preg_replace("/startResp\=(\d+)/", "startResp=".$startResp, $curURL);
		$curPageLinks.="<a href='".$curURL."' class='".$class."'>".$pageNum." </a> ";
	}
    
    if($pageNum<$totalPages){
        $curPageLinks.=" ...";
    }
    return $curPageLinks."</span>";
}

/**
 * Facets are being created and displayed.
 * Format of facetDislayNames is solr_name=>[Human Readable, color, php name (ie year[]),hideenFacet Name ]
 */
function createFacets($response,$auths,$years,$facetDislayNames){
    //New facets should always point to the first page of results.
    $curURL=getCurURL();
    $curURL=preg_replace("/startResp\=(\d+)/","startResp=1&",$curURL);


    //Save which facets have already been chosen to avoid displaying them as an option again.
    foreach ($auths as $existFacet){
        $chosenFacets[]=$existFacet;
    }
    foreach ($years as $existFacet){
        $chosenFacets[]=$existFacet;
    }

    $facetsDisp="<div id='facets'>";

    //Loop through every facet field returned by SOLR...ie AuthorFacet->AuthorNames[]
    foreach(get_object_vars($response->facet_counts->facet_fields) as $facetName=>$facets){

	//Hidden Content is used to power the "view all facets" functionality"
       $hiddenContent.="<div id='hidden".$facetDislayNames[$facetName][3]."' style='display:none'>";

       $numDisp=0;

       //Create the div for the current facet group.
       $facetsDisp.= "<div class=\"facetGroup \"><div class='facetTitle ".$facetDislayNames[$facetName][1]."'>".$facetDislayNames[$facetName][0]."</div>";
       $hiddenContent.="<div class=\"facetGroup\">".$facetDislayNames[$facetName][0]."<br/>";

       //Loop through every facet in the current facet group 
       foreach(get_object_vars($facets) as $facet=>$count){
           //make sure the current facet is not already being used and make sure the current facet contains articles
           if((!in_array($facet,$chosenFacets)) && ($count!=0&&$facet!="_empty_")){
                   //only display the first five facets
                   $facetLink="<div class='facet'><a  href='".$curURL."&".$facetDislayNames[$facetName][2]."=".$facet."'/>".$facet." (".$count.")</a></div>";
                   $hiddenContent.=$facetLink;
                   if ($numDisp<5){
                       $facetsDisp.= $facetLink; 
                       $numDisp++;
                   }
           }
       }

      //handle Edge cases
      if($numDisp>=5)
        $facetsDisp.="<div class='facet'>><a href=\"#TB_inline?height=155&width=300&inlineId=hidden".$facetDislayNames[$facetName][3]."\" class=\"thickbox\">
             Show all ".$facetDislayNames[$facetName][0]."</a></div>";
      if($numDisp==0){
        $facetsDisp.="<div class='facet'>No more ".$facetDislayNames[$facetName][0]."</div>";
      }
        
      //Close out the divs.
       $facetsDisp.="</div>";
       $hiddenContent.="</div></div>";
    }
    return $facetsDisp.$hiddenContent;
}


/**
 * A function used to parse the request to find out what clusters are being used and thus belong in the breadcrumb (BC).
 * Example $cluster: 
	Array
	(
	    [0ICANN MoU] => Array
		(
		    [0] => 213
		    [1] => 220
		    [2] => 219
		    [3] => 215
		)
	)
 * It works by going through every cluster in $cluster and cutting off the leading digits. IE 0ICANN MoU ->ICANN MoU
 * 
 */
function getBCClust($clusters){
    $count=0;
    foreach($clusters as $clusterName=>$docs){
        $numDigits=floor($count/10)+1;
        $clustNames[]=substr($clusterName,$numDigits);
        $count++;
    }
    return $clustNames;
}

/**
 * Used to create the breadcrubm displayed at the top of the screen.
 *
 */
function createBreadCrumb($bcFac,$bcClust,$reverseNames,$facetDisplayNames){
    $bc= "<div id=\"breadCrumb\">";

    //Handle standard facets by looping through each facet type (author, year, etc) and the chosen ones.
    foreach($bcFac as $facType=>$facNames){
        //associate the php array name with display name. IE auth -> Authors
        $facetName=$reverseNames[$facType];

        $bc.="<span class='".$facetDisplayNames[$facetName][1]."'>".$facetDisplayNames[$facetName][0].": </span>";

	//Loop through each chosen facet of facType and display it.
        foreach($facNames as $facName){
            $url=str_replace($facetDisplayNames[$facetName][2]."=".str_replace(" ","%20",$facName),"",getCurURL());
            $url=preg_replace("/&+/","&",$url);
            $bc.="<span class=\"crumb ".$facetDisplayNames[$facetName][1]."\">".$facName."<a href=\"".$url."\"><span class=\"removeBox\">x</span></a></span>";
        }
    }

    //clusters neeed to be handled separately.
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

/**
 * Used to create the second header (breadcrumb, page links, etc)
 *
 */
function createSecondHeader($query, $startResp,$numRows,$numResponses,$endResp,$breadCrumbFac,$bcClust,$reverseNames,$facetDislayNames){
    $secondHeader= '<div id="secondHeader">';
        $secondHeader.= "<span id='searchTerm'>Searched for: ".$query."</span>";
        $secondHeader.= createPageLinks($startResp,$numRows,$numResponses,$endResp);
        $secondHeader.= createBreadCrumb($breadCrumbFac,$bcClust,$reverseNames,$facetDislayNames);
    return $secondHeader."</div>";
}

//initialize some variables
$query=$_GET['searchTerm'];
$startResp = $_GET['startResp'];
$numRows = $_GET['numRows'];
$fq= "db:".$_GET['db']." AND ";//used as the filter for the solr query.;

$breadCrumbFac; //used to keep track of chosen facets/clusters and create the breadcrumb
//add all of the authors to $breadCrumbFac and to $fq
if(isset ($_GET['auth'])){
    $auth=$_GET['auth'];
    $breadCrumbFac['auth']=array();
    $fq=decipherAuths($auth,$fq);
    foreach($auth as $author){
        $breadCrumbFac['auth'][]=$author;
    }
}

//add all years to $breadCrumbFac and to $fq
if(isset ($_GET['year'])){
    $years=$_GET['year'];
    $breadCrumbFac['year']=array();
    $fq=decipherYears($years,$fq);
    foreach($years as $year){
        $breadCrumbFac['year'][]=$year;
    }
}

//add all clusters to fq and create the chosen clusters list, bcClust
$bcClust=array();
if(isset($_GET['clust'])){
    $fq=decipherClusts($_GET['clust'],$fq);
    $bcClust=getBCClust($_GET['clust']);
}

//the options to be used in the solr query
$options = array(
   'fl'=> '*,score',
   'hl'=> 'on', //use highlighting
   'hl.maxAnalyzedChars'=>-1, //ananlyze as many characters as needed
   'hl.snippets'=>3,
   'hl.mergeContiguous'=>'true',
   'hl.fl'=>'attr_stream_name,authors,body,desc,subject,sup_title', //fields to highlight based on
   'facet'=>'true', //facet the results
   'facet.field'=>array('authorFacet','sup_year'), //fields to facet on
   'fq'=>$fq //a filter for the query; used for faceting/clustering
 );

try{
    $response = $solr->search($query, $startResp-1, $numRows,$options);
    $numResponses=$response->response->numFound;
    $endResp=min($startResp-1+$numRows,$numResponses);
   if($numResponses==0){
    echo "No responses found";
   }else{
	echo createSecondHeader($query, $startResp,$numRows,$numResponses,$endResp,$breadCrumbFac,$bcClust,$reverseNames,$facetDislayNames);

   echo createFacets($response,$auth,$years,$facetDislayNames);
   echo "<div id='clusters' class='facetGroup'><div class='facetTitle blue'>Dynamically Created Clusters</div><div class='facet' id='loading'> <img src='searchHandlers/loading.gif'> </div></div></div>";
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

