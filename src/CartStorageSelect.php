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

    public  function getCart($nid = NULL) {
        return $this->cart->getCart($nid);
    }

    public  function removeFromCart($nid) {
        return $this->cart->removeFromCart($nid);
    }
    public  function emptyCart() {
        return $this->cart->emptyCart();
    }
    public  function addToCart($id, $params = array()) {
        return $this->cart->addToCart($id, $params);
    }

    public function loggedInActionCart() {
     return $this->cart->loggedInActionCart();
    }
}
