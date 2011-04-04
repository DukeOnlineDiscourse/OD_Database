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

$query='id:*';

$start = 0;
$rows = 300;
$options = array(
  'fl' => '*,score',
);
if(!$solr->ping())
    {
       echo "server not responding";
    }
$response = $solr->search($query, 0, 300,$options);

$responses = array();


foreach ($response->response->docs as $docNum =>$doc){

    $author=$doc->getField('authors');
    $body=$doc->getField('body');
    $title=$doc->getField('sup_title');
    $id=$doc->getField('id');
    $name=$doc->getField('attr_stream_name');

    $resp = new SearchResult($author['value'],$body['value'],$title['value'],$name['value'],$id['value']);
    $responses[]=$resp;
    echo $id['value'].",";
}

$postedIds=array();
$con = mysql_connect('localhost:8888', 'root', 'root');
    if (!$con)
      {
      die('Could not connect: ' . mysql_error());
      }
    mysql_select_db("newOnlineDiscourse", $con);
foreach($responses as $resp){
    $query="UPDATE `cd_file_table` SET `ignore`=0 WHERE `id`=".$resp->id;
  //  echo "<br>".$query."<br>";
    mysql_query($query) or die(mysql_error());
    $postedIds[]=$resp->id;
}

//echo $query;


/*$query="SELECT *
FROM  `cd_file_table`
WHERE  `ignore` =0
AND  `fail` =0";
$con = mysql_connect('localhost:8888', 'root', 'root');
    if (!$con)
      {
      die('Could not connect: ' . mysql_error());
      }
    mysql_select_db("newOnlineDiscourse", $con);
$result = mysql_query($query)or die(mysql_error());
$tableIds=array();//expected, haystack
while($row = mysql_fetch_assoc($result)) {
    $tableIds[]=$row['id'];
}
$count=0;
foreach($tableIds as $expectedId){
    if(!in_array($expectedId,$postedIds)){
        $count++;
        $query="UPDATE `cd_file_table` SET `ignore`=1 WHERE `id`=".$expectedId;
        mysql_query($query);
        echo $expectedId." "."<br>";

    }
}
    echo "count:".$count;
*/
?>