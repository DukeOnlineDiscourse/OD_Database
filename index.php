<?php
require_once  $_SERVER['DOCUMENT_ROOT']."/ODDemo/Kernel/core.php";

session_start();
    $homeArray=array('pages/home.php',array(''),array('styles/home.css'));
    $pages = array(
        'genSearch'=>array('searchHandlers/genSearchHandler.php',
                            array("js/searchHandlers/thickbox-compressed.js", "js/searchHandlers/toolTips.min.js","js/searchHandlers/genSearchHandler.js"),
                            array("styles/searchHandlers/thickbox.css","styles/searchHandlers/search.css")),
        'home'=>$homeArray,
        'login'=>array('pages/login.php',
                        array('pages/login.js'),
                        array()
                ),
        'logout'=>array('pages/logout.php',
                        array(),
                        array(),
                )
       );

	/*$curPage = 'home';*/
	if(isset($_GET['page']))
		$curPage = $_GET['page'];

	if(array_key_exists($curPage,$pages)) {
        if(!($_SESSION['login']==1 && $_SESSION['auth']==1)&&$curPage!='login'){
            $_SESSION['url']=$curPage;
            header('Location: login');
        }
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
        <?
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
            <? 
            include $mainContentFile; ?>
        </div>
    </body>
</html>
