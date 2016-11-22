<?php

namespace Drupal\basic_cart\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
/**
 * Plugin implementation of the 'addtocartwithquantity' formatter.
 *
 * @FieldFormatter(
 *   id = "addtocartwithquantity",
 *   module = "basic_cart",
 *   label = @Translation("Add to cart with quantity"),
 *   field_types = {
 *     "addtocart"
 *   }
 * )
 */
class AddtoCartWithQuantityFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $config = \Drupal::config('basic_cart.settings');
   // $id			= $config->get('quantity_status') ? '\Drupal\basic_cart\Form\AddToCartWithQuantity' : '\Drupal\basic_cart\Form\AddToCart';
   // $id     = '\Drupal\basic_cart\Form\AddToCartWithQuantity';
    $entity = $items->getEntity();
    $config = \Drupal::config('basic_cart.settings');
    $elements = array();

     $option = [
    'query' => ['entitytype' => $entity->getEntityTypeId(),'quantity' => ''],
    'absolute' => TRUE
    ];

    if(trim($config->get('add_to_cart_redirect')) != "<none>" && trim($config->get('add_to_cart_redirect')) != "") {
        $url = Url::fromRoute('basic_cart.cartadddirect',["nid"=>$entity->id()],$option);
        $link = '<a id="forquantitydynamictext_'.$entity->id().'" class="basic_cart-get-quantity button" href="'.$url->toString().'">'.$this->t($config->get('add_to_cart_button')).'</a>';
      }else {
        $url = Url::fromRoute('basic_cart.cartadd',["nid"=>$entity->id()],$option);
        $link = '<a id="forquantitydynamictext_'.$entity->id().'" class="basic_cart-get-quantity button use-basic_cart-ajax" href="'.$url->toString().'">'.$this->t($config->get('add_to_cart_button')).'</a>';
      }
   
  $link_options = [
    'attributes' => [
      'class' => [
        'basic_cart-get-quantity',
        'use-basic_cart-ajax',
        'button',
      ],
    ],
  ];
  $url->setOptions($link_options);

$quantity_content = $config->get('quantity_status') ? '<div id="quantity-wrapper_'.$entity->id().'" class="addtocart-quantity-wrapper-container"></div>' : '';
//$link = new Link($this->t($config->get('add_to_cart_button')),$url);
    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#type' => 'container',
      '#attributes' => ['class' => 'ajax-addtocart-wrapper' ,'id' => 'ajax-addtocart-message-'.$entity->id()],
      '#prefix' =>'<div class="addtocart-wrapper-container">'.$quantity_content.'<div class="addtocart-link-class">'.$link."</div>",
      '#suffix' =>'</div>',
      ];
    }
   
     $elements['#attached']['library'][] = 'core/drupal.ajax';
   // print_r($elements); die;
    return $elements;
    /*$unit_price = $entity->getTranslation($langcode)->get('add_to_cart_price')->getValue();
    $unit_price = $unit_price ? $unit_price[0]['value'] : 0;
    if(empty($unit_price)) {
      $form = array();
      drupal_set_message(t('No price configured for this product'),'warning');
    }else{
       $form = \Drupal::formBuilder()->getForm($id,$entity->id(),$entity->getEntityTypeId(),$langcode);
    }
   
    return $form; */
  }

}
