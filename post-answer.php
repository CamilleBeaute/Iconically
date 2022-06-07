<?php 
require "authHeader.php";

// If a post request was sent from login.html, authorize user.
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // Index of the question chosen.
    $questionIndex=$_POST["question-select"];

    // Index of chosen question's message (first message is index 7, each subsequent message is offset by 84)
    $messageIndex = 7+(84*$questionIndex);

    // Call get yard posts request.
    $response = getMessageYardTimeline();
    $pieces = explode('"', $response);

    // Message to be posted.
    $message = "ANSWER FROM: " . preg_replace('/\s+/', '', ucfirst($_POST['fname'])) 
    . " " . preg_replace('/\s+/', '', ucfirst($_POST['lname'])) 
    . "\nQuestion Answered: " . substr($pieces[$messageIndex],9);     
    
    // Post message to yard.
    $result = postMessage($message);

    // Keep sending the request until it is successful.
    while($result === false) {
        $result = postMessage($message);
    }

    header("Location: index-icon.html");

} else {
    header("Location: answer-submission.html");
}

?>