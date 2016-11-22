<?php
/**
 * @file
 * Contains \Drupal\basic_cart\Utility
 */
namespace Drupal\basic_cart;

use Drupal\basic_cart\CartSession;
use Drupal\basic_cart\CartTable;
use Drupal\basic_cart\CartStorage;

class CartStorageSelect {

    private $cart = NULL; 
    private $cart_storage;

    public function __construct($user, $use_table = NULL) {
        $enable = $user->id() && $use_table ? $user->id() : 0 ; 
        switch ($enable) {
            case 0: 
                $this->cart = new CartSession($user);
            break;
            default:    
								$cart_storage = new CartStorage();
                $this->cart   = new CartTable($cart_storage, $user);
            break;
        }
    }

    public  function get_cart($nid = NULL) {
        return $this->cart->get_cart($nid);
    }

    public  function remove_from_cart($nid) {
        return $this->cart->remove_from_cart($nid);
    }
    public  function empty_cart() {
        return $this->cart->empty_cart();
    }
    public  function add_to_cart($id, $params = array()) {
        return $this->cart->add_to_cart($id, $params);
    }

    public function loggedinactioncart() {
     return $this->cart->loggedinactioncart();
    }
}
