<?php

use Drupal\basic_cart\Utility;

function basic_cart_get_cart() {
  $cart =  Utility::get_cart();
  return $cart;
}

function basic_cart_empty_cart(){
  $cart =  Utility::empty_cart();
  return $cart;
}