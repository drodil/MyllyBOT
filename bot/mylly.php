<?php
    include("myllybot.php");
    include("conf_inc.php");
 
    /* Set these values only when there is no install
     * script run and there is no conf_inc.php available */
     
    if(!isset($bot))
    {
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
        $bot['serverchannel'] = array("#channel1", "#channel2"); //or just a "#singlechannel"
     
        /* Database settings */
        $cfg['database_host'] = "";
        $cfg['database_user'] = "";
        $cfg['database_password'] = "";
        $cfg['database_name'] = "";
        
        /* Proxy settings */
        $bot['http_proxy'] = "";
        $bot['http_proxy_port'] = "";
        $bot['http_proxy_userpwd'] = "";   // user:pwd
        $bot['timezone'] = "Europe/Helsinki";
    }
    
    if(isset($bot['timezone']))
    {
        date_default_timezone_set($bot['timezone']);
    }
 
    $mybot = new MyllyBot($bot);
?>
