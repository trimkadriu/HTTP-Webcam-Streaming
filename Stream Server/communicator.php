<?php
include 'functions.php';

$command = isset($_POST['command']) ? $_POST['command'] : false;
$auth_id = isset($_POST['auth_id']) ? $_POST['auth_id'] : false;

// Process Command
switch($command) {
    case "alive" : {
        check_auth_id();
        set_last_alive($auth_id); // Update last alive
        $user = get_user($auth_id)[0];
        if ($user['status'] === '0') {
            $result['command'] = 'stop';
            cleanup_leftover_images($auth_id); // Cleanup leftovers
        }
        else if ($user['status'] === '1') {
            global $stream_timeout;
            $leftovers = count_left_stream($auth_id)[0];
            if(intval($leftovers['leftovers']) >= $stream_timeout) {
                // SHOULD STOP -- Client is disconnected
                $result['command'] = 'stop';
                cleanup_leftover_images($auth_id); // Cleanup leftovers
            } else {
                $result['command'] = 'start';
                $result['stream_speed'] = $user['stream_speed'];
            }
        }
        break;
    }
    case "start" : {
        check_auth_id();
        $user = get_user($auth_id)[0];
        global $alive_timeout;
        if(time() - strtotime($user['last_alive']) >= $alive_timeout) { // 60 sec passed since server showed up - he's dead
            $result['status'] = 'error';
            $result['message'] = 'Server is not connected.';
        } else {
            global $default_stream_speed;
            $stream_speed = isset($_POST['stream_speed']) ? intval($_POST['stream_speed']) : $default_stream_speed;
            start_stream($auth_id, $stream_speed);
            $result['status'] = 'success';
            $result['message'] = 'Stream should start soon...';
        }
        break;
    }
    case "stop" : {
        check_auth_id();
        stop_stream($auth_id);
        $result['status'] = 'success';
        $result['message'] = 'Stream should stop now...';
        cleanup_leftover_images($auth_id);
        break;
    }
    case "getauthids" : {
        global $alive_timeout;
        $result['status'] = 'invisible';
        $onlineUsers = get_all_online_users($alive_timeout);
        $temp['online'] = array();
        $temp['offline'] = array();
        foreach($onlineUsers as $user) {
            array_push($temp['online'], $user["auth_id"]);
        }
        $offlineUsers = get_all_offline_users($alive_timeout);
        foreach($offlineUsers as $user) {
            array_push($temp['offline'], $user["auth_id"]);
        }
        $result['data'] = $temp;
        break;
    }
    default : {
        $result['message'] = 'Get out of here...';
    }
}
echo json_encode($result);
die();

function check_auth_id() {
    global $auth_id;
    $v1result = validate_auth_id($auth_id);
    if($v1result['status'] !== 'success') {
        echo json_encode($v1result);
        die();
    }
}