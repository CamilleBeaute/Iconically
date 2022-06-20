<?php
// Remove all cookies
if (isset($_COOKIE['userId'])) {
    unset($_COOKIE['userId']);
    setcookie('userId', '', time() - 3600); // empty value and old timestamp
}

if (isset($_COOKIE['accessToken'])) {
    unset($_COOKIE['accessToken']);
    setcookie('accessToken', '', time() - 3600); // empty value and old timestamp
}

if (isset($_COOKIE['accessTokenSecret'])) {
    unset($_COOKIE['accessTokenSecret']);
    setcookie('accessTokenSecret', '', time() - 3600); // empty value and old timestamp
}

if (isset($_COOKIE['icon'])) {
    unset($_COOKIE['icon']);
    setcookie('icon', '', time() - 3600); // empty value and old timestamp
}


// Redirect to home page.
header("Location: index.html");

?>