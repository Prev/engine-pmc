<?php

	class FileUploadModel extends Model {

		public function getFileData($fileHash) {
			return DBHandler::for_table('files')
				->where('file_hash', $fileHash)
				->find_one();
		}

		public function insertFileData($isBinary, $fileHash, $fileSize) {
			$record = DBHandler::for_table('files')->create();
			$record->set(array(
				'is_binary' => $isBinary,
				'file_hash' => $fileHash,
				'file_size' => $fileSize
			));
			$record->save();
		}

	}