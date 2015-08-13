<?php
// Config variables
$db_host = 'localhost';
$db_name = 'wc_stream';
$db_username = 'root';
$db_password = '';
$connection_string = 'mysql:host='.$db_host.';dbname='.$db_name;

// Timeout is in Image Stream units, not in time
// (ex. if stream is set on 500ms and timeout 50 then -> 2 images per sec. -> 50 / 2 = 25 => ~25sec timeout
$stream_timeout = 30;
// Unit in seconds
$alive_timeout = 10;
// Default stream speed
$default_stream_speed = 300;
// Maximum image file size
$max_image_size = 512000;
//
// PDO for DB access
$dbh = new PDO($connection_string, $db_username, $db_password);