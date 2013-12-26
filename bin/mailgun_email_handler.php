<?php
// CGI handler that is called by mailgun when a new email is received
include_once "../config.php";

$mailbox = $_GET['mailbox'];

$out .= "<hr>\n";
$out .= "<p>From: {$_POST['From']}<p>\n";
$out .= "<p>To: {$_POST['To']}<p>\n";
$out .= "<p>Date: {$_POST['Date']}<p>\n";
$out .= "<!--BEGIN EMAIL-->\n";
$out .= $_POST['stripped-html'];
$out .= "\n<!--END EMAIL-->\n";

// TODO: save images, add to html

// Save the email contents in staging	
$filename = '../data/staging/' . date('Y-m-d\TH-i-i\Z') . '.html';
file_put_contents($filename, $out);

// Log file
file_put_contents($config['LOG_FILE'], "\n---\n" . print_r ($_POST, TRUE), FILE_APPEND | LOCK_EX);		

?>
