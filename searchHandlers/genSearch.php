<?php

$facetDislayNames=array('authorFacet'=>array("Authors",'yellow','auth[]','auth'),'sup_year'=>array("Years",'green','year[]','year'));
function decipherAuths($auth){
    $first=true;
    $fq='';
    for($i=0;$i<sizeof($auth);$i++){
        if(!$first){
            $fq=$fq." AND authorFacet:\"".$auth[$i]."\"";
        }else{
            $fq.="(authorFacet:\"".$auth[$i]."\"";
            $first=false;
        }
    }
    $fq.=")";
    return $fq;
}

function decipherYears($years, $fq){
    $first=true;
    if($fq!='')
        $fq.=" AND ";

    for($i=0;$i<sizeof($years);$i++){
        if(!$first){
            $fq=$fq." AND sup_year:\"".$years[$i]."\"";
        }else{
            $fq.=" (sup_year:\"".$years[$i]."\"";
            $first=false;
        }
    }
    $fq.=")";
    return $fq;
}

function decipherClusts($clusts,$fq){
    foreach($clusts as $clust){
        if($fq!="")
            $fq.=" AND ";
        $first =true;
        for($i=0;$i<sizeof($clust);$i++){
            if(!$first)
                $fq.="OR id:\"".$clust[$i]."\"";
            else{
                $fq.="(id:\"".$clust[$i]."\"";
                $first=false;
            }
        }
        $fq.=")";
    }
    return $fq;
}


?>
