<?php
/* MyllyBOT - Simple PHP IRC bot based on AK-Bot version 1.0 - Written by greybird - 2004 - startx@aknetwork.org 

   Original AK-Bot source code http://forums.devnetwork.net/viewtopic.php?t=16878

*/

if(isset($_GET['showsrc'])) 
{
	highlight_file();
}

class MyllyBot {

	var $fp = NULL;
	var $lfp = NULL;
	var $rfp = NULL;

	var $rawdata;
	var $data;

	var $log;
    var $debug = 0;

	var $lecture;
	var $lecture_pause;

	var $botnick;
	var $botpassword;
	var $botident;
	var $botrealname;
	var $localhost;
	var $quit_message;

	var $serveraddress;
	var $serverport;
	var $serverchannel;

	var $database_host;
	var $database_user;
	var $database_password;
	var $database_name;
	
	var $http_proxy = "";
	var $http_proxy_port = "";
	var $http_proxy_userpwd = "";

	var $lasturl = "No URLs in this session";

	var $msg_queue = array();
	var $last_msg_time = 0;
	var $last_pong_sent;
	var $first_ping = 1;

	##-------------------------------------------- CLASS CONSTRUCTOR --------------------------------------------
	function MyllyBot($bot)
	{
		$this->log = $bot['log'];
		$this->last_pong_sent = time();

		$this->rfp = NULL;

		$this->botnick = $bot['botnick'];
		$this->botpassword = $bot['botpassword'];
		$this->botident = $bot['botident'];
		$this->botrealname = $bot['botrealname'];
		$this->localhost = $bot['localhost'];

		$this->serveraddress = $bot['serveraddress'];
		$this->serverport = $bot['serverport'];
		$this->serverchannel = $bot['serverchannel'];

		$this->database_host = $bot['database_host'];
		$this->database_user = $bot['database_user'];
		$this->database_password = $bot['database_password'];
		$this->database_name = $bot['database_name'];
		
		if(isset($bot['http_proxy']))
		{
			$this->http_proxy = $bot['http_proxy'];
			$this->http_proxy_port = $bot['http_proxy_port'];
			$this->http_proxy_userpwd = $bot['http_proxy_userpwd'];
		}
        
        $this->debug = $bot['debug'];

		$this->version_message = "BASED ON Ak-Bot Version 1.0 - Written by greybird - 2004 - startx@aknetwork.org";
        
        $this->debug_msg("Startup settings:");
        $this->debug_msg($bot);

		set_time_limit(0); #sets the timeout limit of the script

		#connects to the database
		$this->database_connect();

		#handles the connection
		$this->connect();
	}
    
    function log_timestamp()
    {
        /* Timestamp generation */
        list($usec, $sec) = explode(" ",microtime());
        $string = ((float)$usec + (float)$sec);

        $string2 = explode(".", $string);

        return date("d-m-Y H:i:s", $string2[0]).":".$string2[1];
    }

    function debug_msg($s)
    {
        if($this->debug === 1)
        {
            $timestamp = $this->log_timestamp();
            
            if(is_array($s))
            {
                echo $timestamp . " - ";
                print_r($s);
            }
            else
            {
                echo $timestamp . " - " . $s . "\n";
            }
        }
    }
    
	##-------------------------------------------- DATABASE CONNECT FUNCTION Connects the bot to the database --------------------------------------------
	function database_connect()
	{
		$db = mysql_connect($this->database_host, $this->database_user, $this->database_password);
		mysql_select_db($this->database_name, $db);
	}

	##-------------------------------------------- CONNECT FUNCTION Connects the bot to the server --------------------------------------------
	function connect()
	{
		$this->fp = fsockopen($this->serveraddress, $this->serverport);

		if (!$this->fp)
		{
			$this->debug_msg("There was an error in connecting to " . $this->serveraddress);
			exit;
		}
		else
		{
			if(strlen($this->botpassword) > 0) {
				$this->send("PASS ".$this->botpassword, true);
			}
			
			$this->send("NICK ".$this->botnick, true);
			$this->send("USER ".$this->botident.' '.$this->localhost.' '.$this->serveraddress.' :'.$this->botrealname, true);

			$this->receive();
		}
	}

