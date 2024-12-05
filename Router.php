<?php
	include_once("GenericREST.php");
	include_once("EntityDAO.php");
	
	$URL = "sqlite:./entitiesDB.sqlite";

	$dao = new EntityDAO();
	$rest = new GenericREST($dao);
	$dao->connect($URL);

	$resp=[ "status" => 410, "body" => "" ];
	
	if ($_SERVER['REQUEST_METHOD'] === "GET") {
		if(isset($_GET["id"])) {
			$id = $_GET["id"];  
			$resp =  $rest->get($id);
		}
		else { 
			$resp = $rest->getAll();
		}
	}
	if ($_SERVER['REQUEST_METHOD'] === "PUT") {
		$data = file_get_contents("php://input");
		$obj = json_decode($data);
		$resp = $rest->put($obj);
	}
	if ($_SERVER['REQUEST_METHOD'] === "POST") {
		$data = file_get_contents("php://input");
		$obj = json_decode($data);
		$resp = $rest->post($obj);
	}	
	if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
		if (isset($_REQUEST["id"])) {
			$id = $_REQUEST["id"];
			$resp = $rest->delete($id);
		} else 
			$resp = [ "status" => 410, "body" => "" ];	
	}
	$dao->disconnect();		
	http_response_code($resp['status']);
	echo $resp['body']; 						
?>
