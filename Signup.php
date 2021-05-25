<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Signup extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('Signup_model');
		$this->load->model('Auth_model');
	}

	public function staff() {		
		$this->form_validation->set_rules('empid','Employee ID','trim|required|xss_clean|callback_validate_empid');
		$empid = $this->input->post('empid');
		
		$data['table'] = NULL;
		if ($this->form_validation->run() == FALSE) {
			$data['table'] = NULL;
		}
		else {
			$data['error'] = '';
			$empid = $this->input->post('empid');			
			$this->form_validation->set_rules('code','Activation code','trim|required|xss_clean');
			$this->form_validation->set_rules('salutation','Saltutation','trim|required|xss_clean');
			$this->form_validation->set_rules('pwd','Password','trim|required|xss_clean');
			$this->form_validation->set_rules('cpwd','Confirm Password','trim|required|xss_clean|callback_validate_pwd');
			$this->form_validation->set_rules('intercom','Intercom number','trim|required|xss_clean|integer');
			$this->form_validation->set_rules('mobile','Mobile Number','trim|required|xss_clean|min_length[10]|max_length[10]|integer');
			$this->form_validation->set_rules('gender','Gender','trim|required|xss_clean');
			if ($this->form_validation->run() == FALSE) {
				$data['table'] = $this->Signup_model->staff_info($empid);
			} else {				
				$code = $this->input->post('code');
				$sal = $this->input->post('salutation');
				$pwd = $this->input->post('pwd');
				$intercom = $this->input->post('intercom');
				$mobile = $this->input->post('mobile');
				$gender = $this->input->post('gender');
				if($this->Signup_model->check_activation_code($empid, $code)) {
					$res = $this->Signup_model->add_profile($code, $empid, $sal, $pwd, $intercom, $mobile, $gender);
					if($res) {
						$data['user'] = 'staff';
						$this->load->view('auth/signup_success', $data);
					}
				} else {
					$data['table'] = $this->Signup_model->staff_info($empid);
					$data['error'] = 'Invalid activation code';
				}
			}
		}
		$this->load->view('auth/signup_staff', $data);
    }

    public function validate_pwd() {
    	$pwd = $this->input->post('pwd');
    	$cpwd = $this->input->post('cpwd');
    	if($pwd === $cpwd) {
    		return TRUE;
    	} else {
    		$this->form_validation->set_message('validate_pwd', 'Password and Confirm Password does not match');
    		return FALSE;
    	}
    }

    public function test($empid) {
    	var_dump($this->Signup_model->staff_info($empid));
    }

    public function validate_empid() {
    	$empid = $this->input->post('empid');
    	if(!$this->Signup_model->is_exist($empid)) {
    		$this->form_validation->set_message('validate_empid', 'Invalid Employee ID!');
    		return FALSE;
    	} elseif($this->Signup_model->already_registered($empid)) {
    		$this->form_validation->set_message('validate_empid', 'Your profile already exists in the database. If not, Contact ACOE(UD) Office.');
    		return FALSE;
    	} else return TRUE;
    }

    public function student() {    	
		$this->form_validation->set_rules('regno','Register No','trim|required|xss_clean|callback_validate_regno');
		$regno = $this->input->post('regno');
		
		$data['table'] = NULL;
		if ($this->form_validation->run() == FALSE) {
			$data['table'] = NULL;
		}
		else {
			$regno = $this->input->post('regno');
			//step 1
			$this->form_validation->set_rules('pwd','Password','trim|required|xss_clean');
			$this->form_validation->set_rules('cpwd','Confirm Password','trim|required|xss_clean|callback_validate_pwd');
			$this->form_validation->set_rules('mobile','Mobile Number','trim|required|xss_clean|min_length[10]|max_length[10]|integer');
			$this->form_validation->set_rules('email','Email','trim|required|xss_clean|valid_email');
			$this->form_validation->set_rules('gender','Gender','trim|required|xss_clean');
			$this->form_validation->set_rules('accommodation','Accommodation','trim|required|xss_clean');
			$this->form_validation->set_rules('spl','Specially Abled','trim|required|xss_clean');
			$this->form_validation->set_rules('fgrad','First Graduate','trim|required|xss_clean');

			//step 2
			$this->form_validation->set_rules('pname','Parent\'s Name','trim|required|xss_clean');
			$this->form_validation->set_rules('occupation','Parent\'s Occupation','trim|required|xss_clean');
			$this->form_validation->set_rules('pno','Parent\'s Number','trim|required|xss_clean');

			//step 3
			$this->form_validation->set_rules('pdoor','Door No','trim|required|xss_clean|max_length[10]');
			$this->form_validation->set_rules('pstreet','Stree Name','trim|required|xss_clean|max_length[40]');
			$this->form_validation->set_rules('parea','Area','trim|required|xss_clean|max_length[40]');
			$this->form_validation->set_rules('pcity','City/District','trim|required|xss_clean');
			$this->form_validation->set_rules('ppincode','Pin Code','trim|required|xss_clean');

			//step4
			$this->form_validation->set_rules('cdoor','Door No','trim|required|xss_clean|max_length[10]');
			$this->form_validation->set_rules('cstreet','Stree Name','trim|required|xss_clean|max_length[40]');
			$this->form_validation->set_rules('carea','Area','trim|required|xss_clean|max_length[40]');
			$this->form_validation->set_rules('ccity','City/District','trim|required|xss_clean');
			$this->form_validation->set_rules('cpincode','Pin Code','trim|required|xss_clean');

			if ($this->form_validation->run() == FALSE) {
				$data['table'] = $this->Signup_model->stud_info($regno);
			} else {
				$pwd = $this->input->post('pwd');

				$data = array(
					'MOBILE' => $this->input->post('mobile'),
					'EMAIL' => $this->input->post('email'),
					'GENDER' => $this->input->post('gender') == 'M' ? 'male': 'female',
					'ACCOM' => $this->input->post('accommodation') == 'D' ? 'Day Scholar': 'Hostler',
					'SPL' => $this->input->post('spl') == 'Y' ? 'Yes': 'No',
					'FIRST' => $this->input->post('fgrad') == 'Y' ? 'Yes': 'No',
					'PNAME' => $this->input->post('pname'),
					'PMOBILE' => $this->input->post('pno'),
					'POCCUP' => $this->input->post('occupation'),
					'PDOOR' => $this->input->post('pdoor'),
					'PSTREET' => $this->input->post('pstreet'),
					'PAREA' => $this->input->post('parea'),
					'PCITY' => $this->input->post('pcity'),
					'PCODE' => $this->input->post('ppincode'),
					'CDOOR' => $this->input->post('cdoor'),
					'CSTREET' => $this->input->post('cstreet'),
					'CAREA' => $this->input->post('carea'),
					'CCITY' => $this->input->post('ccity'),
					'CCODE' => $this->input->post('cpincode')
					);
				
				$res = $this->Signup_model->add_profile_stud($regno, $pwd, $data);
				if($res) {
					$data['user'] = 'student';
					$this->load->view('auth/signup_success', $data);
				}
			}
		}
		$this->load->view('auth/signup_student', $data);
    }

    public function validate_regno() {
    	$regno = $this->input->post('regno');
    	if(!$this->Signup_model->is_exist_stud($regno)) {
    		$this->form_validation->set_message('validate_regno', 'Invalid Register number!');
    		return FALSE;
    	} elseif($this->Signup_model->already_registered_stud($regno)) {
    		$this->form_validation->set_message('validate_regno', 'Your profile already exists in the database. If not, Contact ACOE(UD) Office.');
    		return FALSE;
    	} else return TRUE;
    }
}
