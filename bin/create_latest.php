<?php
require_once "../config.php";
require_once "create_letter.php";
include_once "setup.php";

// Create latest rev of pdf
create_letter_from_emails($config['STAGING_DIR'] . '/' . 'latest.pdf', FALSE);

?>