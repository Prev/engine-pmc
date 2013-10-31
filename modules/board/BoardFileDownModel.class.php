<?php
	
	class BoardFileDownModel extends Model {

		public function getFileData($fileHash, $articleNo) {
			return DBHandler::for_table('files')
				->select_many('files.*', 'article_files.file_name', 'board.readable_group')
				->join('article_files', array(
					'article_files.file_id', '=', 'files.id'
				))
				->join('article', array(
					'article.no', '=', 'article_files.article_no'
				))
				->join('board', array(
					'board.id', '=', 'article.board_id'
				))
				->where('files.file_hash', $fileHash)
				->where('article_files.article_no', $articleNo)
				->find_one();
		}
		
	}