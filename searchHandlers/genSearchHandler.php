<script>
function validateForm(){
    if($('#searchBox').val()==""){
        alert("No empty queries");
        return false
    }
    return true
}
</script>

<div id="header">
    <form method="get" action="genSearch" id="headerSearch" onsubmit="return validateForm()">
        <label for="searchTerm"><h1>Online Discourse</h1></label><input type="text" name="searchTerm" id="searchBox"/>
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

        $patterns[]="/startResp\=".$startResp."/";
        $startResp=($pageNum-1)*($numRows)+1;
        $replacements[]="startResp=".$startResp;
        $curURL= preg_replace($patterns, $replacements, $curURL);
        $curPageLinks.="<a href='".$curURL."' class='".$class."'>".$pageNum." </a> ";
    }
    return $curPageLinks;
}

function createFacets($response,$numFacets){
    $curURL=getCurURL();
    $facetsDisp="<div id='facets'>";
    $hiddenContent="<div id='hiddenFacets' style='display:none'>";
    foreach(get_object_vars($response->facet_counts->facet_fields) as $facetName=>$facets){
        $numDisp=0;
       //echo "fName:".$facetName."<br/>";
       $facetsDisp.= "<div class='facetGroup'>".$facetName."<br/>";
       $hiddenContent.="<div class='facetGroup'>".$facetName."<br/>";
       foreach(get_object_vars($facets) as $facet=>$count){
           if($count!=0){
               if ($numDisp<5){
                   $facetsDisp.="<a href='".$curURL."&fq[".(++$numFacets)."]=".$facetName.":\"".$facet."\"'/>".$facet." ".$count."</a><br/>";
                   $numDisp++;
                   $hiddenContent.="<a href='".$curURL."&fq[".($numFacets)."]=".$facetName.":\"".$facet."\"'/>".$facet." ".$count."</a><br/>";
               }else {
                     $hiddenContent.="<a href='".$curURL."&fq[".(++$numFacets)."]=".$facetName.":\"".$facet."\"'/>".$facet." ".$count."</a><br/>";
              }
           }

       }
       $facetsDisp.="</div>";
       $hiddenContent.="</div>";
    }
     $facetsDisp.="<a href=\"#TB_inline?height=155&width=300&inlineId=hiddenFacets\" class=\"thickbox\">
    Show all facets .</a></div>";
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
$fq=str_replace("\\","",$_GET['fq']);
printer($fq);
$numFacets=sizeof($fq);
echo $numFacets;
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

$response = $solr->search($query, $startResp-1, $numRows,$options);
$numResponses=$response->response->numFound;
$endResp=min($startResp-1+$numRows,$numResponses);
if($numResponses==0){
    echo "No responses found";
}else{
    echo "<div id='pageNums'> Showing responses ".($startResp)."-".$endResp." of ".$numResponses."   ".
   createPageLinks($startResp,$numRows,$numResponses).
    "</div>";

   echo createFacets($response,$numFacets);
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
        $resp = new SearchResult($snippets,$author['value'],$body['value'],$title['value'],$name['value'],$id['value']);
        $responses[]=$resp;
    }

//print out the responses
    echo "<div id='responses'>";
    foreach($responses as $resp){
       echo $resp->format();
    }
    echo "</div>";


     

     echo '<div id="other" style="display:none">
<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>
<p style="text-align:center"><input type="submit" id="Login" value="&nbsp;&nbsp;Ok&nbsp;&nbsp;" onclick="tb_remove()" /></p>
</div> ';
}


?>

