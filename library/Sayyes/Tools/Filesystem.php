<?php

class Sayyes_Tools_Filesystem {
	
	public static function createdir($path) {
		mkdir($path);
	}
	
	public static function removeFile($path) {
		unlink($path);
	}
	
	public static function backupFile($path) {
		if(file_exists($path)) {
			copy($path, $path . '.bak');
		}
	}
	
	public static function createFile($path, $data, $backup = true) {
		if($backup) {
			self::backupFile($path);
		}
		$handle = fopen($path, 'w');
		fputs($handle, $data);
		fclose($handle);
	}
	
	public static function removeDirectory($dirname) {
	    if (is_dir($dirname))
	       $dir_handle = opendir($dirname);
	    if (!$dir_handle)
	       return false;
	    while($file = readdir($dir_handle)) {
	       if ($file != "." && $file != "..") {
	          if (!is_dir($dirname."/".$file))
	             self::removeFile($dirname."/".$file);
	          else
	             self::removeDirectory($dirname.'/'.$file);    
	       }
	    }
	    closedir($dir_handle);
	    rmdir($dirname);
	    return true;
	}
	
	public static function cleanDirectory($dirname) {
		foreach (new DirectoryIterator($dirname) as $fileInfo) {
			if($fileInfo->isDot() || $fileInfo->getFileName() == '.svn') continue;
				if($fileInfo->isDir()) {
					Sayyes_Tools_Filesystem::removeDirectory($dirname . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
				}
				else if($fileInfo->isFile()) {
					Sayyes_Tools_Filesystem::removeFile($dirname . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
				}
			}		    
	}
	
	public static function getDirectoryList($base, $ignores = array()) {
		$directories = array();
		if(is_dir($base)) {
			foreach (new DirectoryIterator($base) as $fileInfo) {
				if($fileInfo->isDir() && !$fileInfo->isDot() && !in_array($fileInfo->getFileName(), $ignores)) {
					$directories[] = $fileInfo->getFileName();
				}
			}
		}
		return $directories;
	}
	
	public static function getByExtension($base, $ext) {
		$files = array();
		if(is_dir($base)) {
			foreach (new DirectoryIterator($base) as $fileInfo) {
				if(!$fileInfo->isDir() && !$fileInfo->isDot() && $fileInfo->getFileName() != '.DS_Store' && strtolower(substr(strrchr($fileInfo->getFileName(), '.'), 1)) == strtolower($ext)) {
					$files[] = $fileInfo->getFileName();
				}
			}
		}
		return $files;
	}
	
	public static function getFiles($base) {
		$files = array();
		if(is_dir($base)) {
			foreach (new DirectoryIterator($base) as $fileInfo) {
				if(!$fileInfo->isDir() && !$fileInfo->isDot() && $fileInfo->getFileName() != '.DS_Store') {
					$files[] = $fileInfo->getFileName();
				}
			}
		}
		return $files;
	}
	
	public static function getFilesByDir($base, $recusirve = false) {
		$files = array();
		if(is_dir($base)) {
			foreach ( new DirectoryIterator($base) as $fileInfo) {
				if($fileInfo->isDir() && !$fileInfo->isDot() && $recusirve) {
					$files[$fileInfo->getFileName()] = self::getFilesByDir($fileInfo->getPathname(), true);
				}
				else if(!$fileInfo->isDir() && !$fileInfo->isDot() && $fileInfo->getFileName() != '.DS_Store') {
					$files[] = $fileInfo->getFileName();
				}
			}
		}
		return $files;
	}
	
	public static function getFileExtension($fileName) {
		return substr($fileName, strrpos($fileName,'.') + 1);
	}
	
	
	public static function restoreFilesFromBackup($base, $ext) {
		$datas = array();
		$files = self::getByExtension($base, $ext);
		foreach ($files as $file) {
			$backupFilePath = $base . DIRECTORY_SEPARATOR . $file;
			$restoreFilePath = $base . DIRECTORY_SEPARATOR . str_replace('.'.$ext, '', $file);
			if(file_exists($restoreFilePath)) {
				self::removeFile($restoreFilePath);
			}
			copy($backupFilePath, $restoreFilePath);
			self::removeFile($backupFilePath);
			$datas[] = str_replace('.'.$ext, '', $file);
		}
		return $datas;
	}
	
	public static function fileExist($path) {
	    return file_exists($path);
	}
	
	public static function directoryExist($path) {
	    return is_dir($path);
	}
}