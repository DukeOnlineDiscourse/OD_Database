<?php
require_once $_SERVER['DOCUMENT_ROOT']."/ODDemo/SolrPhpClient/Apache/Solr/Service.php";
 $solr = new Apache_Solr_Service(
            'localhost',
            '8983',
            '/solr');

?>
