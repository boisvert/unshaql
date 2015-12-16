<?php

session_start();

// register your app to github to use oauth
// then modify the configuration

$client_id = ""; // client ID goes here

$client_secret = ""; // client secret goes here

$redirect_uri = "your-site/authorised.php"; // adapt for your own site

$token_url = "https://github.com/login/oauth/access_token";
$gui_uri = "https://github.com/login/oauth/authorize";

$scope ="gist, user";

?>
