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
        <input type="hidden" name="startResp" value="0"/>
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

$options = array(
   'fl'=> '*,score',
   'hl'=> 'on',
   'hl.maxAnalyzedChars'=>-1,
   'hl.snippets'=>3,
   'hl.mergeContiguous'=>'true',
   'hl.fl'=>'attr_stream_name,authors,body,desc,subject,sup_title'
);

$response = $solr->search($query, $startResp, $numRows,$options);

$numResponses=$response->response->numFound;
$endResp=min($startResp+$numRows,$numResponses);
if($numResponses==0){
    echo "No responses found";
}else{

    echo "<div> Showing responses ".($startResp+1)."-".$endResp." of ".$numResponses."</div>";

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
    foreach($responses as $resp){
       echo$resp->format();
     /*   if(strcmp(preg_replace('/\s\s+/', '', $resp->body),"")==0)
                echo "<br/> body empty: ".$resp->id."<br>";
    */
    }
}


?>
