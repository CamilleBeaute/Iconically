<?php
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Call to Update User Info API
    require "authHeader.php";

    $userUrl = "https://api.connectyard.com/v1/users/" . $_COOKIE["userId"];
    $oauthTimestamp = generate_timestamp();
    $nonce = generate_nonce(); 

    // Updated Account Information
    $email = trim($_POST['email']);                                  // New email
    $name = preg_replace('/\s+/', '', $_POST['fname']) . " " . preg_replace('/\s+/', '', $_POST['lname']);     // New Name



    // Sign Request
    $sigBase = "POST&" . rawurlencode($userUrl) . "&"
        . rawurlencode("email="   . rawurlEncode($email)
        . "&name="    . rawurlEncode($name)
        . "&oauth_consumer_key=" . rawurlencode($key)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
        . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
        . "&oauth_version=" . rawurlencode($oauthVersion)
        . "&user_id=" . rawurlencode($_COOKIE["userId"]));

    $sigKey = rawurlEncode($secret). "&" . rawurlEncode($_COOKIE["accessTokenSecret"]);
    $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

    // Build signed request URL
    $requestUrl = $userUrl . "?"
        . "oauth_consumer_key=" . rawurlencode($key)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
        . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
        . "&oauth_version=". rawurlencode($oauthVersion)
        . "&oauth_signature=" . rawurlencode($oauthSig);

    // Create request body
    $data = "email="   . rawurlEncode($email)
        . "&name="    . rawurlEncode($name)
        . "&oauth_consumer_key=" . rawurlencode($key)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature=" . rawurlencode($oauthSig)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
        . "&oauth_token=" . rawurlencode($_COOKIE["accessToken"])
        . "&oauth_version=". rawurlencode($oauthVersion)
        . "&user_id=" . rawurlencode($_COOKIE["userId"]);

    // Create request context for POST
    $context = stream_context_create(array("http" => array(
        "method" => "POST",
        "header" => "Content-type: application/x-www-form-urlencoded; charset=UTF-8\r\n", 
        "content" => $data)));
        
    // Send request
    $result = file_get_contents($requestUrl, false, $context);

    // If the user is an icon, redirect to icon index, otherwise if they are not redirect to member index.
    if($_COOKIE["icon"])
        header("Location: icon-profile.php");
    else if(!$_COOKIE["icon"])
        header("Location: member-profile.php");

}

?>