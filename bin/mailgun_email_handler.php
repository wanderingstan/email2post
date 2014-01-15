<?php
// CGI handler that is called by mailgun when a new email is received
require_once "../config.php";
require_once "create_letter.php";
include_once "setup.php";

$mailbox = $_GET['mailbox'];

$filebasename = date('Y-m-d\TH-i-i\Z') . '_' . $_POST['sender'];
$serialized_filename = $config['STAGING_DIR'] . '/'  . $filebasename .'.serialized';
$log = "";

// Save the serialized version
file_put_contents($serialized_filename,serialize($_POST));

// Save attchements
$count = 0;
foreach ($_FILES as $file) {
	$count++;

	$attachment_filename =  $filebasename . '_' . $count . '_' . basename($file['name']);
    $attachment_filepath = $config['STAGING_DIR'] . '/' . $attachment_filename;

	$log .= "Getting attachment {$count} as {$attachment_filename}.\n";
	// todo test for jpg or other safe image

	if (move_uploaded_file($file['tmp_name'], $attachment_filepath)) {
	    $log .= "File is valid, and was successfully uploaded.\n";
        //$out .= "<div><img src='" . $attachment_filename . "'/></div>";
	} else {
	    $log .= "Possible file upload attack!\n";
	}
}

// Log file
file_put_contents($config['LOG_FILE'], "\n---\n" . $log . "\n\n" . print_r ($_POST, TRUE), FILE_APPEND | LOCK_EX);

// Create latest rev of pdf
create_letter_from_emails($config['STAGING_DIR'] . '/' . 'latest.pdf', FALSE);

// Email notification to stan
$headers = 'From: stan@wanderingstan.com' . "\r\n" .
    'Reply-To: stan@wanderingstan.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
$content = 'Latest PDF visible at ' . $config['BASE_URL'] . '/data/staging/latest.pdf' . "\n" . $count . " files were attached.\n";
mail('stan@wanderingstan.com', 'New content added to ' . $_GET['mailbox'] . ' mailbox at ' .$_SERVER["SERVER_NAME"] .' with URI '.$_SERVER["REQUEST_URI"] , $content, $headers);


?>
