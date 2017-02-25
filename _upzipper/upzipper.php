<?php 
namespace upzipper;

class upzipper {
	/**
	 * Base dir
	 * @var string
	 */
	protected $dir;

	/**
	 * Project dir
	 * @var string
	 */
	protected $project_dir;

	/**
	 * data.json path
	 * @var string
	 */
	protected $data_file;

	/**
	 * stored data
	 * @var array
	 */
	protected $data;

	/**
	 * Files list array
	 * @var array
	 */
	protected $filesList;

	/**
	 * New files
	 * @var array
	 */
	protected $filesNew;

	/**
	 * Load data.json file
	 */
	private function _load_data () {
		if (file_exists($this->data_file)){
			$string = file_get_contents($this->data_file);
			$json = json_decode($string, true);
			$this->data = $json;
		}else{
			$file = fopen($this->data_file, "w") or die("Unable to open file!");
			$_data = array(
				'build_last' => 0,
				'build_date' => '',
				'data' => array()
			);
			$txt = json_encode($_data);
			fwrite($file, $txt);
			fclose($file);
			$this->data = $_data;
		}
	}


	/**
	 * Load file structure
	 */
	private function _load_file_structure () {	
		$filesList = array();
		$rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->project_dir, \RecursiveDirectoryIterator::SKIP_DOTS));
		foreach($rii as $file) {
			$filename = $file->getPathname();
			if ( ! preg_match('/(_upzipper|upzipper.php)/', $filename ) ) {
				array_push($filesList,$filename );
			}
		}
		$this->filesList = $filesList;
	}

	/**
	 * Check file hash
	 */
	private function _check_hash () {
		foreach ($this->filesList as $file) {
			$hash = md5_file($file);
			if (!array_key_exists($file, $this->data['data'])) {
				$this->filesNew[$file] = $hash;
			}else{
				if( $this->data['data'][$file] != $hash ) {
					$this->filesNew[$file] = $hash;
				}
			}
		}
	}

	/**
	 * Update data.json
	 */
	private function _update_data () {
		$new_data = array_merge($this->data['data'], $this->filesNew);
		$this->data['data'] = $new_data;
		$this->data['build_last'] = $this->data['build_last'] + 1;
		$this->data['build_date'] = date('d-m-Y');

		$string = json_encode($this->data);
		$file = fopen($this->data_file, "w");
		fwrite($file, $string);
		fclose($file);
	}

	/**
	 * Create update.zip 
	 */
	private function _createZip () {
		if(!empty($this->filesNew)){
			$zip = new \ZipArchive;
			$filename = $this->dir.DIRECTORY_SEPARATOR."builds".DIRECTORY_SEPARATOR."update-".str_replace( '-', '', $this->data['build_date'])."-".str_pad( $this->data["build_last"], 5, "0", STR_PAD_LEFT).".zip";
			if ($zip->open($filename, \ZipArchive::CREATE)) {
				foreach ( $this->filesNew as $file => $hash) {
					$path = ltrim( str_replace($this->project_dir, '', $file),  DIRECTORY_SEPARATOR );
					$zip->addFile($file, $path);
				}
				$zip->close();
				return $filename;
			}
		}

		return false;
	}
	/**
	 * Main constructer
	 */
	function __construct () {
		$this->dir = dirname(__FILE__);
		$this->project_dir = str_replace(DIRECTORY_SEPARATOR.'_upzipper', '', $this->dir);
		$this->data_file = $this->dir.DIRECTORY_SEPARATOR.'_inc'.DIRECTORY_SEPARATOR.'data.json';
		$this->filesNew = array();

		$this->_load_data();
	}


	/**
	 * Create new update zip
	 * @param  string $base_url
	 */
	public function build ( $base_url = '' ) {
		$this->_load_file_structure();
		$this->_check_hash();
		if ( ! empty($this->filesNew) )		
			$this->_update_data();

		if ( $path = $this->_createZip() ) {
			$filename = basename($path);
			$rel_path = str_replace($this->project_dir, '', $path);
			$rel_path = str_replace('\\', '/', $rel_path);
			return array(
				'name' => $filename,
				'url' => $base_url.$rel_path
			);
		}else{
			return false;
		}	
	}


	/**
	 * Get last build name and url
	 * @return [type] [description]
	 */
	public function last_build ( $base_url = '' ) {
		$path = $this->dir.DIRECTORY_SEPARATOR."builds".DIRECTORY_SEPARATOR."update-".str_replace( '-', '', $this->data['build_date'])."-".str_pad( $this->data["build_last"], 5, "0", STR_PAD_LEFT).".zip";
		$filename = basename($path);
		$rel_path = str_replace($this->project_dir, '', $path);
		$rel_path = str_replace('\\', '/', $rel_path);
		return array(
			'name' => $filename,
			'url' => $base_url.$rel_path
		);
	}
}
