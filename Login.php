<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('Auth_model');
	}

	public function index(){
          $this->load->view('staff/lecturer_login');
	}
        
          public function l_hod(){
           $this->load->view('staff/hod_login');
	
        }
		 public function l_principal(){
           $this->load->view('staff/principal_login'); 
        }
     
       
         public function l_lecturer(){
           $this->load->view('staff/lecturer_login'); 
        }
        public function l_print(){

            redirect('Secure_Controller');
       
        }
         public function lecturer() {
        $this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean|callback_check_database_lecturer');

        if ($this->form_validation->run() == FALSE) {
            $this->l_lecturer();

        } else {
             redirect('login/hod');
           
        }
    }
     public function hod() 
        {
		$this->form_validation->set_rules('username','Username','trim|required|xss_clean');		
		$this->form_validation->set_rules('password','Password','trim|required|xss_clean|callback_check_database_hod');

		if ($this->form_validation->run() == FALSE)
                {
                       $this->l_hod();
          // print_r($this->session);
		}
		else 
                {
                     
                   redirect('login/principal');  
		}
	}
	
	 public function principal() {
        $this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean|callback_check_database_principal');

        if ($this->form_validation->run() == FALSE) {
            $this->l_principal();
        } else {
           redirect('login/l_print');
        }
    }

   
         function check_database_lecturer() {
        $name = $this->input->post('username');
        $password = $this->input->post('password');
        $role = 'LECTURER';
        $res = $this->Auth_model->auth1($name, $password, $role);
        if ($res == 0) {
            $this->form_validation->set_message('check_database_lecturer', 'Invalid Username or Password');
            return FALSE;
        } elseif ($res == 1) {
            return TRUE;
        } 
    }
	
	 function check_database_hod() {
        $name = $this->input->post('username');
        $password = $this->input->post('password');
        $role = 'HOD';
        $res = $this->Auth_model->auth1($name, $password, $role);
        if ($res == 0) {
            $this->form_validation->set_message('check_database_hod', 'Invalid Username or Password');
            return FALSE;
        } elseif ($res == 1) {
            return TRUE;
        } 
    }
	
	 function check_database_principal() {
        $name = $this->input->post('username');
        $password = $this->input->post('password');
        $role = 'PRINCIPAL';
        $res = $this->Auth_model->auth1($name, $password, $role);
        if ($res == 0) {
            $this->form_validation->set_message('check_database_principal', 'Invalid Username or Password');
            return FALSE;
        } elseif ($res == 1) {
            return TRUE;
        } 
    }
        
	
	

	function change_password() {
		$this->form_validation->set_rules('current','Current Password','trim|required|xss_clean');
		$this->form_validation->set_rules('newp','New Password','trim|required|xss_clean');
		$this->form_validation->set_rules('confirm','Confirm Password','trim|required|xss_clean|callback_check_update_password');
		if ($this->form_validation->run() == FALSE) {
			
		} else {

		}
		$this->load->view('auth/change_password');
	}

	function check_update_password() {
		$old = $this->input->post('current');
		$newp = $this->input->post('newp');
		$confirm = $this->input->post('confirm');
		if ($newp !== $confirm) {
			$this->form_validation->set_message('check_update_password', 'New Password and Confirm Password does not match');
			return FALSE;
		} else {			
			if($this->Auth_model->change_password($this->session->userdata('username'), $old, $newp)) {
				$this->form_validation->set_message('check_update_password', 'Password updated successfully');
				return FALSE;
			} else {
				$this->form_validation->set_message('check_update_password', 'Old password does not match');
				return FALSE;
			}
		}
	}

	public function logout()
	{
        $staff = $this->session->userdata('is_staff');
        $this->session->sess_destroy();
        delete_cookie(1);
        $this->Auth_model->logout($this->session->userdata('username'));
        if($staff) {        	
        	redirect('login/staff');
        } 
        exit;
    }
    
    public function logoutlecturer()
	{
        $staff = $this->session->userdata('is_staff');
        $this->session->sess_destroy();
        delete_cookie(1);
        $this->Auth_model->logout($this->session->userdata('username1'));
        if($staff) {
                if($this->session->userdata('is_lecturer')==TRUE){            
        	     redirect('login');
                }
        } 
        exit;
    }
    public function logouthod()
	{
        $staff = $this->session->userdata('is_staff');
        $this->Auth_model->logout($this->session->userdata('username2'));
         $data = array('username2' => NULL, 'is_hod' => NULL);
	$this->session->set_userdata($data);
        if($staff) {  
                 if($this->session->userdata('is_lecturer')==TRUE){
                      redirect('login/l_hod');
                      
                 }else{
                     redirect('login');
                 }
        } 
        exit;
    }
    public function logoutprincipal()
	{
        $staff = $this->session->userdata('is_staff');
        $this->Auth_model->logout($this->session->userdata('username3'));
        $data = array('username3' => NULL, 'is_principal' => NULL);
	$this->session->set_userdata($data);
        if($staff) { 
                 if($this->session->userdata('is_lecturer')==TRUE && $this->session->userdata('is_hod')==TRUE) {           
        	      redirect('login/l_principal');
                 }
                 else{
                      redirect('login');
                 }
        } 
        exit;
    }
    
    
}
?>
