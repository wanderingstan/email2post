<?php
// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
require_once "../config.php";

class noEmailsStagedException extends Exception
{
}
class tooManyPagesException extends Exception
{
}

/**
 * Extend TCPDF to work with multiple columns
 */
class MC_TCPDF extends TCPDF {

	/**
	 * Print chapter
	 * @param $content_dir (string) name of the directory containing emails and images to include
	 * @param $mode (boolean) if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function PrintLetter($content_dir, $mode=true) {
		// add a new page
		$this->AddPage();
		// disable existing columns
		$this->resetColumns();
		// set columns
		$this->setEqualColumns(2, 86);
		// print body
		return $this->LetterBody($content_dir, $mode);
	}

	/**
	 * Print body
	 * @param $file (string) name of the file containing the chapter body
	 * @param $mode (boolean) if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function LetterBody($content_dir, $mode=false) {
		$this->selectColumn();

		// get all emails
		$content='';
		$files = array();
		printf ("REading from " . $content_dir . "\n");
		if ($handle = opendir($content_dir)) {
		    while (false !== ($entry = readdir($handle))) {
		    	if (substr($entry, -strlen('.html')) === '.html') {
		    		array_push($files, $entry);
		        }
		    }
		    closedir($handle);
		}
		sort($files);
		foreach ($files as $entry) {
	        echo "Included file: " . $content_dir . '/' . $entry . "\n";
			$content .= file_get_contents($content_dir . '/' . $entry, false);
		}

		if (count($files) == 0) {
			// There were no emails
			throw new noEmailsStagedException;
		}

		// set font
		$this->SetFont('times', '', 9);
		$this->SetTextColor(50, 50, 50);
		// print content
		if ($mode) {
			// ------ HTML MODE ------
			$this->writeHTML($content, true, false, true, false, 'J');
		} else {
			// ------ TEXT MODE ------
			$this->Write(0, $content, '', 0, 'J', true, 0, false, true, 0);
		}
		$this->Ln();

		return TRUE;
	}
} // end of extended class



function create_letter_from_emails($pdfFile, $clearStaging = FALSE) {

    global $config;

	// create new PDF document
	$pdf = new MC_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Stan James');
	$pdf->SetTitle('James Family News');
	$pdf->SetSubject('This is the subject.');
	// $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

	// set default header data
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 010', PDF_HEADER_STRING);

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		require_once(dirname(__FILE__).'/lang/eng.php');
		$pdf->setLanguageArray($l);
	}

	// ---------------------------------------------------------

	$pdf->PrintLetter($config['STAGING_DIR'], true);

	if ($pdf->getNumPages() > 8) {
		throw new tooManyPagesException;
	}

	//Close and output PDF document
	// 'I' = inline 'F' = save to file
	$pdf->Output($pdfFile, 'F');

	print "Saved to {$pdfFile}\n";

	// ---------------------------------------------------------

	if ($clearStaging) {
		// Move our completed emails out of staging
		shell_exec('mv ' . $config['STAGING_DIR'] . '/*.html ../data/completed/');
	}

	return 1;
}