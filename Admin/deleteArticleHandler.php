<?php

$incPath = $_SERVER['DOCUMENT_ROOT']."";
require_once  $incPath."/Kernel/core.php";


$id=trim($_GET['id']);
$msg =urlencode("<delete><id>".$id."</id></delete>");
$url="http://localhost:8080/solr/update/?stream.body=".$msg;
$ch = curl_init($url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

 $resp=curl_exec($ch);
handle($resp,$url);
   curl_close($ch);

   commit();



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

function handle($resp,$url){
    printer($resp);
  if($resp==false && strpos($resp,"java.net.UnknownHostException:")!=false && json_decode($resp)->responseHeader->status!=0  ) {
        echo "Failed: ".urldecode($url)."<br>";
   }else
       echo "Success: ".urldecode($url)."<br>";
}
?>