	##-------------------------------------------- RECONNECT FUNCTION --------------------------------------------
	function reconnect()
	{
		if($this->fp)
		{
			fclose($this->fp);
			$this->fp = NULL;
		}

		if($this->lfp)
		{
			fclose($this->lfp);
			$this->lfp = NULL;
		}

		$this->last_pong_sent = time();

		$this->connect();
	}

	##-------------------------------------------- DISCONNECT FUNCTION Disconnects the bot from the server --------------------------------------------
	function disconnect()
    {
		$this->send("QUIT :".$this->version_message);
		if($this->fp)
		{
			fclose($this->fp);
			$this->fp = NULL;
		}

		if($this->lfp)
		{
			fclose($this->lfp);
			$this->fp = NULL;
		}

		exit();
	}

	##-------------------------------------------- RECEIVE FUNCTION Receives all data from connection --------------------------------------------
	function receive()
	{
		if($this->fp)
		{
			stream_set_timeout($this->fp, 10);
			$run = true;
		}
		else
		{
			$this->reconnect();
		}

		/* Main loop */
		while ($run)
		{
			if( $this->last_pong_sent+60*5 < time() && !($this->fp) )
			{
				$this->first_ping = 1;
                $this->debug_msg("Timed out: Reconnecting..");
				$this->reconnect();
			}

			$this->rawdata = fgets($this->fp);
			$this->rawdata = str_replace("\r", "", str_replace("\n", "", $this->rawdata));
			if($this->rawdata)
			{
				$this->process_data();
				$this->parse_data();

				if($this->log)
				{
					$this->logging();
				}
			}

			if(count($this->msg_queue) > 0 && $this->last_msg_time+1.5 < microtime(true))
			{
				$data = array_shift($this->msg_queue);
				fputs($this->fp, $data."\r\n");
				$this->last_msg_time = microtime(true);
			}
			usleep(100000);
		}

		sleep(10);
		$this->reconnect();
	}

	function log($param_arr)
	{
		$query = "INSERT INTO log (".implode(', ',array_keys($param_arr)).") VALUES (\"".implode('", "',array_values($param_arr))."\")";
		$result = mysql_query($query);
	}

	function log_events($line_array)
	{
		$event_type = $line_array[1];
		$rest = array_slice($line_array, 3);

		switch($event_type)
		{
			case 'PRIVMSG':
			$gg1 = explode('!', $line_array[0]);
			$gg2 = explode('@', $gg1[1]);
			if($event_type[0] == ':ACTION')
			{
				$event_type = 'ACTION';
				array_unshift($rest);
				$msg = implode(" ", $rest);
			}
			else
			{
				$event_type = 'PRIVMSG';
				$msg = substr(implode(' ', $rest), 1);
			}

			$this->log(array(
							 'data_type' => $event_type,
							 'nick' => substr($gg1[0],1),
							 'ident' => $gg2[0],
							 'host' => $gg2[1],
							 'Timestamp' => time(),
							 'msg' => $msg,
							 'raw_data' => implode(" ", $line_array)
						));

			break;

			default:
			break;
		}
	}
	##-------------------------------------------- PROCESS DATA FUNCTION Processes the data sent into an array or useful items
	##--------------------------------------------
	function process_data()
	{
		$params = explode(" ", $this->rawdata);
		$this->log_events($params);

		@$message = str_replace("$params[0]", "", $this->rawdata);
		@$message = str_replace("$params[1]", "", $message);
		@$message = str_replace("$params[2]", "", $message);
		@$from = explode ("!", $params[0]);
		@$this->data['from'] = str_replace(":", "", $from[0]);
		@$user = str_replace(":", "", $from[0]);
		@$details = explode ("@", $from[1]);

		@$this->data['ident'] = $details[0];
		@$this->data['host'] = $details[1];
		@$this->data['action'] = $params[1];
		@$this->data['sent_to'] = $params[2];
		@$this->data['ping'] = $params[0];
		@$this->data['message'] = substr($message, 4);
        
        $this->debug_msg("Processing data:");
        $this->debug_msg($this->data);

		if($this->data['message'][0] == "!")
		{
			$maction = explode(" ", $this->data['message']);
			$mfullaction = str_replace($maction[0], "", $this->data['message']);

			$this->data['action'] = "TRUE";
			$this->data['message_action'] = $maction[0];
			@$this->data['message_target'] = $maction[1];
			@$this->data['message_target2'] = $maction[2];
			$this->data['message_action_text'] = str_replace(" ", "%20", substr($mfullaction, 1));
			$this->data['message_action_text_plain'] = str_replace("%20", " ", $this->data['message_action_text']);
			@$this->data['message_action_text_plain_with_params'] = substr(str_replace($maction[0], "", str_replace($maction[1], "", $mfullaction)), 2);
		}
		else if((preg_match("/http/i",$this->data['message']) || preg_match("/www/i",$this->data['message'])) && strcasecmp($this->data['from'], $this->botnick) != 0)
		{
			$maction = explode(" ", $this->data['message']);

			$this->data['URL_log'] = array(); 
			
			foreach ($maction as $key => $action)
			{
				$this->data['URL_log'][$key] = $action;
			}

			$mfullaction = str_replace($maction[0], "", $this->data['message']);
			$this->data['action'] = "TRUE";
			$this->data['message_action'] = "URL_action";
		}
	}

