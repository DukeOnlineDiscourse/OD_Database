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

?>
