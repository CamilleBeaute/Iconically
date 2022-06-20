<?php 
require "authHeader.php";

// If a post request was sent from login.html, authorize user.
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    $message = "QUESTION:" . preg_replace('/\s+/', '', ucfirst($_POST['fname'])) . " " . preg_replace('/\s+/', '', ucfirst($_POST['lname']));     // Name of question submitter
    
    // Post message to yard
    $result = postMessage($message);

    $MAX_COUNT = 0;

    // Keep sending the request until it is successful a maximum of 3 times.
    while($result === false && $MAX_COUNT < 3) { 
        $result = postMessage($message);
        $MAX_COUNT++;
    }

    header("Location: index-member.php");

}else {
    header("Location: question-submission.php");
}

?>