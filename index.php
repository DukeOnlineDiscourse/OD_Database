<?php

    $pages = array('genSearch'=>'searchHandlers/genSearchHandler.php',''=>'pages/home.php','home'=>'pages/home.php');

	/*$curPage = 'home';*/
	if(isset($_GET['page']))
		$curPage = $_GET['page'];

	$mainContentFile = '';
	if(array_key_exists($curPage,$pages)) {
		$mainContentFile = $pages[$curPage];
	}else{
        header('Location: home');
    } 
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Online Discourse Search Engine</title>
        <link rel="stylesheet" href="style.css" type="text/css" />
        <script type="text/javascript"src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
    </head>
    <body> 
        <div id="content">
            <? 
            include $mainContentFile; ?>
        </div>
    </body>
</html>
