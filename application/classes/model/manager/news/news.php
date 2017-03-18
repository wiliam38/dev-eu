<?php defined('SYSPATH') or die('No direct script access.');

class Model_Manager_News_News extends Model {
	public function __construct() {
		parent::__construct();
		
		$this->files = Model::factory('manager_files');
	}
	
	public function getNews($id = null, $lang_id = null, $filter_data = array(),  $limit = null, $offset = null, $count_all = false) {
		// PARAMS
		if (!is_null($lang_id) AND !is_numeric($lang_id)) $lang_id = '-1';
		
		// SELECT
		$res = $this->db->select(); 
		
		// FROM
		$res->from('news'); 
		$res->join('new_contents', 'LEFT')
			->on('news.id', '=', 'new_contents.new_id');
			if (!is_null($lang_id)) $res->on('new_contents.language_id', '=', DB::expr($lang_id)); 
		           
		// WHERE
		if (!is_null($id)) $res->where('news.id', '=', $id);
		if (isset($filter_data['from_status_id'])) $res->where('new_contents.status_id', '>=', $filter_data['from_status_id']);
		if (isset($filter_data['type_id'])) $res->where('news.type_id', 'IN', is_array($filter_data['type_id'])?$filter_data['type_id']:array($filter_data['type_id']));
		if (isset($filter_data['not_type_id'])) $res->where('news.type_id', 'NOT IN', is_array($filter_data['not_type_id'])?$filter_data['not_type_id']:array($filter_data['not_type_id']));
		if (isset($filter_data['page_alias'])) $res->where('new_contents.alias', '=', $filter_data['page_alias']);
            
		// ORDER BY
		$res->order_by('IFNULL("news.main",0)', 'DESC');
		$res->order_by('new_contents.pub_date', 'DESC');
		$res->order_by('news.id', 'DESC');
		
		// ONLY FOR COUNT ROWS
		if ($count_all) {
			$res->select(array('COUNT("news.id")', 'cnt'));
			$res->group_by('news.id');
			$data = $db_data = $res->execute()->as_array();
                  
			return count($data);
		}
		
		// LIMIT
		if(!is_null($limit)) {
			$tmp_res = $res;
			// SELECT
			$tmp_res->select(array('news.id', 'id'));
                                                
			// LIMIT
			$tmp_res->limit($limit);
			if (!is_null($offset) && $offset > 0) $tmp_res->offset($offset);
			
			// GROUP BY			
			//$tmp_res->group_by('news.id');
                  
			// DATA
			$db_data = $tmp_res->execute()->as_array();         
                                    
			$id_list = array();
			foreach($db_data as $key => $val) $id_list[] = $val['id'];
			$res->where('news.id', 'IN', !empty($id_list)?$id_list:array(-1));      
			$res->limit(null);
			$res->offset(null);                   
		} 
		
		// SELECT
		$res->select(
			array('news.id', 'id'),
			array('news.admin_title', 'admin_title'),
			array('news.main', 'main'),
			array('news.image_src', 'image_src'),
			array('news.main_image_id', 'main_image_id'),
			array('new_images.image_src', 'main_image_src'),
			array('news.type_id', 'type_id'),
			array('types.name', 'type_name'),
			array('types.description', 'type_description'),
			array(	$this->db->select('MIN("new_contents.pub_date")')
						->from('new_contents')
						->where('new_contents.new_id', '=', 'news.id'), 'date'),
			
			array('new_contents.id', 'l_id'),
			array('new_contents.language_id', 'l_language_id'),
			array('new_contents.status_id', 'l_status_id'),
			array('status.name', 'l_status_name'),
			array('status.description', 'l_status_description'),
			array('new_contents.title', 'l_title'),
			array('new_contents.pub_date', 'l_pub_date'),
			array('new_contents.unpub_date', 'l_unpub_date'),
			array('new_contents.vote_from', 'l_vote_from'),
			array('new_contents.vote_to', 'l_vote_to'),
			array('IF (IFNULL("new_contents.vote_from",NOW()) <= NOW() AND IFNULL("new_contents.vote_to", NOW()) >= NOW(), 1, 0)', 'l_vote_allowed'),
			array('new_contents.intro', 'l_intro'),
			array('new_contents.content', 'l_content'),
			array('new_contents.link', 'l_link'),
			array('new_contents.alias', 'l_alias'),
			array('CONCAT("new_contents.alias",\'-i\',"news.id")', 'l_full_alias') );
		
		// FROM
		$res->join('status', 'LEFT')
			->on('new_contents.status_id', '=', 'status.status_id')
			->on('status.table_status_name', '=', DB::expr("'new_contents_status_id'"));
		$res->join('types', 'LEFT')
			->on('news.type_id', '=', 'types.type_id')
			->on('types.table_type_name', '=', DB::expr("'news_type_id'"));
		$res->join('new_images', 'LEFT')
			->on('new_images.new_id', '=', 'news.id')
			->on('new_images.id', '=', 'news.main_image_id');
			
		// ORDER BY
		$res->order_by('new_contents.pub_date', 'DESC');
		
		// DATA
		$db_data = $res->execute()->as_array();		
		
		// LANGS
		if (is_null($lang_id)) $db_data = CMS::langArray($db_data);	
		
		return $db_data;
	}

