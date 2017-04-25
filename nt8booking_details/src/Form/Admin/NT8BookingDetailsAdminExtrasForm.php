<?php

namespace Drupal\nt8booking_details\Form\Admin;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\nt8booking_enquiry\Service\NT8BookingService;
use Drupal\nt8tabsio\Service\NT8TabsRestService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The booking path details form.
 */
class NT8BookingDetailsAdminExtrasForm extends ConfigFormBase {

  /**
   * Instance of NT8TabsRestService.
   *
   * @var \Drupal\nttabsio\Service\NTTabsRestService
   */
  protected $nt8TabsRestService;

  /**
   * Instance of NT8BookingService.
   *
   * @var NT8BookingService
   */
  protected $nt8bookingService;

  /**
   * {@inheritdoc}
   */
  public function __construct(NT8TabsRestService $nt8TabsRestService, NT8BookingService $nt8bookingService) {
    $this->nt8TabsRestService = $nt8TabsRestService;
    $this->nt8bookingService = $nt8bookingService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      // Load the service required to construct this class.
      $container->get('nt8tabsio.tabs_service'), $container->get('nt8booking.service'), $container->get('event_dispatcher')
    );
  }

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

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('nt8booking_details.settings');

    $attr_pets = $config->get('attr_pets');
    $extras_pets = $config->get('extras_pets');
    $extras = $config->get('extras');

    // Get all attributes as $options.
    $this->nt8TabsRestService->setSuperDebug(TRUE);
    $api_info_string = $this->nt8TabsRestService->get('/');
    $api_info = json_decode($api_info_string, TRUE);
    $all_atts = $api_info['constants']['attributes'];
    $attr_ops = ['none' => '--'];
    foreach ($all_atts as $value) {
      $code = $value['code'];
      $lab = $value['label'];
      $attr_ops[$value['code']] = t(
        '[:cod] :lab', array(':cod' => $code, ':lab' => $lab)
      );
    }

    // Which of the extras are web bookable.
    $all_extras = $api_info['constants']['extras'];
    foreach ($all_extras as $extra) {
      $code = $extra['code'];
      $label = $extra['label'];
      $cb_webextras[$code] = format_string(
        '@label (@code)', ['@label' => $label, '@code' => $code]
      );
    }

    $form['pets'] = [
      '#type' => 'fieldset',
      '#title' => t('Pets Overrides'),
    ];

    $form['pets']['attr_pets'] = [
      '#type' => 'select',
      '#options' => $attr_ops,
      '#description' => t('Which attribute indicates the max number of pets on a property.'),
      '#default_value' => $attr_pets,
    ];

    $form['pets']['extras_pets'] = [
      '#type' => 'select',
      '#options' => array_merge(['none' => '--'], $cb_webextras),
      '#description' => t('Which extra is the pet extra.'),
      '#default_value' => $extras_pets,
    ];

    $form['extras'] = [
      '#type' => 'fieldset',
      '#title' => t('Which extras should be web bookable'),
      '#description' => t('Select which extras should appear on the booking form.'),
    ];

    $form['extras']['extras'] = [
      '#type' => 'checkboxes',
      '#options' => $cb_webextras,
      '#default_value' => $extras,
    ];

    $form['extras']['desc'] = [
      '#type' => 'item',
      '#markup' => t('<strong>You MUST apply changes here before the extras will appear in the field set below.</strong>'),
    ];

    // Which extras are overriden by attributes on a per property basis.
    $form['attr'] = [
      '#type' => 'fieldset',
      '#title' => 'Per property overrides',
      '#description' => t('If an extra is overriden by an attribute (e.g. to set the maximum value or a custom price) the associate the extra with the correct attribute here.'),
    ];

    $attributes = $api_info['constants']['attributes'];
    array_multisort($attributes);

    foreach ($cb_webextras as $code => $label) {
      if (isset($extras[$code]) && $extras[$code]) {
        // We need to build a custom key for each of the extras.
        $attrs = array('false' => '-- use global --');
        foreach ($attributes as $attr) {
          $attrs[$attr['code']] = format_string(
            ':label (:code)', [':label' => $attr['label'], ':code' => $attr['code']]
          );
        }

        $varname = 'extras_override_' . $code;
        $default = $config->get($varname);
        $form['attr'][$varname] = [
          '#type' => 'select',
          '#title' => $label,
          '#options' => $attrs,
          '#default_value' => $default,
        ];
        // variable_set($varname, $default);
      }
    }

    $form['#attached'] = array(
      'library' => array('nt8booking_details/nt8booking-details-admin'),
    );

    return parent::buildForm($form, $form_state);
  }


  public function submitForm(array &$form, FormStateInterface $form_state) {
    $attr_pets = $form_state->getValue('attr_pets');
    $extras_pets = $form_state->getValue('extras_pets');
    $extras = $form_state->getValue('extras');

    $this->config('nt8booking_details.settings')
      ->set('attr_pets', $attr_pets)
      ->set('extras_pets', $extras_pets)
      ->set('extras', $extras)
      ->save();

    $all_values = $form_state->getValues();
    foreach ($all_values as $key => $val) {
      if (strpos($key, 'extras_override_') === 0) {
        $this->config('nt8booking_details.settings')->set($key, $val)->save();
      }
    }

    parent::submitForm($form, $form_state);
  }

}
