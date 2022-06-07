<?php 
session_start();
require_once "authHeader.php";

// If a post request was sent from login.html, authorize user.
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Get submitted inputs
    $email = trim($_POST['email']); // Email for Connect Yard account
    $password = trim($_POST['password']);

    // Request for Unauthorized Request Token
    $requestTokenUrl = "https://api.connectyard.com/oauth/request_token.php"; 
    $oauthTimestamp = generate_timestamp();
    $nonce = generate_nonce(); 

    // Sign request
    $sigBase = "GET&" . rawurlencode($requestTokenUrl) . "&"
        . rawurlencode("oauth_consumer_key=" . rawurlencode($key)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . $oauthTimestamp
        . "&oauth_version=" . $oauthVersion);

    $sigKey = $secret . "&"; 
    $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

    // Build signed request URL
    $requestUrl = $requestTokenUrl . "?"
        . "oauth_consumer_key=" . rawurlencode($key)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
        . "&oauth_version=" . rawurlencode($oauthVersion)
        . "&oauth_signature=" . rawurlencode($oauthSig); 



    // Get Request Token, redirect to login if request fails
    $response = @file_get_contents($requestUrl);
    if($response === false) {
        header("Location: login.html");
    }

    // Save token and secret as cookies
    parse_str($response, $values);

    setcookie("requestToken", $values["oauth_token"]);
    setcookie("requestTokenSecret", $values["oauth_token_secret"]);



    // Request Authorization of Request Token
    $authorizeUrl = "https://api.connectyard.com/oauth/authorize.php";
    $authorizeUrl = $authorizeUrl . "?oauth_token=" . getcookie("requestToken") . "&userEmail=" . $email; 

    // Send request for authorized request token, redirect to login if request fails
    $response = @file_get_contents($authorizeUrl);
    if($response === false) {
        header("Location: login.html");
    }

    $pieces = explode('"', $response);

    // Save user Id as session variable and get oauth_verifier from response
    setcookie("userId", $pieces[11]);
    $verifier = $pieces[3];




    // Request Access Token 
    $accessTokenUrl = "https://api.connectyard.com/oauth/access_token.php";
    $oauthTimestamp = generate_timestamp();
    $nonce = generate_nonce(); 

    // Sign request for access token
    $sigBase = "GET&" . rawurlencode($accessTokenUrl) . "&"
        . rawurlencode("oauth_consumer_key=" . rawurlencode($key)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
        . "&oauth_token=" . rawurlencode(getcookie("requestToken"))
        . "&oauth_verifier=" . rawurlencode($verifier)
        . "&oauth_version=" . rawurlencode($oauthVersion)); 
    $sigKey = $secret. "&" . getcookie("requestTokenSecret"); 
    $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

    // Build signed request URL for access token
    $requestUrl = $accessTokenUrl . "?"
        . "oauth_consumer_key=" . rawurlencode($key)
        . "&oauth_nonce=" . rawurlencode($nonce)
        . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
        . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
        . "&oauth_token=" . rawurlencode(getcookie("requestToken"))
        . "&oauth_verifier=" . rawurlencode($verifier)
        . "&oauth_version=". rawurlencode($oauthVersion)
        . "&oauth_signature=" . rawurlencode($oauthSig); 

    // Send request for access token, redirect to login if request fails
    $response = @file_get_contents($requestUrl);
    if($response === false) {
        header("Location: login.html");
    }

    // Save access token and access token secret as session variables
    parse_str($response, $values);
    setcookie("accessToken", $values["oauth_token"]);
    setcookie("accessTokenSecret", $values["oauth_token_secret"]);



    // Determine whether user is member or icon.

    // Get user info
    $response = getUserInfo(getcookie("userId"), getcookie("accessToken"), getcookie("accessTokenSecret"));

    // Get user's name
    $pieces = explode('"', $response);
    $name = $pieces[7];

    //Get users in yard
    $yardUrl = "https://api.connectyard.com/v1/yards/" . $yardId . "/users";
    $oauthTimestamp = generate_timestamp();
    $nonce = generate_nonce(); 

    // Sign Request
    $sigBase = "GET&" . rawurlencode($yardUrl) . "&"
    . rawurlencode("oauth_consumer_key=" . rawurlencode($key)
    . "&oauth_nonce=" . rawurlencode($nonce)
    . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
    . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
    . "&oauth_token=" . rawurlencode(getcookie("accessToken"))
    . "&oauth_version=" . rawurlencode($oauthVersion)
    . "&search_name=" . rawurlencode($name)
    . "&yard_id=" . rawurlencode($yardId)); 
    $sigKey = $secret. "&" . getcookie("accessTokenSecret"); 
    $oauthSig = base64_encode(hash_hmac("sha1", $sigBase, $sigKey, true));

    $requestUrl = $yardUrl . "?"
    . "oauth_consumer_key=" . rawurlencode($key)
    . "&oauth_nonce=" . rawurlencode($nonce)
    . "&oauth_signature_method=" . rawurlencode($oauthSignatureMethod)
    . "&oauth_timestamp=" . rawurlencode($oauthTimestamp)
    . "&oauth_token=" . rawurlencode(getcookie("accessToken"))
    . "&oauth_version=". rawurlencode($oauthVersion)
    . "&oauth_signature=" . rawurlencode($oauthSig)
    . "&search_name=" . rawurlencode($name)
    . "&yard_id=" . rawurlencode($yardId);

    $response = file_get_contents($requestUrl);

    $pieces = explode('"', $response);

    // Index of ID and role of first user in list of users
    $idIndex = 5;
    $roleIndex = 21;

    // If multiple users have the same name, increment to the next user until the correct ID is found.
    while($pieces[$idIndex] != getcookie("userId")) {
        $idIndex += 24;
        $roleIndex += 24;
    }

    // If the user is an admin, set icon to true; otherwise false.
    if($pieces[$roleIndex] == "admin") {
        setcookie("icon", true);
    } else {
        setcookie("icon", false); 
    }

    // If the user is an icon, redirect to icon index, otherwise if they are not redirect to member index.
    if(getcookie("icon"))
        header("Location: index-icon.html");
    else if(!getcookie("icon"))
        header("Location: index-member.html");

// Do not allow user to access this file through the search bar.
} else {
    header("Location: login.html");
}

?>