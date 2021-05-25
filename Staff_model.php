<?php
Class Staff_model extends MY_Model
{

    function __construct() {
        parent::__construct();
        $this->load->model('TT_model', 'TT');
        $this->load->model('Module_model', 'MOD');
		$this->load->model('Prevented_model', 'PM');
        $this->load->model('General_model', 'GM');
        $this->staffid = $this->session->userdata('username');
        $this->sesid = $this->MOD->get_write_ses('MA', 'ALL');
    }

    public function conver_to_option($data) {
        $option = "";
        if($data == "") return $option;
        foreach($data as $r) {
            $option .= '<option value = "'.$r.'">'.$r.'</option>';
        }
        return $option;
    }

    public function offered_subject_details($sesid, $subcode, $brcode, $markid, $ass, $att, $flag) {
        $data = array(
                'MARKID' => $markid,
                'SESID' => $sesid
            );        

        $res = '';
        
        $this->db->select('SEM, BATCH');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');
        if($query->num_rows() == 0){
            $this->db->select('SEM, BATCH');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK_N16');
        }
        if($query->num_rows() > 0) {
            $row = $query->row();
            $subject = $this->GM->get_subject_info($subcode);
            $branch = $this->GM->get_branch_info($brcode);
            if($flag == 1)
                $e_list = $this->subject_enrolled_student($markid, $ass, $att);
            else 
                $e_list = '';
            $res = array(
                    'result' => 'true',
                    'subname' => $subject['subname'],
                    'type' => $subject['type'],
                    'credit' => $subject['credit'],
                    'sem' => $row->SEM,
                    'batch' => $row->BATCH,
                    'mode' => $branch['degmode'],
                    'elist' => $e_list
                );
        }
        return $res; 
    }

    public function get_ses_marks($markid, $sesid, $ass, $att) {
        $data = array(
                'MARKID' => $markid,
                'FLAG !=' => 'D',
                'SESID' => $sesid
            );
        $this->db->select('REGNO');
        $this->db->where($data);
        $reg_query = $this->db->get('REGISTER');

        $this->db->select('REGNO, ASS1, ASS2, ASS3, ATT1, ATT2, ATT3, ESEM');
        $data = array('MARKID' => $markid);
        $this->db->where($data);
        $mark_query = $this->db->get('MARKS');

        $flag = 0;
        $record = "";
        $arr = $foo = '';

        if($mark_query->num_rows() != 0 && $mark_query->num_rows() === $reg_query->num_rows()) {
            $record = $mark_query->result();            
        } else {            
            $record = $reg_query->result();
            $flag = 1;
            $ass1 = '';
            $ass2 = '';
            $ass3 = '';
            $att1 = '';
            $att2 = '';
            $att3 = '';
            $esem = '';
        }

        $i = 0;
        foreach($record as $r) {
            $j = 0;
            if($flag == 0) {
                $ass1 = $r->ASS1;
                $ass2 = $r->ASS2;
                $ass3 = $r->ASS3;
                $att1 = $r->ATT1;
                $att2 = $r->ATT2;
                $att3 = $r->ATT3;
                $esem = $r->ESEM;
                if($att1 == '-1') $att1 = '';
                if($att2 == '-1') $att2 = '';
                if($att3 == '-1') $att3 = '';
            }
            $student = $this->GM->get_student_info($r->REGNO);
            $arr[$i][$j++] = $i+1;
            $arr[$i]['regno'] = $r->REGNO;
            $arr[$i]['name'] = $student['name'];
            $arr[$i]['ass1'] = $ass1;
            $arr[$i]['ass2'] = $ass2;
            $arr[$i]['ass3'] = $ass3;
            $arr[$i]['att1'] = $att1;
            $arr[$i]['att2'] = $att2;
            $arr[$i]['att3'] = $att3;
            if($ass[3] == 1)
                $arr[$i]['esem'] = $esem;
            $i++;
        }
        $j = 0;
        if($ass[0] == 1) {
            if($this->is_attlocked($markid, 'ass1')) {
                $foo[$j++] = '<button type="button" class="btn cell-green btn-sm btn-flat unlock" value="ass1"><i class="fa fa-fw fa-unlock"></i> Unlock</button>';
            } else {
                $foo[$j++] = '<button type="button" class="btn cell-red btn-sm btn-flat lock" value="ass1"><i class="fa fa-fw fa-lock"></i> Lock</button>';
            }
        } if($ass[1] == 1) {
            if($this->is_attlocked($markid, 'ass2')) {
                $foo[$j++] = '<button type="button" class="btn cell-green btn-sm btn-flat unlock" value="ass2"><i class="fa fa-fw fa-unlock"></i> Unlock</button>';
            } else {
                $foo[$j++] = '<button type="button" class="btn cell-red btn-sm btn-flat lock" value="ass2"><i class="fa fa-fw fa-lock"></i> Lock</button>';
            }
        } if($ass[2] == 1) {
            if($this->is_attlocked($markid, 'ass3')) {
                $foo[$j++] = '<button type="button" class="btn cell-green btn-sm btn-flat unlock" value="ass3"><i class="fa fa-fw fa-unlock"></i> Unlock</button></th>';
            } else {
                $foo[$j++] = '<button type="button" class="btn cell-red btn-sm btn-flat lock" value="ass3"><i class="fa fa-fw fa-lock"></i> Lock</button></th>';
            }
        } if($att[0] == 1) {
            if($this->is_attlocked($markid, 'att1')) {
                $foo[$j++] = '<button type="button" class="btn cell-green btn-sm btn-flat unlock" value="att1"><i class="fa fa-fw fa-unlock"></i> Unlock</button></th>';
            } else {
                $foo[$j++] = '<button type="button" class="btn cell-red btn-sm btn-flat lock" value="att1"><i class="fa fa-fw fa-lock"></i> Lock</button></th>';
            }
        } if($att[1] == 1) {
            if($this->is_attlocked($markid, 'att2')) {
                $foo[$j++] = '<button type="button" class="btn cell-green btn-sm btn-flat unlock" value="att2"><i class="fa fa-fw fa-unlock"></i> Unlock</button></th>';
            } else {
                $foo[$j++] = '<button type="button" class="btn cell-red btn-sm btn-flat lock" value="att2"><i class="fa fa-fw fa-lock"></i> Lock</button></th>';
            }
        } if($att[2] == 1) {
            if($this->is_attlocked($markid, 'att3')) {
                $foo[$j++] = '<button type="button" class="btn cell-green btn-sm btn-flat unlock" value="att3"><i class="fa fa-fw fa-unlock"></i> Unlock</button></th>';
            } else {
                $foo[$j++] = '<button type="button" class="btn cell-red btn-sm btn-flat lock" value="att3"><i class="fa fa-fw fa-lock"></i> Lock</button></th>';
            }
        } if($ass[3] == 1) {
            if($this->is_attlocked($markid, 'esem')) {
                $foo[$j++] = '<button type="button" class="btn cell-green btn-sm btn-flat unlock" value="esem"><i class="fa fa-fw fa-unlock"></i> Unlock</button></th>';
            } else {
                $foo[$j++] = '<button type="button" class="btn cell-red btn-sm btn-flat lock" value="esem"><i class="fa fa-fw fa-lock"></i> Lock</button></th>';
            }
        }
        $res = array('result' => 'true', 'tbody' => $arr, 'footer' => $foo);
        return $res;
    }

    public function subject_enrolled_student($markid, $ass, $att) {
        $mark = $this->markid_details($markid);
        $data = array(
                'MARKID' => $markid,
                'FLAG !=' => 'D',
                'SESID' => $mark['sesid']
            );
        $this->db->select('REGNO');
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $reg_query = $this->db->get('REGISTER');

        $data = array('MARKID' => $markid);
        $this->db->select('REGNO, ASS1, ASS2, ASS3, ATT1, ATT2, ATT3, ATTPER, ESEM, TOTMARK, RESULT, SESMARK');
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $mark_query = $this->db->get('MARKS');

        $flag = 0;
        $record = "";

        if($mark_query->num_rows() != 0 && $mark_query->num_rows() === $reg_query->num_rows()) {
            $record = $mark_query->result();            
        } else {            
            $record = $reg_query->result();
            $flag = 1;
            $ass1 = $ass2 = $ass3 = '';
            $att1 = $att2 = $att3 = '';
            $iass = $esem = '';
            $percent = $grade = $totmark = '';
        }
        $table = '';

        $col_count = 0;
        if($att[0] == 1) $col_count++;
        if($att[1] == 1) $col_count++;
        if($att[2] == 1) $col_count++;
        if($att[3] == 1) $col_count++;
        if($ass[0] == 1) $col_count++;
        if($ass[1] == 1) $col_count++;
        if($ass[2] == 1) $col_count++;
        if($ass[3] == 1) $col_count++;
        if($ass[4] == 1) $col_count++;
        if($ass[5] == 1) $col_count++;
        if($ass[6] == 1) $col_count++;
        $cwidth = 7;
        $rwidth = 10;
        $nwidth = 85 - ($col_count * 7);

        $m_att1 = $this->get_max_att($markid, 'att1');
        $m_att2 = $this->get_max_att($markid, 'att2');
        $m_att3 = $this->get_max_att($markid, 'att3');

        $count = 1;
        $table .= '<thead>';
        $table .= '<tr>';
        $table .= '<th width="'.($cwidth-2).'%" class="cell-blue">S.No.</th>';
        $table .= '<th width="'.$rwidth.'%" class="cell-blue">Register No.</th>';
        $table .= '<th width="'.$nwidth.'%" class="cell-blue">Student\'s Name</th>';
        if($att[0] == 1)
            $table .= '<th width="'.$cwidth.'%" class="cell-21">Att 1</th>';
        if($att[1] == 1)
            $table .= '<th width="'.$cwidth.'%" class="cell-21">Att 2</th>';
        if($att[2] == 1)
            $table .= '<th width="'.$cwidth.'%" class="cell-21">Att 3</th>';
        if($att[3] == 1)
            $table .= '<th width="'.$cwidth.'%" class="cell-21">Att %</th>';
        if($ass[0] == 1)
            $table .= '<th width="'.$cwidth.'%" class="cell-28">Assess 1</th>';
        if($ass[1] == 1)
            $table .= '<th width="'.$cwidth.'%" class="cell-28">Assess 2</th>';
        if($ass[2] == 1)
            $table .= '<th width="'.$cwidth.'%" class="cell-28">Assess 3</th>';
        if($ass[3] == 1)
            $table .= '<th width="'.$cwidth.'%" class="cell-28">I. Assess.</th>';
        if($ass[4] == 1)
            $table .= '<th width="'.$cwidth.'%" class="cell-28">E. Sem.</th>';
        if($ass[5] == 1)
            $table .= '<th width="'.$cwidth.'%" class="cell-28">Tot. Mark</th>';
        if($ass[6] == 1)
            $table .= '<th width="'.$cwidth.'%" class="cell-43">Grade</th>';
        $table .= '</tr>';
        $table .= '</thead>';
        $table .= '<tbody>';

        $regular = $shortage = $reappear = '';
        $reg_count = $sa_count = $ra_count = 0;

            foreach($record as $r) {
                if($flag == 0) {
                    $ass1 = $r->ASS1;
                    $ass2 = $r->ASS2;
                    $ass3 = $r->ASS3;
                    $att1 = $r->ATT1;
                    $att2 = $r->ATT2;
                    $att3 = $r->ATT3;
                    $iass = $r->SESMARK;
                    $esem = $r->ESEM;
                    $totmark = $r->TOTMARK;
                    $grade = $r->RESULT;
                    $percent = $r->ATTPER;
                    if($ass1 == '') $ass1 = '';
                    if($ass2 == '') $ass2 = '';
                    if($ass3 == '') $ass3 = '';
                    if($esem == '') $esem = '';
                    if($att1 == '-1') $att1 = '';
                    if($att2 == '-1') $att2 = '';
                    if($att3 == '-1') $att3 = '';
                }

                $student = $this->GM->get_student_info($r->REGNO);

                if($this->is_reappear($r->REGNO, $mark['subcode'])) {
                    $reg_count++;
                    $count = $reg_count;
                } elseif($this->is_shortage($r->REGNO, $mark['subcode'])) {
                    $sa_count++;
                    $count = $sa_count;
                } else {
                    $ra_count++;
                    $count = $ra_count;
                }

                $trow = '<tr>';
                $trow .= '<td width="'.($cwidth-2).'%">'.$count.'</td>';
                $trow .= '<td width="'.$rwidth.'%">'.$r->REGNO.'</td>';
                $trow .= '<td width="'.$nwidth.'%" class="text-left">'.$student['name'].'</td>';
                if($att[0] == 1)
                    $trow .= '<td width="'.$cwidth.'%">'.$att1.'</td>';
                if($att[1] == 1)
                    $trow .= '<td width="'.$cwidth.'%">'.$att2.'</td>';
                if($att[2] == 1)
                    $trow .= '<td width="'.$cwidth.'%">'.$att3.'</td>';
                if($att[3] == 1) {
                    if($percent >= 75)
                        $trow .= '<td width="'.$cwidth.'%" class="text-green">'.$percent.'</td>';
                    elseif($percent >= 65)
                        $trow .= '<td width="'.$cwidth.'%" class="text-yellow">'.$percent.'</td>';
                    else
                        $trow .= '<td width="'.$cwidth.'%" class="text-red">'.$percent.'</td>';
                }
                if($ass[0] == 1)
                    $trow .= '<td width="'.$cwidth.'%">'.$ass1.'</td>';
                if($ass[1] == 1)
                    $trow .= '<td width="'.$cwidth.'%">'.$ass2.'</td>';
                if($ass[2] == 1)
                    $trow .= '<td width="'.$cwidth.'%">'.$ass3.'</td>';
                if($ass[3] == 1)
                    $trow .= '<td width="'.$cwidth.'%">'.$iass.'</td>';
                if($ass[4] == 1)
                    $trow .= '<td width="'.$cwidth.'%">'.$esem.'</td>';
                if($ass[5] == 1)
                    $trow .= '<td width="'.$cwidth.'%">'.$totmark.'</td>';
                if($ass[6] == 1)
                    $trow .= '<td width="'.$cwidth.'%">'.$grade.'</td>';
                $trow .= '</tr>';

                if($this->is_reappear($r->REGNO, $mark['subcode'])) {
                    $reappear .= $trow;
                } elseif($this->is_shortage($r->REGNO, $mark['subcode'])) {
                    $shortage .= $trow;
                } else
                    $regular .= $trow;
            }
            $table .= $regular;
            if($shortage != '') {
                $table .= '<tr><td class="cell-blue" width="100%">List of Shortage of Attendance Students</td></tr>';
                $table .= $shortage;
            }
            if($reappear != '') {
                $table .= '<tr><td class="cell-blue" width="100%">List of Reappear Students</td></tr>';
                $table .= $reappear;
            }
            $table .= '</tbody>';
            $table .= '<thead>';
            $table .= '<tr>';
            $table .= '<th width="'.($nwidth+$cwidth+$rwidth - 2).'%">Lock/Unlock</th>';
            if($att[0] == 1) {
                if($this->is_attlocked($markid, 'att1')) {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-tomato btn-sm btn-flat" value="att1"><i class="fa fa-fw fa-lock"></i></button></th>';
                } else {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-green btn-sm btn-flat lock" value="att1"><i class="fa fa-fw fa-unlock"></i></button></th>';
                }
            } if($att[1] == 1) {
                if($this->is_attlocked($markid, 'att2')) {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-tomato btn-sm btn-flat" value="att2"><i class="fa fa-fw fa-lock"></i></button></th>';
                } else {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-green btn-sm btn-flat lock" value="att2"><i class="fa fa-fw fa-unlock"></i></button></th>';
                }
            } if($att[2] == 1) {
                if($this->is_attlocked($markid, 'att3')) {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-tomato btn-sm btn-flat" value="att3"><i class="fa fa-fw fa-lock"></i></button></th>';
                } else {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-green btn-sm btn-flat lock" value="att3"><i class="fa fa-fw fa-unlock"></i></button></th>';
                }
            } if($att[3] == 1) {
                if($this->is_attlocked($markid, 'att1') && $this->is_attlocked($markid, 'att2') && $this->is_attlocked($markid, 'att3')) {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-tomato btn-sm btn-flat"><i class="fa fa-fw fa-lock"></i></button></th>';
                } else {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-green btn-sm btn-flat"><i class="fa fa-fw fa-unlock"></i></button></th>';
                }
            } 
            if($ass[0] == 1) {
                if($this->is_attlocked($markid, 'ass1')) {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-tomato btn-sm btn-flat" value="ass1"><i class="fa fa-fw fa-lock"></i></button></th>';
                } else {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-green btn-sm btn-flat lock" value="ass1"><i class="fa fa-fw fa-unlock"></i></button></th>';
                }
            } if($ass[1] == 1) {
                if($this->is_attlocked($markid, 'ass2')) {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-tomato btn-sm btn-flat" value="ass2"><i class="fa fa-fw fa-lock"></i></button></th>';
                } else {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-green btn-sm btn-flat lock" value="ass2"><i class="fa fa-fw fa-unlock"></i></button></th>';
                }
            } if($ass[2] == 1) {
                if($this->is_attlocked($markid, 'ass3')) {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-tomato btn-sm btn-flat" value="ass3"><i class="fa fa-fw fa-lock"></i></button></th>';
                } else {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-green btn-sm btn-flat lock" value="ass3"><i class="fa fa-fw fa-unlock"></i></button></th>';
                }
            } if($ass[3] == 1) {
                if($this->is_attlocked($markid, 'ass1') && $this->is_attlocked($markid, 'ass2') && $this->is_attlocked($markid, 'ass3')) {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-tomato btn-sm btn-flat"><i class="fa fa-fw fa-lock"></i></button></th>';
                } else {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-green btn-sm btn-flat"><i class="fa fa-fw fa-unlock"></i></button></th>';
                }
            } if($ass[4] == 1) {
                if($this->is_attlocked($markid, 'esem')) {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-tomato btn-sm btn-flat" value="esem"><i class="fa fa-fw fa-lock"></i></button></th>';
                } else {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-green btn-sm btn-flat lock" value="esem"><i class="fa fa-fw fa-unlock"></i></button></th>';
                }
            }

            if($ass[5] == 1) {
                if($this->is_attlocked($markid, 'esem')) {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-tomato btn-sm btn-flat"><i class="fa fa-fw fa-lock"></i></button></th>';
                } else {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-green btn-sm btn-flat"><i class="fa fa-fw fa-unlock"></i></button></th>';
                }
            } 

            if($ass[6] == 1) {
                if($this->is_attlocked($markid, 'result')) {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-tomato btn-sm btn-flat"><i class="fa fa-fw fa-lock"></i></button></th>';
                } else {
                    $table .= '<th  width="'.$cwidth.'%"><button type="button" class="btn cell-green btn-sm btn-flat lock" value="result"><i class="fa fa-fw fa-unlock"></i></button></th>';
                }
            } 
            $table .= '</tr>';
            $table .= '</thead>';
        
        return $table;
    }

    public function generate_prevention_list($markid) {        
        $mark = $this->markid_details($markid);
        if($mark['sesid'] != $this->sesid) {
            return array('result' => 'false', 'msg' => 'Session Locked! Cannot generate prevention list');
        }

        if($this->get_mark_status($markid) < 1) {
            return array('result' => 'false', 'msg' => 'Lock all the attendance to generate prevention list!');
        }

        $n =$this->calculate_attendance($markid);

        $this->db->select('REGNO, ATTPER');
        $data = array('MARKID' => $markid);
        $this->db->where($data);
        $query = $this->db->get('MARKS');

        if($query->num_rows() > 0) {
            $record = $query->result();

            $this->db->trans_start();
            foreach($record as $r) {               
                if($r->ATTPER < 75) {
                    if($r->ATTPER < 65) $status = 0;
                    else $status = 1;
                    $student = $this->GM->get_student_info($r->REGNO);
                    $data = array('REGNO' => $r->REGNO, 
                        'BRCODE' => $student['brcode'],
                        'MARKID'  => $markid,
                        'SUBCODE' => $mark['subcode'],
                        'SESID' => $this->sesid,
                        'PDATE' => date('d-M-Y'),
                        'STATUS' => $status);
                    $this->db->insert('PREVENTED', $data);
                }
            }
            $this->db->trans_complete();
            return array('result' => 'true', 'msg' => 'Prevention list generated!');
        }
    }

    public function is_attlocked($markid, $column) {
        $column = strtoupper($column);
        $data = array('MARKID' => $markid, $column => 1);
        $this->db->select($column);
        $this->db->where($data);
        $query = $this->db->get('ATTSTATUS');
        if($query->num_rows() === 1) {
            return TRUE;
        } else
            return FALSE;
    }

    public function get_attlock_time($markid, $column) {
        $column = strtoupper($column);
        $data = array('MARKID' => $markid);
        $this->db->select($column);
        $this->db->where($data);
        $query = $this->db->get('ATTLOCK');
        if($query->num_rows() === 1) {
            return $query->row()->{$column};
        } else
            return '';
    }

    public function get_mark_status($markid) {
        $data = array('MARKID' => $markid);
        $this->db->select('STATUS');
        $this->db->where($data);
        $query = $this->db->get('ATTSTATUS');
        if($query->num_rows() === 1) {
            $row = $query->row();
            return $row->STATUS;
        } else
            return 0;
    }

    public function is_mark_empty($markid, $column) {
        $column = strtoupper($column);
        $data = array('MARKID' => $markid);
        $this->db->select($column.' AS COL');
        $this->db->where($data);
        $query = $this->db->get('MARKS');
        if($query->num_rows() > 0) {
            $row = $query->row();
            if($row->COL == '' || $row->COL == '-1')
                return TRUE;
            else
                return FALSE;
        } else
            return TRUE;
    }

    public function is_all_filled($markid, $column) {
        $data = array('MARKID' => $markid);
        switch($column) {
            case 'ASS1':
            case 'ASS2':
            case 'ASS3':
            case 'ESEM':
            case 'RESULT':
                $data[$column] = NULL;
                //$data = array(NULL, '0');
                break;
            case 'ATT1':
            case 'ATT2':
            case 'ATT3':
                $data[$column] = -1;
                //$data = array(-1);
        }
        //$this->db->where_in($column, $data);
        $this->db->where($data);
        $query = $this->db->get('MARKS');
        if($query->num_rows() > 0) {
            return FALSE;
        } else
            return TRUE;
    }

    public function lock_attstatus($markid, $column) {
        $column = strtoupper($column);
        if($column == 'ESEM' && $this->get_ses_weightage($markid, 'esem') == 0) {
            //ESEM weightage is 0. Skip whether all mark is filled check.
        } elseif(!$this->is_all_filled($markid, $column)) {
            return array('result' => 'false', 'msg' => 'Please enter all the student\'s data before locking.');
        }

        $data = array('MARKID' => $markid);
        $this->db->where($data);
        $query = $this->db->get('ATTSTATUS');
        if($query->num_rows() === 1) {
            $this->db->set($column, 1);
            $this->db->where($data);
            $this->db->update('ATTSTATUS');

            $this->db->set($column, date('d-M-Y h:i:sa'));
            $this->db->where($data);
            $this->db->update('ATTLOCK');            
        } else {
            $data = array('MARKID' => $markid, $column => date('d-M-Y h:i:sa'));
            $this->db->insert('ATTLOCK', $data);
            $data = array('MARKID' => $markid, $column => 1);
            $this->db->insert('ATTSTATUS', $data);
        }        

        switch($column) {
            case 'ASS1': $cname = 'Assessment 1'; break;
            case 'ASS2': $cname = 'Assessment 2'; break;
            case 'ASS3': $cname = 'Assessment 3'; break;
            case 'ATT1': $cname = 'Attendance 1'; break;
            case 'ATT2': $cname = 'Attendance 2'; break;
            case 'ATT3': $cname = 'Attendance 3'; break;
            case 'ESEM': $cname = 'End Semester'; break;
            case 'RESULT': $cname = 'Grade'; break;
            default: $cname = '';
        }

        $generate = '';

        switch($column) {
            case 'ASS1':
            case 'ASS2':
            case 'ASS3':
                #updating sessional marks
                $this->calculate_mark($markid);
                if($this->is_attlocked($markid, 'ASS1') 
                    && $this->is_attlocked($markid, 'ASS2') 
                    && $this->is_attlocked($markid, 'ASS3')) {
                    $status = $this->get_mark_status($markid);
                    if($status >= 1) {
                        if($this->PM->is_locked($markid)) $status =  3;
                        else $status = 2;

                        $this->db->set('STATUS', $status);
                        $this->db->where($data);
                        $this->db->update('ATTLOCK');

                        $this->db->set('STATUS', $status);
                        $this->db->where($data);
                        $this->db->update('ATTSTATUS');
                    } else {
                        $this->db->set($column, 0);
                        $this->db->where($data);
                        $this->db->update('ATTSTATUS');
                        return array('result' => 'false', 'msg' => 'Lock attendance before locking assessment.');
                    }
                }
                break;
            case 'ATT1':
            case 'ATT2':
            case 'ATT3':
                $n = $this->calculate_attendance($markid);
                if($this->is_attlocked($markid, 'ATT1') 
                    && $this->is_attlocked($markid, 'ATT2') 
                    && $this->is_attlocked($markid, 'ATT3')) {
                        #if all the attendance has been locked.. update status from 0 -> 1
                        $this->db->set('STATUS', 1);
                        $this->db->where($data);
                        $this->db->update('ATTLOCK');

                        $this->db->set('STATUS', 1);
                        $this->db->where($data);
                        $this->db->update('ATTSTATUS');

                        #generating prevention list
                        //$generate = $this->generate_prevention_list($markid);
                }
                break;
            case 'ESEM':
            		$this->calculate_totmark($markid);
                        #if ESEM has been locked.. update status from 3 -> 4
                    if($this->get_mark_status($markid) >= 3){
                        $this->db->set('STATUS', 4);
                        $this->db->where($data);
                        $this->db->update('ATTLOCK');

                        $this->db->set('STATUS', 4);
                        $this->db->where($data);
                        $this->db->update('ATTSTATUS');
                    } else {
                    	$this->db->set($column, 0);
                        $this->db->where($data);
                        $this->db->update('ATTSTATUS');
                        return array('result' => 'false', 'msg' => 'Lock all assessment before locking End Semester.');
                    }
                break;
            case 'RESULT':
            	#if Grade has been locked.. update status from 4 -> 5
                if($this->lock_grade($markid)) {
            	   $this->db->set('STATUS', 5);
            	   $this->db->where($data);
            	   $this->db->update('ATTLOCK');

            	   $this->db->set('STATUS', 5);
            	   $this->db->where($data);
            	   $this->db->update('ATTSTATUS');
                }
                break;

        }

        if($column != 'ESEM' && $column != 'RESULT')
            $result = $this->send_lock_alert($markid, $cname);
        else $result['msg'] = '';

        $msg = 'Locked '.$cname.' successfully. ';
        if($this->send_locked_mark($markid, $column, $cname)) {
            $msg .= 'Successfully sent attendance & mark details to your email. ';
        } else {
            $msg .= 'Failed sending attendance & mark details to your email. ';
        }
        $msg .= $result['msg'];
        if($generate != '') {
            $msg .= ' '.$generate['msg'];
        }
        return array('result' => 'true', 'msg' => $msg);
    }
	
	#send locked marks email to staff
    public function send_locked_mark($markid, $column, $cname) {        
        $data = array('MARKID' => $markid);
        $this->db->select('REGNO, '.$column);
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $query = $this->db->get('MARKS');

        if($query->num_rows() > 0) {
            $records = $query->result();

            $sno = 1; $tbody = '';
            foreach($records as $r) {
                $student = $this->GM->get_student_info($r->REGNO);                
                $tbody .= '<tr>';
                $tbody .= '<td>'.$sno++.'</td>';
                $tbody .= '<td>'.$r->REGNO.'</td>';
                $tbody .= '<td>'.$student['name'].'</td>';
                $tbody .= '<td>'.$r->{$column}.'</td>';
                $tbody .= '</tr>';

            }

            $mdetail = $this->markid_details($markid);
            $subject = $this->GM->get_subject_info($mdetail['subcode']);


            $message  = '<html><body>';
            $message .= '<h2 style="text-align: center"><strong>Anna University, Chennai - 6000025</strong></h2>';
            $message .= '<h3 style="text-align: center"><strong>Additional Controller of Examinations</strong></h3>';
            $message .= '<h3 style="text-align: center"><strong>Choice Based Credit System - Regulation 2015</strong></h3>';
            $message .= '<h3 style="text-align: center"><strong>Asessment & Mark Details</strong></h3>';
            $message .= '<h3 style="text-align: center"><strong>'.$mdetail['subcode'].' - '.$subject['subname'].'</strong></h3>';
            $message .= '<table border="1" style="border-collapse:collapse; margin-left: auto; margin-right: auto" cellspacing="0" cellpadding="5">';
            $message .= '<thead><tr>';
            $message .= '<th style="background-color: #0073b7 !important; color: #ffffff !important;">S.No.</th>';
            $message .= '<th style="background-color: #0073b7 !important; color: #ffffff !important;">Register No.</th>';
            $message .= '<th style="background-color: #0073b7 !important; color: #ffffff !important;">Name</th>';
            $message .= '<th style="background-color: #00735c !important; color: #ffffff !important;">'.$cname.'</th>';
            $message .= '</tr>';
            $message .= '</thead><tbody>';
            $message .= $tbody;
            $message .= '</tbody></table>';            
            $message .= '</body></html>';
            
            $data = array('STAFFID' => $mdetail['staffid']);
            $this->db->select('EMAIL');
            $this->db->where($data);
            $query = $this->db->get('STAFF');

            if($query->num_rows() < 1)
                return FALSE;
            $row = $query->row();
			
			$this->email
                ->from('noreply@acoe.annauniv.edu', 'noreply')
                ->to('admin@acoe.annauniv.edu')
                ->subject('Student Attendance / Mark details -'.$markid.' - '.$mdetail['subcode'].' - '.$cname)
                ->message($message)
                ->set_mailtype('html');
				
			$res = $this->email->send();
				
            
            $this->email
                ->from('admin@acoe.annauniv.edu', 'ACOE')
                ->to($row->EMAIL)
                ->subject('Student Attendance / Mark details - '.$mdetail['subcode'].' - '.$cname)
                ->message($message)
                ->set_mailtype('html');

            if($this->email->send()) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }
	
	#send lock email alerts to students
    public function send_lock_alert($markid, $cname) {
        $subject = $cname. ' updated';
        $mdetail = $this->markid_details($markid);
        $subjects = $this->GM->get_subject_info($mdetail['subcode']);
        $staff = $this->GM->get_staff_info($mdetail['staffid']);
        $message = $cname.' of '.$mdetail['subcode'].' - '.$subjects['subname'].' was locked by '.$staff['staffname'].' on '.date('d-M-Y h:i:sa');

        $this->db->select('REGNO');
        $data = array('MARKID' => $markid);
        $this->db->where($data);        
        $query = $this->db->get('MARKS');
        if($query->num_rows() > 0) {
            $record = $query->result();
            $tcount = $query->num_rows();
            $scount = 0;
            foreach($record as $r) {
                $data = array('USERNAME' => $r->REGNO);

                $this->db->select('EMAIL');
                $this->db->where($data);
                $query = $this->db->get('STUD_LOGIN');
                if($query->num_rows() != 1) {
                    continue;
                }
                $row = $query->row();
                
                $this->email
                    ->from('admin@acoe.annauniv.edu', 'ACOE')
                    ->to($row->EMAIL)
                    ->subject($subject)
                    ->message($message)
                    ->set_mailtype('html');

                if($this->email->send()) {
                    $scount++;
                }
            }
            //mail to directors

            return array('result' => 'true', 'msg' => 'Sent mail to '.$scount.'/'.$tcount.' students');

        } else return array('result' => 'false', 'msg' => 'No student record found');
        
    }

    public function get_prevented_count($markid) {
        $data = array('MARKID' => $markid);
        $this->db->select('COUNT(*) AS CNT');
        $this->db->where($data);
        $query = $this->db->get('PREVENTED');
        $row = $query->row();
        return $row->CNT;
    }

    public function get_prevented_list_status($staffid, $sesid) {
        $data = array('STAFFID' => $staffid, 'SESID' => $sesid);
        $this->db->select('MARKID, SUBCODE, SLOTNO, SUBNO');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');
        #echo $this->db->last_query();
        if($query->num_rows() > 0) {
            $record = $query->result();
            $table = ''; $sno = 1;
            foreach($record as $r) {
                $status = $this->get_mark_status($r->MARKID);
                $subject = $this->GM->get_subject_info($r->SUBCODE);
                $table .= '<tr>';
                $table .= '<td>'.$sno++.'</td>';
                $table .= '<td>'.$r->SUBCODE.'</td>';
                $table .= '<td class="text-left">'.$subject['subname'].'</td>';
                $table .= '<td>'.$r->SLOTNO.'- '.$this->GM->get_code($r->SUBNO).'</td>';
                if($status < 1)
                    $table .= '<td><span class="label label-danger">Pending</span></td>';
                else
                    $table .= '<td><span class="label label-success">Generated</span></td>';
                $table .= '<td>'.$this->get_prevented_count($r->MARKID).'</td>';
                $table .= '<td><button type="button" class="btn btn-sm btn-flat cell-25 view" value="'.$this->MOD->encode($r->MARKID).'">View</button></td>';
                $table .= '</tr>';
            }
            return array('result' => 'true', 'msg' => $table);
        } else {
            return array('result' => 'false', 'msg' => 'Prevented status not found');
        }
    }

    public function prevented_student_list($markid, $sesid) {
        $data = array('MARKID' => $markid, 'SESID' => $sesid);
        $this->db->select('REGNO, STATUS');
        $this->db->where($data);
        $query = $this->db->get('PREVENTED');
        if($query->num_rows() > 0) {
            $record = $query->result();
            $table = '';
            $sno = 1;
            foreach($record as $r) {
                $percent = $this->get_student_attendance($r->REGNO, $markid);
                $student = $this->GM->get_student_info($r->REGNO);
                $table .= '<tr>';
                $table .= '<td width="10%">'.$sno++.'</td>';
                $table .= '<td width="20%">'.$r->REGNO.'</td>';
                $table .= '<td width="20%" class="text-left">'.$student['name'].'</td>';    
                if($percent >= 75)
                    $table .= '<td width="20%" class="text-green">'.$percent.'</td>';
                elseif($percent >= 65)
                    $table .= '<td width="20%" class="text-yellow">'.$percent.'</td>';
                else
                    $table .= '<td width="20%" class="text-red">'.$percent.'</td>';
                
                if($r->STATUS < 2)
                    $table .= '<td width="10%"><span class="label label-danger">Prevented</span></td>';
                else
                    $table .= '<td width="10%"><span class="label label-success">Allowed</span></td>';
                if($r->STATUS == 1) {
                    $table .= '<td width="20%"><button type="button" class="btn btn-sm btn-flat cell-red reject" value="'.$this->MOD->encode($markid).'">Reject</button> &nbsp;';
                    $table .= '<button type="button" class="btn btn-sm btn-flat cell-green allow" value="'.$this->MOD->encode($markid).'">Allow</button></td>';
                } else {
                    $table .= '<td width="20%"></td>';
                }
                $table .= '</tr>';
            }
            return array('result' => 'true', 'msg' => $table);
        } else
            return array('result' => 'false', 'msg' => 'Prevention list not yet generated.');
    }

    public function prevention_allow($regno, $markid) {
        $data = array('MARKID' => $markid, 'REGNO' => $regno);
        $udata = array('STATUS' => 2, 'RDATE' => date('d-M-Y'));
        $this->db->where($data);
        $this->db->update('PREVENTED', $udata);
        return array('result' => 'true', 'msg' => $regno.' allowed to attend exam');
    }

    public function prevention_reject($regno, $markid) {
        $data = array('MARKID' => $markid, 'REGNO' => $regno);
        $udata = array('STATUS' => 0, 'RDATE' => date('d-M-Y'));
        $this->db->where($data);
        $this->db->update('PREVENTED', $udata);
        return array('result' => 'true', 'msg' => $regno.' rejected to attend exam');
    }

    public function get_student_attendance($regno, $markid) {
        $data = array('REGNO' => $regno, 'MARKID' => $markid);
        $this->db->select('ATTPER');
        $this->db->where($data);
        $query = $this->db->get('MARKS');
        if($query->num_rows() > 0) {
            $row = $query->row();
            return $row->ATTPER;
        }
        return 0;
    }

    public function unlock_attstatus($markid, $column) {
        $data = array('MARKID' => $markid);
        $this->db->select('STATUS');
        $this->db->where($data);
        $query = $this->db->get('ATTLOCK');
        if($query->num_rows() === 1) {
            $row = $query->row();
            switch($column) {
                case 'ass1':
                case 'ass2':
                case 'ass3':
                    if($row->STATUS > 0) {
                        $res = array('result' => 'false', 'msg' => 'Assessment has been locked. Contact administrator to unlock.');
                        return $res;
                    }
                    break;
                case 'att1':
                case 'att2':
                case 'att3':
                    if($row->STATUS > 1) {
                        $res = array('result' => 'false', 'msg' => 'Attendance has been locked. Contact administrator to unlock.');
                        return $res;
                    }                    
                    break;
                default:
                    $res = array('result' => 'false', 'msg' => 'Invalid column selected.');
                    return $res;
            }

            $this->db->set($column, 0);
            $this->db->where($data);
            $this->db->update('ATTSTATUS');
        } else {
            $res = array('result' => 'false', 'msg' => 'Failed getting sessional data.');
            return $res;
        }

        switch($column) {
                case 'ass1': $cname = 'Assessment 1'; break;
                case 'ass2': $cname = 'Assessment 2'; break;
                case 'ass3': $cname = 'Assessment 3'; break;
                case 'att1': $cname = 'Attendance 1'; break;
                case 'att2': $cname = 'Attendance 2'; break;
                case 'att3': $cname = 'Attendance 3'; break;
                case 'esem': $cname = 'End Semester';break;
                default: $cname = '';
            }
        $res = array('result' => 'true', 'msg' => 'Unlocked '.$cname.' successfully.');
        return $res;
    }

    public function custom_attlock($markid, $column) {
        $column = strtoupper($column);
        $data = array('MARKID' => $markid);
        $this->db->where($data);
        $query = $this->db->get('ATTLOCK');
        if($query->num_rows() === 1) {            
            $this->db->set($column, date('d-M-Y h:i:sa'));
            $this->db->where($data);
            $this->db->update('ATTLOCK');

            $this->db->set($column, 1);
            $this->db->where($data);
            $this->db->update('ATTSTATUS');
        } else {
            $data = array('MARKID' => $markid, $column => date('d-M-Y h:i:sa'));
            $this->db->insert('ATTLOCK', $data);
            $data = array('MARKID' => $markid, $column => 1);
            $this->db->insert('ATTSTATUS', $data);
        }
    }

    public function get_max_att($markid, $column) {
        $column = strtoupper($column);
        $data = array('MARKID' => $markid);
        $this->db->select($column);
        $this->db->where($data);
        $query = $this->db->get('GRADE');
        if($query->num_rows() === 1) {
            $row = $query->row(); 
            return $row->{$column};
        }
        return 0;
    }

    public function get_ses_weightage($markid, $column) {
        $column = strtoupper($column);
        $data = array('MARKID' => $markid);
        $this->db->select($column);
        $this->db->where($data);
        $query = $this->db->get('GRADE');
        if($query->num_rows() === 1) {
            $row = $query->row();            
            return $row->{$column};
        }
        return 0;
    }

    public function get_ses_custom($markid) {
        $data = array('MARKID' => $markid);
        $this->db->select('SES1, SES2, SES3');
        $this->db->where($data);
        $query = $this->db->get('CUSTOM');
        if($query->num_rows() === 1) {
            $row = $query->row();
            $record['ass1'] = $row->SES1;
            $record['ass2'] = $row->SES2;
            $record['ass3'] = $row->SES3;
            return $record;
        }
        return FALSE;
    }

    public function get_ses_selection($markid) {
        $data = array('MARKID' => $markid);
        $this->db->select('SELECTION');
        $this->db->where($data);
        $query = $this->db->get('GRADE');
        if($query->num_rows() > 0) {
            $row = $query->row();            
            return $row->SELECTION;
        }
        return 0;
    }

    #if options are not generated in mark update - comment this /*
    public function get_soptions($markid, $type) {
        $option = '';
        $val = '';
        $count = 0;

        $sessional = $this->get_ses_custom($markid);
        $sessional['selection'] = $this->get_ses_selection($markid);

        $ass_one = $ass_two = $ass_three = TRUE;
        $att_one = $att_two = $att_three = TRUE;
        $mode = 0;

        switch($sessional['selection']) {
            case 1:
                $mode = 1;
                if($this->is_mark_empty($markid, 'ass1')) $ass_two = FALSE;
                if($this->is_mark_empty($markid, 'ass2')) $ass_three = FALSE;
                if($this->is_mark_empty($markid, 'att1')) $att_two = FALSE;
                if($this->is_mark_empty($markid, 'att2')) $att_three = FALSE;
                break;
            case 2:
                $mode = 2;
                if($this->is_mark_empty($markid, 'ass1')) $ass_two = FALSE;
                if($this->is_mark_empty($markid, 'att1')) $att_two = FALSE;
                $ass_three = $att_three = FALSE;
                break;
            case 3:
                $mode = 3;
                $ass_two = $att_two = FALSE;
                $ass_three = $att_three = FALSE;
                break;
            case 4:
                if($sessional['ass1'] == 0) $ass_one = $att_one = FALSE;
                else {
                    $mode = 3; //Assuming only one
                }

                if($sessional['ass2'] == 0) $ass_two = $att_two = FALSE;
                else {
                    $mode = 2; //Now 2 exists, Assuming only two
                    if($sessional['ass1'] != 0 && $this->is_mark_empty($markid, 'ass1')) $ass_two = FALSE;
                    if($sessional['ass1'] != 0 && $this->is_mark_empty($markid, 'att1')) $att_two = FALSE;
                }
                
                if($sessional['ass3'] == 0) $ass_three = $att_three = FALSE;
                else {
                    $mode = 1; //All 3 exsits. Assuming all 3 equal weightage
                    if($sessional['ass2'] != 0 && $this->is_mark_empty($markid, 'ass2')) $ass_three = FALSE;
                    if($sessional['ass2'] != 0 && $this->is_mark_empty($markid, 'att2')) $att_three = FALSE;
                }
                
                break;
            default:
                $res = array('result' => false, 'msg' => 'Invalid Selection');
                return $res;
        }

        if($type == 'ass') {            
            if($ass_one && !$this->is_attlocked($markid, 'ass1')) {
                $count++; 
                $val = 'ass1';
                $option .= '<option value="ass1">Assessment 1</option>';
            }
            if($ass_two && !$this->is_attlocked($markid, 'ass2')) {
                $count++; 
                $val = 'ass2';
                $option .= '<option value="ass2">Assessment 2</option>';
            }
            if($ass_three && !$this->is_attlocked($markid, 'ass3')) {
                $count++; 
                $val = 'ass3';
                $option .= '<option value="ass3">Assessment 3</option>';
            }
            if($count == 0)
                return array('result' => 'false', 'msg' => 'Assessment is Locked');
        } elseif($type == 'att') {  
            if($att_one && !$this->is_attlocked($markid, 'att1') && ($this->ses_lock_status($markid) > 1)) {
                $count++; 
                $val = 'att1';
                $option .= '<option value="att1">Attendance 1</option>';
            }
            if($att_two && !$this->is_attlocked($markid, 'att2') && ($this->ses_lock_status($markid) > 2)) {
                $count++; 
                $val = 'att2';
                $option .= '<option value="att2">Attendance 2</option>';
            }
            if($att_three && !$this->is_attlocked($markid, 'att3') && ($this->ses_lock_status($markid) > 3)) {
                $count++; 
                $val = 'att3';
                $option .= '<option value="att3">Attendance 3</option>';
            }
            if($count == 0) {
                switch($this->ses_lock_status($markid)) {
                    case 4: #locked att 3
                            break;
                    case 3: #locked att 2
                        if($mode != 1) break;
                    case 2: #locked att 1
                    case 1: #accepted sess
                            return array('result' => 'false', 'msg' => 'Lock Max. Attendance');
                    default: return array('result' => 'false', 'msg' => 'Invalid Sessional Weightage');
                }
                return array('result' => 'false', 'msg' => 'Attendance is Locked');
            }
        } elseif($type = 'esem') {
			if($this->get_mark_status($markid) < 3) {
				return array('result' => 'false', 'msg' => 'Lock prevention and Sessional, before the end-sem mark entry');
			} elseif($this->get_ses_weightage($markid, 'esem') == 0)
                return array('result' => 'false', 'msg' => 'End Sem. weightage is 0. Lock ESEM.');
            elseif($this->get_mark_status($markid) == 3) {				
                $val = 'esem';
                $option .= '<option value="esem">End Semester</option>';
            } else {
                return array('result' => 'false', 'msg' => 'End Semester is Locked');
            }
        } 

        if($option == '') {
            $res = array('result' => false, 'msg' => 'Invalid Type');
        } else {
            if($count > 1) {
                $option = '<option value=""></option>'.$option;
                $val = '';
            }
            $res = array('result' => 'true', 'msg' => $option, 'val' => $val);
        }
        return $res;
    }

    public function lock_status_check($markid, $column) {
        $res = '';
        switch($column) {
            case 'ass1': $cname = 'Assessment 1'; break;
            case 'ass2': $cname = 'Assessment 2'; break;
            case 'ass3': $cname = 'Assessment 3'; break;
            case 'att1': $cname = 'Attendance 1'; break;
            case 'att2': $cname = 'Attendance 2'; break;
            case 'att3': $cname = 'Attendance 3'; break;
            case 'esem': $cname = 'End Semester';break;
            default: $cname = '';
        }
        if($this->is_attlocked($markid, $column)) {
            $res = array('result' => 'false', 'msg'=>$cname.' is locked.');
        }

        return $res;
    }

    public function edit_mark_form($markid, $type, $column) {
        $mark = $this->markid_details($markid);
        if($mark['sesid'] != $this->sesid) {
            return array('result' => 'false', 'msg' => 'Session locked! Cannot edit (or) update.');
        }
        $res = $this->lock_status_check($markid, $column);
        if($res != '') return $res;

        $data = array(
                'MARKID' => $markid,
                'FLAG !=' => 'D',
                'STATUS' => 1,
                'SESID' => $mark['sesid']
            );
        $this->db->select('REGNO');
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $reg_query = $this->db->get('REGISTER');

        if($column == 'esem')
            $str = 'REGNO, SESMARK, ESEM, STATUS';
        elseif($type == 'ass')
            $str = 'REGNO, ASS1, ASS2, ASS3, STATUS';
        elseif($type == 'att')
            $str = 'REGNO, ATT1, ATT2, ATT3, STATUS';

        $this->db->select($str);
        $data = array('MARKID' => $markid);
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $mark_query = $this->db->get('MARKS');

        $flag = 0;
        $record = "";

        if($mark_query->num_rows() > 0/* && $mark_query->num_rows() === $reg_query->num_rows()*/) {
            $record = $mark_query->result();
            $row = $mark_query->row();
            $status = $row->STATUS;
        } else {            
            $record = $reg_query->result();
            $flag = 1;
            $status = 0;
        }

            $ass = array(0, 0, 0, 0); $att = array(0, 0, 0);
            $max1 = $max2 = $max3 = $count = 0;
            switch($column) {
                case 'ass3': $ass[2] = 1; $count++;
                case 'ass2': $ass[1] = 1; $count++;
                case 'ass1': $ass[0] = 1; $count++; break;
                case 'att3': $att[2] = 1; $count++; $max3 = $this->get_max_att($markid, 'att3');
                case 'att2': $att[1] = 1; $count++; $max2 = $this->get_max_att($markid, 'att2');
                case 'att1': $att[0] = 1; $count++; $max1 = $this->get_max_att($markid, 'att1'); break;
                case 'esem': $ass[3] = 1; $count++; $count++; break;
                default: 
                    return array('result' => 'false', 'msg'=>'Invalid Selection!');
            }            
            $cwidth = 20;
            $nwdith = 80 - $count * $cwidth;
            $sno = 1;
            $table = '<thead>';
            $table .= '<tr>';
            $table .= '<th width="10%" class="cell-blue">S.No</th>';
            $table .= '<th width="10%" class="cell-blue">Register No.</th>';
            $table .= '<th width="'.$nwdith.'%" class="cell-blue">Student Name</th>';
            if($type == 'att') {
                $max = $this->get_max_att($markid, $column);
            }
            
            if($ass[0] == 1)
                $table .= '<th width="'.$cwidth.'%" class="cell-blue">Assessment 1 (Max: 100)</th>';
            if($ass[1] == 1)
                $table .= '<th width="'.$cwidth.'%" class="cell-blue">Assessment 2 (Max: 100)</th>';
            if($ass[2] == 1)
                $table .= '<th width="'.$cwidth.'%" class="cell-blue">Assessment 3 (Max: 100)</th>';
            if($ass[3] == 1) {
                $table .= '<th width="'.$cwidth.'%" class="cell-blue">Internal Assessment</th>';
                $table .= '<th width="'.$cwidth.'%" class="cell-blue">End Semester (100)</th>';
            }
            if($att[0] == 1)
                $table .= '<th width="'.$cwidth.'%" class="cell-blue">Attendance 1 (Max: '.$max1.')</th>';
            if($att[1] == 1)
                $table .= '<th width="'.$cwidth.'%" class="cell-blue">Attendance 2 (Max: '.$max2.')</th>';
            if($att[2] == 1)
                $table .= '<th width="'.$cwidth.'%" class="cell-blue">Attendance 3 (Max: '.$max3.')</th>';
            if($this->is_attlocked($markid, $column)) {
                $res = array('result' => 'false', 'msg'=>'Selected options is locked.');
                return $res;
            }
            $table .= '</tr>';
            $table .= '</thead>';
            $table .= '<tbody>';

            $regular = $shortage = $reappear = '';
            $reg_count = $sa_count = $ra_count = 0;
            $ses_weightage = $this->get_ses_weightage($markid, 'ses') / 2;
            $subject = $this->GM->get_subject_info($mark['subcode']);
			if($subject['subtype'] == 'T' || $subject['subtype'] == 'L') {
				$filter = 'range-filter';
			} else $filter = 'project-filter';

            foreach($record as $r) {
                if($this->is_reappear($r->REGNO, $mark['subcode'])) {
                    $reg_count++;
                    $count = $reg_count;
                } elseif($this->is_shortage($r->REGNO, $mark['subcode'])) {
                    $sa_count++;
                    $count = $sa_count;
                } else {
                    $ra_count++;
                    $count = $ra_count;
                }
                $student = $this->GM->get_student_info($r->REGNO);
                $trow = '<tr>';
                $trow .= '<td width="10%">'.$count.'</td>';
                $trow .= '<td width="10%">'.$r->REGNO.'</td>';
                $trow .= '<td width="'.$nwdith.'%" class="text-left">'.$student['name'].'</td>';
                if(!$this->is_attlocked($markid, $column)) {
                    $ass1 = $ass2 = $ass3 = $att1 = $att2 = $att3 = $esem = '';
                    if($flag == 0) {
                        switch($column) {
                            case 'ass3': $ass3 = $r->ASS3;
                            case 'ass2': $ass2 = $r->ASS2;
                            case 'ass1': $ass1 = $r->ASS1; break;
                            case 'att3': $att3 = $r->ATT3 == -1? '' : $r->ATT3; 
                            case 'att2': $att2 = $r->ATT2 == -1? '' : $r->ATT2;
                            case 'att1': $att1 = $r->ATT1 == -1? '' : $r->ATT1; break;
                            case 'esem': $iassess = $r->SESMARK; $esem = $r->ESEM; break;
                            default: 
                            return array('result' => 'false', 'msg'=>'Invalid Options');                            
                        }

                        if($type == 'att' && $subject['subtype'] == 'T' && $this->is_reappear($r->REGNO, $mark['subcode']) ) {
                            $att1 = $max1;
                            $att2 = $max2;
                            $att3 = $max3;
                            $ra = TRUE;
                        } else
                            $ra = FALSE;
                    }
                    if($type == 'att') {
                        if($att[0] == 1) {                            
                            if($column == 'att1') {
                                if($ra)
                                    $trow .= '<td width="'.$cwidth.'%"><input type="text" class="form-control-inline input-sm '.$filter.'" name="val['.$r->REGNO.']" placeholder="'.$r->REGNO.'" value="'.$att1.'" readonly></td>';
                                else
                                    $trow .= '<td width="'.$cwidth.'%"><input type="text" class="form-control-inline input-sm '.$filter.'" name="val['.$r->REGNO.']" placeholder="'.$r->REGNO.'" value="'.$att1.'"></td>';
                            }
                            else
                                $trow .= '<td width="'.$cwidth.'%">'.$att1.'</td>';
                        }
                        if($att[1] == 1) {
                            if($column == 'att2') {
                                if($ra)
                                    $trow .= '<td width="'.$cwidth.'%"><input type="text" class="form-control-inline input-sm '.$filter.'" name="val['.$r->REGNO.']" placeholder="'.$r->REGNO.'" value="'.$att2.'" readonly></td>';
                                else
                                    $trow .= '<td width="'.$cwidth.'%"><input type="text" class="form-control-inline input-sm '.$filter.'" name="val['.$r->REGNO.']" placeholder="'.$r->REGNO.'" value="'.$att2.'"></td>';
                            }
                            else
                                $trow .= '<td width="'.$cwidth.'%">'.$att2.'</td>';
                        }
                        if($att[2] == 1) {
                            if($column == 'att3') {
                                if($ra)
                                    $trow .= '<td width="'.$cwidth.'%"><input type="text" class="form-control-inline input-sm '.$filter.'" name="val['.$r->REGNO.']" placeholder="'.$r->REGNO.'" value="'.$att3.'" readonly></td>';
                                else
                                    $trow .= '<td width="'.$cwidth.'%"><input type="text" class="form-control-inline input-sm '.$filter.'" name="val['.$r->REGNO.']" placeholder="'.$r->REGNO.'" value="'.$att3.'"></td>';
                            }
                            else
                                $trow .= '<td width="'.$cwidth.'%">'.$att3.'</td>';
                        }
                    }
                    else {
                        if($ass[0] == 1) {
                            if($column == 'ass1')
                                $trow .= '<td width="'.$cwidth.'%"><input type="text" class="form-control-inline input-sm custom-num-filter" name="val['.$r->REGNO.']" placeholder="'.$r->REGNO.'" value="'.$ass1.'"></td>';
                            else
                                $trow .= '<td width="'.$cwidth.'%">'.$ass1.'</td>';
                        }
                        if($ass[1] == 1) {
                            if($column == 'ass2')
                                $trow .= '<td width="'.$cwidth.'%"><input type="text" class="form-control-inline input-sm custom-num-filter" name="val['.$r->REGNO.']" placeholder="'.$r->REGNO.'" value="'.$ass2.'"></td>';
                            else
                                $trow .= '<td width="'.$cwidth.'%">'.$ass2.'</td>';
                        }
                        if($ass[2] == 1) {
                            if($column == 'ass3')
                                $trow .= '<td width="'.$cwidth.'%"><input type="text" class="form-control-inline input-sm custom-num-filter" name="val['.$r->REGNO.']" placeholder="'.$r->REGNO.'" value="'.$ass3.'"></td>';
                            else
                                $trow .= '<td width="'.$cwidth.'%">'.$ass3.'</td>';
                        }
                        if($ass[3] == 1) {
                            $trow .= '<td width="'.$cwidth.'%">'.$iassess.'</td>';
                            if($column == 'esem') {
                                if($this->PM->is_prevented($r->REGNO, $markid, $mark['sesid'])) {
                                    $trow .= '<td width="'.$cwidth.'%"><input type="text" class="form-control-inline input-sm custom-num-filter" name="val['.$r->REGNO.']" value="P" readonly></td>';
                                } elseif($this->is_exam_absent($r->REGNO, $markid, $mark['sesid'])){
                                    $trow .= '<td width="'.$cwidth.'%"><input type="text" class="form-control-inline input-sm custom-num-filter" name="val['.$r->REGNO.']" value="A" readonly></td>';
                                } else {
                                    if($mark['prgm'] == 'P.G' && $subject['subtype'] == 'P' && $r->SESMARK < $ses_weightage) {
                                        $trow .= '<td width="'.$cwidth.'%"><input type="text" class="form-control-inline input-sm custom-num-filter" name="val['.$r->REGNO.']" value="RA" readonly></td>';
                                    } else {
                                        $trow .= '<td width="'.$cwidth.'%"><input type="text" class="form-control-inline input-sm custom-num-filter" name="val['.$r->REGNO.']" placeholder="'.$r->REGNO.'" value="'.$esem.'"></td>';
                                    }
                                }
                            }
                            else
                                $trow .= '<td width="'.$cwidth.'%">'.$esem.'</td>';
                        }
                    }
                }                
                $trow .= '</tr>';
                if($this->is_reappear($r->REGNO, $mark['subcode'])) {
                    $reappear .= $trow;
                } elseif($this->is_shortage($r->REGNO, $mark['subcode'])) {
                    $shortage .= $trow;
                } else {
                    $regular .= $trow;
                }
            }
            $table .= $regular;
            if($shortage != '') {
                $table .= '<tr><td class="cell-blue" width="100%">List of Shortage of Attendance Students</td></tr>';
                $table .= $shortage;
            }
            if($reappear != '') {
                $table .= '<tr><td class="cell-blue" width="100%">List of Reappear Students</td></tr>';
                $table .= $reappear;
            }
            $table .= '<tr class="hidden"><td><input type="hidden" name="id" value="'.$this->MOD->encode($markid).'"/></td>';
            $table .= '<td><input type="hidden" name="type" id="types" value="'.$type.'"/></td>';
            $table .= '<td><input type="hidden" name="option" id="soptions" value="'.$column.'"/></td></tr>';
            $table .= '</tbody>';
            $res = array('result' => 'true', 'msg'=>$table);
            return $res;
        
        return $table;
    }

    public function get_student_info($regno) {
        $data = array('REGNO' => $regno);
        $this->db->select('NAME, BRCODE, BATCH, SEM');
        $this->db->where($data);
        $query = $this->db->get('STUDENT');
        if($query->num_rows() > 0) {
            $row = $query->row();
            $res['name'] = $row->NAME;
            $res['brcode'] = $row->BRCODE;
            $res['batch'] = $row->BATCH;
            $res['sem'] = $row->SEM;
        } else {
            $res['name'] = '';
            $res['brcode'] = '';
            $res['batch'] = '';
            $res['sem'] = '';
        }
        return $res;
    }

    public function update_mark($markid, $type, $column, $marks) {
        $mark = $this->markid_details($markid);
        if($mark['sesid'] != $this->sesid) {
            return array('result' => 'false', 'msg' => 'Session locked! Cannot edit (or) update.');
        }
        $column = strtoupper($column);
        $res = $this->lock_status_check($markid, $column);
        if($res != '') return $res;

        $data = array('MARKID' => $markid, 'FLAG !=' => 'D', 'STATUS' => 1);
        $this->db->where($data);
        $query = $this->db->get('REGISTER');
        if($query->num_rows() < 1) {
            $res = array('result' => 'false', 'msg' => 'Registered students count and Received students count do not match!');
            return $res;
        }

        $data = array('MARKID' => $markid);
        $this->db->select('SESMARK, STATUS');
        $this->db->where($data);
        $query = $this->db->get('MARKS');
        if($query->num_rows() === 0) {
            //Inserting marks for first time            
            foreach ($marks as $key => $value) {
                $data = array(
                        'MARKID' => $markid,
                        'REGNO' => $key,
                        'ATT1' => -1,
                        'ATT2' => -1,
                        'ATT3' => -1,
                        'STATUS' => 0
                    );
                $data[$column] = $value;
                $this->db->insert('MARKS', $data);
            }
            $res = array('result' => 'true', 'msg' => 'Inserted successfully');
            return $res;
        } else {
            $max1 = $this->get_max_att($markid, 'att1');
            $max2 = $this->get_max_att($markid, 'att2');
            $max3 = $this->get_max_att($markid, 'att3');
            $subject = $this->GM->get_subject_info($mark['subcode']);
//var_dump($markid);
//var_dump(ses);
            $ses_weightage = intval($this->get_ses_weightage($markid, 'ses')) / 2;
//var_dump($ses_weightage);
            foreach ($marks as $key => $value) {                
                $data = array(
                        'MARKID' => $markid,
                        'REGNO' => $key
                    );

                //overwriting attendance for reappear student
                if($type == 'att' && $subject['subtype'] == 'T' && $this->is_reappear($key, $mark['subcode']) ) {
                    switch($column) {
                        case 'ATT1': $value = $max1; break;
                        case 'ATT2': $value = $max2; break;
                        case 'ATT3': $value = $max3; break;
                    }
                }
                if($type == 'att' && $value == '')
                    $value = -1;

                $this->db->select($column.',SESMARK');
                $this->db->where($data);
                $query = $this->db->get('MARKS');
                if($query->num_rows() > 0) {
                    $row = $query->row();
                    $old = $row->{$column};
					
                } else $old = '';

                if($column == 'esem' || $column == 'ESEM') {
                    if($this->PM->is_prevented($key, $markid, $mark['sesid']))
                        $value = 'P';
                    elseif($this->is_exam_absent($key, $markid, $mark['sesid']))
                        $value = 'A';
			
                    elseif($mark['prgm'] == 'P.G' && $subject['subtype'] == 'P' && $query->row()->SESMARK < $ses_weightage)
                        $value = 'RA';
                }

                if($old == $value) continue;

                $this->db->set($column, $value);
                $this->db->where($data);
                $this->db->update('MARKS');

                if($value == -1) $value = '';

                $data = array(
                        'MARKID' => $markid,
                        'REGNO' => $key,
                        'STAFFID' => $mark['staffid'],
                        'UPDATED_FIELD' => $column,
                        'OLD_VALUE' => $old,
                        'NEW_VALUE' => $value,
                        'UTIME' => date('d-M-Y h:i:sa')
                    );
                $this->db->insert('MARK_LOG', $data);
            }
            if($type == 'att')
                $n = $this->calculate_attendance($markid);
            elseif($type == 'ass') {
                $n = $this->calculate_mark($markid);
            } else {
                if($this->is_all_filled($markid, $column)) {
                    $n = $this->calculate_totmark($markid);
                }
            }
            $res = array('result' => 'true', 'msg' => 'Updated successfully');
            return $res;
        }
    }

    public function get_tt($sesid, $staffid = null, $mode = null) {
        if($staffid == null) $staffid = $this->staffid;
        $tt = $this->TT->get_staff_tt($staffid, $sesid, $mode);
        $this->load->model('Enrollment_model', 'EM');
        $table = $this->EM->tt_to_table($tt, $sesid);
        return $table;
    }

    public function get_staff_sub($sesid, $staffid = null) {
        if($staffid == null) $staffid = $this->staffid;
        $data = array('STAFFID' => $staffid, 'SESID' => $sesid);
        $this->db->SELECT('MARKID, SUBCODE, SLOTNO, SUBNO, CLASSROOM');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');

        $this->load->model('Enrollment_model', 'EM');       
        
        if($query->num_rows() > 0) {
            $record = $query->result();
            $i = 0; 
            foreach($record as $r) {
            $subject = $this->GM->get_subject_info($r->SUBCODE);
            $avail = $this->EM->get_availability($r->MARKID);

            $j = 0;         
            $s_sub[$i][$j++] = $r->SLOTNO.' - '.$this->GM->get_code($r->SUBNO);
            $s_sub[$i][$j++] = $r->SUBCODE;
            $s_sub[$i][$j++] = $subject['subname'];
            //$s_sub[$i][$j++] = $avail['reg'].' - '.$avail['rea'].' - '.$avail['oth'];
            $s_sub[$i][$j++] = $r->CLASSROOM;
            $s_sub[$i][$j++] = $this->TT->get_sub_contact($r->SLOTNO, $r->SUBNO, $sesid);
            //$s_sub[$i][$j++] = $this->get_sub_reg_count($r->MARKID);

            $i++;
            
            }
            return $s_sub;
        }
        return FALSE;
    }

    public function get_staff_sub_tt($staffid, $sesid) {
        $data = array('STAFFID' => $staffid, 'SESID' => $sesid);
        $this->db->select('MARKID, SUBCODE, SUBNO, SLOTNO, CLASSROOM');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');

        $this->load->model('Enrollment_model', 'EM');
        $table = '';
        
        if($query->num_rows() > 0) {
            $record = $query->result(); 
            foreach($record as $r) {
                $subject = $this->GM->get_subject_info($r->SUBCODE);
                $avail = $this->EM->get_availability($r->MARKID);/*
                if($sesid > 34) {
                    $slotno = $this->GM->get_slotno($r->SLOTNO);
                } else {
                    $slotno = $r->SLOTNO;
                }*/
                $slotno = $this->GM->get_slotno($r->SLOTNO);

                $table .= '<tr>';
                $table .= '<td>'.$slotno.' - '.$this->GM->get_code($r->SUBNO).'</td>';
                $table .= '<td>'.$r->SUBCODE.'</td>';
                $table .= '<td class="text-left">'.$subject['subname'].'</td>';
                $table .= '<td>'.$avail['reg'].' - '.$avail['rea'].' - '.$avail['oth'].'</td>';
                $table .= '<td>'.$r->CLASSROOM.'</td>';
                $table .= '<td>'.$this->get_sub_reg_count($r->MARKID, $sesid).'</td>';
                $table .= '<td><button type="button" class="btn cell-red btn-sm btn-flat pdf" value="'.$this->MOD->encode($r->MARKID).'">PDF</button></td>';
                $table .= '<td><button type="button" class="btn cell-green btn-sm btn-flat view" value="'.$this->MOD->encode($r->MARKID).'">View</button></td>';
                $table .= '</tr>';
            }
            return array('result' => TRUE, 'msg' => $table);
        }
        return array('result' => FALSE, 'msg' => 'No subjects found!');
    }

    public function get_sub_reg_count($markid, $sesid) {
        $data = array(
            'MARKID' => $markid, 
            'SESID' => $sesid,
            'STATUS' => 1
            );
        //$nflag = array('RE', 'D');
        $this->db->select('REGNO');
        $this->db->where($data);
        //$this->db->where_not_in('FLAG', $NFLAG);
        $query = $this->db->get('REGISTER');

        $this->load->model('Enrollment_model', 'EM');
        
        if($query->num_rows() > 0) {
            $record = $query->result(); 
            $reg_count = 0;
            foreach($record as $r) {
                if($this->EM->is_registered($r->REGNO, $sesid))
                    $reg_count++;
            }
            return $reg_count;
        }
        return 0;
    }
	#flag - RA
	#flag - SA
	public function get_reappear_count($markid, $flag, $sesid) {
		
        $data = array(
            'MARKID' => $markid, 
            'SESID' => $sesid,
			'FLAG !=' => 'D',
            'STATUS' => 1
            );
		$mdetail = $this->markid_details($markid);
        //$this->is_shortage($r->REGNO, $mdetail['subcode']);
        $this->db->select('REGNO');
        $this->db->where($data);
        $query = $this->db->get('REGISTER');
        $count = 0;
        if($query->num_rows() > 0) {
			$record = $query->result();
            foreach($record as $r) {
                if($flag == 'SA') {
					if($this->is_shortage($r->REGNO, $mdetail['subcode']))
						$count++;
				} elseif($flag == 'RA') {
					if($this->is_reappear($r->REGNO, $mdetail['subcode']))
						$count++;
				}
            }
        }
        return $count;
    }

    public function get_sub_and_count($sesid) {
        $data = array('STAFFID' => $this->staffid, 'SESID' => $sesid);
        $this->db->select('SUBCODE, MARKID, BRCODE');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');
		
			if($query->num_rows() == 0) {
                    $data = array('STAFFID' => $this->staffid, 'SESID' => $sesid);
					$this->db->select('SUBCODE, MARKID, BRCODE');
					$this->db->where($data);
                    $query = $this->db->get('SUBLINK_N16');
                }
				
        if($query->num_rows() > 0) {
            $record = $query->result();
            $count = 0;
            foreach($record as $r) {
				$branch = $this->GM->get_branch_info($r->BRCODE);
                $subject = $this->GM->get_subject_info($r->SUBCODE);
                $sub[$count]['subcode'] = $subject['subname'];
                $sub[$count]['count'] = $this->get_sub_reg_count($r->MARKID, $sesid).' -'.$branch['degshort'];
                $sub[$count]['id'] = $this->MOD->encode($r->MARKID);
                $count++;
            }
            //return $sub;
            return array('result' => TRUE, 'msg' => $this->course_to_html($sub, 'sessional/'));
        }
        return array('result' => FALSE, 'msg' => 'No courses found for given session!');
    }

    public function course_to_html($sub, $url) {
        $result = '';
        $size = count($sub);
        for($i = 0; $i < $size; $i++) {
            switch($i%10) {
                case 0: $color = 'cell-2'; break;
                case 1: $color = 'cell-3'; break;
                case 2: $color = 'cell-6'; break;
                case 3: $color = 'cell-7'; break;
                case 4: $color = 'cell-12'; break;
                case 5: $color = 'cell-37'; break;
                case 6: $color = 'cell-45'; break;
                case 7: $color = 'cell-24'; break;
                case 8: $color = 'cell-25'; break;
                case 9: $color = 'cell-26'; break;
            }

            if($i%4 == 0 && $i != 0)
                $result .= '</div><div class="row">';

            $result .= '<div class="col-lg-3 col-xs-6">';
            $result .= '<div class="small-box '.$color.'">';
            $result .= '<div class="inner">';
            $result .= '<h3>'.$sub[$i]['count'].'<sup style="font-size: 20px"></sup></h3>';
            $result .= '<p><small>'.$sub[$i]['subcode'].'</small></p>';
            $result .= '</div>';
            $result .= '<div class="icon">';
            $result .= '<i class="ion ion-person-add"></i>';
            $result .= '</div>';
            $result .= '<a href="'.base_url().'staff/'.$url.$sub[$i]['id'].'" class="small-box-footer"><b>Course Details</b> <i class="fa fa-arrow-circle-right"></i></a>';
            $result .= '</div>';
            $result .= '</div>';
        }
        return $result;
    }

    public function get_slist_tt($markid) {
        $slist = $this->get_sub_slist($markid, FALSE);     
        if($slist === FALSE) {
            return array('result' => 'false', 'msg' => '');
        }
        $mdetail = $this->markid_details($markid);
        $subjects = $this->GM->get_subject_info($mdetail['subcode']);
        $table = '<thead>';
        $table .= '<tr><th colspan = "4">'.$mdetail['subcode'].' - '.$subjects['subname'].'</th></tr>';
        $table .= '<tr class="cell-blue"><th>S.No.</th><th>Register No.</th><th>Name</th><th>Branch</th></tr>';
        $table .= '</thead><tbody>';
        $imax = count($slist);
        $jmax = count($slist[0]); 

        $regular = $shortage = $reappear = '';
        $reg_count = $sa_count = $ra_count = 0;    

        for($i = 0; $i < $imax; $i++) {

            if($this->is_reappear($slist[$i][1], $mdetail['subcode'])) {
                $ra_count++;
                $count = $ra_count;
            } elseif($this->is_shortage($slist[$i][1], $mdetail['subcode'])) {
                $sa_count++;
                $count = $sa_count;
            } else {
                $reg_count++;
                $count = $reg_count;
            }

            $trow = '<tr>';
            for($j = 0; $j < $jmax; $j++) {
                if($j == 0)
                    $trow .= '<td>'.$count.'</td>';
                elseif($j == 2)
                    $trow .= '<td class="text-left">'.$slist[$i][$j].'</td>';
                else
                    $trow .= '<td>'.$slist[$i][$j].'</td>';
                
            }
            $trow .= '</tr>';

            if($this->is_reappear($slist[$i][1], $mdetail['subcode'])) {
                $reappear .= $trow;
            } elseif($this->is_shortage($slist[$i][1], $mdetail['subcode'])) {
                $shortage .= $trow;
            } else {
                $regular .= $trow;
            }
        }
        $table .= $regular;
        if($shortage != '') {
            $table .= '<tr><td class="cell-blue" colspan="4">List of Shortage of Attendance Students</td></tr>';
            $table .= $shortage;
        }
        if($reappear != '') {
            $table .= '<tr><td class="cell-blue" colspan="4">List of Reappear Students</td></tr>';
            $table .= $reappear;
        }
        $table .= '</tbody>';
        $res = array('result' => 'true', 'msg' => $table);
        return $res;
    }

    public function get_sub_slist($markid, $short = TRUE) {
        $data = array('MARKID' => $markid, 'STATUS' => 1, 'FLAG !=' => 'D');
        //$nflag = array('RE', 'D');
        $this->db->select('REGNO');
        $this->db->where($data);
		$this->db->order_by('REGNO');
        //$this->db->where_not_in('FLAG', $NFLAG);
        $query = $this->db->get('REGISTER');

        $this->load->model('Enrollment_model', 'EM');
        $sesid = $this->MOD->get_write_ses('EM', 'ALL');
        
        if($query->num_rows() > 0) {
            $record = $query->result(); 
            $count = 0;
            foreach($record as $r) {
                if(!$this->EM->is_registered($r->REGNO, $sesid))
                    continue;
                $student = $this->GM->get_student_info($r->REGNO);
                $branch = $this->GM->get_branch_info($student['brcode']);           
                $slist[$count][0] = $count+1;
                $slist[$count][1] = $r->REGNO;
                $slist[$count][2] = $student['name'];
                if($short)
                    $slist[$count][3] = $branch['degshort'].' '.$branch['brshort'].' ['.$branch['degmode'].']';
                else
                    $slist[$count][3] = $branch['degshort'].' '.$branch['brname'].' ['.$branch['degmode'].']';
                $count++;
            }
            return $slist;
        }
        return FALSE;
    }

    public function is_valid_markid($markid) {
        $data = array('MARKID' => $markid);
        $this->db->select('SUBNO');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    public function is_auth_markid($markid) {
        $data = array('MARKID' => $markid, 'STAFFID' => $this->staffid);
        $this->db->select('SUBNO');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');
        if($query->num_rows() == 0){
            $this->db->select('SUBNO');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK_N16');
        }
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    public function markid_details($markid) {
        $data = array('MARKID' => $markid);
        $this->db->select('CAMCODE, DEPCODE, PRGM, DEGMODE, DEGSHORT, SUBCODE, STAFFID, BRCODE, BATCH, SUBNO, SLOTNO, SEM, SESID');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');
        if($query->num_rows() == 0) {
            $this->db->select('CAMCODE, DEPCODE, PRGM, DEGMODE, DEGSHORT, SUBCODE, STAFFID, BRCODE, BATCH, SUBNO, SLOTNO, SEM, SESID');
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
            $res['slot'] = $row->SLOTNO.' - '.$this->GM->get_code($row->SUBNO);
            $data = array('BRCODE' => $row->BRCODE);
            $this->db->select('DEGSHORT, BRNAME');
            $this->db->where($data);
            $query = $this->db->get('BRANCH');
            if($query->num_rows() === 1) {
                $row = $query->row();
                $res['brname'] = $row->DEGSHORT.' '.$row->BRNAME;
            } else $res['brname'] = '';
            return $res;
        } else
            return FALSE;
    }

    public function update_mark_sessional($markid, $selection, $ass, $w_attd, $session, $esem, $mfactor, $att, $tatt) {
        $data = array(
                'MARKID' => $markid,
                'SELECTION' => $selection,
                'ATT' => $w_attd,
                'SES' => $session,
                'ESEM' => $esem,
                'ATT1' => $att[0],
                'ATT2' => $att[1],
                'ATT3' => $att[2],
                'TOTATT' => $tatt,
                'MF' => $mfactor
            );
        $this->db->insert('GRADE', $data);
        if($selection == 2) {
            $data = array(
                'MARKID' => $markid,
                'SES1' => $ass[0],
                'SES2' => $ass[1],
                'SES3' => $ass[2],
                );
            $this->db->insert('CUSTOM', $data);
        }
        $res = array('result' => 'true', 'msg' => 'Sessional Details Updated.');
        return $res;
    }

    /*
    @type = 1 -- session accpeted
    @type = 2 -- attendance 1 locked
    @type = 3 -- attendance 2 locked
    @type = 4 -- attendance 3 locked
    */
    public function ses_lock_status($markid) {
        $data = array('MARKID' => $markid);        
        $this->db->select('STATUS');
        $this->db->where($data);
        $query = $this->db->get('GRADE');
        if($query->num_rows() === 1) {
            $row = $query->row();
            return $row->STATUS;
        } else
            return FALSE;
    }

    /*not used: see get_mark_sessional*/
    public function generate_mark_sessional($markid) {
        $res = $this->markid_details($markid);
        if($res == FALSE) {
            $res = array('result' => 'false', 'msg' => 'Invalid ID');
        }
        $subject = $this->GM->get_subject_info($res['subcode']);
        $data = '';
        switch($subject['subtype']) {
            case 'T':
                $data['SELECTION'] = 2;
                $data['ATT'] = 0;
                $data['SES'] = 50;
                $data['ESEM'] = 50;
                break;
            case 'L':
                $data['SELECTION'] = 3;
                $data['ATT'] = 0;
                $data['SES'] = 75;
                $data['ESEM'] = 25;
                break;
            case 'P':
                $data['SELECTION'] = 1;
                $data['ATT'] = 0;
                $data['SES'] = 50;
                $data['ESEM'] = 50;
                break;
            case 'S':
                break;
            default:
                //do nothing;
        }
        $data['MARKID'] = $markid;        
        $this->db->insert('GRADE', $data);
        return TRUE;
    }

    public function get_mark_sessional($markid) {
        $data = array('MARKID' => $markid);
        $this->db->where($data);
        $query = $this->db->get('GRADE');
        if($query->num_rows() == 0) {
            //$res = $this->generate_mark_SESsional($MARKID);
            $res = $this->markid_details($markid);
            if($res == FALSE) {
                return array('result' => 'false', 'msg' => 'Invalid ID');
            }
            $subject = $this->GM->get_subject_info($res['subcode']);
            /*$data = '';            
            $data['MARKID'] = $markid;
            $data['STATUS'] = 0;*/
            $data = array('MARKID' => $markid, 'O' => 0, 'AP' => 0, 'A' => 0, 'BP' => 0, 'B' => 0, 'RA' => 0, 'SA' => 0, 'AB' => 0, 'STATUS' => 0);
            switch($subject['subtype']) {
                case 'T':
                    $data['SELECTION'] = 2;
                    $data['ATT'] = 0;
                    $data['SES'] = 50;
                    $data['ESEM'] = 50;
                    break;
				case 'O':
                    $data['SELECTION'] = 2;
                    $data['ATT'] = 0;
                    $data['SES'] = 50;
                    $data['ESEM'] = 50;
                    break;
                case 'L':
                    $data['SELECTION'] = 3;
                    $data['ATT'] = 0;
                    $data['SES'] = 75;
                    $data['ESEM'] = 25;
                    break;
                case 'P':
					$branch = $this->GM->get_branch_info($res['brcode']);
					if($branch === FALSE) return array('result' => 'false', 'msg' => 'Invalid ID');
					if($branch['prgm'] == 'P.G' && $branch['camcode'] == 4 ) {
						$data['SELECTION'] = 4;
                        $data['ATT'] = 0;
                        $data['SES'] = 70;
                        $data['ESEM'] = 30;
					} else {
                    $data['SELECTION'] = 1;
                    $data['ATT'] = 0;
                    $data['SES'] = 60;
                    $data['ESEM'] = 40;

                    $cdata['MARKID'] = $markid;
                    $cdata['SES1'] = 30;
                    $cdata['SES2'] = 30;
                    $cdata['SES3'] = 0;
                    $this->db->insert('CUSTOM', $cdata);
					}
                    break;
                case 'C':
                    $data['SELECTION'] = 1;
                    $data['ATT'] = 0;
                    $data['SES'] = 50;
                    $data['ESEM'] = 50;
                    break;
                case 'D':
                    /*
                    $data['SELECTION'] = 1;
                    $data['ATT'] = 0;
                    $data['SES'] = 70;
                    $data['ESEM'] = 30;*/
                    $branch = $this->GM->get_branch_info($res['brcode']);
                    if($branch === FALSE) return array('result' => 'false', 'msg' => 'Invalid ID');
                    if($branch['prgm'] == 'U.G') {
                        $data['SELECTION'] = 4;
                        $data['ATT'] = 0;
                        $data['SES'] = 60;
                        $data['ESEM'] = 40;
                    } else {
                        $data['SELECTION'] = 4;
                        $data['ATT'] = 0;
                        $data['SES'] = 70;
                        $data['ESEM'] = 30;
                    }
                    break;
                case 'S':
                        $data['SELECTION'] = 3;
                        $data['ATT'] = 0;
                        $data['SES'] = 100;
                        $data['ESEM'] = 0;
                     break;
                default:
                    return;
                    //do nothing;
            }
            $this->db->insert('GRADE', $data);
        }
        $data = array('MARKID' => $markid);
        $this->db->select('SELECTION, ATT, SES, ESEM, ATT1, ATT2, ATT3, TOTATT');
        $this->db->where($data);
        $query = $this->db->get('GRADE');
        $row = $query->row();
        $record['selection'] = $row->SELECTION;
        $record['att'] = $row->ATT;
        $record['ses'] = $row->SES;
        $record['esem'] = $row->ESEM;
        $record['att1'] = $row->ATT1;
        $record['att2'] = $row->ATT2;
        $record['att3'] = $row->ATT3;
        $record['totatt'] = $row->TOTATT;

        $this->db->select('SES1, SES2, SES3');
        $this->db->where($data);
        $query = $this->db->get('CUSTOM');
        if($query->num_rows() === 1) {
            $row = $query->row();
            $record['ass1'] = $row->SES1;
            $record['ass2'] = $row->SES2;
            $record['ass3'] = $row->SES3;
        } else {
            $record['ass1'] = '';
            $record['ass2'] = '';
            $record['ass3'] = '';
        }
        return $record;
    }

    public function already_accepted_grade($markid) {
        $data = array('MARKID' => $markid, 'STATUS > ' => 0);
        $this->db->select('STATUS');
        $this->db->where($data);
        $query = $this->db->get('GRADE');
        if($query->num_rows() > 0) 
            return TRUE;
        else
            return FALSE;
    }

    public function accept_mark_sessional($markid, $selection, $ass) {
        /**if error, remove this **/
        if($this->already_accepted_grade($markid))
            return array('result' => 'false', 'msg' => 'Already accepted.');
        /** ends here **/
        $mark = $this->markid_details($markid);
        if($mark['sesid'] != $this->sesid) {
            return array('result' => 'false', 'msg' => 'Session Locked! Cannot accept subject\'s sessional details!');
        }
        if($selection == 4) {
            $wdata = array('MARKID' => $markid);
            $this->db->where($wdata);
            $query = $this->db->get('CUSTOM');
            $data = array('SES1' => $ass[0], 'SES2' => $ass[1], 'SES3' => $ass[2]);
            if($query->num_rows() == 1) {
                $this->db->where($wdata);
                $this->db->update('CUSTOM', $data);
            } else {
                $data['MARKID'] = $markid;
                $this->db->insert('CUSTOM', $data);
            }
        }
        if(($selection == 4 && $ass[0] == 0)) {
            $this->custom_attlock($markid, 'ass1');
            $this->custom_attlock($markid, 'att1');
        }

        if($selection == 3 || ($selection == 4 && $ass[1] == 0)) {
            $this->custom_attlock($markid, 'ass2');
            $this->custom_attlock($markid, 'att2');
        }

        if($selection == 2 || $selection == 3 || ($selection == 4 && $ass[2] == 0)) {
            $this->custom_attlock($markid, 'ass3');
            $this->custom_attlock($markid, 'att3');
        }

        $this->db->trans_start();

        #creating student - a & m list
        $data = array('MARKID' => $markid, 'FLAG !=' => 'D', 'STATUS' => 1, 'SESID' => $mark['sesid']);
        $this->db->select('REGNO');
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $query = $this->db->get('REGISTER');
        if($query->num_rows() > 0) {
            $record = $query->result();

            foreach($record as $r) {
                $data = array('MARKID' => $markid, 'REGNO' => $r->REGNO, 'ATT1' => -1, 'ATT2' => -1, 'ATT3' => -1, 'STATUS' => 0);
                $this->db->insert('MARKS', $data);
            }
        }
        
        #changin status to 1 - Accepted sessional weightate
        $data = array('MARKID' => $markid);
        $this->db->set('STATUS', 1);
        $this->db->where($data);
        $this->db->update('GRADE');
        $res = array('result' => 'true', 'msg' => '');

        #logging sessional weightage accept time
        $data = array(
                'MARKID' => $markid, 
                'REASON' => 'Accepted SW', 
                'SESID' => $mark['sesid'], 
                'TIME' => date("d-M-Y h:i:sa"), 'STATUS' => 0, 
                'STATUS' => 1
                );
        $this->db->insert('MARK_SESSION', $data);
        
        $this->db->trans_complete();

        return $res;
    }

    public function decline_mark_sessional($markid, $reason) {
        $mark = $this->markid_details($markid);
        if($mark['sesid'] != $this->sesid) {
            return array('result' => 'false', 'msg' => 'Session Locked! Cannot decline subject\'s sessional details!');
        }

        $data = array('MARKID' => $markid, 'REASON' => $reason, 'SESID' => $mark['sesid'], 'TIME' => date("d-M-Y h:i:sa"), 'STATUS' => 0);
        $this->db->insert('MARK_SESSION', $data);
        
        $res = array('result' => 'true', 'msg' => 'Thank you. Please be patient. The administrator will contact you as soon as possible.');
        return $res;
    }

    #status 1 - Sessional accepted
    #status 2 - Assessment 1 accepted
    #status 3 - Assessment 2 accepted
    public function lock_att_sessional($markid, $att) {
        $mark = $this->markid_details($markid);
        if($mark['sesid'] != $this->sesid) {
            return array('result' => 'false', 'msg' => 'Session Locked! Cannot accept subject\'s sessional details!');
        }
        $status = $this->ses_lock_status($markid);
        $cname = '';
        $max = $this->get_max_att($markid, $att);
        switch($att) {
            case 'att1': 
                $cname = 'Attendance 1';
                if($status < 1) return array('result' => 'false', 'msg' => 'Accept Sessional Details first'); 
                elseif($status >= 2) return array('result' => 'false', 'msg' => $cname.' already locked');                 
                if($max < 1) return array('result' => 'false', 'msg' => $cname.' must be greater than 0'); 
                break;
            case 'att2': 
                $cname = 'Attendance 2';
                if($status < 2) return array('result' => 'false', 'msg' => 'Lock Attendance 1 first');                 
                elseif($status >= 3) return array('result' => 'false', 'msg' => $cname.' already locked'); 
                if($max < 1) return array('result' => 'false', 'msg' => $cname.' must be greater than 0'); 
                break;
            case 'att3': 
                $cname = 'Attendance 3';
                if($status < 3) return array('result' => 'false', 'msg' => 'Lock Attendance 2 first');
                  elseif($status >= 4) return array('result' => 'false', 'msg' => $cname.' already locked'); 
                if($max < 1) return array('result' => 'false', 'msg' => $cname.' must be greater than 0'); 
                break;
            default: return array('result' => 'false', 'msg' => 'Invalid attendance type'); break;
        }
        $data = array('MARKID' => $markid);
        $this->db->set('STATUS', 'STATUS + 1', FALSE);
        $this->db->where($data);
        $this->db->update('GRADE');
        $res = array('result' => 'true', 'msg' => $cname.' Locked successfully!');

        $data = array(
                'MARKID' => $markid, 
                'REASON' => $cname, 
                'SESID' => $mark['sesid'], 
                'TIME' => date("d-M-Y h:i:sa"), 'STATUS' => 0, 
                'STATUS' => 1
                );
        $this->db->insert('MARK_SESSION', $data);
        return $res;
    }

    public function update_attendance($markid, $att, $tatt) {
        $mark = $this->markid_details($markid);
        if($mark['sesid'] != $this->sesid) {
            return array('result' => 'false', 'msg' => 'Session Locked! Cannot update attendance!');
        }
        $data = array('MARKID' => $markid);
        $udata = array(
            'ATT1' => $att[0],
            'ATT2' => $att[1],
            'ATT3' => $att[2],
            'TOTATT' => $tatt,
            );

        if($this->is_valid_markid($markid)) {
            $this->db->where($data);
            $this->db->update('GRADE', $udata);
            $res = array('result' => 'true', 'msg' => 'Attendance updated');
            return $res;
        } else {
            $res = array('result' => 'false', 'msg' => 'Invalid id');
            return $res;
        }
    }

    //Function to generate PDF of enrolled subjects
    public function slist_pdf($markid) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf.php');
        
        $sno = 1;

        $staff = $this->GM->get_staff_info($this->staffid);
        $slist = $this->get_sub_slist($markid);
        $size = count($slist);
        
        $data = array('MARKID' => $markid);
        $this->db->select('SUBCODE, CLASSROOM');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');
        $row = $query->row();
        $subject = $this->GM->get_subject_info($row->SUBCODE);

        $pdf = new PDF();
        $pdf->SetTitle($row->SUBCODE);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',15);
        $pdf->Cell(0,15,'Choice Based Credit System - Tentative Student List',0,0,'C');
        $pdf->Ln(15);

        $pdf->printDetails($sno++, 'Name',':',$staff['staffname']);
        $pdf->printDetails($sno++, 'Staff ID',':',$this->staffid);
        $pdf->printDetails($sno++, 'Department',':',$staff['dept']);
        $pdf->printDetails($sno++, 'Subject',':',$subject['subname']);
        $pdf->printDetails($sno++, 'Classroom',':',$row->CLASSROOM);
        $pdf->Ln(10);
        
        $pdf->SetFont('Arial','B',10.5);
        $pdf->SetWidths(array(12,27, 70, 81));
        $pdf->align('C');
        $pdf->Row(array('S.No.', 'Register No.', 'Name', 'Branch'));
        $pdf->SetAligns(array('C', 'C', 'L', 'C'));
        //$pdf->align('L');
        $pdf->SetFont('Arial','',10);
        $shortage = $reappear = $regular = '';
        $ra_count = $sa_count = $reg_count = 0;
        for($i = 0; $i < $size; $i++) {
            if($this->is_reappear($slist[$i][1], $row->SUBCODE)) {
                $slist[$i][0] = $ra_count+1;
                $reappear[$ra_count++] = $slist[$i];                
            } elseif($this->is_shortage($slist[$i][1], $row->SUBCODE)) {
                $slist[$i][0] = $sa_count+1;
                $shortage[$sa_count++] = $slist[$i];
            } else {
                $slist[$i][0] = $reg_count+1;
                $regular[$reg_count++] = $slist[$i];
            }
        }
        $size = sizeof($regular);
        for($i = 0; $i < $size; $i++) {
            $pdf->Row($regular[$i]);
        }
        
        if($sa_count > 0) {
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(0,10.5,'List of Shortage of Attendance Student of previous Semester',1,1,'C');
            $pdf->SetFont('Arial','',10);
            for($i = 0; $i < $sa_count; $i++) {
                $pdf->Row($shortage[$i]);
            }
        }
        if($ra_count > 0) {
            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(0,10.5,'List of Reappear Student of previous Semester',1,1,'C');
            $pdf->SetFont('Arial','',10);
            for($i = 0; $i < $ra_count; $i++) {
                $pdf->Row($reappear[$i]);
            }
        }

        $pdf->SetFont('Arial','',10);
        $pdf->SetY(-28);
        $pdf->Cell(0,6,'Note: Please do not allow the students those Register Number are not in this list.','',0,'C');
        
        $pdf->Output($row->SUBCODE.'.pdf','I');
    }

    public function tt_pdf($sesid) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_no_wm.php');

        $staff = $this->GM->get_staff_info($this->staffid);

        $pdf = new PDF();
        $pdf->SetTitle($this->staffid);
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',15);
        $pdf->Cell(0,15,'Choice Based Credit System',0,0,'C');
        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,15,'Time Table',0,0,'C');
        $pdf->Ln(15);
        $sno = 1;
        $pdf->printDetails($sno++, 'Name',':',$staff['staffname']);
        $pdf->printDetails($sno++, 'Staff ID',':',$this->staffid);
        $pdf->printDetails($sno++, 'Department',':',$staff['dept']);
        $pdf->Ln(10);
        
        //Timetable Array
        $pdf->SetFont('Arial','B',11);
        $pdf->SetWidths(array(22,21,21,21,21,21,21,21,21));
        $pdf->align('C');
        $pdf->Row(array('Day/Hour', '1', '2', '3', '4', '5', '6', '7', '8'));
        $pdf->SetFont('Arial','',10);

        $tt = $this->TT->get_staff_tt($this->staffid, $sesid);
        $size = count($tt);
        for($i = 0; $i < $size; $i++)
            $pdf->Row($tt[$i]);
        $pdf->SetFont('Arial','',10);
        $pdf->Ln(10);

        //Subject and Staff List
        $pdf->SetFont('Arial','B',11);
        $pdf->SetWidths(array(15, 20, 90, 45, 20));
        $pdf->align('C');        
        $pdf->Row(array('Slot', 'Course Code', 'Course Name', 'Classroom', 'Contact'));
        $pdf->SetAligns(array('C', 'C', 'L', 'C', 'C'));
        $pdf->SetFont('Arial','',10);

        $s_sub = $this->get_staff_sub($sesid);
        $size = count($s_sub);
        for($i = 0; $i < $size; $i++)
            $pdf->Row($s_sub[$i]);
        
        $pdf->Output($this->staffid.'.pdf','I');
    }

    public function calculate_mark($markid) {
        $ass = $att = array(1, 1, 1, 0);
        $mdetail = $this->markid_details($markid);
        if($mdetail['sesid'] != $this->sesid) {
            return array('result' => 'false', 'msg' => 'Session Locked! Cannot compute marks');
        }
        //Selection mode
        $selection = $this->get_ses_selection($markid);
        //Custom weightae
        $custom = $this->get_ses_custom($markid);
        //Sessional Weightage
        $ses_weightage = $this->get_ses_weightage($markid, 'ses');
        //Attendance Weightage
        $att_weightage = $this->get_ses_weightage($markid, 'att');
        
        switch($selection) {
            case 1: $ass = array(1, 1, 1); break;
            case 2: $ass = array(1, 1, 0); break;
            case 3: $ass = array(1, 0, 0); break;
            case 4:
                $ass[0] = $custom['ass1'] == 0 ? 0 : 1;
                $ass[1] = $custom['ass2'] == 0 ? 0 : 1;
                $ass[2] = $custom['ass3'] == 0 ? 0 : 1;                
                break;
            default: 
                return array('result' => 'false', 'msg' => 'Invalid selection');
        }
        $maxatt = 0;
        if($ass[0] == 1)
            $maxatt += $this->get_max_att($markid, 'att1');
        if($ass[1] == 1)
            $maxatt += $this->get_max_att($markid, 'att2');
        if($ass[2] == 1)
            $maxatt += $this->get_max_att($markid, 'att3');

        $data = array('MARKID' => $markid);
        $this->db->select('REGNO, ASS1, ASS2, ASS3, ATT1, ATT2, ATT3');
        $this->db->where($data);
        $query = $this->db->get('MARKS');
        
        if($query->num_rows() > 0) {
            $record = $query->result();
            foreach($record as $r) {
                $totass = $totatt = 0;
                if($ass[0] == 1) {
                    $totass += $r->ASS1;
                    $totatt += $r->ATT1 == -1 ? 0 : $r->ATT1;
                }
                if($ass[1] == 1) {
                    $totass += $r->ASS2;
                    $totatt += $r->ATT2 == -1 ? 0 : $r->ATT2;
                }
                if($ass[2] == 1) {
                    $totass += $r->ASS3;
                    $totatt += $r->ATT3 == -1 ? 0 : $r->ATT3;
                }
                //Calculating sessional weightage
                switch($selection) {
                    case 1:
                        $totass = $totass / 3;
                        break;
                    case 2:
                        $totass = $totass / 2;
                        break;
                    case 3:
                        //do nothing
                        break;
                    case 4:
                        $totass = $r->ASS1 * $custom['ass1'] / 100;
                        $totass += $r->ASS2 * $custom['ass2'] / 100;
                        $totass += $r->ASS3 * $custom['ass3'] / 100;
                        $totass = round ($totass, 0,  PHP_ROUND_HALF_UP);
                        break;
                }
                if($selection != 4)
                    $totass = round ($totass * $ses_weightage / 100, 0,  PHP_ROUND_HALF_UP);
                //Calculating attendance weightage
                if($att_weightage != 0) {
                    $percent = $totatt / $maxatt * 100;
                    if($percent > 95)
                        $att_weightage = 5;
                    elseif($percent > 90)
                        $att_weightage = 4;
                    elseif($percent > 85)
                        $att_weightage = 3;
                    elseif($percent > 80)
                        $att_weightage = 2;
                    elseif($percent > 75)
                        $att_weightage = 1;
                    else
                        $att_weightage = 0;
                }

                //updating mark & assessment data
                $data = array('REGNO' => $r->REGNO, 'MARKID' => $markid);
                $udata = array('TOTATT' => $totatt, 'ATTMARK' => $att_weightage, 'SESMARK' => $totass);
                $this->db->where($data);
                $this->db->update('MARKS', $udata);
            }
            return array('result' => 'true', 'msg' => 'Sessional Mark updated');
        }
    }

    public function calculate_attendance($markid) {
        $mark = $this->markid_details($markid);
        if($mark['sesid'] != $this->sesid) {
            return array('result' => 'false', 'msg' => 'Session Locked! Cannot calculate attendance %');
        }

        $this->db->select('REGNO, ATT1, ATT2, ATT3');
        $data = array('MARKID' => $markid);
        $this->db->where($data);
        $query = $this->db->get('MARKS');

        if($query->num_rows() > 0) {
            $record = $query->result();

            $m_att1 = $this->get_max_att($markid, 'att1');
            $m_att2 = $this->get_max_att($markid, 'att2');
            $m_att3 = $this->get_max_att($markid, 'att3');

            $selection = $this->get_ses_selection($markid);
            switch($selection) {
                case 1: $ass = array(1, 1, 1); break;
                case 2: $ass = array(1, 1, 0); break;
                case 3: $ass = array(1, 0, 0); break;
                case 4:
                    $custom = $this->get_ses_custom($markid);
                    $ass[0] = $custom['ass1'] == 0 ? 0 : 1;
                    $ass[1] = $custom['ass2'] == 0 ? 0 : 1;
                    $ass[2] = $custom['ass3'] == 0 ? 0 : 1;
                    break;
                default:
                    return array('result' => 'false', 'msg' => 'Invalid selection');
            }

            $this->db->trans_start();
            foreach($record as $r) {
                $numerator = 0;
                $denominator = 0;
                if($r->ATT1 != -1 && $ass[0] == 1){  
                    $numerator += $r->ATT1;
                    $denominator += $m_att1;
                }

                if($r->ATT2 != -1 && $ass[1] == 1) {
                    $numerator += $r->ATT2;
                    $denominator += $m_att2;
                }

                if($r->ATT3 != -1 && $ass[2] == 1) {
                    $numerator += $r->ATT3;
                    $denominator += $m_att3;
                }

                if($denominator == 0) $percent = 0;
                else
                    $percent = $numerator / $denominator * 100;

                $percent = round ($percent, 0,  PHP_ROUND_HALF_UP);
                $data = array('MARKID' => $markid, 'REGNO' => $r->REGNO);
                $udata = array('ATTPER' => $percent, 'TOTATT' => $numerator);
                $this->db->where($data);
                $this->db->update('MARKS', $udata);
            }
            $this->db->trans_complete();
        } else {
            return array('result' => 'false', 'msg' => 'Invalid request');
        }
    }

    
    public function calculate_totmark($markid) {
        $mdetail = $this->markid_details($markid);
        if($mdetail['sesid'] != $this->sesid) {
            return array('result' => 'false', 'msg' => 'Session Locked! Cannot compute total marks');
        }
        
        $esem_weightage = $this->get_ses_weightage($markid, 'esem');
        $data = array('MARKID' => $markid);
        $this->db->select('REGNO, SESMARK, ESEM');
        $this->db->where($data);
        $query = $this->db->get('MARKS');
        if($query->num_rows()  > 0) {
            foreach ($query->result() as $row) {
                if($row->ESEM == 'A' || $row->ESEM == 'P') $esem = 0;
                else $esem = round($row->ESEM * $esem_weightage / 100 , 2, PHP_ROUND_HALF_UP);
                $totmark = $row->SESMARK + $esem;
                $this->db->set(array('TOTMARK' => $totmark));
                $this->db->where(array('MARKID' => $markid, 'REGNO' => $row->REGNO));
                $this->db->update('MARKS');
            }
        }
    }

    public function is_prevented($regno, $markid, $sesid) {
        $data = array('REGNO' => $regno, 'MARKID' => $markid, 'SESID' => $sesid);
        $wdata = array(0, 1);
        $this->db->select('STATUS');
        $this->db->where($data);
        $this->db->where_in('STATUS', $wdata);
        $query = $this->db->get('PREVENTED');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    public function mark_pdf($markid, $sesid) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_no_wm.php');    
      
        $sno = 1;

        $ass = $att = array(1, 1, 1, 0);
        $mdetail = $this->markid_details($markid);
        $staff = $this->GM->get_staff_info($mdetail['staffid']);
        $subject = $this->GM->get_subject_info($mdetail['subcode']);
        $res = $this->get_ses_marks($markid, $sesid, $ass, $ass);        
        $branch = $this->GM->get_branch_info($mdetail['brcode']);
        $campus = $this->GM->get_campus_info($branch['camcode']);
        $selection = $this->get_ses_selection($markid);
        
        $weightage = $this->get_ses_weightage($markid, 'ses');
        $a_weightage = $this->get_ses_weightage($markid, 'att');
        
        $max = $this->get_max_att($markid, 'att1');
        $max += $this->get_max_att($markid, 'att2');
        $max += $this->get_max_att($markid, 'att3');

        if($this->get_mark_status($markid) >= 3){
        	$locked = TRUE;
        }else $locked = FALSE;

        $table = '';
        switch($selection) {
            case 1: $ass = array(1, 1, 1); $mode = 'All three equal'; break;
            case 2: $ass = array(1, 1, 0); $mode = 'Only two'; break;
            case 3: $ass = array(1, 0, 0); $mode = 'Only one'; break;
            case 4:
                $custom = $this->get_ses_custom($markid);
                $ass[0] = $custom['ass1'] == 0 ? 0 : 1;
                $ass[1] = $custom['ass2'] == 0 ? 0 : 1;
                $ass[2] = $custom['ass3'] == 0 ? 0 : 1;
                $mode = 'Custom';
                break;
        }

        $j = 0;
        $twidth = 13;
        $harray[$j] = 'S.No.'; 
        $warray[$j++] = 12;
        $harray[$j] = 'Register No.';
        $warray[$j++] = 25;
        $harray[$j] = 'Name of the Student';
        $warray[$j++] = 99;
        if($ass[0] == 1) {
            $harray[$j] = 'Ass1 (100)';
            $warray[2] -= $twidth;
            $warray[$j++] = $twidth;
        }
        if($ass[1] == 1) {
            $harray[$j] = 'Ass2 (100)';
            $warray[2] -= $twidth;
            $warray[$j++] = $twidth;
        }
        if($ass[2] == 1) {
            $harray[$j] = 'Ass3 (100)';
            $warray[2] -= $twidth;
            $warray[$j++] = $twidth;
        }
        $harray[$j] = 'T.Atten ('.$max.')';
        $warray[$j++] = $twidth+2;
        //$harray[$j] = 'Atten. ('.$a_weightage.')';
        //$warray[$j++] = $twidth;
        $harray[$j] = 'I.Assess. ('.$weightage.')';
        $warray[$j++] = $twidth+4;
        //$harray[$j] = 'Total ('.($weightage+$a_weightage).')';
		$harray[$j] = 'Signature';
        $warray[$j++] = $twidth+$twidth-4;

        $pdf = new PDF();
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        if(!$locked) {
        	$pdf->SetFont('Arial','B',50);
            $pdf->SetTextColor(255,192,203);
            $pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
            $pdf->SetTextColor(0,0,0);
        }
        $pdf->SetFont('Arial','B',15);
        $pdf->Cell(0,10,'Assessment Marks',0,0,'C');
        $pdf->Ln(10);
        $arr = array('Campus', $campus['camname'], 'Session', 'NOV - 18');
        $pdf->printBox($arr, 'T');
        $arr = array('Branch', $branch['degshort'].' '.$branch['brname'].' - [ '.$mdetail['batch'].' ]', 'Mode', $branch['degmode']);
        $pdf->printBox($arr, '');
        $arr = array('Code', $mdetail['subcode'], 'Credit :', $subject['credit'], 'Semester', $mdetail['sem']);
        $pdf->printBox($arr, '');
        $arr = array('Course', $subject['subname'], 'Regulation', 2015);
        $pdf->printBox($arr, 'B');

        $data = array('MARKID' => $markid);
        $this->db->select('REGNO, ASS1, ASS2, ASS3, TOTATT, ATTMARK, SESMARK, TOTMARK');
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $query = $this->db->get('MARKS');
        if($query->num_rows() > 0) {
            $record = $query->result();
            $sno = 1;
            $pdf->SetWidths($warray);
            $pdf->SetFont('Arial','B',9.5);
            $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C'));
            $pdf->Row($harray);
            $pdf->SetAligns(array('C','C','L','C','C','C','C','C','C'));
            $pdf->SetFont('Arial','',9.5);
            $regular = $shortage = $reappear = '';
            $reg_count = $sa_count = $ra_count = 0;
            foreach($record as $r) {
                $student = $this->GM->get_student_info($r->REGNO);
                $j = 1;
                //$carray[$j++] = $sno++;
                $carray[$j++] = $r->REGNO;
                $carray[$j++] = $student['name'];
                if($ass[0] == 1) $carray[$j++] = $r->ASS1;
                if($ass[1] == 1) $carray[$j++] = $r->ASS2;
                if($ass[2] == 1) $carray[$j++] = $r->ASS3;
                $carray[$j++] = $r->TOTATT;
                //$carray[$j++] = $r->ATTMARK;
                $carray[$j++] = $r->SESMARK;
				$carray[$j++] = '';
                //$carray[$j++] = $r->ATTMARK + $r->SESMARK;
                if($this->is_reappear($r->REGNO, $mdetail['subcode'])) {
                    $carray[0] = $ra_count+1;
                    $reappear[$ra_count++] = $carray;
                } elseif($this->is_shortage($r->REGNO, $mdetail['subcode'])) {
                    $carray[0] = $sa_count+1;
                    $shortage[$sa_count++] = $carray;
                } else {
                    $carray[0] = $reg_count+1;
                    $regular[$reg_count++] = $carray;
                }
            }

            #unviersal count for 3 list
            $ucount = 1;
            for($j = 0; $j < 3; $j++) {
                if($j == 0) {
                    $list = $regular;
                    $count = $reg_count;
                } elseif($j == 1) {
                    $list = $shortage;
                    $count = $sa_count;
                } else {
                    $list = $reappear;
                    $count = $ra_count;
                }
                if($count > 0 && $j > 0) {
                    if($pdf->GetY() > 240) {
                        $pdf->Ln(2);
                        $pdf->SetFont('Arial','B',9.5);
                        if($selection == 4) {
                            $pdf->Cell(0,6,'Mode of Calculation: '.$mode,'LRT',0,'C');
                            $pdf->Ln(6);
                            $pdf->Cell(64,6,'Session 1: '.$custom['ass1'],'LB',0,'C');
                            $pdf->Cell(63,6,'Session 2: '.$custom['ass2'],'B',0,'C');
                            $pdf->Cell(63,6,'Session 3: '.$custom['ass3'],'RB',0,'C');
                        } else {
                            $pdf->Cell(0,6,'Mode of Calculation: '.$mode,'1',0,'C');
                        }
                        $pdf->SetY(-31);
                        $pdf->SetFont('Arial','B', 9.5);
                        $pdf->Cell(120,5,'',0,0);
                        $pdf->Cell(0, 5, $staff['staffname'], '', 1, 'C');
                        $pdf->Cell(120,5,'',0,0);
                        $pdf->Cell(0, 5, 'Signature of the Course Instructor', '', 1, 'C');
                        $pdf->AddPage();
                        if(!$locked) {
        					$pdf->SetFont('Arial','B',50);
            				$pdf->SetTextColor(255,192,203);
            				$pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
            				$pdf->SetTextColor(0,0,0);
        				}
                        $pdf->SetFont('Arial','B',15);
                        $pdf->Cell(0,10,'Assessment Marks',0,0,'C');
                        $pdf->Ln(10);
                        $arr = array('Campus', $campus['camname'], 'Session', 'NOV - 18');
                        $pdf->printBox($arr, 'T');
                        $arr = array('Branch', $branch['degshort'].' '.$branch['brname'].' - [ '.$mdetail['batch'].' ]', 'Mode', $branch['degmode']);
                        $pdf->printBox($arr, '');
                        $arr = array('Code', $mdetail['subcode'], 'Credit :', $subject['credit'], 'Semester', $mdetail['sem']);
                        $pdf->printBox($arr, '');
                        $arr = array('Course', $subject['subname'], 'Regulation', 2015);
                        $pdf->printBox($arr, 'B');
                        $pdf->SetFont('Arial','B',9.5);
                        $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C'));
                        $pdf->Row($harray);
                        $pdf->SetFont('Arial','',9.5);
                        $pdf->SetAligns(array('C','C','L','C','C','C','C','C','C'));
                    }                        
                        $pdf->SetFont('Arial','B',10);
                        if($j == 1)
                            $pdf->Cell(0,10,'List of Shortage of Attendance student',1,1,'C');
                        else
                            $pdf->Cell(0,10,'List of Reappear student',1,1,'C');
                        $pdf->SetFont('Arial','',9.5);
                    
                }

                for($i = 0; $i <$count; $i++) {
                    $pdf->Row($list[$i]);
                if($pdf->GetY() > 250 || ($ucount++)%25 == 0) {
                    $pdf->Ln(2);
                    $pdf->SetFont('Arial','B',9.5);
                    if($selection == 4) {
                        $pdf->Cell(0,6,'Mode of Calculation: '.$mode,'LRT',0,'C');
                        $pdf->Ln(6);
                        $pdf->Cell(64,6,'Session 1: '.$custom['ass1'],'LB',0,'C');
                        $pdf->Cell(63,6,'Session 2: '.$custom['ass2'],'B',0,'C');
                        $pdf->Cell(63,6,'Session 3: '.$custom['ass3'],'RB',0,'C');
                    } else {
                        $pdf->Cell(0,6,'Mode of Calculation: '.$mode,'1',0,'C');
                    }
                    $pdf->SetY(-31);
                    $pdf->SetFont('Arial','B', 9.5);
                    $pdf->Cell(120,5,'',0,0);
                    $pdf->Cell(0, 5, $staff['staffname'], '', 1, 'C');
                    $pdf->Cell(120,5,'',0,0);
                    $pdf->Cell(0, 5, 'Signature of the Course Instructor', '', 1, 'C');
                    
                    $pdf->AddPage('');
                    if(!$locked) {
        				$pdf->SetFont('Arial','B',50);
            			$pdf->SetTextColor(255,192,203);
            			$pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
            			$pdf->SetTextColor(0,0,0);
        			}
                    $pdf->SetFont('Arial','B',15);
                    $pdf->Cell(0,10,'Assessment Marks',0,0,'C');
                    $pdf->Ln(10);
                    $arr = array('Campus', $campus['camname'], 'Session', 'NOV - 18');
                    $pdf->printBox($arr, 'T');
                    $arr = array('Branch', $branch['degshort'].' '.$branch['brname'].' - [ '.$mdetail['batch'].' ]', 'Mode', $branch['degmode']);
                    $pdf->printBox($arr, '');
                    $arr = array('Code', $mdetail['subcode'], 'Credit :', $subject['credit'], 'Semester', $mdetail['sem']);
                    $pdf->printBox($arr, '');
                    $arr = array('Course', $subject['subname'], 'Regulation', 2015);
                    $pdf->printBox($arr, 'B');
                    $pdf->SetFont('Arial','B',9.5);
                    $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C'));
                    $pdf->Row($harray);
                    $pdf->SetFont('Arial','',9.5);
                    $pdf->SetAligns(array('C','C','L','C','C','C','C','C','C'));
                }
            }}
            if($pdf->GetY() < 260) {
                $pdf->Ln(2);
                $pdf->SetFont('Arial','B',9.5);
                if($selection == 4) {
                    $pdf->Cell(0,6,'Mode of Calculation: '.$mode,'LRT',0,'C');
                    $pdf->Ln(6);
                    $pdf->Cell(64,6,'Session 1: '.$custom['ass1'],'LB',0,'C');
                    $pdf->Cell(63,6,'Session 2: '.$custom['ass2'],'B',0,'C');
                    $pdf->Cell(63,6,'Session 3: '.$custom['ass3'],'RB',0,'C');
                } else {
                    $pdf->Cell(0,6,'Mode of Calculation: '.$mode,'1',0,'C');
                }
                $pdf->SetY(-31);
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(120,5,'',0,0);
                $pdf->Cell(0, 5, $staff['staffname'], '', 1, 'C');
                $pdf->Cell(120,5,'',0,0);
                $pdf->Cell(0, 5, 'Signature of the Course Instructor', '', 1, 'C');
            }
        }

        $pdf->output($mdetail['subcode'].'.pdf','I');
    }
	
	public function assess_pdf($markid, $col, $sesid) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_no_wm.php');    
      
        $sno = 1;

        $ass = $att = array(1, 1, 1, 0);
        $mdetail = $this->markid_details($markid);
        $staff = $this->GM->get_staff_info($mdetail['staffid']);
        $subject = $this->GM->get_subject_info($mdetail['subcode']);
        $res = $this->get_ses_marks($markid, $sesid, $ass, $ass);        
        $branch = $this->GM->get_branch_info($mdetail['brcode']);
        $campus = $this->GM->get_campus_info($branch['camcode']);
        $selection = $this->get_ses_selection($markid);
        
        $weightage = $this->get_ses_weightage($markid, 'ses');
        $a_weightage = $this->get_ses_weightage($markid, 'att');
        
        $max = $this->get_max_att($markid, 'att'.$col);
        if($this->is_attlocked($markid, 'att'.$col) && $this->is_attlocked($markid, 'ass'.$col)) {
            $status = TRUE;
        } else $status = FALSE;

        $table = '';
        switch($selection) {
            case 1: $ass = array(1, 1, 1); $mode = 'All three equal'; break;
            case 2: $ass = array(1, 1, 0); $mode = 'Only two'; break;
            case 3: $ass = array(1, 0, 0); $mode = 'Only one'; break;
            case 4:
                $custom = $this->get_ses_custom($markid);
                $ass[0] = $custom['ass1'] == 0 ? 0 : 1;
                $ass[1] = $custom['ass2'] == 0 ? 0 : 1;
                $ass[2] = $custom['ass3'] == 0 ? 0 : 1;
                $mode = 'Custom';
                break;
        }

        $j = 0;
        $twidth = 13;
        $harray[$j] = 'S.No.'; 
        $warray[$j++] = 12;
        $harray[$j] = 'Register No.';
        $warray[$j++] = 25;
        $harray[$j] = 'Name of the Student';
        $warray[$j++] = 80;
        $harray[$j] = 'Atten '.$col.' ('.$max.')';
        $warray[$j++] = $twidth+2;
        $harray[$j] = 'Ass '.$col.' (100)';
        $warray[$j++] = $twidth;
        $harray[$j] = 'Signature';
        $warray[$j++] = 45;

        $pdf = new PDF();
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();
        $pdf->AddPage();
		if(!$status) {
            $pdf->SetFont('Arial','B',50);
            $pdf->SetTextColor(255,192,203);
            $pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
            $pdf->SetTextColor(0,0,0);
        }
        
        $pdf->SetFont('Arial','B',15);
        $pdf->Cell(0,10,'Assessment Marks',0,0,'C');
        $pdf->Ln(10);
        $arr = array('Campus', $campus['camname'], 'Session', 'NOV - 18');
        $pdf->printBox($arr, 'T');
        $arr = array('Branch', $branch['degshort'].' '.$branch['brname'].' - [ '.$mdetail['batch'].' ]', 'Mode', $branch['degmode']);
        $pdf->printBox($arr, '');
        $arr = array('Code', $mdetail['subcode'], 'Credit :', $subject['credit'], 'Semester', $mdetail['sem']);
        $pdf->printBox($arr, '');
        $arr = array('Course', $subject['subname'], 'Regulation', 2015);
        $pdf->printBox($arr, 'B');

        $data = array('MARKID' => $markid);
        $this->db->select('REGNO, ASS'.$col.', ATT'.$col);
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $query = $this->db->get('MARKS');
        if($query->num_rows() > 0) {
            $record = $query->result();
            $sno = 1;
            $pdf->SetWidths($warray);
            $pdf->SetFont('Arial','B',9.5);
            $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C'));
            $pdf->Row($harray);
            $pdf->SetAligns(array('C','C','L','C','C','C','C','C','C'));
            $pdf->SetFont('Arial','',9.5);
            $regular = $shortage = $reappear = '';
            $reg_count = $sa_count = $ra_count = 0;
            $a = 'ATT'.$col;
            $b = 'ASS'.$col;            
            foreach($record as $r) {
                $student = $this->GM->get_student_info($r->REGNO);
                $j = 1;
                $carray[$j++] = $r->REGNO;
                $carray[$j++] = $student['name'];
                $carray[$j++] = $r->{$a} < 0 ? '' : $r->{$a};
                $carray[$j++] = $r->{$b};
                $carray[$j] = '';
                if($this->is_reappear($r->REGNO, $mdetail['subcode'])) {
                    $carray[0] = $ra_count+1;
                    $reappear[$ra_count++] = $carray;
                } elseif($this->is_shortage($r->REGNO, $mdetail['subcode'])) {
                    $carray[0] = $sa_count+1;
                    $shortage[$sa_count++] = $carray;
                } else {
                    $carray[0] = $reg_count+1;
                    $regular[$reg_count++] = $carray;
                }
            }

            #unviersal count for 3 list
            $ucount = 1;
            for($j = 0; $j < 3; $j++) {
                if($j == 0) {
                    $list = $regular;
                    $count = $reg_count;
                } elseif($j == 1) {
                    $list = $shortage;
                    $count = $sa_count;
                } else {
                    $list = $reappear;
                    $count = $ra_count;
                }
                if($count > 0 && $j > 0) {
                    if($pdf->GetY() > 240) {
                        $pdf->SetY(-31);
                        $pdf->SetFont('Arial','B', 9.5);
                        $pdf->Cell(120,5,'',0,0);
                        $pdf->Cell(0, 5, $staff['staffname'], '', 1, 'C');
                        $pdf->Cell(120,5,'',0,0);
                        $pdf->Cell(0, 5, 'Signature of the Course Instructor', '', 1, 'C');
                        $pdf->AddPage();
                        if(!$status) {
							$pdf->SetFont('Arial','B',50);
							$pdf->SetTextColor(255,192,203);
							$pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
							$pdf->SetTextColor(0,0,0);
						}
                        $pdf->SetFont('Arial','B',15);
                        $pdf->Cell(0,10,'Assessment Marks',0,0,'C');
                        $pdf->Ln(10);
                        $arr = array('Campus', $campus['camname'], 'Session', 'NOV - 18');
                        $pdf->printBox($arr, 'T');
                        $arr = array('Branch', $branch['degshort'].' '.$branch['brname'].' - [ '.$mdetail['batch'].' ]', 'Mode', $branch['degmode']);
                        $pdf->printBox($arr, '');
                        $arr = array('Code', $mdetail['subcode'], 'Credit :', $subject['credit'], 'Semester', $mdetail['sem']);
                        $pdf->printBox($arr, '');
                        $arr = array('Course', $subject['subname'], 'Regulation', 2015);
                        $pdf->printBox($arr, 'B');
                        $pdf->SetFont('Arial','B',9.5);
                        $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C'));
                        $pdf->Row($harray);
                        $pdf->SetFont('Arial','',9.5);
                        $pdf->SetAligns(array('C','C','L','C','C','C','C','C','C'));
                    }                        
                        $pdf->SetFont('Arial','B',10);
                        if($j == 1)
                            $pdf->Cell(0,10,'List of Shortage of Attendance student',1,1,'C');
                        else
                            $pdf->Cell(0,10,'List of Reappear student',1,1,'C');
                        $pdf->SetFont('Arial','',9.5);
                    
                }

                for($i = 0; $i <$count; $i++) {
                    $pdf->Row($list[$i]);
                if($pdf->GetY() > 250 || ($ucount++)%25 == 0) {
                    $pdf->SetY(-31);
                    $pdf->SetFont('Arial','B', 9.5);
                    $pdf->Cell(120,5,'',0,0);
                    $pdf->Cell(0, 5, $staff['staffname'], '', 1, 'C');
                    $pdf->Cell(120,5,'',0,0);
                    $pdf->Cell(0, 5, 'Signature of the Course Instructor', '', 1, 'C');
                    
                    $pdf->AddPage();
                    if(!$status) {
						$pdf->SetFont('Arial','B',50);
						$pdf->SetTextColor(255,192,203);
						$pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
						$pdf->SetTextColor(0,0,0);
					}
                    $pdf->SetFont('Arial','B',15);
                    $pdf->Cell(0,10,'Assessment Marks',0,0,'C');
                    $pdf->Ln(10);
                    $arr = array('Campus', $campus['camname'], 'Session', 'NOV - 18');
                    $pdf->printBox($arr, 'T');
                    $arr = array('Branch', $branch['degshort'].' '.$branch['brname'].' - [ '.$mdetail['batch'].' ]', 'Mode', $branch['degmode']);
                    $pdf->printBox($arr, '');
                    $arr = array('Code', $mdetail['subcode'], 'Credit :', $subject['credit'], 'Semester', $mdetail['sem']);
                    $pdf->printBox($arr, '');
                    $arr = array('Course', $subject['subname'], 'Regulation', 2015);
                    $pdf->printBox($arr, 'B');
                    $pdf->SetFont('Arial','B',9.5);
                    $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C'));
                    $pdf->Row($harray);
                    $pdf->SetFont('Arial','',9.5);
                    $pdf->SetAligns(array('C','C','L','C','C','C','C','C','C'));
                }
            }}
            if($pdf->GetY() < 260) {
                $pdf->SetY(-31);
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(120,5,'',0,0);
                $pdf->Cell(0, 5, $staff['staffname'], '', 1, 'C');
                $pdf->Cell(120,5,'',0,0);
                $pdf->Cell(0, 5, 'Signature of the Course Instructor', '', 1, 'C');
            }
        }

        $pdf->output($mdetail['subcode'].'.pdf','I');
    }

    public function course_grade_pdf($markid) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_no_wm.php');

        $warray = array(12, 25, 83, 20, 20, 15, 15);
        $mdetail = $this->markid_details($markid);
        $staff = $this->GM->get_staff_info($mdetail['staffid']);
        $branch = $this->GM->get_branch_info($mdetail['brcode']);
        $dept = $this->GM->get_dept_info($branch['depcode']);
        $campus = $this->GM->get_campus_info($branch['camcode']);
        $subject = $this->GM->get_subject_info($mdetail['subcode']);
        $ses_weightage = $this->get_ses_weightage($markid, 'ses');
        $esem_weightage = $this->get_ses_weightage($markid, 'esem');
        $values = $this->get_mean($markid);
        $o = $ap = $a = $bp = $b = $ra = ''; 
        $pass = $values['pass'];
        $o = $values['o'].' - 100';
        if($o == ' - 100') $o = 'N/A';
        if($values['o'] - 1 >= $pass) $ap = $values['ap'].' - '.($values['o'] - 1);
        else $ap ='N/A';
        if($values['ap'] - 1 >= $pass) $a = $values['a'].' - '.($values['ap'] - 1);
        else $a ='N/A';
        if($values['a'] - 1 >= $pass) $bp = $values['bp'].' - '.($values['a'] - 1);
        else $bp ='N/A';
        if($values['bp'] - 1 >= 50) $b = '50 - '.($values['bp'] - 1);
        else $b ='N/A';
        
        $harray = array('S.No.', 'Register No.', 'Name of the Student', 'I. Asses ('.$ses_weightage.')', 'End. Sem (100)', 'Total (100)', 'Grade');

        $pdf = new PDF();
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $locked = $this->get_mark_status($markid) == 5 ? TRUE : FALSE;
        if(!$locked) {
        	$pdf->SetFont('Arial','B',50);
            $pdf->SetTextColor(255,192,203);
            $pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
            $pdf->SetTextColor(0,0,0);
        }
        $pdf->SetFont('Arial','B',15);
        $pdf->Cell(0,10,'Course Grade Sheet',0,0,'C');
        $pdf->Ln(10);
       
        $arr = array('Campus', $campus['camname'], 'Session', 'NOV - 18');
        $pdf->printBox($arr, 'T');
        $arr = array('Branch', $branch['degshort'].' '.$branch['brname'].' - [ '.$mdetail['batch'].' ]', 'Mode', $branch['degmode']);
        $pdf->printBox($arr, '');
        $arr = array('Code', $mdetail['subcode'], 'Credit :', $subject['credit'], 'Semester', $mdetail['sem']);
        $pdf->printBox($arr, '');
        $arr = array('Course', $subject['subname'], 'Regulation', 2015);
        $pdf->printBox($arr, 'B');
        $pdf->SetWidths($warray);
        $pdf->SetAligns(array('C','C','C','C','C','C','C'));
        $pdf->SetFont('Arial','B',9.5);
        $pdf->Row($harray);
        $pdf->SetFont('Arial','',9.5);
        $pdf->SetAligns(array('C','C','L','C','C','C','C'));
        $data = array('MARKID' => $markid);
        $this->db->select('REGNO, SESMARK, ESEM, TOTMARK, RESULT');
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $query = $this->db->get('MARKS');
        
        if($query->num_rows() > 0) {
            $record = $query->result(); 
            $sno = 1;
            $regular = $shortage = $reappear = '';
            $reg_count = $sa_count = $ra_count = 0;
            foreach($record as $r) {               
               $student = $this->GM->get_student_info($r->REGNO);
               if($this->is_reappear($r->REGNO, $mdetail['subcode'])) {
                    $reappear[$ra_count++] = array($ra_count, $r->REGNO, $student['name'], $r->SESMARK, $r->ESEM, $r->TOTMARK, $r->RESULT);
               } elseif($this->is_shortage($r->REGNO, $mdetail['subcode'])) {
                    $shortage[$sa_count++] = array($sa_count, $r->REGNO, $student['name'], $r->SESMARK, $r->ESEM, $r->TOTMARK, $r->RESULT);
               } else {
                    $regular[$reg_count++] = array($reg_count, $r->REGNO, $student['name'], $r->SESMARK, $r->ESEM, $r->TOTMARK, $r->RESULT);
               }               
            }
            //$pdf->Row(array($sno++, $r->REGNO, $student['name'], $r->SESMARK, $r->ESEM, $r->TOTMARK, $r->RESULT));

            #universal count for 3 list
            $ucount = 1;
            for($j = 0; $j < 3; $j++) {
                if($j == 0) {
                    $list = $regular;
                    $count = $reg_count;
                } elseif($j == 1) {
                    $list = $shortage;
                    $count = $sa_count;
                } else {
                    $list = $reappear;
                    $count = $ra_count;
                }
                if($count > 0 && $j > 0) {
                    if($pdf->GetY() > 240) {
                        $pdf->Ln(2);
                        $pdf->SetFont('Arial','B', 9.5);
                        $pdf->Cell(38, 5, ' O = '.$o, 'TL', 0, 'C');
                        $pdf->Cell(38, 5, ' A+ = '.$ap, 'T', 0, 'C');
                        $pdf->Cell(38, 5, ' A = '.$a, 'T', 0, 'C');
                        $pdf->Cell(38, 5, ' B+ = '.$bp, 'T', 0, 'C');
                        $pdf->Cell(38, 5, ' B = '.$b, 'TR', 1, 'C');
                        $pdf->Cell(38, 5, 'RA: ReAppear', 'L', 0, 'C');
                        $pdf->Cell(38, 5, 'AB: Absent', '', 0, 'C');
                        $pdf->Cell(38, 5, 'W: Withdrawn', '', 0, 'C');
                        $pdf->Cell(38, 5, 'SA: Shortage of Attendance', '', 0, 'C');
                        $pdf->Cell(38, 5, '\'-\': Withheld', 'R', 1, 'C');
                        $pdf->Cell(47, 5, 'Mean = '.$values['mean'], 'LB', 0, 'C');
                        $pdf->Cell(47, 5, 'Standard Deviation = '.$values['sd'], 'B', 0, 'C');
                        $pdf->Cell(0, 5, 'Weightage: I. Asses '.$ses_weightage.' ; End. Sem. '.$esem_weightage, 'BR', 1, 'C');
                        $pdf->SetY(-31);
                        $pdf->SetFont('Arial','B', 9.5);
                        $pdf->Cell(120,5,'',0,0);
                        $pdf->Cell(0, 5, $staff['staffname'], '', 1, 'C');
                        $pdf->Cell(120,5,'',0,0);
                        $pdf->Cell(0, 5, 'Signature of the Course Instructor', '', 1, 'C');

                        $pdf->AddPage();
                        if(!$locked) {
        					$pdf->SetFont('Arial','B',50);
            				$pdf->SetTextColor(255,192,203);
            				$pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
            				$pdf->SetTextColor(0,0,0);
        				}
                        $pdf->SetFont('Arial','B',15);
                        $pdf->Cell(0,10,'Course Grade Sheet',0,0,'C');
                        $pdf->Ln(10);
                        $arr = array('Campus', $campus['camname'], 'Session', 'NOV - 18');
                        $pdf->printBox($arr, 'T');
                        $arr = array('Branch', $branch['degshort'].' '.$branch['brname'].' - [ '.$mdetail['batch'].' ]', 'Mode', $branch['degmode']);
                        $pdf->printBox($arr, '');
                        $arr = array('Code', $mdetail['subcode'], 'Credit :', $subject['credit'], 'Semester', $mdetail['sem']);
                        $pdf->printBox($arr, '');
                        $arr = array('Course', $subject['subname'], 'Regulation', 2015);
                        $pdf->printBox($arr, 'B');
                        $pdf->SetFont('Arial','B',9.5);
                        $pdf->SetAligns(array('C','C','C','C','C','C','C'));
                        $pdf->Row($harray);
                        $pdf->SetFont('Arial','',9.5);
                        $pdf->SetAligns(array('C','C','L','C','C','C','C'));
                    }                        
                    $pdf->SetFont('Arial','B',10);
                    if($j == 1)
                        $pdf->Cell(0,10,'List of Shortage of Attendance student',1,1,'C');
                    else
                        $pdf->Cell(0,10,'List of Reappear student',1,1,'C');
                    $pdf->SetFont('Arial','',9.5);
                }                
            for($i = 0; $i < $count; $i++) {
                $pdf->Row($list[$i]);

               if($pdf->GetY() > 240 || ($ucount++)%25 == 0) {
                    $pdf->Ln(2);
                    $pdf->SetFont('Arial','B', 9.5);
                    $pdf->Cell(38, 5, ' O = '.$o, 'TL', 0, 'C');
                    $pdf->Cell(38, 5, ' A+ = '.$ap, 'T', 0, 'C');
                    $pdf->Cell(38, 5, ' A = '.$a, 'T', 0, 'C');
                    $pdf->Cell(38, 5, ' B+ = '.$bp, 'T', 0, 'C');
                    $pdf->Cell(38, 5, ' B = '.$b, 'TR', 1, 'C');
                    $pdf->Cell(38, 5, 'RA: ReAppear', 'L', 0, 'C');
                    $pdf->Cell(38, 5, 'AB: Absent', '', 0, 'C');
                    $pdf->Cell(38, 5, 'W: Withdrawn', '', 0, 'C');
                    $pdf->Cell(38, 5, 'SA: Shortage of Attendance', '', 0, 'C');
                    $pdf->Cell(38, 5, '\'-\': Withheld', 'R', 1, 'C');
                    $pdf->Cell(47, 5, 'Mean = '.$values['mean'], 'LB', 0, 'C');
                    $pdf->Cell(47, 5, 'Standard Deviation = '.$values['sd'], 'B', 0, 'C');
                    $pdf->Cell(0, 5, 'Weightage: I. Asses '.$ses_weightage.' ; End. Sem. '.$esem_weightage, 'BR', 1, 'C');
                    $pdf->SetY(-31);
                    $pdf->SetFont('Arial','B', 9.5);
                    $pdf->Cell(120,5,'',0,0);
                    $pdf->Cell(0, 5, $staff['staffname'], '', 1, 'C');
                    $pdf->Cell(120,5,'',0,0);
                    $pdf->Cell(0, 5, 'Signature of the Course Instructor', '', 1, 'C');

                    $pdf->AddPage();
                    if(!$locked) {
        				$pdf->SetFont('Arial','B',50);
            			$pdf->SetTextColor(255,192,203);
            			$pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
            			$pdf->SetTextColor(0,0,0);
        			}
                    $pdf->SetFont('Arial','B',15);
                    $pdf->Cell(0,10,'Course Grade Sheet',0,0,'C');
                    $pdf->Ln(10);
                    $arr = array('Campus', $campus['camname'], 'Session', 'NOV - 18');
                    $pdf->printBox($arr, 'T');
                    $arr = array('Branch', $branch['degshort'].' '.$branch['brname'].' - [ '.$mdetail['batch'].' ]', 'Mode', $branch['degmode']);
                    $pdf->printBox($arr, '');
                    $arr = array('Code', $mdetail['subcode'], 'Credit :', $subject['credit'], 'Semester', $mdetail['sem']);
                    $pdf->printBox($arr, '');
                    $arr = array('Course', $subject['subname'], 'Regulation', 2015);
                    $pdf->printBox($arr, 'B');
                    $pdf->SetFont('Arial','B',9.5);
                    $pdf->SetAligns(array('C','C','C','C','C','C','C'));
                    $pdf->Row($harray);
                    $pdf->SetFont('Arial','',9.5);
                    $pdf->SetAligns(array('C','C','L','C','C','C','C'));
                }
            }}
            if($pdf->GetY() < 250) {
                    $pdf->Ln(2);
                    $pdf->SetFont('Arial','B', 9.5);
                    $pdf->Cell(38, 5, ' O = '.$o, 'TL', 0, 'C');
                    $pdf->Cell(38, 5, ' A+ = '.$ap, 'T', 0, 'C');
                    $pdf->Cell(38, 5, ' A = '.$a, 'T', 0, 'C');
                    $pdf->Cell(38, 5, ' B+ = '.$bp, 'T', 0, 'C');
                    $pdf->Cell(38, 5, ' B = '.$b, 'TR', 1, 'C');
                    $pdf->Cell(38, 5, 'RA: ReAppear', 'L', 0, 'C');
                    $pdf->Cell(38, 5, 'AB: Absent', '', 0, 'C');
                    $pdf->Cell(38, 5, 'W: Withdrawn', '', 0, 'C');
                    $pdf->Cell(38, 5, 'SA: Shortage of Attendance', '', 0, 'C');
                    $pdf->Cell(38, 5, '\'-\': Withheld', 'R', 1, 'C');
                    $pdf->Cell(47, 5, 'Mean = '.$values['mean'], 'LB', 0, 'C');
                    $pdf->Cell(47, 5, 'Standard Deviation = '.$values['sd'], 'B', 0, 'C');
                    $pdf->Cell(0, 5, 'Weightage: I. Asses '.$ses_weightage.' ; End. Sem. '.$esem_weightage, 'BR', 1, 'C');
                    $pdf->SetY(-31);
                    $pdf->SetFont('Arial','B', 9.5);
                    $pdf->Cell(120,5,'',0,0);
                    $pdf->Cell(0, 5, $staff['staffname'], '', 1, 'C');
                    $pdf->Cell(120,5,'',0,0);
                    $pdf->Cell(0, 5, 'Signature of the Course Instructor', '', 1, 'C');
            }
        }
        $pdf->output('Course Grade.pdf','I');
    }

    public function test($regno) {
        $data = array('REGNO' => $regno);
        $this->db->where($data);
        $query = $this->db->get('REGISTER');
        if($query->num_rows() > 0) {
            $records = $query->result();
            $sno = 1;
            echo '<table>';
            foreach($records as $r) {
                echo '<tr>';
                echo '<td>'.$sno++.'</td>';
                echo '<td>'.$r->REGNO.'</td>';
                echo '<td>'.$r->MARKID.'</td>';
                echo '<td>'.$r->SUBCODE.'</td>';
                echo '<td>'.$r->FLAG.'</td>';
                echo '<td>'.$r->STATUS.'</td>';
                echo '<td>'.$r->SESID.'</td>';
                echo '</tr>';
            }
            echo '<table>';

        } else {
            echo '0 record';
        }

        //$data = array('MARKID > ' => 300000 );
        $this->db->select('COUNT(*) AS CNT');
        //$this->db->where($data);
        $query = $this->db->get('REGISTER');
        $row = $query->row();
        echo 'Record: '.$row->CNT;
    }

    public function get_fa_slist($staffid) {
        $data = array('FASTAFFID' => $staffid);
        $this->db->select('REGNO, BRCODE');
        $this->db->where($data);
        $query = $this->db->get('FACULTY_ADVISOR');
        if($query->num_rows() > 0) {
            $record = $query->result();
            $sno = 1;
            $table = '';
            foreach($record as $r) {
                $branch = $this->GM->get_branch_info($r->BRCODE);
                $student = $this->GM->get_student_info($r->REGNO);
                $table .= '<tr>';
                $table .= '<td>'.$sno++.'</td>';
                $table .= '<td>'.$r->REGNO.'</td>';
                $table .= '<td class="text-left">'.$student['name'].'</td>';
                $table .= '<td>'.$branch['brname'].'</td>';
                $table .= '<td>'.$student['sem'].'</td>';
                $table .= '<td><button type="button" class="btn cell-green btn-sm btn-flat view" value="'.$r->REGNO.'"><i class="fa fa-file-text-o"></i> View</button></td>';
                $table .= '</tr>';
            }
            return $table;
        } else return '';
    }


    public function mail_student_records($regno, $sesid, $email, $flag = TRUE) {
        $this->load->model('Enrollment_model', 'EM');
        $flag = FALSE;
        $student_record = $this->EM->subject_att_and_mark($regno, $sesid, $flag);
        if($student_record['result'] == 'true') {
            $student = $this->GM->get_student_info($regno);
            $message  = '<html><body>';
            $message .= '<h2 style="text-align: center"><strong>Anna University, Chennai - 6000025</strong></h2>';
            $message .= '<h3 style="text-align: center"><strong>Additional Controller of Examinations</strong></h3>';
            $message .= '<h3 style="text-align: center"><strong>Choice Based Credit System - Regulation 2015</strong></h3>';         
            $message .= "<p><strong>Name:</strong> ".$student['name']."</p>";
            $message .= "<p><strong>Roll Number:</strong> ".$student['regno']."</p>";
            $message .= "<p><strong>Branch:</strong> ".$student['branch']."</p>";
            $message .= "<p><strong>Semester:</strong> ".$student['sem']."</p>";
            $message .= '<table border="1" style="border-collapse:collapse; margin-left: auto; margin-right: auto" cellspacing="0" cellpadding="5">';
            $message .= '<thead><tr>';
            $message .= '<th style="background-color: #0073b7 !important; color: #ffffff !important;">S.No.</th>';
            $message .= '<th style="background-color: #0073b7 !important; color: #ffffff !important;">Course Code</th>';
            $message .= '<th style="background-color: #0073b7 !important; color: #ffffff !important;">Course Name</th>';
            if($flag) {
                $message .= '<th style="background-color: #00735c !important; color: #ffffff !important;">Att. 1</th>';
                $message .= '<th style="background-color: #00735c !important; color: #ffffff !important;">Att. 2</th>';
                $message .= '<th style="background-color: #00735c !important; color: #ffffff !important;">Att. 3</th>';
                $message .= '<th style="background-color: #00735c !important; color: #ffffff !important;">Att.(%)</th>';
                $message .= '<th style="background-color: #002240 !important; color: #ffffff !important;">Assess. 1</th>';
                $message .= '<th style="background-color: #002240 !important; color: #ffffff !important;">Assess. 2</th>';
                $message .= '<th style="background-color: #002240 !important; color: #ffffff !important;">Assess. 3</th>';
                $message .= '<th style="background-color: #002240 !important; color: #ffffff !important;">I. Assess</th>';
            }
            $message .= '<th style="background-color: #f20041 !important; color: #ffffff !important;">Grade</th>';
            $message .= '</tr>';
            $message .= '</thead><tbody>';
            $message .= $student_record['msg'];
            $message .= '</tbody></table>';            
            $message .= '<p>For more information login into SEMS: <a href = "'.base_url().'login/student">SEMS Student Login</a></p>';        
            $message .= '</body></html>';
            
            $this->email
                ->from('admin@acoe.annauniv.edu', 'ACOE')
                ->to($email)
                ->subject('Student Academic Records')
                ->message($message)
                ->set_mailtype('html');

            if($this->email->send()) {
                return TRUE;
            } else {
                return FALSE;
            }
        } 
        return FALSE;
    }

    //Function to check whether given subcode is reappearance
    public function is_reappear($regno, $subcode) {
        $data = array('REGNO' => $regno, 'SUBCODE' => $subcode, 'GRADE' => 'RA');
        $this->db->select('GRADE');
        $this->db->where($data);
        $query = $this->db->get('REAPPEAR');
        if($query->num_rows() > 0) {
            return TRUE;
        } 

        $data = array('REGNO' => $regno, 'SCODE' => $subcode, 'GRADE' => 'RA');
        $this->db->select('GRADE');
        $this->db->where($data);
        $query = $this->db->get('RESULT');
        if($query->num_rows() > 0) {
            return TRUE;
        } 

        $data = array('REGNO' => $regno, 'SCODE' => $subcode, 'GRADE' => 'RA');
        $this->db->select('GRADE');
        $this->db->where($data);
        $query = $this->db->get('RESULTN15');
        if($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function is_shortage($regno, $subcode) {        
        $data = array('REGNO' => $regno, 'SUBCODE' => $subcode, 'GRADE' => 'SA');
        $this->db->select('GRADE');
        $this->db->where($data);
        $query = $this->db->get('REAPPEAR');
        if($query->num_rows() > 0) {
            return TRUE;
        } 
        
        $data = array('REGNO' => $regno, 'SCODE' => $subcode, 'GRADE' => 'SA');
        $this->db->select('GRADE');
        $this->db->where($data);
        $query = $this->db->get('RESULT');
        if($query->num_rows() > 0) {
            return TRUE;
        } 

        $data = array('REGNO' => $regno, 'SCODE' => $subcode, 'GRADE' => 'SA');
        $this->db->select('GRADE');
        $this->db->where($data);
        $query = $this->db->get('RESULTN15');
        if($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function attendance_pdf($markid, $sesid) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_no_wm.php');

        $mdetail = $this->markid_details($markid);
        $staff = $this->GM->get_staff_info($mdetail['staffid']);
        $subject = $this->GM->get_subject_info($mdetail['subcode']);
        $branch = $this->GM->get_branch_info($mdetail['brcode']);
        $campus = $this->GM->get_campus_info($branch['camcode']);
        
        $table = '';

        $j = 0;
		
		$warray = array(12, 22, 90, 33, 33);
		$harray = array('S.No.', 'Register No.', 'Name of the Student', 'ANS. BOOK NO', 'Signature');

        $pdf = new PDF();
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $pdf->SetFont('Arial','B',15);
        $pdf->Cell(0,10,'Attendance Sheet',0,0,'C');
        $pdf->SetFont('Arial','',10);
        $pdf->Ln(9);
        $pdf->SetX(-100);
        $pdf->Cell(60,10,'Date of Examination: ',0,0,'');
        $pdf->Cell(0,10,'Session : FN / AN',0,0,'');

        $pdf->Ln(10);
        $arr = array('Campus', $campus['camname'], 'Session', 'NOV - 18');
        $pdf->printBox($arr, 'T');
        //$arr = array('Branch', $branch['degshort'].' '.$branch['brname'], 'Mode', $branch['degmode']);
        $arr = array('Branch', $branch['degshort'].' '.$branch['brname'], 'Mode', $branch['degmode']);
        $pdf->printBox($arr, '');
        $arr = array('Code', $mdetail['subcode'], 'Credit :', $subject['credit'], 'Semester', $mdetail['sem']);
        $pdf->printBox($arr, '');
        $arr = array('Course', $subject['subname'], 'Regulation', 2015);
        $pdf->printBox($arr, 'B');

        $data = array('MARKID' => $markid);
        $this->db->select('REGNO');
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $query = $this->db->get('MARKS');
        if($query->num_rows() > 0) {
            $record = $query->result();
            $sno = 0;
            $pdf->SetWidths($warray);
            $pdf->SetFont('Arial','B',9.5);
            $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C'));
            $pdf->Row($harray);
            $pdf->SetAligns(array('C','C','L','C','C','C','C','C','C'));
            $pdf->SetFont('Arial','',9.5);          
            foreach($record as $r) {
                $student = $this->GM->get_student_info($r->REGNO);
                $pdf->Row(array(++$sno, $r->REGNO, $student['name'], '', ''));
                if($sno%25 == 0 || $pdf->GetY() > 250) {
                    $pdf->SetY(-31);
                    $pdf->SetFont('Arial','B', 9.5);
                    $pdf->Cell(120,5,'',0,0);
                    $pdf->Cell(0, 5, $staff['staffname'], '', 1, 'C');
                    $pdf->Cell(120,5,'',0,0);
                    $pdf->Cell(0, 5, 'Signature of the Course Instructor', '', 1, 'C');
                    
                    $pdf->AddPage();                    
                    $pdf->SetFont('Arial','B',15);
                    $pdf->Cell(0,10,'Attendance Sheet',0,0,'C');
                    $pdf->SetFont('Arial','',10);
                    $pdf->Ln(9);
                    $pdf->SetX(-100);
                    $pdf->Cell(60,10,'Date of Examination: ',0,0,'');
                    $pdf->Cell(0,10,'Session : FN / AN',0,0,'');

                    $pdf->Ln(10);
                    $arr = array('Campus', $campus['camname'], 'Session', 'NOV - 18');
                    $pdf->printBox($arr, 'T');
                    $arr = array('Branch', $branch['degshort'].' '.$branch['brname'], 'Mode', $branch['degmode']);
                    $pdf->printBox($arr, '');
                    $arr = array('Code', $mdetail['subcode'], 'Credit :', $subject['credit'], 'Semester', $mdetail['sem']);
                    $pdf->printBox($arr, '');
                    $arr = array('Course', $subject['subname'], 'Regulation', 2015);
                    $pdf->printBox($arr, 'B');
                    $pdf->SetFont('Arial','B',9.5);
                    $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C'));
                    $pdf->Row($harray);
                    $pdf->SetFont('Arial','',9.5);
                    $pdf->SetAligns(array('C','C','L','C','C','C','C','C','C'));
                }
            }
            if($pdf->GetY() < 260) {
                $pdf->SetY(-31);
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(120,5,'',0,0);
                $pdf->Cell(0, 5, $staff['staffname'], '', 1, 'C');
                $pdf->Cell(120,5,'',0,0);
                $pdf->Cell(0, 5, 'Signature of the Course Instructor', '', 1, 'C');
            }
        }

        $pdf->output('Attendance - '.$mdetail['subcode'].'.pdf','I');
    }
	
	public function get_sub_sem($subcode, $brcode, $sesid) {
    	$data = array('SUBCODE' => $subcode, 'BRCODE' => $brcode, 'SESID' => $sesid);
    	$this->db->select('SEM');
    	$this->db->where($data);
    	$query = $this->db->get('SUBLINK');
    	if($query->num_rows() > 0) {
    		$row = $query->row();
    		return $row->SEM;
    	} else return 0;
    }
	
	public function hod_prevention_list_pdf($depcode = 0, $highersem) {
        if($depcode == 0) $depcode = $this->session->userdata('depcode');
        $this->load->model('CSO_model', 'CSO');
        $sesid = $this->sesid;

        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_no_wm.php');

        $dept = $this->GM->get_dept_info($depcode);
        $campus = $this->GM->get_campus_info($dept['camcode']);
        
        $table = '';

        $j = 0;

        $locked = $this->PM->all_prevention_locked($depcode, $highersem);

        $pdf = new PDF();
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        if(!$locked) {
        	$pdf->SetFont('Arial','B',50);
            $pdf->SetTextColor(255,192,203);
            $pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
            $pdf->SetTextColor(0,0,0);
        }
		$page_title = 'Department-wise Prevented List';

        $pdf->SetFont('Arial','B',15);
        $pdf->Cell(0,10,$page_title,0,0,'C');
        $pdf->Ln(10);
        $header_arr1 = array('Campus', $campus['camname'], 'Session', 'APR - 18');
		$header_dept = array('Department', $dept['depname'], 'Date', date('d-m-Y'));
        $pdf->printBox($header_arr1, 'T');
		$pdf->printBox($header_dept, 'TB');
        $harray = array('S.No.', 'Register No.', 'Name of the Student', 'Attendance %', 'Remarks');
        $warray = array(20, 40, 80, 30, 20);

        for($i = 0; $i < 2; $i++) {
            if($i == 0) $prgm = 'U.G';
            else $prgm = 'P.G';
			$this->load->model('CSO_model', 'CSO');
			#degmode - Full Time is conisdered
            $brcode = $this->CSO->get_prgm_branch($depcode, $prgm, 'F');
			if($brcode == FALSE) continue;
            foreach ($brcode as $key) {
                if(!$this->PM->is_prevented_category($key)) continue;                

                $branch = $this->GM->get_branch_info($key);                
                $header_arr2 = array('Branch', $branch['degshort'].' '.$branch['brname'], 'Mode', $branch['degmode']);

                $data = array('BRCODE' => $key, 'SESID' => $sesid);
                if($highersem) $data['SEM !='] = 1;
                else $data['SEM'] = 1;

                $this->db->select('DISTINCT(SUBCODE)');
                $this->db->where($data);
                $this->db->order_by('SUBCODE');
                $query = $this->db->get('REGISTER');
                if($query->num_rows() > 0) {
                	$first = TRUE;
                	$outer_header;                	
                    foreach($query->result() as $r1) {
                        if(!$this->PM->is_prevented_category($key, $r1->SUBCODE)) continue;
                        $sem = $this->get_sub_sem($r1->SUBCODE, $key, $sesid);
                        $subject = $this->GM->get_subject_info($r1->SUBCODE);
                        $header_arr3 = array('Code', $r1->SUBCODE, 'Credit:', $subject['credit'], 'Semester', $sem);
                        $header_arr4 = array('Course', $subject['subname'], 'Regulation', 2015);

                        $end = $pdf->GetY() > 235 ? TRUE : FALSE;
                        if($end || $first) {
                        	$first = FALSE;
                        	if($end) {
                        		$pdf->SetY(-28);
                        		$pdf->SetFont('Arial','B', 9.5);
                        		$pdf->Cell(0, 6, 'Signature of the HOD','','','L');	
                        		$pdf->Cell(0, 6, 'Signature of the ACOE', '', 1, 'R');

                        		$pdf->AddPage();
                        		if(!$locked) {
                        			$pdf->SetFont('Arial','B',50);
                        			$pdf->SetTextColor(255,192,203);
                        			$pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
                        			$pdf->SetTextColor(0,0,0);
                        		}
                        		$pdf->SetFont('Arial','B',15);
                        		$pdf->Cell(0,10,$page_title,0,0,'C');
                        		$pdf->Ln(10);
                        	}
                        	$pdf->SetFont('Arial','',9.5);
                        	//$pdf->printBox($header_arr1, 'T');
                        	$pdf->printBox($header_arr2, 'T');
                        	$outer_header = TRUE;

                        } //end of if

                        $data = array('BRCODE' => $key, 'SUBCODE' => $r1->SUBCODE, 'STATUS <' => 2, 'SESID' => $sesid);
                        $this->db->select('REGNO, ATTPER');
                        $this->db->where($data);
                        $this->db->order_by('REGNO');
                        $query = $this->db->get('PREVENTED_STUDENT');
                        if($query->num_rows() > 0) {
                            $sno = 1;                            
                            $first = TRUE;
                            foreach ($query->result() as $r2) {
                            	$end = $pdf->GetY() > 250 ? TRUE : FALSE;
                            	if($end || $first) {
                            		$first = FALSE;
                            		if($end) {
                            			$pdf->SetY(-28);
                            			$pdf->SetFont('Arial','B', 9.5);
                            			$pdf->Cell(0, 6, 'Signature of the HOD','','','L');	
                            			$pdf->Cell(0, 6, 'Signature of the ACOE', '', 1, 'R');
                        		
                            			$pdf->AddPage();
                            			if(!$locked) {
                            				$pdf->SetFont('Arial','B',50);
                            				$pdf->SetTextColor(255,192,203);
                            				$pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
                            				$pdf->SetTextColor(0,0,0);
                            			}
                            			$pdf->SetFont('Arial','B',15);
                            			$pdf->Cell(0,10,$page_title,0,0,'C');
                            			$pdf->Ln(10);
                            		}
                            		if(!$outer_header) {
                            			$pdf->SetFont('Arial','',9.5);
                            			//$pdf->printBox($header_arr1, 'T');
                            			$pdf->printBox($header_arr2, 'T');                            			
                                	}
                                	$outer_header = FALSE;

                                	$pdf->printBox($header_arr3, 'T');
                                	$pdf->printBox($header_arr4, 'B');
                                	
                                	$pdf->SetWidths($warray);
                                	$pdf->SetFont('Arial','B',9.5);
                                	$pdf->SetAligns(array('C','C','C','C', 'C'));
                                	$pdf->Row($harray);
                                	$pdf->SetFont('Arial','',9.5);
                                	$pdf->SetAligns(array('C','C','L','C', 'C'));

                                } //end of if

                                $student = $this->GM->get_student_info($r2->REGNO);
                                if($r2->ATTPER < 75) $pstatus = 'P';
                                else $pstatus = '';
                                $pdf->Row(array($sno++, $r2->REGNO, $student['name'], $r2->ATTPER, $pstatus));
                            }
                        } 
                    } //end of for
                } //end of if                
            }
        }
        if($pdf->GetY() < 260) {
        	$pdf->SetY(-28);
        	$pdf->SetFont('Arial','B', 9.5);
        	$pdf->Cell(0, 6, 'Signature of the HOD','','','L');	
        	$pdf->Cell(0, 6, 'Signature of the ACOE', '', 1, 'R');
        }

        $pdf->output('Cumulative Prevention.pdf','I');
    }

    function get_sems_dept($camcode = null, $flag = null) {
        $this->db->select('DISTINCT(BRCODE)');
        $query = $this->db->get('REGISTER');
        if($query->num_rows() < 1) {
            return;
        }

        foreach($query->result() as $row) {
            $brcode[] = $row->BRCODE;
        }
        $this->db->select('DISTINCT(DEPCODE)');
        $this->db->where_in('BRCODE', $brcode);
        $query = $this->db->get('BRANCH');
        if($query->num_rows() < 1) {
            return;
        }

        foreach($query->result() as $row) {
            $depcode[] = $row->DEPCODE;
        }

        $this->db->select('DEPCODE, DEPNAME');
        if($camcode != null) $this->db->where(array('CAMCODE' => $camcode));
        $this->db->where_in('DEPCODE', $depcode);
        $query = $this->db->get('DEPARTMENT');
        if($query->num_rows() < 1) {
            return;
        }

        $dlist = '';

        $option = '<option></option>';
        foreach($query->result() as $row) {
            $dlist[] = $row->DEPCODE;
            $option .= '<option value="'.$row->DEPCODE.'">'.$row->DEPNAME.'</option>';
        }

        if($flag === TRUE) return $dlist;

        return $option;        
    }

    public function already_assigned($depcode, $role, $sesid) {
        $data = array('DEPCODE' => $depcode, 'DESG' => $role, 'SESID' => $sesid);
        $this->db->select('STAFFID');
        $this->db->where($data);
        $query = $this->db->get('EXCEPTIONAL');
        if($query->num_rows() > 0)
            return TRUE;

        $data = array('DEPCODE' => $depcode, 'ROLE' => $role, 'SESID' => $sesid);
        $this->db->select('STAFFID');
        $this->db->where($data);
        $query = $this->db->get('USER_PERMISSIONS');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    public function assign_role($depcode, $staffid, $role, $sesid) {        
        switch($role) {
            case 'HOD':
            case 'TT':
            case 'DCSO':
                if($this->already_assigned($depcode, $role, $sesid)) {
					if(!$this->session->userdata('is_admin'))
						return array('result' => FALSE, 'msg' => 'Role already assigned');
				}
                break;        
        }

        if($role == 'HOD' || $role == 'Admin' || $role == 'SAdmin') {   
            $record = array('DEPCODE' => $depcode, 'STAFFID' => $staffid, 'DESG' => $role, 'SESID' => $sesid, 'STATUS' => 1);
            $this->db->insert('EXCEPTIONAL', $record);
        } else {
            //if($role == 'TT') {
			$dept = $this->GM->get_dept_info($depcode);            
			$record = array(
				'CAMCODE' => $dept['camcode'], 
				'DEPCODE' => $depcode, 'STAFFID' => $staffid, 
				'ROLE' => $role,
                'PRGM' => 'P.G',
				'LAST_MODIFIED' => date('d-m-Y h:i:sa'), 
				'STATUS' => 1, 
				'SESID' => $sesid);
			$this->db->insert('USER_PERMISSIONS', $record);
		}
        return array('result' => TRUE, 'msg' => 'Role assigned successfully');
    }

    public function unassign_role($depcode, $staffid, $role, $sesid) {
        if($role == 'HOD' || $role == 'Admin' || $role == 'SAdmin') {
            $data = array('DEPCODE' => $depcode, 'STAFFID' => $staffid, 'DESG' => $role, 'SESID' => $sesid);
            $this->db->set(array('STATUS' => 0));
            $this->db->where($data);
            $this->db->delete('EXCEPTIONAL');
        } else {
            //if($role == 'TT') {
			$dept = $this->GM->get_dept_info($depcode);
			$data = array('CAMCODE' => $dept['camcode'], 'DEPCODE' => $depcode, 'STAFFID' => $staffid, 'ROLE' => $role, 'SESID' => $sesid);
            $this->db->set(array('STATUS' => 0));
			$this->db->where($data);
			$this->db->update('USER_PERMISSIONS');
		}

        return array('result' => TRUE, 'msg' => 'Role removed successfully');
    }

    public function get_roles_all($depcode = null, $role, $sesid) {
        $data = array('DESG' => $role);
        if(isset($depcode)) $data['DEPCODE'] = $depcode;

        $table = '';

        $this->db->select('STAFFID, DESG, STATUS');
        $this->db->where($data);
        $query = $this->db->get('EXCEPTIONAL');
        if($query->num_rows() > 0) {            
            foreach($query->result() as $row) {
                $staff = $this->GM->get_staff_info($row->STAFFID);
                $dept = $this->GM->get_dept_info($staff['depcode']);
                $table .= '<tr>';
                $table .= '<td>'.$row->STAFFID.'</td>';
                $table .= '<td>'.$staff['staffname'].'</td>';
                $table .= '<td>'.$staff['desg'].'</td>';
                $table .= '<td>'.$dept['depname'].'</td>';
                $table .= '<td>'.$row->DESG.'</td>';
                $table .= '<td><button type="button" class="btn cell-red btn-xs btn-flat unassign-role" id="unassign-role" value="'.$row->STAFFID.'"><i class="fa fa-fw fa-remove"></i></button></td>';
                if($row->STATUS == 1)
                    $table .= '<td><span class="label cell-green">Active</span></td>';
                else
                    $table .= '<td><span class="label cell-red">Disabled</span></td>';
                $table .= '</tr>';
            }            
        }

        $data = array('ROLE' => $role, 'SESID' => $sesid);
        if(isset($depcode)) $data['DEPCODE'] = $depcode;

        $this->db->select('STAFFID, ROLE, STATUS');
        $this->db->where($data);
        $query = $this->db->get('USER_PERMISSIONS');
        if($query->num_rows() > 0) {
            $table = '';
            foreach($query->result() as $row) {
                $staff = $this->GM->get_staff_info($row->STAFFID);
                $dept = $this->GM->get_dept_info($staff['depcode']);
                $table .= '<tr>';
                $table .= '<td>'.$row->STAFFID.'</td>';
                $table .= '<td>'.$staff['staffname'].'</td>';
                $table .= '<td>'.$staff['desg'].'</td>';
                $table .= '<td>'.$dept['depname'].'</td>';
                $table .= '<td>'.$row->ROLE.'</td>';
                $table .= '<td><button type="button" class="btn cell-red btn-xs btn-flat unassign-role" id="unassign-role" value="'.$row->STAFFID.'"><i class="fa fa-fw fa-remove"></i></button></td>';
                if($row->STATUS == 1)
                    $table .= '<td><span class="label cell-green">Active</span></td>';
                else
                    $table .= '<td><span class="label cell-red">Disabled</span></td>';
                $table .= '</tr>';
            }            
        }


        return array('result' => TRUE, 'msg' => $table);

        return array('result' => FALSE, 'msg' => 'No staff assigned');
    }

    public function is_fa_assigned($regno) {
        $this->db->select('STATUS');
        $this->db->where(array('REGNO' => $regno, 'STATUS' => 1));
        $query = $this->db->get('FACULTY_ADVISOR');
        if($query->num_rows() > 0) {
            return TRUE;
        } else return FALSE;
    }

    public function fa_unassigned_slist($brcode, $sem) {
        $data = array('BRCODE' => $brcode, 'SEM' => $sem);
        $this->db->select('REGNO, NAME');
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $query = $this->db->get('STUDENT');
		$dt = '';
        if($query->num_rows() > 0) {
            $slist = $regno = '';
            $i = 0;
            foreach($query->result() as $row) {
                if($this->is_fa_assigned($row->REGNO)) continue;
                $j = 0;
                $dt[$i][$j++] = $row->REGNO;
                $dt[$i][$j++] = $row->NAME;
                $dt[$i++][$j] = '<input class="toggle" type="checkbox" id="'.$row->REGNO.'" name="slist['.$row->REGNO.']" data-toggle="toggle" data-size="mini" data-onstyle="success" data-offstyle="danger">';
            }
            return array('result' => TRUE, 'msg' => $dt);
        }
        return array('result' => FALSE, 'msg' => 'No unassigned student found');
    }

    public function fa_assigned_slist($staffid) {
        $data = array('FASTAFFID' => $staffid, 'STATUS' => 1);
        $this->db->select('REGNO');
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $query = $this->db->get('FACULTY_ADVISOR');
        if($query->num_rows() > 0) {
            $slist = $regno = '';
            $i = 0;
            foreach($query->result() as $row) {
                $student = $this->GM->get_student_info($row->REGNO);
                $j = 0;
                $dt[$i][$j++] = $row->REGNO;
                $dt[$i][$j++] = $student['name'];
                $dt[$i++][$j] = '<button type="button" class="btn cell-red btn-xs btn-flat unassign-fa" value="'.$row->REGNO.'"><i class="fa fa-fw fa-remove"></i></button>';
            }
            return array('result' => TRUE, 'msg' => $dt);
        }
        return array('result' => FALSE, 'msg' => 'No assigned student found');
    }

    public function assign_fa($brcode, $staffid, $slist) {
        $staff = $this->GM->get_staff_info($staffid);
        foreach ($slist as $key => $value) {
            $data = array('DEPCODE' => $staff['depcode'], 'BRCODE' =>$brcode, 'FASTAFFID' => $staffid, 'REGNO' => $key, 'ATIME' => date('d-M-Y h:i:sa'), 'STATUS' => 1);
            $this->db->insert('FACULTY_ADVISOR', $data);
        }
        return array('result' => TRUE, 'msg' => 'Faculty Advisor assigned successfully');
    }

    public function unassign_fa($regno) {
        $udata = array('RTIME' => date('d-M-Y h:i:sa'), 'STATUS' => 0);
        $wdata = array('REGNO' => $regno, 'STATUS' => 1);
        $this->db->set($udata);
        $this->db->where($wdata);
        $this->db->update('FACULTY_ADVISOR');
        
        return array('result' => TRUE, 'msg' => 'Faculty Advisor unassigned for '.$regno);
    }
	
	public function attlist_pdf($camcode, $depcode, $prgm, $subcode, $sesid) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_no_wm.php');
		
		$pdf = new PDF();
		$pdf->SetTitle('CBCS R2015');
		$pdf->AliasNbPages();
		$table = '';
		$j = 0;
		$twidth = 13;
		
		$this->load->model('Enrollment_model', 'EM');
		$harray1 = array('S.No.', 'Register No', 'Name of the Student', 'ANS. BOOK NO', 'Signature');
		$harray2 = array('S.No.', 'Seat No.', 'Register No', 'Name of the Student', 'ANS.  BOOK NO', 'Signature');		
		$warray1 = array(12, 25, 76, 35, 42);
		$warray2 = array(12, 18, 22, 71, 35, 32);
		
		if($depcode[0] == 0) {
            $depcode = $this->SM->get_sems_dept($camcode, TRUE);
        }
		foreach($depcode as $d) {
			$brcode = $this->CSO->get_prgm_branch($d, $prgm);
			if($brcode === FALSE) continue;
			$data = array('SUBCODE' => $subcode);
			$this->db->select('MARKID');
			$this->db->where($data);
			$this->db->where_in('BRCODE', $brcode);
			$this->db->order_by('MARKID');
			$query = $this->db->get('SUBLINK');
			if($query->num_rows() > 0) {
				$record = $query->result();
				foreach ($record as $r ) {
					$markid = $r->MARKID;
					//$sesid = $r ->SESID;
					$seq_status = $this->is_markid_seq_generated($markid);
					$mdetail = $this->markid_details($markid);
					$subject = $this->GM->get_subject_info($subcode);
					$branch = $this->GM->get_branch_info($mdetail['brcode']);
					$campus = $this->GM->get_campus_info($branch['camcode']);
					$exam_date = $this->get_examdate($subcode, $sesid);
					
					$data = array('MARKID' => $markid, 'SESID' => $sesid);
					$this->db->select('REGNO');
					$this->db->where($data);
					$this->db->order_by('REGNO');
					$query = $this->db->get('MARKS');
					$new_page = TRUE;
					if($query->num_rows() > 0) {
						$record = $query->result();
						$sno = 0;         
						foreach($record as $r) {
							if($new_page){
								$new_page = FALSE;
								$pdf->AddPage();
								$pdf->SetFont('Arial','B',15);
								$pdf->Cell(0,10,'Regular Examination Attendance List',0,0,'C');
								$pdf->Ln(10);
								$arr = array('Campus', $campus['camname'], 'Session', 'APR - 18');
								$pdf->printBox($arr, 'T');
								$arr = array('Branch', $branch['degshort'].' '.$branch['brname'], 'Mode', $branch['degmode']);
								$pdf->printBox($arr, '');	
								$arr = array('Code', $mdetail['subcode'], 'Date :', $exam_date['DOE'].' '.$exam_date['SESS'], 'Semester', $mdetail['sem']);
								$pdf->printBox($arr, '');
								$arr = array('Course', $subject['subname'], 'Batch No:',$mdetail['batch'] ,'Regulation', 2015);
								$pdf->printBox($arr, 'B');
								if($seq_status) {
									$pdf->SetWidths($warray2);
									$pdf->SetFont('Arial','B',9.5);
									$pdf->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C'));
									$pdf->Row($harray2);
									$pdf->SetAligns(array('C','C','C','L','C','C','C','C','C','C','C'));
									$pdf->SetFont('Arial','',9.5); 
								} else {
									$pdf->SetWidths($warray1);
									$pdf->SetFont('Arial','B',9.5);
									$pdf->SetAligns(array('C','C','C','C','C','C','C','C','C','C'));
									$pdf->Row($harray1);
									$pdf->SetAligns(array('C','C','L','C','C','C','C','C','C','C'));
									$pdf->SetFont('Arial','',9.5); 
								}
							}
							$student = $this->GM->get_student_info($r->REGNO);
							$j = 1;
							$carray[$j++] = $r->REGNO;
							$carray[$j++] = $student['name'];
							$carray[$j] = '';
							
							if($this->PM->is_prevented($r->REGNO, $markid, $sesid)) {
								$p = 'Prevented'; $s= 'Not Permitted';
							}
							elseif(!$this->EM->is_paid_examfee($r->REGNO, $sesid)) {
								$p = 'Exam Fee Not Paid'; $s= 'Not Permitted';
							}
							else $s = $p = '';
							
							if($seq_status) {
								$this->db->select('SEATNO');
								$this->db->where(array('MARKID' => $markid, 'REGNO' => $r->REGNO));
								$query = $this->db->get('SEAT_NO');
								if($query->num_rows() == 1 ) {
									$row = $query->row();
									$seatno = $row->SEATNO;
								} else $seatno = '';
								$pdf->Row(array(++$sno, $seatno, $r->REGNO, $student['name'], $p, $s));
							} else{
								$pdf->Row(array(++$sno, $r->REGNO, $student['name'], $p, $s));
							}
							
							if($sno%25 == 0 || $pdf->GetY() > 250) {
								$new_page = TRUE;
								$pdf->SetY(-51);
								$arr = array('Report on', date("d-M-Y h:i:sa"),'Packet No.:',' ','Total No. of Present', ' ');
								$pdf->printBox1($arr, 'T');
								$pdf->Cell(0,2,'','LR',1);
								$arr = array('Staff Name', '','Total No. of Absent', ' ');
								$pdf->printBox1($arr, '');
								$pdf->Cell(0,2,'','LR',1);
								$arr = array('Signature', ' ', 'Page No.', $pdf->PageNo().'/{nb}');
								$pdf->printBox1($arr, 'B');
							} //end of if
						} //end of foreach
						$pdf->SetY(-51);
						$arr = array('Report on', date("d-M-Y h:i:sa"),'Packet No.:',' ','Total No. of Present', ' ');
						$pdf->printBox1($arr, 'T');
						$pdf->Cell(0,2,'','LR',1);
						$arr = array('Staff Name', '','Total No. of Absent', ' ');
						$pdf->printBox1($arr, '');
						$pdf->Cell(0,2,'','LR',1);
						$arr = array('Signature', ' ', 'Page No.', $pdf->PageNo().'/{nb}');
						$pdf->printBox1($arr, 'B');
					} //end of if
				} //end of foreach
			} //end of if			
        }
		$pdf->output('Attendance - '.$subcode.'.pdf','I');
    }

    public function get_examdate($subcode, $sesid) {
        $data = array('SUBCODE' => $subcode, 'SESID' => $sesid);
        $this->db->select('DOE, SESS');
        $this->db->where($data);
        $query = $this->db->get('EXAM_TT');
        if($query->num_rows() ==1) {
            foreach($query->result() as $row) {
                return array('DOE' => $row->DOE, 'SESS' => $row->SESS);
            }
        }
        return;
    }
	
	public function is_markid_seq_generated($markid) {
		$this->db->select('SEQ');
		$this->db->where(array('MARKID' => $markid));
		$query = $this->db->get('MARKID_SEQ');
		if($query->num_rows() > 0 )
			return TRUE;
		else
			return FALSE;
	}
	
	public function foil_sheet_pdf($markid) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_no_wm.php');

        $mdetail = $this->markid_details($markid);
        $staff = $this->GM->get_staff_info($mdetail['staffid']);
        $subject = $this->GM->get_subject_info($mdetail['subcode']);
        $branch = $this->GM->get_branch_info($mdetail['brcode']);
        $campus = $this->GM->get_campus_info($branch['camcode']);
        $locked = $this->is_attlocked($markid, 'ESEM');
        
        $harray = array('S.No.', 'Register No.', 'Marks', 'Marks in Words', 'S.No.', 'Register No.', 'Marks', 'Marks in Words');
        $warray = array(12, 25, 18, 40, 12, 25, 18, 40);

        $pdf = new PDF();
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();

        $data = array('MARKID' => $markid);
        $this->db->select('REGNO, ESEM, TOTMARK');
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $query = $this->db->get('MARKS');
        if($query->num_rows() > 0) {
            $count = $query->num_rows();
            $carray = ''  ;
            $record = $query->result();
            $sno = 0;
            //$markInWords = new NumberFormatter("en", NumberFormatter::SPELLOUT);
            foreach($record as $r) {
                
                $carray[$sno][0] = $r->REGNO;
                $carray[$sno++][1] = $r->ESEM;

            }
            $i_max = $count / 50;
            $sno = 0;
            for($i = 0; $i< $i_max; $i++) {
                $pdf->AddPage();
                if(!$locked) {
                    $pdf->SetFont('Arial','B',50);
                    $pdf->SetTextColor(255,192,203);
                    $pdf->RotatedText(55,160,'P r o v i s i o n a l',45);
                    $pdf->SetTextColor(0,0,0);
                }
                $pdf->SetFont('Arial','B',15);
                $pdf->Cell(0,10,'FOIL SHEET',0,0,'C');
                $pdf->Ln(10);
                $arr = array('Campus', $campus['camname'], 'Session', 'NOV - 18');
                $pdf->printBox($arr, 'T');
                $arr = array('Branch', $branch['degshort'].' '.$branch['brname'].' - [ '.$mdetail['batch'].' ]', 'Mode', $branch['degmode']);
                $pdf->printBox($arr, '');
                $arr = array('Code', $mdetail['subcode'], 'Credit :', $subject['credit'], 'Semester', $mdetail['sem']);
                $pdf->printBox($arr, '');
                $arr = array('Course', $subject['subname'], 'Regulation', 2015);
                $pdf->printBox($arr, 'B');
                $pdf->SetWidths($warray);
                $pdf->SetFont('Arial','B',9.5);
                $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C'));
                $pdf->Row($harray);
                $pdf->SetAligns(array('C','C','C','L','C','C','C','L','C'));
                $pdf->SetFont('Arial','',9.5);
                
                for($j = $sno; $j < $sno + 25; $j++) {
                    $sno1 = $j < $count ? $j+1 : '';
                    $sno2 = $j+25 < $count ? $j+26 : '';
                    if(isset($carray[$j][0])) {
                        $col1 = $carray[$j][0];
                        $col2 = $carray[$j][1]; 
						if($col2=="A" || $col2=="P") $col3 = "ABSENT"; 
						else
                        $col3 = $col2 != NULL ? strtoupper($this->convert_number_to_words($col2)) : '';
                    } else {
                        $col1 = '';
                        $col2 = '';
                        $col3 = '';
                    }
                    if(isset($carray[$j+25][0])) {
                        $col4 = $carray[$j+25][0];
                        $col5 = $carray[$j+25][1];
						if($col5=="A" || $col5=="P") $col6 = "ABSENT"; 
						else
                        $col6 = $col5 != NULL ? strtoupper($this->convert_number_to_words($col5)) : '';
                    } else {
                        $col4 = '';
                        $col5 = '';
                        $col6 = '';
                    }
                    
                    $pdf->Row(array($sno1, $col1, $col2, $col3, $sno2, $col4, $col5, $col6));
                }
                $sno += 50;
                $pdf->SetY(-51);
                
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(18.5, 6, 'Staff Id ','','','L');
                $pdf->Write(6, ': ');
                $pdf->SetFont('Arial','',10);
                $pdf->MultiCell(0, 6, trim($staff['staffid']),0,'');
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(20, 6, 'Staff Name : ','','','L');
                $pdf->SetFont('Arial','',10);
                $pdf->MultiCell(0, 6, trim($staff['staffname']),0,'');
                
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(18.5, 6, 'Date','','','L');
                $pdf->Write(6, ': ');
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(0,6, date("d-M-Y h:i:sa"),0,0,'L');
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(-60, 6, 'Signature of the Course Instructor', '', 1, 'C');
            }
        }
        $pdf->output('Foil Sheet - '.$mdetail['subcode'].'.pdf','I');
    }
	
	public function convert_number_to_words($number) {		
		$dictionary  = array(
			0                   => 'zero',
			1                   => 'one',
			2                   => 'two',
			3                   => 'three',
			4                   => 'four',
			5                   => 'five',
			6                   => 'six',
			7                   => 'seven',
			8                   => 'eight',
			9                   => 'nine',
		);
		$word = '';
		if($number == 0) return "ZERO";
		while($number > 0) {
			$n = intval($number % 10);
			$word =  $dictionary[$n].' '.$word;
			$number = intval($number / 10);
		}
		return $word;
	}

    function get_exam_att_count($markid, $type) {
        $data = array('MARKID' => $markid, 'ATT' => $type);
        $this->db->select('COUNT(*) AS CNT');
        $this->db->where($data);
        $query = $this->db->get('EXAM_ATT');
        return $query->row()->CNT;
    }
	
	public function abs_cover_pdf($camcode, $depcode, $prgm, $subcode, $sesid) {
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_l_no_wm.php');

        
        $pdf = new PDF('L');
        //$pdf->AddPage();
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();
        $table = '';
        $j = 0;
		$count= 0;
		$warray = array(20, 48.5, 20, 48.5, 20, 50, 20, 50);		
		$harray = array('S.No', 'Register No.', 'S.No', 'Register No.', 'S.No', 'Register No.', 'S.No', 'Register No.');
		
        $total = 0;
        
        if($depcode[0] == 0) {
            $depcode = $this->SM->get_sems_dept($camcode, TRUE);
            $dep = 0;
        } else $dep = $depcode[0];
        //var_dump($depcode);
		$status=$this->CSO->is_locked_exam_att($camcode, $prgm, $dep, $subcode, $sesid);
        foreach($depcode as $d) {
            //var_dump($d);
            $brcode = $this->CSO->get_prgm_branch($d, $prgm);
            if($brcode === FALSE) continue;
            //var_dump($this->db->last_query());
			foreach($brcode as $bcode) {
				$count = $total = $pcount = 0 ;
				$data = array('SUBCODE' => $subcode, 'BRCODE' => $bcode, 'SESID' => $sesid);
				$this->db->select('MARKID');
				$this->db->where($data);
				//$this->db->where_in('BRCODE', $brcode);
				$this->db->order_by('MARKID');
				$query = $this->db->get('SUBLINK');
				if($query->num_rows()==0) continue;
				//var_dump($this->db->last_query());
				if($query->num_rows() > 0) {
					$record = $query->result();
					foreach ($record as $r ) {
						$markid = $r->MARKID;
                    
						$seq_status = $this->is_markid_seq_generated($markid);
						$mdetail = $this->markid_details($markid);
						//$staff = $this->GM->get_staff_info($mdetail['staffid']);
						$subject = $this->GM->get_subject_info($subcode);
						$branch = $this->GM->get_branch_info($mdetail['brcode']);
						$campus = $this->GM->get_campus_info($branch['camcode']);
						$exam_date = $this->get_examdate($subcode, $sesid);
                        
                        $pdf->AddPage();
                        if(!$status) {
                            $pdf->SetFont('Arial','B',50);
                            $pdf->SetTextColor(255,192,203);
                            $pdf->RotatedText(55,160,'P r o v i s i o n a l',45);
                            $pdf->SetTextColor(0,0,0);
                        }
                        $data = array('MARKID' => $markid);
                        $this->db->select('PACKET_NO');
                        $this->db->where($data);
                        $query = $this->db->get('EXAM_PACKET');
                        //var_dump($this->db->last_query());
                        if($query->num_rows() ==1 ) {
                            $packetno = $query->row()->PACKET_NO;
                        } else $packetno = '';


                        $pdf->SetFont('Arial','B',15);
                        $pdf->Cell(0,10,'Regular Examination Absentees List',0,0,'C');
                        $pdf->Ln(10);
                        $arr = array('Campus', $campus['camname'], 'Packet.No : ', $packetno, 'Session', 'APR - 18');
                        $pdf->printBox($arr, 'T');
                        $arr = array('Branch', $branch['degshort'].' '.$branch['brname'], 'Mode', $branch['degmode']);
                        $pdf->printBox($arr, '');   
                        $arr = array('Code', $mdetail['subcode'], 'Date :', $exam_date['DOE'].' '.$exam_date['SESS'], 'Semester', $mdetail['sem']);
                        $pdf->printBox($arr, '');
                        $arr = array('Course', $subject['subname'], 'Regulation', 2015);
                        $pdf->printBox($arr, 'B');
                        $pdf->SetWidths($warray);
                        $pdf->SetFont('Arial','B',10.5);
                        $pdf->SetAligns(array('C','C','C','C','C','C','C','C'));
                        $pdf->Row($harray);
                        $pdf->SetAligns(array('C','C','C','C','C','C','C','C'));
                        $pdf->SetFont('Arial','',10.5);
                        
                        $count = 0;
                        
                        $data = array('MARKID' => $markid, 'ATT' => 'P');
                        $this->db->select('REGNO');
                        $this->db->where($data);
                        $this->db->order_by('REGNO');
                        $query = $this->db->get('EXAM_ATT');
                        //var_dump($this->db->last_query());
                        if($query->num_rows() > 0) {
                            $pcount = $query->num_rows();
                        }     

						$data = array('MARKID' => $markid, 'ATT' => 'A');
						$this->db->select('REGNO');
						$this->db->where($data);
						$this->db->order_by('REGNO');
						$query = $this->db->get('EXAM_ATT');
						//var_dump($this->db->last_query());
						if($query->num_rows() > 0) {
							$count = $query->num_rows();
							$carray = ''  ;
							$record = $query->result();
							foreach($record as $r) {
								$carray[] = $r->REGNO;
							}
							$i_max = $count /60 ;
							$sno = 0;
                            for($i = 0; $i< $i_max; $i++) {
                                for($j = $sno; $j < $sno + 15; $j++) {
                                    $sno1 = $j < $count ? $j+1 : '';
                    
                                    if(isset($carray[$j])) {
                                        $col1 = $carray[$j];
                                    } else {
                                        $col1 = '';
                                    }
                                    $sno2 = $j+16 < $count ? $j+16 : '';
                                    if(isset($carray[$j+16])) {
                                        $col2 = $carray[$j+16];
                                    } else {
                                        $col2 = '';
                                    }
                                    $sno3 = $j+31 < $count ? $j+31 : '';
                                    if(isset($carray[$j+31])) {
                                        $col3 = $carray[$j+31];
                                    } else {
                                        $col3 = '';
                                    }
                                    $sno4 = $j+46 < $count ? $j+46 : '';
                                    if(isset($carray[$j+46])) {
                                        $col4 = $carray[$j+46];
                                    } else {
                                        $col4 = '';
                                    }
                    
                                    $pdf->Row(array($sno1, $col1,$sno2, $col2,$sno3, $col3,$sno4, $col4));
                                }
                                $sno += 60;
                            }
                        } //end of foreach
                         //end of if
                        if($count==0){
                            for($i=0;$i<15;$i++)
                                $pdf->Row(array('', '','','','', '','', ''));
                        }
                        $pdf->SetFont('Arial','B',11);
                        $pdf->Cell(38.5,6,'Total Presentees','LRB',0,'R');
                        $pdf->Cell(30,6,$pcount,'LRB',0,'C');
                        $pdf->SetFont('Arial','B',11);
                        $pdf->Cell(178.5,6,'Total Absentees','LRB',0,'R');
                        $pdf->Cell(30,6,$count,'LRB',0,'C');
                        $pdf->SetY(-27);
                        $pdf->SetFont('Arial','B', 9.5);
                        $pdf->Cell(10, 6, 'Date','','','L');
                        $pdf->Write(6, ': ');
                        $pdf->SetFont('Arial','',10);
                        $pdf->Cell(0,6, date("d-M-Y h:i:sa"),0,0,'L');
                        $pdf->SetFont('Arial','B', 9.5);
                        $pdf->Cell(-60, 6, 'Signature of the Chief Superintendant', '', 1, 'C');
						
					}
					
                } //end of if
                
            } //end of foreach
				
        }
        $pdf->output('Attendance - '.$subcode.'.pdf','I'); //end of if           
    }
	
	public function sub_packet_pdf($camcode, $depcode, $prgm, $subcode = null, $sesid) {
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_l_no_wm.php');
        
        $pdf = new PDF('L');
        
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();
        $j = 0;
        //$harray = array('S.No','Packet No.','Code','Course Name','Branch','Sem','Batch','Present','Absent' );
		$harray = array('S.No','Packet No.','Code','Course Name','Branch','Sem','Batch','Pre.','Abs.','NP', 'SA' );
        //$warray = array(15,25,30,67,65,20,20,20,15,15);
		$warray = array(15,25,30,67,65,10,14,14,12,12,13,13);
        $campus = $this->GM->get_campus_info($camcode);
        $exam_date = $this->get_examdate($subcode, $sesid);
		$arr1 = array('Campus', $campus['camname'], 'Session', 'APR - 18');
		$arr2 = array('Date :', $exam_date['DOE'].' '.$exam_date['SESS'], 'Regulation', 2015);
        $new_page = TRUE;
		$footer = FALSE;
		$sno=0;
		if($depcode[0] == 0) {
            $depcode = $this->SM->get_sems_dept($camcode, TRUE);
            $dep = 0;
        } else $dep = $depcode[0];
        //var_dump($depcode);
        $status=$this->CSO->is_locked_exam_att($camcode, $prgm, $dep, $subcode, $sesid);
        foreach($depcode as $d) {
            //var_dump($d);
            $brcode = $this->CSO->get_prgm_branch($d, $prgm);
            if($brcode === FALSE) continue;
            //var_dump($this->db->last_query());
            foreach($brcode as $bcode) {
                $count = $total = 0 ;
                $data = array('BRCODE' => $bcode);
                if($subcode !== null) $data['SUBCODE'] = $subcode;
                $this->db->select('MARKID');
                $this->db->where($data);
                //$this->db->where_in('BRCODE', $brcode);
                $this->db->order_by('MARKID');
                $query = $this->db->get('SUBLINK');
                if($query->num_rows()==0) continue;
                //var_dump($this->db->last_query());
                if($query->num_rows() > 0) {
                    $record = $query->result();
                    foreach ($record as $r ) {
                        $markid = $r->MARKID;
                        $j = 0;
                        $data = array('MARKID' => $markid);
                        $this->db->select('PACKET_NO, SUBCODE, BRCODE, SEM, BATCH');
                        $this->db->where($data);
                        $query = $this->db->get('EXAM_PACKET');
                        //var_dump($this->db->last_query());
                        if($query->num_rows() > 0) {
                            $record = $query->result();
                            $acount = $pcount = $npcount = $sacount = 0;
                            foreach($record as $r) {
                                $pno = $r->PACKET_NO;
                                $scode = $r->SUBCODE;
                                $subject = $this->GM->get_subject_info($scode);
                                $branch = $this->GM->get_branch_info($r->BRCODE);
                                $sem = $r->SEM;
                                $batch = $r->BATCH;

                                $pcount = $this->get_exam_att_count($markid, 'P');
                                $acount = $this->get_exam_att_count($markid, 'A');
                                $npcount = $this->get_exam_att_count($markid, 'NP');
                                $sacount = $this->get_exam_att_count($markid, 'SA');
								
                                if($new_page){
									$new_page = FALSE;
									$footer = TRUE;
									$pdf->AddPage();
									$pdf->SetFont('Arial','B',15);
									$pdf->Cell(0,10,'Daywise Bundle Information',0,0,'C');
									$pdf->Ln(10);
									$pdf->printBox($arr1, 'T');
									$pdf->printBox($arr2, '');
									$pdf->SetWidths($warray);
									$pdf->SetFont('Arial','B',10.5);
									$pdf->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C'));
									$pdf->Row($harray);
									$pdf->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C'));
									$pdf->SetFont('Arial','',10.5);
								}
                                //$pdf->Row(array(++$sno, $pno,$scode,$subject['subname'], $branch['degshort'].' '.$branch['brname'],$sem, $batch,$pcount,$acount));
								$pdf->Row(array(++$sno, $pno,$scode,$subject['subname'], $branch['degshort'].' '.$branch['brname'],$sem, $batch,$pcount,$acount,$npcount,$sacount));
								if(($sno%15==0 || $pdf->GetY()>170) && $footer){
									$footer = FALSE;
									$new_page = TRUE;
									$pdf->SetY(-40);
									$pdf->SetFont('Arial','B', 9.5);
									$pdf->Cell(0, 6, 'Signature of the Chief Superintendant', '', 0, 'L');
									$pdf->SetFont('Arial','B', 9.5);
									$pdf->Cell(0, 6, 'Signature of the Additional Controller of Examinations', '', 1, 'R');
									$pdf->Ln(5);
									$pdf->MultiCell(0,6,'Note: Pre. - Present; Abs - Absent; NP - Exam fees not paid; SA - Prevented; ','1','C');									
								}
                            }
							
                        }

                    }
                    
                } //end of if
				
            } //end of foreach
			
        }
		if($footer){
			$pdf->SetY(-40);
			$pdf->SetFont('Arial','B', 9.5);
			$pdf->Cell(0, 6, 'Signature of the Chief Superintendant', '', 0, 'L');
			$pdf->SetFont('Arial','B', 9.5);
			$pdf->Cell(0, 6, 'Signature of the Additional Controller of Examinations', '', 1, 'R');
			$pdf->Ln(5);
			$pdf->MultiCell(0,6,'Note: Pre. - Present; Abs - Absent; NP - Exam fees not paid; SA - Prevented; ','1','C');
		}
		
		$pdf->output('Packet - '.$subcode.'.pdf','I'); //end of if           
    }
	
	
	public function day_packet_pdf($camcode, $depcode, $doe, $prgm, $sesid) {
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_l_no_wm.php');
        
        $pdf = new PDF('L');
        
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();
        $count = 1;
		$harray = array('S.No','Packet No.','Code','Course Name','Branch','Sem','Batch','Pre.','Abs.','NP', 'SA' );
		$warray = array(15,25,30,67,65,10,14,14,12,12,13,13);
        $campus = $this->GM->get_campus_info($camcode);
		$arr1 = array('Campus', $campus['camname'], 'Session', 'APR - 18');
        $new_page = TRUE;
		$footer = FALSE;
		$old_doe = '';
		$sno=1;
		$data = array('SESID' => $sesid);
		if($doe!==null) $data['DOE'] = $doe;
        $this->db->select('SUBCODE, DOE');
        $this->db->where($data);
        $this->db->order_by('DOE, SUBCODE');
        $query = $this->db->get('EXAM_TT');
		if($query->num_rows() > 0) {
			foreach ($query->result() as $row ) {
				if($old_doe != $row->DOE) {
                    $sno=1;
					$new_page = TRUE;
					$old_doe = $row->DOE;
					if($footer){
						$footer = FALSE;
						$pdf->SetY(-35);
						$pdf->SetFont('Arial','B', 9.5);
						$pdf->Cell(0, 6, 'Signature of the Chief Superintendant', '', 0, 'L');
						$pdf->SetFont('Arial','B', 9.5);
						$pdf->Cell(0, 6, 'Signature of the Additional Controller of Examinations', '', 1, 'R');
						$pdf->Ln(2);
						$pdf->MultiCell(0,6,'Note: Pre. - Present; Abs - Absent; NP - Exam fees not paid; SA - Prevented; ','1','C');
					}
				}
				$arr2 = array('Date :', $row->DOE, 'Regulation', 2015);
				$data = array('CAMCODE' => $camcode, 'SUBCODE' => $row->SUBCODE, 'SESID' => $sesid, 'PRGM' => $prgm);
                if($depcode!==0) $data['DEPCODE'] = $depcode; 
				$this->db->select('MARKID, PACKET_NO, BRCODE, SEM, BATCH');
                $this->db->where($data);
				$this->db->order_by('PACKET_NO');
                $query = $this->db->get('EXAM_PACKET');
				//var_dump($this->db->last_query());
				//return;
                if($query->num_rows() > 0) {
					$record = $query->result();
					$acount = $pcount = $npcount = $sacount = 0;
                    foreach($record as $r) {
                        $pno = $r->PACKET_NO;
                        $scode = $row->SUBCODE;
                        $subject = $this->GM->get_subject_info($scode);
                        $branch = $this->GM->get_branch_info($r->BRCODE);
                        $sem = $r->SEM;
                        $batch = $r->BATCH;

                        $pcount = $this->get_exam_att_count($r->MARKID, 'P');
                        $acount = $this->get_exam_att_count($r->MARKID, 'A');
                        $npcount = $this->get_exam_att_count($r->MARKID, 'NP');
                        $sacount = $this->get_exam_att_count($r->MARKID, 'SA');

                        if($new_page){
							$new_page = FALSE;
							$footer = TRUE;
							$pdf->AddPage();
							$pdf->SetFont('Arial','B',15);
							$pdf->Cell(0,10,'Daywise Bundle Information',0,0,'C');
							$pdf->Ln(10);
							$pdf->printBox($arr1, 'T');
							$pdf->printBox($arr2, '');
							$pdf->SetWidths($warray);
							$pdf->SetFont('Arial','B',10.5);
							$pdf->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C'));
							$pdf->Row($harray);
							$pdf->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C'));
							$pdf->SetFont('Arial','',10.5);
						}
						$pdf->Row(array($sno++, $pno,$scode,$subject['subname'], $branch['degshort'].' '.$branch['brname'],$sem, $batch,$pcount,$acount,$npcount,$sacount));
						
						if(($count==11|| $pdf->GetY()>150) && $footer){
							$count=1;
							$footer = FALSE;
							$new_page = TRUE;
							$pdf->SetY(-35);
							$pdf->SetFont('Arial','B', 9.5);
							$pdf->Cell(0, 6, 'Signature of the Chief Superintendant', '', 0, 'L');
							$pdf->SetFont('Arial','B', 9.5);
							$pdf->Cell(0, 6, 'Signature of the Additional Controller of Examinations', '', 1, 'R');
							$pdf->Ln(2);
							$pdf->MultiCell(0,6,'Note: Pre. - Present; Abs - Absent; NP - Exam fees not paid; SA - Prevented; ','1','C');
                        }//end of if
						$count++;
                    }//end of foreach
                } //end of if
            } //end of foreach
        }
		if($footer){
			$pdf->SetY(-35);
			$pdf->SetFont('Arial','B', 9.5);
			$pdf->Cell(0, 6, 'Signature of the Chief Superintendant', '', 0, 'L');
			$pdf->SetFont('Arial','B', 9.5);
			$pdf->Cell(0, 6, 'Signature of the Additional Controller of Examinations', '', 1, 'R');
			$pdf->Ln(2);
			$pdf->MultiCell(0,6,'Note: Pre. - Present; Abs - Absent; NP - Exam fees not paid; SA - Prevented; ','1','C');
		}
		
		$pdf->output('Packet - '.$doe.'.pdf','I'); //end of if           
    }

    public function prevention_cat_pdf($depcode, $catid, $sesid) {
        $this->load->model('CSO_model', 'CSO');
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_no_wm.php');

        $dept = $this->GM->get_dept_info($depcode);
        $campus = $this->GM->get_campus_info($dept['camcode']);
    
        $count = $this->PM->get_prevention_cat_count($depcode, $catid, $sesid);
        if($count['pcount'] == $count['tcount']) $locked = TRUE;
        else $locked = FALSE;

        $pdf = new PDF();
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();

        $page_title = 'Department-wise Prevented List';
        $header_arr1 = array('Campus', $campus['camname'], 'Session', 'APR - 18');
        $header_dept = array('Department', $dept['depname'], 'Date', date('d-m-Y'));
        $harray = array('S.No.', 'Register No.', 'Name of the Student', 'Attendance %', 'Remarks');
        $warray = array(20, 40, 80, 30, 20);

        $first_page = $new_page = TRUE; $footer = FALSE;

        $this->db->select('PRGM, DEGSHORT, DEGMODE, START_SEM, END_SEM, ORD');
        $this->db->where(array('CATID' => $catid));
        $this->db->order_by('ORD');
        $query = $this->db->get('PREVENTED_CAT');
        if($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
            //Getting unique markid of student belong to particular category and depcode
                $data = array('DEPCODE' => $depcode, 'PRGM' => $row->PRGM, 'SUBLINK_SEM >=' => $row->START_SEM, 'STATUS' => 1, 'FLAG !=' => 'D', 'SESID' => $sesid);
                if($row->DEGSHORT != 'ALL') $data['SUBLINK_DEGSHORT'] = $row->DEGSHORT;
                if($row->DEGMODE != 'ALL') $data['DEGMODE'] = $row->DEGMODE;
                if($row->END_SEM != 0) $data['SUBLINK_SEM <='] = $row->END_SEM;

                $type = 'R';
                if($type == 'A') $flag = array('R','RA','RE','SA');
                elseif($type == 'R') $flag = array('R','E','O');
                else $flag = array('RA','RE','SA');
                $old_brcode = 0;

                $sq1 = $this->db->select('DISTINCT (BRCODE), SUBCODE')->where($data)->where_in('FLAG', $flag)->order_by('BRCODE, SUBCODE')->get('REGISTER');
                if($sq1->num_rows() > 0) {
                    foreach($sq1->result() as $r1) {                        
                        if(!$this->PM->is_prevented_category($r1->BRCODE, $r1->SUBCODE)) continue;
                        if($r1->BRCODE != $old_brcode) {
                            $old_brcode = $r1->BRCODE;
                            $sub_header = TRUE;
                        }
                        $sem = $this->get_sub_sem($r1->SUBCODE, $r1->BRCODE, $sesid);
                        $subject = $this->GM->get_subject_info($r1->SUBCODE);
                        $branch = $this->GM->get_branch_info($r1->BRCODE);
                        $header_arr2 = array('Branch', $branch['degshort'].' '.$branch['brname'], 'Mode', $branch['degmode']);
                        $header_arr3 = array('Code', $r1->SUBCODE, 'Credit:', $subject['credit'], 'Semester', $sem);
                        $header_arr4 = array('Course', $subject['subname'], 'Regulation', 2015);

                        $data = array('DEPCODE' => $depcode, 'PRGM' => $row->PRGM, 'SUBLINK_SEM >=' => $row->START_SEM, 'STATUS' => 1, 'FLAG !=' => 'D', 'SESID' => $sesid);
                        if($row->DEGSHORT != 'ALL') $data['SUBLINK_DEGSHORT'] = $row->DEGSHORT;
                        if($row->DEGMODE != 'ALL') $data['DEGMODE'] = $row->DEGMODE;
                        if($row->END_SEM != 0) $data['SUBLINK_SEM <='] = $row->END_SEM;
                        $data['BRCODE'] = $r1->BRCODE;
                        $data['SUBCODE'] = $r1->SUBCODE;

                        $this->db->select('DISTINCT (MARKID)');
                        $this->db->where($data);
                        $query = $this->db->get('REGISTER');
                        if($query->num_rows() < 1) continue;

                        $markid = '';
                        foreach($query->result() as $r2) {
                            $markid[] = $r2->MARKID;
                        }

                        $data = array('BRCODE' => $r1->BRCODE, 'STATUS <' => 2, 'SESID' => $sesid);

                        $this->db->select('REGNO, ATTPER');
                        $this->db->where($data);
                        $this->db->where_in('MARKID', $markid);
                        $this->db->order_by('REGNO');
                        $query = $this->db->get('PREVENTED_STUDENT');
                        $header = TRUE;
                        if($query->num_rows() > 0) {
                            $sno = 1;
                            foreach ($query->result() as $r3) {
                                $end = $pdf->GetY() > 245 ? TRUE : FALSE;

                                if($end && $footer) {
                                    $pdf->SetY(-28);
                                    $pdf->SetFont('Arial','B', 9.5);
                                    $pdf->Cell(0, 6, 'Signature of the HOD','','','L'); 
                                    $pdf->Cell(0, 6, 'Signature of the ACOE', '', 1, 'R');
                                    $footer = FALSE; $new_page = TRUE; 
                                }
                                if($new_page) {
                                    $pdf->AddPage();
                                    if(!$locked) {
                                        $pdf->SetFont('Arial','B',50);
                                        $pdf->SetTextColor(255,192,203);
                                        $pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
                                        $pdf->SetTextColor(0,0,0);
                                    }
                                    //if($first_page) {
                                        $pdf->SetFont('Arial','B',15);
                                        $pdf->Cell(0,10,$page_title,0,0,'C');
                                        $pdf->Ln(10);
                                        $pdf->printBox($header_arr1, 'T');
                                        $pdf->printBox($header_dept, 'TB');
                                        $first_page = FALSE; 
                                    //}                                    
                                    $new_page = FALSE; $footer = TRUE; $header = TRUE; $sub_header = TRUE;
                                }
                                if($header) {
                                    if($sub_header) {
                                        $pdf->printBox($header_arr2, 'T');
                                        $sub_header = FALSE;
                                    }
                                    $pdf->printBox($header_arr3, 'T');
                                    $pdf->printBox($header_arr4, 'B');
                                    $pdf->SetWidths($warray);
                                    $pdf->SetFont('Arial','B',9.5);
                                    $pdf->SetAligns(array('C','C','C','C', 'C'));
                                    $pdf->Row($harray);
                                    $pdf->SetFont('Arial','',9.5);
                                    $pdf->SetAligns(array('C','C','L','C', 'C'));
                                    $header = FALSE;
                                }

                                $student = $this->GM->get_student_info($r3->REGNO);
                                if($r3->ATTPER < 75) $pstatus = 'P';
                                else $pstatus = '';
                                $pdf->Row(array($sno++, $r3->REGNO, $student['name'], $r3->ATTPER, $pstatus));
                            }
                        }
                    }
                }
            }
        }
        if($first_page) {            
            $pdf->AddPage();
            if(!$locked) {
                $pdf->SetFont('Arial','B',50);
                $pdf->SetTextColor(255,192,203);
                $pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
                $pdf->SetTextColor(0,0,0);
            }
            $pdf->SetFont('Arial','B',15);
            $pdf->Cell(0,10,$page_title,0,0,'C');
            $pdf->Ln(10);
            $pdf->printBox($header_arr1, 'T');
            $pdf->printBox($header_dept, 'TB');
            $footer = TRUE;
        }
        if($pdf->GetY() < 250 && $footer) {
            $pdf->SetY(-28);
            $pdf->SetFont('Arial','B', 9.5);
            $pdf->Cell(0, 6, 'Signature of the HOD','','','L'); 
            $pdf->Cell(0, 6, 'Signature of the ACOE', '', 1, 'R');
        }

        $pdf->output('Cumulative Prevention'.'.pdf','I');
    }

    public function is_grade_range_generated($markid) {
        $mdetail = $this->markid_details($markid);
        $data = array('STAFFID' => $mdetail['staffid'], 'SUBCODE' => $mdetail['subcode'], 'SESID' => $mdetail['sesid']);
        $cnt = $this->db->select('SUBCODE')->where($data)->get('GRADE_RANGE')->num_rows();
        if($cnt > 0) return TRUE;
        else return FALSE;
    }

    public function compute_grade_range($markid) {
        $mdetail = $this->markid_details($markid);
        if($mdetail['sesid'] != $this->sesid) {
            return array('result' => FALSE, 'msg' => 'Session Locked! Cannot generate grade range.');
        }

        if($this->is_grade_range_generated($markid)) {
            return array('result' => FALSE, 'msg' => 'Grade range already generated');
        }

        $data = array('SUBCODE' => $mdetail['subcode'], 'STAFFID' => $mdetail['staffid'], 'SESID' => $mdetail['sesid']);
        $this->db->select('MARKID');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');
        if($query->num_rows() == 0) {
            $this->db->select('MARKID');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK_N16');
        }
        if($query->num_rows() > 0) {
            /**prerequiste**/
            foreach($query->result() as $row) {
                /**checking if student has registered **/
                $data = array('MARKID' => $row->MARKID, 'SESID' => $mdetail['sesid'], 'FLAG !=' => 'D');
                $cnt = $this->db->select('COUNT(*) AS CNT')->where($data)->get('REGISTER')->row()->CNT;
                if($cnt == 0) continue;

                /**check if end sem is locked **/
                if(!$this->is_attlocked($row->MARKID, 'ESEM'))
                    return array('result' => FALSE, 'msg' => 'Lock End Sem marks of all batches of '.$mdetail['subcode'].' to compute result');
            }

            $tot_stud = $present_stud = $prev_stud = $abs_stud = $na_stud = $passcount = 0;
            $passtotmark = $totmark = $passmean = $mean = $num = $variance = $sd  = 0;
            /**calculating total mark and no of students**/
                foreach($query->result() as $row) {
                $data = array('MARKID' => $row->MARKID, 'SESID' => $mdetail['sesid'], 'FLAG !=' => 'D');
                $cnt = $this->db->select('COUNT(*) AS CNT')->where($data)->get('REGISTER')->row()->CNT;
                if($cnt == 0) continue; 
            
                /**calculating total mark = internal + esem **/
                $this->calculate_totmark($row->MARKID);
                
                $data = array('MARKID' => $row->MARKID);
                $this->db->select('REGNO, ESEM, TOTMARK');
                $this->db->where($data);
                $sq1 = $this->db->get('MARKS');
                if($sq1->num_rows() > 0) {
                    foreach($sq1->result() as $r) {
                        $tot_stud++;
                        
                        if($r->TOTMARK >=50) {
                            $passtotmark += $r->TOTMARK;
                            $passcount++;    
                        }
                        if($r->ESEM == 'P' || $this->PM->is_prevented($r->REGNO, $row->MARKID, $mdetail['sesid'])) {
                            $prev_stud++;
                            continue;
                        } elseif($r->ESEM == 'A') {
                            $abs_stud++;
                            continue;
                        } elseif($r->ESEM == 'RA') {
                            $na_stud++;
                            continue;
                            
                        } 
                      
                         $present_stud++;
                         $totmark += $r->TOTMARK;
                    }
                } else {
                    return array('result' => FALSE, 'msg' => 'Student marks not found');
                }                
            }

            if($present_stud == 0)  {
                //return array('result' => FALSE, 'msg' => 'Present Student is 0');
                $mean = $variance = $sd = 0;
            } else {
                //calculating mean
                $mean = round($totmark / $present_stud, 2, PHP_ROUND_HALF_UP);

                /**calculating population standard deviation and population variance**/
                
                 if($passcount == 0)  {
                //return array('result' => FALSE, 'msg' => 'Present Student is 0');
                 $passmean = 0;
            } else {
                //calculating passmean
                $passmean = round($passtotmark / $passcount, 2, PHP_ROUND_HALF_UP);

              
                foreach($query->result() as $row) {
                    $data = array('MARKID' => $row->MARKID);
                    $this->db->select('REGNO, ESEM,TOTMARK');
                    $this->db->where($data);
                    $sq2 = $this->db->get('MARKS');
                    if($sq2->num_rows() > 0) {
                        foreach($sq2->result() as $r) {
                            if($r->ESEM == 'A'|| $r->ESEM == 'P' || $r->ESEM == 'RA' || $this->PM->is_prevented($r->REGNO, $row->MARKID, $mdetail['sesid']))
                                continue;
                            $num += pow($r->TOTMARK - $mean, 2);
                        }
                    }
                }
                
                $variance = round ($num / $present_stud, 2, PHP_ROUND_HALF_UP);
                $sd = round (sqrt($variance), 2, PHP_ROUND_HALF_UP);
            }

            $record['STAFFID'] = $mdetail['staffid']; 
            $record['SUBCODE'] = $mdetail['subcode'];
            $record['MEAN'] = $mean;
            $record['VARIANCE'] = $variance;
            $record['SD'] = $sd;
            $record['TOT_STUD'] = $tot_stud;
            $record['PRESENT_STUD'] = $present_stud;
            $record['PREV_STUD'] = $prev_stud;
            $record['ABS_STUD'] = $abs_stud;
            $record['NA_STUD'] = $na_stud;
            $record['TOTMARK'] = $totmark;
            $record['SESID'] = $mdetail['sesid'];
            $record['STATUS'] = 1;  
            $record['PASSMEAN'] = $passmean;
            //echo $passcount;
         
            

            if($present_stud >= 30) {
                //$record['O'] = round ($mean + 1 * $sd, 0, PHP_ROUND_HALF_UP);                
               // $record['AP'] = round ($mean + 0.5 * $sd, 0, PHP_ROUND_HALF_UP);
               // $record['A'] = round ($mean - 0.5 * $sd, 0, PHP_ROUND_HALF_UP);
               // $record['BP'] = round ($mean - 1 * $sd, 0, PHP_ROUND_HALF_UP);
                //$b1 = round ($mean - 1.5 * $sd, 0, PHP_ROUND_HALF_UP);
               // $b2 = round ($mean * 0.6, 0, PHP_ROUND_HALF_UP);
				//$record['BO'] = $b1;
               // $record['BT'] = $b2;
                //$record['B'] = $b1 < $b2 ? $b1 : $b2;
               // $record['B'] = 50;
                
                //$b1 = round ($mean - 1.5 * $sd, 0, PHP_ROUND_HALF_UP);
               // $b2 = round ($mean * 0.6, 0, PHP_ROUND_HALF_UP);
				//$record['BO'] = $b1;
               // $record['BT'] = $b2;
                //$record['B'] = $b1 < $b2 ? $b1 : $b2;

                $record['O']  = 90;                
                $record['AP'] =80;
                $record['A'] = 70;
                $record['BP'] =60;
                $record['B'] = 50;
            } else {
               // $record['O'] = round ($mean + 1.29 * $sd, 0, PHP_ROUND_HALF_UP);                
               // $record['AP'] = round ($mean + 0.53 * $sd, 0, PHP_ROUND_HALF_UP);
               // $record['A'] = round ($mean - 0.39 * $sd, 0, PHP_ROUND_HALF_UP);
               // $record['BP'] = round ($mean - 1.04 * $sd, 0, PHP_ROUND_HALF_UP);
                //$b1 = round ($mean - 1.64 * $sd, 0, PHP_ROUND_HALF_UP);
                //$b2 = round ($mean * 0.6, 0, PHP_ROUND_HALF_UP);
				//$record['BO'] = $b1;
                //$record['BT'] = $b2;
                //$record['B'] = $b1 < $b2 ? $b1 : $b2;
                $record['O']  = 90;                
                $record['AP'] =80;
                $record['A'] = 70;
                $record['BP'] =60;
                $record['B'] = 50;
                
            }             
            /*if($present_stud >= 10) {}
            else {
                $record['O'] = 90;
                $record['AP'] = 80;
                $record['A'] = 70;
                $record['BP'] = 60;
                $record['B'] = 50;
                $record['BO'] = 0; 
                $record['BT'] = 0;
            }*/

           

            //if($record['O'] > 100) $record['O'] = 100;
            //if($record['AP'] > 100) $record['AP'] = 100;
            //if($record['A'] > 100) $record['A'] = 100;
            //if($record['BP'] > 100) $record['BP'] = 100;
            //if($record['B'] > 100) $record['B'] = 100;
            //if($record['PASS'] > 100) $record['PASS'] = 100;

            //if($record['O'] < 0) $record['O'] = 0;
            //if($record['AP'] < 0) $record['AP'] = 0;
            //if($record['A'] < 0) $record['A'] = 0;
            //if($record['BP'] < 0) $record['BP'] = 0;
           // if($record['B'] < 0) $record['B'] = 0;
           // if($record['PASS'] < 0) $record['PASS'] = 0;

            $record['GDATE'] = date('d-M-Y h:i:sa');
            $this->db->insert('GRADE_RANGE', $record);
            return array('result' => TRUE, 'msg' => 'Grade range generated');
        }
        return array('result' => FALSE, 'msg' => 'Grade range generation failed');
    }
    }
    public function get_mean($markid) {
        $mdetail = $this->markid_details($markid);

        $data = array('STAFFID' => $mdetail['staffid'], 'SUBCODE' => $mdetail['subcode'], 'SESID' => $mdetail['sesid']);
        $query = $this->db->select('MEAN, SD, O, AP, A, BP, B, PASS, PASSMEAN')->where($data)->get('GRADE_RANGE');
        if($query->num_rows() > 0) {
            $row = $query->row();
            return array('result' => TRUE, 'mean' => $row->MEAN,'sd' => $row->SD, 'o' => $row->O, 'ap' => $row->AP, 'a' => $row->A, 'bp' => $row->BP, 'b' => $row->B, 'pass' => $row->PASS, 'passmean' => $row->PASSMEAN,);
        }
        return array('result' => FALSE, 'mean' => '', 'sd' => '', 'o' => '', 'ap' => '', 'a' => '', 'bp' => '', 'b' => '', 'pass' => '','passmean' =>'');
    }

    public function calculate_result($markid, $generate = TRUE) {
        $mdetail = $this->markid_details($markid);
        if($mdetail['sesid'] != $this->sesid) {
            return array('result' => 'false', 'msg' => 'Session Locked! Cannot compute result');
        }

        if($generate) {
            $grade = $this->compute_grade_range($markid);
            if(!$grade['result'])
                return array('result' => FALSE, 'msg' => $grade['msg']);
        }

        if(!$this->is_grade_range_generated($markid)) {
            return array('result' => FALSE, 'msg' => 'Compute grade range first!');
        }

        $mean = $this->get_mean($markid);
        if($mean['result'] == FALSE)
            return array('result' => FALSE, 'msg' => 'Failed getting grade range!');

        $data = array('SUBCODE' => $mdetail['subcode'], 'STAFFID' => $mdetail['staffid'], 'SESID' => $mdetail['sesid']);
        $this->db->select('MARKID');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK_N16');
        if($query->num_rows() > 0) {
            foreach($query->result() as $row) {
                /**checking if student has registered **/
                $data = array('MARKID' => $row->MARKID, 'SESID' => $mdetail['sesid'], 'FLAG !=' => 'D');
                $cnt = $this->db->select('COUNT(*) AS CNT')->where($data)->get('REGISTER')->row()->CNT;
                if($cnt == 0) continue;

                if(!$generate) {
                	/**if custom grade - calculating totmark **/
                	$this->calculate_totmark($row->MARKID);
                }

                $data = array('MARKID' => $row->MARKID);
                $this->db->select('REGNO, ESEM, TOTMARK');
                $this->db->where($data);
                $query = $this->db->get('MARKS');
                if($query->num_rows() > 0) {
                    $o = $ap = $a = $bp = $b = $ra = $sa = $ab = 0;
                    foreach($query->result() as $r) {
                        if($r->ESEM == 'P' || $this->PM->is_prevented($r->REGNO, $row->MARKID, $mdetail['sesid'])){
                        	$sa++;
                            $udata = array('RESULT' => 'SA');
                        }
                        elseif($r->ESEM == 'A') {
                        	$ab++;
                            $udata = array('RESULT' => 'AB');
                        }
                        elseif($r->ESEM == 'RA') {
                            $ra++;
                            $udata = array('RESULT' => 'RA');
                        }
                        elseif($r->TOTMARK >= $mean['o']) {
                            $o++;
                            $udata = array('RESULT' => 'O');
                        }
                        elseif($r->TOTMARK >= $mean['ap']) {
                            if($r->TOTMARK + 1  == $mean['o']) {
                                $o++;
                                $udata = array('RESULT' => 'O', 'TOTMARK' => $mean['o']);

                            }
                            else {
                                $ap++;
                                $udata = array('RESULT' => 'A+');
                            }
                        }
                        elseif($r->TOTMARK >= $mean['a']) {
                            $a++;
                            $udata = array('RESULT' => 'A');
                        }
                        elseif($r->TOTMARK >= $mean['bp']) {
                            $bp++;
                            $udata = array('RESULT' => 'B+');
                        }
                        elseif($r->TOTMARK >= 50) {
                            $b++;
                            $udata = array('RESULT' => 'B');
                        }
                        else {
                            if($r->TOTMARK + 1 == 50) {
                                $b++;
                                $udata = array('RESULT' => 'B', 'TOTMARK' => $mean['b']);
                            }
                            else {
                            	$ra++;
                                $udata = array('RESULT' => 'RA');
                            }
                        }

                        $wdata = array('MARKID' => $row->MARKID, 'REGNO' => $r->REGNO);
                        $this->db->where($wdata);
                        $this->db->update('MARKS', $udata);
                    }
                    $udata = array('O' => $o, 'AP' => $ap, 'A' => $a, 'BP' => $bp, 'B' => $b, 'RA' => $ra, 'SA' => $sa, 'AB' => $ab);
                    $wdata = array('MARKID' => $row->MARKID);
                    $this->db->set($udata);
                    $this->db->where($wdata);
                    $this->db->update('GRADE');
                }
            }
            return array('result' => TRUE, 'msg' => 'Result calculated');
        } else {
            return array('result' => FALSE, 'msg' => 'Failed calculating result');
        }
    }

    function get_sublink_campus($sesid) {
        $data = array('SESID' => $sesid);
        $this->db->select('DISTINCT(CAMCODE)');
        $this->db->where($data);
        $this->db->order_by('CAMCODE');
        $query = $this->db->get('SUBLINK');
        
        $option = '<option></option>';
        if($query->num_rows() > 0 ) {
            foreach($query->result() as $row) {
                $campus = $this->GM->get_campus_info($row->CAMCODE);
                $option .= '<option value="'.$row->CAMCODE.'">'.$campus['camshort'].'</option>';
            }
        }
        return $option;
    }

    function get_sublink_dept($camcode, $sesid) {
        $data = array('CAMCODE' => $camcode, 'SESID' => $sesid);
        $this->db->select('DISTINCT(DEPCODE)');
        $this->db->where($data);
        $this->db->order_by('DEPCODE');
        $query = $this->db->get('SUBLINK');
        
        $option = '<option></option>';
        if($query->num_rows() > 0 ) {
            foreach($query->result() as $row) {
                $dept = $this->GM->get_dept_info($row->DEPCODE);
                $option .= '<option value="'.$row->DEPCODE.'">'.$dept['depname'].'</option>';
            }
        }
        return $option;
    }

    function get_sublink_prgm($depcode, $sesid) {
        $data = array('DEPCODE' => $depcode, 'SESID' => $sesid);
        $this->db->select('DISTINCT(PRGM)');
        $this->db->where($data);
        $this->db->order_by('PRGM');
        $query = $this->db->get('SUBLINK');
        
        $option = '<option></option>';
        if($query->num_rows() > 0 ) {
            foreach($query->result() as $row) {
                $option .= '<option value="'.$row->PRGM.'">'.$row->PRGM.'</option>';
            }
        }
        return $option;
    }

    function get_sublink_dept2($camcode, $prgm, $sesid) {
        $data = array('CAMCODE' => $camcode, 'PRGM' => $prgm, 'SESID' => $sesid);
        $this->db->select('DISTINCT(DEPCODE)');
        $this->db->where($data);
        $this->db->order_by('DEPCODE');
        $query = $this->db->get('SUBLINK_N16');
//        if($query->num_rows() == 0) {
//            $this->db->select('DISTINCT(DEPCODE)');
//            $this->db->where($data);
//            $this->db->order_by('DEPCODE');
//            $query = $this->db->get('SUBLINK_N16');
//        }
        
        $option = '<option></option>';
        if($query->num_rows() > 0 ) {
            foreach($query->result() as $row) {
                $dept = $this->GM->get_dept_info($row->DEPCODE);
                //print_r($dept);
                $option .= '<option value="'.$row->DEPCODE.'">'.$dept['depname'].'</option>';
            }
        }
        return $option;
    }

    function get_sublink_prgm2($camcode, $sesid) {
        $data = array('CAMCODE' => $camcode, 'SESID' => $sesid);
        $this->db->select('DISTINCT(PRGM)');
        $this->db->where($data);
        $this->db->order_by('PRGM');
        $query = $this->db->get('SUBLINK');
        if($query->num_rows() == 0) {
            $this->db->select('DISTINCT(PRGM)');
            $this->db->where($data);
            $this->db->order_by('PRGM');
            $query = $this->db->get('SUBLINK_N16');
        }
        
        $option = '<option></option>';
        if($query->num_rows() > 0 ) {
            foreach($query->result() as $row) {
                $option .= '<option value="'.$row->PRGM.'">'.$row->PRGM.'</option>';
            }
        }
        return $option;
    }

    function get_sublink_branch($depcode, $sesid, $prgm = null) {
        /*$this->load->model('CSO_model', 'CSO');
        $brcode = $this->CSO->get_prgm_branch($depcode);*/

        $data = array('DEPCODE' => $depcode);
        if($prgm !== null) $data['PRGM'] = $prgm;
        
        $this->db->select('DISTINCT(BRCODE)');
        $this->db->where($data);
        $this->db->order_by('BRCODE');
        $query = $this->db->get('SUBLINK');
        if($query->num_rows() == 0) {
            $this->db->select('DISTINCT(BRCODE)');
            $this->db->where($data);    
            $this->db->order_by('BRCODE');
            $query = $this->db->get('SUBLINK_N16');
        }
        $option = '<option></option>';
        if($query->num_rows() > 0 ) {
            foreach($query->result() as $row) {
                $branch = $this->GM->get_branch_info($row->BRCODE);
                $option .= '<option value="'.$row->BRCODE.'">'.$branch['degshort'].' '.$branch['brname'].' - '.$branch['degmode'].'</option>';
            }
        }
        return $option;
    }
    
    function get_sublink_sem($brcode, $sesid) {
        $data = array ('BRCODE' => $brcode, 'SESID' => $sesid);
        $this->db->select('DISTINCT(SEM)');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');
        if($query->num_rows() == 0) {
            $this->db->select('DISTINCT(SEM)');
            $this->db->where($data);    
            $this->db->order_by('BRCODE');
            $query = $this->db->get('SUBLINK_N16');
        }

        $option = "<option></option>";
        foreach($query->result() as $r) {
            $option .= '<option value = "'.$r->SEM.'">'.$r->SEM.'</option>';
        }
        return $option;
    }
    
    function get_register_sem($brcode, $sesid) {
        $data = array ('BRCODE' => $brcode, 'SESID' => $sesid);
        $this->db->select('DISTINCT(SEM)');
        $this->db->where($data);
        $query = $this->db->get('REGISTER');
        $record =  $query->result();

        $option = "<option></option>";
        foreach($record as $r) {
            $option .= '<option value = "'.$r->SEM.'">'.$r->SEM.'</option>';
        }
        return $option;
    }

    public function get_report_status($brcode, $sem, $sesid) {
        $data = array('BRCODE' => $brcode, 'SUBLINK_SEM' => $sem, 'SESID' => $sesid);
        $this->db->select('DISTINCT (SUBCODE), MARKID, STAFFID');
        $this->db->where($data);
        $this->db->order_by('SUBCODE, MARKID');
        $query = $this->db->get('REGISTER');
        if($query->num_rows() > 0) {
            $table = '';
            $i = 0;
            foreach($query->result() as $r) {
                $j = 0;
                $status = $this->get_mark_status($r->MARKID);
                $subject = $this->GM->get_subject_info($r->SUBCODE);
                $staff = $this->GM->get_staff_info($r->STAFFID);
                $estatus = $this->is_attlocked($r->MARKID, 'esem');
                $atttlock = $this->get_attlock_time($r->MARKID, 'result');
				
				// add by kulothunga to find the grade status
				$resstatus = $this->is_attlocked($r->MARKID, 'result');
                $table[$i][$j++] = $r->SUBCODE;
                $table[$i][$j++] = $subject['subname'];
                $table[$i][$j++] = $staff['staffname'];
                $table[$i][$j++] = $staff['dept'];
                if($status < 1)
                    $table[$i][$j++] = '<span class="label cell-green">NL</span>';
                else
                    $table[$i][$j++] = '<span class="label cell-red">L</span>';
                if($status < 2)
                    $table[$i][$j++] = '<span class="label cell-green">NL</span>';
                else
                    $table[$i][$j++] = '<span class="label cell-red">L</span>';
                if($this->PM->is_locked($r->MARKID)) {
                    $table[$i][$j++] = '<span class="label cell-red">L</span>';
                }
                else
                    $table[$i][$j++] = '<span class="label cell-green">NL</span>';
                if(!$estatus)
                    $table[$i][$j++] = '<span class="label cell-green">NL</span>';
                else
                    $table[$i][$j++] = '<span class="label cell-red">L</span>';
                // add by kulothunga to find the grade status
                if(!$resstatus)
                    $table[$i][$j++] = '<span class="label cell-green">NL</span>';
                else
                    $table[$i][$j++] = '<span class="label cell-red">L</span>';
                $table[$i++][$j] = $atttlock;
            }

            return array('result' => 'true', 'msg' => $table);
        } else {
            return array('result' => 'false', 'msg' => 'Invalid branch or sem');
        }
    }

    public function staff_subject_stud_count($markid) {
        $mdetail = $this->markid_details($markid);
        $data = array('STAFFID' => $mdetail['staffid'], 'SUBCODE' => $mdetail['subcode'], 'SESID' => $mdetail['sesid'], 'FLAG!=' => 'D');
        $count = $this->db->select('COUNT(*) as CNT')->where($data)->get('REGISTER')->row()->CNT;
        //var_dump($count);
        return $count;
    }

    public function custom_grade_range($markid, $record) {
        $mdetail = $this->markid_details($markid);
        if($mdetail['sesid'] != $this->sesid)
            return array('result' => FALSE, 'msg' => 'Session Locked! Cannot customize grade range!');
        if(!$this->is_attlocked($markid, 'esem'))
            return array('result' => FALSE, 'msg' => 'Lock End. Sem to customize grade range!');

        if($this->is_attlocked($markid, 'result'))
            return array('result' => FALSE, 'msg' => 'Grade Locked! Cannot customize grade range!');

        /**checking if it falls under custom grade **/
        $count = $this->staff_subject_stud_count($markid);
        if($count < 0) {
            return array('result' => FALSE, 'msg' => 'Custom grade range is not available!');
        }

        /**checking if all subject has been locked **/
        $data = array('SUBCODE' => $mdetail['subcode'], 'STAFFID' => $mdetail['staffid'], 'SESID' => $mdetail['sesid']);
        $this->db->select('MARKID');
        $this->db->where($data);
        $query = $this->db->get('SUBLINK');
        if($query->num_rows() > 0) {
            foreach($query->result() as $row) {
                /**checking if student has registered **/
                $data = array('MARKID' => $row->MARKID, 'SESID' => $mdetail['sesid'], 'FLAG !=' => 'D');
                $cnt = $this->db->select('COUNT(*) AS CNT')->where($data)->get('REGISTER')->row()->CNT;
                if($cnt == 0) continue;

                /**check if end sem is locked **/
                if(!$this->is_attlocked($row->MARKID, 'ESEM')) {
                    return array('result' => FALSE, 'msg' => 'Lock End Sem marks of all batches of '.$mdetail['subcode'].' to compute result');
                }

                /**resetting result**/
                $this->db->set(array('RESULT' => null));
                $this->db->where(array('MARKID' => $row->MARKID));
                $this->db->update('MARKS');
            }
        } else {
            return array('result' => FALSE, 'msg' => 'Invalid ID');
        }

        /**checking if cbcs-grade range generated**/
        if(!$this->is_grade_range_generated($markid)) {
        	return array('result' => FALSE, 'msg' => 'Generate auto grade range before customizing grade range');
        }

        $wdata = array('STAFFID' => $mdetail['staffid'], 'SUBCODE' => $mdetail['subcode'], 'SESID' => $mdetail['sesid']);
        $record['GDATE'] = date('d-M-Y h:i:sa');
        $record['STATUS'] = 0;
        $record['BO'] = 50;
        $record['BT'] = 50;
        $this->db->set($record);
        $this->db->where($wdata);
        $this->db->update('GRADE_RANGE');

        $res = $this->calculate_result($markid, FALSE);
        if($res['result'] == FALSE) {
            return array('result' => FALSE, 'msg' => 'Failed computing grade!');
        } else
            return array('result' => TRUE, 'msg' => 'Custom grade range generated successfully!');
    }

    public function is_grade_range_locked($markid) {
        $mdetail = $this->markid_details($markid);
        $data = array('STAFFID' => $mdetail['staffid'], 'SUBCODE' => $mdetail['subcode'], 'SESID' => $mdetail['sesid'], 'STATUS' => 2);
        $this->db->select('MEAN');
        $this->db->where($data);
        $query = $this->db->get('GRADE_RANGE');
        if($query->num_rows() > 0)
            return TRUE;
        else
            return FALSE;
    }

    public function lock_grade($markid) {
        $mdetail = $this->markid_details($markid);
        $data = array('STAFFID' => $mdetail['staffid'], 'SUBCODE' => $mdetail['subcode'], 'SESID' => $mdetail['sesid']);
        $this->db->select('STATUS');
        $this->db->where($data);
        $query = $this->db->get('GRADE_RANGE');
        if($query->num_rows() > 0) {
            $status = $query->row()->STATUS;
            if($status == 1) 
                return TRUE;
            else {
                $this->db->set(array('STATUS' => 2));
                $this->db->where($data);
                $this->db->update('GRADE_RANGE');
                return TRUE;
            }
        } else
            return FALSE;
    }

    public function is_exam_absent($regno, $markid, $sesid) {
        $data = array('REGNO' => $regno, 'MARKID' => $markid, 'ATT' => 'A', 'SESID' => $sesid);
        $this->db->where($data);
        $query = $this->db->get('EXAM_ATT');
        if($query->num_rows() > 0)
            return TRUE;
        else return FALSE;
    }

 public function hod_checklist_pdf($hodid, $brcode, $sem){
    	define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_no_wm.php');

        $staff = $this->GM->get_staff_info($hodid);
        $branch = $this->GM->get_branch_info($brcode);
        $dept = $this->GM->get_dept_info($staff['depcode']);
        $sem = 'Sem - '.$sem;

        $degmode = $branch['degmode'] == 'FULL TIME' ? ' [F.T] ' : ' [P.T] '; 
        $br = $branch['degshort'].' '.$branch['brname'].$degmode.$sem;

        $uname = 'Anna University, Chennai - 600 025.';
        $pdf = new PDF();
        $pdf->SetTitle('CBCS');
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',15);
        //$pdf->Cell(0,15,'HOD Checklist',0,0,'C');
        $pdf->Ln(15);
        $warray = array(12, 100);
        $pdf->SetWidths($warray);
        $pdf->border(FALSE);
        $pdf->SetFont('Arial','B',10.5);
        $pdf->SetAligns(array('L','L'));
        $pdf->Row(array('From',''));
        $pdf->SetFont('Arial','',10.5);
        $pdf->Row(array('',$staff['staffname']));
        $pdf->Row(array('',$br));
        $pdf->Row(array('',$dept['depname']));
        $pdf->Row(array('',$uname));
        $pdf->SetFont('Arial','B',10.5);
        $pdf->Row(array('To',''));
        $pdf->SetFont('Arial','',10.5);
        $pdf->Row(array('','The Additional Controller of Examinations'));
        $pdf->Row(array('','(University Departments)'));
        $pdf->Row(array('',$uname));
        $pdf->Ln(2);

        $warray = array(12, 155);
        $pdf->SetWidths($warray);
        $pdf->border(FALSE);
        $pdf->SetFont('Arial','B',10.5);
        $pdf->SetAligns(array('L','L'));
        $pdf->Row(array('Sir,',''));
        $pdf->SetFont('Arial','B',10.5);
        $pdf->Row(array('','Sub: Results of '.$br.'-Enclosed-Reg.'));
        $pdf->Row(array('','I am enclosing the following sheet pertaining to '.$br));

        $sno = 1; $width = 6; $height = 5; $x = 175;
        $warray = array(20, 7, 125);
        $pdf->SetWidths($warray);
        $pdf->border(FALSE);
        $pdf->SetAligns(array('L','L','L'));
        $pdf->SetFont('Arial','',10.5);/*
        $bY = $pdf->GetY();
        $pdf->Row(array('',($sno++).'.','Hall Ticket Issue List'));
        $aY = $pdf->GetY();
        $pdf->SetY($bY);$pdf->SetX($x); $pdf->Cell($width, $height,'',1,1);
        $pdf->SetY($aY);*/
        $bY = $pdf->GetY();
        $pdf->Row(array('',($sno++).'.','Cummulative Assessment Report (Sessional Weightage)'));
        $aY = $pdf->GetY();
        $pdf->SetY($bY);$pdf->SetX($x); $pdf->Cell($width, $height,'',1,1);
        //$pdf->SetY($aY);
        //$bY = $pdf->GetY();
        //$pdf->Row(array('',($sno++).'.','Print Two copies of the same result sheet PDF file'));
        //$aY = $pdf->GetY();
        //$pdf->SetY($bY);$pdf->SetX($x); $pdf->Cell($width, $height,'',1,1);
        $pdf->SetY($aY);
        $bY = $pdf->GetY();
        $pdf->Row(array('',($sno++).'.','Course Grade Sheet(For all Subjects with Instructor Signature.)'));
        $aY = $pdf->GetY();
        $pdf->SetY($bY);$pdf->SetX($x); $pdf->Cell($width, $height,'',1,1);
        $pdf->SetY($aY);
		$bY = $pdf->GetY();
        $pdf->Row(array('',($sno++).'.','Attendance Sheet and Foil Sheet for all theory subjects (Except UG program)'));
        $aY = $pdf->GetY();
        $pdf->SetY($bY);$pdf->SetX($x); $pdf->Cell($width, $height,'',1,1);
        $pdf->SetY($aY);
		$bY = $pdf->GetY();
        $pdf->Row(array('',($sno++).'.','Printed foil sheet and attendance sheet for all laboratory subjects'));
        $aY = $pdf->GetY();
        $pdf->SetY($bY);$pdf->SetX($x); $pdf->Cell($width, $height,'',1,1);
        $pdf->SetY($aY);
		$bY = $pdf->GetY();
        $pdf->Row(array('',($sno++).'.','In case of PG Project work,'));
        $pdf->Row(array('','','  (i) Viva voce- Mark Split up Sheet'));
        $aY = $pdf->GetY();
        $pdf->SetY($bY);$pdf->SetX($x); $pdf->Cell($width, $height,'',1,1);
        $pdf->SetY($aY);
		$bY = $pdf->GetY();
        $pdf->Ln(2);
        $pdf->SetWidths(array(12,175));
        $pdf->Row(array('','This is for your kind information and necessary action.'));
        //$pdf->SetFont('Arial','B',10.5);
        //$pdf->Cell(12,6,'',0,0);
        //$pdf->MultiCell(0,6,'Note: The Result sheet should be printed with full page size setting. Kindly go through "PRINTER SETTING" while printing result sheet.',1,1);
        $pdf->SetFont('Arial','',10.5);        
        $pdf->Row(array('','Encl: As above(Original Copy)'));
        $pdf->SetY(-55);
        $pdf->SetFont('Arial','B', 9.5);
        $pdf->SetX(155);
        $pdf->Cell(0, 6, 'Your\'s faithfully,','','');
        $pdf->SetY(-37);
        $pdf->SetFont('Arial','B', 9.5);
        $pdf->Cell(10, 6, 'Date','','','L');
        $pdf->Write(6, ': ');
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(0,6, date("d-M-Y h:i:sa"),0,0,'L');
        $pdf->SetFont('Arial','B', 9.5);
        $pdf->Cell(-60, 6, 'Signature of the HOD', '', 1, 'C');
		
		$pdf->Output($branch['brname'].' '.$sem.'.pdf','I');

    }

    public function grade_count($markid) {
        $data = array('MARKID' => $markid);
        $this->db->select('O, AP, A, BP, B, RA, SA, AB');
        $this->db->where($data);
        $query = $this->db->get('GRADE');
        $table = '';
        if($query->num_rows() == 1) {
            $row = $query->row_array();

            $mdetail = $this->markid_details($markid);
            $grade_info = $this->get_mean($markid);
            $subject = $this->GM->get_subject_info($mdetail['subcode']);
            $table = '<tr>';
            $table .= '<td>'.$mdetail['subcode'].'</td>';
            $table .= '<td>'.$subject['subname'].'</td>';
            $table .= '<td>'.$grade_info['mean'].'</td>';
            $table .= '<td>'.$grade_info['passmean'].'</td>';
            $table .= '<td>'.$grade_info['sd'].'</td>';
            foreach ($row as $key => $value) {
                $table .= '<td>'.$value.'</td>';
            }
            $table .= '</tr>';
        }
        return $table;
    }

    public function result_sheet_pdf($brcode, $sesid, $sem, $processed) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_l_no_wm.php');

        /*Maximum no. of subject displayed in one page*/
        $MAX_COLUMN = 9;
        /*Maximum no. of records displayed in one page*/
        $MAX_PAGE = 15;
        /*
        *DO NOT EDIT
        *Table Width Properties
        */
        $nwdith = 219;
        $cwidth = 16;
        $swidth = 21;

        $sub_array = '';
        $data = array('BRCODE' => $brcode, 'SESID' => $sesid, 'SUBLINK_SEM' => $sem);
        $credit = '';
        $this->db->select('DISTINCT(SUBCODE)');
        $this->db->where($data);
        $query = $this->db->get('REGISTER');
        if($query->num_rows() < 1) {
            echo '<h1>No students found</h1>';
            return;
        }

        if($processed) {
            $table = 'PROCESSED_MARKS';
            $column = 'PGPA';
            $length = 92;
        }
        else {
            $table = 'MARKS';
            $column = 'GPA';
            $length = 69;
        }

        $TOTAL_SUBJECT = $query->num_rows();
        $N = intval($TOTAL_SUBJECT / $MAX_COLUMN);
        if($TOTAL_SUBJECT % $MAX_COLUMN != 0) $N++;
        for($i = 0; $i < $N; $i++) {
            $harray[$i] = array('S.No.', 'Register No.', 'Name of the Student');
            $warray[$i] = array(12, 25, $nwdith);
        }

        /*Fetching subjects & generating table header for each page*/
        $i = $k = 0; $j = 3;
        foreach($query->result() as $r) {
            $subject = $this->GM->get_subject_info($r->SUBCODE);
            $sub_array[$k] = $r->SUBCODE;
            $credit[$r->SUBCODE] = $subject['credit'];
            $harray[$i][$j] = $r->SUBCODE;
            $warray[$i][2] -= $cwidth;
            $warray[$i][$j++] = $cwidth;
            $k++;
            if($k % $MAX_COLUMN == 0) {                
                $harray[$i][$j] = 'GPA';
                $warray[$i][$j] = $swidth;
                $i++; $j = 3;
            }
        }
        /*MAX_COLUMN boundry value fix*/
        if($k == $TOTAL_SUBJECT && $j == 3) $i--;
        else {
            $harray[$i][$j] = 'GPA';
            $warray[$i][$j] = $swidth;
        }

        /*Checking if result has been locked*/
        $locked = $this->all_result_locked($brcode, $sem, $sesid);

        $branch = $this->GM->get_branch_info($brcode);
        $dept = $this->GM->get_dept_info($branch['depcode']);
        $campus = $this->GM->get_campus_info($branch['camcode']);
        $header1 = array('Campus', $campus['camname'], 'Session', 'APR - 18');
        $header2 = array('Department ', $branch['depcode'].' - '.$dept['depname'], 'Mode', $branch['degmode']);
        $header3 = array('Branch', $brcode.' - '.$branch['degshort'].' '.$branch['brname'], 'Semester :', $sem,  'Regulation', '2015',);
        $pdf = new PDF();
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();

        /*Generting record to display*/
        $this->db->select('DISTINCT(REGNO)');
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $query = $this->db->get('REGISTER');
        if($query->num_rows() > 0) {
            $TOTAL_RECORD = $query->num_rows();
            $record = '';
            $l = 0; $sno = 0;
            foreach($query->result() as $r) {
                $sno++;
                $i = 0;
                $student = $this->GM->get_student_info($r->REGNO);
                for($i = 0; $i < $N; $i++) {
                    $j = 0;
                    $record[$i][$l][$j++] = $sno;
                    $record[$i][$l][$j++] = $r->REGNO;
                    $record[$i][$l][$j++] = $student['name'];
                } //end of for loop - i
                $i = 0; $j = 3;  
                $num = $den = 0; $all_sub_locked = TRUE;
                for($k = 0; $k < $TOTAL_SUBJECT; $k++) {
                    $data = array('REGNO' => $r->REGNO, 'SUBCODE' => $sub_array[$k], 'SUBLINK_SEM' => $sem, 'SESID' => $sesid, 'FLAG !=' => 'D');
                    $this->db->select('MARKID, FLAG');
                    $this->db->where($data);
                    $query = $this->db->get('REGISTER');
                    if($query->num_rows() == 1) {
                        $row = $query->row();

                        $data = array('MARKID' => $row->MARKID, 'REGNO' => $r->REGNO);
                        $this->db->select('RESULT');
                        $this->db->where($data);
                        $query = $this->db->get($table);

                        if($query->num_rows() == 1) {
                            
                            $row = $query->row();

                            if($row->RESULT == 'AB')
                                $record[$i][$l][$j++] = 'RA';
                            else                            
                                $record[$i][$l][$j++] = $row->RESULT;
                            switch($row->RESULT) {
                                case 'O': $grade = 10; break;
                                case 'A+': $grade = 9; break;
                                case 'A': $grade = 8; break;
                                case 'B+': $grade = 7; break;
                                case 'B': $grade = 6; break;
                                case 'SA': $grade = 0; break; 
                                case 'AB': $grade = 0; break;
                                case 'RA': $grade = 0; break;
                                case 'W': $grade = 0; break;
                                case '-': $grade = 0; break;
                                default: $grade = 0; $all_sub_locked = FALSE;
                            } //end of switch
                            if($grade != 0) {
                                $num += $credit[$sub_array[$k]] * $grade;
                                $den += $credit[$sub_array[$k]];
                            }
                        } else {
                            $record[$i][$l][$j++] = ' ';                            
                        } //end of if
                        
                    } else {
                        $record[$i][$l][$j++] = ' ';
                    } //end of if
                    if(($k+1) % $MAX_COLUMN == 0) {
                        $record[$i][$l][$j] = ' ';
                        $i++; $j = 3;
                    }
                } //end of for loop - k

                if($k == $TOTAL_SUBJECT && $j == 3) {
                    $i--;
                    $j = sizeof($record[$i][$l]) - 1;
                }
                if($all_sub_locked) {
                    if($den == 0)
                        $gpa = 0;
                    else{
                        $gpa = round ($num / $den, 2,  PHP_ROUND_HALF_UP);
                    }
                    $d1 = array('REGNO' => $r->REGNO, 'SEM' => $sem, 'SESID' => $sesid);
                    $this->db->select($column);
                    $this->db->where($d1);
                    $sq2 = $this->db->get('GPA');
                    if($sq2->num_rows() > 0) {
                        if($sq2->row()->{$column} != $gpa) {
                            $this->db->set(array($column => $gpa));
                            $this->db->where($d1);
                            $this->db->update('GPA');
                        }
                    } else {
                        $d1[$column] = $gpa;
                        $this->db->insert('GPA',$d1);                        
                    }
                } else $gpa = '';

                $record[$i][$l++][$j++] = $gpa;

                /*Displaying generated data when MAX_PAGE limit or TOTAL_RECORD is reached*/
                if($l % $MAX_PAGE == 0 || $sno == $TOTAL_RECORD) {
                    for($i = 0; $i < $N; $i++) {
                        $pdf->AddPage('L');
                        if(!$locked) {
                            $pdf->SetFont('Arial','B',50);
                            $pdf->SetTextColor(255,192,203);
                            $pdf->RotatedText(55,160,'P r o v i s i o n a l',45);
                            $pdf->SetTextColor(0,0,0);
                        }
                        $pdf->SetFont('Arial','B',15);
                        $pdf->Cell(0, 10, 'Result Sheet', 0, 0, 'C');
                        $pdf->Ln(10);
                        $pdf->printBox($header1, 'T');
                        $pdf->printBox($header2, null);
                        $pdf->printBox($header3, 'B');
                        $pdf->SetFont('Arial','B', 9.5);
                        $pdf->SetWidths($warray[$i]);
                        $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C'));
                        $pdf->Row($harray[$i]);
                        $pdf->SetFont('Arial','', 9.5);
                        $pdf->SetAligns(array('C','C','L','C','C','C','C','C','C','C','C','C','C','C','C','C','C'));
                        for($m = 0; $m < $l; $m++) {
                            $arr = $record[$i][$m];
                            if($i+1 != $N) {
                                $arr[sizeof($arr) - 1] = $record[$N - 1][$m][sizeof($record[$N-1][$m]) - 1];
                            }
                            $pdf->Row($arr);
                        } //end of for
                        $pdf->SetY(-33);
                        $pdf->SetFont('Arial','B', 9.5);
                        if(!$processed)
                           $pdf->Cell($length, 6, 'Signature of ',0,'','C');
							$pdf->Cell($length, 6, 'Deputy Controller of Examinations', 0, '', 'C');
							$pdf->Cell($length, 6, 'Deputy Controller of Examinations', 0, '', 'C');
							$pdf->Cell($length, 6, 'Additional Controller of Examinations', 0, 1, 'C');
                        if(!$processed)
                            $pdf->Cell($length, 6, 'Head of the Department ',0,'','C');
							$pdf->Cell($length, 6, ' ( Data Centre ) ',0,0,'C');
							$pdf->Cell($length, 6, ' ',0,0,'C');
							$pdf->Cell($length, 6, ' ',0,1,'C');
						//$pdf->Cell($length, 6, '( Data Centre )',0,'','C');
                    } //end of for 
                    $record = ''; $l = 0;
                } //end of if;
            }

            /*Displaying subject details*/
            $pdf->AddPage('L');
            if(!$locked) {
                $pdf->SetFont('Arial','B',50);
                $pdf->SetTextColor(255,192,203);
                $pdf->RotatedText(55,160,'P r o v i s i o n a l',45);
                $pdf->SetTextColor(0,0,0);
            }
            $pdf->SetFont('Arial','B',15);
            $pdf->Cell(0,10,'Result Sheet',0,0,'C');
            $pdf->Ln(10);
            $pdf->printBox($header1, 'T');
            $pdf->printBox($header2, null);
            $pdf->printBox($header3, 'B');
            $n = round(sizeof($sub_array) / 2, 0,  PHP_ROUND_HALF_UP);
            $pdf->SetWidths(array(20, 93.5, 25, 20, 93.5, 25));
            $pdf->SetFont('Arial','B',9.5);
            $pdf->SetAligns(array('C','C','C','C','C','C'));
            $pdf->Row(array('Code', 'Course Name', 'Credits','Code', 'Course Name', 'Credits'));
            $pdf->SetFont('Arial','',9.5);
            $pdf->SetAligns(array('C','L','C','C','L','C'));
            for($i = 0; $i < $n; $i++) {
                $subject1 = $this->GM->get_subject_info($sub_array[$i]);
                if($i + $n < $TOTAL_SUBJECT) {
                    $subject2 = $this->GM->get_subject_info($sub_array[$i+$n]);
                    $pdf->Row(array($sub_array[$i],$subject1['subname'], $subject1['credit'],$sub_array[$i+$n],$subject2['subname'], $subject2['credit']));
                } else
                    $pdf->Row(array($sub_array[$i],$subject1['subname'], $subject1['credit'] )); 
            }
            $pdf->SetY(-33);
            $pdf->SetFont('Arial','B', 9.5);
            if(!$processed)
                $pdf->Cell($length, 6, 'Signature of ',0,'','C');
				$pdf->Cell($length, 6, 'Deputy Controller of Examinations', 0, '', 'C');
				$pdf->Cell($length, 6, 'Deputy Controller of Examinations', 0, '', 'C');
				$pdf->Cell($length, 6, 'Additional Controller of Examinations', 0, 1, 'C');
				
            if(!$processed)
                $pdf->Cell($length, 6, 'Head of the Department ',0,'','C');
				$pdf->Cell($length, 6, ' ( Data Centre ) ',0,0,'C');
                $pdf->Cell($length, 6, ' ',0,0,'C');
                $pdf->Cell($length, 6, ' ',0,1,'C');
            
		//$pdf->Cell($length, 6, '( Data Centre )',0,'','C');
        }
        $pdf->output('Cummulative Result Sheet'.'.pdf','I');
    }

    public function all_result_locked($brcode, $sem, $sesid){
        $data = array('BRCODE' => $brcode, 'SUBLINK_SEM' => $sem, 'SESID' => $sesid);
        $this->db->select('DISTINCT (MARKID)');
        $this->db->where($data);
        $query = $this->db->get('REGISTER');

        if($query->num_rows() > 0){
            $total = $query->num_rows();
            $locked = 0;
            foreach ($query->result() as $row) {
                $data = array('MARKID' => $row->MARKID, 'RESULT' => 1 );
                $this->db->select('STATUS');
                $this->db->where($data);
                $sq1 = $this->db->get('ATTSTATUS');                
                if($sq1->num_rows() === 1){
                    $locked++;
                }else{
                    return FALSE;
                }
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function cumulative_ass_pdf($brcode, $sesid, $sem) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_l_no_wm.php');

        /*Maximum no. of subject displayed in one page*/
        $MAX_COLUMN = 9;
        /*Maximum no. of records displayed in one page*/
        $MAX_PAGE = 15;
        /*
        *DO NOT EDIT
        *Table Width Properties
        */
        $nwdith = 240;
        $cwidth = 16;

        $sub_array = '';
        $data = array('BRCODE' => $brcode, 'SESID' => $sesid, 'SUBLINK_SEM' => $sem);
        $credit = '';
        $this->db->select('DISTINCT(SUBCODE)');
        $this->db->where($data);
        $query = $this->db->get('REGISTER');
        if($query->num_rows() < 1) {
            echo '<h1>No students found</h1>';
            return;
        }

        $TOTAL_SUBJECT = $query->num_rows();
        $N = intval($TOTAL_SUBJECT / $MAX_COLUMN);
        if($TOTAL_SUBJECT % $MAX_COLUMN != 0) $N++;
        for($i = 0; $i < $N; $i++) {
            $harray[$i] = array('S.No.', 'Register No.', 'Name of the Student');
            $warray[$i] = array(12, 25, $nwdith);
        }

        /*Fetching subjects & generating table header for each page*/
        $i = $k = 0; $j = 3;
        foreach($query->result() as $r) {
            $subject = $this->GM->get_subject_info($r->SUBCODE);
            $sub_array[$k] = $r->SUBCODE;
            $credit[$r->SUBCODE] = $subject['credit'];
            $harray[$i][$j] = $r->SUBCODE;
            $warray[$i][2] -= $cwidth;
            $warray[$i][$j++] = $cwidth;
            $k++;
            if($k % $MAX_COLUMN == 0) {
                $i++; $j = 3;
            }
        }
        /*MAX_COLUMN boundry value fix*/
        if($k == $MAX_COLUMN && $j == 3) $i--;

        /*Checking if result has been locked*/
        $locked = $this->all_result_locked($brcode, $sem, $sesid);


        $branch = $this->GM->get_branch_info($brcode);
        $dept = $this->GM->get_dept_info($branch['depcode']);
        $campus = $this->GM->get_campus_info($branch['camcode']);
        $header1 = array('Campus', $campus['camname'], 'Session', 'APR - 18');
        $header2 = array('Department ', $branch['depcode'].' - '.$dept['depname'], 'Mode', $branch['degmode']);
        $header3 = array('Branch', $brcode.' - '.$branch['degshort'].' '.$branch['brname'], 'Semester :', $sem,  'Regulation', '2015',);
        $pdf = new PDF();
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();

        /*Generting record to display*/
        $this->db->select('DISTINCT(REGNO)');
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $query = $this->db->get('REGISTER');
        if($query->num_rows() > 0) {
            $TOTAL_RECORD = $query->num_rows();
            $record = '';
            $l = 0; $sno = 0;
            foreach($query->result() as $r) {
                $sno++;
                $i = 0;
                $student = $this->GM->get_student_info($r->REGNO);
                for($i = 0; $i < $N; $i++) {
                    $j = 0;
                    $record[$i][$l][$j++] = $sno;
                    $record[$i][$l][$j++] = $r->REGNO;
                    $record[$i][$l][$j++] = $student['name'];
                } //end of for loop - i
                $i = 0; $j = 3;  
                $num = $den = 0;
                for($k = 0; $k < $TOTAL_SUBJECT; $k++) {
                    $data = array('REGNO' => $r->REGNO, 'SUBCODE' => $sub_array[$k], 'SUBLINK_SEM' => $sem, 'SESID' => $sesid, 'FLAG !=' => 'D');
                    $this->db->select('MARKID, FLAG');
                    $this->db->where($data);
                    $query = $this->db->get('REGISTER');
                    if($query->num_rows() == 1) {
                        $row = $query->row();

                        $data = array('MARKID' => $row->MARKID, 'REGNO' => $r->REGNO);
                        $this->db->select('SESMARK');
                        $this->db->where($data);
                        $query = $this->db->get('MARKS');

                        if($query->num_rows() == 1) {
                            $row = $query->row();
                            $record[$i][$l][$j++] = $row->SESMARK;
                        } else {
                            $record[$i][$l][$j++] = ' ';                            
                        } //end of if
                        
                    } else {
                        $record[$i][$l][$j++] = ' ';
                    } //end of if
                    if(($k+1) % $MAX_COLUMN == 0) {
                        $i++; $j = 3;
                    }
                } //end of for loop - k

                if($k == $MAX_COLUMN && $j == 3) {
                    $i--;
                    $j = sizeof($record[$i][$l]) - 1;
                }
                $l++;

                /*Displaying generated data when MAX_PAGE limit or TOTAL_RECORD is reached*/
                if($l % $MAX_PAGE == 0 || $sno == $TOTAL_RECORD) {
                    for($i = 0; $i < $N; $i++) {
                        $pdf->AddPage('L');
                        if(!$locked) {
                            $pdf->SetFont('Arial','B',50);
                            $pdf->SetTextColor(255,192,203);
                            $pdf->RotatedText(55,160,'P r o v i s i o n a l',45);
                            $pdf->SetTextColor(0,0,0);
                        }
                        $pdf->SetFont('Arial','B',15);
                        $pdf->Cell(0, 10, 'Cumulative Assessment Report', 0, 0, 'C');
                        $pdf->Ln(10);
                        $pdf->printBox($header1, 'T');
                        $pdf->printBox($header2, null);
                        $pdf->printBox($header3, 'B');
                        $pdf->SetFont('Arial','B', 9.5);
                        $pdf->SetWidths($warray[$i]);
                        $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C'));
                        $pdf->Row($harray[$i]);
                        $pdf->SetFont('Arial','', 9.5);
                        $pdf->SetAligns(array('C','C','L','C','C','C','C','C','C','C','C','C','C','C','C','C'));
                        for($m = 0; $m < $l; $m++) {
                            $arr = $record[$i][$m];
                            $pdf->Row($arr);
                        } //end of for
                        $pdf->SetY(-33);
                        $pdf->SetFont('Arial','B', 9.5);
                        $pdf->Cell(69, 6, '',0,'','C');
                        $pdf->Cell(69, 6, '',0,'','C');
                        $pdf->Cell(69, 6, '',0,'','C');
                        $pdf->Cell(69, 6, 'Signature of',0,1,'C');
                        $pdf->Cell(69, 6, '',0,'','C');
                        $pdf->Cell(69, 6, '', 0, 0, 'C');
                        $pdf->Cell(69, 6, '', 0, 0, 'C');
                        $pdf->Cell(69, 6, 'Head of the Department', 0, 1, 'C');
                    } //end of for 
                    /*Emptying the generated record before fetching next set of record*/
                    $record = ''; $l = 0;
                } //end of if;
            }

            /*Displaying subject details*/
            $pdf->AddPage('L');
            if(!$locked) {
                $pdf->SetFont('Arial','B',50);
                $pdf->SetTextColor(255,192,203);
                $pdf->RotatedText(55,160,'P r o v i s i o n a l',45);
                $pdf->SetTextColor(0,0,0);
            }
            $pdf->SetFont('Arial','B',15);
            $pdf->Cell(0,10,'Cumulative Assessment Report',0,0,'C');
            $pdf->Ln(10);
            $pdf->printBox($header1, 'T');
            $pdf->printBox($header2, null);
            $pdf->printBox($header3, 'B');
            $n = round(sizeof($sub_array) / 2, 0,  PHP_ROUND_HALF_UP);
            $pdf->SetWidths(array(20, 93.5, 25, 20, 93.5, 25));
            $pdf->SetFont('Arial','B',9.5);
            $pdf->SetAligns(array('C','C','C','C','C','C'));
            $pdf->Row(array('Code', 'Course Name', 'Credits','Code', 'Course Name', 'Credits'));
            $pdf->SetFont('Arial','',9.5);
            $pdf->SetAligns(array('C','L','C','C','L','C'));
            for($i = 0; $i < $n; $i++) {
                $subject1 = $this->GM->get_subject_info($sub_array[$i]);
                if($i + $n < $TOTAL_SUBJECT) {
                    $subject2 = $this->GM->get_subject_info($sub_array[$i+$n]);
                    $pdf->Row(array($sub_array[$i],$subject1['subname'], $subject1['credit'],$sub_array[$i+$n],$subject2['subname'], $subject2['credit']));
                } else
                    $pdf->Row(array($sub_array[$i],$subject1['subname'], $subject1['credit'] )); 
            }
            $pdf->SetY(-33);
            $pdf->SetFont('Arial','B', 9.5);
            $pdf->Cell(69, 6, '',0,'','C');
            $pdf->Cell(69, 6, '',0,'','C');
            $pdf->Cell(69, 6, '',0,'','C');
            $pdf->Cell(69, 6, 'Signature of',0,1,'C');
            $pdf->Cell(69, 6, '',0,'','C');
            $pdf->Cell(69, 6, '', 0, 0, 'C');
            $pdf->Cell(69, 6, '', 0, 0, 'C');
            $pdf->Cell(69, 6, 'Head of the Department', 0, 1, 'C');
        }
        $pdf->output('Cumulative Assessment Report.pdf','I');
    }

    public function student_subject_pdf($brcode, $sesid, $sem) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_l_no_wm.php');

        /*Maximum no. of subject displayed in one page*/
        $MAX_COLUMN = 9;
        /*Maximum no. of records displayed in one page*/
        $MAX_PAGE = 15;
        /*
        *DO NOT EDIT
        *Table Width Properties
        */
        $nwdith = 219;
        $cwidth = 16;
        $swidth = 21;

        $sub_array = '';
        /*$data = array('BRCODE' => $brcode, 'SESID' => $sesid, 'SUBLINK_SEM' => $sem);*/
        $data = array('BRCODE' => $brcode, 'SESID' => $sesid, 'SUBLINK_SEM' => $sem);
        $credit = '';
        $this->db->select('DISTINCT(SUBCODE)');
        $this->db->where($data);
        $query = $this->db->get('REGISTER');
        if($query->num_rows() < 1) {
            echo '<h1>No students found</h1>';
            return;
        }

        $TOTAL_SUBJECT = $query->num_rows();
        $N = intval($TOTAL_SUBJECT / $MAX_COLUMN);
        if($TOTAL_SUBJECT % $MAX_COLUMN != 0) $N++;
        for($i = 0; $i < $N; $i++) {
            $harray[$i] = array('S.No.', 'Register No.', 'Name of the Student');
            $warray[$i] = array(12, 25, $nwdith);
        }

        /*Fetching subjects & generating table header for each page*/
        $i = $k = 0; $j = 3;
        foreach($query->result() as $r) {
            $subject = $this->GM->get_subject_info($r->SUBCODE);
            $sub_array[$k] = $r->SUBCODE;
            $credit[$r->SUBCODE] = $subject['credit'];
            $harray[$i][$j] = $r->SUBCODE;
            $warray[$i][2] -= $cwidth;
            $warray[$i][$j++] = $cwidth;
            $k++;
            if($k % $MAX_COLUMN == 0) {                
                $harray[$i][$j] = 'Signature';
                $warray[$i][$j] = $swidth;
                $i++; $j = 3;
            }
        }
        /*MAX_COLUMN boundry value fix*/
        if($k == $MAX_COLUMN && $j == 3) $i--;
        else {
            $harray[$i][$j] = 'Signature';
            $warray[$i][$j] = $swidth;
        }

        /*Checking if result has been locked*/
        $locked = $this->all_result_locked($brcode, $sem, $sesid);

        $branch = $this->GM->get_branch_info($brcode);
        $dept = $this->GM->get_dept_info($branch['depcode']);
        $campus = $this->GM->get_campus_info($branch['camcode']);
        $header1 = array('Campus', $campus['camname'], 'Session', 'NOV - 18');
        $header2 = array('Department ', $branch['depcode'].' - '.$dept['depname'], 'Mode', $branch['degmode']);
        $header3 = array('Branch', $brcode.' - '.$branch['degshort'].' '.$branch['brname'], 'Semester :', $sem,  'Regulation', '2015',);
        $pdf = new PDF();
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();

        /*Generting record to display*/
        $this->db->select('DISTINCT(REGNO)');
        $this->db->where($data);
        $this->db->order_by('REGNO');
        $query = $this->db->get('REGISTER');
        if($query->num_rows() > 0) {
            $TOTAL_RECORD = $query->num_rows();
            $record = '';
            $l = 0; $sno = 0;
            foreach($query->result() as $r) {
                $sno++;
                $i = 0;
                $student = $this->GM->get_student_info($r->REGNO);
                for($i = 0; $i < $N; $i++) {
                    $j = 0;
                    $record[$i][$l][$j++] = $sno;
                    $record[$i][$l][$j++] = $r->REGNO;
                    $record[$i][$l][$j++] = $student['name'];
                } //end of for loop - i
                $i = 0; $j = 3;  
                $num = $den = 0;
                for($k = 0; $k < $TOTAL_SUBJECT; $k++) {
                    $data = array('REGNO' => $r->REGNO, 'SUBCODE' => $sub_array[$k], 'SUBLINK_SEM' => $sem, 'SESID' => $sesid, 'FLAG !=' => 'D');
                    $this->db->select('MARKID, FLAG');
                    $this->db->where($data);
                    $query = $this->db->get('REGISTER');

                    if($query->num_rows() == 1) {
                        $record[$i][$l][$j++] = 'R';
                    } else {
                        $record[$i][$l][$j++] = '-';
                    } //end of if
                    if(($k+1) % $MAX_COLUMN == 0) {
                        $record[$i][$l][$j] = ' ';
                        $i++; $j = 3;
                    }
                } //end of for loop - k

                if($k == $MAX_COLUMN && $j == 3) {
                    $i--;
                    $j = sizeof($record[$i][$l]) - 1;
                }

                $record[$i][$l++][$j++] = '';

                /*Displaying generated data when MAX_PAGE limit or TOTAL_RECORD is reached*/
                if($l % $MAX_PAGE == 0 || $sno == $TOTAL_RECORD) {
                    for($i = 0; $i < $N; $i++) {
                        $pdf->AddPage('L');
                        /* if(!$locked) {
                            $pdf->SetFont('Arial','B',50);
                            $pdf->SetTextColor(255,192,203);
                            $pdf->RotatedText(55,160,'P r o v i s i o n a l',45);
                            $pdf->SetTextColor(0,0,0);
                        } */
                        $pdf->SetFont('Arial','B',15);
                        $pdf->Cell(0, 10, 'Students Course Registered List', 0, 0, 'C');
                        $pdf->Ln(10);
                        $pdf->printBox($header1, 'T');
                        $pdf->printBox($header2, null);
                        $pdf->printBox($header3, 'B');
                        $pdf->SetFont('Arial','B', 9.5);
                        $pdf->SetWidths($warray[$i]);
                        $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C'));
                        $pdf->Row($harray[$i]);
                        $pdf->SetFont('Arial','', 9.5);
                        $pdf->SetAligns(array('C','C','L','C','C','C','C','C','C','C','C','C','C','C','C','C'));
                        for($m = 0; $m < $l; $m++) {
                            $arr = $record[$i][$m];
                            if($i+1 != $N) {
                                $arr[sizeof($arr) - 1] = $record[$N - 1][$m][sizeof($record[$N-1][$m]) - 1];
                            }
                            $pdf->Row($arr);
                        } //end of for
                        $pdf->SetY(-33);
                        $pdf->SetFont('Arial','B', 9.5);
                        $pdf->SetFont('Arial','B', 9.5);
                        $pdf->Cell(69, 6, '',0,'','C');
                        $pdf->Cell(69, 6, '',0,'','C');
                        $pdf->Cell(69, 6, '',0,'','C');
                        $pdf->Cell(69, 6, 'Signature of',0,1,'C');
                        $pdf->Cell(69, 6, '',0,'','C');
                        $pdf->Cell(69, 6, '', 0, 0, 'C');
                        $pdf->Cell(69, 6, '', 0, 0, 'C');
                        $pdf->Cell(69, 6, 'Head of the Department', 0, 1, 'C');
                    } //end of for 
                    /*Emptying the generated record before fetching next set of record*/
                    $record = ''; $l = 0;
                } //end of if;
            }

            /*Displaying subject details*/
            $pdf->AddPage('L');
           /* if(!$locked) {
                $pdf->SetFont('Arial','B',50);
                $pdf->SetTextColor(255,192,203);
                $pdf->RotatedText(55,160,'P r o v i s i o n a l',45);
                $pdf->SetTextColor(0,0,0);
            } */
            $pdf->SetFont('Arial','B',15);
            $pdf->Cell(0,10,'Students Course Registered List',0,0,'C');
            $pdf->Ln(10);
            $pdf->printBox($header1, 'T');
            $pdf->printBox($header2, null);
            $pdf->printBox($header3, 'B');
            $n = round(sizeof($sub_array) / 2, 0,  PHP_ROUND_HALF_UP);
            $pdf->SetWidths(array(20, 93.5, 25, 20, 93.5, 25));
            $pdf->SetFont('Arial','B',9.5);
            $pdf->SetAligns(array('C','C','C','C','C','C'));
            $pdf->Row(array('Code', 'Course Name', 'Credits','Code', 'Course Name', 'Credits'));
            $pdf->SetFont('Arial','',9.5);
            $pdf->SetAligns(array('C','L','C','C','L','C'));
            for($i = 0; $i < $n; $i++) {
                $subject1 = $this->GM->get_subject_info($sub_array[$i]);
                if($i + $n < $TOTAL_SUBJECT) {
                    $subject2 = $this->GM->get_subject_info($sub_array[$i+$n]);
                    $pdf->Row(array($sub_array[$i],$subject1['subname'], $subject1['credit'],$sub_array[$i+$n],$subject2['subname'], $subject2['credit']));
                } else
                    $pdf->Row(array($sub_array[$i],$subject1['subname'], $subject1['credit'] )); 
            }
            $pdf->SetY(-33);
            $pdf->SetFont('Arial','B', 9.5);
            $pdf->Cell(69, 6, '',0,'','C');
            $pdf->Cell(69, 6, '',0,'','C');
            $pdf->Cell(69, 6, '',0,'','C');
            $pdf->Cell(69, 6, 'Signature of',0,1,'C');
            $pdf->Cell(69, 6, '',0,'','C');
            $pdf->Cell(69, 6, '', 0, 0, 'C');
            $pdf->Cell(69, 6, '', 0, 0, 'C');
            $pdf->Cell(69, 6, 'Head of the Department', 0, 1, 'C');
        }
        $pdf->output('Cumulative Registered Report.pdf','I');
    }

    function get_image($staffid, $web = FALSE) {
        $tnsname = '(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = 10.9.63.223)(PORT = 1521))
        (CONNECT_DATA = (SERVER = DEDICATED) (SERVICE_NAME = VIJI)))';
        $conn = oci_connect('kovalan', 'Kovalan1993', $tnsname);
       
        if (!$conn) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }
        
        $sql = "SELECT PHOTO FROM STAFFPHOTO WHERE STAFFID = ".$staffid;
        $query = oci_parse($conn, $sql);
        oci_execute($query); 
        
        $row = oci_fetch_array($query, OCI_ASSOC+OCI_RETURN_NULLS);
        if (!$row) {
            return null;
        } else {
            $img = $row['PHOTO']->load();
            if($web)
                header("Content-type: image/JPEG");
            return $img;
        }        
    }

    function get_rev_sub_and_count($sesid) {
        $staffid = $this->staffid;
        $data = array('STAFFID' => $staffid, 'SESID' => $sesid, 'STATUS >' => 1);
        $this->db->select('DISTINCT (TYPE), CAMCODE, PRGM, SUBCODE');
        $this->db->where($data);
        $query = $this->db->get('REVALUATION');
        if($query->num_rows() > 0) {
            $record = $query->result();
            $count = 0;
            foreach($record as $r) {
                $subject = $this->GM->get_subject_info($r->SUBCODE);
                $sub[$count]['subcode'] = $subject['subname'];
                $sub[$count]['count'] = $this->get_rev_assigned_student_count($r->CAMCODE, $r->PRGM, $r->SUBCODE, $staffid, $r->TYPE, $sesid);
                $sub[$count]['id'] = $r->CAMCODE.'/'.$r->PRGM.'/'.$r->TYPE.'/'.$r->SUBCODE.'/'.$staffid.'/'.$this->MOD->encode($sesid);
                $count++;
            }
            return array('result' => TRUE, 'msg' => $this->course_to_html($sub, 'revaluation/marks/'));
        }
        return array('result' => FALSE, 'msg' => 'No courses found for given session!');
    }

    function get_rev_assigned_student_count($camcode, $prgm, $subcode, $staffid, $type, $sesid) {       
        $data = array('CAMCODE' => $camcode, 'PRGM' => $prgm, 'SUBCODE' => $subcode, 'STAFFID' => $staffid, 'TYPE' => $type, 'SESID' => $sesid, 'STATUS > ' => 1);
        $this->db->select('COUNT(*) AS CNT');
        $this->db->where($data);
        $query = $this->db->get('REVALUATION');
        return $query->row()->CNT;
    }

    function is_auth_rev_sub($camcode, $prgm, $type, $subcode, $staffid, $sesid) {
        $data = array('CAMCODE' => $camcode, 'PRGM' => $prgm, 'TYPE' => $type, 'SUBCODE' => $subcode, 'STAFFID' => $staffid, 'SESID' => $sesid, 'STATUS > ' => 1);
        $this->db->select('COUNT(*) AS CNT');
        $this->db->where($data);
        $query = $this->db->get('REVALUATION');
        if($query->row()->CNT > 0) {
            return TRUE;
        } else return FALSE;
    }

    function get_rev_details($update, $camcode, $prgm, $type, $subcode, $staffid, $sesid) {
        $data = array('CAMCODE' => $camcode, 'PRGM' => $prgm, 'TYPE' => $type, 'SUBCODE' => $subcode, 'STAFFID' => $staffid, 'SESID' => $sesid, 'STATUS >' => 1);
        
        $this->db->select('REGNO, DUMMY, NEW_ESEM, STATUS');
        $this->db->where($data);
        $this->db->order_by('DUMMY, REGNO');
        $query = $this->db->get('REVALUATION');
        if($query->num_rows() > 0) {
            $i = 0; $table = '';
            foreach($query->result() as $row) {
                $j = 0; $action = '';
                //$student = $this->GM->get_student_info($row->REGNO);
				//Edited by Kulothungan
                $table[$i][$j++] = $row->REGNO;
				//$table[$i][$j++] = $row->DUMMY;
                //$table[$i][$j++] = $student['name'];
                if($update && $row->STATUS == 2) {
                    $table[$i++][$j] = '<input type="text" class="form-control-inline input-sm custom-num-filter" name="marks['.$row->DUMMY.']" placeholder="'.$row->REGNO.'" value="'.$row->NEW_ESEM.'">';
					//Edited by Kulothungan
					//$table[$i++][$j] = '<input type="text" class="form-control-inline input-sm custom-num-filter" name="marks['.$row->DUMMY.']" placeholder="'.$row->DUMMY.'" value="'.$row->NEW_ESEM.'">';
                } else {                    
                    $table[$i][$j++] = $row->NEW_ESEM;
                    if($row->STATUS == 2)
                        $status = '<button type="button" class="btn btn-xs cell-green"><i class="fa fa-unlock"></button>';
                    else
                        $status = '<button type="button" class="btn btn-xs cell-red"><i class="fa fa-lock"></button>';

                    $table[$i++][$j] = $status;
                }
            }            
            return array('result' => TRUE, 'msg' => $table);
        }
        return array('result' => FALSE, 'msg' => 'No assigned (or) un-locked students found!');
    }
    
    function review_update($camcode, $prgm, $type, $subcode, $staffid, $marks, $sesid) {
        if(!$this->MOD->is_write('RS', $sesid)) 
            return array('result' => FALSE, 'msg' => 'Cannot update. Session Locked!');
        $data = array('SUBCODE' => $subcode, 'STAFFID' => $staffid, 'TYPE' => $type, 'SESID' => $sesid, 'STATUS' => 2);
        foreach ($marks as $dummy => $esem) {
            $data['DUMMY'] = $dummy;
            $this->db->set(array('LTIME' => date('d-M-Y h:i:sa'), 'NEW_ESEM' => $esem));
            $this->db->where($data);
            $this->db->update('REVALUATION');
        }
        return array('result' => TRUE, 'msg' => 'Marks updated successfully!');
    }

    function revaluation_update($camcode, $prgm, $type, $subcode, $staffid, $marks, $sesid) {
        if(!$this->MOD->is_write('RS', $sesid)) 
            return array('result' => FALSE, 'msg' => 'Cannot update. Session Locked!');
        $data = array('SUBCODE' => $subcode, 'STAFFID' => $staffid, 'TYPE' => $type, 'SESID' => $sesid, 'STATUS' => 2);
        foreach ($marks as $dummy => $esem) {
            $data['DUMMY'] = $dummy;
            $this->db->set(array('LTIME' => date('d-M-Y h:i:sa'), 'NEW_ESEM' => $esem));
            $this->db->where($data);
            $this->db->update('REVALUATION');
        }
        return array('result' => TRUE, 'msg' => 'Marks updated successfully!');
    }

    function is_all_rev_filled($camcode, $prgm, $type, $subcode, $staffid, $sesid) {
        $data = array('SUBCODE' => $subcode, 'TYPE' => $type, 'STAFFID' => $staffid, 'SESID' => $sesid, 'STATUS' => 2);
        $data['NEW_ESEM'] = NULL;

        $this->db->where($data);
        $query = $this->db->get('REVALUATION');
        if($query->num_rows() > 0) {
            return FALSE;
        } else
            return TRUE;
    }

    function revaluation_lock($camcode, $prgm, $type, $subcode, $staffid, $sesid) {
        if(!$this->MOD->is_write('RS', $sesid)) 
            return array('result' => FALSE, 'msg' => 'Cannot lock. Session Locked!');
        if(!$this->is_all_rev_filled($camcode, $prgm, $type, $subcode, $staffid, $sesid))
            return array('result' => FALSE, 'msg' => 'Enter all REVALUATION marks before locking');

        $data = array('SUBCODE' => $subcode, 'TYPE' => $type, 'STAFFID' => $staffid, 'SESID' => $sesid, 'STATUS' => 2);
        $this->db->select('REGNO, MARKID, OLD_ESEM, NEW_ESEM');
        $this->db->where($data);
        $query = $this->db->get('REVALUATION');
        if($query->num_rows() > 0) {
            foreach($query->result() as $row) {
                $udata = '';
                $totmark = $grade = '';
                $r_status = $p_status = $avg = -1; 
                $diff = $change = $locked = FALSE;
                $old_esem = $row->OLD_ESEM;
                $mdetail = $this->GM->get_markid_details($row->MARKID);
                switch($type) {
                    case 'R1':
                        $d = $old_esem - $row->NEW_ESEM;
                        if($d < 0) $d *= -1;
                        if($d > 15) {
                            //Needs to go for revaluation #2
                            $diff = TRUE;
                        } else {
                            $avg = round (($old_esem + $row->NEW_ESEM) / 2, 2,  PHP_ROUND_HALF_UP);
                            $avg = $row->NEW_ESEM;
                            if($avg > $old_esem) {
                                $change = TRUE;
                            }
                        }
                        break;
                    case 'R2':
                        $where = array('REGNO' => $row->REGNO, 'SUBCODE' => $subcode, 'TYPE' => 'R1', 'SESID' => $sesid);
                        $rev1 = $this->GM->get_record('NEW_ESEM', $where, 'REVALUATION');
                        if($rev1 == ' ') return array('result' => FALSE, 'msg' => 'Invalid revaluation mark!');
                        /**close diff**/
                        $a = $old_esem - $rev1;
                        $b = $rev1 - $row->NEW_ESEM;
                        $c = $old_esem - $row->NEW_ESEM;
                        if($a < 0) $a *= -1;
                        if($b < 0) $b *= -1;
                        if($c < 0) $c *= -1;
                        if($a < $b && $a < $c) {
                            $one = $old_esem;
                            $two = $rev1;
                        } elseif($b < $a && $b < $c) {
                            $one = $rev1;
                            $two = $row->NEW_ESEM;
                        } else {
                            $one = $old_esem;
                            $two = $row->NEW_ESEM;
                        }
                        /** big 2 
                        $a = $old_esem + $rev1;
                        $b = $rev1 + $row->NEW_ESEM;
                        $c = $old_esem + $row->NEW_ESEM;
                        if($a > $b && $a > $c) {
                            $one = $old_esem;
                            $two = $rev1;
                        } elseif($b > $a && $b > $c) {
                            $one = $rev1;
                            $two = $row->NEW_ESEM;
                        } else {
                            $one = $old_esem;
                            $two = $row->NEW_ESEM;
                        }     
                        **/   
                        $avg =  round (($one + $two) / 2, 2,  PHP_ROUND_HALF_UP);
                        if($avg > $old_esem) {
                            $change = TRUE;
                        }
                        break;
                    case 'C1':
                        //Checking if C2 is locked
                        $where = array('REGNO' => $row->REGNO, 'SUBCODE' => $subcode, 'TYPE' => 'C2', 'SESID' => $sesid);
                        $col = $this->GM->get_multi_col_record('NEW_ESEM, STATUS', $where, 'REVALUATION');
                        if($col != ' ' && $col->STATUS > 2) {
                            $locked = TRUE;
                            if($col->NEW_ESEM == $row->NEW_ESEM) {
                                if($row->NEW_ESEM > $old_esem) {
                                    $avg = $row->NEW_ESEM;
                                    $change = TRUE;
                                }
                            } else {
                                return array('result' => FALSE, 'msg' => 'Challenge -> Staff #1 & Staff #2 mark must be same!');
                            }
                        }
                        break;
                    case 'C2':
                        //Checking if C1 is locked
                        $where = array('REGNO' => $row->REGNO, 'SUBCODE' => $subcode, 'TYPE' => 'C1', 'SESID' => $sesid);
                        $col = $this->GM->get_multi_col_record('NEW_ESEM, STATUS', $where, 'REVALUATION');
                        if($col != ' ' && $col->STATUS > 2) {
                            $locked = TRUE;
                            if($col->NEW_ESEM == $row->NEW_ESEM) {
                                if($row->NEW_ESEM > $old_esem) {
                                    $avg = $row->NEW_ESEM;
                                    $change = TRUE;
                                }
                            } else {
                                return array('result' => FALSE, 'msg' => 'Challenge -> Staff #1 & Staff #2 mark must be same!');
                            }
                        }
                        break;
                }
                
                if($diff) {
                    $r_status = 6; $p_status = 9;
                } elseif($change) {
                    //Checking if difference is signficant enough to affect grade!                    
                    $where = array('MARKID' => $row->MARKID, 'REGNO' => $row->REGNO);
                    $old = $this->GM->get_multi_col_record('SESMARK, RESULT', $where, 'PROCESSED_MARKS');
                    if($old == ' ')
                        return array('result' => FALSE, 'msg' => 'Failed fetching students records!');
                    $esem_weightage = $this->get_ses_weightage($row->MARKID, 'esem');
                    $totmark = round($avg * $esem_weightage / 100 , 2, PHP_ROUND_HALF_UP) + $old->SESMARK;
                    $mean = $this->get_mean($row->MARKID);
                    if($avg == 'P' || $this->PM->is_prevented($row->REGNO, $row->MARKID, $sesid)) {
                        $grade = 'SA';
                    } elseif($avg == 'A') {
                        $grade = 'AB';
                    } elseif($totmark < $mean['pass']) {
                        $grade = 'RA';
                    } elseif($totmark >= $mean['o']) {
                        $grade = 'O';
                    } elseif($totmark >= $mean['ap']) {
                        $grade = 'A+';
                    } elseif($totmark >= $mean['a']) {
                        $grade = 'A';
                    } elseif($totmark >= $mean['bp']) {
                        $grade = 'B+';
                    } 
					//elseif($totmark >= $mean['b']) 
					elseif($totmark >= 50){
                        $grade = 'B';
                    } else { 
                        $grade = 'RA';
                    }
                    if($grade != $old->RESULT) {
                        $r_status = 5;                                                
                        switch($type) {
                            case 'R1': 
                            case 'R2': $p_status = 13; break;
                            case 'C1': 
                            case 'C2':  $p_status = 20; break;
                        }
                    } else {
                        $r_status = 4;
                        switch($type) {
                            case 'R1': $p_status = 12; break;
                            case 'R2': $p_status = 12; break;
                            case 'C1': 
                            case 'C2':  $p_status = 19; break;
                        }
                    }
                    $udata = array('TOTMARK' => $totmark, 'NEW_RESULT' => $grade);
                } else {
                    $r_status = 4;
                    switch($type) {
                        case 'R1': $p_status = 12; break;
                        case 'R2': $p_status = 12; break;
                        case 'C1': 
                        case 'C2': 
                            if($locked) $p_status = 20;
                            else $r_status = 3;
                            break;
                    }
                }
                //Checking if status changed 
                if($r_status == -1) continue;

                $wdata = $data;
                $wdata['REGNO'] = $row->REGNO;
                $udata['ITIME'] = date('d-M-Y h:i:sa');
                $udata['STATUS'] = $r_status;
                if($avg != -1) $udata['AVG_ESEM'] = $avg;
                $this->db->set($udata);
                if($locked && $type == 'C1' || $type == 'C2') {
                    unset($wdata['STATUS']);
                    unset($wdata['STAFFID']);
                    unset($wdata['TYPE']);
                }
                $this->db->where($wdata);
                if($locked) {
                    $type_array = array('C1', 'C2');
                    $this->db->where_in('TYPE', $type_array);
                }
                $this->db->update('REVALUATION');

                $update = FALSE; $utype = '';      
                if($type == 'R1' || $type == 'R2') {
                    $utype = 'R';
                    if(!$diff) $update = TRUE;
                } elseif($type  == 'C1' || $type_array == 'C2') {
                    $utype = 'C';
                    if($locked) $update = TRUE;
                }

                if($update) {
                    if($change) $status = 1;
                    else $status = 0;
                    $where = array('REGNO' => $row->REGNO, 'MARKID' => $row->MARKID, 'TYPE' => $utype);
                    $col = $this->GM->get_record('STATUS', $where, 'REVALUATION_MARKS');
                    if($col == ' ') {
                        $student = $this->GM->get_student_info($row->REGNO);
                        $old_result = $this->GM->get_record('RESULT', array('REGNO' => $row->REGNO, 'MARKID' => $row->MARKID), 'PROCESSED_MARKS');
                        $record = array('REGNO' => $row->REGNO, 'MARKID' => $row->MARKID, 'SUBCODE' => $subcode, 'SESID' => $sesid, 'TYPE' => $utype, 'STATUS' => $status,
                        'OLD_ESEM' => $row->OLD_ESEM, 'NEW_ESEM' => $row->NEW_ESEM, 'AVG_ESEM' => $avg, 'TOTMARK' => $totmark, 'NEW_RESULT' => $grade, 'OLD_RESULT' => $old_result,
                        'ITIME' => date('d-M-Y h:i:sa'), 'CAMCODE' => $student['camcode'], 'PRGM' => $student['prgm'], 'DEPCODE' => $student['depcode'], 'BRCODE' => $student['brcode'],
                        'SEM' => $student['sem'], 'SUB_DEPCODE' => $mdetail['depcode'], 'SUB_BRCODE' => $mdetail['brcode'], 'SUB_SEM' => $mdetail['sem']);
                        $this->db->insert('REVALUATION_MARKS', $record);
                    } else {
                        $set = array('NEW_ESEM' => $row->NEW_ESEM, 'AVG_ESEM' => $avg, 'TOTMARK' => $totmark, 'NEW_RESULT' => $grade, 'STATUS' => $status, 'ITIME' => date('d-M-Y h:i:sa'));
                        $this->db->set($udata);
                        $this->db->where($where);
                        $this->db->update('REVALUATION_MARKS');
                    }
                }

                if($p_status == -1) continue;

                if(!$locked) {
                    unset($wdata['STATUS']);
                    unset($wdata['STAFFID']);
                    unset($wdata['TYPE']);
                }                

                $this->db->set(array('LTIME' => date('d-M-Y h:i:sa'), 'STATUS' => $p_status));
                $this->db->where($wdata);
                $this->db->update('PHOTOCOPY');
            }
            return array('result' => TRUE, 'msg' => 'Locked successfully');
        } else return array('result' => FALSE, 'msg' => 'No (unlocked) students found!');        
    }

    public function dept_stud_pdf($depcode, $prgm, $brcode, $sem) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_no_wm.php');

        $dept = $this->GM->get_dept_info($depcode);
        $campus = $this->GM->get_campus_info($dept['camcode']);
        $sno = 0;
        
        $header2 = array('Department', $dept['depname'], 'Regulation', '2015');        
        
        $harray = array('S.No.', 'Register Number', 'Name of the Student');
        $warray = array(20,50,120);
        $pdf = new PDF();
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();
        
        $first_page = $new_page = TRUE; $footer = FALSE;

        if($brcode == 0) {
            $data = array('DEPCODE' => $depcode, 'PRGM' => $prgm);
            $this->db->select('BRCODE');
            $this->db->from('BRANCH');
            $this->db->where($data);
            $where_clause = $this->db->get_compiled_select();
        }

        if($brcode != 0) {
            $data = array('BRCODE' => $brcode);
            if($sem != 0) $data['SEM'] = $sem;
        }

        $this->db->select('BRCODE, SEM, REGNO, NAME');
        if($brcode == 0)
            $this->db->where('BRCODE IN ('.$where_clause.')', NULL, FALSE);
        else
            $this->db->where($data);
        $this->db->order_by('BRCODE, SEM, REGNO');
        $query = $this->db->get('STUDENT');
        $brcode = $sem = 0;
        if($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                if($brcode != $row->BRCODE) {
                    $brcode = $row->BRCODE;
                    $sem = $row->SEM;
                    $new_page = TRUE;
                    $sno = 1;                    
                    $branch = $this->GM->get_branch_info($brcode);
                } elseif($sem != $row->SEM) {                    
                    $sem = $row->SEM;
                    $new_page = TRUE;
                    $sno = 1; 
                }
                if($new_page) {
                    $new_page = $first_page = FALSE;
                    $footer = TRUE;
                    $header1 = array('Campus', $campus['camname'],'Semester :', $sem);
                    $header3 = array('Branch', $branch['degshort'].' '.$branch['brname'].' [ '.$branch['degmode'].' ]','','');
                    $pdf->AddPage();
                    $pdf->SetFont('Arial','B',15);
                    $pdf->Cell(0, 10, 'Student Name List', 0, 0, 'C');
                    $pdf->Ln(10);
                    $pdf->printBox($header1, 'T');
                    $pdf->printBox($header2, null);
                    $pdf->printBox($header3, 'B');
                    $pdf->SetFont('Arial','B', 9.5);
                    $pdf->SetWidths($warray);
                    $pdf->SetAligns(array('C','C','C'));
                    $pdf->Row($harray);
                    $pdf->SetFont('Arial','', 9.5);
                    $pdf->SetAligns(array('C','C','L'));
                }
                
                $pdf->Row(array($sno++, $row->REGNO, $row->NAME));
                
                if($pdf->GetY() > 245){
                    $new_page = TRUE; $footer = FALSE;
                }
            }

            if($first_page) {
                $footer = TRUE;
                $pdf->AddPage();
                $pdf->SetFont('Arial','B',15);
                $pdf->Cell(0, 10, 'Student Name List', 0, 0, 'C');
                $pdf->Ln(10);
                $pdf->printBox($header1, 'T');
                $pdf->printBox($header2, null);
                $pdf->printBox($header3, 'B');
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->SetWidths($warray);
                $pdf->SetAligns(array('C','C','C'));
                $pdf->Row($harray);
                $pdf->SetFont('Arial','', 9.5);
                $pdf->SetAligns(array('C','C','L'));
            }

            if($footer) {
                $pdf->SetY(-40);
            }
        }
        $pdf->output($dept['depname'].' Student Name list.pdf','I');
    }
public function rev_foil_sheet_pdf($camcode, $prgm, $type, $subcode, $staffid, $sesid) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_no_wm.php');

        $staff = $this->GM->get_staff_info($staffid);
        $subject = $this->GM->get_subject_info($subcode);
        
        $harray = array('S.No.', 'Register No.', 'Marks', 'Marks in Words', 'S.No.', 'Register No.', 'Marks', 'Marks in Words');
        $warray = array(12, 25, 18, 40, 12, 25, 18, 40);
        switch($type) {
            case 'R1' : $title = 'Second Revaluation'; break;
            case 'R2' : $title = 'Third Revaluation'; break;
            case 'C1' :
            case 'C2' : $title = 'Challenge'; break;
            default : echo "Invalid Type."; return;
        }

        $pdf = new PDF();
        $pdf->SetTitle($title.' - Foil Sheet');
        $pdf->AliasNbPages();

        $data = array('CAMCODE' => $camcode, 'PRGM' => $prgm, 'SUBCODE' => $subcode, 'STAFFID' => $staffid, 'SESID' => $sesid);        
        $this->db->select('DUMMY, REGNO, NEW_ESEM, TOTMARK');
        $this->db->where($data);
        $this->db->order_by('DUMMY, REGNO');
        $query = $this->db->get('REVALUATION');
        $data['STATUS'] = 2; //Staff assigned status. i.e., unlocked status
        $count = $this->GM->get_multi_col_record('COUNT(*) AS N', $data, 'REVALUATION');
        if($count->N == 0) $locked = TRUE;
        else $locked = FALSE;
        if($query->num_rows() > 0) {
            $count = $query->num_rows();
            $carray = ''  ;
            $record = $query->result();
            $sno = 0;
            //$markInWords = new NumberFormatter("en", NumberFormatter::SPELLOUT);
            foreach($record as $r) {
                
                //$carray[$sno][0] = $r->DUMMY;
				$carray[$sno][0] = $r->REGNO;
                $carray[$sno++][1] = $r->NEW_ESEM;

            }
            $i_max = $count / 50;
            $sno = 0;
            for($i = 0; $i< $i_max; $i++) {
                $pdf->AddPage();
                if(!$locked) {
                    $pdf->SetFont('Arial','B',50);
                    $pdf->SetTextColor(255,192,203);
                    $pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
                    $pdf->SetTextColor(0,0,0);
                }
                //$pdf->SetFont('Arial','B',15);
                //$pdf->Cell(0,10,$title.' - '.'FOIL SHEET',0,0,'C');
                //$pdf->Ln(10);
                //$pdf->SetWidths($warray);
                //$pdf->SetFont('Arial','B',9.5);
                //$pdf->SetAligns(array('C','C','C','C','C','C','C','C','C'));
                //$pdf->Row($harray);
                //$pdf->SetAligns(array('C','C','C','L','C','C','C','L','C'));
                //$pdf->SetFont('Arial','',9.5);
				//newly added code for the subject detials
                $pdf->SetFont('Arial','B',12);
                $pdf->Cell(0,10,$title.' - '.'FOIL SHEET - Nov/Dec - 16',0,0,'C');
                $pdf->Ln(10);
               // $arr = array('Campus', $campus['camname'], 'Session', 'DEC - 16');
                //$pdf->printBox($arr, 'T');
               // $arr = array('Branch', $branch['degshort'].' '.$branch['brname'].' - [ '.$mdetail['batch'].' ]', 'Mode', $branch['degmode']);
                //$pdf->printBox($arr, '');
               // $arr = array('Code', $subcode, 'Credit :', $subject['credit'], 'Semester', $mdetail['sem']);
               // $pdf->printBox($arr, '');
                $arr = array('Course', $subject['subname'], 'Regulation', 2015);
                $pdf->printBox($arr, 'B');
                $pdf->SetWidths($warray);
                $pdf->SetFont('Arial','B',9.5);
                $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C'));
                $pdf->Row($harray);
                $pdf->SetAligns(array('C','C','C','L','C','C','C','L','C'));
                $pdf->SetFont('Arial','',9.5);
				//added from foil sheet for regular examination
                for($j = $sno; $j < $sno + 25; $j++) {
                    $sno1 = $j < $count ? $j+1 : '';
                    $sno2 = $j+25 < $count ? $j+26 : '';
                    if(isset($carray[$j][0])) {
                        $col1 = $carray[$j][0];
                        $col2 = $carray[$j][1]; 
                        if($col2=="A" || $col2=="P") $col3 = "ABSENT"; 
                        else
                        $col3 = $col2 != NULL ? strtoupper($this->convert_number_to_words($col2)) : '';
                    } else {
                        $col1 = '';
                        $col2 = '';
                        $col3 = '';
                    }
                    if(isset($carray[$j+25][0])) {
                        $col4 = $carray[$j+25][0];
                        $col5 = $carray[$j+25][1];
                        if($col5=="A" || $col5=="P") $col6 = "ABSENT"; 
                        else
                        $col6 = $col5 != NULL ? strtoupper($this->convert_number_to_words($col5)) : '';
                    } else {
                        $col4 = '';
                        $col5 = '';
                        $col6 = '';
                    }
                    
                    $pdf->Row(array($sno1, $col1, $col2, $col3, $sno2, $col4, $col5, $col6));
                }
                $sno += 50;
                $pdf->SetY(-51);
                
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(18.5, 6, 'Staff Id ','','','L');
                $pdf->Write(6, ': ');
                $pdf->SetFont('Arial','',10);
                $pdf->MultiCell(0, 6, trim($staff['staffid']),0,'');
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(20, 6, 'Staff Name : ','','','L');
                $pdf->SetFont('Arial','',10);
                $pdf->MultiCell(0, 6, trim($staff['staffname']),0,'');
                
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(18.5, 6, 'Date','','','L');
                $pdf->Write(6, ': ');
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(0,6, date("d-M-Y h:i:sa"),0,0,'L');
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(-60, 6, 'Signature of the Examiner', '', 1, 'C');
            }
        }
        $pdf->output($type.'Foil Sheet - '.$subcode.'.pdf','I');
    }
    
    public function EditList_pdf($camcode, $prgm, $type, $subcode, $staffid, $sesid) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/editlistfpdf.php');
        require(APPPATH .'plugins/editlistmc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/editlistpdf_no_wm.php');

        $staff = $this->GM->get_staff_info($staffid);
        $subject = $this->GM->get_subject_info($subcode);
        $dept = $this->GM->get_dept_info($depcode);
        $stud = $this->GM->get_student_info($regno);
        $branch = $this->GM->get_branch_info($brcode);
       
        $harray = array('S.No.','Marlkid','Dummyno','Subcode','Subname', 'Intmark', 'ExtMark', 'Total', 'Grade');
        $warray = array(12, 18, 18, 15, 50, 14, 14, 14, 15);
        $pdf = new PDF();
        $pdf->SetTitle('Review Edit List Foil Sheet');
        $pdf->AliasNbPages();

        $data = array('CAMCODE' => $camcode, 'PRGM' => $prgm, 'SUBCODE' => $subcode, 'STAFFID' => $staffid, 'SESID' => $sesid);        
        $this->db->select('MARKID, DUMMY, NEW_ESEM, TOTMARK, NEW_R');
        $this->db->where($data);
        $this->db->order_by('DUMMY, REGNO');
        $query = $this->db->get('REVALUATION');
        $data['STATUS'] = 2; //Staff assigned status. i.e., unlocked status
        $count = $this->GM->get_multi_col_record('COUNT(*) AS N', $data, 'REVALUATION');
        if($count->N == 0) $locked = TRUE;
        else $locked = FALSE;
        if($query->num_rows() > 0) {
            $count = $query->num_rows();
            $carray = ''  ;
            $record = $query->result();
            $sno = 0;
            //$markInWords = new NumberFormatter("en", NumberFormatter::SPELLOUT);
            foreach($record as $r) {
                
                //$carray[$sno][0] = $r->DUMMY;
				$carray[$sno][0] = $r->REGNO;
                $carray[$sno++][1] = $r->NEW_ESEM;

            }
            $i_max = $count / 50;
            $sno = 0;
            for($i = 0; $i< $i_max; $i++) {
                $pdf->AddPage();   
                $pdf->SetFont('Arial','B',12);
                $pdf->Cell(0,10,' - '.'FOIL SHEET - Nov/Dec - 16',0,0,'C');
                $pdf->Ln(10);
                $arr = array('Registerno',$stud['regno'],$stud['name'],'Department',$dept['deptname'],$branch['brname'],  'Regulation', 2015);
                $pdf->printBox($arr, 'B');
                $pdf->SetWidths($warray);
                $pdf->SetFont('Arial','B',9.5);
                $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C'));
                $pdf->Row($harray);
                $pdf->SetAligns(array('C','C','C','L','C','C','C','L','C'));
                $pdf->SetFont('Arial','',9.5);
				//added from foil sheet for regular examination
                for($j = $sno; $j < $sno + 25; $j++) {
                    $sno = $j < $count ? $j+1 : '';

                    if(isset($carray[$j][0])) {
                        $col1 = $carray[$j][0];
                     
                        if($col2=="A" || $col2=="P") $col3 = "ABSENT"; 
                        else
                        $col3 = $col2 != NULL ? strtoupper($this->convert_number_to_words($col2)) : '';
                    } else {
                        $col1 = '';
                        $col2 = '';
                        $col3 = '';
                    }
                    if(isset($carray[$j+25][0])) {
                        $col4 = $carray[$j+25][0];
                        $col5 = $carray[$j+25][1];
                        if($col5=="A" || $col5=="P") $col6 = "ABSENT"; 
                        else
                        $col6 = $col5 != NULL ? strtoupper($this->convert_number_to_words($col5)) : '';
                    } else {
                        $col4 = '';
                        $col5 = '';
                        $col6 = '';
                    }
                    
                    $pdf->Row(array($sno1, $col1, $col2, $col3, $sno2, $col4, $col5, $col6));
                }
                $sno += 50;
                $pdf->SetY(-51);
                
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(18.5, 6, 'Staff Id ','','','L');
                $pdf->Write(6, ': ');
                $pdf->SetFont('Arial','',10);
                $pdf->MultiCell(0, 6, trim($staff['staffid']),0,'');
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(20, 6, 'Staff Name : ','','','L');
                $pdf->SetFont('Arial','',10);
                $pdf->MultiCell(0, 6, trim($staff['staffname']),0,'');
                
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(18.5, 6, 'Date','','','L');
                $pdf->Write(6, ': ');
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(0,6, date("d-M-Y h:i:sa"),0,0,'L');
                $pdf->SetFont('Arial','B', 9.5);
                $pdf->Cell(-60, 6, 'Signature of the Examiner', '', 1, 'C');
            }
        }
        $pdf->output($type.'Foil Sheet - '.$subcode.'.pdf','I');
    }
   public function cgpa_report_pdf($brcode) {
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_no_wm.php');

        $warray = array(12, 25, 83, 20, 20, 15, 15);
        //$mdetail = $this->markid_details($markid);
        //$staff = $this->GM->get_staff_info($mdetail['staffid']);
        $branch = $this->GM->get_branch_info($brcode);
        $dept = $this->GM->get_dept_info($branch['depcode']);
        $campus = $this->GM->get_campus_info($branch['camcode']);
        //$subject = $this->GM->get_subject_info($mdetail['subcode']);
        //$ses_weightage = $this->get_ses_weightage($markid, 'ses');
        //$esem_weightage = $this->get_ses_weightage($markid, 'esem');
        
        
        //$harray = array('S.No.', 'Register No.', 'Name of the Student', 'SEM', 'Credits', 'GPA', 'CGPA');

        $pdf = new PDF();
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();
        $pdf->AddPage();
		$harray = array('S.No.', 'Register No.', 'Name of the Student', 'SEM', 'Credits', 'GPA', 'CGPA');
        //$locked = $this->get_mark_status($markid) == 5 ? TRUE : FALSE;
        /*if(!$locked) {
        	$pdf->SetFont('Arial','B',50);
            $pdf->SetTextColor(255,192,203);
            $pdf->RotatedText(35,190,'P r o v i s i o n a l',45);
            $pdf->SetTextColor(0,0,0);
        }*/
        $pdf->SetFont('Arial','B',15);
        $pdf->Cell(0,10,'Branchwise - CGPA Sheet',0,0,'C');
        $pdf->Ln(10);
       
        $arr = array('Campus', $campus['camname'], 'Session', 'NOV - 17');
        $pdf->printBox($arr, 'T');
        $arr = array('Branch', $branch['degshort'].' '.$branch['brname'].'', 'Mode', $branch['degmode']);
        //$pdf->printBox($arr, '');
        //$arr = array('Code', $mdetail['subcode'], 'Credit :', $subject['credit'], 'Semester', $mdetail['sem']);
        //$pdf->printBox($arr, '');
        //$arr = array('Course', $subject['subname'], 'Regulation', 2015);
        $pdf->printBox($arr, 'B');
        $pdf->SetWidths($warray);
        $pdf->SetAligns(array('C','C','C','C','C','C','C'));
        $pdf->SetFont('Arial','B',9.5);
        $pdf->Row($harray);
        $pdf->SetFont('Arial','',9.5);
        $pdf->SetAligns(array('C','C','L','C','C','C','C'));
        
		
		$data = array('BRCODE' => $brcode);
        $this->db->select('REGNO, SEM, CREDITS_EARNED, GPA, CGPA');
        $this->db->where($data);
        $this->db->order_by('REGNO');
		$this->db->order_by('SEM');
        $query = $this->db->get('FINAL_GPA');
		
		
        
        if($query->num_rows() > 0) {
            $record = $query->result(); 
            $sno = 1;
            //$regular = $shortage = $reappear = '';
           // $reg_count = $sa_count = $ra_count = 0;
            foreach($record as $r) {   
                            
               $student = $this->GM->get_student_info($r->REGNO);
			    $col = $this->GM->get_multi_col_record('MAX(SEM) AS SEM', array('REGNO' => $r->REGNO), 'FINAL_GPA');
                        if($col != ' ' && $col->SEM != null) {
                            $max_sem = $col->SEM;
							//var_dump($max_sem);
			   if($r->SEM ==$max_sem){
			   
               $pdf->Row(array($sno++, $r->REGNO, $student['name'], $r->SEM, $r->CREDITS_EARNED, $r->GPA, $r->CGPA));
			     $pdf->Row(array('', '', '', '', '', '', '')); 
               }
						
						
			   else{
				  
				     
					$pdf->Row(array($sno++, $r->REGNO, $student['name'], $r->SEM, $r->CREDITS_EARNED, $r->GPA, $r->CGPA));
			   }
						}					
            }
			
            //$pdf->Row(array($sno++, $r->REGNO, $student['name'], $r->SESMARK, $r->ESEM, $r->TOTMARK, $r->RESULT));


            if($pdf->GetY() < 250) {
                    $pdf->Ln(2);
                    
                    $pdf->SetY(-31);
                    $pdf->SetFont('Arial','B', 9.5);
				    $pdf->SetX(100);
                    $pdf->Cell(120,5,'DCOE(DC)',0,0);                 
			        $pdf->Cell(0, 0,'' , '', 1, 0);
                    $pdf->Cell(120,5,'Verification',0,0);
					$pdf->Cell(120, 5, 'ACOE(UDs)', '', 1, 'C');
					
            }
        }
        $pdf->output($brcode.'.pdf','I');
    }
	
	// added for complete report on 06-01-18
	    public function get_report_all_status($brcode, $sem, $sesid) {
        $data = array('PRGM' => 'U.G','SUBLINK_SEM' => $sem, 'SESID' => $sesid);
        $this->db->select('DISTINCT (SUBCODE), MARKID, STAFFID');
        $this->db->where($data);
        $this->db->order_by('SUBCODE, MARKID');
        $query = $this->db->get('REGISTER');
        if($query->num_rows() > 0) {
            $table = '';
            $i = 0;
            foreach($query->result() as $r) {
                $j = 0;
                $status = $this->get_mark_status($r->MARKID);
                $subject = $this->GM->get_subject_info($r->SUBCODE);
                $staff = $this->GM->get_staff_info($r->STAFFID);
                $estatus = $this->is_attlocked($r->MARKID, 'esem');
                $atttlock = $this->get_attlock_time($r->MARKID, 'result');
				
				// add by kulothunga to find the grade status
				$resstatus = $this->is_attlocked($r->MARKID, 'result');
                $table[$i][$j++] = $r->SUBCODE;
                $table[$i][$j++] = $subject['subname'];
                $table[$i][$j++] = $staff['staffname'];
                $table[$i][$j++] = $staff['dept'];
                if($status < 1)
                    $table[$i][$j++] = '<span class="label cell-green">NL</span>';
                else
                    $table[$i][$j++] = '<span class="label cell-red">L</span>';
                if($status < 2)
                    $table[$i][$j++] = '<span class="label cell-green">NL</span>';
                else
                    $table[$i][$j++] = '<span class="label cell-red">L</span>';
                if($this->PM->is_locked($r->MARKID)) {
                    $table[$i][$j++] = '<span class="label cell-red">L</span>';
                }
                else
                    $table[$i][$j++] = '<span class="label cell-green">NL</span>';
                if(!$estatus)
                    $table[$i][$j++] = '<span class="label cell-green">NL</span>';
                else
                    $table[$i][$j++] = '<span class="label cell-red">L</span>';
                // add by kulothunga to find the grade status
                if(!$resstatus)
                    $table[$i][$j++] = '<span class="label cell-green">NL</span>';
                else
                    $table[$i][$j++] = '<span class="label cell-red">L</span>';
                $table[$i++][$j] = $atttlock;
            }

            return array('result' => 'true', 'msg' => $table);
        } else {
            return array('result' => 'false', 'msg' => 'Invalid branch or sem');
        }
    }
	
	//question paper related functions 11-1-2018
	
	

//Question paper upload model
	
	public function get_subcode($staffid) {

$condition = "STAFFID =" . "'" . $staffid . "'";
$this->db->select('SUBCODE');
$this->db->from('SUBLINK');
$this->db->where($condition);

$query = $this->db->get();

if ($query->num_rows() >0) {
return $query->result();
} else {
return false;
}
}


public function get_staffdet($id){

	$condition = "QPID =" . "'" . $id . "'";
	$this->db->select('STAFFID,SUBCODE');
	$this->db->from('STAFF_ASSIGN_QP');
	$this->db->where($condition);

	$query = $this->db->get();

		if ($query->num_rows() >0) {
			return $query->result();
			} 
			else {
			return false;
				}
			}	
public function get_qp_details($staffid)
		{
				$sql = "select a.qpid,a.subcode,b.subname,c.mobile from STAFF_ASSIGN_QP a, NONCBCSSUBJECTS b,STAFF_PERSONAL c where a.staffid=c.empid and a.staffid=$staffid and a.subcode=b.subcode and STATUS=0";
				$query1 = $this->db->query($sql);
			
			if ($query1->num_rows() >0) {
					return $query1->result();
					} 
					else {
						return false;
						}

					
		}
public function get_qp_complete($staffid)
		{
				$sql = "select a.qpid,a.subcode,b.subname,c.mobile,a.status from STAFF_ASSIGN_QP a, NONCBCSSUBJECTS b,STAFF_PERSONAL c where a.staffid=c.empid and a.staffid=$staffid and a.subcode=b.subcode and STATUS NOT IN (0)";
				$query1 = $this->db->query($sql);
			
			if ($query1->num_rows() >0) {
					return $query1->result();
					} 
					else {
						return false;
						}

					
		}
		/**
		public function get_qp_complete($staffid)
		{
				$sql = "select a.qpid,a.subcode,b.subname,c.mobile from STAFF_ASSIGN_QP a, NONCBCSSUBJECTS b,STAFF_PERSONAL c where a.staffid=c.empid and a.staffid=$staffid and a.subcode=b.subcode and STATUS=1";
				$query1 = $this->db->query($sql);
			
			if ($query1->num_rows() >0) {
					return $query1->result();
					} 
					else {
						return false;
						}

					
		}
		*/
	public function get_staff_details($staffid)
		{
		
		$sql= "select name,mobile from STAFF_PERSONAL WHERE EMPID=$staffid";
	
			//$sql = "select a.qpid,a.subcode,c.subname,b.name,b.mobile from STAFF_ASSIGN_QP a,staff_personal b,noncbcssubjects c where a.subcode=c.subcode AND a.staffid= $staffid AND STATUS=0";
			$query2 = $this->db->query($sql);
			
			if ($query2->num_rows() >0) {
					return $query2->result();
					} 
					else {
						return false;
						}

		}
					
	public function get_qpid($staffid,$subcode)
		{
	$condition = "STAFFID = " . "'".$staffid. "' AND " . "SUBCODE =" . "'$subcode'";
	$this->db->select('QPID');
	$this->db->from('STAFF_ASSIGN_QP');
	$this->db->where($condition);
	$query = $this->db->get();

		if ($query->num_rows() >0) {
			return $query->result();
			} 
			else {
			return false;
				}
			}
	
public function get_subname($subcode) {
	
	//var_dump($subcode);
$condition = "SUBCODE =" . "'" . $subcode . "'";
$this->db->select('SUBNAME');
$this->db->from('NONCBCSSUBJECTS');
$this->db->where($condition);
$query = $this->db->get();

if ($query->num_rows() >0) {
	
return $query->row();

} else {
return false;
}
}





// QP Model for OTP and Upload

public function upload_photo($id,$zfile,$otp,$user_ip,$sesid) 
	{   
	//echo $id;
	 
		
			
	$tnsname = '(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = 10.9.63.223)(PORT = 1521))
        (CONNECT_DATA = (SERVER = DEDICATED) (SERVICE_NAME = XE)))';
        $conn = oci_connect('kovalan', 'Kovalan1993', $tnsname);
		
		
	$lob = oci_new_descriptor($conn, OCI_D_LOB);
	
	
	$sql = "INSERT INTO QP_UPLOAD (QPID,FILEDATA,UPLOAD_DATE,OTP,USER_IP,SESID)VALUES ('$id',:FILEDATA,TO_CHAR(SYSDATE, 'MM-DD-YYYY HH:MI:SS'),'$otp','$user_ip','$sesid')";
	
	$s = oci_parse($conn, $sql);
	
	oci_bind_by_name($s, ':FILEDATA', $lob, -1, OCI_B_BLOB);
    
	$myv = file_get_contents($zfile);
	 
	$lob->writeTemporary($myv, OCI_TEMP_BLOB);
	 
	$r = oci_execute($s, OCI_NO_AUTO_COMMIT);
	 
	oci_commit($conn);
	
	$lob->close();
if ($r == 1) {
return true;
} else {
return false;
}	



	
    }
	
	public function get_staff_name($staffid) {
        $data = array('EMPID' => $staffid);
        $this->db->select('NAME','EMAIL','MOBILE');
        $this->db->where($data);
        $query = $this->db->get('STAFF_PERSONAL');
       // $row = $query->row();
        return $query->result();
    }
	
	

	public function checkcode($data)
    {
		//$this->db->where($data);
		$condition = "QPID = " . "'" . $data . "'";
		//$condition = "STAFFID =" . "'" . $data['staffid'] . "' AND " . "SUBCODE =" . "'" . $data['subcode'] . "'";
		$this->db->select('*');
		$this->db->from('STAFF_ASSIGN_QP');
		$this->db->where($condition);
		$this->db->limit(1);
		$query = $this->db->get();
		if ($query->num_rows() == 1) 
		{
			return true;
		} 
		else 
		
		
		{
return false;
}

}

	public function checkotp($data,$data1)
	{
		$sql = "select * from OTP_CHECK where QPID = $data and OTP = $data1 ";
		$query1 = $this->db->query($sql);
		if ($query1->num_rows() == 1) 
		{
			return true;
		}
		else
		{
			return false;
		}
	
	}

	public function checkstatus($data)
	{
		$sql = "update STAFF_ASSIGN_QP set status=1 where QPID = $data ";
		$query1 = $this->db->query($sql);
	
	}


	public function checkupload($data)
    {
		$condition = "QPID = " . "'".$data. "' AND " . "STATUS =" . "'1'";
		$this->db->select('*');
		$this->db->from('STAFF_ASSIGN_QP');
		$this->db->where($condition);
		$this->db->limit(1);
		$query = $this->db->get();

		if ($query->num_rows() == 1) 
		{
			return true;
		} 
		else
		{
			return false;
		}

	}

	public function get_uploadstatus($staffid)
	{
	
		$sql = "select * from STAFF_ASSIGN_QP where STAFFID = $staffid ";
		$query1 = $this->db->query($sql);
		if ($query1->num_rows() == 1) 
		{
			return true;
		} 
		else 
		{
			return false;
		}
	
	}


	public function otpstore($data) 
	{
		$query = $this->db->insert('OTP_CHECK', $data);

		$afftectedRows = $this->db->affected_rows();
		if($afftectedRows == 1)
		{
			return true;
		}
		else
		{
			return false;
		}

	}
	
	public function get_staffname($staffid) 
	{

		$condition = "STAFFID =" . "'" . $staffid . "'";
		$this->db->select('STAFFNAME');
		$this->db->from('STAFF');
		$this->db->where($condition);
		$query = $this->db->get();
		if ($query->num_rows() >0) 
		{
			return $query->row();

		} 
		else 
		{
			return false;
		}
	}
	public function get_staffmobile($staffid) 
	{

		$condition = "EMPID =" . "'" . $staffid . "'";
		$this->db->select('MOBILE');
		$this->db->from('STAFF_PERSONAL');
		$this->db->where($condition);
		$query = $this->db->get();
		if ($query->num_rows() >0) 
		{
			return $query->row();

		} 
		else 
		{
			return false;
		}
	}
	public function get_load($data) 
	{
		
		$sql = "select A.SUBCODE , B.SUBNAME from STAFF_ASSIGN_QP A,NONCBCSSUBJECTS B where QPID = $data AND A.SUBCODE = B.SUBCODE";
		$query1 = $this->db->query($sql);
		if ($query1->num_rows() == 1) 
		{
			return $query1->result();
		} 
		else 
		{
			return false;
		}

	}
	
	public function mark_entry($data1) 
	{
		
		$query = $this->db->insert('MARKENTRY_OTP', $data1);

		$afftectedRows = $this->db->affected_rows();
		if($afftectedRows == 1)
		{
			return true;
		}
		else
		{
			return false;
		}

	}
	
	
	public function mark_status($staffid) 
	{
		
		$sql = "update MARKENTRY_OTP set STATUS=0 where STAFFID = $staffid";
$query1 = $this->db->query($sql);
		

	}
	
	public function mark_otpcheck($markid1,$uotp)
	{
		//$condition = "MARKID =" . "'" . $markid1 . "' AND " . "OTP =" . "'" . $uotp . "'". "' AND " STATUS = 1;
		
		$condition = array('MARKID' => $markid1, 'OTP' => $uotp, 'STATUS' => 1);
		$this->db->select('*');
		$this->db->from('MARKENTRY_OTP');
		$this->db->where($condition);
		//$this->db->limit(1);
		
		$query = $this->db->get();
	// $query->num_rows();
		
		if ($query->num_rows() == 1) 
		{
			return true;
		} 
		else
		{
			return false;
		}
	
	}
	
	
	public function qp_keyupload($qpid,$zfile,$otp,$sesid,$date,$user_ip)
	{
	/*	$query = $this->db->insert('QP_KEY_UPLOAD', $data);
		
		$afftectedRows = $this->db->affected_rows();
		if($afftectedRows == 1)
		
		{
			return true;
		}
		else
		{
			return false;
		}*/
            $tnsname = '(DESCRIPTION = (ADDRESS = (PROTOCOL = TCP)(HOST = 10.9.63.223)(PORT = 1521))
        (CONNECT_DATA = (SERVER = DEDICATED) (SERVICE_NAME = XE)))';
        $conn = oci_connect('kovalan', 'Kovalan1993', $tnsname);
		
		
		
	$lob = oci_new_descriptor($conn, OCI_D_LOB);
	
	
	$sql = "INSERT INTO QP_KEY_UPLOAD (QPID,FILEDATA,UPLOAD_DATE,OTP,USER_IP,SESID)VALUES ($qpid,:FILEDATA,'$date',$otp,'$user_ip',$sesid)";
	
	$s = oci_parse($conn, $sql);
	
	
	oci_bind_by_name($s, ':FILEDATA', $lob, -1, OCI_B_BLOB);
    
	$myv = file_get_contents($zfile);
	 
	$lob->writeTemporary($myv, OCI_TEMP_BLOB);
	 
	$r = oci_execute($s, OCI_NO_AUTO_COMMIT);
	echo $r;
	 
	oci_commit($conn);
	
	$lob->close();
		if ($r == 1) {
			return true;
		} else {
			return false;
		}
		
		
		
		
	}
	
	public function qp_keystatus($qpid)
	{
		$data = array('QPID' => $qpid );
		$this->db->set('STATUS' , 2);
            $this->db->where($data);
            $this->db->update('STAFF_ASSIGN_QP');
		
	}
	public function qpclaim_pdf($id)
	{
	
	
	
		define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
    
		$row = $this->get_staffdet($id);
		$subname = $this->get_subname($row[0]->SUBCODE);
		//$staff = $this->get_staffname($row[0]->STAFFID);
		$data = array('STAFFID' => $row[0]->STAFFID);
		$this->db->select('STAFFNAME, DESG, DEPCODE');
        $this->db->where($data);
		$query = $this->db->get('STAFF');
		$sdet =  $query->result();
		$dept = $this->GM->get_dept_info($sdet[0]->DEPCODE);
		$pdf = new FPDF();
		$colHeight = 8;
        $serialWidth = 7;
        $labelWidth = 65;
        $colonWidth = 5;
        $contentWidth = 75;
       
        $pdf->SetTitle('CBCS R2015');
        $pdf->AliasNbPages();
        $pdf->AddPage();
		//$pdf->Rect(10,190,190,30);
		$pdf->Rect(15,15,180,158.5);
		$pdf->Image(base_url().'assests/img/anna-univ.jpg',20,20,20,0);
		
		
		$pdf->Image(base_url().'/assests/img/acoe.jpg',170,20,14,0);
		$pdf->SetFont('Times','B',11);
		$pdf->Cell(0,25,'OFFICE OF THE ADDITIONAL CONTROLLER OF EXAMINATIONS',0,0,'C');
		$pdf->Ln(5);
		$pdf->Cell(0,25,' (UNIVERSITY DEPARTMENTS)',0,0,'C');
		$pdf->Ln(5);
		$pdf->Cell(0,25,' ANNA UNIVERSITY:: CHENNAI 25',0,0,'C');
		$pdf->Ln(5);
        $pdf->Cell(0,25,'Honorarium for Question Paper Setting including Typing Charges ',0,0,'C');
		$pdf->Ln(10);
		$pdf->SetFont('Times','',11);
		$pdf->Cell(0,25,'UG / PG (FT/PT) Examinations - August 2018 ',0,0,'C');
		
        $pdf->Line(15,50,195,50);
		
		$pdf->Line(15,65,195,65);
		$pdf->Line(15,80,195,80);
		$pdf->Line(15,95,195,95);
		$pdf->Line(15,115,195,115);
		$pdf->Line(43,50,43,115);
		$pdf->Line(120,50,120,95);	
		$pdf->Line(150,50,150,95);	
		$pdf->Line(150,58,195,58);	
		$pdf->Line(150,73,195,73);	
		$pdf->Line(150,88,195,88);	
		$pdf->SetXY(16,50);
		$pdf->Cell(0,15,'Name','',0,1);
		$pdf->SetXY(45,50);
		$pdf->Cell(0,15,$sdet[0]->STAFFNAME,'',0,1);
		$pdf->SetXY(16,65);
		$pdf->Cell(0,15,'Designation','',0,1);
		$pdf->SetXY(45,65);
		$pdf->Cell(0,15,$sdet[0]->DESG,'',0,1);
		
		
		$pdf->SetXY(16,80);
		$pdf->Cell(0,15,'Department','',0,1);
		$pdf->SetFont('Times','',8);
		$pdf->SetXY(45,80);
		$pdf->Cell(0,15,$dept['depname'],'',0,1);
		$pdf->SetXY(16,95);
		
		$pdf->Cell(0,15,'Branch','',0,1);
		$pdf->SetFont('Times','',10);
		$pdf->SetXY(120,50);
		$pdf->Cell(0,8,'SBI A/c.No','',0,1);
		$pdf->SetXY(120,50);
		$pdf->Cell(0,23,'IFS Code No.','',0,1);
		$pdf->SetXY(120,65);
		$pdf->Cell(0,8,'Canara A/c.No','',0,1);
		$pdf->SetXY(120,65);
		$pdf->Cell(0,23,'IFS Code No.','',0,1);
		$pdf->SetXY(120,80);
		$pdf->Cell(0,8,'Indian Bank A/c.No','',0,1);
		$pdf->SetXY(120,80);
		$pdf->Cell(0,23,'IFS Code No.','',0,1);
		$pdf->Line(130,95,130,165);	
		$pdf->SetXY(145,96.5);
		$pdf->Cell(0,7,'REGULAR/ARREAR','',0,1);
		$pdf->Line(130,102,195,102);
		$pdf->SetXY(142,105.5);
		$pdf->Cell(0,3,'FULL TIME/PART TIME','',0,1);
		$pdf->Line(130,109,195,109);
		$pdf->SetXY(140,111);
		$pdf->Cell(0,3,'(Tick Whichever is applicable)','',0,1);
		$pdf->Line(15,165,195,165);//Total
		$pdf->Line(15,127,195,127);
		
		$pdf->SetXY(15,115);
	//	$pdf->Cell(0,10,'Sem','',0,1);
	//$pdf->SetXY(31,115);
		$pdf->Cell(0,10,'Course Code','',0,1);
		$pdf->Line(35,115,35,165);
		$pdf->SetXY(80,115);
		$pdf->Cell(0,10,'Course Title','',0,1);
		$pdf->Line(150,115,150,173);
		$pdf->SetXY(132,115);
		$pdf->Cell(0,10,'No.of QP','',0,1);
		$pdf->Line(170,115,170,173);
		$pdf->SetXY(150,115);
		$pdf->Cell(0,10,'Rate per QP','',0,1);
		//$pdf->Image('http://10.9.63.227/cgi/assests/img/rupayy.jpg',159,123,2,0);
		$pdf->SetXY(174,115);
		$pdf->Cell(0,10,'Amount','',0,1);
		//$pdf->Image('http://10.9.63.227/cgi/assests/img/rupayy.jpg',181,123,2,0);
		$pdf->SetXY(17,137);
		$pdf->SetFont('Times','',9);
		$pdf->Cell(0,10,$row[0]->SUBCODE,'',0,1);
		
		$pdf->SetXY(35,137);
		$pdf->Cell(0,10,$subname->SUBNAME,'',0,1);
		$pdf->SetXY(137,137);
		$pdf->SetFont('Times','',11);
		$pdf->Cell(0,10,'1','',0,1);
		$pdf->SetXY(174,137);
		$pdf->Cell(0,10,'1000/-','',0,1);
		
		
		$pdf->SetXY(160,165);
		$pdf->Cell(0,10,'Total','',0,1);
		$pdf->SetXY(174,165);
		$pdf->Cell(0,10,'1000/-','',0,1);
		
		$pdf->SetXY(155,137);
		$pdf->Cell(0,10,'1000/-','',0,1);
		$pdf->SetXY(16,182);
		$pdf->SetFont('Times','',11);
		$pdf->Cell(0,3,'Received a sum of','',0,1);
		//$pdf->Image('http://10.9.63.227/cgi/assests/img/rupayy.jpg',47,180.5,2,0);
		$pdf->SetXY(48,182);
		$pdf->Cell(0,3,'. ...........................(Rupees ................................................................................................only)','',0,1);
		$pdf->SetXY(16,187);
		$pdf->SetFont('Times','',10);
		$pdf->Cell(0,3,'and will be shown in my IT Statement.','',0,1);
		$pdf->SetFont('Times','',12);
		$pdf->SetXY(88,194);
		$pdf->Cell(0,3,'UNDERTAKING','',0,1);
		$pdf->Line(89,197.5,118,197.5);
		$pdf->SetFont('Times','',10);
		$pdf->SetXY(16,200);
		$pdf->Cell(0,3,'a) I hereby certify that the question paper was typed by me and I nave not retained any copy of the same in any form.','',0,1);
		$pdf->SetXY(16,205);
		$pdf->Cell(0,3,'b) I hereby certify that I have taken sufficient care to destroy the draft copy used and have deleted the relevant files  ','',0,1);
		$pdf->SetXY(20,210);
		$pdf->Cell(0,3,'from my computer taking care to ensure it is not retrievable by any means.','',0,1);
		$pdf->SetXY(16,215);
		$pdf->Cell(0,3,'c) I hereby certify that I have utmost care to maintain the confidentiality.','',0,1);
		$pdf->SetXY(16,220);
		$pdf->Cell(0,3,'d) I am certifying that none of my relatives are either studying in or appearing for the Examination for which question','',0,1);
		$pdf->SetXY(20,225);
		$pdf->Cell(0,3,' paper has been set by me.','',0,1);
		
		$pdf->SetXY(16,235);
		$pdf->Cell(0,3,'Date:','',0,1);
		$pdf->SetXY(175,235);
		$pdf->Cell(0,3,'Signature','',0,1);
		$pdf->SetXY(16,240);
		$pdf->Cell(0,3,'.....................................................................................................................................................................................................','',0,1);
		$pdf->SetXY(93,250);
		
		$pdf->Cell(0,3,'Office Use','',0,1);
		$pdf->Line(94,253,110,253);
		$pdf->SetXY(16,258);
		$pdf->Cell(0,3,'Passed for ','',0,1);
	//	$pdf->Image('http://10.9.63.227/cgi/assests/img/rupayy.jpg',34,258.4,2,0);
		$pdf->SetXY(35,258);
		$pdf->Cell(0,3,'.................................(Rupees ...............................................................................................................................) ','',0,1);
		$pdf->SetXY(179,272);
		$pdf->Cell(0,3,'DCOE','',0,1);
		
		
		
		
		
		
		
		 $pdf->output();
		
	}
		
}
