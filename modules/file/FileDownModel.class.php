<?php
	
	class FileDownModel extends Model {

		public function getFileUrl($fileHash) {
			return DBHandler::for_table('files')
				->where('file_hash', $fileHash)
				->find_one();
		}

	}