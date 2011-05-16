<?php
require_once $incPath."/SolrPhpClient/Apache/Solr/Service.php";
 $solr = new Apache_Solr_Service(
            'localhost',
            '8080',
            '/solr');
?>
