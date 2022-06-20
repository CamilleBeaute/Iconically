<?php

// Allow cookies to be retrieved within the request that they are set.
function getcookie($name) {
    $cookies = [];
    $headers = headers_list();
    // see http://tools.ietf.org/html/rfc6265#section-4.1.1
    foreach($headers as $header) {
        if (strpos($header, 'Set-Cookie: ') === 0) {
            $value = str_replace('&', urlencode('&'), substr($header, 12));
            parse_str(current(explode(';', $value, 1)), $pair);
            $cookies = array_merge_recursive($cookies, $pair);
        }
    }
    return $cookies[$name];
}

function generate_timestamp() {
    return time();
}

function generate_nonce() {
    $mt = microtime();
    $rand = mt_rand();

    return md5($mt . $rand); // md5s look nicer than numbers
}

// Get messages from yard.
function getMessageYardTimeline() {

    global $yardId, $key, $secret, $oauthSignatureMethod, $oauthVersion;

    // Get questions from yard.
    $yardUrl = "https://api.connectyard.com/v1/messages/yards/" . $yardId;
    $oauthTimestamp = generate_timestamp();
    $nonce = generate_nonce(); 

    // Sign Request
    $sigBase = "GET&" . rawurlencode($yardUrl) . "&"
        . rawurlencode("count=" . rawurlencode("50") 
        . "&oauth_consumer_key=" . rawurlencode($key)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
        . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
        . "&oauth_version=" . rawurlencode($oauthVersion)
        . "&yard_id=" . rawurlencode($yardId)); 
    $sigKey = $secret. "&" . $_COOKIE["accessTokenSecret"]; 
    $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

    $requestUrl = $yardUrl . "?"
        . "count=" . rawurlencode("50")
        . "&oauth_consumer_key=" . rawurlencode($key)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
        . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
        . "&oauth_version=". rawurlencode($oauthVersion)
        . "&oauth_signature=" . rawurlencode($oauthSig)
        . "&yard_id=" . rawurlencode($yardId);

    $response = file_get_contents($requestUrl);

    return $response;
}


