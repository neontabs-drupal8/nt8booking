<?php

namespace Drupal\nt8booking_details\Form\Admin;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;

/**
 * The booking path details form.
 */
class NT8BookingDetailsAdminFinalizeForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return __CLASS__;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'nt8booking_details.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('nt8booking_details.settings');

    $tnc_url = $config->get('tnc_url');
    $other_error_text = $config->get('other_error_text');
    $source_other = $config->get('source_other');

    $form['tnc_url'] = [
      '#type' => 'textfield',
      '#title' => t('Terms and conditions URL'),
      '#default_value' => $tnc_url,
      '#description' => t('The path to the terms and conditions page, to be passed to the url() function.'),
    ];

    $form['other_error_text'] = [
      '#type' => 'textfield',
      '#title' => t('Other error text'),
      '#default_value' => $other_error_text,
      '#description' => t('The error message to use if the other field is left blank.'),
    ];

    $form['source_other'] = [
      '#type' => 'textfield',
      '#title' => t('Other source text'),
      '#default_value' => $source_other,
      '#description' => t('The text to be added to the end of the sources list to indicate free text entry.  If blank, no other source will be added'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $tnc_url = $form_state->getValue('tnc_url');

    if (!UrlHelper::isValid($tnc_url)) {
      $form_state->setErrorByName('tnc_url', t('Invalid URL'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $tnc_url = $form_state->getValue('tnc_url');
    $other_error_text = $form_state->getValue('other_error_text');
    $source_other = $form_state->getValue('source_other');

    $this->config('nt8booking_details.settings')
      ->set('tnc_url', $tnc_url)
      ->set('other_error_text', $other_error_text)
      ->set('source_other', $source_other)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
