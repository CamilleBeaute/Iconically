<?php

// If the user ID, access token, access token secret, and icon cookies are not all set, redirect to index page.
if(!(isset($_COOKIE['userId']) && isset($_COOKIE['accessToken']) && isset($_COOKIE['accessTokenSecret']) && isset($_COOKIE['icon']))) {
    header("Location: index.html");
}

// If the user is an icon, redirect them to icon's index page.
if($_COOKIE['icon']) {
    header("Location: index-icon.php");
}

?>