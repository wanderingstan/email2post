<?
require_once "../config.php";
global $config;

if (!is_dir($config['STAGING_DIR'])) {
    mkdir($config['STAGING_DIR'], 0777, true);
    chmod($config['STAGING_DIR'], 0777);
}
if (!is_dir($config['LETTERS_DIR'])) {
    mkdir($config['LETTERS_DIR'], 0777, true);
    chmod($config['LETTERS_DIR'], 0777);
}
if (!is_dir($config['COMPLETED_DIR'])) {
    mkdir($config['COMPLETED_DIR'], 0777, true);
    chmod($config['COMPLETED_DIR'], 0777);
}

?>