	public function getNewsAnswers($news_data) {
		for ($i=0; $i<count($news_data); $i++) {
			if ($news_data[$i]['type_id'] == 30) {
				$news_data[$i]['answers'] = $this->getAnswers(null, $news_data[$i]['id'], $this->lang_id);
			} 
		}
		
		return $news_data;
	}

	public function getAnswers($id, $new_id = null, $lang_id = null, $filter_data = array()) {
		// PARAMS
		if (!is_null($lang_id) AND !is_numeric($lang_id)) $lang_id = '-1';
		
		// SELECT
		$res = $this->db->select(
			array('new_answers.id', 'id'),
			array('new_answers.new_id', 'new_id'),
			array('new_answers.parent_id', 'parent_id'),
			array('new_answers.answer_value', 'answer_value'),
			array('new_answers.type_id', 'type_id'),
			array('new_answers.image_src', 'image_src'),
			array('new_answers.order_index', 'order_index'),
			array('new_answers.count', 'count'),
			array('IF(SUM(IFNULL("all_answers.count",0)) = 0,
				0,
				"new_answers.count" / SUM(IFNULL("all_answers.count",0)) * 100 )', 'count_percents'),								
			
			array('new_answer_contents.id', 'l_id'),
			array('new_answer_contents.language_id', 'l_language_id'),
			array('new_answer_contents.answer', 'l_answer') );
			
		// FROM
		$res->from('new_answers');
		$res->join('new_answer_contents', 'LEFT')
			->on('new_answers.id', '=', 'new_answer_contents.new_answer_id');
			if (!is_null($lang_id)) $res->on('new_answer_contents.language_id', '=', DB::expr($lang_id)); 
		$res->join(array('new_answers', 'all_answers'))->on('new_answers.new_id', '=', 'all_answers.new_id');
		
		// WHERE
		if (!is_null($id)) $res->where('new_answers.id', '=', $id);	
		else $res->where('new_answers.new_id', '=', $new_id);
			
		if (isset($filter_data['parent_id'])) $res->where('new_answers.parent_id', '=', $filter_data['parent_id']);
		
		// GROUP BY
		$res->group_by(
			'new_answers.id',
			'new_answers.new_id',
			'new_answers.parent_id',
			'new_answers.answer_value',
			'new_answers.type_id',
			'new_answers.image_src',
			'new_answers.order_index',
			'new_answers.count',
			'new_answer_contents.id',
			'new_answer_contents.language_id',
			'new_answer_contents.answer'
		);
		
		// ORDER BY
		$res->order_by('new_answers.order_index', 'ASC');
		
		// DATA
		$db_data = $res->execute()->as_array();
		
		// LANGS
		if (is_null($lang_id)) $db_data = CMS::langArray($db_data);
		
		return $db_data;
	}

