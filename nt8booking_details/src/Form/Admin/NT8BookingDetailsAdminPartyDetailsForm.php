<?php

namespace Drupal\nt8booking_details\Form\Admin;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

/**
 * The booking path details form.
 */
class NT8BookingDetailsAdminPartyDetailsForm extends ConfigFormBase {

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('nt8booking_details.settings');

    $adult_ages = $config->get('adult_ages');
    $child_ages = $config->get('child_ages');
    $infant_ages = $config->get('infant_ages');

    $form['adult_ages'] = array(
      '#type' => 'textarea',
      '#title' => t('Adult age bands'),
      '#default_value' => json_encode(json_decode($adult_ages), JSON_PRETTY_PRINT),
      '#rows' => 8,
      '#description' => t('JSON encoded array of age bands for adults.'),
    );

    $form['child_ages'] = array(
      '#type' => 'textarea',
      '#title' => t('Child age bands'),
      '#default_value' => json_encode(json_decode($child_ages), JSON_PRETTY_PRINT),
      '#rows' => 8,
      '#description' => t('JSON encoded array of age bands for adults.'),
    );

    $form['infant_ages'] = array(
      '#type' => 'textarea',
      '#title' => t('Infant age bands'),
      '#default_value' => json_encode(json_decode($infant_ages), JSON_PRETTY_PRINT),
      '#rows' => 8,
      '#description' => t('JSON encoded array of age bands for adults.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'nt8booking_details.settings',
    ];
  }

  public function getFormId() {
    return __CLASS__;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $age_list = $form_state->getValue('adult_ages');
    $data = json_decode($age_list, TRUE);
    if (!$data) {
      $form_state->setErrorByName('adult_ages', t('Invalid JSON'));
    }

    $age_list = $form_state->getValue('child_ages');
    $data = json_decode($age_list, TRUE);
    if (!$data) {
      $form_state->setErrorByName('child_ages', t('Invalid JSON'));
    }

    $age_list = $form_state->getValue('infant_ages');
    $data = json_decode($age_list, TRUE);
    if (!$data) {
      $form_state->setErrorByName('infant_ages', t('Invalid JSON'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $adult_ages = $form_state->getValue('adult_ages');
    $child_ages = $form_state->getValue('child_ages');
    $infant_ages = $form_state->getValue('infant_ages');

    $config = $this->config('nt8booking_details.settings')
      ->set('adult_ages', $adult_ages)
      ->set('child_ages', $child_ages)
      ->set('infant_ages', $infant_ages)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
