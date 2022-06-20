<?php 
require "authHeader.php";

// If a post request was sent from login.html, authorize user.
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // Index of the question chosen.
    $questionIndex=$_POST["question-select"];

    // Index of chosen question's message (first message is index 7, each subsequent message is offset by 84)
    // $messageIndex = 7+(84*$questionIndex);

    // Call get yard posts request.
    $response = @getMessageYardTimeline();
    $pieces = explode('"', $response);

    $question = "";     // Initialize question variable
    $messageIndex = 7;  // Index of first message

    // Go through each post to find the current question, incrementing by 84 if the post does not
    // Have any attachments and 16 if it does.
    for($i=0; $i<$questionIndex; $i++) {
        $messageIndex += 84;
        if($pieces[$messageIndex] == "canDelete") {
            $messageIndex += 16;
        }
    }

    // Message to be posted.
    $message = "ANSWER FROM: " . preg_replace('/\s+/', '', ucfirst($_POST['fname'])) 
    . " " . preg_replace('/\s+/', '', ucfirst($_POST['lname'])) 
    . "\nQuestion Answered: " . substr($pieces[$messageIndex],9);     
    
    // Post message to yard.
    $result = postMessage($message);

    $MAX_COUNT = 0;

    // Keep sending the request until it is successful a maximum of 3 times.
    while($result === false && $MAX_COUNT < 3) {
        $result = postMessage($message);
        $MAX_COUNT++;
    }

    header("Location: index-icon.php");

} else {
    header("Location: answer-submission.php");
}

?>