<?php
/**
 * @file
 * Contains \Drupal\block_example\Plugin\Block\BlockExampleSimpleBlock.
 */

namespace Drupal\block_example\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\file\Entity\File;

/**
 * Provides a 'Example: empty block' block.
 *
 * @Block(
 *   id = "example_simple",
 *   admin_label = @Translation("Simple example block")
 * )
 */
class BlockExampleSimpleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $textarea = $config['textarea'];
    $image_id = $config['image'][0];
    $textfield = $config['textfield'];
    $date = $config['date'];

    $file = File::load($image_id);

    $variables = [
      'style_name' => 'example_block',
      'uri' => $file->getFileUri(),
    ];

    // The image.factory service will check if our image is valid.
    $image = \Drupal::service('image.factory')->get($file->getFileUri());
    if ($image->isValid()) {
      $variables['width'] = $image->getWidth();
      $variables['height'] = $image->getHeight();
    }
    else {
      $variables['width'] = $variables['height'] = NULL;
    }

    $logo_render_array = [
      '#theme' => 'image_style',
      '#width' => $variables['width'],
      '#height' => $variables['height'],
      '#style_name' => $variables['style_name'],
      '#uri' => $variables['uri'],
    ];

    $renderer = \Drupal::service('renderer');

    $block = [];

    $block['textfield'] = [
      '#type' => 'markup',
      '#markup' => t("$textfield"),
    ];

    $block['image'] = [
      '#type' => 'markup',
      '#markup' => $renderer->render($logo_render_array),
    ];

    $block['textarea'] = [
      '#type' => 'markup',
      '#markup' => t("$textarea"),
    ];

    $block['date'] = [
      '#type' => 'markup',
      '#markup' => t("<br>" . "$date"),
    ];
    return $block;
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $form['#attributes'] = ['enctype' => 'multipart/form-data'];

    $form['textfield'] = [
      '#type' => 'textfield',
      '#title' => t('title'),
    ];
    $form['image'] = [
      '#type' => 'managed_file',
      '#title' => t('File *'),
      '#size' => 20,
      '#description' => t(''),
      '#upload_validators' => [
        'file_validate_extensions' => [
          'jpg',
          'JPG',
          'jpeg',
          'gif',
          'bmp',
          'png',
        ],
      ],
      '#upload_location' => 'public://my_files/',
    ];
    $form['textarea'] = [
      '#type' => 'textarea',
      '#title' => t('Description'),
    ];
    $form['date'] = [
      '#type' => 'date',
      '#title' => t('date'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $textfield_count = $form_state->getValue('textfield_count');
    $textarea_count = $form_state->getValue('textarea_count');

    if (strlen($textfield_count) > 50) {
      $form_state->setErrorByName('textfield', t('This field can not contain more than 50 characters.'));
    }

    if (strlen($textarea_count) > 255) {
      $form_state->setErrorByName('textarea', t('This field can not contain more than 255 characters.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['textfield'] = $form_state->getValue('textfield');
    $this->configuration['image'] = $form_state->getValue('image');
    $this->configuration['textarea'] = $form_state->getValue('textarea');
    $this->configuration['date'] = $form_state->getValue('date');
  }
}