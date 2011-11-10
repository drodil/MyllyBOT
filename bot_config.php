<?php
    /* Configuration of the bot when the installation is done
     * manually as in not using the installation script */

    /* Logging and debugging */
    $bot['console_output'] = true;
    $bot['database_debug'] = false;
    $bot['file_debug'] = false;
    $bot['file_debug_location'] = "";
     
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
?>