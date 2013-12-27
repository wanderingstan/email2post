<?php
// CGI handler that is called by mailgun when a new email is received
require_once "../config.php";

$mailbox = $_GET['mailbox'];

$out .= "<hr><p>\n";
$out .= "From: <b>{$_POST['From']}<b> on {$_POST['Date']}<br>\n";
// $out .= "To: {$_POST['To']}<br>\n";
$out .= "Subject: <b>{$_POST['Subject']}<b><br>\n";
$out .= "</p>\n";
$out .= "<!--BEGIN EMAIL-->\n";
$out .= $_POST['stripped-html'];
$out .= "\n<!--END EMAIL-->\n";

// Save the email contents in staging	

$filebasename = date('Y-m-d\TH-i-i\Z') . '_' . $_POST['sender'];
$filename = $config['STAGING_DIR'] . '/'  . $filebasename .'.html';
file_put_contents($filename, $out);

$log = "";

// TODO: save images, add to html
$count = 0;
foreach ($_FILES as $file) {
	$count++;
	$attachment_filename = $config['STAGING_DIR'] . '/' . $filebasename . '_' . $count . '_' . basename($file['name']);

	$log .= "Getting attachment {$count} as {$attachment_filename}.\n";
	// todo test for jpg or other safe image

	if (move_uploaded_file($file['tmp_name'], $attachment_filename)) {
	    $log .= "File is valid, and was successfully uploaded.\n";
	} else {
	    $log .= "Possible file upload attack!\n";
	}
}

// Log file
file_put_contents($config['LOG_FILE'], "\n---\n" . $log . "\n\n" . print_r ($_POST, TRUE), FILE_APPEND | LOCK_EX);		

file_put_contents($config['LOG_DIR'] . "/most_recent_post.serialized",serialize($_POST));

?>
