<?php
	require APPPATH . '/libraries/JWT.php';


	class ImplementJWT{





		private $key = '123abc';

		public function GenerateToken($data){
			$jwt = JWT::encode($data, $this->key);
			return $jwt;
		}


		public function DecodeToken($token){
			$decoded = JWT::decode($token,$this->key,array('HS256'));
			$decodedData = (array) $decoded;
			return $decodedData;
		}
	}


?>