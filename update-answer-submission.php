<?php 
require "authHeader.php";

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $questionIndex = $_POST['questionIndex']; // Question selected by icon

    // Get all posts from yard, then break response up at each '"'
    $response = @getMessageYardTimeline();

    $MAX_COUNT = 0;

    // Keep sending the request until it is successful; a maximum of 3 times.
    while($response === false && $MAX_COUNT < 3) { 
        $response = @getMessageYardTimeline();
        $MAX_COUNT++;
    }

    if($response !== false) {

        $pieces = explode('"', $response);

        $messageIndex = 7;  // Index of first message

        // Go through each post to find the current question, incrementing by 84 if the post does not
        // Have any attachments and 16 if it does.
        for($i=0; $i<$questionIndex; $i++) {
            $messageIndex += 84;
            if($pieces[$messageIndex] == "canDelete") {
                $messageIndex += 16;
            }
        }

        $messageIndex += 50; // Index of the current message's attachment.

        // Confirm that this message has an attachment.
        if($pieces[$messageIndex-2] == "id") {
            $attachmentId = $pieces[$messageIndex];
        }



        // Get message attachment
        $attachUrl = "https://api.connectyard.com/v1/messages/attachments/" . $attachmentId;
        $oauthTimestamp = generate_timestamp();
        $nonce = generate_nonce(); 

        // Sign Request
        $sigBase = "GET&" . rawurlencode($attachUrl) . "&"
            . rawurlencode("attachment_id=" . rawurlencode($attachmentId)
            . "&oauth_consumer_key=" . rawurlencode($key)
            . "&oauth_nonce=" . rawurlencode($nonce)
            . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
            . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
            . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
            . "&oauth_version=" . rawurlencode($oauthVersion));

        $sigKey = $secret. "&" . $_COOKIE["accessTokenSecret"]; 
        $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

        $requestUrl = $attachUrl . "?"
            . "attachment_id=" . rawurlencode($attachmentId)
            . "&oauth_consumer_key=" . rawurlencode($key)
            . "&oauth_nonce=" . rawurlencode($nonce)
            . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
            . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
            . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
            . "&oauth_version=". rawurlencode($oauthVersion)
            . "&oauth_signature=" . rawurlencode($oauthSig);


        $response = file_get_contents($requestUrl);
        $pieces = explode('"', $response);

        $attachment = $pieces[3]; // Attachment url

        $name = $pieces[11];
        // if(strpos($name, ".m4a") !== false) {
        //     echo '<audio style="width:520; height: 440" controls>
        //     <source src="' . $attachment . '" type="audio/mp4">
        //     Your browser does not support the audio tag.
        //     </audio>';
        // } else {
        //     echo '<video style="width:520; height: 440" controls>
        //     <source src="' . $attachment . '" type="video/mp4">
        //     Your browser does not support the audio tag.
        //     </video>';
        // }

        $output['image'] = $attachment;
        $output['name'] = $name;
        echo json_encode($output);
    }

    



}
?>