<?php
	date_default_timezone_set('Europe/Helsinki');
    include("myllybot.php");
 
    $bot['log'] = 1;
    $bot['debug'] = 0;
 
	/* Bot settings */
    $bot['botnick'] = "";
    $bot['botpassword'] = "";
    $bot['botident'] = "";
    $bot['botrealname'] = "";
    $bot['localhost'] = "";
 
	/* IRC server settings */
    $bot['serveraddress'] = "";
    $bot['serverport'] = "6667";
    $bot['serverchannel'] = "";
 
	/* Database settings */
    $bot['database_host'] = "";
    $bot['database_user'] = "";
    $bot['database_password'] = "";
    $bot['database_name'] = "";
	
	/* Proxy settings */
	$bot['http_proxy'] = "";
	$bot['http_proxy_port'] = "";
	$bot['http_proxy_userpwd'] = "";   // user:pwd
 
    $mybot = new MyllyBot($bot);
?>