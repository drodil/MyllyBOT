<?php
    /* Basic class for logging system happenings to file or/and
     * database
     */
    require_once 'conf_inc.php';
     
    class Logger
    {
        var $link;
        var $file_logging = false;
        var $db_logging = false;
        var $file_location = "";
        var $file_handle = NULL;
    
        function __construct($dblog = false, $flog = false, $floc = "")
        {
            $db_logging = $dblog;
            $file_logging = $flog;
            $file_location = $floc;
        
            if($db_logging)
            {
                $this->connectDB();
            }
            
            if($file_logging)
            {
                $file_handle = fopen($file_location, 'a+');
            }
        }
        
        /* Private connect function; to be replaced with the 
         * DB class when it is done(?) */
        private function connectDB()
        {
            $link = mysql_connect($cfg['database_host'], $cfg['database_user'], $cfg['database_password']);
            if (!$link) 
            {
                echo 'Logger class error: ' . mysql_error();
                return;
            }
            
            $sel_db = mysql_select_db($cfg['database_name']);
            
            if (!$sel_db) 
            {
                echo 'Logger class error: ' . mysql_error();
                return;
            }
        }
        
        /* Class destructor
         * Closes MySQL connection
         * Destroys filehandle
         */
        function __destruct()
        {
            if($link)
            {
                mysql_close($link);
            }
            
            if($file_handle)
            {
                fclose($file_handle);
            }
        }
        
        /* Sets up the file logging if not entered in constructor */
        function fileLogging($flog = false, $floc = "")
        {
            $file_logging = $flog;
            
            if($file_logging)
            {
                if($file_handle)
                {
                    // Close existing handle just in case
                    fclose($file_handle);
                }
                
                $file_location = $floc;
                
                $file_handle = fopen($file_location, 'a+');
            }
        }
        
        /* Sets up the database logging if not entered in the constructor */
        function dbLogging($dbl = false)
        {
            $db_logging = $dbl;
            
            if($db_logging && !($link))
            {
                $this->connectDB();
            }
        }
    
        /* Actual logging function */
        function log($msg)
        {
            if(!$msg)
            {
                return;
            }
        
            $log = "";
            
            if(is_array($msg))
            {
                $log = implode(",", $msg);
            }
            else
            {
                $log = $msg;
            }
            
            $log .= "\n";
        
            if($file_logging)
            {
                fwrite($file_handle, $log);
            }

            if($db_logging && $link)
            {
                mysql_query("INSERT INTO debug VALUES ('', '".$log."', NOW())";
            }
        }
    }
?>