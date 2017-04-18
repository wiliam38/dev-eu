
		//
		// NEWS
		//
		$this->news = Model::factory('manager_news_news');
		$news = $this->news->getNews(null, null, array('active' => true));
		$news_page = $this->resources->getDocuments(CMS::$news_page_id);
		
		foreach($news as $key => $link) {
			foreach($link['lang'] as $key => $lang) {
				if (isset($news_page[0]['lang'][$key]['full_alias'])) {
					$links[] = array(
						'link' => $this->base_url.$news_page[0]['lang'][$key]['full_page_alias'].'/'.$lang['alias'].'-i'.$link['id'],
						'last_change' => $link['datetime'],
						'change_frequency' => 'monthly',
						'priority' => '0.5' );
				}
			}
		}
