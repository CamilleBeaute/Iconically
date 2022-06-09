<?php 
require "authHeader.php";

// If a post request was sent from login.html, authorize user.
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    $message = "QUESTION:" . preg_replace('/\s+/', '', ucfirst($_POST['fname'])) . " " . preg_replace('/\s+/', '', ucfirst($_POST['lname']));     // Name of question submitter
    
    // Post message to yard
    $result = postMessage($message);

    // Keep sending the request until it is successful.
    while($result === false) { 
        $result = postMessage($message);
    }

    header("Location: index-member.html");

}else {
    header("Location: question-submission.html");
}

?>