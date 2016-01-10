<?php
// this is loaded after remote login

include("oauth_data.php");

$state = $_SESSION["oauth_state"];

if ($_REQUEST["state"] == $state)
{
    // send for token
    // put token in session
    $result = get_token();

    $_SESSION['oauth_token'] = $result["access_token"];
    $_SESSION['oauth_scope'] = $result["scope"];
?>
<script>
   opener.showUser();
   window.close();
</script>
<?php
}
else {
    // behaviour to refine
    // if the "no login" is clear, then the window self-closing is enough (no message or button)
?>
Sorry, you are not logged in. <br />

<button onclick="window.close();">Close</button>

<?php
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
