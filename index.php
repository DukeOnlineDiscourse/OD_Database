<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->



<?php

    $pages = array('genSearch'=>'searchHandlers/genSearchHandler.php');

	$curPage = 'home';
	if(isset($_GET['page']) && array_key_exists($_GET['page'],$pages))
		$curPage = $_GET['page'];

	$mainContentFile = 'pages/home.php';
	if(array_key_exists($curPage,$pages)) {
		$mainContentFile = $pages[$curPage];
	}


   
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Online Discourse Search Engine</title>
        <link rel="stylesheet" href="style.css" type="text/css" />
    </head>
    <body>
        <div id="content">
            <? include $mainContentFile; ?>
        </div>
    </body>
</html>
