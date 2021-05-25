<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Staff extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('Auth_model');
		if($this->Auth_model->is_logged_in('staff')) {
			if(!$this->Auth_model->is_auth_user('staff')) {
				echo $this->load->view('404', '', TRUE);
				die();
			}
		} else {
			redirect('login/staff');
		}

		$this->load->model('General_model', 'GM');
		$this->load->model('Staff_model', 'SM');
		$this->load->model('Module_model', 'MOD');
	}

	public function index() {
        $this->dashboard();
    }

    public function dashboard() {
    	$sesid = $this->MOD->get_write_ses('MA', 'ALL');
		$data['active'] = 'dashboard';
		$data['active2'] = '';
		$data['tt'] = $this->SM->get_tt($sesid);
		$this->load->view('staff/dashboard', $data);
	}

	public function tt() {
    	$sesid = $this->MOD->get_write_ses('MA', 'ALL');
		$data['active'] = 'staff';
		$data['active2'] = 'tt';
		$data['sessions'] = $this->MOD->get_module_option('MA', 'ALL');
		$this->load->view('staff/tt', $data);
	}

    public function get_courses() {
    	$this->form_validation->set_rules('staffid','Staff ID','trim|xss_clean');
    	$this->form_validation->set_rules('session','Session','trim|required|xss_clean');
		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$staffid = $this->input->post('staffid');
			if($staffid == null) $staffid = $this->session->userdata('username');
			$sesid = $this->MOD->decode($this->input->post('session'));
			$result = $this->SM->get_staff_sub($sesid, $staffid);
			$table = $this->GM->array_to_table($result);
			echo $table;
		}
    }

	public function get_tt($url1 = 0, $url2 = null, $url3 = null) {
		$staffid = $url1 == 0 ? $this->session->userdata('username') : $url1;
		$sesid = $url2 == null? $this->MOD->get_write_ses('MA', 'ALL') : $this->MOD->decode($url2);
		if($url3 != null) {
			$branch = $this->GM->get_branch_info($url3);
			$mode = $branch['degmode2'];
			if($url3 == 272 || $url3 == 185) $mode = 'P';
		} else $mode = null;
		$res = $this->SM->get_tt($sesid, $staffid, $mode);
		echo $res;
	}

    public function get_staff_sub() {
    	$this->form_validation->set_rules('session','Session','trim|required|xss_clean');
		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$staffid = $this->session->userdata('username');
			$sesid = $this->MOD->decode($this->input->post('session'));
			$staff_sub = $this->SM->get_staff_sub($sesid);
			if($staff_sub != FALSE) {
				$table = $this->GM->array_to_table($staff_sub);
			} else
				$result = array('result' => FALSE, 'msg' => 'No subjects found for selected session!');
			echo json_encode($result);
		}
    }

	public function staff_slist() {
    	$sesid = $this->MOD->get_write_ses('MA', 'ALL');
		$data['active'] = 'staff';
		$data['active2'] = 'slist';
		$data['sessions'] = $this->MOD->get_module_option('MA', 'ALL');
		$this->load->view('staff/students', $data);
	}

    public function sessional($url = 0) {
    	$data['active'] = 'staff';
    	$data['active2'] = 'mark';
		if($url === 0) {
			$sesid = $this->MOD->get_write_ses('MA', 'ALL');
			//$data['scount'] = $this->SM->get_sub_and_count($sesid);
			$data['sessions'] = $this->MOD->get_module_option('MA', 'ALL');
			$this->load->view('staff/courses', $data); 
		} else {
    	$markid = $this->MOD->decode($url);
		if(!$this->SM->is_auth_markid($markid)) {
    		echo $this->load->view('404', '', TRUE);
			die();
		}
		$data['sessional'] = $this->SM->get_mark_sessional($markid);
		if($data['sessional'] === FALSE) {
    		echo $this->load->view('disabled', '', TRUE);
			die();		
		}
		$data['sdetail'] = $this->SM->markid_details($markid);
		$data['ses_locked'] = $this->SM->ses_lock_status($markid) > 0 ? TRUE: FALSE;
		$data['att_locked'] = $this->SM->ses_lock_status($markid);
    	$data['markid'] = $this->MOD->encode($markid);
        $data['staff'] = $this->GM->get_staff_info($this->session->userdata('username'));
        $this->load->view('staff/sessional_weightage', $data); 
    	}
    }

    public function course($url) {
    	$sesid = $this->MOD->decode($url);
    	$res = $this->SM->get_sub_and_count($sesid);
    	echo json_encode($res);
    }

    public function revaluation_course($url) {
    	$sesid = $this->MOD->decode($url);
    	$res = $this->SM->get_rev_sub_and_count($sesid);
    	echo json_encode($res);
    }

    public function mark($url = 0) {
    	$markid = $this->MOD->decode($url);
    	if(!$this->SM->is_auth_markid($markid)) {
    		echo $this->load->view('404', '', TRUE);
			die();
		}
		$data['sessional'] = $this->SM->get_mark_sessional($markid);
		if($data['sessional'] === FALSE) {
    		echo $this->load->view('404', '', TRUE);
			die();
		}
		if($this->SM->ses_lock_status($markid) < 1) {
			echo $this->load->view('disabled', '', TRUE);
			die();
		}
		#custom selection
		if($data['sessional'] == 4) {
			$custom = $this->SM->get_ses_custom($markid);
			if($custom['ass1'] == 1) $data['sessional']--;
			if($custom['ass2'] == 1) $data['sessional']--;
			if($custom['ass3'] == 1) $data['sessional']--;
		}
		$count = $this->SM->staff_subject_stud_count($markid);
		if($count >= 30) {
			$data['custom_grade'] = TRUE;
		} else $data['custom_grade'] = TRUE;
		$data['ass1'] = $this->SM->get_max_att($markid, 'att1');
		$data['ass2'] = $this->SM->get_max_att($markid, 'att2');
		$data['ass3'] = $this->SM->get_max_att($markid, 'att3');
		$data['active'] = 'staff';
    	$data['active2'] = 'mark';
    	$data['markid'] = $this->MOD->encode($markid);
		$data['sdetail'] = $this->SM->markid_details($markid);
    	$data['staff'] = $this->GM->get_staff_info($this->session->userdata('username'));
        $this->load->view('staff/marks_view', $data);
    }
	
	public function assess_pdf($url, $ass) {
		$url = $this->MOD->decode($url);
		if(!$this->SM->is_auth_markid($url)) {
    		echo $this->load->view('404', '', TRUE);
			die();
		}
		$this->SM->assess_pdf($url, $ass, $this->MOD->get_write_ses('MA', 'ALL'));
	}

	public function att_pdf($url) {
		$url = $this->MOD->decode($url);
		if(!$this->SM->is_auth_markid($url)) {
    		echo $this->load->view('404', '', TRUE);
			die();
		}
		$this->SM->attendance_pdf($url, $this->MOD->get_write_ses('MA', 'ALL'));
	}

    public function update($url) {
    	$markid = $this->MOD->decode($url);
    	if(!$this->SM->is_auth_markid($markid)) {
    		if($this->session->userdata('username') != 61602) {
    			echo $this->load->view('404', '', TRUE);
				die();
			}
    	}
    	if($this->SM->ses_lock_status($markid) < 1) {
			echo $this->load->view('disabled', '', TRUE);
			die();
		}    	
    	$data['active'] = 'staff';
    	$data['active2'] = 'mark';
    	$data['markid'] = $this->MOD->encode($markid);
    	$data['sdetail'] = $this->SM->markid_details($markid);
        $data['staff'] = $this->GM->get_staff_info($this->session->userdata('username'));
        $this->load->view('staff/marks_update', $data);
    }

    public function ac($url) {
    	if($this->Auth_model->is_auth_user('ac') == false) {
    		echo $this->load->view('404', '', TRUE);
    		//die();
    	}
    	$data['active'] = 'slot';
    	$this->load->model('Slot_model', 'Slot_model');    	
    	$data['sessions'] = $this->MOD->get_module_option('AC', 'ALL');
    	if($url == 'slot_creation') {
    		$data['active2'] = 'sc';
    		$data['slot_code'] = $this->Slot_model->get_code_option();
    		$this->load->view('tt/slot_creation', $data);
    	}elseif($url == 'slot_allocation') {
    		$data['active2'] = 'sa';
    		$data['campus'] = $this->Slot_model->get_campus();
    		$this->load->view('tt/slot_allocation', $data);
    	} else {
    		echo $this->load->view('404', '', TRUE);
			die();
    	}
    }
    
    
    public function fa($url = 0) {
    	$data['active'] = 'fa';
    	if($url == 0) {
    		$data['slist'] = $this->SM->get_fa_slist($this->session->userdata('username'));
    		$this->load->view('view_fa', $data);
    	} else {
    		$regno = $url;
    		$this->load->model('Enrollment_model');
			$data = $this->Enrollment_model->get_contact_info($regno);
    		$data['student'] = $this->GM->get_student_info($regno);
    		$data['sessions'] = $this->MOD->get_student_module_option($regno);
    		$this->load->view('view_mark_stud', $data);
    	}
    }
 public function review_page($url = 0) {
    	$data['active'] = 'review_page';
    	if($url == 0) {
    		$data['slist'] = $this->SM->get_fa_slist($this->session->userdata('username'));
    		$this->load->view('admin/review/fa_review_page', $data);
    	} else {
    		$regno = $url;
    		$this->load->model('Enrollment_model');
			$data = $this->Enrollment_model->get_contact_info($regno);
    		$data['student'] = $this->GM->get_student_info($regno);
    		$data['sessions'] = $this->MOD->get_student_module_option($regno);
    		$this->load->view('admin/review/view_mark_stud', $data);
    	}
    }

    public function gui($url = 0) {
    	$data['active'] = 'fa';
    	if($url == 0) {
    		$data['slist'] = $this->SM->get_fa_slist($this->session->userdata('username'));
    		$this->load->view('view_fa', $data);
    	} else {
    		$regno = $url;
    		$sem = 1;
    		$this->load->model('Enrollment_model', 'EM');
			$data = $this->EM->get_contact_info($regno);
    		$data['student'] = $this->GM->get_student_info($regno);
    		$data['sessions'] = $this->MOD->get_student_module_option($regno);
    		$data['semester'] = $sem;
    		$data['bargraph'] = $this->EM->get_gui_data($regno, $sem);
    		$this->load->view('student/gui', $data);
    	}
    }

    public function fa_send_report() {
    	$this->form_validation->set_rules('email','Email','trim|required|xss_clean');
    	$this->form_validation->set_rules('regno','Rollno','trim|required|xss_clean');
    	$this->form_validation->set_rules('session','Session','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$email = $this->input->post('email');
			$regno = $this->input->post('regno');
			$sesid = $this->MOD->decode($this->input->post('session'));			
			if($this->SM->mail_student_records($regno, $sesid, $email)) {
				echo json_encode(array('result' => 'true'));
			} else {
				echo json_encode(array('result' => 'false'));
			}

		}
    }

    public function hod($url) {
		$this->load->model('TT_model', 'TT');
		$this->load->model('Prevented_model', 'PM');
    	$data['active'] = 'hod';
    	$data['sessions'] = $this->MOD->get_module_option('MA', 'ALL');
    	$data['depcode'] = $this->session->userdata('depcode');
    	$dept = $this->GM->get_dept_info($data['depcode']);
    	$data['depname'] = $dept['depname'];
    	$data['admin'] = FALSE;
		if($url == 'report') {
			$data['active2'] = 'report';
			$this->load->view('hod/report', $data);
			
		} elseif($url == 'prevention') {
			$data['active2'] = 'prevention';
			$this->load->view('hod/prevention', $data);
		} else {
			echo $this->load->view('404', '', TRUE);
			die();
		}
	}

    public function report_status() {
    	$this->form_validation->set_rules('branch','Branch','trim|required|xss_clean');
    	$this->form_validation->set_rules('sem','Semester','trim|required|xss_clean');
    	$this->form_validation->set_rules('session','Session','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$brcode = $this->input->post('branch');
			$sem = $this->input->post('sem');
			$sesid = $this->MOD->decode($this->input->post('session'));
			$result = $this->SM->get_report_status($brcode, $sem, $sesid);
			echo json_encode($result);
		}
    }
	
	 public function report_all_status() {
    	$this->form_validation->set_rules('branch','Branch','trim|required|xss_clean');
    	$this->form_validation->set_rules('sem','Semester','trim|required|xss_clean');
    	$this->form_validation->set_rules('session','Session','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$brcode = $this->input->post('branch');
			$sem = $this->input->post('sem');
			$sesid = $this->MOD->decode($this->input->post('session'));
			$result = $this->SM->get_report_all_status($brcode, $sem, $sesid);
			echo json_encode($result);
		}
    }

    public function get_report($branch, $sem, $session, $type) {    	
    	$sesid = $this->MOD->decode($session);
    	switch($type) {
    		case 'stud': $this->SM->student_subject_pdf($branch, $sesid, $sem); break;
    		case 'cumi': $this->SM->cumulative_ass_pdf($branch, $sesid, $sem); break;
    		case 'result': $this->SM->result_sheet_pdf($branch, $sesid, $sem, TRUE); break;
    		case 'processed': $this->SM->result_sheet_pdf($branch, $sesid, $sem, TRUE); break;
    		default: return array('result' => 'false', 'msg' => 'Invalid report request');
    	}		
	}

    public function prevention() {
    	$this->load->model('TT_model', 'TT');
    	$data['active'] = 'staff';
    	$data['active2'] = 'prevention';
    	$data['sessions'] = $this->MOD->get_module_option('MA', 'ALL');
    	$data['depcode'] = $this->session->userdata('depcode');
    	$dept = $this->GM->get_dept_info($data['depcode']);
    	$data['depname'] = $dept['depname'];
    	$this->load->view('staff/prevention', $data);
    }

    public function prevention_status() {
    	$this->form_validation->set_rules('staffid','Staff ID','trim|required|xss_clean');
    	$this->form_validation->set_rules('session','Session','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$staffid= $this->input->post('staffid');
			$sesid = $this->MOD->decode($this->input->post('session'));
			$result = $this->SM->get_prevented_list_status($staffid, $sesid);
			echo json_encode($result);
		}
    }


    public function prevention_slist() {
    	$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
    	$this->form_validation->set_rules('session','Session','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$markid = $this->MOD->decode($this->input->post('id'));
			$sesid = $this->MOD->decode($this->input->post('session'));
			$result = $this->SM->prevented_student_list($markid, $sesid);
			echo json_encode($result);
		}
    }

    public function prevention_allow_student() {
    	$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
    	$this->form_validation->set_rules('regno','Regno','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$markid = $this->MOD->decode($this->input->post('id'));
			$regno = $this->input->post('regno');
			$result = $this->SM->prevention_allow($regno, $markid);
			echo json_encode($result);
		}
    }

    public function prevention_reject_student() {
    	$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
    	$this->form_validation->set_rules('regno','Regno','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$markid = $this->MOD->decode($this->input->post('id'));
			$regno = $this->input->post('regno');
			$result = $this->SM->prevention_reject($regno, $markid);
			echo json_encode($result);
		}
    }

    public function branch_handled() {
    	$this->form_validation->set_rules('ccode','Course Code','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$subcode = $this->input->post('ccode');
			$result = $this->SM->get_branch($subcode);
			echo json_encode($result);
		}
    }    

    public function subject_details() {
    	$this->form_validation->set_rules('ccode','Course Code','trim|required|xss_clean');
    	$this->form_validation->set_rules('brcode','Branch Code','trim|required|xss_clean');
    	$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
    	$this->form_validation->set_rules('flag','Flag','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$subcode = $this->input->post('ccode');
			$brcode = $this->input->post('brcode');
			$markid = $this->MOD->decode($this->input->post('id'));
			$flag = $this->input->post('flag');			
			$ass = $this->input->post('ass');
			$att = $this->input->post('att');
			for($i = 0; $i < 7; $i++) {
				$asses[$i] = 0;
				$attend[$i] = 0;
			}
			if(isset($ass)) {				
				foreach($ass as $a) {
					$asses[$a - 1] = 1;
				}
			}

			if(isset($att)) {
				foreach ($att as $a) {
					$attend[$a - 1] = 1;
				}
			}

			$sesid = $this->MOD->get_write_ses('MA', 'ALL');
			$result = $this->SM->offered_subject_details($sesid, $subcode, $brcode, $markid, $asses, $attend, $flag);
			$result['grade'] = $this->SM->grade_count($markid);
			echo json_encode($result);
		}
    }

    public function subject_slot() {
    	$this->form_validation->set_rules('ccode','Course Code','trim|required|xss_clean');
    	$this->form_validation->set_rules('brcode','Branch Code','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$subcode = $this->input->post('ccode');
			$brcode = $this->input->post('brcode');
			$result = $this->SM->offered_slot($subcode, $brcode);
			echo json_encode($result);
		}
    }

    public function slist() {
		$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
    	if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$markid = $this->MOD->decode($this->input->post('id'));
			$res = $this->SM->get_slist_tt($markid);
			echo json_encode($res);
		}
	}

	public function slist_pdf($url = 0) {
		$markid = $this->MOD->decode($url);
		if($markid == 0 || !($this->SM->is_valid_markid($markid))) {
			echo $this->load->view('404', '', TRUE);
			die();
		}
		$this->SM->slist_pdf($markid);
	}

	public function tt_pdf($url = 0) {
		if($url == 0)
			$sesid = $this->MOD->get_write_ses('MA', 'ALL');
		else
			$sesid = $this->MOD->decode($url);
		$this->SM->tt_pdf($sesid);
	}

	public function mark_pdf($url) {
		$url = $this->MOD->decode($url);
		if(!$this->SM->is_auth_markid($url)) {
    		echo $this->load->view('404', '', TRUE);
			die();
		}
		$this->SM->mark_pdf($url, $this->MOD->get_write_ses('MA', 'ALL'));
	}

	public function course_grade_pdf($url) {
		$url = $this->MOD->decode($url);
		if(!$this->SM->is_auth_markid($url)) {
    		echo $this->load->view('404', '', TRUE);
			die();
		}
		$this->SM->course_grade_pdf($url);
	}

	public function get_soptions() {
		$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
    	$this->form_validation->set_rules('type','Type','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$markid = $this->MOD->decode($this->input->post('id'));
			$type = $this->input->post('type');
                        //echo $markid;
			$result = $this->SM->get_soptions($markid, $type);
			echo json_encode($result);
		}
	}

    public function edit_mark() {
    	$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
    	$this->form_validation->set_rules('type','Type','trim|required|xss_clean');
    	$this->form_validation->set_rules('option','Column','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$markid = $this->MOD->decode($this->input->post('id'));
			$type = $this->input->post('type');
			$column = $this->input->post('option');
			$result = $this->SM->edit_mark_form($markid, $type, $column);
			echo json_encode($result);
		}
    }

    public function lock_mark() {
    	$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
    	$this->form_validation->set_rules('type','Type','trim|required|xss_clean');
    	if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$markid = $this->MOD->decode($this->input->post('id'));
			$type = $this->input->post('type');
			$result = $this->SM->lock_attstatus($markid, $type);
			echo json_encode($result);
		}
    }

    public function unlock_mark() {
    	$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
    	$this->form_validation->set_rules('type','Type','trim|required|xss_clean');
    	if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$markid = $this->MOD->decode($this->input->post('id'));
			$type = $this->input->post('type');
			$result = $this->SM->unlock_attstatus($markid, $type);			
			echo json_encode($result);
		}
    }

    public function update_mark() {    	
    	$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
    	$this->form_validation->set_rules('type','Type','trim|required|xss_clean');
    	$this->form_validation->set_rules('option','Column','trim|required|xss_clean');
    	$this->form_validation->set_rules('val[]','Values','trim|xss_clean|');
    	$type = $this->input->post('type');
    	if($type == 'att') {
    		$this->form_validation->set_rules('val[]','Values','callback_validation_att');
    	} else {
    		$this->form_validation->set_rules('val[]','Values','callback_validation_ass');
    	}
    	 	
    	if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$markid = $this->MOD->decode($this->input->post('id'));
			$type = $this->input->post('type');
			$column = $this->input->post('option');
			$val = $this->input->post('val');			
			$result = $this->SM->update_mark($markid, $type, $column, $val);
			echo json_encode($result);
		}		
    }
	
    
    // callback function
    public function validation_ass($str) {
    	if ($str == '' || $str == 'A' || ($str > -1 && $str < 101)) {
    		return TRUE;
    	} else {
    		$this->form_validation->set_message('validation_ass', 'Accepted values: A, 0 - 100');
    		return FALSE;
    	}
    }

    // callback function
    public function validation_att($str) {
    	$markid = $this->MOD->decode($this->input->post('id'));
    	$column = $this->input->post('option');
    	$max = $this->SM->get_max_att($markid, $column);
    	if ($str == '' || ($str > -1 && $str <= $max)) {
    		return TRUE;
    	} else {
    		$this->form_validation->set_message('validation_att', 'Accepted values: 0 - '.$max);
    		return FALSE;
    	}
    }

    // callback function
    public function validation_custom($str) {
    	$markid = $this->MOD->decode($this->input->post('id'));
    	$tass = $this->input->post('ass1');
    	$tass += $this->input->post('ass2');
    	$tass += $this->input->post('ass3'); 
    	$max = $this->SM->get_ses_weightage($markid, 'ses');
    	if ($tass == $max) {
    		return TRUE;
    	} else {
    		$this->form_validation->set_message('validation_custom', 'Custom Assessment total must be '.$max);
    		return FALSE;
    	}
    }

	public function test($url = 0) {
		if($url == 0) {
			echo substr(str_shuffle(str_repeat('0123456789',8)),0,10);
			echo '<br>';
			echo mt_rand(1000000000, 9999999999);
		}
		else
		$this->SM->course_grade_pdf($url);
	}

	public function test1() {
		$this->EM->getimage();
		$ext = 'JPEG';
        $this->output->set_header('Content-type: image/'.$ext);
        echo $rec;
	}

	public function test2($markid) {/*
    	if(!$this->SM->is_auth_markid($markid)) {
    		echo $this->load->view('403', '', TRUE);
			die();
		}*/
		$data['sessional'] = $this->SM->get_mark_sessional($markid);
		if($data['sessional'] === FALSE) {
    		echo $this->load->view('404', '', TRUE);
			die();
		}
		if($this->SM->ses_lock_status($markid) < 1) {
			echo $this->load->view('disabled', '', TRUE);
			die();
		}
		#custom selection
		if($data['sessional'] == 4) {
			$custom = $this->SM->get_ses_custom($markid);
			if($custom['ass1'] == 1) $data['sessional']--;
			if($custom['ass2'] == 1) $data['sessional']--;
			if($custom['ass3'] == 1) $data['sessional']--;
		}
		$count = $this->SM->staff_subject_stud_count($markid);
		if($count >= 30) {
			$data['custom_grade'] = TRUE;
		} else $data['custom_grade'] = TRUE;
		$data['ass1'] = $this->SM->get_max_att($markid, 'att1');
		$data['ass2'] = $this->SM->get_max_att($markid, 'att2');
		$data['ass3'] = $this->SM->get_max_att($markid, 'att3');
		$data['active'] = 'staff';
    	$data['active2'] = 'mark';
    	$data['markid'] = $this->MOD->encode($markid);
		$data['sdetail'] = $this->SM->markid_details($markid);
    	$data['staff'] = $this->GM->get_staff_info($this->session->userdata('username'));
        $this->load->view('staff/marks_view', $data);
    
	}

	
	public function accept_mark_sessional() {
		$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
    	$this->form_validation->set_rules('selection','Selection','trim|required|xss_clean');
    	if ($this->form_validation->run() == FALSE){
			echo validation_errors();			
		} else {			
			$markid = $this->MOD->decode($this->input->post('id'));
			$selection = $this->input->post('selection');
			//custom selection value is 4
			if($selection == 4) {
				$this->form_validation->set_rules('ass1','Assessment 1','trim|required|xss_clean|callback_validation_custom');
				$this->form_validation->set_rules('ass2','Assessment 2','trim|required|xss_clean');
				$this->form_validation->set_rules('ass3','Assessment 3','trim|required|xss_clean');
				if ($this->form_validation->run() == FALSE){
					echo validation_errors();
				} else {
					$ass[0] = $this->input->post('ass1');
					$ass[1] = $this->input->post('ass2');
					$ass[2] = $this->input->post('ass3');
					if($ass[0] == 0) {
						$ass[0] = $ass[1];
						$ass[1] = $ass[2];
						$ass[2] = 0;
					} elseif($ass[1] == 0) {
						$ass[1] = $ass[2];
						$ass[2] = 0;
					}
					$res = $this->SM->accept_mark_sessional($markid, $selection, $ass);
					echo json_encode($res);
				}
			} else {
				$res = $this->SM->accept_mark_sessional($markid, $selection, NULL);
				echo json_encode($res);
			}
		}
	}

	public function decline_sessional() {
    	$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
    	$this->form_validation->set_rules('reason','Reason','trim|required|xss_clean|max_length[500]');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$markid = $this->MOD->decode($this->input->post('id'));
			$reason = $this->input->post('reason');

			$result = $this->SM->decline_mark_sessional($markid, $reason);
			echo json_encode($result);
		}
    }

    public function lock_ses_att() {
    	$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
    	$this->form_validation->set_rules('att','Att','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$markid = $this->MOD->decode($this->input->post('id'));
			$att = $this->input->post('att');

			$result = $this->SM->lock_att_sessional($markid, $att);
			echo json_encode($result);
		}
    }

    public function update_attendance() { 
    	$this->form_validation->set_rules('id','id','trim|required|xss_clean');
    	$this->form_validation->set_rules('att1','Att 1','trim|xss_clean');
    	$this->form_validation->set_rules('att2','Att 2','trim|xss_clean');
    	$this->form_validation->set_rules('att3','Att 3','trim|xss_clean');
    	if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$this->load->model('TT_model', 'TT');
			$markid = $this->MOD->decode($this->input->post('id'));
			$mdetail = $this->SM->markid_details($markid);			
			$subject = $this->GM->get_subject_info($mdetail['subcode']);
			$att[0] = $this->input->post('att1');
			$att[1] = $this->input->post('att2');
			$att[2] = $this->input->post('att3');
			$tatt = $att[0] + $att[1] + $att[2];
			
			if($subject['type'] == 'THEORY' || $subject['type'] == 'LAB') {
				$max = 80;
			} else $max = 500;

			if($tatt > $max) {
				echo 'Total attendance must be less than or equal to '.$max;
				return;
			}
			$result = $this->SM->update_attendance($markid, $att, $tatt);
			$result['total'] = $tatt;
			echo json_encode($result);
		}		
    }
	
	function cumulative_prevention($depcode = 0) {
		if($depcode === 0) $depcode = $this->session->userdata('depcode');
		$highersem = TRUE;
		$this->SM->hod_prevention_list_pdf($depcode, $highersem);
	}

	function prev_status($depcode) {
		if($depcode === 0) $depcode = $this->session->userdata('depcode');
		$higher_sem = $this->PM->prevention_lock_count($depcode, TRUE);		
		$lower_sem = $this->PM->prevention_lock_count($depcode, FALSE);		
		echo '<strong>Higher Sem: Prevention Status: Total no. of Courses - '.$higher_sem['den'].' Current locked status - '.$higher_sem['num'].'</strong><br/>';
	}

	public function roles() {
		/*
		if($this->session->userdata('username') != 61602) {
			echo $this->load->view('maintenance', '', TRUE);
			die();
		}*/
		if(!$this->Auth_model->is_auth_user('hod')) {
			echo $this->load->view('404', '', TRUE);
			die();
		}
    	$this->load->model('TT_model', 'TT');
    	$data['active'] = 'hod';
    	$data['active2'] = 'roles';
    	$data['depcode'] = $this->session->userdata('depcode');
    	$dept = $this->GM->get_dept_info($data['depcode']);
    	$data['depname'] = $dept['depname'];
    	$data['sessions'] = $this->MOD->get_module_option('TT', 'ALL');
    	$data['admin'] = FALSE;
    	$this->load->view('auth/assign_roles', $data);
    }

    public function get_roles(){
		$this->form_validation->set_rules('depcode','Department','trim|required|xss_clean');
		$this->form_validation->set_rules('session','Session','trim|required|xss_clean');
		$this->form_validation->set_rules('role','Role','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$sesid = $this->MOD->decode($this->input->post('session'));
			$depcode = $this->input->post('depcode');
			$role = $this->input->post('role');
			$res = $this->SM->get_roles_all($depcode, $role, $sesid);
			echo json_encode($res);
		}
	}

	public function edit_role(){
		$this->form_validation->set_rules('depcode','Department','trim|required|xss_clean');
		$this->form_validation->set_rules('session','Session','trim|required|xss_clean');
		$this->form_validation->set_rules('role','Role','trim|required|xss_clean');
		$this->form_validation->set_rules('staffid','Staffid','trim|required|xss_clean');
		$this->form_validation->set_rules('type','Type','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$depcode = $this->input->post('depcode');
			$staffid = $this->input->post('staffid');
			$role = $this->input->post('role');
			$sesid = $this->MOD->decode($this->input->post('session'));
			$type = $this->input->post('type');
			if($type == 'assign-role') {
				$res = $this->SM->assign_role($depcode, $staffid, $role, $sesid);
			} elseif($type == 'unassign-role') {
				$res = $this->SM->unassign_role($depcode, $staffid, $role, $sesid);
			} else {
				$res = array('result' => FALSE, 'msg' => 'Invalid type!');
			}
			echo json_encode($res);
		}
	}

	public function fa_list() {
		$this->form_validation->set_rules('brcode','Branch','trim|required|xss_clean');
		$this->form_validation->set_rules('sem','Semester','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$brcode = $this->input->post('brcode');
			$sem = $this->input->post('sem');
			$res = $this->SM->fa_unassigned_slist($brcode, $sem);
			echo json_encode($res);
		}
	}

	public function fa_assign_list() {
		$this->form_validation->set_rules('brcode','Branch','trim|required|xss_clean');
		$this->form_validation->set_rules('sem','Semester','trim|required|xss_clean');
		$this->form_validation->set_rules('faid','Faculty Advisor','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$brcode = $this->input->post('brcode');
			$sem = $this->input->post('sem');
			$faid = $this->input->post('faid');
			$res = $this->SM->fa_assigned_slist($faid);
			echo json_encode($res);
		}
	}

	public function assign_fa() {
		$this->form_validation->set_rules('branch','Branch','trim|required|xss_clean');
		$this->form_validation->set_rules('sem','Semester','trim|required|xss_clean');
		$this->form_validation->set_rules('faid','Faculty Advisor','trim|required|xss_clean');
		$this->form_validation->set_rules('slist[]','Student List','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$brcode = $this->input->post('branch');
			$sem = $this->input->post('sem');
			$staffid = $this->input->post('faid');
			$slist = $this->input->post('slist');
			$res = $this->SM->assign_fa($brcode, $staffid, $slist);
			echo json_encode($res);
		}
	}

	public function unassign_fa() {
		$this->form_validation->set_rules('regno','Register No','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$regno = $this->input->post('regno');
			$res = $this->SM->unassign_fa($regno);
			echo json_encode($res);
		}
	}
	
	public function foil_sheet($url = 0) {
		if($url === 0) {
			echo $this->load->view('404', '', TRUE);
			die();
		} else {
			$markid = $this->MOD->decode($url);
			$this->SM->foil_sheet_pdf($markid);
		}
	}

	public function calculate_result() {
		$this->form_validation->set_rules('id','ID','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$markid = $this->MOD->decode($this->input->post('id'));			
			$mdetail = $this->SM->markid_details($markid);
			if($mdetail['staffid'] != $this->session->userdata('username')) {
				if($this->session->userdata('username') != 61602) {
					echo json_encode(array('result' => FALSE, 'msg' => 'Unauthorized ID'));
					return;
				}
			}
			echo json_encode($this->SM->calculate_result($markid));
		}
	}

	public function campus() {
		$this->form_validation->set_rules('session','Session','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$sesid = $this->MOD->decode($this->input->post('session'));
			$res = $this->SM->get_sublink_campus($sesid);
			echo $res;
		}
	}

	public function prgm2() {
		$this->form_validation->set_rules('camcode','Campus','trim|required|xss_clean');
		$this->form_validation->set_rules('session','Session','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$camcode = $this->input->post('camcode');
			$sesid = $this->MOD->decode($this->input->post('session'));
			$res = $this->SM->get_sublink_prgm2($camcode, $sesid);
			echo $res;
		}
	}

	public function dept2() {
		$this->form_validation->set_rules('camcode','Campus','trim|required|xss_clean');
		$this->form_validation->set_rules('prgm','PRGM','trim|required|xss_clean');
		$this->form_validation->set_rules('session','Session','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$camcode = $this->input->post('camcode');
			$prgm  = $this->input->post('prgm');
			$sesid = $this->MOD->decode($this->input->post('session'));
			$res = $this->SM->get_sublink_dept2($camcode, $prgm, $sesid);
			echo $res;
		}
	}

	public function dept() {
		$this->form_validation->set_rules('camcode','Campus','trim|required|xss_clean');
		$this->form_validation->set_rules('session','Session','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$camcode = $this->input->post('camcode');			
			$sesid = $this->MOD->decode($this->input->post('session'));
			$res = $this->SM->get_sublink_dept($camcode, $sesid);
			echo $res;
		}
	}

	public function prgm() {
		$this->form_validation->set_rules('depcode','Department','trim|required|xss_clean');
		$this->form_validation->set_rules('session','Session','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$depcode = $this->input->post('depcode');
			$sesid = $this->MOD->decode($this->input->post('session'));
			$res = $this->SM->get_sublink_prgm($depcode, $sesid);
			echo $res;
		}
	}
	
	public function branch() {
		$this->form_validation->set_rules('depcode','Department','trim|required|xss_clean');
		$this->form_validation->set_rules('prgm','PRGM','trim|xss_clean');
		$this->form_validation->set_rules('session','Session','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {			
			$depcode = $this->input->post('depcode');
			$prgm  = $this->input->post('prgm');
			$sesid = $this->MOD->decode($this->input->post('session'));
			$branch = $this->SM->get_sublink_branch($depcode, $sesid, $prgm);
			echo $branch;
		}
	}
	
	public function sem() {
		$this->form_validation->set_rules('brcode','Branch','trim|required|xss_clean');
		$this->form_validation->set_rules('session','Session','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$brcode = $this->input->post('brcode');
			$sesid = $this->MOD->decode($this->input->post('session'));
			$sem = $this->SM->get_sublink_sem($brcode, $sesid);
			echo $sem;
		}
	}
	
	public function student_sem() {
		$this->form_validation->set_rules('brcode','Branch','trim|required|xss_clean');
		$this->form_validation->set_rules('session','Session','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$brcode = $this->input->post('brcode');
			$sesid = $this->MOD->decode($this->input->post('session'));
			$sem = $this->SM->get_register_sem($brcode, $sesid);
			echo $sem;
		}
	}

	public function custom($url = 0) {
		$markid = $this->MOD->decode($url);
    	if(!$this->SM->is_auth_markid($markid)) {
    		echo $this->load->view('404', '', TRUE);
			die();
		}
		$data['sessional'] = $this->SM->get_mark_sessional($markid);
		if($data['sessional'] === FALSE) {
    		echo $this->load->view('404', '', TRUE);
			die();
		}
		if($this->SM->ses_lock_status($markid) < 1) {
			echo $this->load->view('disabled', '', TRUE);
			die();
		}
		$count = $this->SM->staff_subject_stud_count($markid);
		if($count >= 0) {
			//echo $this->load->view('disabled', '', TRUE);
			//die();
		}

		$data['active'] = 'staff';
    	$data['active2'] = 'mark';
    	$data['markid'] = $this->MOD->encode($markid);
		$data['sdetail'] = $this->SM->markid_details($markid);
    	$data['staff'] = $this->GM->get_staff_info($this->session->userdata('username'));
    	$data['mean'] = $this->SM->get_mean($markid);
    	if($this->SM->is_attlocked($markid,'result')) $data['locked'] = TRUE;
    	else $data['locked'] = $this->SM->is_grade_range_locked($markid);
        $this->load->view('staff/custom_grade', $data);
	}

	public function custom_grade() {
		$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
		$this->form_validation->set_rules('omin','Mininum value','trim|required|xss_clean|greater_than[0]|callback_validation_custom_grade');
		$this->form_validation->set_rules('apmin','Mininum value','trim|required|xss_clean|greater_than[0]');
		$this->form_validation->set_rules('amin','Mininum value','trim|required|xss_clean|greater_than[0]');
		$this->form_validation->set_rules('bpmin','Mininum value','trim|required|xss_clean|greater_than[0]');
		//$this->form_validation->set_rules('bmin','Mininum value','trim|required|xss_clean|greater_than[0]');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$markid = $this->MOD->decode($this->input->post('id'));
			$rec['O'] = $this->input->post('omin');
    		$rec['AP'] = $this->input->post('apmin');
    		$rec['A'] = $this->input->post('amin');
    		$rec['BP'] = $this->input->post('bpmin');
    		$rec['B'] = $this->input->post('bmin');

			echo json_encode($this->SM->custom_grade_range($markid, $rec));
		}   	
    }

    public function validation_custom_grade($str) {
    	$omin = $this->input->post('omin');
    	$apmin = $this->input->post('apmin');
    	$amin = $this->input->post('amin');
    	$bpmin = $this->input->post('bpmin');
    	$bmin = $this->input->post('bmin');

    	if($omin <= $apmin) {
    		$this->form_validation->set_message('validation_custom_grade', 'O Minimum value must be greater than A+ Minimum value');
    		return FALSE;
    	}
    	elseif($apmin <= $amin) {
    		$this->form_validation->set_message('validation_custom_grade', 'A+ Minimum value must be greater than A Minimum value');
    		return FALSE;
    	}
    	elseif($amin <= $bpmin) {
    		$this->form_validation->set_message('validation_custom_grade', 'A Minimum value must be greater than B+ Minimum value');
    		return FALSE;
    	}
    	elseif($bpmin <= $bmin) {
    		$this->form_validation->set_message('validation_custom_grade', 'B+ Minimum value must be greater than B Minimum value');
    		return FALSE;
    	}

    	return TRUE;
    }

    public function lock_custom_grade() {
		$this->form_validation->set_rules('id','ID','trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$markid = $this->MOD->decode($this->input->post('id'));
			echo json_encode($this->SM->lock_custom_grade($markid));
		}   	
    }

    public function hod_checklist($url1 = 0, $url2 = 0){
    	if($this->session->userdata('is_hod') == FALSE || $url1 === 0 || $url2 ===0){
    		echo $this->load->view('404', '', TRUE);
			die();
    	} else {
    		$brcode = $url1;
    		$sem = $url2;
    		$staffid = $this->session->userdata('username');
    		$this->SM->hod_checklist_pdf($staffid, $url1, $url2);
		}
	}


	public function get_offered_subjects() {
		$this->form_validation->set_rules('session','Session','trim|required|xss_clean');
		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$staffid = $this->session->userdata('username');
			
			$sesid = $this->MOD->decode($this->input->post('session'));			
			$res = $this->SM->get_staff_sub_tt($staffid, $sesid);
			echo json_encode($res);
		}  
	}

	public function is_img_exist($staffid) {		
		if(!$this->session->userdata('is_admin'))
			$regno = $this->session->userdata('username');
		$img =  $this->SM->get_image($staffid);
		if($img == null) {
			return base_url().'assests/img/user-shadow.png';
		} else
			return base_url().'staff/get_image/'.$staffid;
	}

	public function get_image($staffid) {
		$img = $this->SM->get_image($staffid, TRUE);
		if($img == null) {
			redirect(base_url().'assests/img/user-shadow.png');
		}
		else
			echo $img;
	}
        
        

    public function revaluation($page = 0, $camcode = 0, $prgm = 0, $type = 0, $subcode = 0, $staffid = 0, $session = 0) {
    	//changed from MA to RS by Kulothungan
    	$sesid = $this->MOD->get_write_ses('RS', 'ALL');
    	$data['active'] = 'staff';
    	$data['active2'] = 'revaluation';
		if($page === 'courses') {
			$data['active3'] = 'courses';
			$data['scount'] = $this->SM->get_rev_sub_and_count($sesid);
			$data['sessions'] = $this->MOD->get_module_option('RS', 'ALL');
			$this->load->view('staff/revaluation/courses', $data); 
		} elseif($page == 'marks' || $page == 'update') {
			if($camcode === 0 || $prgm === 0 || $type === 0 || $subcode === 0 || $staffid === 0 || $session === 0 || 
				!$this->SM->is_auth_rev_sub($camcode, $prgm, $type, $subcode, $staffid, $sesid)) {
				echo $this->load->view('404', '', TRUE);
				die();
			}
			$data['active3'] = $page;
    		$data['id'] = $camcode.'/'.$prgm.'/'.$type.'/'.$subcode.'/'.$staffid.'/'.$this->MOD->encode($sesid);
			$data['subject'] = $this->GM->get_subject_info($subcode);
    		$data['subject']['subcode'] = $subcode; 
    		$data['staff'] = $this->GM->get_staff_info($this->session->userdata('username'));
        	$this->load->view('staff/revaluation/'.$page, $data);
        } elseif($page == 'foil') {
        	echo $this->SM->rev_foil_sheet_pdf($camcode, $prgm, $type, $subcode, $staffid, $sesid);
        } else {
    		echo $this->load->view('404', '', TRUE);
			die();
			return;
    	}
    }

    public function revaluation_details($campus = 0, $prgm = 0, $type = 0, $subcode = 0, $staffid = 0, $session = 0) {
    	if($campus === 0 || $prgm === 0 || $type === 0 || $subcode === 0 || $staffid === 0 || $session === 0) {
    		$res = array('result' => FALSE, 'msg' => 'Missing required paramters!');
		} else {
			$sesid = $this->MOD->decode($session);
			$update = FALSE;
			$res = $this->SM->get_rev_details($update, $campus, $prgm, $type, $subcode, $staffid, $sesid);
		}
		echo json_encode($res);
    }

    public function revaluation_details_update($campus = 0, $prgm = 0, $type = 0, $subcode = 0, $staffid = 0, $session = 0) {
    	if($campus === 0 || $prgm === 0 || $type === 0 || $subcode === 0 || $staffid === 0 || $session === 0) {
    		$res = array('result' => FALSE, 'msg' => 'Missing required paramters!');
		} else {
			$sesid = $this->MOD->decode($session);
			$update = TRUE;
			$res = $this->SM->get_rev_details($update, $campus, $prgm, $type, $subcode, $staffid, $sesid);
		}
		echo json_encode($res);
    }

    public function revaluation_lock($campus = 0, $prgm = 0, $type = 0, $subcode = 0, $staffid = 0, $session = 0) {
    	if($campus === 0 || $prgm === 0 || $type === 0 || $subcode === 0 || $staffid === 0 || $session === 0) {
    		$res = array('result' => FALSE, 'msg' => 'Missing required paramters!');
		} else {
			$sesid = $this->MOD->decode($session);
			$res = $this->SM->revaluation_lock($campus, $prgm, $type, $subcode, $staffid, $sesid);
		}
		echo json_encode($res);
    }

	public function revaluation_update($camcode = 0, $prgm = 0, $type = 0, $subcode = 0, $staffid = 0, $session = 0) {
    	if($camcode === 0 || $prgm === 0 || $type === 0 || $subcode === 0 || $staffid === 0 || $session === 0) {
    		$res = array('result' => FALSE, 'msg' => 'Missing required paramters!');
		} 
		$this->form_validation->set_rules('marks[]','Student List','trim|xss_clean');

		if ($this->form_validation->run() == FALSE){
			echo validation_errors();
		}
		else {
			$marks = $this->input->post('marks');
			$sesid = $this->MOD->decode($session);
			$res = $this->SM->revaluation_update($camcode, $prgm, $type, $subcode, $staffid, $marks, $sesid);
			echo json_encode($res);
		}
	}

	public function dept_stud_list($dept = 0, $prgm = 0, $brcode = 0, $sem = 0){
        if((!$this->session->userdata('is_hod') && !$this->session->userdata('is_admin') && !$this->session->userdata('is_sadmin')) || ($dept === 0 || $prgm === 0)) {
            echo $this->load->view('404', '', TRUE);
            die();
        }
        $this->SM->dept_stud_pdf($dept, $prgm, $brcode, $sem);
        
	}
	
	//Question Paper Related Functions 11-1-2018
	
	// QP Upload
	/***************************************************************/
	
	
	public function qpupload() 
	{
		
		$staffid = ($this->session->userdata('username'));
		//for staff details 6 jan
		
		
		
		$data['result'] = $this->SM->get_qp_details($staffid);
                
                    //print_r($data);
		
		$data1['result'] =$this->SM->get_staff_name($staffid);
		if($data)
		{
                
			$this->load->view('Upload_View',$data);
		}
		else
		{
			$data['result'] = $this->SM->get_uploadstatus($staffid);
			
			$this->load->view('upload_success',$data);
		}
	
	}
	public function qp_staff_info($staffid) 
	{
		//print_r($staffid);
		$data['result']= $this->SM->get_staff_name($staffid);
		//$data['result'] = $this->SM->get_uploadstatus($staffid);
		//$this->load->view('Upload_View',$data);
		//var_dump($data);
		echo json_encode($data);
	}
	public function qp_upload_status() 
	{
		$staffid = ($this->session->userdata('username'));
		//for staff details 6 jan
		
		//var_dump($data);
		//$data['result'] = $this->SM->get_staff_details($staffid);
		$data['result'] = $this->SM->get_qp_complete($staffid);
		
		//$data1['result'] =$this->SM->get_staff_name($staffid);
		if($data)
		{
	
			$this->load->view('upload_success',$data);
		}
		
	}
	
	public function getstaffname($staffid) 
	{
		//$staffid = ($this->session->userdata('username'));
		//staff model		
		$data= $this->SM->get_staff_name($staffid);
		$this->load->view('Upload_View',$data);
		//echo json_encode ($data) ;
		//echo $row->SUBNAME;
		//$this->load->view('Upload_View',$data1);
		var_dump($data);
	}

	public function getsubname() 
	{
		$subcode = $this->input->post('subcode');
		//staff model		
		$row= $this->GM->get_subname($subcode);
		//echo json_encode ($data) ;
		echo $row->SUBNAME;
	}

