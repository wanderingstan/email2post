<?php

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
require_once "../config.php";
include_once "setup.php";

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
	 * @param $isHTML (boolean) if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function PrintLetter($content_dir) {
		// add a new page
		$this->AddPage();
		// disable existing columns
		$this->resetColumns();
		// set columns
		$this->setEqualColumns(2, 90);
		// print body
		return $this->LetterBody($content_dir);
	}

    /**
     * Take mailgun POST data and format our message.
     */
    public function messageFromSerialized($serializedFilename, $isHTML) {
        $postData = unserialize(file_get_contents($serializedFilename));
        if (!$postData['From']) {
            // empty email. skip it
            return "";
        }
        if ($isHTML) {
            $from = htmlentities($postData['From']);
            $subject = htmlentities($postData['Subject']);

            $out .= "<hr><p>\n";
            $out .= "From: <b>{$from}<b><br>\n";
            $out .= "Date: <b>{$postData['Date']}</b><br>\n";
            // $out .= "To: <b>{$postData['To']}</b><br>\n";
            $out .= "Subject: <b>{$subject}<b><br>\n";
            $out .= "</p>\n";
            $out .= "<!--BEGIN EMAIL-->\n";
            //$out .= $postData['stripped-html'];
            $out .= $postData['body-html'];
            $out .= "<p></p>\n<!--END EMAIL-->\n";
        }
        else {
            $out .= "From: {$postData['From']}\n";
            $out .= "Date: {$postData['Date']}\n";
            $out .= "Subject: {$postData['Subject']}\n";
            $out .= "\n";
            $body = preg_replace('/\r\n/',"\n", $postData['body-plain']);
            $body = preg_replace('/([^\n])\n([^\n])/s','\1 \2', $body);
            $out .= $body;
            $out .= "\n-----------------------------------------------------------------------\n";
        }
        return $out;
    }

    public function plainHTMLmessageFromSerialized($serializedFilename) {
        $postData = unserialize(file_get_contents($serializedFilename));
        if (!$postData['From']) {
            // empty email. skip it
            return "";
        }

        $from = htmlentities($postData['From']);
        $subject = htmlentities($postData['Subject']);

        $out .= "<hr><p>\n";
        $out .= "From: <b>{$from}</b><br>\n";
        $out .= "Date: <b>{$postData['Date']}</b><br>\n";
        // $out .= "To: <b>{$postData['To']}</b><br>\n";
        $out .= "Subject: <b>{$subject}</b>\n";
        $out .= "</p>\n";
        $out .= "<!--BEGIN EMAIL-->\n";
        $body = preg_replace('/\r\n/',"\n", $postData['body-plain']);
        $body = preg_replace('/([^\n])\n([^\n])/s','\1 \2', $body);
        $body = preg_replace('/\n\n/s',"\n<br><br>\n", $body);
        $out .= $body;
        $out .= "<p></p>\n<!--END EMAIL-->\n";
        return $out;
//     	if ($isHTML) {
// 	    }
    }

    public function includeImage($fileName, $entry) {
        // $imageSize = getimagesize($fileName);
        // $width = $imageSize[0];
        // $height = $imageSize[1];
        // style='width:{$width};height:{$height};'
        $out .= '<img src="../data/staging/'.$entry.'">Filename: '.$entry.'<br>';
        return $out;
    }

	/**
	 * Print body
	 * @param $file (string) name of the file containing the chapter body
	 * @param $mode (boolean) if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function LetterBody($content_dir) {
		$this->selectColumn();

		// get all emails
		$content='';
		$files= array();
		if ($handle = opendir($content_dir)) {
		    $extension = '.serialized';
		    while (false !== ($entry = readdir($handle))) {
		        print pathinfo($entry, PATHINFO_EXTENSION);
		    	if (pathinfo($entry, PATHINFO_EXTENSION) == "serialized") {
		            echo "Included file: " . $entry . "\n";
					array_push($files,$entry);
		        }
		    	if ((pathinfo($entry, PATHINFO_EXTENSION) == "jpg") || ((pathinfo($entry, PATHINFO_EXTENSION) == "jpeg"))) {
		            echo "Included image file: " . $entry . "\n";
					array_push($files,$entry);
		    	}
		        else {
		            echo "Skipping file: " . $entry . "\n";
		        }
		    }
		    closedir($handle);
		}
		sort($files);
		foreach ($files as $entry) {
	        echo "Included file: " . $content_dir . '/' . $entry . "\n";
            if (pathinfo($entry, PATHINFO_EXTENSION) == "serialized") {
                //$content .= $this->messageFromSerialized($content_dir . '/' . $entry, $isHTML);
                $content .= $this->plainHTMLmessageFromSerialized($content_dir . '/' . $entry);
            }
            elseif (pathinfo($entry, PATHINFO_EXTENSION) == "jpg") {
                $content .= $this->includeImage($content_dir . '/' . $entry, $entry);
            }
		}

		if ((count($files) == 0) || ($content == "")) {
		    print (count($files) . " files found\n");
			// There were no emails
			throw new noEmailsStagedException;
		}

		// set font
		$this->SetFont('times', '', 9);
		$this->SetTextColor(50, 50, 50);

        // ------ HTML MODE ------
        $this->writeHTML($content, true, false, true, false, 'J');

        //------ TEXT MODE ------
        //$this->Write(0, $content, '', 0, 'J', true, 0, false, true, 0);

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
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, "Pony Tales", PDF_HEADER_STRING);

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

	$pdf->PrintLetter($config['STAGING_DIR']);

//	if ($pdf->getNumPages() > 8) {
//		throw new tooManyPagesException;
//	}

	//Close and output PDF document
	// 'I' = inline 'F' = save to file
	$pdf->Output($pdfFile, 'F');

	print "Saved to {$pdfFile}\n";

	// ---------------------------------------------------------

	if ($clearStaging) {
		// Move our completed emails out of staging
		shell_exec('mv ' . $config['STAGING_DIR'] . '/* ../data/completed/');
	}

	return 1;
}