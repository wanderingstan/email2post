<?php
// Called by cron job to assemble all existing letters and mail them

require_once "../config.php";
require_once "create_letter.php";
require_once "lob_mail_letter.php";

$jobName = date('Y-m-d\TH-i-i\Z Pete');
$pdfFile = "../data/letters/letter_" . $jobName . ".pdf";

try {
	// Create the letter 
	create_letter_from_emails($pdfFile, FALSE);

	// print "PDF created. Visible at " . $config['BASE_URL'] . '/' . 

	// Mail the letter!
	$lob = new lob_mail_letter($pdfFile, $jobName);

	$lob->mail_letter();

	print_r($result);

}
catch (noEmailsStagedException $e) {
	print "No emails are staged to be printed. Nothing to do.\n";
}

?>