// upload function for the Question Paper

	function upload($data,$mobile)
	{
    
		$result = $this->SM->checkcode($data);
		$result1 = $this->SM->checkupload($data);
		if(!empty($result) && empty($result1))
		{
		 //PHP CURL API SAMPLE
		//You need to change your settings below.
		//https://www.smsgatewaycenter.com/library/send_sms_2.php?UserName=username&Password=password&Type=Individual&To=9999999999&Mask=Senderid&Message=Hello%20World
		//---------------------------------
			$username = "dcdcoe";
			$mypassword = "DCacoe@123";
			$sendername = "ACOEUD";
			//$sendername = "SUPPRT";
			$domain = "bhashsms.com/api/";
			//API Domain
			$type = "normal";
			$priority="ndnd";
			$rndno=rand(100000, 999999);
			$otp = urlencode($rndno);
			$method = "POST";
			//---------------------------------
			//$mobile = $this->input->post('mobileno');
			$username = urlencode($username);
			$password = urlencode($mypassword);
			$sendername = urlencode($sendername);
			$message = urlencode('This is an OTP for Question Paper upload '.$otp.' by ACOE(UDs).Dont share this with anyone. ');
			$parameters = "user=".$username."&pass=".$mypassword."&sender=".$sendername."&phone=".$mobile."&text=".$message."&priority=".$priority."&stype=".$type ;
			$apiurl = "http://".$domain."/sendmsg.php";
			$get_url = $apiurl."?".$parameters;
			//echo $get_url;
			$ch = curl_init($apiurl);
			if($method == "POST")
			{
				curl_setopt($ch, CURLOPT_POST,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,$parameters);
			}
			else 
			{
				$get_url = $apiurl."?".$parameters;
				echo $get_url;
				curl_setopt($ch, CURLOPT_POST,0);
				curl_setopt($ch, CURLOPT_URL, $get_url);
			}

			curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
			curl_setopt($ch, CURLOPT_HEADER,0);
			// DO NOT RETURN HTTP HEADERS
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			// RETURN THE CONTENTS OF THE CALL
			$return_val = curl_exec($ch);
			//if($return_val==""){echo "Process Failed, Please check domain, username and password.";} else {echo $return_val;}
			date_default_timezone_set('Asia/Kolkata');
			$data1 = array('QPID' => $data,
			'OTP' => $rndno,
			'UPDATED_TIME' =>  date("Y-m-d H:i:s")
			);
			$result = $this->SM->otpstore($data1);
			$this->session->set_userdata($data1) ;
			$data1=$this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Note: <strong>OTP</strong> sent to ur registered mobile number and E-Mail address !..............</div>');
			//added for sending OTP in email	
			$this->load->library('email');
			$staffid = ($this->session->userdata('username'));
$data = array('USERNAME' => $staffid);

                $this->db->select('EMAIL');
                $this->db->where($data);
                $query = $this->db->get('STAFF_LOGIN');
				$row = $query->row();
$this->email->from('admin@acoe.annauniv.edu', 'ACOEUD');
$this->email->to($row->EMAIL);
$this->email->cc('admin@acoe.annauniv.edu');
//$this->email->bcc('them@their-example.com');

$this->email->subject('OTP for Question Paper');
$this->email->message('This is an OTP for Question Paper upload '.$otp.' by ACOE(UDs).Dont share this with anyone. ');

$this->email->send();
/*
if (!$this->email->send())
{
        // Generate error
		$data1=$this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Error: <strong>OTP</strong> is not sent to the E-Mail ID!..............</div>');
} */
			redirect('Load_Controller');
		 				
		}
			
		else if (!empty($result1))
		{
			
			
			$data['success_msg'] = '<div class="alert alert-danger text-center">ALREADY UPLOADED!</div>';
			$this->load->view('Upload_View', $data);
		}
    
		else
		{    
			$staffid = $this->input->post('staffid');
			$data['result']= $this->SM->get_subcode($staffid);
			$data['success_msg'] = '<div class="alert alert-danger text-center">ENTER CORRECT KEY!</div>';
			$this->load->view('Upload_View', $data);
		
		}
	
	}
