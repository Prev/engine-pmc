<?php
	
	class TestSQLController extends Controller {

		function procSQL() {
			echo 'TestSQLController::procSQL';

			/*var_dump2(
				DBHandler::for_table('article_comment')
					->select('article_comment.*')
					->join('user', array(
						'user.id', '=', 'article_comment.writer_id'
					))
					->find_many()
			);
			
			
			/*$d = DBHandler::for_table('login_log')
				->where_equal('input_id', 'tester')
				->find_many();*/

			/*$data = DBHandler::for_table('login_log')
				->select('login_log.*')
				->join('user', array(
					'user.input_id', '=', 'login_log.input_id'
				))
				->limit(10)
				->find_many();

			for ($i=0; $i < count($data); $i++) { 
				var_dump2($data[$i]->getData());
			}*/
			//var_dump2($d);

			/*DBHandler::for_table('article')
				->where('board_id', 1)
				->find_one();*/
			//$d = DBHandler::for_table('article')
				//->raw_execute('SHOW KEYS FROM pmc_article WHERE Key_name =  "PRIMARY"');
			/*$d = DBHandler::raw_execute('SHOW KEYS FROM pmc_article WHERE Key_name =  "PRIMARY"');
			$d2 = DBHandler::get_db()
				->query('SHOW KEYS FROM pmc_article WHERE Key_name =  "PRIMARY"');

			var_dump2($d);
			foreach ($d2 as $row) {
				var_dump2($row);
			}
			*/
			//var_dump2(self::get_last_statement());
		}

	}