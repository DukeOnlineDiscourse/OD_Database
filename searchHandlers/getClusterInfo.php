<?php
require_once '../SolrPhpClient/Apache/Solr/Service.php';
require_once '../Kernel/solrConn.php';
require_once '../Kernel/SearchResult.php';


$solr = new Apache_Solr_Service(
        'localhost',
        '8983',
        '/solr');

/*$query=$_GET['filter'].$_GET['searchTerm'];
$startResp = $_GET['startResp'];
$numRows = $_GET['numRows'];
if(isset($_GET['fq']))
    $fq=str_replace("\\","",$_GET['fq']);
else $fq=array();
*/
$options = array(
   'fl'=> '*,score',
   'hl'=> 'on',
   'hl.maxAnalyzedChars'=>-1,
   'hl.snippets'=>3,
   'hl.mergeContiguous'=>'true',
   'hl.fl'=>'attr_stream_name,authors,body,desc,subject,sup_title',
   'facet'=>'true',
   'facet.field'=>'authorFacet',
    'fq'=>'',
    'qt'=>'/clustering'
);

if($_GET['facetChange']==1){
    $startResp=1;
}
$response = $solr->search('freedom', 0, 10000,$options);
$numResponses=$response->response->numFound;

echo json_encode($numResponses);

?>
