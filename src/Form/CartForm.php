<?php

namespace Drupal\basic_cart\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\basic_cart\Utility;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

class CartForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
   
    return 'basic_cart_cart_form';
  	
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
      // Getting the shopping cart.
    $form['#theme'] = 'cart_form';
 
    
    $cart = Utility::getCart();
    $config = Utility::cartSettings();  
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $price = Utility::getTotalPrice();
    $total = Utility::formatPrice($price->total);
    $vat_is_enabled = (int) $config->get('vat_state');
    $vat_value = !empty ($vat_is_enabled) && $vat_is_enabled ? Utility::formatPrice($price->vat) : 0;

    // And now the form.
    $form['cartcontents'] = array(
      // Make the returned array come back in tree form.
      '#tree' => TRUE,
      '#prefix' => '<div class="basic_cart-cart basic_cart-grid">',
      '#suffix' => '</div>',
    );
    // Cart elements.
    foreach ($cart['cart_quantity'] as $nid => $quantity) {
      $form['cartcontents'][$nid] = array(
        '#type' => $config->get('quantity_status') ? 'textfield' : 'markup',
        '#size' => 1,
        '#quantity_id'  => $nid,
        "#suffix" =>    '</div></div></div>',
        "#prefix" => $this->getQuantityPrefixSuffix($nid,$langcode),
        '#default_value' => $quantity,
        // TO DO  
       //'#url' => $cart['cart'][$nid]->urlInfo('canonical'),
        //'#theme' => 'basic_cart_quantity',
      );
    }

      $form['total_price'] = array(
      '#markup' => Utility::renderCartBlock('total-price-markup.html.twig', Utility::getTotalPriceMarkupData()),
    );


    // Buttons.
    $form['buttons'] = array(
      '#tree' => TRUE,
    );

    $form['buttons']['update'] = array(
      '#type' => 'submit',
      '#value' =>  t($config->get('cart_update_button')),
      '#name' => "update",
    );
  
    if($config->get('order_status')) {
       $form['buttons']['checkout'] = array(
          '#type' => 'submit',
          '#value' =>  t('Checkout'),
          '#name' => "checkout",
       );
    }

    return $form;

  }

  /**
   * {@inheritdoc}
   */
 public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = Utility::cartSettings();  

    if($config->get('quantity_status')) {

      foreach ($form_state->getValue('cartcontents') as $nid => $value) {
        $quantity = (int) $value;
        if ($quantity > 0) {
          $_SESSION['basic_cart']['cart_quantity'][$nid] = $quantity;
        }
        // If the quantity is zero, we just remove the node from the cart.
        elseif ($quantity == 0) {
          unset($_SESSION['basic_cart']['cart'][$nid]);
          unset($_SESSION['basic_cart']['cart_quantity'][$nid]);
        }
      }
      Utility::cartUpdatedMessage();
    }
    $config = Utility::cartSettings();
    if($config->get('order_status') && $form_state->getValue('checkout')) {
      $url = new Url('basic_cart.checkout');    
      $form_state->setRedirectUrl($url);
    }
  }

  public function getQuantityPrefixSuffix($nid, $langcode) {
    $url = new Url('basic_cart.cartremove', array("nid" => $nid));
    $link = new Link('X',$url);
    $delete_link = '<span class="basic_cart-delete-image-image">'.$link->toString().'</span>';
    $cart = Utility::getCart($nid);
     if(!empty($cart['cart'])) {
    $unit_price = $cart['cart']->getTranslation($langcode)->get('add_to_cart_price')->getValue();  
    $unit_price = isset($unit_price[0]['value']) ? $unit_price[0]['value'] : 0;
    $title = $cart['cart']->getTranslation($langcode)->get('title')->getValue()[0]['value'];
    // Price and currency.
    $url = new Url('entity.node.canonical',array("node"=>$nid));
    $link = new Link($title,$url);
    $unit_price = isset($unit_price) ? $unit_price : 0;
    $unit_price = Utility::formatPrice($unit_price);
    
    // Prefix.
    $prefix  = '<div class="basic_cart-cart-contents row">';
    $prefix .= '  <div class="basic_cart-delete-image cell">' . $delete_link . '</div>';
    $prefix .= '  <div class="basic_cart-cart-node-title cell">' . $link->toString() . '<br />';
    $prefix .= '  </div>';
    $prefix .= '  <div class="cell basic_cart-cart-unit-price"><strong>' . $unit_price . '</strong></div>';
    $prefix .= '  <div class="basic_cart-cart-quantity cell">';
    $prefix .= '    <div class="cell">';
    }else{
      $prefix = '';
    }
    return $prefix;
  }
}

