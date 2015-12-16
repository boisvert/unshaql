<?
// this is loaded after remote login

include("oauth_data.php");

$state = $_SESSION["oauth_state"];

if ($_GET["state"] == $_SESSION["oauth_state"])
{
    echo "Hello, authorised user<br />";

    // send for token
    // put token in session
    $result = get_token();

    $_SESSION['oauth_token'] = $result["token"];
    $_SESSION['oauth_scope'] = $result["scope"];
}
else {
    echo "Not you";
}

// rewrite to request token
// should some curl wrapper go in a util library? This is the second page that needs curl.
function get_token() {

    global $client_id;
    global $client_secret;
    global $token_url;

    $data['client_id'] = $client_id;
    $data['client_secret'] = $client_secret;
    $data['code'] = $_GET["code"];
    
    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ),
    );

    $context  = stream_context_create($options);
    // debug_msg("Executing $url");
    $response = file_get_contents($token_url, false, $context);

    parse_str($response, $result);

    return $result;

}

?>