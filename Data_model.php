<?php

Class Data_model extends CI_Model
{
    function __construct() {
        parent::__construct();
      
    }
    
  
       public function print_screen($otp) {
            
        define('FPDF_FONTPATH',APPPATH .'plugins/font/');
        require(APPPATH .'plugins/fpdf.php');
        require(APPPATH .'plugins/mc_table.php');
        require(APPPATH .'plugins/rotate.php');
        require(APPPATH .'plugins/pdf_no_wm.php');
        $pdf = new FPDF_Protection('P', 'mm','A4');  
        $pdf->AddPage('P', 'A4');
        $pdf->SetProtection(array('print'),$otp);
        $pdf->SetMargins(5, 2, 5, 2);//(5, 2, 5, 2)
        $pdf->AliasNbPages();
       
      $pdf->SetAutoPageBreak(true, 10);
              $pdf->SetFont('Arial', '', 12);
              $pdf->SetTopMargin(10);
              $pdf->SetLeftMargin(10);
              $pdf->SetRightMargin(10);


              /* --- MultiCell --- */
              /* --- Cell --- */
              $pdf->SetXY(59, 26);
              $pdf->SetFont('', 'B', 12);
              $pdf->Cell(107, 7, ' C PROGRAMMING LAB - APRIL/MAY  2021', 0, 1, 'L', false);
              /* --- Cell --- */
              $pdf->SetXY(21, 40);
              $pdf->Cell(166, 8, 'B.Tech Information Technology', 0, 1, 'C', false);
              /* --- Cell --- */
              $pdf->SetXY(81, 54);
              $pdf->Cell(33, 8, 'Semester 2', 0, 1, 'L', false);
              /* --- Cell --- */
              $pdf->SetXY(68, 67);
              $pdf->SetFont('', 'B', 12);
              $pdf->Cell(59, 7, 'C1107 C Programming Lab', 0, 1, 'L', false);
              /* --- Cell --- */
              $pdf->SetXY(77, 77);
              $pdf->Cell(41, 9, '(Regulation 2018)', 0, 1, 'L', false);
              $pdf->SetXY(21, 90);

              $pdf->Cell(81, 9, 'Time:3 hours', 0, 1, 'L', false);

              $pdf->SetXY(70, 90);

              $pdf->Cell(81, 9, 'Answer All Questions', 0, 1, 'L', false);


              $pdf->SetXY(140, 90);

              $pdf->Cell(81, 9, 'Max Marks:100', 0, 1, 'L', false);

                                     
                                     
              $pdf->SetXY(21, 110);

              $pdf->Cell(210, 9, '1. Write a C program to implement arithmetic operations using switch case (25 Marks)', 0, 1, 'L', false);
                                 
                          
                                      
              $pdf->SetXY(21, 130);

              $pdf->Cell(210, 9, '2.Write a C Program to implement find Fibbanacci series upto 2000(25 Marks)', 0, 1, 'L', false);
                                 
                    
                                    
              $pdf->SetXY(21, 150);

              $pdf->Cell(210, 9, '3.Write a  C program to implement Quick Sorting Algorithm(50 Marks)', 0, 1, 'L', false);
                                 
                               
              
               
          
                   
               
                
        
      
      $pdf->Output('quesition.pdf','I');
    }

     public function staff_info($role)
     {
        $sql="SELECT MOBILE,EMAIL FROM STAFF_LOGIN WHERE ROLE='$role'";
        $query = $this->db->query($sql);
        if($query->num_rows()>0)
        {
             
            $res['email']=$query->row()->EMAIL;
            $res['mobile']=$query->row()->MOBILE;
            
        }
        else 
        {
            $res['email']='';
            $res['email']='';
        }
        
        return $res;
     }
     
     
     

}
?>