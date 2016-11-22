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
	public function getCart($nid = NULL);
	public function removeFromCart($nid);
	public function emptyCart();
	public function addToCart($id, $params = array());
}	