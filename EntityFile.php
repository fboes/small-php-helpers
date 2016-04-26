<?php
# namespace fboes\SmallPhpHelpers;

/**
 * @class EntityFile
 * Extends Entity; allows files to be attached to a single DB row
 */
class EntityFile extends Entity {
	/**
	 * Directory for files, relative to document root. Omit trailing slash
	 * @var string
	 */
	protected $webDir            = '/uploads';
	/**
	 * Document root, defaults to $_SERVER['DOCUMENT_ROOT']. Omit trailing slash
	 * @var string
	 */
	protected $documentRoot      = '';
	/**
	 * List of files if a read-operation took place
	 * @var array
	 */
	public $_files = array();

	public function __construct () {
		if (empty($this->documentRoot)) {
			$this->documentRoot = $_SERVER['DOCUMENT_ROOT'];
		}
		return parent::__construct();
	}


	// --------------------------------------------------
	// CREATE / UPDATE
	// --------------------------------------------------

	/**
	 * Move (temporary) file to new location for storage with this row
	 * @param  [type] $tmpFilename   [description]
	 * @param  [type] $storeFilename [description]
	 * @return [type]                [description]
	 */
	public function storeFile ($tmpFilename, $storeFilename) {
		$filename = $this->getFilename($storeFilename, TRUE);
		$path = dirname($filename);
		if (!file_exists($path)) {
			$old_umask = umask(0);
			if (!mkdir($path, 0777, TRUE)) {
				throw new \Exception('Unable to create directory '.$path);
			}
			umask($old_umask);
		}
		return rename($tmpFilename, $filename);
	}

	// --------------------------------------------------
	// READ
	// --------------------------------------------------

	/**
	 * Check if file exists and return its name if it is there
	 * @param  string  $filename [description]
	 * @param  boolean $absolute if paths returned are absolute or web paths
	 * @return boolean           [description]
	 */
	public function getFile ($filename, $absolute = FALSE) {
		$absoluteFilename = $this->getFilename($filename, TRUE);
		return file_exists($absoluteFilename)
			? ($absolute) ? $absoluteFilename : $this->getFilename($filename)
			: NULL
		;
	}

	/**
	 * Get all files attaches to this entity
	 * @param  boolean $absolute if paths returned are absolute or web paths
	 * @return array            of paths to files
	 */
	public function getAllFiles ($absolute = FALSE) {
		$files = glob(
			$this->getFilename('', TRUE) . '*'
		);
		$filesRelative = array();
		foreach ($files as &$f) {
			$filesRelative[] = $this->getFilename($f);
		}
		$this->_files = $filesRelative;
		return $absolute ? $files : $filesRelative;
	}

	// --------------------------------------------------
	// DELETE
	// --------------------------------------------------

	/**
	 * Delete file for this entity
	 * @param  string  $filename [description]
	 * @return boolean           [description]
	 */
	public function deleteFile ($filename) {
		return unlink($this->getFilename($filename, TRUE));
	}

	/**
	 * Delete all files attached to this entity, and then delete directory for entity
	 * @return integer number of dleted files or -1 if something went wrong
	 */
	public function deleteAllFiles () {
		$files = $this->getAllFiles();
		$count = 0;
		foreach ($files as $f) {
			if ($this->deleteFile($f) && $count >= 0) {
				$count ++;
			} else {
				$count = -1;
			}
		}
		if ($count >= 0) {
			rmdir(dirname($this->getFilename('x', TRUE)));
		}
		return $count;
	}

	// --------------------------------------------------
	// OTHER STUFF
	// --------------------------------------------------

	/**
	 * Convert file to a filename matching this entity
	 * @param  string $filename [description]
	 * @return string           filename with tablename / id / filename
	 */
	public function getFilename ($filename, $absolute = FALSE) {
		$id = $this->getId();
		$filenameReturned = !empty($id)
			? $this->webDir . '/' . $this->tableName . '/' . $id . '/' . preg_replace('#([^a-z0-9_\-\.])#is','',strtolower(basename($filename)))
			: NULL
		;
		return ($absolute)
			? $this->convertRelativeToAbsoluteFilename($filenameReturned)
			: $filenameReturned
		;
	}

	/**
	 * Convert relative filename to absolute filename
	 * @param  string $filename [description]
	 * @return string           [description]
	 */
	public function convertRelativeToAbsoluteFilename ($filename) {
		return !empty($filename) ? $this->documentRoot . $filename : NULL;
	}

	/**
	 * Convert array of relative filenames to absoluet filenames
	 * @param  array $files [description]
	 * @return array        [description]
	 */
	public function convertRelativeToAbsoluteFilenames ($files) {
		foreach ($files as &$f) {
			$f = $this->convertRelativeToAbsoluteFilename($f);
		}
		return $files;
	}

	/**
	 * Add file array to entity after doing a fetch
	 * @return Entity $this
	 */
	public function postFetch () {
		parent::postFetch();
		$this->getAllFiles();
		return $this;
	}
}
