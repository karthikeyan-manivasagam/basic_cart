<?php

namespace Drupal\basic_cart\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
/**
 * Plugin implementation of the 'addtocart' formatter.
 *
 * @FieldFormatter(
 *   id = "addtocart",
 *   module = "basic_cart",
 *   label = @Translation("Add to cart"),
 *   field_types = {
 *     "addtocart"
 *   }
 * )
 */
class AddtoCartFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
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


  //$link = new Link($this->t($config->get('add_to_cart_button')),$url);
      foreach ($items as $delta => $item) {
        $elements[$delta] = ['#type' => 'container',
        '#attributes' => ['class' => 'ajax-addtocart-wrapper' ,'id' => 'ajax-addtocart-message-'.$entity->id()],
        '#prefix' =>'<div class="addtocart-wrapper-container"><div class="addtocart-link-class">'.$link."</div>",
        '#suffix' =>'</div>',
        ];
      }
     
       $elements['#attached']['library'][] = 'core/drupal.ajax';


    return $elements;
  }

}
