<?php
// CGI handler that is called by mailgun when a new email is received
require_once "../config.php";
require_once "create_letter.php";
include_once "setup.php";

function extract_email($email_string) {
    preg_match("/<?([^<]+?)@([^>]+?)>?$/", $email_string, $matches);
    if ($matches && $matches[1] && $matches[2]) {
        return $matches[1] . "@" . $matches[2];
    }
    else {
        return "";
    }
}

$true_sender_email = extract_email($_POST['from']);

$mailbox = $_GET['mailbox'];
$filebasename = date('Y-m-d\TH-i-i\Z') . '_' . $true_sender_email;
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
	}
	else {
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

$subject = 'Email2post content to ' . $_GET['mailbox'] . ' mailbox  from '. $_POST['from'] ;
$content  = count( explode(PHP_EOL, $_POST['body-plain']) ) . " lines were received from " . $_POST['from'] . "\n\n";
if ($count>0) {
    $content .= $count . " files were attached.\n\n";
}
$content .= 'Latest PDF visible at ' . $config['BASE_URL'] . '/data/staging/latest.pdf' . "\n\n";
$content .= 'Received at ' . $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"] ."\n\n";

mail('stan@wanderingstan.com', $subject , $content, $headers);

?>
