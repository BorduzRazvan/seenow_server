<?php
class DB_Functions {

    private $conn;

    // constructor
    function __construct() {
        require_once 'DB_Connect.php';
        // connecting to database
        $db = new Db_Connect();
        $this->conn = $db->connect();
    }

    // destructor
    function __destruct() {
        
    }


    /**
     * Storing new user with normal_register
     * returns user details
     */
    public function storeUser($name, $email, $password, $birthday, $country, $gender) {
        $hash = $this->hashSSHA($password);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"]; // salt

        $stmt = $this->conn->prepare("INSERT INTO users(name, email, encrypted_password, salt, country, birthday, gender, created_at) VALUES(?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssss",$name, $email, $encrypted_password, $salt, $country, $birthday, $gender);
        $result = $stmt->execute();
        $stmt->close();

        // check for successful store
        if ($result) {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user;	
        } else {
            return false;
        }
    }


   /**  
    * Seting the socialId for an user 
    * returns user details 
    */
    public function setSocialLogin($social_id, $email) {
	$stmt = $this->conn->prepare("UPDATE users SET socialLoggedIn = ? WHERE email = ?");
	$stmt->bind_param("ss",$social_id, $email);
	$result = $stmt->execute();
	$stmt->close();

	 $result = $stmt->execute();
        $stmt->close();

        // check for successful store
        if ($result) {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user;	
        } else {
            return false;
        }
    }

    /**
     * Get user by email and password
     */
    public function getUserByEmailAndPassword($email, $password) {

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");

        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // verifying user password
            $salt = $user['salt'];
            $encrypted_password = $user['encrypted_password'];
            $hash = $this->checkhashSSHA($salt, $password);
            // check for password equality
            if ($encrypted_password == $hash) {
                // user authentication details are correct
                return $user;
            }
        } else {
            return NULL;
        }
    }

 /**
     * Get user by email and social_id
     */
    public function getUserByEmailAndSocialId($email, $social_id) {

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");

        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // check for password equality
            if ($user['socialLoggedIn'] == $social_id) {
                // user authentication details are correct
                return $user;
            }
        } else {
            return NULL;
        }
    }

    /**
     * Check user is existed or not
     */
    public function isUserExisted($email) {
        $stmt = $this->conn->prepare("SELECT email from users WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // user existed 
            $stmt->close();
            return true;
        } else {
            // user not existed
            $stmt->close();
            return false;
        }
    }

    /**
     * Encrypting password
     * @param password
     * returns salt and encrypted password
     */
    public function hashSSHA($password) {

        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }

    /**
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    public function checkhashSSHA($salt, $password) {

        $hash = base64_encode(sha1($password . $salt, true) . $salt);

        return $hash;
    }
 
    /** 
    * Uploading a file and storeing it in table 
    */
    public function uploadeImage($_FILES, $author, $description, $predicted) {
	 //uploading file and storing it to database as well  
	 try{
		move_uploaded_file($_FILES['pic']['tmp_name'], UPLOAD_PATH . $_FILES['pic']['name']);

		$stmt = $conn->prepare("INSERT INTO images (image, author) VALUES (?,?)");
	 	$stmt->bind_param("ss", $_FILES['pic']['name'],$author);
	 	$stmt->execute();
	 	$result = $stmt->get_result()->fetch_assoc();
	 	$stmt->close();
		if($result){
	 		$response['error'] = false;
			$response['message'] = 'File uploaded successfully';
			
			/** If we know already the person from picture */
			if($predicted){
				insertFeed($result['image'],$result['author'], $predicted, $description);
			}
			else {
				/** Start analysis */
			}
 		}else{
			 throw new Exception("Could not upload file");
 		}
 	}catch(Exception $e){
 		$response['error'] = true;
 		$response['message'] = 'Could not upload file';
 	}
 	return $response;
    }
    
    
    /**
    * Get the pics from Table
    */
    public function getPics() {
    	 //getting server ip for building image url 
		 $server_ip = gethostbyname(gethostname());
 
		 //query to get images from database
		 $stmt = $conn->prepare("SELECT id, image, tags FROM images");
		 $stmt->execute();
		 $stmt->bind_result($id, $image, $author);
 
		 $images = array();
		 
		 //fetching all the images from database
		 //and pushing it to array 
		 while($stmt->fetch()){
			 $temp = array();
			 $temp['id'] = $id; 
			 $temp['image'] = 'http://' . $server_ip . '/MyApi/'. UPLOAD_PATH . $image; 
			 $temp['tags'] = $author; 
			 
			 array_push($images, $temp);
		 }
		 
		 //pushing the array in response 
		 $response['error'] = false;
		 $response['images'] = $images; 
		return $response;
    }

    
    private function startAnalysis($pictureName, $description, $authorId){
    		/** Todo */
    }
    
    private function insertFeed($authorId, $foundUserid, $pictureId, $description){
    	if($description){
	    	$stmt = $conn->prepare("INSERT INTO feeds (author_id, foundUser_id, picture_id, description, posted_at) VALUES (?,?,?,?,NOW())");
			$stmt->bind_param("ssss",$authorId, $foundUserid, $pictureId, $description);		
    	}
    	else {
	    	$stmt = $conn->prepare("INSERT INTO feeds (author_id, foundUser_id, picture_id, posted_at) VALUES (?,?,?,NOW())");
			$stmt->bind_param("sss",$authorId, $foundUserid, $pictureId);		
    	}
    	
    	$stmt->execute();
	 	$result = $stmt->get_result()->fetch_assoc();
	 	$stmt->close();
		
    	
    }
    
    private function getFeed($userId){
    	$stmt = $conn->prepare("SELECT * FROM feeds f WHERE author_id=? OR foundUserid=? JOIN users ");
    	$stmt->bind_param("ss",$userId, $userId);
    	$stmt->execute();
    	$result = $stmt->get_result();
	
    }
}

?>