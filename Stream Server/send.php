<?php
include 'functions.php';

// Bunch of variables needed
$auth_id = isset($_POST['auth_id']) ? $_POST['auth_id'] : false;
$image = isset($_FILES['image']['tmp_name']) ? $_FILES['image']['tmp_name'] : false;

// Validation result
$v1result = validate_auth_id($auth_id);
$v2result = validate_image($image);

// Check the validation and return proper responses
if($v1result['status'] !== 'success') {
    echo json_encode($v1result);
    die();
}
elseif($v2result['status'] !== 'success') {
    echo json_encode($v2result);
    die();
}

// Everything OK, read the image
$imgfp = fopen($image, 'rb');
// Save the data to DB
save_image($auth_id, $imgfp);

// Print successful message
$response['status'] = 'success';
$response['message'] = 'Images saved successfully';
echo json_encode($response);
