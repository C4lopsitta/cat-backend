<?php
	include_once("DAO/GenericDAO.php");

	class GenericREST {
	
		private ?GenericDAO $dao = null;

		public function __construct(GenericDAO $dao) {
			$this->dao = $dao;
		}
		public function get($id) {
			$obj = $this->dao->read(intval($id));
			return $obj? $this->response (200, $obj->to_json()): $this->response (404, "");
		}
		public function getAll() {
			$objs = $this->dao->readAll();
			return $this->response(200, json_encode($objs));
		}
		public function post(object $obj) {
			$rc = $this->dao->create($obj);
			return ($rc == -1)? $this->respond(410,""): $this->response(200, "");
		}
		public function put(object $obj) {
			$rc = $this->dao->update($obj);
			return $rc? $this->respond(410, "") :$this->response(200, "");
		}
		public function delete($id) {
			$rc = $this->dao->delete($id);
			return $rc? $this->response(404, ""): $this->response (200, "");
		}

		private function response(int $status, string $body):array {
			return( [ "status" => $status, "body" => $body ] );
		}
	}
?>
