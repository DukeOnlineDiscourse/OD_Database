<?php
require_once $_SERVER['DOCUMENT_ROOT']."/SolrPhpClient/Apache/Solr/Service.php";
 $solr = new Apache_Solr_Service(
            'localhost',
            '8080',
            '/solr');
?>
