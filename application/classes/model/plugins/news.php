<?php defined('SYSPATH') or die('No direct script access.');

class Model_Plugins_News extends Model {
	
	public function __construct() {
		parent::__construct();
		
		$this->news = Model::factory('manager_news_news');
	}	
	
	public function load($parameters, $template, $page_data, $page_class) {
		// PARAMS
		$limit = 5;		
		$sticky_limit = 0;
		
		// GET PARAMETERS
		foreach ($parameters as $key => $val) {
			$$key = $val;
		}
		
		// CSS / JS
		$page_class->tpl->css_file[] = 'assets/plugins/news/news.css';
		$page_class->tpl->js_file[] = 'assets/plugins/news/news.js';
		
		$page_class->tpl->css_file[] = 'assets/libs/jquery-plugins/sliderkit/sliderkit-core.css';
		$page_class->tpl->css_file[] = 'assets/libs/jquery-plugins/sliderkit/sliderkit-demos.css';
		$page_class->tpl->js_file[] = 'assets/libs/jquery-plugins/sliderkit/jquery.sliderkit.1.9.2.pack.js';
		
		// SUB PAGE ALIAS
		$page_alias = CMS::getPluginPageAlias($page_data);	
		
		// PAGINATE
		$paginate = CMS::getPageAliasParam('p', $page_alias);
		if (empty($paginate)) $paginate = 1;
		
		// NEW ID
		$new_id = CMS::getPageAliasParam('i', $page_alias); 
		if (!empty($new_id)) {
			// GET NEWS
			$news = $this->news->getNews($new_id, $this->lang_id, array('from_status_id' => '10', 'page_alias' => $page_alias));
		}
		
		if (empty($news[0]['id'])) {
			//
			// LIST
			//
			
			//
			// GET NEWS
			//
			$filter_data = array(
				'from_status_id' => '10' );
				
			// GET NEWS
			$tpl_data['news'] = $this->news->getNews(null, $this->lang_id, $filter_data);
			
			// NEWS PAGE
			$news_page = CMS::getDocuments(CMS::$news_page_id, null, null, $this->lang_id);
			$tpl_data['news_page'] = $news_page[0];
			
			$tpl_data['action'] = 'list';		
			return $this->tpl->factory($template, $tpl_data);			
		} else {
			//
			// VIEW
			//
			
			if ($news[0]['type_id'] == 20) {
				$news[0]['l_link'] = mb_strtolower(mb_substr($news[0]['l_link'], 0, 4))!='http'?'http://'.$news[0]['l_link']:$news[0]['l_link'];
				$page_class->request->redirect($news[0]['l_link']);
			}
			
			$tpl_data['new'] = $news[0];
			
			// GALLERY
			$tpl_data['images'] = $this->news->getNewImages(null, $news[0]['id'], $this->lang_id);
			
			$tpl_data['page'] = $page_data['page_data'];
			
			$tpl_data['action'] = 'view';		
			return $this->tpl->factory($template, $tpl_data);
		}		
	}

