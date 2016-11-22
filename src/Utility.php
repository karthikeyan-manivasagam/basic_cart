<?php
/**
 * @file
 * Contains \Drupal\basic_cart\Utility
 */
namespace Drupal\basic_cart;


use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\basic_cart\Settings;
use Drupal\basic_cart\CartStorageSelect;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

class Utility extends Settings {

  private $storage;

  const FIELD_ADDTOCART    = 'addtocart';
  const FIELD_ORDERCONNECT = 'orderconnect';
  const BASICCART_ORDER    = 'basic_cart_order';

  private static function getStorage() {
     $user = \Drupal::currentUser();
     $config = self::cartSettings();
     $storage = new CartStorageSelect($user, $config->get('use_cart_table'));
     return $storage;
  }


  public static function isBasicCartOrder($bundle) {
    if($bundle == self::BASICCART_ORDER) {
      return TRUE;
    }
    return FALSE;
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
public static function getCart($nid = NULL) {
   $storage = static::getStorage();
   return $storage->getCart($nid);
}

/**
 * Returns the final price for the shopping cart.
 *
 * @return mixed $total_price
 *   The total price for the shopping cart. 
 */

  /**
   * Callback function for cart/remove/.
   *
   * @param int $nid
   *   We are using the node id to remove the node in the shopping cart
   */
  public static function removeFromCart($nid = NULL) {
    $nid = (int) $nid;
    $storage = static::getStorage();
    $storage->removeFromCart($nid);
  }

/**
 * Shopping cart reset.
 */
  public static function emptyCart() {
    $storage = static::getStorage();
    $storage->emptyCart();
  }

  public static function addToCart($id, $params = array()) {
  $storage = static::getStorage();
  $storage->addToCart($id, $params);  
  }
   
  public function loggedInActionCart() {
	  $storage = static::getStorage();
    return $storage->loggedInActionCart();   
  }


   /**
   * Returns the fields we need to create.
   * 
   * @return mixed
   *   Key / Value pair of field name => field type. 
   */
  public static function  getFieldsConfig($type = null) {

    $config = self::cartSettings();
    $fields['bundle_types'] = $config->get('content_type');
      foreach ($config->get('content_type') as $key => $value) {
        if($value){
          $bundles[$key] = $key;
        }
      }
    $fields['bundle_types'] = $bundles;
    if($type == self::FIELD_ORDERCONNECT) {

     $fields['bundle_types'] = array('basic_cart_connect' =>  'basic_cart_connect'); 
     $fields['fields'] =  array(
                      'basic_cart_contentoconnect' => array(
                        'type' => 'entity_reference',
                        'entity_type' => 'node',
                        'bundle' => 'basic_cart_connect',
                        'title' => t('Basic Cart Content Connect'),
                        'label' => t('Basic Cart Content Connect'),
                        'required' => FALSE,
                        'description' => t('Basic Cart content connect'),
                        'settings' => array('handler' => 'default:node',
                                            'handler_settings'=> array(
                                                  "target_bundles" =>  $bundles,
                                              ) 
                                            )

                          ),);
    }
    else {
     $fields['fields'] =  array(
                      'add_to_cart_price' => array(
                        'type' => 'decimal',
                        'entity_type' => 'node',
                        'title' => t($config->get('price_label')),
                        'label' => t($config->get('price_label')),
                        'required' => FALSE,
                        'description' => t('Please enter this item\'s price.'),
                        'widget' => array('type' => 'number'),
                        'formatter' => array('default'=> array(
                                'label' => 'inline',
                                'type' => 'number_decimal',
                                'weight' => 11,
                              ), 'search_result' =>  'default', 'teaser' => 'default') 
                      ),
                      'add_to_cart' => array(
                        'type' => 'addtocart',
                        'entity_type' => 'node',
                        'title' => t($config->get('add_to_cart_button')),
                        'label' => t($config->get('add_to_cart_button')),
                        'required' => FALSE,
                        'description' => 'Enable add to cart button',
                        'widget' => array('type' => 'addtocart'),
                        'formatter' => array('default'=> array(
                                'label' => 'hidden',
                                'weight' => 11,
                                'type' => $config->get('quantity_status') ? 'addtocartwithquantity' : 'addtocart',
                              ), 'search_result' =>  array(
                                'label' => 'hidden',
                                'weight' => 11,
                                'type' => 'addtocart',
                              ), 'teaser' => array(
                                'label' => 'hidden',
                                'weight' => 11,
                                'type' => 'addtocart',
                              ),) 

                      ), 
                      );
                 
    }
    return (object) $fields;
  }

  public static function createFields($type = null) {
    $fields = ($type == self::FIELD_ORDERCONNECT) ? self::getFieldsConfig(self::FIELD_ORDERCONNECT) : self::getFieldsConfig();
    $view_modes = \Drupal::entityManager()->getViewModes('node');
    foreach($fields->fields as $field_name => $config) {
     $field_storage = FieldStorageConfig::loadByName($config['entity_type'], $field_name);
     if(empty($field_storage)) {
         FieldStorageConfig::create(array(
            'field_name' => $field_name,
            'entity_type' => $config['entity_type'],
            'type' => $config['type'],
          ))->save();
     }
    }
    foreach($fields->bundle_types as  $bundle) {
      foreach ($fields->fields as $field_name => $config) {
        $config_array = array(
                'field_name' =>  $field_name,
                'entity_type' => $config['entity_type'],
                'bundle' => $bundle,
                'label' => $config['label'],
                'required' => $config['required'],
                
              );

        if(isset($config['settings'])) {
          $config_array['settings'] = $config['settings'];
        }
        $field = FieldConfig::loadByName($config['entity_type'], $bundle, $field_name);
        if(empty($field) && $bundle !== "" && !empty($bundle)) {
                FieldConfig::create($config_array)->save();
        }

        if($bundle !== "" && !empty($bundle)) {
          if(!empty($field)) {
             $field->setLabel($config['label'])->save();
             $field->setRequired($config['required'])->save();
          }
           if($config['widget']) {
              entity_get_form_display($config['entity_type'], $bundle, 'default')
              ->setComponent($field_name, $config['widget'])
              ->save(); 
           }
           if($config['formatter']) { 
             foreach ($config['formatter'] as $view => $formatter) {
                if (isset($view_modes[$view]) || $view == "default") { 
                  //$formatter['type'] = ($formatter['type'] == "addcartsearch") ? "addtocart"  : $formatter['type'];
                   entity_get_display($config['entity_type'], $bundle, $view)
                  ->setComponent($field_name, !is_array($formatter) ? $config['formatter']['default'] : $config['formatter']['default'])
                  ->save();
                }  
             } 
          } 
        } 
      }
    }
  } 



  public static function orderConnectFields() {
    self::createFields(self::FIELD_ORDERCONNECT);
  }

     public static function getCartContent() {
    //$Utility  = $this;
    $config = self::cartSettings();
    $cart = self::getCart();
    $quantity_enabled = $config->get('quantity_status');
    $total_price = self::getTotalPrice();
    $cart_cart = isset($cart['cart']) ? $cart['cart'] : array();
    $output = '';
 if (empty($cart_cart)){
  $output .= '<div class="basic_cart-grid basic-cart-block">'.t($config->get('empty_cart')).'</div>';
  } 
else {

  $output .= '<div class="basic_cart-grid basic-cart-block">';
  if(is_array($cart_cart) && count($cart_cart) >= 1){
    foreach($cart_cart as $nid => $node){
    $langcode = $node->language()->getId();
      $price_value = $node->getTranslation($langcode)->get('add_to_cart_price')->getValue();
      $title = $node->getTranslation($langcode)->get('title')->getValue();

      $url = new Url('entity.node.canonical',array("node"=>$nid));

      $link = new Link($title[0]['value'],$url);
         $output .= '<div class="basic_cart-cart-contents row">
          <div class="basic_cart-cart-node-title cell">'.$link->toString().'</div>';
         if($quantity_enabled) {
          $output .= '<div class="basic_cart-cart-quantity cell">'.$cart['cart_quantity'][$nid].'</div>';
          $output .= '<div class="basic_cart-cart-x cell">x</div>';
         }
         
         $output .='<div class="basic_cart-cart-unit-price cell">';
       $output .= isset($price_value[0]) ? '<strong>'.self::formatPrice($price_value[0]['value']).'</strong>' : '';
       $output .='</div>
        </div>';
    }

       $output .=  '<div class="basic_cart-cart-total-price-contents row">
        <div class="basic_cart-total-price cell">
            '.t($config->get('total_price_label')).':<strong>'.self::formatPrice($total_price->total).'</strong>
        </div>
      </div>';
        if (!empty ($config->get('vat_state'))) {
       $output .='<div class="basic_cart-block-total-vat-contents row">
          <div class="basic_cart-total-vat cell">'.t('Total VAT').': <strong>'.self::formatPrice($total_price->vat).'</strong></div>
        </div>';
        }
      $url = new Url('basic_cart.cart');
      //$link = new Link($this->t($config->get('view_cart_button')),$url);
      $link = "<a href='".$url->toString()."' class='button'>".t($config->get('view_cart_button'))."</a>";
        $output .='<div class="basic_cart-cart-checkout-button basic_cart-cart-checkout-button-block row">
        '.$link.'
      </div>';
  }
  $output .= '</div>';
}


  return $output;
}
}

 
