<?php

/**
 * @file
 * Contains \Drupal\basic_cart\CartInterface.
 */

namespace Drupal\basic_cart;

/**
 * Cart interface definition for basic_cart plugins.
 *
 */
interface CartInterface {
	public function get_cart($nid = NULL);
	public function remove_from_cart($nid);
	public function empty_cart();
	public function add_to_cart($id, $params = array());
}	