	public function vote($data) {
		/*
		 * LEXICON VALUES
		 * 		news.no_selected_answer
		 * 		news.no_selected_answer_value
		 */
		
		$ret_data = array(
			'status' => 0,
			'response' => '',
			'error' => null);
		
		$question_id = $this->request->post('question_id');
		$answer_id = $this->request->post('question_answer_'.$question_id);
		$answer_value = $this->request->post('question_answer_value_'.$answer_id);
		
		if (empty($question_id) || empty($answer_id)) {
			// ANSWER ERROR
			$ret_data['error'][] = CMS::getLexicons('news.no_selected_answer');
		} else {
			$answer_data = $this->news->getAnswers($answer_id, $question_id);
			if (count($answer_data) == 0) {
				// NO ANSWER
				$ret_data['error'][] = CMS::getLexicons('news.no_selected_answer');
			} else {
				if ($answer_data[0]['type_id'] == '20' AND empty($answer_value)) {
					// NO ANSWER VALUE
					$ret_data['error'][] = CMS::getLexicons('news.no_selected_answer_value');
				} else {
					$cookie = Cookie::get('new_question_'.$question_id);
					if (empty($cookie)) {
						// SAVE ANSWER										
						if ($answer_data[0]['type_id'] == '20') {
							// FREE TEXT
							$test_value = strtolower($this->replace_latvian($answer_value));
							
							// ALL SUB VALUES
							$sub_answers = $this->news->getAnswers(null, null, null, array('parent_id' => $answer_id));
							$sub_answer_id = null;
							for ($i=0; $i<count($sub_answers) && is_null($sub_answer_id); $i++) {
								if (strtolower($this->replace_latvian($sub_answers[$i]['answer_value'])) == $test_value)
									$sub_answer_id = $sub_answers[$i]['id'];
							}
							
							if (!is_null($sub_answer_id)) {
								// UPDATE SUB ANSWER
								$sql = "UPDATE new_answers
										SET 
											count = IFNULL(count,0) + 1,
											datetime = NOW()
										WHERE new_answers.id = :id";
								$res = $this->db->query(Database::UPDATE, $sql);
								$res->bind(':id', $sub_answer_id);
								$db_data = $res->execute();	
							} else {
								// ADD SUBANSWER
								$sql = "INSERT INTO new_answers (
											new_id,
											parent_id,
											answer_value,
											type_id,
											image_src,
											order_index,
											count,
											user_id,
											datetime,
											creation_user_id,
											creation_datetime )
										VALUES ( 
											null,
											:parent_id,
											:answer_value,
											null, 
											null,
											null,
											1,
											null, 
											NOW(),
											null,
											NOW() ) ";
								$res = $this->db->query(Database::INSERT, $sql);
								$res->bind(':parent_id', $answer_id);
								$res->bind(':answer_value', $answer_value);
								$db_data = $res->execute();	
							}
							
							// UPDATE ANSWERS
							$sql = "SELECT IFNULL(SUM(new_answers.count),0) AS sum
									FROM new_answers
									WHERE new_answers.parent_id = :parent_id ";
							$res = $this->db->query(Database::SELECT, $sql);
							$res->bind(':parent_id', $answer_id);
							$db_data = $res->execute();									
							$sum = count($db_data > 0)?$db_data[0]['sum']:0;
							
							$sql = "UPDATE new_answers
									SET count = :sum
									WHERE new_answers.id = :answer_id ";
							$res = $this->db->query(Database::UPDATE, $sql);
							$res->bind(':answer_id', $answer_id);
							$res->bind(':sum', $sum);
							$db_data = $res->execute();
						} else {
							// SIMPLE ANSWER
							$sql = "UPDATE new_answers
									SET count = IFNULL(count,0) + 1
									WHERE new_answers.id = :id";
							$res = $this->db->query(Database::UPDATE, $sql);
							$res->bind(':id', $answer_id);
							$db_data = $res->execute();
						}
						
						// SET COOKIE
						$cookie = $question_id;
						Cookie::set('new_question_'.$question_id, $cookie);						
					}
				}
			}			
		}

		// RETURN
		if (count($ret_data['error']) > 0) {
			$ret_data['error'] = implode('<br/>', $ret_data['error']);
		} else {
			$ret_data['status'] = 1;
			
			$question_data = $this->news->getNews($question_id, $this->lang_id);
			$question_data = $this->news->getNewsAnswers($question_data);
			$tpl_data['data'] = $question_data[0];
			$tpl_data['answered'] = $cookie;
			
			$tpl_data['action'] = 'view_item';
			$ret_data['response'] = $this->tpl->factory('plugins/news/news', $tpl_data)->render();
		}
		
		return $ret_data;
	}	

	public function replace_latvian($title) {
		$title = str_replace('Ā','A',$title);
		$title = str_replace('ā','a',$title);
		$title = str_replace('č','c',$title);
		$title = str_replace('Č','C',$title);
		$title = str_replace('ē','e',$title);
		$title = str_replace('Ē','E',$title);
		$title = str_replace('ģ','g',$title);
		$title = str_replace('Ģ','G',$title);
		$title = str_replace('ī','i',$title);
		$title = str_replace('Ī','I',$title);
		$title = str_replace('ķ','k',$title);
		$title = str_replace('Ķ','K',$title);
		$title = str_replace('ļ','l',$title);
		$title = str_replace('Ļ','L',$title);
		$title = str_replace('ņ','n',$title);
		$title = str_replace('Ņ','N',$title);
		$title = str_replace('ō','o',$title);
		$title = str_replace('Ō','O',$title);
		$title = str_replace('š','s',$title);
		$title = str_replace('Š','S',$title);
		$title = str_replace('ū','u',$title);
		$title = str_replace('Ū','U',$title);
		$title = str_replace('Ž','Z',$title);	
		$title = str_replace('ž','z',$title);	
		
		return $title;
	} 
}