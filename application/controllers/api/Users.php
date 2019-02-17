<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
//To Solve File REST_Controller not found
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';
require APPPATH . 'libraries/ImplementJWT.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver

 */
class Users extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Request-Method: OPTIONS, POST, GET, PUT, DELETE');

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key

        $this->objOfJwt = new ImplementJWT();
    }

    public function get_data(){

    }
    public function login_post(){
        $credentials = array(
            'username' =>  $this->post('username'),  // Which is School ID btw
            'password' => $this->post('password')
        );
        $result = $this->user->login($credentials);

        if ($result[0]){
            $session = array (
                'id' => $result[1]->user_school_id,
                'type' => $result[1]->user_type,
                'college' => $result[2]->ui_college,
                'department' => $result[2]->ui_dept,
                'position'=> $result[2]->ui_position,
                'fname' => $result[2]->ui_Fname,
                'mname' => $result[2]->ui_Mname,
                'lname' => $result[2]->ui_Lname
            );
            // $this->session->set_userdata($session);
            // $tokenData['id'] = $result[1]->user_school_id;
            // $tokenData['type'] = $result[1]->user_type;
            $jwtToken = $this->objOfJwt->GenerateToken($session);
            $result[3] = $jwtToken;

            // $this->session->sess_destroy();

            // var_dump($_SESSION);
        }
        // var_dump(session_id());
        $this->response($result);
    }
    
    public function registration_get(){
        // For checking if the user exists
    }
    public function register_post(){
        // For registering new users
        // var_dump($this-  >post());
        $info = array (
            'ui_school_id' => $this->post('id'),
            'ui_college' => $this->post('college'),
            'ui_dept' => $this->post('dept'),
            'ui_email' => $this->post('email'),
            'ui_position' => $this->post('position'),
            'ui_Fname'=> $this->post('fName'),
            'ui_Mname'=> $this->post('mName'),
            'ui_Lname'=> $this->post('lName'),
            'ui_gender'=> $this->post('gender'),
            //'ui_age'=> $this->post(''),
            'ui_contact_number'=> $this->post('cNumber'),
            'ui_birthday' => $this->post('bDay')
        );

        $cred = array (
            'user_school_id' => $this->post('id'),
            'user_pass' => $this->post('pass'),
            'user_type' => $this->post('type'),
        );



        $result = $this->user->register($info,$cred);
        $this->response($result);
        
    }

    public function get_user_post(){
        $result = $this->user->get_user($this->post('id'));

        $this->response($result);
    }

    public function getall_get(){
        $result = $this->user->get_allusers();
        $this->response($result);
    }

    public function check_login_post(){
        // var_dump($_SESSION);
        $response = [];
        $user_data = [];

        $jwtData = $this->objOfJwt->DecodeToken($this->post('token'));


        $result = $this->user->check($jwtData['id']);

        if($result){
            $response[0] = true;
            array_push($user_data, $jwtData['type']);
            array_push($user_data, $jwtData['fname']);
            array_push($user_data, $jwtData['mname']);
            array_push($user_data, $jwtData['lname']);
            $response[1] = $user_data;
        }else{
            $response[0] = false;
        }

        $this->response($response);
    }

    public function submit_proposal_post(){
        $response = [];
        $jwtData = $this->objOfJwt->DecodeToken($this->post('token'));
        $proposal_details = array(
            'user_id' => $jwtData['id'],
            'proposal_title' => $this->post('title'),        
            'proposal_beneficiaries' => $this->post('b_target'),        
            'proposal_bene_gender' => $this->post('b_gender'),        
            'proposal_date_start' =>$this->post('date_start'),        
            'proposal_date_end' => $this->post('date_end'),
            'proposal_program' => $this->post('program'),        
            'proposal_venue' => $this->post('venue'),
            'type_id' => $this->post('trans_type'),
            'proposal_partner'=> $this->post('partner'),
            'proponents'=> $this->post('proponents'),
            'accreditation_level'=> $this->post('accre_level'),
            'total_hours'=> $this->post('total_hours'),
            'budget_ustp'=> $this->post('budget_ustp'),
            'budget_partner'=> $this->post('budget_partner')        
        );

        $result = $this->user->submit_proposal($proposal_details,$jwtData['college']);
        $this->response($result);

    }

        public function update_proposal_post(){
        $response = [];
        $jwtData = $this->objOfJwt->DecodeToken($this->post('token'));
        $proposal_details = array(
            'user_id' => $jwtData['id'],
            'proposal_title' => $this->post('title'),        
            'proposal_beneficiaries' => $this->post('b_target'),        
            'proposal_bene_gender' => $this->post('b_gender'),        
            'proposal_date_start' =>$this->post('date_start'),        
            'proposal_date_end' => $this->post('date_end'),
            'proposal_program' => $this->post('program'),        
            'proposal_venue' => $this->post('venue'),
            'proposal_directory' => '../../../assets/uploaded_files/'.$jwtData['college'].'/'.$jwtData['department'].'/'.$this->post('title').'/',
            'type_id' => $this->post('trans_type'),
            'proposal_partner'=> $this->post('partner'),
            'proponents'=> $this->post('proponents'),
            'accreditation_level'=> $this->post('accre_level'),
            'total_hours'=> $this->post('total_hours'),
            'budget_ustp'=> $this->post('budget_ustp'),
            'budget_partner'=> $this->post('budget_partner'),
            'proposal_status' => 0
        );

        $result = $this->user->update_proposal($proposal_details,$this->post('prop_id'));
        // $response[1] = $result;
        $this->response($result);
        // $this->response(true);

    }
        
    public function getNotifs_post(){
        $result = $this->user->get_notifs($this->post('id'));

        $this->response($result);
    }



    public function file_upload_post(){
        // var_dump($_POST);
        // var_dump($_FILES);
        $data = array(
            'id' => $this->post('id'),
            'file_type' => $this->post('file_type'),
            'folder' => $this->post('folder')
        );

        $result = $this->user->upload_file($data);

        $this->response($result);
    }



    ////Admin Fucntions

    public function get_des_proposals_get(){
        $result = $this->admin->get_des_proposals();

        $this->response($result);
    }
    public function get_proposals_get(){
        $result = $this->admin->get_proposals();

        $this->response($result);
    }

    public function get_proposal_post(){
        $id = $this->post('id');
        $result = $this->admin->get_proposal($id);
        $this->response($result);
    }

    public function get_revised_proposals_get(){
        $result = $this->admin->get_revised_proposals();
        $this->response($result);

    }
    public function get_revised_proposals_post(){
        $result = $this->admin->get_revised_proposals($this->post('user_id'));
        $this->response($result);
    }

    public function get_events_get(){
        $result = $this->admin->get_events();
        $this->response($result);
    }

    public function get_events_post(){
        $college = $this->post('college');

        $result = $this->admin->get_events($college);
        $this->response($result);
    }

    public function proposal_approval_post(){
        // var_dump($this->post());
        $approval = array (
            'id' => $this->post('id'),
            'status' => $this->post('decision'),
            'title' => $this->post('title')
        );
        $result = $this->admin->proposal_approval($approval);

        $this->response($result);
    }





    public function get_transactions_post(){
        $result = $this->user->get_transactions($this->post('id'));

        $this->response($result);
    }

    public function get_prexc_post(){
        $result = $this->admin->get_prexc($this->post('quarter'));
        $this->response($result);

    }
    public function get_hemis_post(){
        $result = $this->admin->get_hemis($this->post('quarter'));
        $this->response($result);

    }

    public function get_unregistered_get(){
        $result = $this->admin->get_unregistered();
        $this->response($result);
    }

    public function approve_registration_post(){
        $data = array(
            'id' => $this->post('id'),
            'status' => $this->post('status')
        );

        $result = $this->admin->approve_registration($data);

        $this->response($result);
    }

    public function update_project_status_post(){
        $data = array(
            'id' => $this->post('id'),
            'status' => $this->post('status')
        );
        $result = $this->admin->update_project_status($data);

        $this->response($result);
    }

    public function moa_c_upload_post(){
        // var_dump($this->post());
        $data = array(
            'id' => $this->post('id'),
            'folder' => $this->post('folder'),
            'prop_id' => $this->post('prop_id'),
            'user_id' => $this->post('user_id'),
        );
        $result = $this->admin->moa_c_upload($data);

        $this->response($result);
    }

    public function revise_proposal_post(){
        $data = array(
            'prop_id' =>  $this->post('prop_id'),
            'comment' => $this->post('comment')
        );

        $result = $this->admin->revise_proposal($data);

        $this->response($result);
    }

    public function get_proposals_user_post(){
        $result = $this->user->get_proposals_user($this->post('user_id'));

        $this->response($result);
    }

    public function implementation_status_post(){
        $data = array(
            'prop_id' => $this->post('prop_id'),
            'status' => $this->post('status')
        );
        $result = $this->user->implementation_status($data);
        $this->response($result);
    }

    public function update_user_post(){
        $id = $this->post('id');
        $info = array (
            'ui_email' => $this->post('email'),
            'ui_contact_number'=> $this->post('cNumber')
        );

        $cred = array (
            'user_pass' => $this->post('pass'),
        );

        $result = $this->user->update_user($info,$cred,$id);
        $this->response($result);
    }
    
    public function upload_photo(){
        $data = array(
            'user_id' => $this->post('user_id'),
        );
        $result = $this->admin->upload_photo($data);

        $this->response($result);
    }

    public function update_report_post(){
        $data = array (
            'persons_trained' => $this->post('p_trained'),
            'days_conducted' => $this->post('day_comp'),
            'rate_satisfactory' => $this->post('rate_s'),
            'rate_v_satisfactory' => $this->post('rate_vs'),
            'rate_excellent' => $this->post('rate_e'),
            'proposal_status' => 5
        );

        $result = $this->user->update_report($data,$this->post('id'));

        $this->response($result);
    }

    public function update_notification_post(){
        $id = $this->post('id');

        $result = $this->user->update_notifs($id);

        $this->response($result);
    }

    public function lockout_post(){
        $id = $this->post('id');

        $result = $this->user->lockout($id);

        $this->response($result);
    }

    public function get_project_count_get(){
        $result = $this->admin->get_project_count();

        $this->response($result);

    }

    public function try_get(){

        $handle = fopen('zip://localhost/files.rar#1.pdf', 'r'); 
        $result = '';
        while (!feof($handle)) {
          $result .= fread($handle, 8192);
        }
        fclose($handle);
        
        // $result = file_get_contents('zip://localhost/files.rar#1.pdf');

        // $result = file_get_contents('zip://test.zip#test.txt');

        $this->response($result);

    }

    public function archive_db_get(){
        $date = date('Y-m');

        $result = $this->admin->archive_db();

        

        $this->response($result);
    }

    public function archive_db_post(){
        $date = date('Y m');



        $this->response($date);
    }

}
