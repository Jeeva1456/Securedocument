<?php
Class Module_model extends CI_Model
{
    function __construct() {
        parent::__construct();
        $this->load->library('encrypt');
    }

    /*
    Function to check whether view rights has been enabled by administrator
    @mid - Module ID
    @sesid - Session ID
    @deg - Degree (UG/PG)
    ---
    @view - 1 (enabled)
    @view - 0 / any other (disabled)
    */
    public function is_view($mid, $sesid, $deg) {
        $data = array('MID' => $mid, 'SESID' => $sesid, 'DEG' => $deg, 'RVIEW' => 1);
        $this->db->select('RVIEW');
        $this->db->where($data);
        $query = $this->db->data('MODULE_STATUS');
        if($query->num_rows() === 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /*
    Function to check whether write right has been enabled by administrator
    @mid - Module ID
    @sesid - Session ID
    @deg - Degree (UG/PG)
    ---
    @write - 1 (enabled)
    @vwrite- 0 / any other (disabled)
    */
    public function is_write($mid, $sesid, $deg = 'ALL') {
        $data = array('MID' => $mid, 'SESID' => $sesid, 'DEG' => $deg, 'EDIT' => 1);
        $this->db->select('EDIT');
        $this->db->where($data);
        $query = $this->db->get('MODULE_STATUS');
        if($query->num_rows() === 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }


    public function get_module_info($mid, $sesid, $deg) {
        $data = array('MID' => $mid, 'SESID' => $sesid, 'DEG' => $deg, 'RVIEW' => 1);
        $this->db->select('SESNAME, RVIEW, EDIT');
        $this->db->where($data);
        $query = $this->db->get('MODULE_STATUS');
        if($query->num_rows() === 1) {
            $row = $query->row();
            $result['sesname'] = $row->$SESNAME;
            $result['rview'] = ($row->RVIEW == 1 ? TRUE : FALSE);
            $result['write'] = ($row->EDIT == 1 ? TRUE : FALSE);
            return $result;
        } else {
            return FALSE;
        }
    }

    /*
    Returns the @mid details whose view is enabled
    @mid - Module ID    
    @deg - Degree (UG/PG)
    */
    public function get_module_view($mid, $deg) {
        $data = array('MID' => $mid, 'DEG' => $deg);
        $this->db->select('SESID, SESNAME, RVIEW, EDIT');
        $this->db->where($data);
        $query = $this->db->get('MODULE_STATUS');
        if($query->num_rows() > 0) {
            $i = 0;
            $record = $query->result();
            foreach ($record as $r) {
                $result[$i]['sesid'] = $r->SESID;
                $result[$i]['sesname'] = $r->SESNAME;
                $result[$i]['rview'] = ($r->RVIEW == 1 ? TRUE : FALSE);
                $result[$i]['write'] = ($r->EDIT == 1 ? TRUE : FALSE);
                $i++;
            }
            return $result;
        } else {
            return FALSE;
        }
    }

    public function get_module_option($mid, $deg) {
        $res = $this->get_module_view($mid, $deg);
        if($res == FALSE) {
            $res = array('result' => 'false', 'msg' => 'Invalid Module/Degree');
        }
        $size = count($res);
        $count = 0;
        $option = $val = '';
        $option = '<option value="" disabled selected> Select Session</option>';
        for($i = 0; $i < $size; $i++) {
            if($res[$i]['rview']) {
                if($res[$i]['write']) {
                    $option .= '<option value="'.$this->encode($res[$i]['sesid']).'" selected>'.$res[$i]['sesname'].'</option>';
                } else
                $option .= '<option value="'.$this->encode($res[$i]['sesid']).'">'.$res[$i]['sesname'].'</option>';
                $count++;
            }
        }

        $res = array('result' => 'true', 'msg' => $option, 'val' => $val);
        return $option;
    }

    public function get_all_modules() {
        $this->db->select('DISTINCT(SESID), SESNAME');
        $query = $this->db->get('MODULE_STATUS');
        $option = '<option value=""></option>';
        if($query->num_rows() > 0) {            
            $record = $query->result();
            foreach ($record as $r) {
                $option .= '<option value="'.$this->encode($r->SESID).'">'.$r->SESNAME.'</option>';
            }            
        }
        return $option;
    }
  
  

    public function get_module_status($sesid) {
        $data = array('SESID' => $sesid);
        $this->db->select('MID, RVIEW, EDIT');
        $this->db->where($data);
        $query = $this->db->get('MODULE_STATUS');
        $table = '';
        if($query->num_rows() > 0) {
            $record = $query->result();foreach($record as $r) {
                $rc = $wc = '';
                if($r->RVIEW == 1) $rc = 'checked';
                if($r->EDIT == 1) $wc = 'checked';
                $table .= '<tr>';
                $table .= '<td>'.$this->get_module_name($r->MID).'</td>';
                $table .= '<td><label><input type="checkbox" class="checkbox-inline minimal" name="'.$r->MID.'R" value="1" '.$rc.'></label></td>';
                $table .= '<td><label><input type="checkbox" class="checkbox-inline minimal" name="'.$r->MID.'W" value="1" '.$wc.'></label></td>';
                $table .= '</tr>';
            }
        }
        return $table;
    }

    public function get_module_name($mid) {
        $data = array('MID' => $mid);
        $this->db->select('MNAME');
        $this->db->where($data);
        $query = $this->db->get('MODULE');
        if($query->num_rows() > 0) {
            $row = $query->row();
            return $row->MNAME;
        } else
            return 'Invalid Module';
    }

    /*
    Returns the @mid details whose view is enabled
    @mid - Module ID    
    @deg - Degree (UG/PG)
    */
    public function get_write_ses($mid, $deg) {
        $data = array('MID' => $mid, 'DEG' => $deg, 'EDIT' => 1);
        $this->db->select('SESID');
        $this->db->where($data);
        $query = $this->db->get('MODULE_STATUS');
        if($query->num_rows() > 0) {            
            $row = $query->row();
            return $row->SESID;
        } else {
            return 0;
        }
    }

    public function encode($str) {
        $this->load->library('encrypt');
        $str = $this->encrypt->encode($str);
        $str = str_replace('/', '>', $str);
        return urlencode($str);
        return $str;
    }

    public function decode($str) {
        $str = urldecode($str);
        $str = str_replace('>', '/', $str);
        $str = str_replace(' ', '+', $str);;
        return $this->encrypt->decode($str);
        return $str;
    }

    public function get_student_module_option($regno) {
        $data = array('REGNO' => $regno);
        $this->db->select('DISTINCT(SESID)');
        $this->db->where($data);
        $query = $this->db->get('REGISTER');
        $option = '';
        if($query->num_rows() > 0) {
            $option = $val = '';
            $option = '<option value="" disabled selected> Select Session</option>';
            $record = $query->result();
            foreach($record as $r) {
                $data = array('MID' => 'MA', 'DEG' => 'ALL', 'RVIEW' => 1, 'SESID' => $r->SESID);
                $this->db->select('SESNAME, EDIT');
                $this->db->where($data);
                $query = $this->db->get('MODULE_STATUS');
                if($query->num_rows() < 1) continue;
                $row = $query->row();
                if($row->EDIT == 1) {
                    $option .= '<option value="'.$this->encode($r->SESID).'" selected>'.$row->SESNAME.'</option>';
                } else
                    $option .= '<option value="'.$this->encode($r->SESID).'">'.$row->SESNAME.'</option>';
            }
        }

        $res = array('result' => 'true', 'msg' => $option);
        return $option;
    }


    public function add_session($sesid, $sesname,$sesshort) {
        $data = array('SESID' => $sesid);
        $this->db->select('SESID');
        $this->db->where($data);
        $query = $this->db->get('MODULE_STATUS');
        if($query->num_rows() > 0)
            return array('result' => 'false', 'msg' => 'Session ID already exists!');
        //added for sesshort on 30 may 17
				$data = array('SESID' => $sesid, 'SESNAME' => $sesname, 'SESSHORT'=> $sesshort);
                $this->db->insert('SESION', $data);
        $this->db->select('MID');
        $query = $this->db->get('MODULE');
        
        if($query->num_rows() > 0) {
            $record = $query->result();
            foreach($record as $r) {                
                $data = array('MID' => $r->MID, 'SESID' => $sesid, 'SESNAME' => $sesname, 'DEG' => 'ALL', 'RVIEW' => 0, 'EDIT' => 0);
                $this->db->insert('MODULE_STATUS', $data);
				
            }
            return array('result' => 'true', 'msg' => 'New session created');
        } else
            return array('result' => 'false', 'msg' => 'No module found');
    }

    public function update_session($sesid, $arr) {        
        foreach ($arr as $key => $value) {
            $r = $w = 0;
            if($arr[$key]['r'] == 1) $r = 1;
            if($arr[$key]['w'] == 1) {
                if($this->already_write($sesid, $key))
                    return array('result' => 'false', 'msg' => $key.' write enabled for different session. Disable it first');
                $r = $w = 1;
            }            

            $udata = array('RVIEW' => $r, 'EDIT' => $w);
            $data = array('SESID' => $sesid, 'MID' => $key);
            $this->db->where($data);
            $this->db->update('MODULE_STATUS', $udata);
        }
        return array('result' => 'true', 'msg' => 'Session Updated');
    }

    public function already_write($sesid, $mid) {
        $data = array('SESID !=' => $sesid, 'MID' => $mid, 'EDIT' => 1);
        $this->db->select('EDIT');
        $this->db->where($data);
        $query = $this->db->get('MODULE_STATUS');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

}
