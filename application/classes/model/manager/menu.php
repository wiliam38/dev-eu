<?php defined('SYSPATH') or die('No direct script access.');
 
class Model_Manager_Menu extends Model {	
	
	//
	// GET MENU DATA
	//
	public function getMenuData($parent_id = 0) {
		
		$sql = "SELECT
					menu_links.id					AS id,
					menu_links.name					AS name,
					menu_links.link					AS link,
					menu_links.target				AS target,
					sub_menu_links.id				AS sub_id,
					sub_menu_links.name				AS sub_name,
					sub_menu_links.link				AS sub_link,
					sub_menu_links.target			AS sub_target
				FROM
					menu_links
					LEFT JOIN menu_links sub_menu_links ON
						menu_links.id = sub_menu_links.parent_id AND
						sub_menu_links.role_id IN ( SELECT user_roles.role_id
													FROM user_roles
													WHERE user_roles.status_id >= 10 AND user_roles.user_id = :user_id ) AND
						sub_menu_links.type_id = 1
				WHERE
					menu_links.type_id = 1 AND
					menu_links.role_id IN ( SELECT user_roles.role_id
											FROM user_roles
											WHERE user_roles.status_id >= 10 AND user_roles.user_id = :user_id ) AND
					IFNULL(menu_links.parent_id,0) = :parent_id
				ORDER BY
					menu_links.order_index,
					sub_menu_links.order_index ";			
		$result = $this->db->query(Database::SELECT, $sql);
		$result->bind(':parent_id', $parent_id);
		$result->bind(':user_id', $this->user_id);
		
		$sub_data = $result->execute()->as_array();

		$data = array();
		$i = -1;
		for ($j=0; $j<count($sub_data); $j++) {			
			if (!isset($data[$i]['id']) OR $data[$i]['id'] != $sub_data[$j]['id']) {
				$i++;
				$data[$i] = array( 	'id' => $sub_data[$j]['id'],
									'name' => $sub_data[$j]['name'],
									'link' => $sub_data[$j]['link'],
									'target' => $sub_data[$j]['target'] );
				$data[$i]['sub_menu'] = array();
			}
			if ($sub_data[$j]['sub_id'] != '') {
				$data[$i]['sub_menu'][] = array( 	'id' => $sub_data[$j]['sub_id'],
													'name' => $sub_data[$j]['sub_name'],
													'link' => $sub_data[$j]['sub_link'],
													'target' => $sub_data[$j]['sub_target'] );
			}
		}
		
		return $data;
	}
 
}