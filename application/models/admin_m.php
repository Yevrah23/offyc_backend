<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class admin_m extends CI_Model{
	public function __construct(){
		parent::__construct();
	}


	public function get_des_proposals(){
		$CITC = [];
		$CEA = [];
		$COT = [];
		$CSM = [];
		$CSTE = [];
		$i = 1;
		// $query = $this->db->get('tblproject_proposals');


		$this->db->select('tblproject_proposals.*,tbluserinfo.*');
		$this->db->from('tblproject_proposals');
		$this->db->join('tbluserinfo', 'tbluserinfo.ui_school_id = tblproject_proposals.user_id');
		$this->db->where('tblproject_proposals.proposal_status != 0');
		$query = $this->db->get();


		$college = "";
		if ($query){
			$response[0] = true;
			foreach ($query->result() as $row) {
				// var_dump($row->ui_college);
				if ($row->proposal_status == 1){
					$row->proposal_status = "Proposal Accepted";
				}else if ($row->proposal_status == 2){
					$row->proposal_status = "Waiting for Notarized MOA";
				}else if ($row->proposal_status == 3){
					$row->proposal_status = "Ongoing Project";
				}else if ($row->proposal_status == 4){
					$row->proposal_status = "Accomplishment Report Pending";
				}


				if($row->ui_college === 'CITC'){
					array_push($CITC, $row);
				}else if($row->ui_college === 'CEA'){
					array_push($CEA, $row);
				}else if($row->ui_college === 'CSM'){
					array_push($CSM, $row);
				}else if($row->ui_college === 'CSTE'){
					array_push($CSTE, $row);
				}else if($row->ui_college === 'COT'){
					array_push($COT, $row);
				}
			}
		}else{
			$response[0] = false;
		}
		$response[1] = array (
			'CITC' => $CITC,
			'CEA' => $CEA,
			'COT' => $COT,
			'CSTE' => $CSTE,
			'CSM' => $CSM
		);
		return $response;
	}

	public function get_proposals(){
		$i = 1;
		// $query = $this->db->get('tblproject_proposals');


		$this->db->select('tblproject_proposals.*,tbluserinfo.*');
		$this->db->from('tblproject_proposals');
		$this->db->join('tbluserinfo', 'tbluserinfo.ui_school_id = tblproject_proposals.user_id');
		$this->db->where('tblproject_proposals.proposal_status',0);
		$query = $this->db->get();


		$props = [];
		if ($query){
			$response[0] = true;
			foreach ($query->result() as $row) {
				$row->proposal_status = "Pending";
				array_push($props, $row);
				// var_dump($row->ui_college);
				// if($row->ui_college === 'CITC'){
				// 	array_push($CITC, $row);
				// }else if($row->ui_college === 'CEA'){
				// 	array_push($CEA, $row);
				// }else if($row->ui_college === 'CSM'){
				// 	array_push($CSM, $row);
				// }else if($row->ui_college === 'CSTE'){
				// 	array_push($CSTE, $row);
				// }else if($row->ui_college === 'COT'){
				// 	array_push($COT, $row);
				// }
				$i++;
			}
		}else{
			$response[0] = false;
		}
		$response[1] = $props;
		return $response;
	}
	public function get_proposal($id){
		$this->db->select('tblproject_proposals.*,tbluserinfo.*');
		$this->db->from('tblproject_proposals');
		$this->db->join('tbluserinfo', 'tbluserinfo.ui_school_id = tblproject_proposals.user_id');
		$this->db->where('tblproject_proposals.proposal_id',$id);
		$query = $this->db->get()->row();
		$query->budget_total = $query->budget_ustp + $query->budget_partner;
		return $query;
	}

	public function get_events(){
		$query = $this->db->get('tbl_events')->result();
		foreach ($query as $row) {
			if($row->color == 0){
				$row->color = 'red';
			}else if ($row->color == 1){
				$row->color = 'green';
			}else {
				$row->color = 'blue';
			}
		}
		return $query;
	}

	public function proposal_approval($details){
		// var_dump($details['id']);
		$get = $this->db->select('*')->from('tblproject_proposals')->where('proposal_id',$details['id'])->get()->row();

		if ($get){
			if ($details['status'] == 1){

			$this->db->where('title',$details['title']);
			$update = $this->db->update('tbl_events',array(
				'start' => $get->proposal_date_start,
				'end' => $get->proposal_date_end,
				'color' => 1

				));
			}else{
			$this->db->where('title',$details['title']);
			$update = $this->db->update('tbl_events',array(
				'start' => $get->proposal_date_start,
				'end' => $get->proposal_date_end,
				'color' => 2

				));			
			}
		}

		if ($get){
			$id = $this->db->select('user_id')->from('tblproject_proposals')->where('proposal_id',$details['id'])->get()->row();
			$notif = array (
				'notification_sender' => 'admin',
				'notification_receiver' => $id->user_id,
				'notification_status' => 0
			);
			// var_dump($notif);
			$this->set_notifs($notif);

			$log = array(
				'id' => $details['id'],
				'type' => 'Proposal Acceptance'
			);
			$this->save_log($log);

			$data = array(
				'id' => $details['id'],
				'status' => $details['status']
			);

			$this->update_project_status($data);


			return true;
		}else{
			return false;
		}
	}

	public function get_unregistered(){
		$this->db->select('tbluser.user_school_id, tbluserinfo.*');
		$this->db->from('tbluser');
		$this->db->join('tbluserinfo','tbluser.user_school_id = tbluserinfo.ui_school_id');
		$this->db->where('tbluser.approved',0);
		$query = $this->db->get()->result();

		if($query){
			return $query;
		}else{	
			return false;
		}
	}

	public function approve_registration($data){
		$this->db->where('user_school_id',$data['id']);
		$query = $this->db->update('tbluser',array('approved'=>$data['status']));

		if($query){
			return $data['status'];
		}else{
			return false;
		}
	}

	
	public function update_project_status($data){
		var_dump($data);
		$status = $data['status'];
		$this->db->where('tblproject_proposals.proposal_id',$data['id']);
		$this->db->where("tblproject_proposals.proposal_status < '$status'");
		$query = $this->db->update('tblproject_proposals',array('proposal_status' => $data['status']));
		if ($query){
			return true;
		}else{
			return false;
		}
	}
	public function set_notifs($notif){
		$query = $this->db->insert('tblnotification',$notif);
	}


	public function add_event($event){
		$query = $this->db->insert('tbl_events', $event);
	}

	public function save_log($log){
		$query = $this->db->insert('tbltrans_log',
			array(
				'user_id' => $log['id'], 
				'log_type' => $log['type']
			)
		);
	}


	public function get_prexc($quarter){
		$year = date('Y');
		$this->db->select('info.*,proposals.*');
		$this->db->from('tblproject_proposals proposals');
		$this->db->join('tbluserinfo info','info.ui_school_id = proposals.user_id');
		if($quarter == "1"){
			$this->db->where("proposals.proposal_date_start >'".$year."-01-01' && proposals.proposal_date_end < '".$year."-03-31'");
		}else if ($quarter == "2"){
			$this->db->where("proposals.proposal_date_start >'".$year."-04-01' && proposals.proposal_date_end < '".$year."-06-30'");
		}else if ($quarter == "3"){
			$this->db->where("proposals.proposal_date_start >'".$year."-07-01' && proposals.proposal_date_end < '".$year."-09-30'");
		}else{
			$this->db->where("proposals.proposal_date_start >'".$year."-10-01' && proposals.proposal_date_end < '".$year."-12-31'");
		}

		$query = $this->db->get()->result();

		if($query){
			foreach ($query as $row) {
				if($row->days_conducted < 1){
					$row->weight = 0.5;
					$row->points = $row->persons_trained * $row->weight;
					$row->raters = $row->rate_satisfactory + $row->rate_v_satisfactory + $row->rate_excellent;					
				}else if ($row->days_conducted == 1){
					$row->weight = 1;
					$row->points = $row->persons_trained * $row->weight;
					$row->raters = $row->rate_satisfactory + $row->rate_v_satisfactory + $row->rate_excellent;
				}else if ($row->days_conducted == 2){
					$row->weight = 1.25;
					$row->points = $row->persons_trained * $row->weight;
					$row->raters = $row->rate_satisfactory + $row->rate_v_satisfactory + $row->rate_excellent;				
				}else if ($row->days_conducted == 3 || $row->days_conducted == 4){
					$row->weight = 1.5;
					$row->points = $row->persons_trained * $row->weight;
					$row->raters = $row->rate_satisfactory + $row->rate_v_satisfactory + $row->rate_excellent;					
				}else if ($row->days_conducted >= 5){
					$row->weight = 2;
					$row->points = $row->persons_trained * $row->weight;
					$row->raters = $row->rate_satisfactory + $row->rate_v_satisfactory + $row->rate_excellent;				
				}
			}

			return $query;
		}else{
			return false;
		}
	}

	public function get_hemis($quarter){
		$year = date('Y');
		$this->db->select('proposals.*');
		$this->db->from('tblproject_proposals proposals');
		if($quarter == "1"){
			$this->db->where("proposals.proposal_date_start >'".$year."-01-01'");
			$this->db->where("proposals.proposal_date_end <'".$year."-03-31'");
		}else if ($quarter == "2"){
			$this->db->where("proposals.proposal_date_start >'".$year."-04-01'");
			$this->db->where("proposals.proposal_date_end <'".$year."-06-30'");
		}else if ($quarter == "3"){
			$this->db->where("proposals.proposal_date_start >'".$year."-07-01'");
			$this->db->where("proposals.proposal_date_end <'".$year."-09-30'");
		}else{
			$this->db->where("proposals.proposal_date_start >'".$year."-10-01'");
			$this->db->where("proposals.proposal_date_end <'".$year."-12-31'");
		}
		$query = $this->db->get()->result();

		if($query){
			return $query;
		}else{
			return false;
		}

	}
		public function moa_c_upload($data){
			$query = $this->db->select('*')->from('tbluserinfo')->where('ui_school_id',$data['user_id'])->get()->row();
	        $foldername = $data['folder'];
			if ($query){
				if (isset($_FILES['moa'])) {
		            $moa_info = pathinfo($_FILES['moa']['name']);                    // Uploaded Image Info
		   
		            $moext = $moa_info['extension']; // get the extension of the file or the file type
		            $moaname = 'notarized-moa'.'.'.$moext; 

		            if (!file_exists('C:/Users/Acer/Desktop/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/')) {
						mkdir('C:/Users/Acer/Desktop/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/', 0777, true);
		            	$target = 'C:/Users/Acer/Desktop/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/'.$moaname;
					}else{
		            	$target = 'C:/Users/Acer/Desktop/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/'.$moaname;
					}
		            move_uploaded_file( $_FILES['moa']['tmp_name'], $target);
		            
		            $this->db->where('tblproject_proposals.proposal_id',$data['prop_id']);
		            $moa_update = $this->db->update('tblproject_proposals',array(
		            		'moa_directory' => '../../../assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/'.$moaname,
		            		'proposal_status' => '3'
		            	));
				}

				if (isset($_FILES['cover'])) {
		            $cover_info = pathinfo($_FILES['cover']['name']);                    // Uploaded Image Info
		   
		            $covext = $cover_info['extension']; // get the extension of the file or the file type
		            $covname = 'signed-cover'.'.'.$covext; 

		            if (!file_exists('C:/Users/Acer/Desktop/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/')) {
						mkdir('C:/Users/Acer/Desktop/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/', 0777, true);
		            	$target = 'C:/Users/Acer/Desktop/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/'.$covname;
					}else{
		            	$target = 'C:/Users/Acer/Desktop/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/'.$covname;
					}
		            move_uploaded_file( $_FILES['cover']['tmp_name'], $target); 

		            
		            $this->db->where('tblproject_proposals.proposal_id',$data['prop_id']);
		            $cover_update = $this->db->update('tblproject_proposals',array(
		            		'cover_directory' => '../../../assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/'.$covname,
		            		'proposal_status' => '3'
		            	));
	            
	        	}

	        return 'File Upload Success';
			
		}
	}
}
?>