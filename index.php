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
        <link rel="stylesheet" href="thickbox.css" type="text/css" />
        <script type="text/javascript"src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
        <script type="text/javascript"src="thickbox-compressed.js"></script>
        <script>
        $(document).ready(function()
        {
            $('.pageLink').click( function(){
              $('#responses').children().remove();
             var startResp=window.location.hash.substring(1,2);
                for(var i =0;i<5;i++){
                    $('#responses').append(jsResponses[startResp+i]);
                }
            }
            );
          /*  function nextPage(startResp){
                alert(startResp);
                alert(jsResponses);
                alert("done");
                return false;
            }*/
        });
</script>

    </head>
    <body> 
        <div id="content">
            <? 
            include $mainContentFile; ?>
        </div>
    </body>
</html>