	##-------------------------------------------- LOGGING FUNCTION Logs the data --------------------------------------------
	function logging()
	{
		if(!$this->lfp)
		{
			$this->lfp = fopen('log.txt', 'w');
		}

		$timestamp = $this->log_timestamp();
		fputs($this->lfp, $timestamp . " - " . $this->rawdata."\n");
	}

	function handle_function($command, $vars='')
	{
		switch($command)
		{
			case '!commands':
				return $this->echo_commands();

			case '!sql':
				$this->database_connect();
				return $this->calculate($vars);
				break;

			case '!addcmd':
				$this->database_connect();
				return $this->addcmd($vars);
				break;

			case '!lasturl':
				return $this->last_url();

			default:
				$this->database_connect();
				return $this->handle_odd_command(substr($command, 1), $vars);
				break;
		}
	}

	##---- RETURNS last URL pasted to channel
	function last_url()
	{
		return $this->lasturl;
	}

	##---- PARSE DATA FUNCTION Parses the data that was just sent - i.e. checks for messages
	function parse_data()
	{
		if(isset($this->data['sent_to']) && $this->data['sent_to']==$this->serverchannel AND $this->data['action'] == 'TRUE')
		{
			if($this->data['message_action'][0] == '!')
			{
				//check for pipeing character
				if(stristr($this->data['message'], '=>') !== false)
				{
					$line = $this->data['message'];
					$line = preg_replace('!\s+!', ' ', $line); //multiple spaces to one
					$line = str_replace(" => ", "=>", $line);
					$line = str_replace("=> ", "=>", $line);
					$line = str_replace(" =>", "=>", $line);
					$commands = explode("=>", $this->data['message']);
					foreach($commands as $command)
					{
						$vars = '';
						$command = trim($command);
						$jibu = explode(" ", $command);
						if(count($jibu) > 1)
						{
							$command = array_shift($jibu);
							$vars = implode(" ", $jibu);
						}

						//if there is space where retval should insert, insert there. otherwice at the end
						if(stristr($vars, '<r>') !== false)
						{
							$retval2 = str_replace('<r>', $retval, $vars);
						}
						else
						{
							$retval2 = $vars.$retval;
						}

						$retval = $this->handle_function($command, $retval2);
					}
				}
				else
				{
					//no chain reaction, just normal function
					$jibu = explode(" ", $this->data['message']);
					if(count($jibu) > 1)
					{
						$command = array_shift($jibu);
						$vars = implode(" ", $jibu);
					}
					else
					{
						$command = $this->data['message'];
						$vars = "";
					}
					$retval = $this->handle_function($command, $vars);
				}
				
				$retval = trim($retval);
				$retval = explode("\n", $retval);
				foreach($retval as $rivi)
				{
					$this->send("PRIVMSG ".$this->data['sent_to']." :$rivi");
				}

			}
			else if($this->data['message_action'] == 'URL_action')
			{
				$retval = $this->URL_log($this->data['URL_log']);
				
				if ($retval != "Added")
				{
					$this->send("PRIVMSG ".$this->data['sent_to']." :$retval");
				}
			}
		}

		if( $this->data['ping'] == 'PING')
		{
			//keep mysql connection alive... :)
			$yksi = mysql_fetch_row(mysql_query("select 1"));
			
			$this->send("PONG ".$this->data['action']);
			$this->last_pong_sent = time();

			if($this->first_ping == 1)
			{
				/* Join after first ping */
                $this->debug_msg("Joining channel");
				$this->send("JOIN ".$this->serverchannel, false);
				$this->first_ping = 0;
			}
		}
	}