	public function getNewImages($id = null, $new_id = null, $lang_id = null) {
		 if (!is_null($lang_id) AND !is_numeric($lang_id)) $lang_id = '-1';
		 
		$res = $this->db->select(
			array('new_images.id', 'id'),
			array('new_images.image_src', 'image_src'),
			
			array('new_image_contents.id', 'l_id'),
			array('new_image_contents.language_id', 'l_language_id'),
			array('new_image_contents.title', 'l_title'),
			array('new_image_contents.description', 'l_description') );
			
		$res->from('new_images');
		$res->join('new_image_contents', 'LEFT')
			->on('new_images.id', '=', 'new_image_contents.new_image_id');
			if (!is_null($lang_id)) $res->on('new_image_contents.language_id', '=', DB::expr($lang_id)); 
		
		if (!is_null($id)) $res->where('new_images.id', '=', $id);
		else $res->where('new_images.new_id', '=', $new_id);
		
		$res->order_by('new_images.order_index', 'ASC');
		
		$db_data = $res->execute()->as_array();
		
		// LANGS
		if (is_null($lang_id)) $db_data = CMS::langArray($db_data);	
		
		return $db_data;
	}
		
	public function save($data) {
		$this->resources = Model::factory('manager_resources');
		
		if ($data['new_id'] == 'new') {
			// INSERT
			$db_data = $this->db->insert('news', array(
					'admin_title',
					'main',
					'type_id',
					'user_id',
					'datetime',
					'creation_user_id',
					'creation_datetime' ))
				->values(array(
					$data['admin_title'],
					isset($data['main'])?1:0,
					$data['type_id'],
					$this->user_id,
					DB::expr('NOW()'),
					$this->user_id,
					DB::expr('NOW()') ))
				->execute();
				
			$new_id = $db_data[0];	
		} else {
			$db_data = $this->db->update('news')
				->set(array(
					'admin_title' => $data['admin_title'],
					'main' => isset($data['main'])?1:0,
					'type_id' => $data['type_id'],
					'user_id' => $this->user_id,
					'datetime' => DB::expr('NOW()') ))
				->where('news.id', '=', $data['new_id'])
				->execute();
				
			$new_id = $data['new_id'];
		}
		
		// REMOVE MAIN FROM OTHERS
		if (isset($data['main'])) {
			$this->db->update('news')
				->set(array('main' => '0'))
				->where('news.id', '!=', $new_id)
				->execute();
		} 
		
		// IMAGE
		$new = $this->getNews($new_id);
		$image_src = $this->files->update_image2('files/news/'.$new_id.'/', $data['image_src'], $new[0]['image_src']);
		
		$db_data = $this->db->update('news')
			->set(array('image_src' => $image_src))
			->where('news.id', '=', $new_id)
			->execute();
		
		//
		// LOOP PRODUCT CONTENTS
		//
		
		// UPDATE LANGUAGES		
		if (empty($data['language_id'])) $data['language_id'] = array();
		$needed_new_content_id = array();
		for ($i=0; $i<count($data['language_id']); $i++) {
			if (isset($data[$data['language_id'][$i].'_new_content_id']) AND $data[$data['language_id'][$i].'_new_content_id'] != 'none') {
				$pub_date = !empty($data[$data['language_id'][$i].'_pub_date'])?date('Y-m-d H:i:s',strtotime($data[$data['language_id'][$i].'_pub_date'])):date('Y-m-d H:i:s',time());
				$unpub_date = !empty($data[$data['language_id'][$i].'_unpub_date'])?date('Y-m-d H:i:s',strtotime($data[$data['language_id'][$i].'_unpub_date'])):null;
				
				$vote_from = !empty($data[$data['language_id'][$i].'_vote_from'])?date('Y-m-d H:i:s',strtotime($data[$data['language_id'][$i].'_vote_from'])):null;
				$vote_to = !empty($data[$data['language_id'][$i].'_vote_to'])?date('Y-m-d H:i:s',strtotime($data[$data['language_id'][$i].'_vote_to'])):null;
								
				if (empty($data[$data['language_id'][$i].'_new_content_id']) OR $data[$data['language_id'][$i].'_new_content_id'] == 'new') {
					// INSERT
					$db_data = $this->db->insert('new_contents', array(
							'new_id',
							'language_id',
							'title',
							'pub_date',
							'unpub_date',
							'vote_from',
							'vote_to',
							'intro',
							'content',
							'link',
							'alias',
							'status_id',
							'user_id',
							'datetime',
							'creation_user_id',
							'creation_datetime'))
						->values(array(
							$new_id,
							$data['language_id'][$i],
							$data[$data['language_id'][$i].'_title'],
							$pub_date,
							$unpub_date,
							$vote_from,
							$vote_to,
							$data[$data['language_id'][$i].'_intro'],
							$data[$data['language_id'][$i].'_content'],
							$data[$data['language_id'][$i].'_link'],
							$this->resources->title_to_alias($data[$data['language_id'][$i].'_title']),
							$data[$data['language_id'][$i].'_status_id'],
							$this->user_id,
							DB::expr('NOW()'),
							$this->user_id,
							DB::expr('NOW()') ))
						->execute();
					$new_content_id = $db_data[0];	
				} else {
					// UPDATE
					$db_data = $this->db->update('new_contents')
						->set(array(
							'title' => $data[$data['language_id'][$i].'_title'],
							'pub_date' => $pub_date,
							'unpub_date' => $unpub_date,
							'vote_from' => $vote_from,
							'vote_to' => $vote_to,
							'intro' => $data[$data['language_id'][$i].'_intro'],
							'content' => $data[$data['language_id'][$i].'_content'],
							'link' => $data[$data['language_id'][$i].'_link'],
							'alias' => $this->resources->title_to_alias($data[$data['language_id'][$i].'_title']),
							'status_id' => $data[$data['language_id'][$i].'_status_id'],
							'user_id' => $this->user_id,
							'datetime' => DB::expr('NOW()') ))
						->where('new_contents.id', '=', $data[$data['language_id'][$i].'_new_content_id'])
						->execute();
					$new_content_id = $data[$data['language_id'][$i].'_new_content_id'];	
				}
				$needed_new_content_id[] = $new_content_id;
			}
		}

		// DELETE REMOVED TRANSLATIONS
		if (empty($needed_new_content_id)) $needed_new_content_id[] = 0;
		$db_data = $this->db->delete('new_contents')
			->where('new_contents.new_id', '=', $new_id)
			->where('new_contents.id', 'NOT IN', $needed_new_content_id)
			->execute();

		//
		// ANSWERS
		//
		$needed_answers = array();
		$lang = $data['language_id'];
		
		if (isset($data['answer_id'])) {
			for ($i=0; $i < count($data['answer_id']); $i++) {
				// ANSWER ID
				if ($data['answer_id'][$i] == "new" OR $data['answer_id'][$i] == "") {
					// IMAGES
					$image_src = $this->files->update_image2('files/news/'.$new_id.'/', $data['answer_image_src'][$i], '');							
					
					$db_data = $this->db->insert('new_answers', array(
							'new_id',
							'parent_id',
							'answer_value',
							'type_id',
							'image_src',
							'order_index',
							'count',								
							'user_id',
							'datetime',
							'creation_user_id',
							'creation_datetime' ))
						->values(array(
							$new_id,
							DB::expr('0'),
							DB::expr("''"),
							$data['answer_type_id'][$i],
							$image_src,
							$i,
							DB::expr('0'),
							$this->user_id,
							DB::expr('NOW()'),
							$this->user_id,
							DB::expr('NOW()') ))
						->execute();
					
					$new_answer_id = $db_data[0];
				} else {
					// IMAGES
					$answer_data = $this->getAnswers($data['answer_id'][$i]);
					$image_src = $this->files->update_image2('files/news/'.$new_id.'/', $data['answer_image_src'][$i], $answer_data[0]['image_src']);							
					
					$db_data = $this->db->update('new_answers')
						->set(array(
							'type_id' => $data['answer_type_id'][$i],
							'image_src' => $image_src,
							'order_index' => $i,
							'user_id' => $this->user_id,
							'datetime' => DB::expr('NOW()') ))
						->where('new_answers.id', '=', $data['answer_id'][$i])
						->execute();	
						
					$new_answer_id = $data['answer_id'][$i];
				}
							
				// NEEDED ANSWER
				$needed_answers[] = $new_answer_id;

				for ($q=0; $q<count($lang); $q++) {
					if (isset($data[$lang[$q].'_answer_content_id'][$i])) {
						if (!empty($data[$lang[$q].'_answer_content_id'][$i]) && preg_match('/^[0-9]*$/', $data[$lang[$q].'_answer_content_id'][$i]) == 1) {
							// UPDATE
							$db_data = $this->db->update('new_answer_contents')
								->set(array('answer' => $data[$lang[$q].'_answer'][$i]))
								->where('new_answer_contents.id', '=', $data[$lang[$q].'_answer_content_id'][$i])
								->execute();						
						} else {
							// INSERT 
							$db_data = $this->db->insert('new_answer_contents', array(
									'new_answer_id',
									'language_id',
									'answer' ))
								->values(array(
									$new_answer_id,
									$lang[$q],
									$data[$lang[$q].'_answer'][$i] ))
								->execute();
						}					
					}
				}
			}
		}

		// DELETE ANSWERS
		if (empty($needed_answers)) $needed_answers[] = 'null';
		
		$db_data = $this->db->select(array('new_answers.id', 'id'))
			->from('new_answers')
			->where('new_answers.id', 'NOT IN', $needed_answers)
			->where('new_answers.new_id', '=', $new_id)
			->execute()
			->as_array();
		
		for ($i=0; $i<count($db_data); $i++) {
			$this->deleteAnswer($db_data[$i]['id']);
		}	
		
		//
		// LOOP GALLERY
		//		
		$needed_images = array();
		
		if (isset($data['new_image_id'])) {
			for ($i=0; $i < count($data['new_image_id']); $i++) {
				// IMAGE ID
				if (!is_numeric($data['new_image_id'][$i])) {
					// IMAGES
					$image_src = $this->files->update_image2('files/news/'.$new_id.'/', $data['new_image_src'][$i], '');							
					
					$db_data = $this->db->insert('new_images', array(
							'new_id',
							'image_src',
							'order_index',
							'user_id',
							'datetime',
							'creation_user_id',
							'creation_datetime'))
						->values(array(
							$new_id,
							$image_src,
							$i,
							$this->user_id,
							DB::expr('NOW()'),
							$this->user_id,
							DB::expr('NOW()')))
						->execute();
						
					$new_image_id = $db_data[0];
				} else {
					$new_image_id = $data['new_image_id'][$i];
				}
				
				// NEEDED IMAGE
				$needed_images[] = $new_image_id;
				
				// UPDATE MAIN IMAGE
				if ($data['new_main_image'][$i] == "1") {
					$db_data = $this->db->update('news')
						->set(array(
							'main_image_id' => $new_image_id))
						->where('news.id', '=', $new_id)
						->execute();
				}
				
				/*
				for ($q=0; $q<count($lang); $q++) {
					if (isset($data[$lang[$q]['id'].'_new_image_content_id'][$i])) {
						if (!empty($data[$lang[$q]['id'].'_new_image_content_id']) && preg_match('/^[0-9]*$/', $data[$lang[$q]['id'].'_new_image_content_id'][$i]) == 1) {
							// UPDATE
							$this->db->update('new_image_contents')
								->set(array(
									'title' => $data[$lang[$q]['id'].'_new_image_content_title'][$i],
									'description' => $data[$lang[$q]['id'].'_new_image_content_description'][$i],
									'user_id' => $this->user_id,
									'datetime' => DB::expr('NOW()')))
								->where('new_image_contents.id', '=', $data[$lang[$q]['id'].'_new_image_content_id'][$i])
								->execute();					
						} else {
							// INSERT 
							$db_data = $this->db->insert('new_image_contents', array(
									'new_image_id',
									'language_id',
									'title',
									'description',
									'user_id',
									'datetime',
									'creation_user_id',
									'creation_datetime'))
								->values(array(
									$new_image_id,
									$lang[$q]['id'],
									$data[$lang[$q]['id'].'_new_image_content_title'][$i],
									$data[$lang[$q]['id'].'_new_image_content_description'][$i],
									$this->user_id,
									DB::expr('NOW()'),
									$this->user_id,
									DB::expr('NOW()')))
								->execute();
						}						
					}
				}
				*/
			}
		}
		
		// DELETE IMAGES
		if (empty($needed_images)) $needed_images[] = 'null';
		$db_data = $this->db->select(array('new_images.id', 'id'))
			->from('new_images')
			->where('new_images.id', 'NOT IN', $needed_images)
			->where('new_images.new_id', '=', $new_id)
			->execute()
			->as_array();	
		
		for ($i=0; $i<count($db_data); $i++) {
			$this->deleteNewImage($db_data[$i]['id']);
		}			

		return $new_id;		
	}

