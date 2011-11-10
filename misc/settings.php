<?php
    /* Class for asking bot settings from the database */
    /* Used in admin panel as well as in the actual bot */
    require_once 'conf_inc.php';
    
    class SettingsProvider
    {
        private $values = array();
        private $link;
        
        /* Class constructor 
         * Connects to database and the settings values to memory 
         */
        function __construct()
        {
            $this->link = mysql_connect($cfg['database_host'], $cfg['database_user'], $cfg['database_password']);
            if (!$this->link) 
            {
                echo 'Settings class error: ' . mysql_error();
                return;
            }
            
            $sel_db = mysql_select_db($cfg['database_name']);
            
            if (!$sel_db) 
            {
                echo 'Settings class error: ' . mysql_error();
                return;
            }
            
            $result = mysql_query("SELECT * FROM settings");
            
            if(mysql_num_rows($result) > 0)
            {
                while($row = mysql_fetch_array($result))
                {
                    $this->values[$row['key']] = $row['value'];
                }
            }
        }
        
        /* Class destructor
         * Closes MySQL connection 
         */
        function __destruct()
        {
            if($this->link)
            {
                mysql_close($this->link);
            }
        }
        
        /* Returns value of the given key 
         * Return values: Value on success, NULL on failure
         */
        public function getValue($key)
        {
            if($this->values[$key])
            {
                $return $this->values[$key];
            }
            
            return NULL;
        }
        
        /* Sets value for given key 
         * Return values: TRUE on success, FALSE on failure
         */
        public function setValue($key, $value)
        {
            if($key && $value)
            {
                // To make sure user does not make stupid errors with the query
                $key = mysql_real_escape_string($key);
                $value = mysql_real_escape_string($value);
            
                mysql_query("UPDATE settings SET value = '".$value."' WHERE key = '".$key."'");
                $this->values[$key] = $value;
                return TRUE;
            }
            
            return FALSE;
        }

    }
?>