/*****************************get_staffname*********************************************************/
public function get_staffname() {

		
	$staffid = $this->input->post('staffid');
		
	$row= $this->SM->get_staffname($staffid);
	 
	 echo $row->STAFFNAME;
}

/*****************************get_staffname*********************************************************/
public function get_staffmobile() {

		
	$staffid = $this->input->post('staffid');
		
	$row= $this->SM->get_staffmobile($staffid);
	 
	 echo $row->MOBILE;
}
/*****************************get_subcode*********************************************************/
/* Code for Mark Entry OTP */


	
	function mark_otp()
	{
    
		$ip = $_SERVER['REMOTE_ADDR'];
		$markid = $this->MOD->decode($this->input->post('id'));
		$subcode = $this->input->post('ccode');
		$sesid = $this->MOD->decode($this->input->post('session'));
	$staffid = $this->session->userdata('username');
	$row= $this->SM->get_staffmobile($staffid);
			$mobile = $row->MOBILE ;
			
			$username = "dcdcoe";
			$mypassword = "DCacoe@123";
			$sendername = "ACOEUD";
			$domain = "bhashsms.com/api/";
			$type = "normal";
			$priority="ndnd";
			$rndno=rand(100000, 999999);
			echo $otp = urlencode($rndno);
			$method = "POST";
			$username = urlencode($username);
			$password = urlencode($mypassword);
			$sendername = urlencode($sendername);
			$message = urlencode('This is an OTP for Mark entry '.$otp.' by ACOE(UDs).Dont share this with anyone. ');
			$parameters = "user=".$username."&pass=".$mypassword."&sender=".$sendername."&phone=".$mobile."&text=".$message."&priority=".$priority."&stype=".$type ;
			$apiurl = "http://".$domain."/sendmsg.php";
			$get_url = $apiurl."?".$parameters;
			//echo $get_url;
			$ch = curl_init($apiurl);
			if($method == "POST")
			{
				curl_setopt($ch, CURLOPT_POST,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,$parameters);
			}
			else 
			{
				$get_url = $apiurl."?".$parameters;
				echo $get_url;
				curl_setopt($ch, CURLOPT_POST,0);
				curl_setopt($ch, CURLOPT_URL, $get_url);
			}

			curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
			curl_setopt($ch, CURLOPT_HEADER,0);
			// DO NOT RETURN HTTP HEADERS
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			// RETURN THE CONTENTS OF THE CALL
			$return_val = curl_exec($ch);
			
			$this->session->set_userdata('otp', $rndno);
			$this->session->unset_userdata('otp');
			$data = array(
                'OTP' => $rndno
            );
			$value =  $this->SM->mark_status($staffid);
        $this->session->set_userdata($data);
			if($return_val==""){echo "Process Failed, Please check domain, username and password.";} else {echo $return_val;}
		date_default_timezone_set('Asia/Kolkata');
			$data1 = array(
			'MARKID' =>$markid,
			'SUBCODE'=>$subcode,
			'STAFFID'=>$staffid,
			'OTP' => $rndno,
			'SESID'=>37,
			'USER_IP'=>$ip,
			'STATUS' =>1,
			'IN_DATE' => date('d-m-Y h:i:sa'),
		);
			 
			//print_r($data1);
		$value =  $this->SM->mark_entry($data1);
		
			//$query = $this->db->insert('MARKENTRY_OTP', $data1);
			// print_r($query);
				
		//$result = $this->SM->otpstore($data1);
			$this->session->set_userdata($data1) ;
			$data1=$this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Note: <strong>OTP</strong> sent to ur registered mobile number and E-Mail address !..............</div>');
			//added for sending OTP in email	
			$this->load->library('email');
			$staffid = ($this->session->userdata('username'));
            $data = array('USERNAME' => $staffid);

                $this->db->select('EMAIL');
                $this->db->where($data);
                $query = $this->db->get('STAFF_LOGIN');
		        $row = $query->row();
			$this->email->from('admin@acoe.annauniv.edu', 'ACOEUD');
			$this->email->to($row->EMAIL);
			$this->email->cc('admin@acoe.annauniv.edu');
			//$this->email->bcc('them@their-example.com');
			$this->email->subject('OTP for Mark Entry in SEMS');
			$this->email->message('This is an OTP for Mark Entry in SEMS '.$otp.' by ACOE(UDs).Dont share this with anyone. ');
			//$this->email->send();
			/*
			if (!$this->email->send())
			{
			// Generate error
			$data1=$this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">Error: <strong>OTP</strong> is not sent to the E-Mail ID!..............</div>');
			} */
		//	redirect('Load_Controller');
		 				
		}
	
	
	/* OLD Version pf Mark URL
	public function mark_otpverify() {

		$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
		$this->form_validation->set_rules('uotp','OTP','trim|required|xss_clean');
		
	$markid1 = $this->MOD->decode($this->input->post('id'));
	$uotp = $this->input->post('uotp');
	$url = $this->MOD->encode($markid1);
	$check = $this->SM->mark_otpcheck($markid1,$uotp);
	
		if($check == 1){
		
			echo $check;
		}
		else{
		
			echo "OTP entered is not valid. Kindly try again";
		}
	}	*/
	public function mark_otpverify() {

		$this->form_validation->set_rules('id','ID','trim|required|xss_clean');
		$this->form_validation->set_rules('uotp','OTP','trim|required|xss_clean');
		
		$markid1 = $this->MOD->decode($this->input->post('id'));
		$uotp = $this->input->post('uotp');
		
		
		
		$url = $this->MOD->encode($markid1);
		$check = $this->SM->mark_otpcheck($markid1,$uotp);
		$returnArray = array();
		if($check == 1){
			$returnArray['url'] = base_url().'Staff/update/'.$url;
		}
		else{
			$returnArray['message'] = 'The OTP entered is incorrect. Kindly enter the correct OTP...';
		}
		
		echo json_encode($returnArray);
	}
	
	
	
	public function qp_keyupload()
	{
		
			

                        $config['upload_path']          = './tests/';
			$config['allowed_types']        = 'pdf';
			$config['max_size']    = '2000000';
			$this->load->library('upload', $config);
                        $this->upload->initialize($config);
			$user_ip = $_SERVER['REMOTE_ADDR'];
			
			if (!$this->upload->do_upload('filename'))
			{
			$upload_error = array('error' => $this->upload->display_errors());
			//$this->load->view('', $upload_error);
			
			}

			else{

			$zdata = array('upload_data' => $this->upload->data()); 
			$zfile = $zdata['upload_data']['full_path']; 
			$zdata = file_get_contents($zfile);
			$qpid = $this->input->post('qpid');
			$otp = 87654;
			$sesid = 37;
			$date = date('d-m-Y h:i:sa');
			
		/*	$data = array('QPID' =>$qpid,
			'FILEDTA' =>$zdata,
			'OTP' =>$otp,
			'UPLOAD_DATE' =>$date,
			'SESID' =>$sesid,
			'USER_IP' =>$user_ip,
			
			);
			*/
		//	$r = $this->SM->qp_keyupload($data);
			
			$r = $this->SM->qp_keyupload($qpid,$zfile,$otp,$sesid,$date,$user_ip);
			
			if($r == 1){

			unlink($zfile);
			$this->SM->qp_keystatus($qpid);
			
			$messge = array('message' => '<strong>Sucessfully Assigned.....</strong>','class' => 'alert alert-success ');
	                $this->session->set_flashdata('item', $messge);	
	                redirect('staff/qp_upload_status');
			
			
			}

			}
	}
	
	public function claim($qid)
	{
		//$staffid = $this->session->userdata('username');
		$this->SM->qpclaim_pdf($qid);
		
		
	}
	
	
	
}
