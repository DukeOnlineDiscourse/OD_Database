<?php
$incPath = $_SERVER['DOCUMENT_ROOT']."";
require_once  $incPath."/Kernel/core.php";
//print_r($_FILES);
//Check for solr instance being on
$filename=$_FILES['fileName']['tmp_name'];

$online=$_POST['online'];
$handle=fopen($filename, "r");
$contents = fread($handle, filesize($filename));
$contents=explode("http",$contents);

foreach($contents as $row){
    if($row==""){
        echo "empty row<br><br>";
        continue;
    }
    $row=explode("|",$row);
    $title=urlencode($row[1]);
    $source=urlencode("http".trim($row[0]));
    $desc=urlencode($row[5]);
    $year=urlencode($row[3]);
    $tempAuths=substr($row[2],1,-1);
    $tempAuths=explode(";",$tempAuths);
    $db=trim($row[9]);
if($db==""){
$db="od";
}
    $authors="";
    foreach($tempAuths as $author){
        $author=explode(",",$author);
        $authors[]=$author[1]." ".$author[0];
    }

    $url="http://localhost:8080/solr/update/extract?";

    $params="uprefix=attr_"."&literal.sup_title=".$title."&wt=json&literal.id=".$source."&literal.sup_title=".$title."&wt=json&literal.sup_year=".$year."&literal.db=".$db;
    foreach($authors as $author){
        $params.="&literal.authors=".urlencode($author);
    }
    $params.="&".$online."=".$source;

    $url.=$params;

    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $desc);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

     $resp=curl_exec($ch);
     handle($resp,$url);
   curl_close($ch);
   break;
}

commit();


function handle($resp,$url){
  if($resp==false && strpos($resp,"java.net.UnknownHostException:")!=false && json_decode($resp)->responseHeader->status!=0  ) {
        echo "Failed: ".$url."<br>";
   }else
       echo "Success: ".$url."<br>";
}

function commit(){
    $commit="<commit></commit>";

$url="http://localhost:8080/solr/update/?commit=true";

    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_POSTFIELDS, "<commit></commit>");
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

     $resp=curl_exec($ch);
    handle($resp,$url);
       curl_close($ch);


}
?>
