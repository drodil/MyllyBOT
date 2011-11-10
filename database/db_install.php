<?php
    /* Database installation script */
    require_once 'conf_inc.php';
    
    // Growing DB version number if the structure changes
    define("DB_VERSION", 1);
    
    $link = mysql_connect($cfg['db_host'], $cfg['db_user'], $cfg['db_pass']);

    $link = mysql_connect($cfg['database_host'], $cfg['database_user'], $cfg['database_password']);
    if (!$link) 
    {
        echo 'DB when installing database: ' . mysql_error();
        return;
    }

    $sel_db = mysql_select_db($cfg['database_name']);

    if(!$sel_db)
    {
        echo 'DB when installing database: ' . mysql_error();
        return;
    }

    /* Check for DB version from the database */
    $res = mysql_query("SELECT value FROM settings WHERE key = 'DB_VERSION'");
    if(mysql_num_rows($res) > 0)
    {
        $row = mysql_fetch_row($result);
        
        /* If current version is newer, drop all tables */
        if($row[0] < DB_VERSION)
        {
            mysql_query("DROP TABLE IF EXIST access, 
                                             commands,
                                             log,
                                             url_log,
                                             settings");
        }
    }
    
    
    /* Create the tables */
    mysql_query('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"');

    mysql_query("CREATE TABLE IF NOT EXISTS `access` (
                                                      `user` varchar(20) NOT NULL,
                                                      `level` tinyint(1) NOT NULL DEFAULT '1',
                                                      `channel` varchar(255) NOT NULL,
                                                      `server` varchar(255) NOT NULL,
                                                       PRIMARY KEY (`user`)
                                                      ) ENGINE=MyISAM DEFAULT CHARSET=latin1;");

    mysql_query("CREATE TABLE IF NOT EXISTS `commands` (
                                                      `ID` int(11) NOT NULL AUTO_INCREMENT,
                                                      `command` varchar(32) NOT NULL,
                                                      `path` varchar(256) NOT NULL,
                                                      PRIMARY KEY (`ID`),
                                                      KEY `command` (`command`)
                                                        ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

    mysql_query("CREATE TABLE IF NOT EXISTS `log` (
                                                   `ID` int(11) NOT NULL AUTO_INCREMENT,
                                                   `data_type` enum('PRIVMSG','ACTION') NOT NULL,
                                                   `raw_data` text NOT NULL,
                                                   `nick` varchar(32) NOT NULL,
                                                   `ident` varchar(32) NOT NULL,
                                                   `host` varchar(32) NOT NULL,
                                                   `msg` text NOT NULL,
                                                   `timestamp` int(11) NOT NULL,
                                                    PRIMARY KEY (`ID`),
                                                    KEY `nick` (`nick`),
                                                    KEY `data_type` (`data_type`),
                                                    KEY `ident` (`ident`),
                                                    KEY `host` (`host`)
                                                   ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

    mysql_query("CREATE TABLE IF NOT EXISTS `url_log` (
                                                       `id` int(11) NOT NULL AUTO_INCREMENT,
                                                       `nick` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
                                                       `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                                                       `time` datetime NOT NULL,
                                                       PRIMARY KEY (`id`),    
                                                       UNIQUE KEY `url` (`url`)
                                                       ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");
                                                       
    mysql_query("CREATE TABLE IF NOT EXISTS `settings` (
                                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                                        `key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
                                                        `value` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
                                                        PRIMARY KEY(`id`),
                                                        UNIQUE KEY `key` (`key`)
                                                        ) ENGINE=MyISAM DEFAULT CHASET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");
                                           
    /* Clear settings table just to be sure */
    mysql_query("TRUNCATE TABLE settings");

    /* Set bot default settings */
    /* TODO: Make these do something :o */
    mysql_query("INSERT INTO settings VALUES ('', 'DB_VERSION', '".DB_VERSION."')");            // Database version number
    
    mysql_query("INSERT INTO settings VALUES ('', 'AUTO_GREET', 'OFF')");                       // Auto greet on join
    mysql_query("INSERT INTO settings VALUES ('', 'AUTO_GREET_MSG', 'hi \$nick')");             // Auto greet message
    
    mysql_query("INSERT INTO settings VALUES ('', 'DEFAULT_SEARCH_ENGINE', 'GOOGLE'");          // Default search engine set
    
    mysql_query("INSERT INTO settings VALUES ('', 'DAILY_WEATHER', 'OFF'");                     // Automatic daily weather post
    mysql_query("INSERT INTO settings VALUES ('', 'DAILY_WEATHER_LOCATION', 'Oulu, Finland'");  // Daily weather post location
    mysql_query("INSERT INTO settings VALUES ('', 'DAILY_WEATHER_TIME', '8:00'");               // Daily weather post time
    
    mysql_query("INSERT INTO settings VALUES ('', 'DAILY_RANDOM_QUOTE', 'OFF'");                // Automatic daily random quote
    mysql_query("INSERT INTO settings VALUES ('', 'DAILY_RANDOM_QUOTE_TIME', '10:00'");         // Random quote time
    
    mysql_query("INSERT INTO settings VALUES ('', 'RSS_FEEDS', 'OFF'");                         // Automatic post of new RSS feed stuff
    
    mysql_close($link);  
?>