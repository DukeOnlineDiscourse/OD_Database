<?php
session_start();

ob_start();
/*
 Below is a very simple and verbose PHP 5 script that implements the Engage token URL processing and some popular Pro/Enterprise examples.
 The code below assumes you have the CURL HTTP fetching library with SSL.
*/

//For a production script it would be better to include the apiKey in from a file outside the web root to enhance security.
$rpx_api_key = '187f4037aa6b683bfd1a78414c3c1acf49baf340';

/*
 Set this to true if your application is Pro or Enterprise.
 Set this to false if your application is Basic or Plus.
*/
$engage_pro = false;

/* STEP 1: Extract token POST parameter */
$token = $_POST['token'];


if(strlen($token) == 40) {//test the length of the token; it should be 40 characters

  /* STEP 2: Use the token to make the auth_info API call */
  $post_data = array('token'  => $token,
                     'apiKey' => $rpx_api_key,
                     'format' => 'json',
                     'extended' => 'true'); //Extended is not available to Basic.

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_URL, 'https://rpxnow.com/api/v2/auth_info');
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_FAILONERROR, true);
  $result = curl_exec($curl);
  if ($result == false){
    echo "\n".'Curl error: ' . curl_error($curl);
    echo "\n".'HTTP code: ' . curl_errno($curl);
    echo "\n"; var_dump($post_data);
  }
  curl_close($curl);


  /* STEP 3: Parse the JSON auth_info response */
  $auth_info = json_decode($result, true);

  if ($auth_info['stat'] == 'ok') {
    /*echo "\n auth_info:";
    echo "\n"; var_dump($auth_info);*/


    /* STEP 4: Use the identifier as the unique key to sign the user into your system.
       This will depend on your website implementation, and you should add your own
       code here. The user profile is in $auth_info.
    */
      //session_start();
      $_SESSION['login']=1;
      $_SESSION['auth']=1;
      $url=$_SESSION['url'];
      $_SESSION['url']='';
      header('Location: '.$url);
    } else {
      // Gracefully handle auth_info error.  Hook this into your native error handling system.
      echo "\n".'An error occured: ' . $auth_info['err']['msg']."\n";
      var_dump($auth_info);
      echo "\n";
      var_dump($result);
    }
}else{
  // Gracefully handle the missing or malformed token.  Hook this into your native error handling system.
  echo 'Authentication canceled.';
}
$debug_out = ob_get_contents();
ob_end_clean();
?>
<html>
<head>
<title>Janrain Engage example</title>
</head>
<body>
<!-- content -->
<pre>
<?php echo $debug_out; ?>
</pre>
<!-- javascript -->
</body>
</html>