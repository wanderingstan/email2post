<?php
// CGI handler that is called by mailgun when a new email is received
require_once "../config.php";
require_once "create_letter.php";

$mailbox = $_GET['mailbox'];

// $from = htmlentities($_POST['From']);
// $subject = htmlentities($_POST['Subject']);
//
// $out .= "<hr><p>\n";
// $out .= "From: <b>{$from}<b> on {$_POST['Date']}<br>\n";
// // $out .= "To: {$_POST['To']}<br>\n";
// $out .= "Subject: <b>{$subject}<b><br>\n";
// $out .= "</p>\n";
// $out .= "<!--BEGIN EMAIL-->\n";
// //$out .= $_POST['stripped-html'];
// $out .= $_POST['body-html'];
// $out .= "\n<!--END EMAIL-->\n";

$out .= "From: {$_POST['From']}<b> on {$_POST['Date']}\n";
$out .= "Subject: {$_POST['Subject']}\n";
$out .= $_POST['body-plainl'];

// Save the email contents in staging

$filebasename = date('Y-m-d\TH-i-i\Z') . '_' . $_POST['sender'];
$filename = $config['STAGING_DIR'] . '/'  . $filebasename .'.html';
$serialized_filename = $config['STAGING_DIR'] . '/'  . $filebasename .'.serialized';
$log = "";

// TODO: save images, add to html
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

// Save our HTML/TXT
file_put_contents($filename, $out);
// Save the serialized version
file_put_contents($serialized_filename,serialize($_POST));

// Log file
file_put_contents($config['LOG_FILE'], "\n---\n" . $log . "\n\n" . print_r ($_POST, TRUE), FILE_APPEND | LOCK_EX);


// Create latest rev of pdf
create_letter_from_emails($config['STAGING_DIR'] . '/' . 'latest.pdf', FALSE);

?>
