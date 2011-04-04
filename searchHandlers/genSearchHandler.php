<?php





function printer($arr){
       echo"<pre>";
       print_r($arr);
       echo"</pre>";
   }

    require_once 'SolrPhpClient/Apache/Solr/Service.php';
    require_once 'Kernel/solrConn.php';
    require_once 'Kernel/SearchResult.php';

    if(!$solr->ping())
    {
       echo "server not responding";
    }

     $solr = new Apache_Solr_Service(
            'localhost',
            '8983',
            '/solr');
     
$query=urlencode($POST['filter'].$_POST['searchTerm']);

$start = 0;
$rows = 300;
$options = array(
  'fl' => '*,score',
   'hl'=> 'on',
   'hl.maxAnalyzedChars'=>-1,
   'hl.snippets'=>5,
   'hl.mergeContiguous'=>'true',
   'hl.fl'=>'attr_stream_name,authors,body,desc,subject,sup_title'
);

if(!$solr->ping())
    {
       echo "server not responding";
    }
$response = $solr->search($query, 0, 4,$options);

$responses = array();

$highlights= array();
foreach(get_object_vars($response->highlighting) as $id=>$resp){
       //echo"ID: ";
       $highlights[$id]=array('body'=>$resp->body);
}


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




foreach($responses as $resp){
   echo$resp->format();
 /*   if(strcmp(preg_replace('/\s\s+/', '', $resp->body),"")==0)
            echo "<br/> body empty: ".$resp->id."<br>";
*/
   //printer($highlights[$resp->id]);
}



?>
