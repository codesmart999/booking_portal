<?php

    include("../config.php");
    require_once('../lib.php');
    // Connect to your database or perform any necessary setup
    $db = getDBConnection();

    // Check if the queryString parameter is set in the POST request
    if(isset($_POST['queryString'])) {
        // Sanitize the input string to prevent SQL injection
        $inputString = $_POST['queryString'];
        // Perform the search query based on the input string
        $query = "SELECT * FROM customers WHERE FullName LIKE '%" . $inputString . "%' OR Email LIKE '%" . $inputString . "%'";

        // Execute the query and fetch results
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stmt->bind_result($id, $name, $email, $address, $phone, $comment, $regdate, $active);
        $arrResult = array();
        while($stmt->fetch()) {
	        $arrResult[$id] = array(
	        	"id" 	=> $id,
	        	"name"	=> $name,
	        	"email"	=> $email,
                "address" => $address,
                "phone" => $phone,
            );
	    }

        // Check if any results were found
        if($id) {
            // If results were found, encode them as JSON and send back to frontend
            echo json_encode($arrResult);
        } else {
            // If no results were found, return a message indicating no results
            echo json_encode(array('message' => 'No results found'));
        }
    } else {
        // If the queryString parameter is not set, return an error message
        echo json_encode(array('error' => 'No input provided'));
    }
?>