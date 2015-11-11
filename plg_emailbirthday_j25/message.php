<?php

/**
 * @package	Joomla.Plugin
 * @subpackage	System.emailbirthday
 * @copyright	Copyright (C) 2015 Shaking Web. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 * @author 	Shaking Web - http://www.shakingweb.com
 */

class message {
    
    private $config;
    
    public function __construct() {
        include_once substr($_SERVER["PHP_SELF"], 0, -40).'configuration.php';
        $this->config = new JConfig();
    }
    
    /**
     * Connect to database
     * 
     * @return type
     */
    private function connect(){
        if((!$link = mysql_connect($this->config->host, $this->config->user, $this->config->password))){
            exit();
        }
        
        if(!mysql_select_db($this->config->db, $link)){
            exit();
        }
        
        return $link;
    }
    
    /**
     * Close database connection
     * 
     * @param type $link
     */
    private function close($link) {
        mysql_close($link);
    }

    /**
     * Getting people who are on birthday today
     * 
     * @return type
     */
    public function getBirthdays() {
        $link = $this->connect();
        
        $query = "SELECT `name`, `email` FROM `".$this->config->dbprefix."users` WHERE `id` IN "
                . "(SELECT `user_id` FROM `".$this->config->dbprefix."user_profiles` "
                . "WHERE `profile_key` = 'profile.dob' AND `profile_value` "
                . "LIKE '%-".date('m')."-".date('d')."%');";
        //echo $query."\n\n";
        $result = mysql_query($query, $link);
        
        $users = Array();
        $pos = 0;
        while(($res = mysql_fetch_array($result))){
            $users['name'][$pos] = $res['name'];
            $users['email'][$pos] = $res['email'];
			$pos++;
        }

        $this->close($link);
        
        return $users;
    }
    
    /**
     * Sending email to people who are on birthday today
     * 
     * @param type $recipient
     */
    public function sendMail($recipient = array()){
        
        if($recipient != null && count($recipient['email']) > 0){
            $link = $this->connect();
            
            // first I get the message
            $query = "SELECT `params` FROM `".$this->config->dbprefix."extensions` "
                    . "WHERE `type` = 'plugin' AND `element` = 'emailbirthday';";
//            echo $query."\n";
            
            $result = mysql_query($query, $link);
            
            // clean the message
            if(($result = mysql_fetch_array($result))){
                $msg = substr($result['params'], 19);
                $msg = substr($msg, 0, -2);
            }
            
            $this->close($link);
            
//            echo $msg."\n";

            $subject = 'Saludo de Cumpleaños - Fondo Esperanza';
			$body = stripslashes($msg);
            $start = strpos($body, "{imagen}") + 8;
            $end = strpos($body, "{/imagen}");
            $images = (substr($body, $start, ($end - $start)));
            
            preg_match_all('/<img(.*)src(.*)=(.*)"(.*)"/U', $body, $img);
            $img = $img[4];
            $img = explode("/", $img[0]);
            $img = implode("\\", $img);
            
            $patterns[0] = "{imagen}".$images."{/imagen}";
            $replacements[0] = " ";

            // minusculas
            $patterns[1] = "u00f1";
            $replacements[1] = "&ntilde;";
            $patterns[2] = "u00e1";
            $replacements[2] = "&aacute;";
            $patterns[3] = "u00e9";
            $replacements[3] = "&eacute;";
            $patterns[4] = "u00ed";
            $replacements[4] = "&iacute;";
            $patterns[5] = "u00f3";
            $replacements[5] = "&oacute;";
            $patterns[6] = "u00fa";
            $replacements[6] = "&uacute;";
            // mayusculas
            $patterns[7] = "u00c1";
            $replacements[7] = "&Aacute;";
            $patterns[8] = "u00c9";
            $replacements[8] = "&Eacute;";
            $patterns[9] = "u00cd";
            $replacements[9] = "&Iacute;";
            $patterns[10] = "u00d3";
            $replacements[10] = "&Oacute;";
            $patterns[11] = "u00da";
            $replacements[11] = "&Uacute;";
            $patterns[12] = "u00d1";
            $replacements[12] = "&Ntilde;";
            // user name
            $patterns[13] = "{nombre}";
            // sending congrats email to each person
			echo "cantidad de recipients: ".count($recipient['email'])."\n\n\n";            
            for($i = 0; $i < count($recipient['email']); $i++){
				echo "email: ".$recipient['email'][$i]."\n\n";
                $replacements[13] = $recipient['name'][$i];
				$body = str_replace($patterns, $replacements, $body);
                $command = substr($_SERVER["PHP_SELF"], 0, -11).'sendmail\sendEmail.exe '
                        . '-f '.$this->config->mailfrom.' '
                        . '-t '.$recipient['email'][$i].' '
                        . '-u "'.$subject.'" '
                        . '-m "'.$body.'" '
                        . '-s '.$this->config->smtphost.':25 '
                        . '-xu intranet_fe '
                        . '-xp '.$this->config->smtppass.' '
                        . '-o tls=yes '
                        . '-o message-content-type=html '
                        . '-o message-charset=utf-8 '
                        . '-a '.substr($_SERVER["PHP_SELF"], 0, -40).$img;
				exec($command);
				sleep(1);
				$body = str_replace($recipient['name'][$i], $patterns[13], $body);
                echo $command;
                
            }
			
        }
        
    }
}

$message = new message();

$recipients = $message->getBirthdays();

$message->sendMail($recipients);