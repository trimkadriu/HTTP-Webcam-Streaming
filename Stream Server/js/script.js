// Global variables
var BASE_URL = "http://localhost/web-cam-stream";
var WAIT_OFFSET = 1000 + STREAM_SPEED + 500; // 1000ms-Alive packet + Stream Speed + 500ms-Up/Down possible delay
var STREAM_SPEED = 0;
var AUTH_ID = "";
var TIMER;

// On document load -- attach events
$(function(){
    // Initialize all variables
    STREAM_SPEED = $('#stream-speed').val();
    communicator("getauthids", 0, addAuthIds);

    $('#auth-id').change(function() {
        AUTH_ID = $(this).val();
    });

    $("#stream-speed").bind('keyup mouseup', function() {
        STREAM_SPEED = $(this).val();
    });

    $('#start-stream').click(function() {
        communicator("start", STREAM_SPEED, startStream);
    });

    $('#stop-stream').click(function() {
        communicator("stop", 0, stopStream);
    });

    $('body').on('click', '#close-button', function() {
        $(this).parent().hide();
    });
});

// Event callbacks
// ------------------------------------------------------------
function addAuthIds(result) {
    if(result.data.online.length > 0) {
        $.each(result.data.online, function (i) {
            $('#auth-id').append('<option value="' + result.data.online[i] + '" class="online">' + result.data.online[i] + '</option>');
        });
    }
    if(result.data.offline.length > 0) {
        $.each(result.data.offline, function (i) {
            $('#auth-id').append('<option value="' + result.data.offline[i] + '" class="offline">' + result.data.offline[i] + '</option>');
        });
    }
}

function startStream(result) {
    // TODO: Decorate with LOADING IMAGE
    if(result.status === "success") {
        setTimeout(function(){
            TIMER = setInterval(getStream, STREAM_SPEED);
            showLog();
        }, WAIT_OFFSET);
    }
}

function stopStream() {
    clearInterval(TIMER);
    setTimeout(function(){
        $('#stream-box').attr('src', '');
    }, STREAM_SPEED * 3);
}

// Communication methods
// ------------------------------------------------------------
function communicator(command, streamSpeed, callback) {
    $.ajax({
        type: "POST",
        async: false,
        url: BASE_URL + "/communicator.php",
        data: "auth_id=" + AUTH_ID + "&command=" + command + "&stream_speed=" + streamSpeed,
        success: function (data) {
            var jsonResult = getJson(data);
            if(jsonResult != undefined) {
                if(jsonResult.status != 'invisible')
                    showLog(jsonResult);
                callback(jsonResult);
            }
        }
    });
}

function getStream() {
    $.ajax({
        type: "GET",
        url: BASE_URL + "/httpstream.php?auth-id=" + AUTH_ID,
        success: function (data) {
            var jsonResult = getJson(data);
            if(jsonResult != undefined) {
                if (jsonResult.status === 'error') {
                    $("#stop-stream").click();
                    setTimeout(function () {
                        showLog(jsonResult);
                    }, (STREAM_SPEED * 2) + 150);
                }
                else {
                    console.log("Lost stream image:\n " + jsonResult);
                }
            }
            else {
                $('#stream-box').attr('src', data);
                showLog();
            }
        }
    });
}


// UTIL methods
// ------------------------------------------------------------
function showLog(message) {
    var logElement = $('#status');
    if (message == undefined || message == null) {
        logElement.hide();
    } else {
        logElement.html('<strong>' + message.status + '</strong> - <span>' + message.message + '</span><span id="close-button">&#10006;</span>');
        logElement.show();
    }
}

function getJson(data) {
    var jsonResult;
    try {
        jsonResult = JSON.parse(data);
    }
    catch (e) {};
    return jsonResult;
}