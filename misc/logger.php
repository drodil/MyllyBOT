<?php
    /* Basic class for logging system happenings to file or/and
     * database
     */
    require_once 'conf_inc.php';
     
    class Logger
    {
        private $link;
        private $file_logging = false;
        private $db_logging = false;
        private $file_location = "";
        private $file_handle = NULL;
    
        function __construct($dblog = false, $flog = false, $floc = "")
        {
            $this->db_logging = $dblog;
            $this->file_logging = $flog;
            $this->file_location = $floc;
        
            if($this->db_logging)
            {
                $this->connectDB();
            }
            
            if($this->file_logging)
            {
                $this->file_handle = fopen($file_location, 'a+');
            }
        }
          
        /* Class destructor
         * Closes MySQL connection
         * Destroys filehandle
         */
        function __destruct()
        {
            if($this->link)
            {
                mysql_close($this->link);
            }
            
            if($this->file_handle)
            {
                fclose($this->file_handle);
            }
        }
        
        /* Private connect function; to be replaced with the 
         * DB class when it is done(?) */
        private function connectDB()
        {
            $this->link = mysql_connect($cfg['database_host'], 
                                        $cfg['database_user'], 
                                        $cfg['database_password']);
            if (!$this->link) 
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

        
        /* Sets up the file logging if not entered in constructor */
        public function fileLogging($flog = false, $floc = "")
        {
            $this->file_logging = $flog;
            
            if($this->file_logging)
            {
                if($this->file_handle)
                {
                    // Close existing handle just in case
                    fclose($this->file_handle);
                }
                
                $this->file_location = $floc;
                
                $this->file_handle = fopen($this->file_location, 'a+');
            }
        }
        
        /* Sets up the database logging if not entered in the constructor */
        public function dbLogging($dbl = false)
        {
            $this->db_logging = $dbl;
            
            if($this->db_logging && !($this->link))
            {
                $this->connectDB();
            }
        }
    
        /* Actual logging function */
        public function log($msg)
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
        
            if($this->file_logging)
            {
                fwrite($this->file_handle, $log);
            }

            if($this->db_logging && $this->link)
            {
                mysql_query("INSERT INTO debug VALUES ('', '".$log."', NOW())";
            }
        }
    }
?>