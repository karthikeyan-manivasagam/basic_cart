<?php

use Drupal\basic_cart\Utility;

function basic_cart_get_cart() {
  $cart =  Utility::getCart();
  return $cart;
}

function basic_cart_empty_cart(){
  $cart =  Utility::emptyCart();
  return $cart;
}