<?php
include 'functions.php';

// Recommended to prevent caching
header('Cache-Control: no-cache');

// Bunch of variables needed
$auth_id = isset($_GET['auth-id']) ? $_GET['auth-id'] : false;

// Validation result
$v1result = validate_auth_id($auth_id);

// Check the validation and return proper responses
if($v1result['status'] !== 'success') {
    echo json_encode($v1result);
    die();
}

// Check if server dead
$user = get_user($auth_id)[0];
global $alive_timeout;
if(time() - strtotime($user['last_alive']) >= $alive_timeout) { // 60 sec passed since server showed up - he's dead
    $result['status'] = 'error';
    $result['message'] = 'Server is not connected.';
    echo json_encode($result);
    die();
}

// Get latest image
$result = get_latest_image($auth_id);
// If no results DIE
if(count($result) === 0) {
    $response['status'] = 'warning';
    $response['message'] = 'No image results (Maybe the user is not streaming)';
    echo json_encode($response);
    die();
}
$image = $result[0];
$older_than = $image['date_added'];

// Delete older images
// TODO: FIX the following line to clean, takes too much time and we loose live images. Anyway the clean will happen on 'alive' packets
//cleanup_older_leftover_images($auth_id, $older_than);

//header('Content-type: image/jpeg');
echo 'data:image/jpeg;base64,' . base64_encode($image['image']);