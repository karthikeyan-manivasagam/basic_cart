<?php

namespace Drupal\basic_cart\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\basic_cart\Utility;

class AddToCartWithQuantity extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
   
  return 'basic_cart_addtocart_form';
  	
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL, $entitytype = NULL, $langcode = NULL) {
    $config = Utility::cartSettings();
    $form['addtocart_container'] = array(
      '#type' => 'fieldset',
      '#title' => t($config->get('add_to_cart_button')),
    );
    $form['addtocart_container']['id'] = array(
      '#type' => 'hidden',
      '#required' => TRUE,
      '#value' => $id, 
    );

    $form['addtocart_container']['entitytype'] = array(
      '#type' => 'hidden',
      '#required' => TRUE,
      '#value' => $entitytype,  
    );
    $form['addtocart_container']['langcode'] = array(
      '#type' => 'hidden',
      '#required' => TRUE,
      '#value' => $langcode,  
    ); 

    $form['addtocart_container']['quantity'] = array(
      '#type' => 'textfield',
      '#title' => t($config->get('quantity_label')),
      '#required' => TRUE,
      '#size' => 1,
      '#maxlength' => 6,
    ); 
    $form['addtocart_container']['submit'] = array(
      '#type' => 'submit',
      '#value' => t($config->get('add_to_cart_button')),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
 public function submitForm(array &$form, FormStateInterface $form_state) {

		$id = (int) $form_state->getValue('id');
		$quantity = (int) $form_state->getValue('quantity');
    $langcode = $form_state->getValue('langcode');
    $entitytype = $form_state->getValue('entitytype');
    $params = array("quantity" => $quantity, "langcode" => $langcode, "entitytype" => $entitytype);
    Utility::addToCart($id, $params);
  }

}

