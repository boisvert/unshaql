
// load files from github
function getSPARQLFile(id,callback) {
    var URL = 'https://api.github.com/repos/boisvert/unshaql/contents/queries/'+id;
    var xhr = xrequest('GET', URL, callback);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send();
}

// Cross-site HTML5 file loading courtesy of HTML5Rocks (http://www.html5rocks.com/en/tutorials/cors/)
function xrequest(method, url, callback) {

    var xhr;
   // 1. Create XHR instance
   if (window.XMLHttpRequest) {
        xhr = new XMLHttpRequest();
    }
    else if (window.ActiveXObject) {
        xhr = new ActiveXObject("Msxml2.XMLHTTP");
    }
    else {
        throw new Error("Ajax is not supported by this browser");
    }

    // 2. Define what to do when XHR feed you the response from the server

    xhr.onreadystatechange = function () {
        //alert(xhr.readyState + " " + xhr.status + " " + xhr.responseText)
        if (xhr.readyState === 4) {
            if (xhr.status == 404) {
                // 404 = file not found; could also be a timeout, etc.
                alert('No response');
            }
            if (xhr.status == 200 || xhr.status==304) {
                // 200 = OK, 304 = no change, already loaded
                // callback
                callback(JSON.parse(xhr.responseText));
            }
        }
    }

    xhr.open(method, url);
    return xhr;

}

function getContents(data) {
    // take the file contents from a JSON response
    // then decode the base64 encoding of it.
    
    return atob(data.content); // base64 to ascii
}