<div id='hiddenFacets' style='display:none'>
    <div class='facetGroup'>authorFacet<br/>
        <a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Milton Mueller"'/>Milton Mueller 10</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"John Mathiason"'/>John Mathiason 8</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"N/A"'/>N/A 8</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"James Boyle"'/>James Boyle 4</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"BBC News"'/>BBC News 3</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Brenden Kuerbis"'/>Brenden Kuerbis 2</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"CNN"'/>CNN 2</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Declan McCullagh"'/>Declan McCullagh 2</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Derrick Cogburn"'/>Derrick Cogburn 2</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Digital Divide"'/>Digital Divide 2</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"First Monday"'/>First Monday 2</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Internet Governance Project"'/>Internet Governance Project 2</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"John Schwartz"'/>John Schwartz 2</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Morino Institute"'/>Morino Institute 2</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"United Nations E-Government Readiness Knowledge Base"'/>United Nations E-Government Readiness Knowledge Base 2</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Yochai Benkler"'/>Yochai Benkler 2</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"AOL Foundation Awards Digital Divide Bridge Grants"'/>AOL Foundation Awards Digital Divide Bridge Grants 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Adam B Jaffe"'/>Adam B Jaffe 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Amy Harmon"'/>Amy Harmon 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Anne Broache"'/>Anne Broache 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Arizona Government Information Technology Agency"'/>Arizona Government Information Technology Agency 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Ashley Packard"'/>Ashley Packard 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Associated Press"'/>Associated Press 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Beaumont Foundation of America (BFA)"'/>Beaumont Foundation of America (BFA) 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Benton Foundation"'/>Benton Foundation 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Berkman Center for Internet & Society at Harvard University"'/>Berkman Center for Internet & Society at Harvard University 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Bridge The Digital Divide"'/>Bridge The Digital Divide 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Bridges.org"'/>Bridges.org 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Bridging the digital divide"'/>Bridging the digital divide 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"CNET News"'/>CNET News 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"California Distance Learning Project"'/>California Distance Learning Project 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Challenging the Digital Divide"'/>Challenging the Digital Divide 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Childnet Academy"'/>Childnet Academy 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Christopher Maag"'/>Christopher Maag 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Comments of the Internet Governance Project: (Docket No"'/>Comments of the Internet Governance Project: (Docket No 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Committee to Democratize Information Technology"'/>Committee to Democratize Information Technology 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Communication Initiative Network"'/>Communication Initiative Network 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Computers for Africa (CFA)"'/>Computers for Africa (CFA) 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Computers for Learning"'/>Computers for Learning 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Contentbank"'/>Contentbank 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Cyberlaw Clinic"'/>Cyberlaw Clinic 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Daniel J. Solove"'/>Daniel J. Solove 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"David H Lange"'/>David H Lange 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"David S Joachim"'/>David S Joachim 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Digital Divide Network"'/>Digital Divide Network 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Digital Dividend"'/>Digital Dividend 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Digital Opportunity Channel"'/>Digital Opportunity Channel 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Eileen M Alexy"'/>Eileen M Alexy 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Ellen Nakashima"'/>Ellen Nakashima 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Elmer-DeWit"'/>Elmer-DeWit 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"F2C: Freedom to Connect"'/>F2C: Freedom to Connect 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Free Speech"'/>Free Speech 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Governance Matters"'/>Governance Matters 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Hannah"'/>Hannah 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Hans Klein"'/>Hans Klein 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Heads On Fire"'/>Heads On Fire 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Herman T Tavani"'/>Herman T Tavani 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Identity"'/>Identity 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Inc Urban Ed"'/>Inc Urban Ed 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Info Today"'/>Info Today 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Intellectual Property"'/>Intellectual Property 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"InterConnection"'/>InterConnection 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Internet control 'nears autonomy"'/>Internet control 'nears autonomy 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"James Bessen"'/>James Bessen 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"James Gooch"'/>James Gooch 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Jamie Merisotis"'/>Jamie Merisotis 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Jennifer Jenkins"'/>Jennifer Jenkins 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Jeremy Malcolm"'/>Jeremy Malcolm 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Jim Giles"'/>Jim Giles 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"John Stokes"'/>John Stokes 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Jonathan D Glater"'/>Jonathan D Glater 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Jonathan Zittrain"'/>Jonathan Zittrain 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Jukka Korpela"'/>Jukka Korpela 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Julian Dibbell"'/>Julian Dibbell 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Keith Aoki"'/>Keith Aoki 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Lee Ayers"'/>Lee Ayers 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Lee McKnight"'/>Lee McKnight 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Literacy.org"'/>Literacy.org 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"M.S. Swaminath Research Foundation"'/>M.S. Swaminath Research Foundation 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Marci Alboher Nusbaum"'/>Marci Alboher Nusbaum 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Maurice Berger"'/>Maurice Berger 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Meurer Michael James"'/>Meurer Michael James 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Michael J Albright"'/>Michael J Albright 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Michael Simonson"'/>Michael Simonson 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Michel Marriott"'/>Michel Marriott 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Mick Brady"'/>Mick Brady 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Neighborhood Networks"'/>Neighborhood Networks 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Netlingo"'/>Netlingo 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Office of E-Government & Information Technology"'/>Office of E-Government & Information Technology 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Organizing for America"'/>Organizing for America 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Paul Goldstein"'/>Paul Goldstein 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Paul Venezia"'/>Paul Venezia 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Peter H Lewis"'/>Peter H Lewis 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Phil Rockrohr"'/>Phil Rockrohr 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Philip and Block"'/>Philip and Block 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Plugged In"'/>Plugged In 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Rebecca Morelle"'/>Rebecca Morelle 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Recovery.gov"'/>Recovery.gov 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Reporters Without Borders"'/>Reporters Without Borders 1</a><br/><a href='http://localhost:8888/ODDemo/genSearch?searchTerm=*%3A*&filter=&startResp=1&numRows=5&fq=authorFacet:"Ronald Phipps"'/>Ronald Phipps 1</a>
        <br/>
    </div>
</div>