	public function deleteNewImage($new_image_id) {
		// REMOVE IMAGE
		$image_data = $this->getNewImages($new_image_id);
		
		if (count($image_data) > 0) {
			$this->files->deleteFile($image_data[0]['image_src']);
			
			$sql = "DELETE FROM new_image_contents
					WHERE new_image_contents.new_image_id = :new_image_id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':new_image_id', $image_data[0]['id']);
			$db_data = $result->execute();
			
			$sql = "DELETE FROM new_images
					WHERE new_images.id = :new_image_id ";
			$result = $this->db->query(Database::DELETE, $sql);
			$result->bind(':new_image_id', $image_data[0]['id']);
			$db_data = $result->execute();
		}
	}

	public function delete($data) {
		$status = array(	'status' => '0',
							'error' => '',
							'response' => '');
		
		if (!empty($data['new_id'])) {
			$news = $this->getNews($data['new_id']);
			
			// DELETE IMAGE
			$this->files->deleteFile($news[0]['image_src']);
			
			// DELETE CONTENTS
			$db_data = $this->db->delete('new_contents')
				->where('new_contents.new_id', '=', $data['new_id'])
				->execute();
			
			// DELETE NEW
			$db_data = $this->db->delete('news')
				->where('news.id', '=', $data['new_id'])
				->execute();
			
			// DELETE ANSWERS
			$answers = $this->getAnswers(null, $data['new_id']);
			for($i=0; $i<count($answers); $i++) {
				$this->deleteAnswer($answers[$i]['id']);
			}
			
			$status = array(	'status' => '1',
								'error' => '',
								'response' => '');
		} 
		
		return $status;	
	}
	
	public function deleteAnswer($answer_id) {
		if (!empty($answer_id)) {
			// REMOVE IMAGE
			$answer_data = $this->getAnswers($answer_id);
			
			if (count($answer_data) > 0) {
				$this->files->deleteFile($answer_data[0]['image_src']);
				
				$db_data = $this->db->delete('new_answer_contents')
					->where('new_answer_contents.new_answer_id', '=', $answer_id)
					->execute();
				
				$db_data = $this->db->delete('new_answers')
					->where('new_answers.id', '=', $answer_id)
					->or_where('new_answers.parent_id', '=', $answer_id)
					->execute();
			}
		}
	}
}