<?php

	class FileUploadModel extends Model {

		public function getFileData($fileType, $fileHash) {
			return DBHandler::for_table('files')
				->where('file_type', $fileType)
				->where('file_hash', $fileHash)
				->find_one();
		}

		public function insertFileData($fileType, $fileHash, $fileSize) {
			$record = DBHandler::for_table('files')->create();
			$record->set(array(
				'file_type' => $fileType,
				'file_hash' => $fileHash,
				'file_size' => $fileSize
			));
			$record->save();
			return $record;
		}

	}