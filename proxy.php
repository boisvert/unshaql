<?
// this is loaded after remote login

include("oauth_data.php");

header('Content-Type: application/json');

if (isset($_SESSION['oauth_token'])) {
    $s = $_GET["service"];
    $ans = request_tunnel($s);
    echo $ans;
}
else {
    echo '{"error": "no token"}';
}

// rewrite to request token
// should some curl wrapper go in a util library? This is the second page that needs curl.
function request_tunnel($service) {

    global $github_url;

    $url = $github_url . $service . "?access_token=" . $_SESSION['oauth_token'];

    // print_r($url); echo "<br />";

    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'GET'
        )
    );

    $context  = stream_context_create($options);

    // print_r($context);

    $response = file_get_contents($url, false, $context);

    return $response;

}

?>