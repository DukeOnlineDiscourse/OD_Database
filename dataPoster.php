<?php

function startsWith($haystack,$needle,$case=true) {
    if($case){return (strcmp(substr($haystack, 0, strlen($needle)),$needle)===0);}
    return (strcasecmp(substr($haystack, 0, strlen($needle)),$needle)===0);
}

function printer($arr){
   echo"<pre>";
   print_r($arr);
   echo"</pre>";
}


$con = mysql_connect('localhost:8888', 'root', 'root');
    if (!$con)
      {
      die('Could not connect: ' . mysql_error());
      }
    mysql_select_db("newOnlineDiscourse", $con);


$query = "SELECT * FROM cd_file_table WHERE `ignore`=0";

$result = mysql_query($query);
if (!$result) {
    die('Invalid query: ' .$query." ". mysql_error());
}
$count=0;
while($row = mysql_fetch_assoc($result)) {
    $source = urlencode("http://".$row['filename']);
    $desc="literal.desc=".$row['description'];
    $title =urlencode($row['title']);
    $authors=$row['authors'];
    //need to split authors
       $id = $row['id'];

    $year = $row['year'];
    $authors=split("#",$authors);
    $authFin = array();
    foreach($authors as $author){
        $author=split(',',$author);
        if (count($author)>1){
            $authFin[]=trim($author[1])." ".trim(($author[0]));
        }else
            $authFin[]=trim($author[0]);
    }

    $url="http://localhost:8983/solr/update/extract?";
    $params="uprefix=attr_&literal.id=".$id."&literal.sup_title=".$title."&wt=json";
    foreach($authFin as $author){
        echo $id."auth:".$author."END<br>";
        if($author=="")
            $author="N/A";
        $params.="&literal.authors=".urlencode($author);
    }
    $params.="&stream.url=".$source;

    $url.=$params;
    $ch = curl_init($url);

   curl_setopt($ch,CURLOPT_POSTFIELDS, $desc);
   curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

    $jsonResp;
    $resp=curl_exec($ch);
    $success=true;
    if(strpos($resp,"java.net.UnknownHostException:")!=false) {
        $success=false;
    }else{
        if($resp!=false){
            $jsonResp=json_decode($resp);
            if($jsonResp->responseHeader->status!=0){
                 $success=false;
            }
            else {
                $count++;
                //echo $id.", ";
           }
        }else
            $success=false;
    }
    if (!$success){
        echo "fail: ".$id."<br>";
        $query="Update cd_file_table
            SET `fail`=1
            WHERE id=".$id;
        mysql_query($query);
    }
   curl_close($ch);

}

echo "<br>".$count;

?>