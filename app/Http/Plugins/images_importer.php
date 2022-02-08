<?php
class imgImportController {
	private $_importPerBatch = 5000;
	private $_arraysDir = '_images_import_array_parts';
	private $_dbFile = '_images_import.db';
	private $_db = array();
	private $_dbInited = false;
	public function run($action = '') {
		if(empty($action))
			$action = 'index';
		$method = '_'. $action. 'Action';
		return call_user_func(array($this, $method));
	}
	private function _splitArrayAction() {
		$allLinks = require 'images_list.php';
		$total = count($allLinks);
		$batch = array();
		$importNum = 0;
		foreach($allLinks as $i => $l) {
			$batch[] = $l;
			if($i >= $this->_importPerBatch + $importNum * $this->_importPerBatch) {
				$filename = 'images_list_'. $importNum. '.php';
				$this->_print($filename);
				$this->_writeArray($this->_arraysDir. '/'. $filename, $batch);
				$importNum++;
				$batch = array();
			}
		}
		$this->_print('Done with '. $total);
	}
	private function _indexAction() {
		$importParts = glob($this->_arraysDir. '/*');
		usort($importParts, array($this, 'sortPartsClb'));
		$view = '<table>';
		foreach($importParts as $i => $p) {
			$view .= '<tr><td>'. $p. '</td><td><a href="?action=findImgs&step='. $i. '" target="_blank">Run</a></td></tr>';
		}
		$view .= '</table>';
		echo $view;
	}
	public function sortPartsClb($a, $b) {
		$numA = preg_replace('/\D/', '', $a);
		$numB = preg_replace('/\D/', '', $b);
		if($numA > $numB)
			return 1;
		if($numA < $numB)
			return -1;
		return 0;
		//var_dump($numA, $a);
	}
	private function _findImgsAction() {
		$step = isset($_GET['step']) ? $_GET['step'] : 0;
		$filename = 'images_list_'. $step. '.php';
		$filepath = $this->_arraysDir. '/'. $filename;
		if(!file_exists('$filepath')) {

		}
		$links = require $filepath;
		$this->_print('Start for '. count($links));
		$this->_print('File '. $filename);
		$dir = '..';
		$iter = 0;
		$temp = 'import_images/';
		if (!is_dir($temp)) {
			mkdir($temp, 0777);
		}
		$alreadyDone = $this->_getDb('done');
		foreach($links as $k => $url) {
			$key = md5($url);
			if($alreadyDone && isset($alreadyDone[$key])) continue;

			$newFilePath = $temp . basename($url);

			$fileUrl = strpos($url, 'uploads') === false ? 'https://premizez.com/wp-content/uploads/'.$url : $url;
			$fileLocation = $dir. '/'. str_replace(array('http://', 'https://'), '', $fileUrl);

			//if(!file_exists($newFilePath) ) {
				if(!file_exists($fileLocation)) {
					// Try to find image resized copy if original file is missing
					$fileLocationSized = $this->_fallBackWithImageSized($fileLocation);
					if($fileLocationSized) {
						$fileLocation = $fileLocationSized;
					} else {
						// In this case - maybe we have invalid character in file path - from italian or grees lang. - try to find it
						$fileLocationValidCharacter = $this->_fallBackWithInvalidCharacter($fileLocation);
						if($fileLocationValidCharacter) {
							$fileLocation = $fileLocationValidCharacter;
						}
					}
				}
				if(file_exists($fileLocation)) {
					$fileContent = file_get_contents($fileLocation);

					if($fileContent !== false) {
						$result = file_put_contents($newFilePath, $fileContent);

						if($result === false) {
							$this->_makeOut($fileUrl, $fileLocation, 'File write error', $iter);
						} elseif(empty($result)) {
							$fileLocation = $this->_fallBackWithImageSized($fileLocation);
							if($fileLocation) {
								$result = file_put_contents($newFilePath, file_get_contents($fileLocation));
							}
							if(empty($result)) {
								$this->_makeOut($fileUrl, $fileLocation, 'File zero write', $iter);
							} else {
								// Well, this is success I think
								$this->_finishWihFile($key, $alreadyDone);
							}
						} else {
							// Well, this is success I think
							$this->_finishWihFile($key, $alreadyDone);
						}
					} else {
						$this->_makeOut($fileUrl, $fileLocation, 'File content empty', $iter);
					}
				} else {
					$this->_makeOut($fileUrl, $fileLocation, 'File not found', $iter);
				}
				$iter++;
			//} else {
			//	$this->_makeOut($fileUrl, $fileLocation, 'File not found');
			//}
		}
		$this->_print('Done');
	}
	private function _makeOut($url, $path, $error, $iter) {
		echo '<b>'. $error. '</b> at <i>'. $iter. '<i><br />';
		echo 'URL: ['. $url. ']<br />Path: ['. $path. ']<br />';
		$this->end();
		exit();
	}
	private function _fallBackWithImageSized($path) {
		$pathExt = explode('.', $path);
		$ext = $pathExt[ count($pathExt) - 1 ];
		$pathAny = str_replace('.'. $ext, '', implode('.', $pathExt));
		$globFind = glob($pathAny. '*');
		if($globFind && count($globFind) > 0) {
			$res = '';
			for($i = count($globFind) - 1; $i >= 0; $i--) {
				if($globFind[ $i ] != $path) {
					$res = $globFind[ $i ];
					break;
				}
			}
			return empty($res) ? $globFind[ count($globFind) - 1 ] : $res;
		}
		return false;
	}
	private function _fallBackWithInvalidCharacter($path) {
		if(strpos($path, '?') !== false) {
			$globReq = str_replace('?', '*', $path);
			$globFind = glob($globReq);
			if(!empty($globFind))
				return $globFind[ count($globFind) - 1 ];
		}
		return false;

	}
	private function _finishWihFile($md5Key, $alreadyDone) {
		$alreadyDone[$md5Key] = 1;
		$this->_writeDb('done', $alreadyDone);
	}
	private function _writeArray($filename, $data) {
		file_put_contents($filename, '<?php return array("'. implode('","', $data). '");');
	}
	private function _print($str) {
		echo $str. '<br />';
	}
	private function _getDb($key) {
		$this->_initDb();
		return isset($this->_db[ $key ]) ? $this->_db[ $key ] : false;
	}
	private function _initDb() {
		if(!$this->_dbInited) {
			if(file_exists($this->_dbFile)) {
				$fileData = file_get_contents($this->_dbFile);
				if(!empty($fileData)) {
					$this->_db = unserialize($fileData);
				}
			} else {
				file_put_contents($this->_dbFile, '');
			}
			$this->_dbInited = true;
		}
	}
	private function _writeDb($key, $value) {
		$this->_initDb();
		$this->_db[ $key ] = $value;
	}
	public function end() {
		$this->_finalizeDb();
	}
	private function _finalizeDb() {
		file_put_contents($this->_dbFile, serialize($this->_db));
	}
	/*private function _makeOut() {

	}
	*/
}
$action = isset($_GET['action']) ? $_GET['action'] : '';
$c = new imgImportController();
$c->run($action);
$c->end();
exit();
/*$links = require 'images_list.php';
getImagesArchive($links);*/
//exec('tar -zcvf import_images.tar import_images/');
//exec('rm -r import_images/');
//var_dump('lalala');
//exit;

