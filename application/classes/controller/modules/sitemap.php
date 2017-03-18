
		
		//
		// PRODUCTS
		//
		$this->products = Model::factory('manager_products_products');
		$products = $this->products->getProducts(null, null, array('active' => true));
		$product_page = $this->resources->getDocuments(CMS::$products_page_id);
		
		foreach($products as $key => $link) {
			foreach($link['lang'] as $key => $lang) {
				if (isset($product_page[0]['lang'][$key]['full_alias'])) {
					$links[] = array(
						'link' => $this->base_url.$product_page[0]['lang'][$key]['full_page_alias'].'/'.$lang['category_parent_alias'].'-'.$lang['category_alias'].'-c'.$link['category_id'].'/'.$lang['alias'].'-i'.$link['id'],
						'last_change' => $link['datetime'],
						'change_frequency' => 'monthly',
						'priority' => '0.5' );
				}
			}
		}
		