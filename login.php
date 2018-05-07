<?php


require_once 'include/DB_Functions.php';
$db = new DB_Functions();

// json response array
$response = array("error" => FALSE);

if (isset($_POST['email']) && isset($_POST['password'])) {

    // receiving the post params
    $email = $_POST['email'];
    $password = $_POST['password'];

    // get the user by email and password
    $user = $db->getUserByEmailAndPassword($email, $password);

    if ($user != false) {
        // use is found
        $response["error"] = FALSE;
        $response["uid"] = $user["unique_id"];
        $response["user"]["name"] = $user["name"];
        $response["user"]["email"] = $user["email"];
        $response["user"]["created_at"] = $user["created_at"];
        $response["user"]["updated_at"] = $user["updated_at"];
        echo json_encode($response);
   }
 } else if(isset($_POST['email']) && isset($_POST['social_id'])) {
	// read all the inputs from post	
	$email = $_POST['email'];
	$social_id = $_POST['social_id'];
	$name = $_POST['name'];
	$password = "facebook_login";
	$birthday = $_POST['dateb'];
	$country = $_POST['country'];
	$gender = $_POST['gender'];

	$user = $db->getUserByEmailAndSocialId($email, $social_id);

    	if ($user != false) {
		// use is found
		$response["error"] = FALSE;
		$response["uid"] = $user["id"];
		$response["user"]["name"] = $user["name"];
		$response["user"]["email"] = $user["email"];
		$response["user"]["created_at"] = $user["created_at"];
		$response["user"]["updated_at"] = $user["updated_at"];
		echo json_encode($response);
	    } else {
		// user is not found with the credentials

			$db->storeUser($name, $email, $password, $birthday, $country, $gender);
			$user = $db->setSocialLogin($social_id, $email);
			if($user != false)
			{
				$response["uid"] = $user["id"];
				$response["user"]["name"] = $user["name"];
				$response["user"]["email"] = $user["email"];
				$response["user"]["created_at"] = $user["created_at"];
				$response["user"]["updated_at"] = $user["updated_at"];
				$response["user"]["country"] = $user["country"];
				$response["user"]["birthday"] = $user["birthday"];	
			}
			else 
			{
				    $response["error"] = TRUE;
			}
			echo json_encode($response);
	    }
  }
  else {
    // required post params is missing
    $response["error"] = TRUE;
    $response["error_msg"] = "Required parameters email or password is missing!";
    echo json_encode($response);
}
?>
