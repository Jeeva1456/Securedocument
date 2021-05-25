<?php
Class Signup_model extends CI_Model
{
    var $staffid = "";
    function __construct() {
        parent::__construct();
        $this->load->model('General_model', 'GM');
        $this->load->model('Module_model', 'MOD');
    }

    function is_exist($staffid) {
        $data = array('STAFFID' => $staffid);
        $this->db->select('STAFFID');
        $this->db->where($data);
        $query = $this->db->get('STAFF');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    function already_registered($staffid) {
        $data = array('EMPID' => $staffid);
        $this->db->select('EMPID');
        $this->db->where($data);
        $query = $this->db->get('STAFF_PERSONAL');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    function get_registered_email($staffid) {
        $data = array('STAFFID' => $staffid, 'DEPCODE !=' => 0);
        $this->db->select('EMAIL');
        $this->db->where($data);
        $query = $this->db->get('STAFF');
        if($query->num_rows() > 0) {
            $row = $query->row();
            return $row->EMAIL;
        } else
            return FALSE;
    }

    function staff_info($staffid) {
        $staff = $this->GM->get_staff_info($staffid);
        $dept = $this->GM->get_dept_info($staff['depcode']);
        $campus = $this->GM->get_campus_info($dept['camcode']);
        $email = $this->get_registered_email($staffid);

        $table = '<tr>';
        $table .= '<th>Employe ID</th>';
        $table .= '<td class="text-left"><div class="col-md-6 no-padding">';
        $table .= '<input type="text" class="form-control input-sm" name="empid" value="'.$staff['staffid'].'" readonly>';
        $table .= '</div></td>';
        //$table .= '<td class="text-left">'.$staff['staffid'].'</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<th>Name</th>';
        $table .= '<td class="text-left">'.$staff['staffname'].'</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<th>Department</th>';
        $table .= '<td class="text-left">'.$dept['depname'].'</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<th>Campus</th>';
        $table .= '<td class="text-left">'.$campus['camname'].'</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<th>Email</th>';
        $table .= '<td class="text-left">'.$email.'</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<td colspan="2" class="text-red"><b>Before you proceed with Signup process, Kindly Check your EMail-ID.If your Email-ID is incorrect.<br /> Please, send the correct E-Mail & Employee ID to Data Centre, ACOE(UDs).</b></td>';
        $table .= '</tr>';
        if($email == FALSE) {
            $table .= '<tr>';
            $table .= '<td colspan="2" class="text-red"><b>Email not found!</b></td>';
            $table .= '</tr>';
        } elseif($this->generate_rand_code($staffid, $email)) {
            $table .= '<tr>';
            $table .= '<td colspan="2" class="text-green"><b>Account creation code (valid for 1 hour) has been mailed to your registered Email(if not, please check in the SPAM). <br />Kindly use it for the signup process.</b></td>';
            $table .= '</tr>';
        } else {
            $table .= '<tr>';
            $table .= '<td colspan="2" class="text-red">Error sending activation code. Contact ACOE (DC)</td>';
            $table .= '</tr>';
        }

        return $table;
    }

    function generate_rand_code($username, $email) {
        $new = TRUE; $random = '';
        $data = array('USERNAME' => $username, 'STATUS' => 1);
        $this->db->select('CODE, XTIMESTAMP');
        $this->db->where($data);
        $query = $this->db->get('REGISTERED_USER');
        if($query->num_rows() > 0) {
            $row = $query->row();
            //echo $row->TIMESTAMP;
            //echo date('U', strtotime($row->TIMESTAMP));
            $time1 = new DateTime(); // string date
            $time2 = new DateTime();
            $time1->setTimestamp(date('U', strtotime($row->XTIMESTAMP)));
            $time2->setTimestamp(date('U')); // timestamps,  it can be string date too.
            $interval =  $time2->diff($time1);
            //Code validaty 1 hour
            $hour = $interval->format("%H");
            if($hour < 1) {
                $random = $row->CODE;
                $new = FALSE;
            }
        }
        if($new) {
            $random = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',8)),0,10);
                
            #invaliating previous random code
            $data = array('USERNAME' => $username);     
            $this->db->set('STATUS', 0);
            $this->db->where($data);
            $this->db->update('REGISTERED_USER');

            $data = array(
                'USERNAME' => $username,
                'CODE' => $random,
                'XTIMESTAMP' => date('d-M-Y h:i:sa'),
                'IPADD' => $_SERVER['REMOTE_ADDR'],
                'HOST' => gethostbyaddr($_SERVER['REMOTE_ADDR']),            
                'STATUS' => 1 );
            $this->db->insert('REGISTERED_USER', $data);
        }

        $this->email
                ->from('admin@acoe.annauniv.edu', 'ACOE')
                ->to($email)
                ->subject('SEMS Activation code to create an account-reg')
                ->message('Dear User, <br /><br /> The activation code to create an account is recevied successfully. <br />Kindly use this code: <font size=3 color=red>'.$random.'</font>
				<br />in the place of activation field in staff registration form. This code is valid for one hour only. <br /><br />Thank you')
                ->set_mailtype('html');
        if($this->email->send()) {
            return TRUE;
        } else
            return FALSE;
    }

    function check_activation_code($username, $code) {
        $data = array('USERNAME' => $username, 'CODE' => $code, 'STATUS' => 1);
        $this->db->select('CODE');
        $this->db->where($data);
        $query = $this->db->get('REGISTERED_USER');
        if($query->num_rows() != 1) {
            return FALSE;
        } return TRUE;
    }

    function add_profile($code, $empid, $sal, $pwd, $intercom, $mobile, $gender) {       

        $email = $this->get_registered_email($empid);

        $random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
        $password = hash('sha512', $pwd.$random_salt);
        $ip = $_SERVER['REMOTE_ADDR'];
        $host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $gender = $gender == 'M' ? 'male': 'female';
        $staff = $this->GM->get_staff_info($empid);

        //adding profile info
        $data = array(
                    'EMPID' => $empid, 
                    'NAME' => $staff['staffname'],
                    'SAL' => $sal, 
                    'INTERCOM_NO' => $intercom, 
                    'MOBILE' => $mobile, 
                    'EMAIL' => $email, 
                    'GENDER' => $gender, 
                    'XTIMESTAMP' => date('d-M-Y h:i:sa'),
                    'IPADD' => $ip,
                    'HOST' => $host );        
        $this->db->insert('STAFF_PERSONAL' ,$data);

        //adding logging info
		$data = array('USERNAME' => $empid);
		$this->db->where($data);
		$query = $this->db->get('STAFF_LOGIN');
		if($query->num_rows() > 0 ) {
			$data = array('USERNAME' => $empid);
			$idata = array(
                    'EMAIL' => $email, 
                    'PASSWORD' => $password,
                    'SALT' => $random_salt,
                    'TIMESTAMP' => date('d-M-Y h:i:sa'),
                    'IPADD' => $ip,
                    'HOST' => $host );
			$this->db->where($data);
			$this->db->update('STAFF_LOGIN', $idata);
		} else {
			$data = array(
                    'USERNAME' => $empid, 
                    'EMAIL' => $email, 
                    'PASSWORD' => $password,
                    'SALT' => $random_salt,
                    'TIMESTAMP' => date('d-M-Y h:i:sa'),
                    'IPADD' => $ip,
                    'HOST' => $host );
			$this->db->insert('STAFF_LOGIN', $data);
		}

        /*activation link
        $random = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',8)),0,20);
        $data = array('USERNAME' => $empid, 'CODE' => $random, 'XTIMESTAMP' => date('d-M-Y h:i:sa'), 'STATUS' => 0);
        $this->db->insert('REGISTERED_USER', $data);

        //sending email
        $actual_link = base_url().'activate/account'.$empid.'/'.$random;
        $this->email
                ->from('admin@acoe.annauniv.edu', 'Admin')
                ->to($email)
                ->subject('User Registration Activation Email')
                ->message("Click this link to activate your account. <a href='" . $actual_link . "'>" . $actual_link . "</a>")
                ->set_mailtype('html');

            if(!$this->email->send()) {
                return FALSE;
            } else {
                return TRUE;
            }
        */

        return TRUE;
    }    

    function is_exist_stud($regno) {
        $data = array('REGNO' => $regno);
        $this->db->select('REGNO');
        $this->db->where($data);
        $query = $this->db->get('STUDENT');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    function already_registered_stud($regno) {
        $data = array('REGNO' => $regno);
        $this->db->select('REGNO');
        $this->db->where($data);
        $query = $this->db->get('STUD_PERSONAL');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    function stud_info($regno) {
        $student = $this->GM->get_student_info($regno);
        $branch = $this->GM->get_branch_info($student['brcode']);
        $dept = $this->GM->get_dept_info($branch['depcode']);
        $campus = $this->GM->get_campus_info($dept['camcode']);

        $table = '<tr>';
        $table .= '<th>Register Number</th>';
        $table .= '<td class="text-left"><div class="col-md-6 no-padding">';
        $table .= '<input type="text" class="form-control input-sm" name="regno" value="'.$regno.'" readonly>';
        $table .= '</div></td>';
        //$table .= '<td class="text-left">'.$staff['staffid'].'</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<th>Name</th>';
        $table .= '<td class="text-left">'.$student['name'].'</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<th>Branch</th>';
        $table .= '<td class="text-left">'.$branch['degshort'].' '.$branch['brname'].' - '.$branch['degmode'].'</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<th>Semester</th>';
        $table .= '<td class="text-left">'.$student['sem'].'</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<th>Department</th>';
        $table .= '<td class="text-left">'.$dept['depname'].'</td>';
        $table .= '</tr>';
        $table .= '<tr>';
        $table .= '<th>Campus</th>';
        $table .= '<td class="text-left">'.$campus['camname'].'</td>';
        $table .= '</tr>';

        return $table;
    }

    function add_profile_stud($regno, $pwd, $data) {
        $random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
        $password = hash('sha512', $pwd.$random_salt);
        $ip = $_SERVER['REMOTE_ADDR'];
        $host = gethostbyaddr($_SERVER['REMOTE_ADDR']);

        //adding profile info
        $data['REGNO'] = $regno;
        $data['XTIMESTAMP'] = date('d-M-Y h:i:sa');
        $data['IPADD'] = $ip;
        $data['HOST'] = $host;
        $this->db->insert('STUD_PERSONAL' ,$data);

        //adding logging info
		
		$wdata = array('USERNAME' => $regno);
		$this->db->where($wdata);
		$query = $this->db->get('STUD_LOGIN');
		if($query->num_rows() > 0) {
			$idata = array(
                    'EMAIL' => $data['EMAIL'],
                    'PASSWORD' => $password,
                    'SALT' => $random_salt,
                    'TIMESTAMP' => date('d-M-Y h:i:sa'),
                    'IPADD' => $ip,
                    'HOST' => $host );
			$this->db->where($wdata);
			$this->db->update('STUD_LOGIN', $idata);
		} else {
			$idata = array(
                    'USERNAME' => $regno, 
                    'EMAIL' => $data['EMAIL'], 
                    'PASSWORD' => $password,
                    'SALT' => $random_salt,
                    'TIMESTAMP' => date('d-M-Y h:i:sa'),
                    'IPADD' => $ip,
                    'HOST' => $host );
			$this->db->insert('STUD_LOGIN', $idata);
		}

        return TRUE;
    }

    function activate_account($username, $code) {
        $data = array('USERNAME' => $username, 'CODE' => $code);
        $this->db->where($data);
        $query = $this->db->get('REGISTERED_USER');
        if($query->num_rows() == 1) {
            $this->db->set('STATUS', 1);
            $this->db->where($data);
            $this->db->update('REGISTERED_USER');
            return TRUE;
        } else return FALSE;
    }
}
