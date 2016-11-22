<?php

namespace Drupal\basic_cart\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\basic_cart\Utility;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a 'Basic Cart' block.
 *
 * @Block(
 *   id = "basic_cart_cartblock",
 *   admin_label = @Translation("Basic Cart Block")
 * )
 */
class CartBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $utility = new Utility();
  	$config = $utility::cart_settings();
     return array(
      //'#theme' => 'basic_cart_cart_template',
      //'#basic_cart' => array(),	
      '#type' => 'markup',
      '#title' => $config->get('cart_block_title'),
      '#markup' => $utility->get_cart_content(),
      '#cache' => array('max-age' => 0),
    );
  }
}