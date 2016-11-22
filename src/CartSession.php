<?php

namespace Drupal\basic_cart;

use Drupal\basic_cart\CartInterface;
use Drupal\basic_cart\Settings;
/**
 * Class CartSession.
 */
class CartSession implements CartInterface {	

  protected $user;
  protected $user_id;

	public function __construct($user) {
		$this->user = $user;
		$this->user_id = $user->id();
	}


	/**
	 * Function for shopping cart retrieval.
	 *
	 * @param int $nid
	 *   We are using the node id to store the node in the shopping cart
	 *
	 * @return mixed
	 *   Returning the shopping cart contents.
	 *   An empty array if there is nothing in the cart
	 */


	public function getCart($nid = NULL) {
	//print_r($nid); die;
	  if (isset($nid)) {
	    return array("cart" => $_SESSION['basic_cart']['cart'][$nid], "cart_quantity" => $_SESSION['basic_cart']['cart_quantity'][$nid]);
	  }
	  if (isset($_SESSION['basic_cart']['cart'])) {
	    return array("cart" => $_SESSION['basic_cart']['cart'], "cart_quantity" => $_SESSION['basic_cart']['cart_quantity']);
	  }
	  // Empty cart.
	  return array("cart" => array(),"cart_quantity" => array());
	}


	/**
	 * Callback function for cart/remove/.
	 *
	 * @param int $nid
	 *   We are using the node id to remove the node in the shopping cart
	 */
	public function removeFromCart($nid) {
	  $nid = (int) $nid;
	  if ($nid > 0) {
	    unset($_SESSION['basic_cart']['cart'][$nid]);
	    unset($_SESSION['basic_cart']['cart_quantity'][$nid]);
	  }
	}

	/**
	 * Shopping cart reset.
	 */

	public function emptyCart() {
	  unset($_SESSION['basic_cart']['cart']);
	  unset($_SESSION['basic_cart']['cart_quantity']);
	}


  public  function addToCart($id, $params = array()) {
    $config = Settings::cartSettings();
    if(!empty($params)) {
      $quantity = $params['quantity'];
      $entitytype = $params['entitytype'];
      $quantity = $params['quantity'];

      if ($id > 0 && $quantity > 0) {
            // If a node is added more times, just update the quantity.
            $cart = self::getCart();
            if ($config->get('quantity_status') && !empty($cart['cart']) && in_array($id, array_keys($cart['cart']))) {
              // Clicked 2 times on add to cart button. Increment quantity.
              $_SESSION['basic_cart']['cart_quantity'][$id] += $quantity;
            }
            else {
               $entity = \Drupal::entityTypeManager()->getStorage($entitytype)->load($id);
               $_SESSION['basic_cart']['cart'][$id] = $entity;
               $_SESSION['basic_cart']['cart_quantity'][$id] = $quantity;
            }
      }
      Settings::cartUpdatedMessage();
    }  
  }

  public function loggedInActionCart() {
    return TRUE;
	}
}	
