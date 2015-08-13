<?php
include 'config.php';

function validate_image($image) {
    $response['status'] = 'success';
    if(!$image) {
        $response['status'] = 'error';
        $response['message'] = 'Image not present';
    } else {
        if(!$_FILES['image']['error']) {
            global $max_image_size;
            if($_FILES['image']['size'] > ($max_image_size)) { //can't be larger than 500 KB
                $response['status'] = 'error';
                $response['message'] = 'Image file\'s size is to large.';
            }
            else {
                $response['message'] = 'Successfully validated image';
            }
        }
        else {
            $response['status'] = 'error';
            $response['message'] = 'Your upload has the following error:  '.$_FILES['photo']['error'];
        }
    }
    return $response;
}

function validate_auth_id($auth_id) {
    global $dbh;
    $sql = 'SELECT auth_id FROM auth_users WHERE auth_id = ? LIMIT 1';
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(1, $auth_id);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $array = $stmt->fetchAll();
    if(count($array) === 1) {
        $response['status'] = 'success';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Invalid AUTH ID';
    }
    return $response;
}

function save_image($auth_id, $imgfp) {
    global $dbh;
    $stmt = $dbh->prepare("INSERT INTO data (auth_id, image) VALUES (? ,?)");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt->bindParam(1, $auth_id);
    $stmt->bindParam(2, $imgfp, PDO::PARAM_LOB);
    $stmt->execute();
}

function get_latest_image($auth_id) {
    global $dbh;
    $sql = 'SELECT * FROM data WHERE auth_id = ? ORDER BY date_added DESC LIMIT 1';
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(1, $auth_id);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    return $stmt->fetchAll();
}

function cleanup_leftover_images($auth_id) {
    global $dbh;
    $sql = 'DELETE FROM data WHERE auth_id = ?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(1, $auth_id);
    $stmt->execute();
}

function cleanup_older_leftover_images($auth_id, $older_than) {
    global $dbh;
    $sql = 'DELETE FROM data WHERE auth_id = ? AND date_added <= ?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(1, $auth_id);
    $stmt->bindParam(2, $older_than);
    $stmt->execute();
}

function get_all_online_users($alive_timeout) {
    global $dbh;
    $sql = 'SELECT * FROM auth_users where last_alive - TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 minute)) > ?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(1, $alive_timeout);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    return $stmt->fetchAll();
}

function get_all_offline_users($alive_timeout) {
    global $dbh;
    $sql = 'SELECT * FROM auth_users where last_alive - TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 minute)) < ?';
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(1, $alive_timeout);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    return $stmt->fetchAll();
}

function get_user($auth_id) {
    global $dbh;
    $sql = 'SELECT * FROM auth_users WHERE auth_id = ? LIMIT 1';
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(1, $auth_id);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    return $stmt->fetchAll();
}

function set_last_alive($auth_id) {
    global $dbh;
    $stmt = $dbh->prepare("UPDATE auth_users SET last_alive = now() WHERE auth_id = ?");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt->bindParam(1, $auth_id);
    $stmt->execute();
}

function start_stream($auth_id, $stream_speed) {
    global $dbh;
    $stmt = $dbh->prepare("UPDATE auth_users SET status = 1, stream_speed = ? WHERE auth_id = ?");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt->bindParam(1, $stream_speed);
    $stmt->bindParam(2, $auth_id);
    return $stmt->execute();
}

function stop_stream($auth_id) {
    global $dbh;
    $stmt = $dbh->prepare("UPDATE auth_users SET status = 0 WHERE auth_id = ?");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt->bindParam(1, $auth_id);
    $stmt->execute();
}

function count_left_stream($auth_id) {
    global $dbh;
    $sql = 'SELECT COUNT(*) AS `leftovers` FROM data WHERE auth_id = ? LIMIT 1';
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(1, $auth_id);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    return $stmt->fetchAll();
}