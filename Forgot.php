<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Forgot extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('Auth_model');
                $this->load->library('email');
	}

	public function staff() {
		$this->index('staff');
	}

	public function index($url) {
		$data['error'] = NULL;
		if($url == 'staff') {
			$type = 'F';
			$data['type'] = 'staff';
		}
		else {
			die();
		}
		if($type == 'F'){
			$this->form_validation->set_rules('username','Username','trim|required|xss_clean|callback_validate_user_staff');
		}
		$this->form_validation->set_rules('email','Email','trim|required|xss_clean|valid_email');		
		if ($this->form_validation->run() == FALSE) {
			$data['table'] = NULL;
		}
		else {
			$data['table'] = 1;
			$username = $this->input->post('username');
			$email = $this->input->post('email');
			$this->form_validation->set_rules('code','Code','trim|required|xss_clean');
			$this->form_validation->set_rules('pwd','Password','trim|required|xss_clean');
			$this->form_validation->set_rules('cpwd','Confirm Password','trim|required|xss_clean|callback_validate_pwd');
			if ($this->form_validation->run() == FALSE) {
				$this->Auth_model->generate_rand_code($username, $email, $type);
			} else 
                         {
				$code = $this->input->post('code');
				$pwd = $this->input->post('pwd');
				$cpwd = $this->input->post('cpwd');
				$res = $this->Auth_model->reset_password($username, $type, $code, $pwd);
				if($res === TRUE) {
					$data['error'] = 'Password reset successful';
				} else {
					$data['error'] = $res;
				}
			}
		}
		$this->load->view('auth/forgot_password', $data);
    }

    public function validate_user_staff() {
    	$username = $this->input->post('username');
    	$email = $this->input->post('email');
    	$type = 'F';
    	if($this->Auth_model->valid_user_email($username, $email, $type)) {
    		return TRUE;
    	} else {
    		$this->form_validation->set_message('validate_user_staff', 'Email address does not match with the registered email');
    		return FALSE;
    	}
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
}
