<?php
	
	class BoardEditorModel extends Model {

		public function getBoardLists() {
			$arr = DBHandler::for_table('board')
				->find_many();

			for ($i=0; $i<count($arr); $i++) {
				$arr[$i] = $arr[$i]->getData();
				
				$me = User::getCurrent();
				if (!$me || (isset($arr[$i]->writable_group) && !$me->checkGroup(json_decode($arr[$i]->writable_group)))) {
					unset($arr[$i]);
					continue;
				}
				
				$arr[$i]->name_locale = fetchLocale($arr[$i]->name_locales);
			}
			return $arr;
		}

		public function getArticleTitle($articleNo) {
			return DBHandler::for_table('article')
				->select('title')
				->where('no', $articleNo)
				->find_one();
		}

		public function getArticleData($articleNo) {
			return DBHandler::for_table('article')
				->where('article.no', $articleNo)
				->find_one();
		}

		public function getArticleFiles($articleNo) {
			return DBHandler::for_table('article_files')
				->select_many('files.*', 'article_files.file_name')
				->where('article_files.article_no', $articleNo)
				->join('files', array(
					'files.id', '=', 'article_files.file_id'
				))
				->find_many();
		}
	}