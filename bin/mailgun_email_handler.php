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

// TODO: save images, add to html

// Save the email contents in staging	
$filename = '../data/staging/' . date('Y-m-d\TH-i-i\Z') . '_' . $_POST['sender'] .'.html';
file_put_contents($filename, $out);

// Log file
file_put_contents($config['LOG_FILE'], "\n---\n" . print_r ($_POST, TRUE), FILE_APPEND | LOCK_EX);		

?>
