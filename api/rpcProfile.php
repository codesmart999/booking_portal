<?php 
    // Include necessary files
    include("../config.php");
    require_once('../lib.php');

    // Connect to the database
    $db = getDBConnection();

    // // Define sample profile data
    // $arrProfile = [
    //     "id"            => 5343,
    //     "business_name" => "AB1 Directional Drilling",
    //     "street"        => "PO Box 19",
    //     "city"          => "Maitland",
    //     "state"         => "NSW",
    //     "postcode"      => "2320",
    //     "email_addr"    => "office.ab1@bigpond.com",
    //     "phone_number"  => "0412 197 858"
    // ];

    // Check if the queryString parameter is set in the POST request
    if(isset($_POST['queryString'])) {
        // Sanitize the input string to prevent SQL injection
        $id = $_POST['queryString'];
        // Perform the search query based on the input string
        $query = "SELECT * FROM `customers` WHERE `CustomerId`= ?";

        // Execute the query and fetch results
        $stmt = $db->prepare($query);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $stmt->bind_result($customer_id, $full_name, $email_address, $postal_address, $phone_number, $comment, $regDate, $active);
        
        // Check if a customer with the given ID is found
        if ($stmt->fetch()) {
            // Prepare array profile data
            $arrayProfile = [
                "id"            => $customer_id,
                "business_name" => $full_name,
                "email_addr"    => $email_address,
                "phone_number"  => $phone_number,
                "addr"          => $postal_address
            ];
            // Output the JSON encoded profile data
            echo json_encode($arrayProfile);
        } else {
            // Output message if no customer is found with the given ID
            echo "No customer found with the given ID.";
        }
        // Terminate the script execution
        exit;
    }
?>
