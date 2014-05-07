<?

// Web
$config['BASE_URL'] = 'http://<YOURWEBSITE>/';
$config['ADMIN_EMAIL'] = '<YOUREMAIL>';

// Directory

$config['LOG_DIR']  = dirname(__FILE__) . '/logs';
$config['LOG_FILE'] = $config['LOG_DIR'] . '/log.txt';

$config['STAGING_DIR']   = dirname(__FILE__) . '/data/staging';
$config['LETTERS_DIR']   = dirname(__FILE__) . '/data/letters';
$config['COMPLETED_DIR'] = dirname(__FILE__) . '/data/completed';

// LOB Settings

$config['LOB_API_KEY'] = '<YOUR_LOB_KEY>';
$config['LOB_SETTING_ID'] = 100;

$config['LOB_FROM_ADDRESS_ID'] = '<LOB_ADDRESS>'; // E.g  adr_0123450123456789
$config['LOB_TO_ADDRESS_ID']   = '<LOB_ADDRESS>'; // E.g. adr_0123450123456789

$config['CONFIRMATION_EMAIL_BODY'] = <<<EOF
This is an automatic message to let you know we recived your message, and it will be printed and mailed within a few days.
EOF;
?>

