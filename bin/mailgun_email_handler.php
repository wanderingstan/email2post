<?php
// CGI handler that is called by mailgun when a new email is received

$mailbox = $_GET['mailbox'];

$out .= "<hr>\n";
$out .= "<p>From: {$_POST['From']}<p>\n";
$out .= "<p>To: {$_POST['To']}<p>\n";
$out .= "<p>Date: {$_POST['Date']}<p>\n";
$out .= "<!--BEGIN EMAIL-->\n";
$out .= $_POST['stripped-html'];
$out .= "\n<!--END EMAIL-->\n";

$filename = '../data/staging/' . date('Y-m-d\TH-i-i\Z' . '.html';

// TODO: save images, add to html

file_put_contents($filename, $out);

?>
