<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class user_m extends CI_Model{
	public function __construct(){
		parent::__construct();
	}
	public function register($info, $cred){
		$id = $cred['user_school_id'];
		$pass = $cred['user_pass'];

		$query = $this->db->select('*')->from('tbluser')->where('user_school_id',$id)->get();

		if ($query->num_rows() > 0){
			return 0;
		}else{
			$register_query1 = $this->db->insert('tbluserinfo',$info);
			$register_query2 = $this->db->insert('tbluser',$cred);

			if ($register_query1 && $register_query2){
				$log = array(
					'id' => $id,
					'type' => 'Registration'
				);
				$this->save_log($log);


				return 1;
			}else{
				return 2;
			}
		}

	}

	public function update_user($info, $cred,$id){

		$query = $this->db->select('*')->from('tbluser')->where('user_school_id', $id)->get();

		if ($query->num_rows() > 0){
			$this->db->where('tbluserinfo.ui_school_id',$id);
			$update_info = $this->db->update('tbluserinfo',$info);


			$this->db->where('tbluser.user_school_id',$id);
			$update_pass = $this->db->update('tbluser',$cred);


			if ($update_info && $update_pass){
				return true;
			}else{
				return false;
			}

		}

		

	}

	public function get_proposals_user($id){
		$return = [];
		$this->db->select('tblproject_proposals.*,tbluserinfo.*');
		$this->db->from('tblproject_proposals');
		$this->db->join('tbluserinfo', 'tbluserinfo.ui_school_id = tblproject_proposals.user_id');
		$this->db->where('tblproject_proposals.user_id',$id);
		$this->db->where('tblproject_proposals.proposal_status != 0');
		$query = $this->db->get()->result();

		if($query){
			foreach ($query as $key) {
				$key->budget_total = $key->budget_ustp + $key->budget_partner;
			}
			$return[0] = true;
			$return[1] = $query;
		}else{
			$return[0] = false;

		}

		return $return;

	}

	public function login($data){
		$response = [];
		$query =  $this->db->select('*')->from('tbluser')->where('user_school_id',$data['username'])->where('user_pass',$data['password'])->get();
		if ($query->num_rows() == 1){
			$log = array(
				'id' => $data['username'],
				'type' => 'Login'
			);

			$info =  $this->db->select('*')->from('tbluserinfo')->where('ui_school_id',$data['username'])->get();
			$this->save_log($log);

			if ($query->row()->approved == 1){
				$response[0] = true;
				$response[1] = $query->row();
				$response[2] = $info->row();
				$response['message'] = 'Successfully Logged In';
			}else if ($query->row()->approved == 0){
				$response[0] = false;
				$response['message'] = 'Your Account has not been approved yet. Please wait for confirmation' ;
			}else{
				$response[0] = false;
				$response['message'] = 'Your request to register an account has been denied for some reason. Please visit the Extension Office to reconcile this matter';
			}

			
			return $response;
		}else{
			$response[0] = false;
			$response['message'] = 'Username/Password Incorrect. Please check your credentials entered and try again';
			return $response;
		}

	}

	public function check($id){
		$query = $this->db->select('*')->from('tbluser')->where('user_school_id',$id)->get();
		if ($query->num_rows() > 0){
			return true;
		}else{
			return false;
		}
	}

	public function get_allusers(){
		return($this->db->get('tbluser')->result());
	}

	public function get_user($id){
		$this->db->select('tbluserinfo.*,tbluser.user_pass');
		$this->db->from('tbluserinfo');
		$this->db->join('tbluser','tbluserinfo.ui_school_id = tbluser.user_school_id');
		$this->db->where('tbluserinfo.ui_school_id',$id);

		$query = $this->db->get()->row();

		if($query){
			return $query;
		}else{
			return false;
		}
	}





	//////////////////// Transactions ///////////////////////////////////
	public function submit_proposal($details,$college){
		$p_details = $this->db->insert('tblproject_proposals',$details);


		if ($p_details){
			$log = array(
				'id' => $details['user_id'],
				'type' => $college.':'.'Proposal Submission'
			);
			$this->save_log($log);

			$event = array (
				'start' => date('c'),
				'title' => $college.':'.$details['proposal_title'],
				'color' => 0,
				'college' => $college
			);
			$this->add_event($event);

			$notif = array (
				'notification_sender' => $details['user_id'],
				'notification_receiver' => '2015101246',
				'notification_status' => 0,
				'notif_type_id' => 1
			);
			$this->set_notifs($notif);



			return true;
		}else{
			return false;
		}


	}
	public function update_proposal($details,$id){
		$this->db->where('tblproject_proposals.proposal_id',$id);
		$p_details = $this->db->update('tblproject_proposals',$details);

		$query = $this->db->select('tbluserinfo.ui_college')->from('tbluserinfo')->where($details['user_id'])->get()->row();


		if ($p_details){
			$log = array(
				'id' => $details['user_id'],
				'type' => $query.':'.'Proposal Submission Revision'
			);
			$this->save_log($log);


			$notif = array (
				'notification_sender' => $details['user_id'],
				'notification_receiver' => '2015101246',
				'notification_status' => 0,
				'notif_type_id' => 4
			);
			$this->set_notifs($notif);



			return true;
		}else{
			return false;
		}


	}

	public function save_log($log){
		$query = $this->db->insert('tbltrans_log',
			array(
				'user_id' => $log['id'], 
				'log_type' => $log['type']
			)
		);
	}

	public function add_event($event){
		$query = $this->db->insert('tbl_events', $event);
	}
	
	public function set_notifs($notif){
		$query = $this->db->insert('tblnotification',$notif);
	}

	public function get_notifs($id){
		$response = [];
		$this->db->select('tblnotification.*,tbluserinfo.*');
		$this->db->from('tblnotification');
		// $this->db->join('tblnotif_type','tblnotif_type.notif_type_id = tblnotification.notif_type_id');
		$this->db->join('tbluserinfo','tbluserinfo.ui_school_id = tblnotification.notification_sender');
		$this->db->where('tblnotification.notification_receiver',$id);
		$query = $this->db->get()->result();
		if ($query){
			$response[0] = true;
			$response[1] = $query;
		}

		return $response;
	}

	public function update_notifs($id){
		$this->db->where('tblnotification.notification_receiver',$id);
		$query = $this->db->update('tblnotification', array('notification_status' => 1));

		if($query){
			return true;
		}else{
			return false;
		}

	}

	public function lockout($id){
		$this->db->where('tbluser.user_school_id',$id);
		$query = $this->db->update('tbluser', array('approved' => 3));


		if($query){
			$log = array(
				'id' => $id,
				'type' => $query.':'.'Locked Out'
			);
			$this->save_log($log);


			$notif = array (
				'notification_sender' => $id,
				'notification_receiver' => '2015101246',
				'notification_status' => 0,
				'notif_type_id' => 6
			);
			$this->set_notifs($notif);
			return true;
		}else{
			return false;
		}
	}


	public function upload_file($data){
		$query = $this->db->select('*')->from('tbluserinfo')->where('ui_school_id',$data['id'])->get()->row();
		if ($query){
			if (isset($_FILES['upload0'])) {
            $file_info = pathinfo($_FILES['upload0']['name']);                    // Uploaded Image Info
            $maxsize = 2097152;             // Restricts 2MB images only
            $bool_image_size = true;               // Stores boolean value for image size
            $bool_image_type = true;               // Stores boolean value for image type/format
            $errors[0]="";$errors[1]="";    // Stores string value of error/s


            if($bool_image_size && $bool_image_type) {
                $ext = $file_info['extension']; // get the extension of the file or the file type
                // $newname = $_SESSION['id'].'_pic.'.$ext; 
                $filename = $data['folder'];
                $newname = $data['file_type'].'.'.$ext; 

                if (!file_exists('C:/xampp/htdocs/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$filename.'/')) {
					mkdir('C:/xampp/htdocs/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$filename.'/', 0777, true);
                	$target = 'C:/xampp/htdocs/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$filename.'/'.$newname;
				}else if(file_exists('C:/xampp/htdocs/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$filename.'/'.$newname)){
                	$target = 'C:/xampp/htdocs/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$filename.'/'.$newname;
                	unlink($target);
				}else{
                	$target = 'C:/xampp/htdocs/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$filename.'/'.$newname;
				}

                    move_uploaded_file( $_FILES['upload0']['tmp_name'], $target);
                    $this->db->where('tblproject_proposals.proposal_title',$filename);
		            if ($data['file_type'] == "proposal"){
		            	$cover_update = $this->db->update('tblproject_proposals',array(
		            		'proposal_directory' => '../../../assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$filename.'/'.$newname,
		            	));
		            }else{
		            	$cover_update = $this->db->update('tblproject_proposals',array(
		            		'report_directory' => '../../../assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$filename.'/'.$newname,
		            	));
		            }                           

            } 
            else {
            	return 'Some Error Occured';
            }
            return 'File Upload Success';
        }

		}
		
	}

	public function upload_photo($data){
		if ($query){
			if (isset($_FILES['upload'])) {
            $file_info = pathinfo($_FILES['upload']['name']);                    // Uploaded Image Info
            $maxsize = 2097152;             // Restricts 2MB images only
            $bool_image_size = true;               // Stores boolean value for image size
            $bool_image_type = true;               // Stores boolean value for image type/format
            $errors[0]="";$errors[1]="";    // Stores string value of error/s


            if($bool_image_size && $bool_image_type) {
                $ext = $file_info['extension']; // get the extension of the file or the file type
                // $newname = $_SESSION['id'].'_pic.'.$ext; 
                $filename = $data['user_id'];
                $newname = 'profile_pic'.'.'.$ext; 

                if (!file_exists('C:/Users/Acer/Desktop/offyc/src/assets/img/'.$filename.'/')) {
					mkdir('C:/Users/Acer/Desktop/offyc/src/assets/img/'.$filename.'/', 0777, true);
                	$target = 'C:/Users/Acer/Desktop/offyc/src/assets/img/'.$filename.'/'.$newname;
				}else if(file_exists('C:/Users/Acer/Desktop/offyc/src/assets/img/'.$filename.'/'.$newname)){
                	$target = 'C:/Users/Acer/Desktop/offyc/src/assets/img/'.$filename.'/'.$newname;
                	unlink($target);
				}else{
                	$target = 'C:/Users/Acer/Desktop/offyc/src/assets/img/'.$filename.'/'.$newname;
				}

                    move_uploaded_file( $_FILES['upload']['tmp_name'], $target);
                    $this->db->where('tbluserinfo.ui_school_id',$data['user_id']);
		            $cover_update = $this->db->update('tbluserinfo',array(
		            		'photo_directory' => '../../../assets/img/'.$filename.'/'.$newname,
		            	));                           

            } 
            else {
            	return 'Some Error Occured';
            }
            return 'File Upload Success';
        }

		}
		
	}

	public function get_transactions($id){
		$return = [];
		$query = $this->db->select('*')->from('tbltrans_log')->where('user_id',$id)->get()->result();

		foreach ($query as $row) {
			if($row->log_type != "Login"){
				array_push($return, $row);
			}
		}
		return $return;
	}

	public function implementation_status($data){
		$this->db->where('tblproject_proposals.proposal_id',$data['prop_id']);
		$query = $this->db->update('tblproject_proposals',array('implementing'=>$data['status']));

			if($query){
				if($data['status'] == 2){
					$status = [];
					$status['prop_id'] = $data['prop_id'];
					$status['status'] = 4;
					if($this->update_project_status($status)){
						return true;
					}

				}else{
					return true;
				}
			}else{
				return true;
			}
	}

	public function update_project_status($data){
		$status = $data['status'];
		$this->db->where('tblproject_proposals.proposal_id',$data['prop_id']);
		$this->db->where("tblproject_proposals.proposal_status < '$status'");
		$query = $this->db->update('tblproject_proposals',array('proposal_status' => 4));
		if ($query){
			return true;
		}else{
			return false;
		}
	}

	public function update_report($data,$id){
		$this->db->where('tblproject_proposals.proposal_id',$id);
		$query = $this->db->update('tblproject_proposals',$data);

		if($query){
			// $log = array(
			// 	'id' => $details['user_id'],
			// 	'type' => $query.':'.'Accomplisment Report Submission'
			// );
			// $this->save_log($log);


			// $notif = array (
			// 	'notification_sender' => $details['user_id'],
			// 	'notification_receiver' => '2015101246',
			// 	'notification_status' => 0,
			// 	'notif_type_id' => 5
			// );
			// $this->set_notifs($notif);

			return true;
		}else{
			return false;
		}
	}
}
?>