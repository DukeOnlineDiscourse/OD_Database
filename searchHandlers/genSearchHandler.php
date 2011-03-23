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
     
$query=$_POST['filter'].$_POST['searchTerm'];

$start = 0;
$rows = 10;
$options = array(
  'fl' => '*,score',
); 
if(!$solr->ping())
    {
       echo "server not responding";
    }
$response = $solr->search($query, 0, 10,$options);

$responses = array();


foreach ($response->response->docs as $docNum =>$doc){

    $author=$doc->getField('author');
    $body=$doc->getField('body');
    $title=$doc->getField('attr_title');
    $id=$doc->getField('id');
    $name=$doc->getField('attr_stream_name');

    $resp = new SearchResult($author['value'],$body['value'],$title['value'],$name['value'],$id['value']);
    $responses[]=$resp;
}

foreach($responses as $resp){
  //  echo $resp->format();
    printer($resp);
    echo"<br><br><br>";
}

//printer($response->response->docs);

?>
