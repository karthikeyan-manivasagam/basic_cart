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

  private static function get_storage() {
     $user = \Drupal::currentUser();
     $config = self::cart_settings();
     $storage = new CartStorageSelect($user, $config->get('use_cart_table'));
     return $storage;
  }


  public static function is_basic_cart_order($bundle) {
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
public static function get_cart($nid = NULL) {
   $storage = static::get_storage();
   return $storage->get_cart($nid);
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
  public static function remove_from_cart($nid = NULL) {
    $nid = (int) $nid;
    $storage = static::get_storage();
    $storage->remove_from_cart($nid);
  }

/**
 * Shopping cart reset.
 */
  public static function empty_cart() {
    $storage = static::get_storage();
    $storage->empty_cart();
  }

  public static function add_to_cart($id, $params = array()) {
  $storage = static::get_storage();
  $storage->add_to_cart($id, $params);  
  }
   
  public function loggedinactioncart() {
	  $storage = static::get_storage();
    return $storage->loggedinactioncart();   
  }


   /**
   * Returns the fields we need to create.
   * 
   * @return mixed
   *   Key / Value pair of field name => field type. 
   */
  public static function  get_fields_config($type = null) {

    $config = self::cart_settings();
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

  public static function create_fields($type = null) {
    $fields = ($type == self::FIELD_ORDERCONNECT) ? self::get_fields_config(self::FIELD_ORDERCONNECT) : self::get_fields_config();
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



  public static function order_connect_fields() {
    self::create_fields(self::FIELD_ORDERCONNECT);
  } 
}

 
