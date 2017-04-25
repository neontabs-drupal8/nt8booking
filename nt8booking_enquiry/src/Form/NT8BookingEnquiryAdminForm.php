<?php

namespace Drupal\nt8booking_enquiry\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\nt8tabsio\Service\NT8TabsRestService;
use Drupal\nt8booking_enquiry\Service\NT8BookingService;
use Drupal\nt8booking_enquiry\Event\NT8BookingEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The booking path details form.
 */
class NT8BookingEnquiryAdminForm extends ConfigFormBase {

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
      'nt8booking_enquiry.settings',
    ];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('nt8booking_enquiry.settings');
    $error_codes = $config->get('error_codes');
    $lead_time = $config->get('lead_time');

    $form['lead_time'] = [
      '#type' => 'textfield',
      '#title' => t('Web booking lead time'),
      '#default_value' => $lead_time,
      '#description' => t("Don't take a webbooking if it is within 'x' days."),
    ];

    $form['error_codes'] = [
      '#type' => 'textarea',
      '#title' => t('TABS Error Codes'),
      '#rows' => 25,
      '#description' => t('This text area translates the TABS Error code to readable text.'),
      '#default_value' => json_encode(json_decode($error_codes), JSON_PRETTY_PRINT),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $error_codes = $form_state->getValue('error_codes');

    $data = json_decode($error_codes, TRUE);
    if (!$data) {
      $form_state->setErrorByName('error_codes', t('Invalid JSON'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $error_codes = $form_state->getValue('error_codes');
    $lead_time = $form_state->getValue('lead_time');

    $config = $this->config('nt8booking_details.settings')
      ->set('error_codes', $error_codes)
      ->set('lead_time', $lead_time)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