function getImagesArchive($links) {
	echo 'start...<br />';
	try {
		$temp = 'import_images/';

		if (!is_dir($temp)) {
			mkdir($temp, 0777);
		}
		$dir = '..';

		//3-bedrooms-2-bathrooms-villa-for-sale-in-el-coto-r3077794-2.jpg
		/*$searchData = glob("../premizez.com/wp-content/uploads/2018/03/3-bedrooms-2-bathrooms-villa-for-sale-in-el-coto-r3077794-2*");
		var_dump($searchData);
		exit();*/
		$iter = 0;
		foreach($links as $k => $url) {
			$newFilePath = $temp . basename($url);
			$fileUrl = strpos('uploads', $url) == false ? 'https://premizez.com/wp-content/uploads/'.$url : $url;
			$fileLocation = $dir. '/'. str_replace(array('http://', 'https://'), '', $fileUrl);
//			var_dump($url);
//			var_dump($fileUrl);
//			var_dump(strpos('uploads', $url));
//			exit;
			//if(!file_exists($newFilePath) ) {
				if(!file_exists($fileLocation)) {
					$fileLocation = _tryGetSized($fileLocation);
				}
				if(file_exists($fileLocation)) {
					$fileContent = file_get_contents($fileLocation);

					if($fileContent !== false) {
						$result = file_put_contents($newFilePath, $fileContent);

						if($result === false) {
							makeOut($fileUrl, $fileLocation, 'File write error', $iter);
						} elseif(empty($result)) {
							makeOut($fileUrl, $fileLocation, 'File zero write', $iter);
						}
					} else {
						makeOut($fileUrl, $fileLocation, 'File content empty', $iter);
					}
				} else {
					makeOut($fileUrl, $fileLocation, 'File not found', $iter);
				}
				$iter++;
			//} else {
			//	makeOut($fileUrl, $fileLocation, 'File not found');
			//}
		}
	} catch (Exception $e) {
		echo "Exception : " . $e;
	}
	echo 'Done!';
}

function _tryGetSized($path) {
	$pathExt = explode('.', $path);
	$ext = $pathExt[ count($pathExt) - 1 ];
	$pathAny = str_replace('.'. $ext, '', implode('.', $pathExt));
	$globFind = glob($pathAny. '*');
	if($globFind && count($globFind) > 0)
		return $globFind[ count($globFind) - 1 ];
	return false;
}

/*function makeOut($url, $path, $error, $iter) {
	global $c;
	echo '<b>'. $error. '</b> at <i>'. $iter. '<i><br />';
	echo 'URL: ['. $url. ']<br />Path: ['. $path. ']<br />';
	$c->end();
	exit();
}*/
