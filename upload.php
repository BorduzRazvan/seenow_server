<?php

require_once 'include/DB_Functions.php';

$db = new DB_Functions();

 //An array to display the response
 $response = array();
 
 //if the call is an api call 
 if(isset($_GET['apicall'])){
 
 //switching the api call 
 switch($_GET['apicall']){
 
 //if it is an upload call we will upload the image
 case 'uploadpic':
 //first confirming that we have the image 
 if(isset($_FILES['pic']['name']) && isset($_POST['author'])){
 	if(isset($_POST['description'])){
 		$description = $_POST['description'];
 	}
 	else 
 	{
 		$description = null;
 	}
 	
 	
 	if(isset($_POST['predictedLabel'])){
 		$predicted = $_POST['predictedLabel'];
 	}
 	else 
 	{
 		$predicted = null;
 	}

 	$response = $db->uploadeImage($_FILES, $_POST['author'], $description, $predicted); 		
 	
 }else{
		$response['error'] = true;
   	 	$response['message'] = "Required params not available";
 }	
 break;
 
 //in this call we will fetch all the images 
 case 'getpics':
 	$response = $db->getPics();
 break; 
 
 default: 
 $response['error'] = true;
 $response['message'] = 'Invalid api call';
 }
 
 }else{
 header("HTTP/1.0 404 Not Found");
 echo "<h1>404 Not Found</h1>";
 echo "The page that you have requested could not be found.";
 exit();
 }

 //displaying the response in json 
 header('Content-Type: application/json');
 echo json_encode($response);
 
?>