	##-------------------------------------------- SEND FUNCTION Sends any data to the server --------------------------------------------
	function send($data, $without_queue = false)
	{
		if($without_queue)
		{
            $this->debug_msg("Sending: " . $data);
			fputs($this->fp, $data."\r\n");
		}
		else
		{
            $this->debug_msg("Queuing: " . $data);
			array_push($this->msg_queue, $data);
		}
	}

	##-------------------------------------------- VERSION FUNCTION Shows bot version info --------------------------------------------
	function version()
	{
		$this->send("NOTICE ".$this->data['from']." :".$this->version_message);
	}

	function echo_commands()
	{
		$commands = array('addcmd', 'sql', 'commands', 'lasturl');
		$query = 'SELECT command from commands';
		$result = mysql_query($query);

		while($row = mysql_fetch_assoc($result))
		{
			$commands[] = $row['command'];
		}

		$commands = implode(', ',$commands);
		
		return "Commands are: ".$commands;
	}

	function handle_odd_command($cmd, $vars = '')
	{
		$query = 'SELECT path FROM commands WHERE command = "'.mysql_real_escape_string($cmd).'"';
		$result = mysql_query($query);
		$row = mysql_fetch_row($result);

		if(mysql_num_rows($result) == 1 && $row !== false)
		{
			$var = $vars;

			$ch = curl_init($row[0]);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "var=".$var."&nick=".$this->data['from']."&host=".$this->data['host']."&ident=".$this->data['ident']);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			
			if($this->http_proxy && $this->http_proxy_port)
			{
				curl_setopt($ch, CURLOPT_PROXY, $this->http_proxy);
				curl_setopt($ch, CURLOPT_PROXYPORT, $this->http_proxy_port);
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->http_proxy_userpwd);
			}

			$lines = curl_exec($ch);
			curl_close($ch);

			$lines = explode("\n", $lines);

