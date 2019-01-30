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

	public function login($data){
		$response = [];
		$query =  $this->db->select('*')->from('tbluser')->where('user_school_id',$data['username'])->where('user_pass',$data['password'])->get();
		if ($query->num_rows() > 0){
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
			}else if ($query->row()->approved == 0){
				$response[0] = false;
				$response[1] = 'Your Account has not been approved yet. Please wait for confirmation' ;
			}else{
				$response[0] = false;
				$response[1] = 'Your request to register an account has been denied for some reason. Please visit the Extension Office to reconcile this matter';
			}

			
			return $response;
		}else{
			$response[0] = false;
			$response[1] = 'Username/Password Incorrect. Please check your credentials entered and try again';
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





	//////////////////// Transactions ///////////////////////////////////
	public function submit_proposal($details){
		$p_details = $this->db->insert('tblproject_proposals',$details);


		if ($p_details){
			$log = array(
				'id' => $details['user_id'],
				'type' => 'Proposal Submission'
			);
			$this->save_log($log);

			$event = array (
				'start' => date('c'),
				'title' => $details['proposal_title'],
				'color' => 0
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




	public function upload_file($data){
		$query = $this->db->select('*')->from('tbluserinfo')->where('ui_school_id',$data['id'])->get()->row();
		if ($query){
			if (isset($_FILES['upload'])) {
            $file_info = pathinfo($_FILES['upload']['name']);                    // Uploaded Image Info
            $maxsize = 2097152;             // Restricts 2MB images only
            $bool_image_size;               // Stores boolean value for image size
            $bool_image_type = true;               // Stores boolean value for image type/format
            $errors[0]="";$errors[1]="";    // Stores string value of error/s
            // $file_types = array(            //
            //     'image/jpeg',               //  Restricts other formats
            //     'image/jpg',                //  except for jpeg,jpg,png
            //     'image/png'                 //
            // );

            if(($_FILES['upload']['size'] > $maxsize) || ($_FILES['upload']['size'] === 0) ) {
                $errors[0] = 'File too large. File must be less than 2 megabytes.';              //Checks if the image 
                $bool_image_size = false;                                                       //uploaded size is 2MB
            }
            else {
                $bool_image_size = true;
            }

            // if(!in_array($_FILES['image']['type'], $file_types) && (!empty($_FILES['image']['type'])) ) {
            //     $errors[1] = 'Invalid file type. Only JPG, JPEG, and PNG types are accepted.';           //Checks if the image
            //     $bool_image_type = false;                                                               //type or format is acceptable
            // }
            // else {
            //     $bool_image_type = true;
            // }

            if($bool_image_size && $bool_image_type) {
                $ext = $file_info['extension']; // get the extension of the file or the file type
                // $newname = $_SESSION['id'].'_pic.'.$ext; 
                $filename = $data['folder'];
                $newname = $data['file_type'].'.'.$ext; 

                if (!file_exists('C:/Users/Acer/Desktop/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$filename.'/')) {
					mkdir('C:/Users/Acer/Desktop/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$filename.'/', 0777, true);
                	$target = 'C:/Users/Acer/Desktop/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$filename.'/'.$newname;
				}else{
                	$target = 'C:/Users/Acer/Desktop/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$filename.'/'.$newname;
				}
                // $link = 'assets/img/profile_pics/'.$newname;

                // $field = array(
                //     'user_id' => $this->session->userdata('id'),
                //     'photo_path' => $link
                // );

                // $query = $this->db->select('*')->from('tbl_photo_upload')->where('user_id',$field['user_id'])->get();
                // if($query->num_rows()>0){
                //     if(!$bool_image_size && !$bool_image_type) {
                //         unlink($target);
                //     }
                //     $this->db->where('user_id',$field['user_id']);
                //     $this->db->update('tbl_photo_upload',$field);
                    move_uploaded_file( $_FILES['upload']['tmp_name'], $target);                           
                // }
                // else{
                //     $this->db->insert('tbl_photo_upload',$field);
                //     move_uploaded_file( $_FILES['image']['tmp_name'], $target);                
                // }
            } 
            else {
            	return 'Some Error Occured';
                // $_SESSION['error_image_upload'] = $errors[0].'\n'.$errors[1];
                // redirect('Applicant/user_settings');
                //echo json_encode($errors);
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
}
?>