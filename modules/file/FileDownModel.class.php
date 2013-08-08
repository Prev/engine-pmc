<?php
	
	class FileDownModel extends Model {

		public function getFileUrl($fileHash, $articleNo=NULL) {
			$row = DBHandler::for_table('files')
				->select_many('files.*', 'article_files.file_name')
				->join('article_files', array(
					'article_files.file_id', '=', 'files.id'
				))
				->where('files.file_hash', $fileHash);
			if ($articleNo)
				$row->where('article_files.article_no', $articleNo);

			return $row->find_one();
		}

	}