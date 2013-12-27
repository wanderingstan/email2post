<?
// TODO: remove insecure from curl!
// 	public $pdf_file = '../data/letters/letter_2013-12-26MST11-08-08-25200.pdf';

require_once "../config.php";

class lob_mail_letter
{
	public $lob_object;
	public $lob_job;
	public $object_name;
	public $job_name;

	public function __construct($letter_pdf_file, $job_name = '') {
		$this->letter_pdf_file = $letter_pdf_file;
		$this->job_name = ($job_name ? $job_name : $letter_pdf_file);
		$this->object_name = $this->job_name;
	}

	public function mail_letter() {
		global $config;

		$lob_object_cmd = <<<EOF
			curl --insecure https://api.lob.com/v1/objects \
			-u {$config['LOB_API_KEY']}: \
			-F "name={$this->letter_pdf_file}" \
			-F "file=@{$this->letter_pdf_file}" \
			-F "setting_id={$config['LOB_SETTING_ID']}" \

EOF;
		// print ($lob_object_json);

		print $config['LOB_API_KEY'];
		print("lob_object_cmd:\n" . $lob_object_cmd);

		$lob_object_json = `$lob_object_cmd`;
		$this->lob_object = json_decode($lob_object_json);
		print_r($this->lob_object);

		// file_put_contents($config['log_file'], print_r ($this->lob_object, TRUE), FILE_APPEND | LOCK_EX);

		if (!$this->lob_object->id) {
			throw new Exception('Problem creating lob object.');
			return 0;
		}

		$create_lob_job_cmd = <<<EOF
			curl --insecure https://api.lob.com/v1/jobs \
			-u {$config['LOB_API_KEY']}: \
			-d "name={$this->job_name}" \
			-d "to={$config['LOB_TO_ADDRESS_ID']}" \
			-d "from={$config['LOB_FROM_ADDRESS_ID']}" \
			-d "object1={$this->lob_object->id}" \

EOF;

		print "create_lob_job_cmd:\n";
		print $create_lob_job_cmd;

		$lob_job_json = `$create_lob_job_cmd`;
		$this->lob_job = json_decode($lob_job_json);

		print_r ($this->lob_job);

		// file_put_contents($config['log_file'], print_r ($this->lob_job, TRUE), FILE_APPEND | LOCK_EX);
	}

}

