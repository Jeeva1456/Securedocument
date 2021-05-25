<?php
 
defined('BASEPATH') OR exit('No direct script access allowed');
 
/**
 * Description of Blog
 *
 * @author http://www.roytuts.com
 */
class Secure_Controller extends CI_Controller {
 
    public function __construct() {
        parent::__construct();
		$this->load->model('Auth_model');
                  if(!$this->Auth_model->is_auth_user('lecturer')) {   
                       if(!$this->Auth_model->is_auth_user('hod') ) {
                             if(!$this->Auth_model->is_auth_user('principal') ) {   
                              echo $this->load->view('access_denied', '', TRUE);
				die();
			    }
                        }
                  }
	
                $this->load->model('Data_model', 'DM');
		$this->load->library('session'); 
                
		
    }
 
    public function index()
    {  
                             
      $this->load->view('quesition_paper_print_screen');   
    }       
     public function quesition_paper(){
         
                      
                       
                                        
      $otp=$this->lecturer_otp();
      $this->DM->print_screen($otp);

                                                
     }    
      public function lecturer_otp() 
      {   
                   $mobile='';
                   $email='';
                   if($this->session->userdata('is_lecturer')==TRUE)
                   {

                        $info = $this->DM->staff_info('LECTURER');
                        $mobile=$info['mobile'];
                        $email=$info['email'];
                   }

                   $n = 18;
                   $characters = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                   $randomString = '';
                   $part = '';
                        for ($i = 0; $i < $n; $i++) {
                            $index = rand(0, strlen($characters) - 1);
                            $randomString .= $characters[$index];
                        }
                   $split  =  str_split("$randomString", 6);
                   $lecturer =  $split[0];
                   $hod =  $split[1];
                   $principal   =  $split[2];
                   $array = array($principal, $hod ,$lecturer);
                   shuffle($array);
                   $rnd1 = $array[0];
                   $rnd2 = $array[1];
                   $rnd3 = $array[2];
                     if($rnd1===$lecturer)
                     {

                      $part=1;
                      $otp1 = urlencode($rnd1);
                     }
                     if($rnd1===$hod)
                     {

                       $part=2;
                       $otp2 = urlencode($rnd1);

                     }
                     if($rnd1===$principal)
                     {

                      $part=3;
                      $otp3 = urlencode($rnd1);
                     } 

                   //$fp = fopen('C:\Users\koval\OneDrive\Desktop\New folder\New Text Document.txt', 'a');
                  //fwrite($fp,$randomString);
                 // fclose($fp);

              
                            if($part===1){

                              $message = urlencode('This is PART 1 OTP for Secure Document Printing: '.$otp1.' by ACOE(Us).Dont share this with anyone. ');
                              $this->send_mail($email,$otp1,$part);

                            }
                             if($part===2){

                              $message = urlencode('This is  PART 2 OTP for Secure Document Printing: '.$otp2.' by Indhumathy& Team.Dont share this with anyone. ');
                              $this->send_mail($email,$otp2,$part);

                             }
                             if($part===3){

                              $message = urlencode('This is  PART 3 OTP for Secure Document Printing: '.$otp3.' by Indhumathy& Team.Dont share this with anyone. ');
                              $this->send_mail($email,$otp3,$part);

                             } 
                  
                   $this->session->set_userdata('principalotp', $principal);
                   $this->session->set_userdata('hodotp', $hod);
                   $this->session->set_userdata('lecturerotp', $lecturer);
                   $this->session->set_userdata('rnd2', $rnd2);
                    $this->session->set_userdata('rnd3', $rnd3);
                   $this->hod_otp();
                   $this->principal_otp();
                   return $randomString;
    }
 public function hod_otp()
 {
                       
                $mobile='';
                $email='';
               if($this->session->userdata('is_hod')==TRUE)
              {
                  $info = $this->DM->staff_info('HOD');
                  $mobile=$info['mobile'];
                  $email=$info['email'];
              }
             
         
              $message = '';
              $part = '';
              $lecturer =$this->session->userdata('lecturerotp');
              $hod = $this->session->userdata('hodotp');
              $principal =$this->session->userdata('principalotp');
              $rnd2=$this->session->userdata('rnd2');

                if($rnd2 === $lecturer)
                {

                 $part=1;
                 $otp1 = urlencode($rnd2);
                }
                if($rnd2 === $hod)
                {

                  $part=2;
                 $otp2 = urlencode($rnd2);

                }
                if($rnd2 === $principal)
                {

                 $part=3;
                 $otp3 = urlencode($rnd2);
                } 

            
                if($part===1){
                $message = urlencode('This is PART 1 OTP for Secure Document Printing: '.$otp1.' by Indhumathy& Team.Dont share this with anyone. ');
                 $this->send_mail($email,$otp1,$part);
                }
                if($part===2){
                $message = urlencode('This is  PART 2 OTP for Secure Document Printing: '.$otp2.' by Indhumathy& Team.Dont share this with anyone. ');
                 $this->send_mail($email,$otp2,$part);
                }
                if($part===3){
                $message = urlencode('This is  PART 3 OTP for Secure Document Printing: '.$otp3.' by Indhumathy& Team.Dont share this with anyone. ');
                 $this->send_mail($email,$otp3,$part);
                } 
        
    }
public function principal_otp()
 {
                       
                $mobile='';
                $email='';
                 if($this->session->userdata('is_principal')==TRUE)
               {

                    $info = $this->DM->staff_info('PRINCIPAL');
                    $mobile=$info['mobile'];
                    $email=$info['email'];

               }
             
               $part = '';
               $message = '';
               $lecturer =$this->session->userdata('lecturerotp');
               $hod = $this->session->userdata('hodotp');
               $principal =$this->session->userdata('principalotp');
               $rnd3 =$this->session->userdata('rnd3');
                  if($rnd3===$lecturer)
                 {

                 $part=1;
                 $otp1 = urlencode($rnd3);
                 }
                 if($rnd3===$hod)
                 {

                 $part=2;
                 $otp2 = urlencode($rnd3);
                 }
                 if($rnd3===$principal)
                 {

                 $part=3;
                  $otp3 = urlencode($rnd3);
                 } 
             
                 if($part===1){
                 $message = urlencode('This is PART 1 OTP for Secure Document Printing: '.$otp1.' by Indhumathy& Team.Dont share this with anyone. ');
                 $this->send_mail($email,$otp1,$part);
                 }
                 if($part===2){
                 $message = urlencode('This is  PART 2 OTP for Secure Document Printing: '.$otp2.' by Indhumathy& Team.Dont share this with anyone. ');
                 $this->send_mail($email,$otp2,$part);
                 }
                 if($part===3){
                 $message = urlencode('This is  PART 3 OTP for Secure Document Printing: '.$otp3.' by Indhumathy& Team.Dont share this with anyone. ');
                 $this->send_mail($email,$otp3,$part);
                 } 
                
    }
    
    public function send_mail($email,$otp,$part)
    {   

                 $this->load->library('phpmailer_lib');
     
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
                $mail->Subject    = "Secure Document Printing Password";
                $mail->Body      = "<html>
                                    <head>
                                    <title>Secure Document Password</title>
                                    </head>
                                    <body>
                                    <p>This is Part-$part OTP for Secure Document Printing</p><br>
                                        <h3>$otp</h3>
                                    <p>With Regards</p>
                                    <p>Indhumathy & Team</p>
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
  
}
?>