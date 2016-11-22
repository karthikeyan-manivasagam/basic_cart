<?php

namespace Drupal\basic_cart\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\basic_cart\Utility;

class AddToCart extends FormBase {

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
    $form['id'] = array(
      '#type' => 'hidden',
      '#required' => TRUE,
      '#value' => $id,  
    );
    $form['entitytype'] = array(
      '#type' => 'hidden',
      '#required' => TRUE,
      '#value' => $entitytype,  
    );
    $form['langcode'] = array(
      '#type' => 'hidden',
      '#required' => TRUE,
      '#value' => $langcode,  
    );    

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t($config->get('add_to_cart_button')),
    );
    return $form;
  }


  /**
   * {@inheritdoc}
   */
 public function submitForm(array &$form,FormStateInterface $form_state) {
	 $id = (int) $form_state->getValue('id');
   $langcode = $form_state->getValue('langcode');
   $entitytype = $form_state->getValue('entitytype');
   $params = array("quantity" => 1, "langcode" => $langcode, "entitytype" => $entitytype);
   Utility::addToCart($id, $params);
  }

}