// Post message to yard
function postMessage($message) {
    global $yardId, $key, $secret, $oauthSignatureMethod, $oauthVersion;

    $postUrl = "https://api.connectyard.com/v1/messages/yards/" . $yardId;
    $oauthTimestamp = generate_timestamp();
    $nonce = generate_nonce(); 

    // Sign Request
    $sigBase = "POST&" . rawurlencode($postUrl) . "&"
        . rawurlencode("message_text=" . rawurlEncode($message)
        . "&oauth_consumer_key=" . rawurlencode($key)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
        . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
        . "&oauth_version=" . rawurlencode($oauthVersion)
        . "&upload_attachments=" . rawurlencode("1")
        . "&yard_id=" . rawurlencode($yardId));

    $sigKey = rawurlEncode($secret). "&" . rawurlEncode($_COOKIE["accessTokenSecret"]);
    $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

    // Build signed request URL
    $requestUrl = $postUrl . "?"
        . "oauth_consumer_key=" . rawurlencode($key)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
        . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
        . "&oauth_version=". rawurlencode($oauthVersion)
        . "&oauth_signature=" . rawurlencode($oauthSig);
    
    // Create request body
    $data = "message_text=" . rawurlEncode($message)
        . "&oauth_consumer_key=" . rawurlencode($key)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature=" . rawurlencode($oauthSig)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
        . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
        . "&oauth_version=". rawurlencode($oauthVersion) 
        . "&upload_attachments=" . rawurlencode("1")
        . "&yard_id=" . rawurlencode($yardId);

    // Create request context for POST
    $context = stream_context_create(array("http" => array(
        "method" => "POST",
        "header" => "Content-type: application/x-www-form-urlencoded; charset=UTF-8\r\n", 
        "content" => $data)));
        
    // Send request
    $result = @file_get_contents($requestUrl, false, $context);

    $pieces = explode('"', $result);

    // Upload ID returned from request
    $uploadId = $pieces[15];

    // If a file was uploaded, send request to attach file to the message.
    if(file_exists($_FILES['answer-upload']['tmp_name'])){

        $uploadUrl = "https://upload.connectyard.com";

        // JSON encoded representation of message attachment
        $attachment = json_encode(array("name" => $_FILES['answer-upload']['name'], "content" => base64_encode(file_get_contents($_FILES['answer-upload']['tmp_name'])), "size" => $_FILES['answer-upload']['size']));
        $attachment = "[" . $attachment . "]";
        
        // Sign Request
        $sigBase = "POST&" . rawurlencode($uploadUrl) . "&"
            . rawurlencode("attachments=" . rawurlEncode($attachment)
            . "&oauth_consumer_key=" . rawurlencode($key)
            . "&oauth_nonce=" . rawurlencode($nonce)
            . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
            . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
            . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
            . "&oauth_version=" . rawurlencode($oauthVersion)
            . "&upload_id=" . rawurlencode($uploadId));

        $sigKey = rawurlEncode($secret). "&" . rawurlEncode($_COOKIE["accessTokenSecret"]);
        $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

        // Build signed request URL
        $requestUrl = $uploadUrl . "?"
            . "oauth_consumer_key=" . rawurlencode($key)
            . "&oauth_nonce=" . rawurlencode($nonce)
            . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
            . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
            . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
            . "&oauth_version=". rawurlencode($oauthVersion)
            . "&oauth_signature=" . rawurlencode($oauthSig);
        
        // Create request body
        $data = "attachments=" . rawurlEncode($attachment)
            . "&oauth_consumer_key=" . rawurlencode($key)
            . "&oauth_nonce=" . rawurlencode($nonce)
            . "&oauth_signature=" . rawurlencode($oauthSig)
            . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
            . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
            . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
            . "&oauth_version=". rawurlencode($oauthVersion) 
            . "&upload_id=" . rawurlencode($uploadId);

        // Create request context for POST
        $context = stream_context_create(array("http" => array(
            "method" => "POST",
            "header" => "Content-type: application/x-www-form-urlencoded; charset=UTF-8\r\n", 
            "content" => $data)));

        // Send request
        $result = @file_get_contents($requestUrl, false, $context);

    } else if(file_exists($_FILES['question-upload']['tmp_name'])){

        $uploadUrl = "https://upload.connectyard.com";

        // JSON encoded representation of message attachment
        $attachment = json_encode(array("name" => $_FILES['question-upload']['name'], "content" => base64_encode(file_get_contents($_FILES['question-upload']['tmp_name'])), "size" => $_FILES['question-upload']['size']));
        $attachment = "[" . $attachment . "]";
        
        // Sign Request
        $sigBase = "POST&" . rawurlencode($uploadUrl) . "&"
            . rawurlencode("attachments=" . rawurlEncode($attachment)
            . "&oauth_consumer_key=" . rawurlencode($key)
            . "&oauth_nonce=" . rawurlencode($nonce)
            . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
            . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
            . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
            . "&oauth_version=" . rawurlencode($oauthVersion)
            . "&upload_id=" . rawurlencode($uploadId));

        $sigKey = rawurlEncode($secret). "&" . rawurlEncode($_COOKIE["accessTokenSecret"]);
        $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

        // Build signed request URL
        $requestUrl = $uploadUrl . "?"
            . "oauth_consumer_key=" . rawurlencode($key)
            . "&oauth_nonce=" . rawurlencode($nonce)
            . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
            . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
            . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
            . "&oauth_version=". rawurlencode($oauthVersion)
            . "&oauth_signature=" . rawurlencode($oauthSig);
        
        // Create request body
        $data = "attachments=" . rawurlEncode($attachment)
            . "&oauth_consumer_key=" . rawurlencode($key)
            . "&oauth_nonce=" . rawurlencode($nonce)
            . "&oauth_signature=" . rawurlencode($oauthSig)
            . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
            . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
            . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
            . "&oauth_version=". rawurlencode($oauthVersion) 
            . "&upload_id=" . rawurlencode($uploadId);

        // Create request context for POST
        $context = stream_context_create(array("http" => array(
            "method" => "POST",
            "header" => "Content-type: application/x-www-form-urlencoded; charset=UTF-8\r\n", 
            "content" => $data)));

        // Send request
        $result = @file_get_contents($requestUrl, false, $context);

    }       

    return $result;

}

// Get user information
function getUserInfo($userId, $accessToken, $accessTokenSecret) {
    global $key, $secret, $oauthSignatureMethod, $oauthVersion;
    $userUrl = "https://api.connectyard.com/v1/users/" . $userId;
    $oauthTimestamp = generate_timestamp();
    $nonce = generate_nonce();

    // Sign request
    $sigBase = "GET&" . rawurlencode($userUrl) . "&"
        . rawurlencode("oauth_consumer_key=" . rawurlencode($key)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
        . "&oauth_token=" . rawurlencode($accessToken)
        . "&oauth_version=" . rawurlencode($oauthVersion)); 
    $sigKey = $secret . "&" . $accessTokenSecret; 
    $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

    // Build signed request URL
    $requestUrl = $userUrl . "?"
        . "oauth_consumer_key=" . rawurlencode($key)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
        . "&oauth_token=" . rawurlencode($accessToken)
        . "&oauth_version=". rawurlencode($oauthVersion)
        . "&oauth_signature=" . rawurlencode($oauthSig);


    $response = file_get_contents($requestUrl);
    return $response;
}

// Define constants to be used in OAuth requests
$yardId = "471596";   // Yard ID
$key = "muhmwPBn3aZz59nbWnrFCc9as5Ua2CNqhQu3jecctJV57";          // Consumer key
$secret = "3BqYEhPjUetS37SbtfwcwrJSzCGVPFHtMMrp9dSekFW5z";       // Consumer secret
$oauthSignatureMethod = "HMAC-SHA1"; 
$oauthVersion = "1.0";

?>
