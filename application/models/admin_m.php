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
		$this->db->where('tblproject_proposals.proposal_status != 6');
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

	public function get_events($college = null){
		if($college == null){
			$query = $this->db->get('tbl_events')->result();
			foreach ($query as $row) {
				if($row->college == 'CITC'){
					$row->color = 'rgb(46,49,49)';
				}else if ($row->college == 'CEA'){
					$row->color = 'rgb(150,40,27)';
				}else if ($row->college == 'CSM'){
					$row->color = 'rgb(38,166,91)';
				}else if ($row->college == 'COT'){
					$row->color = 'rgb(217,30,24)';
				}else if ($row->college == 'CSTE'){
					$row->color = 'rgb(30,139,195)';
				}
			}
			return $query;
		}else{
			$query = $this->db->select('*')->from('tbl_events')->where('tbl_events.college',$college)->get()->result();
			foreach ($query as $row) {
				if($row->college == 'CITC'){
					$row->color = 'rgb(46,49,49)';
				}else if ($row->college == 'CEA'){
					$row->color = 'rgb(150,40,27)';
				}else if ($row->college == 'CSM'){
					$row->color = 'rgb(38,166,91)';
				}else if ($row->college == 'COT'){
					$row->color = 'rgb(217,30,24)';
				}else if ($row->college == 'CSTE'){
					$row->color = 'rgb(30,139,195)';
				}
			}
			return $query;
		}
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
				'notification_sender' => '2015101246',
				'notification_receiver' => $id->user_id,
				'notification_status' => 0,
				'notif_type_id' => 2
			);
			// var_dump($notif);
			$this->set_notifs($notif);

			$log = array(
				'id' => $id->user_id,
				'type' => 'Proposal Acceptance'
			);
			$this->save_log($log);

			$log = array(
				'id' => '2015101246',
				'type' => 'Proposal Approval'
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
		$response = [];
		$this->db->select('tbluser.user_school_id, tbluserinfo.*');
		$this->db->from('tbluser');
		$this->db->join('tbluserinfo','tbluser.user_school_id = tbluserinfo.ui_school_id');
		$this->db->where('tbluser.approved',0);
		$this->db->or_where('tbluser.approved',3);
		$query = $this->db->get()->result();

		if($query){
			$response[0] = true;
			$response[1] = $query;
			return $response;
		}else{	
			$response[0] = false;
			return $response;
		}
	}

	public function approve_registration($data){
		$this->db->where('user_school_id',$data['id']);
		$query = $this->db->update('tbluser',array('approved'=>$data['status'],'user_pass'=>'123'));

		$response = [];
		if($query){
			$response[0] = true;
			return $response;
		}else{	
			$response[0] = false;
			return $response;
		}
	}

	
	public function update_project_status($data){
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

		            if (!file_exists('C:/xampp/htdocs/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/')) {
						mkdir('C:/xampp/htdocs/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/', 0777, true);
		            	$target = 'C:/xampp/htdocs/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/'.$moaname;
					}else{
		            	$target = 'C:/xampp/htdocs/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/'.$moaname;
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

		            if (!file_exists('C:/xampp/htdocs/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/')) {
						mkdir('C:/xampp/htdocs/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/', 0777, true);
		            	$target = 'C:/xampp/htdocs/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/'.$covname;
					}else{
		            	$target = 'C:/xampp/htdocs/offyc/src/assets/uploaded_files/'.$query->ui_college.'/'.$query->ui_dept.'/'.$foldername.'/'.$covname;
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

	public function revise_proposal($data){
		$this->db->where('tblproject_proposals.proposal_id',$data['prop_id']);
		$query = $this->db->update('tblproject_proposals',array(
			'comment' => $data['comment'],
			'proposal_status' => '6'
		));

		$get = $this->db->select('*')->from('tblproject_proposals')->where('proposal_id',$data['prop_id'])->get()->row();


		$notif = array (
			'notification_sender' => '2015101246',
			'notification_receiver' => $get->user_id,
			'notification_status' => 0,
			'notif_type_id' => 3
		);
		// var_dump($notif);
		$this->set_notifs($notif);

		if($query){
			return true;
		}else{
			return false;
		}
	}

	public function get_revised_proposals($data = null){
		if ($data != null){
			$this->db->select('tblproject_proposals.*,tbluserinfo.*');
			$this->db->from('tblproject_proposals');
			$this->db->join('tbluserinfo', 'tbluserinfo.ui_school_id = tblproject_proposals.user_id');
			$this->db->where('tblproject_proposals.user_id',$data);
			$this->db->where('tblproject_proposals.proposal_status',6);
			$query = $this->db->get()->result();
		}else{
			$this->db->select('tblproject_proposals.*,tbluserinfo.*');
			$this->db->from('tblproject_proposals');
			$this->db->join('tbluserinfo', 'tbluserinfo.ui_school_id = tblproject_proposals.user_id');
			$this->db->where('tblproject_proposals.proposal_status',6);
			$query = $this->db->get()->result();
		}

		if ($query){
			return $query;
		}
	}

	public function get_project_count(){
		$chart = [];



		$first = 'SELECT tbluserinfo.ui_college College,COUNT(tblproject_proposals.proposal_id) Count FROM tblproject_proposals 
		INNER JOIN tbluserinfo ON tbluserinfo.ui_school_id = tblproject_proposals.user_id 
		WHERE tblproject_proposals.proposal_status = "5" AND tblproject_proposals.proposal_date_start >= "2019-01" AND tblproject_proposals.proposal_date_end <= "2019-03"
		GROUP BY tbluserinfo.ui_college';
		$second = 'SELECT tbluserinfo.ui_college College,COUNT(tblproject_proposals.proposal_id) Count FROM tblproject_proposals 
		INNER JOIN tbluserinfo ON tbluserinfo.ui_school_id = tblproject_proposals.user_id 
		WHERE tblproject_proposals.proposal_status = "5" AND tblproject_proposals.proposal_date_start >= "2019-04" AND tblproject_proposals.proposal_date_end <= "2019-06"
		GROUP BY tbluserinfo.ui_college';
		$third = 'SELECT tbluserinfo.ui_college College,COUNT(tblproject_proposals.proposal_id) Count FROM tblproject_proposals 
		INNER JOIN tbluserinfo ON tbluserinfo.ui_school_id = tblproject_proposals.user_id 
		WHERE tblproject_proposals.proposal_status = "5" AND tblproject_proposals.proposal_date_start >= "2019-07" AND tblproject_proposals.proposal_date_end <= "2019-09"
		GROUP BY tbluserinfo.ui_college';
		$fourth = 'SELECT tbluserinfo.ui_college College,COUNT(tblproject_proposals.proposal_id) Count FROM tblproject_proposals 
		INNER JOIN tbluserinfo ON tbluserinfo.ui_school_id = tblproject_proposals.user_id 
		WHERE tblproject_proposals.proposal_status = "5" AND tblproject_proposals.proposal_date_start >= "2019-10" AND tblproject_proposals.proposal_date_end <= "2019-12"
		GROUP BY tbluserinfo.ui_college';

		$quarter1 = $this->db->query($first)->result();
		$quarter2 = $this->db->query($second)->result();
		$quarter3 = $this->db->query($third)->result();
		$quarter4 = $this->db->query($fourth)->result();

		array_push($chart, $quarter1);
		array_push($chart, $quarter2);
		array_push($chart, $quarter3);
		array_push($chart, $quarter4);

		return $chart;

	}

	public function archive_db(){
		$date = date('Y-01');
		$year = date('Y') - 1;
		$this->db->select('*');
		$this->db->from('tblproject_proposals');
		$this->db->where("tblproject_proposals.proposal_date_start < '$date'");
		$get = $this->db->get()->result();

		/////////////////////////////////////////////////////////////////////////////////////////////
		
		foreach ($get as $key) {
			$exp = explode('/', $key->proposal_directory);
			$exp[4] = $year;
			$key->proposal_directory = implode('/', $exp);
		}

		foreach ($get as $key) {
			$moa = explode('/', $key->moa_directory);
			$moa[4] = $year;
			$key->moa_directory = implode('/', $moa);
		}

		foreach ($get as $key) {
			$cov = explode('/', $key->cover_directory);
			$cov[4] = $year;
			$key->cover_directory = implode('/', $cov);
		}

		foreach ($get as $key) {
			$rep = explode('/', $key->report_directory);
			$rep[4] = $year;
			$key->report_directory = implode('/', $rep);
		}



		foreach ($get as $key) {
			$this->db->insert('tbl_archive',$key);
		}


		$erase = $this->db->truncate('tblproject_proposals');

		$got = $this->db->get('tbl_archive')->result();
		rename("C:/xampp/htdocs/offyc/src/assets/uploaded_file/", "C:/xampp/htdocs/offyc/src/assets/$year/");
		return $got;
	}

}
?>