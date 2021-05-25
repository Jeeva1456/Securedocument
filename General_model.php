<?php
Class General_model extends MY_Model
{
    var $MAX_DAY = 6;
    function __construct() {
        parent::__construct();
        $this->load->model('Module_model', 'MOD');
    }

    public function get_code($subno) {
        $data = array('SUBNO' => $subno);
        $this->db->select('SLOTID');
        $this->db->where($data);
        $query = $this->db->get('SLOTNO');
        if($query->num_rows() > 0) {
            return $query->row()->SLOTID;
        } else {
            return -1;
        }
    }

    public function get_subno($code) {
    	$data = array('SLOTID' => $code);
        $this->db->select('SUBNO');
        $this->db->where($data);
        $query = $this->db->get('SLOTNO');
        if($query->num_rows() > 0)
        	return $query->row()->SUBNO;
        else
        	return -1;
    }

    public function get_campus_info($camcode) {
        $data = array('CAMCODE' => $camcode);
        $this->db->select('CAMNAME, CAMSHORT');
        $this->db->where($data);
        $query = $this->db->get('CAMPUS');
        if($query->num_rows() > 0) {
            $row = $query->row();
            $campus['camshort'] = $row->CAMSHORT;
            $campus['camname'] = $row->CAMNAME;
            return $campus;
        } else return FALSE;
    }

    public function get_dept_info($depcode) {
        $data = array('DEPCODE' => $depcode);
        $this->db->select('CAMCODE, DEPNAME');
        $this->db->where($data);
        $query = $this->db->get('DEPARTMENT');
        if($query->num_rows() > 0) {
            $row = $query->row();
            $dept['camcode'] = $row->CAMCODE;
            $dept['depname'] = $row->DEPNAME;
            return $dept;
        } else return FALSE;
    }

    public function get_branch_info($brcode) {
        $data = array('BRCODE' => $brcode);
        $this->db->select('CAMCODE, DEPCODE, DEGSHORT, BRNAME, BRSHORT, DEGMODE, PRGM');
        $this->db->where($data);
        $query = $this->db->get('BRANCH');
        if($query->num_rows() > 0) {
            $row = $query->row();
            $branch['camcode'] = $row->CAMCODE;
            $branch['depcode'] = $row->DEPCODE;
            $branch['brname'] = $row->BRNAME;
            $branch['brshort'] = $row->BRSHORT;
            $branch['degshort'] = $row->DEGSHORT;
            if($row->DEGMODE == 'F')
                $mode = 'FULL TIME';
            elseif($row->DEGMODE == 'P')
                $mode = 'PART TIME';
            else
                $mode = $row->DEGMODE;
            $branch['degmode'] = $mode;
            $branch['degmode2'] = $row->DEGMODE;
            $branch['prgm'] = $row->PRGM;
            return $branch;
        } else return FALSE;
    }

    public function get_staff_info($staffid) {
        //var_dump($staffid);
        $data = array(
                'STAFFID' => $staffid,
                'DEPCODE !=' => 0
            );
        $this->db->select('STAFFID, STAFFNAME, DESG, DEPCODE');
        $this->db->where($data);
        $query = $this->db->get('STAFF');
        if($query->num_rows() > 0) {
            foreach ($query->result() as $r) {
                $dept = $this->get_dept_info($r->DEPCODE);
                $staff = array(
                    'staffid' => $r->STAFFID,
                    'staffname' => $r->STAFFNAME,
                    'desg' => $r->DESG,
                    'dept' => $dept['depname'],
                    'depcode' => $r->DEPCODE
                );                
            }            
        } else {
            $staff = array(
                    'staffid' => '',
                    'staffname' => '',
                    'desg' => '',
                    'dept' => '',
                    'depcode' => ''
                );
        }
        return $staff;
    }

    public function search_staff($staffname, $depcode) {
        $data = array('DEPCODE !=' => 0);
        if($depcode !== 0) $data['DEPCODE'] = $depcode;        

        $this->db->select('STAFFID, STAFFNAME, DESG, DEPCODE');
        $this->db->where($data);
        $this->db->group_start();
        $this->db->like('LOWER(STAFFNAME)', strtolower($staffname));
        $this->db->or_like('STAFFID', $staffname);
        $this->db->group_end();
        $query = $this->db->get('STAFF');

        if($query->num_rows() == 0) {
            $table = '<tr><td colspan = "5">No Result Found.</td></tr> ';
            return $table;
        }
        $table = "";
        foreach($query->result() as $r) {
            $dept = $this->get_dept_info($r->DEPCODE);
            $table .= '<tr class="xstaff" id="'.$r->STAFFID.'">';
            $table .= '<td>'.$r->STAFFID.'</td>';
            $table .= '<td>'.$r->STAFFNAME.'</td>';
            $table .= '<td>'.$r->DESG.'</td>';
            $table .= '<td>'.$dept['depname'].'</td>';
            $table .= '<td><button type="button" class="btn btn-sm btn-success select-staff" id="'.$r->STAFFID.'"><i class="glyphicon glyphicon-arrow-right"></i></button></td>';
            $table .= '</tr>';
        }
        return $table;
    }

    public function get_subject_info($subcode) {
        $data = array('SUBCODE' => $subcode);
        $this->db->select('SUBNAME, SUBTYPE, CREDIT, CONTACT');
        $this->db->where($data);
        $query = $this->db->get('SUBJECTS');
        if($query->num_rows() === 1) {
            $record = $query->result();
            $row = $query->row();
            switch($row->SUBTYPE) {
                case 'T': $subtype = 'THEORY'; break;
                case 'L': $subtype = 'LAB'; break;
                case 'P': $subtype = 'PROJECT'; break;
                case 'S': $subtype = 'SEMINAR'; break;
                case 'C': $subtype = 'TLAB'; /*Theory cum Lab*/break;
                case 'D': $subtype = 'TSTUDIO'; /*Theory cum studio*/break;
                default: $subtype =  $row->SUBTYPE;
            }
            $subjects = array('subname' => $row->SUBNAME, 'credit' => $row->CREDIT, 'subtype' => $row->SUBTYPE, 'type' => $subtype, 'contact' => $row->CONTACT);
            
            return $subjects;
        } else {
            return FALSE;
        }
    }

    function get_slotno($slotno) {
        $this->db->select('DEPCODE, LVL, ID');
        $this->db->where(array('SLOTNO' => $slotno));
        $query = $this->db->get('TT_MAP');
        if($query->num_rows() > 0) {
            $row = $query->row();
            if($row->LVL == 'P')
                $slotno = $row->DEPCODE.'-'.$row->LVL.$row->ID;
            else
                $slotno = $row->LVL.$row->ID;
            return $slotno;
        }
        return $slotno;
    }

    function get_markid_details($markid) {
        $data = array('MARKID' => $markid);
        $this->db->select('CAMCODE, DEPCODE, PRGM, DEGMODE, DEGSHORT, SUBCODE, STAFFID, BRCODE, BATCH, SUBNO, SLOTNO, SEM, CLASSROOM, SESID');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');
        if($query->num_rows() == 0) {
            $this->db->select('CAMCODE, DEPCODE, PRGM, DEGMODE, DEGSHORT, SUBCODE, STAFFID, BRCODE, BATCH, SUBNO, SLOTNO, SEM, CLASSROOM, SESID');
            $this->db->where($data);
            $query = $this->db->get('SUBLINK_N16');
        }
        if($query->num_rows() === 1) {
            $row = $query->row();
            $res['camcode'] = $row->CAMCODE;
            $res['depcode'] = $row->DEPCODE;
            $res['prgm'] = $row->PRGM;
            $res['degmode'] = $row->DEGMODE;
            $res['degshort'] = $row->DEGSHORT;
            $res['sesid'] = $row->SESID;
            $res['subcode'] = $row->SUBCODE;
            $res['staffid'] = $row->STAFFID;
            $res['brcode'] = $row->BRCODE;
            $res['batch'] = $row->BATCH;
            $res['sem'] = $row->SEM;
            $res['classroom'] = $row->CLASSROOM;
            $res['slotno'] = $row->SLOTNO;
            $res['subno'] = $row->SUBNO;
			if($row->SESID > 34)
				$slotno = $this->get_slotno($row->SLOTNO, $row->SESID);
			else
				$slotno = $row->SLOTNO;
            $res['slot'] = $slotno.' - '.$this->get_code($row->SUBNO);
            return $res;
        } else
            return FALSE;
    }
	
	//added this module for student mark detials in the admin by kulothungan on 201216 at 8.00PM 
		function get_markid_info($markid) {
        $data = array('MARKID' => $markid);
        $this->db->select('CAMCODE, DEPCODE, PRGM, DEGMODE, DEGSHORT, SUBCODE, STAFFID, BRCODE, BATCH, SUBNO, SLOTNO, SEM, CLASSROOM, SESID');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');
        if($query->num_rows() === 1) {
            $row = $query->row();
            $res['camcode'] = $row->CAMCODE;
            $res['depcode'] = $row->DEPCODE;
            $res['prgm'] = $row->PRGM;
            $res['degmode'] = $row->DEGMODE;
            $res['degshort'] = $row->DEGSHORT;
            $res['sesid'] = $row->SESID;
            $res['subcode'] = $row->SUBCODE;
            $res['staffid'] = $row->STAFFID;
            $res['brcode'] = $row->BRCODE;
            $res['batch'] = $row->BATCH;
            $res['sem'] = $row->SEM;
            $res['classroom'] = $row->CLASSROOM;
            $res['slotno'] = $row->SLOTNO;
            $res['subno'] = $row->SUBNO;
            // $slotno = $this->get_slotno($row->SLOTNO, $row->SESID);
            //$res['slot'] = $slotno.' - '.$this->get_code($row->SUBNO);
            return $res;
        } else
            return FALSE;
    }
	// added by kulothungan on 15 06 17
	function get_subcode_info($subcode) {
        $data = array('SUBCODE' => $subcode);
        $this->db->select('MARKID,CAMCODE, DEPCODE, PRGM, DEGMODE, DEGSHORT, SUBCODE, STAFFID, BRCODE, BATCH, SUBNO, SLOTNO, SEM, CLASSROOM, SESID');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');
        if($query->num_rows() === 1) {
            $row = $query->row();
			$res['markid'] = $row->MARKID;
            $res['camcode'] = $row->CAMCODE;
            $res['depcode'] = $row->DEPCODE;
            $res['prgm'] = $row->PRGM;
            $res['degmode'] = $row->DEGMODE;
            $res['degshort'] = $row->DEGSHORT;
            $res['sesid'] = $row->SESID;
            $res['subcode'] = $row->SUBCODE;
            $res['staffid'] = $row->STAFFID;
            $res['brcode'] = $row->BRCODE;
            $res['batch'] = $row->BATCH;
            $res['sem'] = $row->SEM;
            $res['classroom'] = $row->CLASSROOM;
            $res['slotno'] = $row->SLOTNO;
            $res['subno'] = $row->SUBNO;
           // $slotno = $this->get_slotno($row->SLOTNO, $row->SESID);
            //$res['slot'] = $slotno.' - '.$this->get_code($row->SUBNO);
            return $res;
        } else
            return FALSE;
    }
    public function get_student_info($regno) {
        $res['regno'] = $regno;     

        $data = array('REGNO' => $regno);
        $this->db->select('NAME, BATCH, SEM, REGULATION, BRCODE');
        $this->db->where($data);
        $query = $this->db->get('STUDENT');
        if($query->num_rows() > 0) {
            $row = $query->row();            
            $branch = $this->GM->get_branch_info($row->BRCODE);
            $campus = $this->GM->get_campus_info($branch['camcode']);

            $res['result'] = TRUE;
            $res['name'] = $row->NAME;
            $res['batch'] = $row->BATCH;
            $res['sem'] = $row->SEM;
            $res['regulation'] = $row->REGULATION;
            $res['brcode'] = $row->BRCODE;
            $res['branch'] = $branch['degshort'].' '.$branch['brname'];
            $res['prgm'] = $branch['prgm'];
            $res['depcode'] = $branch['depcode'];
            $res['camcode'] = $branch['camcode'];
            $res['campus'] = $campus['camname'];
        } else {
            $res['result'] = FALSE;
            $res['name'] = '';
            $res['batch'] = '';
            $res['sem'] = '';
            $res['regulation'] = '';
            $res['brcode'] = '';            
            $res['branch'] = '';
            $res['prgm'] = '';
            $res['depcode'] = '';
            $res['camcode'] = '';
            $res['campus'] = '';
        }
        return $res;
    }

    public function get_student_info_enroll($regno) {
        $res['regno'] = $regno;     

        $data = array('REGNO' => $regno);
        $this->db->select('NAME, BATCH, SEM, REGULATION, BRCODE');
        $this->db->where($data);
        $query = $this->db->get('STUDENT_ENROLL');
        if($query->num_rows() > 0) {
            $row = $query->row();            
            $branch = $this->GM->get_branch_info($row->BRCODE);
            $campus = $this->GM->get_campus_info($branch['camcode']);

            $res['name'] = $row->NAME;
            $res['batch'] = $row->BATCH;
            $res['sem'] = $row->SEM;
            $res['regulation'] = $row->REGULATION;
            $res['brcode'] = $row->BRCODE;
            $res['branch'] = $branch['degshort'].' '.$branch['brname'];
            $res['prgm'] = $branch['prgm'];
            $res['camcode'] = $branch['camcode'];
            $res['campus'] = $campus['camname'];
        } else {
            $res['name'] = '';
            $res['batch'] = '';
            $res['sem'] = '';
            $res['regulation'] = '';
            $res['brcode'] = '';
            $res['branch'] = '';
            $res['prgm'] = '';
            $res['camcode'] = '';
            $res['campus'] = '';
        }
        return $res;
    }

    public function is_registered($regno, $sesid) {
        $data = array('REGNO' => $regno, 'SESID' => $sesid);
        $wdata = array('R', 'RA');
        $this->db->SELECT('REGNO');
        $this->db->where($data);
        $this->db->where_in('STATUS', $wdata);
        $query = $this->db->get('STUDENT_REGISTER');
        if($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function is_break($regno, $sesid) {
        $data = array('REGNO' => $regno, 'SESID' => $sesid, 'STATUS' => 0);
        $this->db->select('REGNO');
        $this->db->where($data);
        $query = $this->db->get('BREAK');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    function is_tution_fee_defaulters($regno, $sesid) {
        $data = array('REGNO' => $regno, 'SESID' => $sesid, 'STATUS' => 0);
        $this->db->select('REGNO');
        $this->db->where($data);
        $query = $this->db->get('TUTIONFEE_DEFAULTERS');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    function is_exam_fee_defaulters($regno, $sesid) {
        $this->db->select('STATUS');
        $data = array('REGNO' => $regno, 'STATUS' => 1);
        $this->db->where($data);
        $query = $this->db->get('EXAMFEE');
        if($query->num_rows() == 1){
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function is_withheld($regno, $sesid) {
        $data = array('REGNO' => $regno, 'SESID' => $sesid, 'STATUS' => 0);
        $this->db->select('REGNO');
        $this->db->where($data);
        $query = $this->db->get('WITHHELD');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    function is_malpractice($regno, $sesid) {
        $data = array('REGNO' => $regno, 'SESID' => $sesid);
        $this->db->select('REGNO');
        $this->db->where($data);
        $query = $this->db->get('MALPRACTICE');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    function is_withdrawn($regno, $markid, $sesid) {
        $data = array('REGNO' => $regno, 'MARKID' => $markid, 'SESID' => $sesid, 'STATUS' => 0);
        $this->db->select('REGNO');
        $this->db->where($data);
        $query = $this->db->get('WITHDRAWN');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    function is_result_published($brcode, $sem, $sesid) {
        $data = array('BRCODE' => $brcode, 'SEM' => $sem, 'SESID' => $sesid, 'STATUS' => 2);
        $this->db->select('PDATE');
        $this->db->where($data);
        $query = $this->db->get('RESULT_PROCESS');
        if($query->num_rows() > 0) 
            return TRUE;
        else
            return FALSE;
    }

    function is_oth_result_published($type, $sesid) {
        $data = array('TYPE' => $type, 'SESID' => $sesid, 'STATUS' => 1);
        $this->db->select('PDATE');
        $this->db->where($data);
        $query = $this->db->get('RESULT_PUBLISH');
        if($query->num_rows() > 0) 
            return TRUE;
        else
            return FALSE;
    }

    function get_session($sesid) {
        $session = '';
        switch($sesid) {
            case 34: $session = 'NOV - 16'; break;
            case 35: $session = 'APR - 17'; break;
            case 36: $session = 'NOV - 17'; break;
	case 37: $session = 'APR - 18'; break;
            default:
        }
        return $session;
    }

    function subcode_to_markid($regno, $subcode, $sesid) {
        $data = array('REGNO' => $regno, 'SUBCODE' => $subcode, 'SESID' => $sesid, 'FLAG !=' => 'D', 'STATUS' => 1);
        $this->db->select('MARKID');
        $this->db->where($data);
        $query = $this->db->get('REGISTER');
        if($query->num_rows() > 0) {
            return $query->row()->MARKID;
        } else {
            $this->db->select('MARKID');
            $this->db->where($data);
            $query = $this->db->get('REGISTER_N16');
            if($query->num_rows() > 0) {
                return $query->row()->MARKID;
            } else {
                return 0;
            }
        }
    }

    function get_session_sem($regno, $sesid) {
        $data = array('REGNO' => $regno, 'SESID' => $sesid);
        $this->db->select('DISTINCT(SEM)');
        $this->db->where($data);
        $query = $this->db->get('REGISTER');
        if($query->num_rows() > 0) {
            return $query->row()->SEM;
        } else {
            return 0;
        }
    }

    function get_record($select, $where, $table) {
        $this->db->select($select);
        $this->db->where($where);
        $query = $this->db->get($table);
        if($query->num_rows() > 0) {
            return $query->row()->{$select};
        } else
            return ' ';
    }

    function get_multi_col_record($select, $where, $table) {
        $this->db->select($select);
        $this->db->where($where);
        $query = $this->db->get($table);
        if($query->num_rows() > 0) {
            return $query->row();
        } else
            return ' ';
    }

    function is_elective($brcode, $sem, $subcode, $sesid) {
        $data = array('BRCODE' => $brcode, 'SEM' => $sem, 'SUBCODE' => $subcode, 'SESID' => $sesid);
        $this->db->select('SUBCODE');
        $this->db->where($data);
        $query = $this->db->get('ELECTIVE_COURSES');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    function get_elective_info($brcode, $sem, $sesid) {
        $data = array('BRCODE' => $brcode, 'SEM' => $sem, 'SESID' => $sesid, 'STATUS' => 1);
        $this->db->select('MIN, MAX, TOTAL');
        $this->db->where($data);
        $query = $this->db->get('ELECTIVE_INFO');
        if($query->num_rows() > 0) {
            $row = $query->row();
            return array('min' => $row->MIN, 'max' => $row->MAX, 'total' => $row->TOTAL);
        } else return FALSE;
    }

    function array_to_table($array, $align = null) {
        $table = '';
        $row = count($array);
        for($i = 0; $i < $row; $i++) {
            $col = count($array[$i]);
            $table .= '<tr>';
            for($j = 0; $j < $col; $j++) {
                if(isset($align[$col])) {                    
                    switch($align[$col]) {
                        case 'L': $class = ' class="text-left"'; break;
                        case 'R': $class = ' class="text-right"'; break;
                        case 'C': $class = ' class="text-center"'; break;
                        case 'J': $class = ' class="text-justify"'; break;
                        default: $class = ' class="'.$align[$col].'"';
                    }
                } else $class = '';
                $table .=  '<td'.$class.'>'.$array[$i][$j].'</td>';
            }
            $table .= '</tr>';
        }
        return $table;
    }

    function insert_record($record, $table) {
        $this->db->insert($table, $record);
        return TRUE;
    }

    function get_max_sem($degshort, $prgm, $degmode) {
        $data = array('DEGSHORT' => $degshort, 'DEGPRGM' => $prgm, 'DEGMODE' => $degmode);
        $this->db->select('DEGSEM');
        $this->db->where($data);
        $query = $this->db->get('DEGREE1');
        //var_dump($data);
        if($query->num_rows() > 0)
            return $query->row()->DEGSEM;
        else return -1;
    }
//added by kulothungan on 040417
   /*public function get_staff_personal($staffid) {
        $data = array('EMPID' => $staffid);
        $this->db->select('EMAIL','INTERCOM_NO','MOBILE');
        $this->db->where($data);
        $query = $this->db->get('STAFF_PERSONAL');
        //var_dump($data);
        if($query->num_rows() > 0) {
            $row = $query->row();
            $staffper['email'] = $row->EMAIL;
            $staffper['intercom_no'] = $row->INTERCOM_NO;            
            $staffper['mobile'] = $row->MOBILE;
            $staffper['gender'] = $row->GENDER;

           //return array( $staffper['email'],$staffper['intercom_no'],$staffper['mobile'],$staffper['gender']);
            //return array( 'email' =>$staffper['email'],'inter' => $staffper['intercom_no'],'mobile'=> $staffper['mobile'],'gender' => $staffper['gender']);
            //return array('min' => $row->MIN, 'max' => $row->MAX, 'total' => $row->TOTAL);
            //$res = array('result' => 'true', 'msg' => $staffper);
        return $staffper;
        } else return FALSE;
    } */

}
