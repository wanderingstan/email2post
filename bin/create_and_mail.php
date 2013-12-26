<?php
// Called by cron job to assemble all existing letters and mail them

include_once "../config.php";
include_once "create_letter.php";
include_once "lob_mail_letter.php";

$jobName = date('Y-m-d\TH-i-i\Z');
$pdfFile = "../data/letters/letter_" . $jobName . ".pdf";

try {
	// Create the letter
	create_letter_from_emails($pdfFile, FALSE);

	// Mail the letter!
	$lob = new lob_mail_letter($pdfFile, $jobName);

	$lob->mail_letter();

	print_r($result);
}
catch (noEmailsStagedException $e) {
	print "No emails are staged to be printed. Nothing to do.\n";
}
?>
