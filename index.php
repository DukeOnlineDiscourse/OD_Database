<?php

    $homeArray=array('pages/home.php',array(''),array('styles/home.css'));
    $pages = array(
        'genSearch'=>array('searchHandlers/genSearchHandler.php',
                            array("js/searchHandlers/thickbox-compressed.js", "js/searchHandlers/toolTips.min.js","js/searchHandlers/genSearchHandler.js"),
                            array("styles/searchHandlers/thickbox.css","styles/searchHandlers/search.css")),
        ''=>$homeArray,'home'=>$homeArray);

	$curPage = 'home';
	if(isset($_GET['page']))
		$curPage = $_GET['page'];

	if(array_key_exists($curPage,$pages)) {
		$mainContentFile = $pages[$curPage][0];
        $jsFiles = $pages[$curPage][1];
        $csFiles= $pages[$curPage][2];
	}else{
        header('Location: home');
    } 
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Online Discourse Search Engine</title>
        <script type="text/javascript"src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
        <?php
          foreach ($csFiles as $csFile){
                if($csFile!="")
                    echo "<link rel=\"stylesheet\" href=\"".$csFile."\" type=\"text/css\"/>";
            }

            foreach ($jsFiles as $jsFile){
                if($jsFile!="")
                    echo "<script type=\"text/javascript\" src=\"".$jsFile."\"></script>";
            }
        ?>
        
    </head>
    <body> 
        <div id="content">
            <?php 
            include $mainContentFile; ?>
        </div>
    </body>
</html>
