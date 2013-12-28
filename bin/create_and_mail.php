<?php
// Called by cron job to assemble all existing letters and mail them

require_once "../config.php";
require_once "create_letter.php";
require_once "lob_mail_letter.php";
include_once "setup.php"

$jobName = date('Y-m-d\TH-i-i\Z') . "_Pete_Live";
$pdfFile = "../data/letters/letter_" . $jobName . ".pdf";

try {
	// Create the letter
	create_letter_from_emails($pdfFile, TRUE);
	print "PDF created at " . $pdfFile . "\n";

	// Mail the letter!
	$lob = new lob_mail_letter($pdfFile, $jobName);
	$lob->mail_letter();
}
catch (noEmailsStagedException $e) {
	print "No emails are staged to be printed. Nothing to do.\n";
}

?>