			if(count($lines) > 0 && count($lines) < 5)
			{
				$ret_arr = array();
				foreach($lines as $line)
				{
					if(strlen($line) > 440)
					{
						$line = substr($line,0,437).'...';
					}
					$ret_arr[] = $line;
				}

				return implode("\n", $ret_arr);
			}
		}
	}

	function calculate($vars)
	{
		$banned = array('select', 'from', 'drop', ';', 'delimiter', 'where', '#', '--', 'into', 'update', '/0');
		foreach($banned as $ban)
		{
			if(stristr($vars, $ban) !== false)
			{
				return ("User is stupid!");
			}
		}

		$query = "SELECT ($vars) as calculation";
		$result = mysql_query($query);
		if(!$result) echo mysql_error()."\n";
		
		$row = mysql_fetch_row($result);
		if(!$row) echo mysql_error()."\n";

		if(count($row) != 1)
		{
			return ("Syntax error");
		}
		else
		{
			$value = $row[0];
			return ("$value");
		}
	}

	function addcmd($vars)
	{
		$vars = explode(" ", $vars);
        
		if(count($vars) != 2)
		{
			return ("Usage: <command> <url (with http)>");
		}
        
		$command = mysql_real_escape_string($vars[0]);
		$path = mysql_real_escape_string($vars[1]);
		$query = "SELECT * FROM commands WHERE command = '$command'";
		$result = mysql_query($query);
		$row = mysql_fetch_row($result);

		if(count($result) == 1 && $row !== false)
		{
			return ("That command already exists.");
		}
		else
		{
			$query = "INSERT INTO commands (command, path) values ('$command', '$path')";
			$result = mysql_query($query);
			if($result)
            {
                return ("Done.");
            }
            else
            {
                $this->debug_msg(mysql_error() . "\n Query: " .$query);
            }
		}
	}

	function get_title($content)
	{
		$dada = stristr($content, '<title>'); //strip everything before this
		$dada = stristr($dada, '</title>', true); //and after this
		$dada = str_ireplace("<title>", "", $dada);
		$dada = preg_replace('!\s+!', ' ', $dada);
		return trim(html_entity_decode($dada));
	}

	function URL_log($url)
	{
		/* Fix regexp */
		$ret_str = array();
		$url = $url[0];
		if(substr($url,0,4) != "http")
		{
			$url = "http://".$url;
		}

		$urlArray=array();
		foreach ($url as $new_url)
		{
			$urlArray[] = mysql_real_escape_string($new_url);
		}

		$urlQuery = '"';
		$urlQuery .= implode('" OR `url` = "',$urlArray);
		$urlQuery .= '"';

		$query = 'SELECT * FROM url_log WHERE url = '.$urlQuery;
		$result = mysql_query($query);
		if(!$result) echo mysql_error()."\nQuery: ".$query."\n";

		if($result !== false && (mysql_num_rows($result) >= 1 || mysql_num_rows($cur_result) >= 1) )
		{
			$row = mysql_fetch_assoc($result);
			$ret_str[] =  "That was OLD! (".$row["user"]." pasted ".$row["time"].')';
			return (implode(" ",$ret_str));
		}
		else
		{
			$urlInsert=array();
			
			foreach ($url as $row)
			{
				$urlInsert[]= "('','".mysql_real_escape_string($this->data['from'])."','".mysql_real_escape_string($row)."',NOW())";
				$this->lasturl = $row;
			}

			$q = implode(",",$urlInsert);

			$query = "INSERT INTO url_log values " . $q;
			$result = mysql_query($query);
			if($result)
			{
				return (implode(" ",$ret_str));
			}
			else 
			{
                $this->debug_msg(mysql_error() . "\nQuery: ". $query);
				echo mysql_error()."\nQuery: ".$query."\n";
			}
		}
	}

	##-------------------------------------------- HELP FUNCTION Displays help messages --------------------------------------------
	function help()
	{
		/* TODO: Fixme */
		if(!$this->data[message_target])
		{ 
			$this->send("PRIVMSG ".$this->data['from']." :Help File For ".$this->botnick);
			$this->send("PRIVMSG ".$this->data['from']." :------------------------------");
			$this->send("PRIVMSG ".$this->data['from']." : ");
			$this->send("PRIVMSG ".$this->data['from']." : !op <nick>");
			$this->send("PRIVMSG ".$this->data['from']." : !deop <nick>");
			$this->send("PRIVMSG ".$this->data['from']." : !voice <nick>");
			$this->send("PRIVMSG ".$this->data['from']." : !devoice <nick>");
			$this->send("PRIVMSG ".$this->data['from']." : !kick <nick>");
			$this->send("PRIVMSG ".$this->data['from']." : !mode <+/- mode>");
			$this->send("PRIVMSG ".$this->data['from']." : !topic <topic>");
			$this->send("PRIVMSG ".$this->data['from']." : !addquote <quote>");
			$this->send("PRIVMSG ".$this->data['from']." : !adduser <nick> <userlevel>");
			$this->send("PRIVMSG ".$this->data['from']." : !lecture <filename/pause/stop/start>");
			$this->send("PRIVMSG ".$this->data['from']." : !quit");
			$this->send("PRIVMSG ".$this->data['from']." : !google <search word?");
			$this->send("PRIVMSG ".$this->data['from']." : !time <+/- time zone>");
			$this->send("PRIVMSG ".$this->data['from']." : !8ball <question>");
			$this->send("PRIVMSG ".$this->data['from']." : !userlevel");
			$this->send("PRIVMSG ".$this->data['from']." : !quote <bash|user|from|last|number|random> <value> (note bash, last and random require no
			<value>)");
			$this->send("PRIVMSG ".$this->data['from']." : !base64 <encode|decode> <message to encode/decode>");
			$this->send("PRIVMSG ".$this->data['from']." : !md5 <plaintext>");
			$this->send("PRIVMSG ".$this->data['from']." : !rot13 <message>");
			$this->send("PRIVMSG ".$this->data['from']." : !weather <zip code>");
			$this->send("PRIVMSG ".$this->data['from']." : !version");
			$this->send("PRIVMSG ".$this->data['from']." : !help");
		}
		else
		{

		}
	}
}
?>
