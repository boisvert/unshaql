<?php
include("oauth_data.php");

$state = generateRandomString(20);
$_SESSION["oauth_state"] = $state;

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>

<!doctype html>
<meta charset="utf-8" />

<head>
    <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="ask_github.js"></script>
    <script src="http://codemirror.net/lib/codemirror.js"></script>
    <script src="http://codemirror.net/addon/edit/matchbrackets.js"></script>
    <script src="http://codemirror.net/mode/sparql/sparql.js"></script>
    <link rel="stylesheet" href="http://codemirror.net/lib/codemirror.css">
    <title>Query the Air Quality+ data repository using SPARQL</title>
</head>

<body>

<h1>Query the Air Quality+ data repository using SPARQL</h1>
<div id="user">
    <button onclick="login_gui();">
        login github
    </button>
</div>


<p>
Speedy API guide:
<ul>
    <li>Set the return format: ?format=<i>value</i>, e.g. <a href="sparql_aq+.php?format=application/sparql-results%2Bjson">sparql_aq+.html?format=application/sparql-results%2Bjson</a>
    <li>To load a saved query, use ?gist=<i>id</i>&file=<i>filename</i>, e.g. <a href="sparql_aq+.php?gist=c2fba1f0cf68d6b3b0b8&file=specific%20sensor.sparql">sparql_aq+.html?gist=c2fba1f0cf68d6b3b0b8&file=specific%20sensor.sparql</a></li>
    <li>There is no support for saving queries from here (yet). Save them in github gist, then keep a note of the file's name and the gist id to access the query</li>
</ul></p>

<p>
More SPARQL examples at <a href="https://github.com/BetterWithDataSociety/ShefAirQualityAgent/wiki/Sample-SPARQL">AQ+ on github</a>,
and a list of <a href="https://github.com/boisvert/unshaql/tree/master/queries">queries that have been saved.</a>
</p>

<p>
Sparql highlighter thanks to <a href="http://codemirror.net">CodeMirror</a>.
</p>

<p>
Result format:
<select name="format" id="format">
   <option value="auto" >Auto</option>
   <option value="text/html" selected="selected">HTML</option>
   <option value="application/vnd.ms-excel" >Spreadsheet</option>
   <option value="application/sparql-results+xml" >XML</option>
   <option value="application/sparql-results+json" >JSON</option>
   <option value="application/javascript" >Javascript</option>
   <option value="text/turtle" >Turtle</option>
   <option value="application/rdf+xml" >RDF/XML</option>
   <option value="text/plain" >N-Triples</option>
   <option value="text/csv" >CSV</option>
   <option value="text/tab-separated-values" >TSV</option>
</select>
<input type="button" value="Run query" onClick="runQuery()" />
<input type="button" value="Get link" onClick="openLink()" />
<br />

<textarea id="sparql" cols="120" rows="30">
select ?sensor, max(?day), max(?hour), avg(?observationValue), max(?observationValue), min(?observationValue)
where {
  graph ?g {
    ?s <uri://opensheffield.org/properties#sensor> ?sensor .
    ?s a <http://purl.oclc.org/NET/ssnx/ssn#ObservationValue> .
    ?s <http://purl.oclc.org/NET/ssnx/ssn#endTime> ?observationTime.
    ?s <http://purl.oclc.org/NET/ssnx/ssn#hasValue> ?observationValue .
    ?sensor <http://purl.oclc.org/NET/ssnx/ssn#MeasurementProperty> <http://dbpedia.org/resource/NO2>     .
    BIND (bif:subseq( str( ?observationTime ),0,11) AS ?day) .
    BIND (bif:subseq( str( ?observationTime ),11,13) AS ?hour) .
    BIND (bif:subseq( str( ?observationTime ),0,13) AS ?dayhour) .
    FILTER ( xsd:date(?observationTime) > xsd:date("2014-10-13") && xsd:date(?observationTime) <= xsd:date("2014-11-13") )
  }
}
GROUP BY ?sensor ?dayhour
ORDER BY ?dayhour ?sensor
</textarea>

</p>
<hr />

<iframe
   id="result"
   width="100%"
   height="600"
   frameborder="1"
   scrolling="yes"
   marginheight="0"
   marginwidth="0"
   src=""
>
</iframe>

<script>

// check behaviour
$(document).ready(showUser);
// when previously logged in, showUser would find the token
// the remainder is unclear.
// If the token is out of date, login GUI should get straight to authorisation which self closes.
// if not logging in correctly, or never logged in, the page should leave the login button
// without a message  - not disrupting the work flow?

function login_gui() {
    var page = '<?php echo "$gui_uri?client_id=$client_id&scope=$scope&redirect_uri=$redirect_uri&state=$state"; ?>';
    window.open(page,"Login Github","menubar=no, status=no, scrollbars=no, width=550, height=480");
}

function showUser() {
    $.get("proxy.php?service=user", function(data, status){
        console.log("data: "+JSON.stringify(data)+"\nStatus: "+status);
        if (status=="success" && !(data.error)) {
            var inner = '<img src="'+data.avatar_url+'" width="20" /> '+data.login+'( <div id="out">logout</div>)';
            $("#user").html(inner);
            $("#out").click(logout);
        }
    });
}

function logout() {
    $.get("proxy.php?service=logout", function(data, status){
        console.log("data: "+JSON.stringify(data)+"\nStatus: "+status);
        if (status=="success") {
            var inner = '<button onclick="login_gui();">login github</button>';
            $("#user").html(inner);
        }
    });
}

// service parameters
if (f = getParam("format")) {
    var fElt = document.getElementById('format');
    fElt.value = f;
}

if (id = getParam("gist")) {
    if (f = getParam("file")) {
        getSPARQLFile(id, function(resp) { editor.setValue(getContents(resp, f)); });
    }
}

// public functions

function runQuery() {
   document.getElementById('result').src=getURL();
}

function openLink() {
   var url = getURL();
   var text = "Copy this link to share the query result:<br /> <a href='" + url + "'>" + url + "</a>";
   var linkWindow = window.open("", "MsgWindow", "width=500, height=200");
   linkWindow.document.write(text);
   linkWindow.focus();
}

// private functions

// configure the editor
var editor = CodeMirror.fromTextArea(document.getElementById("sparql"), {
   mode: "application/sparql-query",
   matchBrackets: true
});

// compose URL for running SPARQL query
function getURL() {
   var f = document.getElementById('format').value;
   var sparql = editor.getValue();
   sparql = sparql.split('\r').join(' ').split('\n').join(' ');
   return "http://apps.opensheffield.org/sparql?default-graph-uri=&timeout=0&debug=on&"+val('format',f)+"&"+val('query',sparql);
}

// make name-value pair for a query string
function val(name,value) {
   return name+'='+encodeURIComponent(value);
}

// query string parsing thanks to jolly.exe - http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values-in-javascript
function getParam(name) {
   name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
   var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
   results = regex.exec(location.search);
   return results === null ? null : decodeURIComponent(results[1].replace(/\+/g, " "));
}

</script>
</body>
</html>
