<?php
Class Auth_model extends CI_Model
{
    function __construct() {
        parent::__construct();
      
    }

    function already_logged_in($username) {        
        $data = array('USERNAME' => $username, 'STATUS' => 0);
        $this->db->select('STATUS');
        $this->db->where($data);
        $query = $this->db->get('LOGIN_LOG');
        if($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    //Login function for staff
    //0 - Invalid username/password
    //1 - Login successful
    //2 - Already logged in
   
    
       function auth1($username,$password,$role) {
              $data=array('USERNAME'=>$username,'ROLE'=>$role);
             $this->db->select('PASSWORD,SALT');
          $this->db->where($data);
          $query = $this->db->get('STAFF_LOGIN');
        if($query->num_rows() === 1) {
            $row = $query->row();
            $db_password = $row->PASSWORD;
            $salt = $row->SALT;
            
            $password = hash('sha512',$password.$salt);
            // echo $password.'<br><br>';
             //echo  $db_password.'<br>';
             ///echo $password1.'<br>';
             //echo $salt.'<br>';
            

            if($db_password == $password) {
               
     
                
                $record = $this->get_staff_info($username);
                $this->set_role($username);
                $result = array(
                        'username' => $username,    
                        'name' => $record['name'],
                        'depcode' => $record['depcode'],
                        'logged_in' => TRUE,
                        'sesid' => 34,
                        'role' => $record['desg'],
                        'is_staff' => TRUE
                    );
					
				$user_agent = $_SERVER['HTTP_USER_AGENT'];
				$browser = $this->getBrowser($user_agent);
				$os = $this->getOS($user_agent);
                $ip = $_SERVER['REMOTE_ADDR'];
                $host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
                $data = array('USERNAME' => $username, 
                    
							'IPADDRESS' => $ip, 
							'BROWSER' => $browser,
							'OS' => $os,
							'ITIME' => date('d-M-Y h:i:sa'), 
							'HOST' => $host, 
							'STATUS' => 0);
                $this->db->insert('LOGIN_LOG', $data);

                $this->db->select('ID');
                $this->db->where($data);
                $query = $this->db->get('LOGIN_LOG');
               // print_r($query);
                if($query->num_rows() == 1) {
                    $row = $query->row();
                    $result['login_id'] = $row->ID;
                    $this->session->set_userdata($result);
                    return 1;
                } else return 0;
                
            } else {
                return 0;
            }
        }
        else{
            return 1;
        }
    }

    function get_staff_info($username) {
        $this->db->select('STAFFNAME, DESG, DEPCODE');
        $this->db->where('STAFFID',$username);
        $query = $this->db->get('STAFF');
        if($query->num_rows() === 1){
            $row = $query->row();
            $array = array('name' => $row->STAFFNAME, 'depcode' => $row->DEPCODE, 'desg' => $row->DESG);
            return $array;
        } else return FALSE;
    }

    
       
        function set_role($staffid) {
        $data = array('USERNAME' => $staffid);
        $this->db->select('ROLE');
        $this->db->where($data);
        $query = $this->db->get('STAFF_LOGIN');

        if($query->num_rows() > 0) {
            $record = $query->result();
            foreach($record as $r) {
                switch($r->ROLE) {
                   
                    case 'LECTURER'  : $this->session->set_userdata('is_lecturer',TRUE);   $this->session->set_userdata('username1',$staffid); break;
                    case 'HOD': $this->session->set_userdata('is_hod',TRUE); $this->session->set_userdata('username2',$staffid); break;
                    case 'PRINCIPAL': $this->session->set_userdata('is_principal',TRUE); $this->session->set_userdata('username3',$staffid); break;
                   
                }
            }
        }
       
}



    function is_auth_user($type) {
        

    if($this->session->userdata('is_'.$type) == TRUE && $this->session->userdata('is_staff') == TRUE) {
        return TRUE;
    } else {
        return FALSE;
    }
            
    }

    function logout_all_machine($username, $user) {
        if($user == 'staff'||'acoe'||'l_ceg'||'l_mit'||'l_act'||'l_sap'||'dcdcoe') $type = 'F';
        else $type = 'S';
        $data = array('USERNAME' => $username, 'TYPE' => $type, 'STATUS' => 0);
		$udata = array('STATUS' => 2, 'OTIME' => date('d-M-Y h:i:sa'));
        $this->db->set($udata);
        $this->db->where($data);
        $this->db->update('LOGIN_LOG');
        $this->session->set_userdata('logged_in', FALSE);
        return TRUE;
    }


    function is_logged_in($type) {
        header("cache-Control: no-store, no-cache, must-revalidate");
        header("cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        $is_logged_in = $this->session->userdata('logged_in');
        if(isset($is_logged_in) && $is_logged_in ===TRUE)
        {
            $data = array('ID' => $this->session->userdata('login_id'), 'STATUS' => 0);
            $this->db->where($data);
            $query = $this->db->get('LOGIN_LOG');
            if($query->num_rows() == 1)
                return TRUE;
            else {
                $this->session->set_userdata('logged_in', FALSE);
                return FALSE;
            }             
        } else {
            return FALSE;
        }
    }

    function change_password($username, $old, $new) {
        $data = array('USERNAME' => $username);
        $this->db->select('PASSWORD, SALT');
        $this->db->where($data);
		if($this->session->userdata('is_staff')){
			$table = 'STAFF_LOGIN';
                        
		}
        
        $query = $this->db->get($table);
        if($query->num_rows() === 1) {
            $row = $query->row();
            $db_password = $row->PASSWORD;
            $salt = $row->SALT;
            $old_password = hash('sha512', $old . $salt);

            if($db_password === $old_password) {
                //old password is correct
                $random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
                $new_password = hash('sha512', $new.$random_salt);

                $udata = array('PASSWORD' => $new_password, 'SALT' => $random_salt);
                $data = array('USERNAME' => $username);

                $this->db->where($data);
                if($this->session->userdata('is_staff')) {
                    $this->db->update('STAFF_LOGIN', $udata);
                } 

                return TRUE;
            } else {
                return FALSE;
            }
        }
        else{
            return FALSE;
        }
    }

    function logout($username) {
        $data = array('USERNAME' => $username, 'STATUS' => 0);
		$udata = array('STATUS' => 1, 'OTIME' => date('d-M-Y h:i:sa'));
        $this->db->set($udata);
        $this->db->where($data);
        $this->db->update('LOGIN_LOG');
        return TRUE;
    }

    function valid_user_email($username, $email, $type) {
        $data = array('USERNAME' => $username, 'EMAIL' => $email);
        $table = $type == 'F' ? 'STAFF_LOGIN' : '';
        $this->db->where($data);
        $query = $this->db->get($table);

        if($query->num_rows() > 0) 
            return TRUE;
        else
            return FALSE;
    }

    function generate_rand_code($username, $email, $type) {
        $random = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',8)),0,10);
        #invaliating previous random code
        $data = array('USERNAME' => $username, 'TYPE' => $type);
        $this->db->set('FLAG', 0);
        $this->db->where($data);
        $this->db->update('FORGOT_PWD');

        $data = array(
            'USERNAME' => $username,
            'TYPE' => $type,
            'RAND' => $random,
            'FLAG' => 1,
            'TIMESTAMP' => date('d-M-Y h:i:sa'),
            'IPADD' => $_SERVER['REMOTE_ADDR'],
            'HOST' => gethostbyaddr($_SERVER['REMOTE_ADDR']) );
        $this->db->insert('FORGOT_PWD', $data);
         $this->load->library('phpmailer_lib');
        // PHPMailer object
        $mail = $this->phpmailer_lib->load(); 
        $mail->IsSMTP(); // we are going to use SMTP
        $mail->SMTPAuth   = true; // enabled SMTP authentication
        $mail->SMTPSecure = "ssl";  // prefix for secure protocol to connect to the server
        $mail->Host       = "smtp.gmail.com";      // setting GMail as our SMTP server
        $mail->Port       = 465;                   // SMTP port to connect to GMail
        $mail->Username   = "itsmeviji00@gmail.com";  // user email address
        $mail->Password   = "Vijikkovalan@10091993";            // password in GMail
        $mail->setFrom('kovalan.annauniv@gmail.com', 'SECURE DOCUMENT PRINTING');
        $mail->addReplyTo('itsmeviji00@gmail.com', 'Indhumathy & Team');
            //Who is sending 
        $mail->isHTML(true);
        $mail->Subject    = "Secure Document Creation - Forgot Password";
        $mail->Body      = "<html>
                            <head>
                            <title>Secure Document Creation - Forgot Password</title>
                            </head>
                            <body>
                            <p><b>Secure Document Creation - Forget Password</b>.Use this code to recover your password</p><br>
                                <h3>$random</h3>
                            <p>With Regards</p>
                            <p>Indhumathy& Team</p>
                            </body>
                            </html>";
        
        
        $destino = $email;
        $mail->AddAddress($destino, "Receiver");
        if(!$mail->Send()) {
            return false;
        } else {
            return true;
        } 
    }
        
    

    function reset_password($username, $type, $code, $pwd) {
        $data = array('USERNAME' => $username, 'TYPE' => $type, 'RAND' => $code);
        $this->db->select('RAND, FLAG, TIMESTAMP');
        $this->db->where($data);
        $query = $this->db->get('FORGOT_PWD');
        if($query->num_rows() > 0) {
            $row = $query->row();

            //Reset code is valid for only 2 hour
            $time1 = new DateTime($row->TIMESTAMP); // string date
            $time2 = new DateTime();
            $time2->setTimestamp(date('U'));
            $interval =  $time2->diff($time1);
            $x = $interval->format("%H");
            if($row->FLAG != 1) {
                return 'Reset code expired';
            } elseif($row->RAND !== $code) {
                return 'Reset code does not match';
            }
          
            $table2 =  'STAFF_LOGIN';
            $random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
            $password = hash('sha512', $pwd.$random_salt);
            
            $ip = $_SERVER['REMOTE_ADDR'];
            $host = gethostbyaddr($_SERVER['REMOTE_ADDR']);

            //reseting password
            $data = array('USERNAME' => $username);
            
            $idata = array(
                    'PASSWORD' => $password,
                    'SALT' => $random_salt,
                    'TIMESTAMP' => date('d-M-Y h:i:sa'),
                    'IPADD' => $ip,
                    'HOST' => $host );
            $this->db->where($data);
            $this->db->update($table2, $idata);
            $data = array('USERNAME' => $username, 'TYPE' => $type);
            $this->db->set('FLAG', 0);
            $this->db->where($data);
            $this->db->update('FORGOT_PWD');

            return TRUE;
        } else 
            return 'Invalid Reset code.';
    }
	 
//    function reset_dummy_password($username,$idata) {
//            $data = array('USERNAME' => $username);
//            $table =  'STAFF_LOGIN';
//          $this->db->where($data);
//          $this->db->update($table, $idata); 
//    }
	
	function getOS($user_agent) { 
		$os_platform = "Unknown OS Platform";

		$os_array = array(
            '/windows nt 10/i'      =>  'Windows 10',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/IPHONE/i'             =>  'IPHONE',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );

		foreach ($os_array as $regex => $value) { 
			if (preg_match($regex, $user_agent)) {
				$os_platform    =   $value;
			}
		}   
		
		return $os_platform;	
	}
	
	function getBrowser($user_agent) {
		$browser = "Unknown Browser";
		$browser_array = array(
            '/msie/i'       =>  'Internet Explorer',
            '/firefox/i'    =>  'Firefox',
            '/safari/i'     =>  'Safari',
            '/chrome/i'     =>  'Chrome',
            '/edge/i'       =>  'Edge',
            '/opera/i'      =>  'Opera',
            '/netscape/i'   =>  'Netscape',
            '/maxthon/i'    =>  'Maxthon',
            '/konqueror/i'  =>  'Konqueror',
            '/mobile/i'     =>  'Handheld Browser'
        );

		foreach ($browser_array as $regex => $value) { 
			if (preg_match($regex, $user_agent)) {
				$browser    =   $value;
			}
		}
		return $browser;
	}
        
        function staff_list()
        {
           
            $this->db->select('USERNAME,ROLE');
            $query=$this->db->get('STAFF_LOGIN');
            if($query->num_rows()>0)    
            {
               return $query->result();   
            }
            else 
            {
            return FALSE;
            }
            
            
        }

}
