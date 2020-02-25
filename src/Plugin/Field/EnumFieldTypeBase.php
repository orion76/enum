<?php

namespace Drupal\enum\Plugin\Field;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\OptionsProviderInterface;
use Drupal\enum\Entity\EnumInterface;
use Drupal\enum\EnumServiceInterface;
use function array_keys;
use function array_rand;
use function options_allowed_values;
use function t;

abstract class EnumFieldTypeBase extends FieldItemBase implements OptionsProviderInterface {

  /** @var EnumServiceInterface */
  protected $_enumService;

  protected function getEnumService(): EnumServiceInterface {
    if (empty($this->_enumService)) {
      $this->_enumService = \Drupal::service('enum.service');
    }
    return $this->_enumService;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
        'enum' => '',
      ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function getPossibleValues(AccountInterface $account = NULL) {
    // Flatten options firstly, because Possible Options may contain group
    // arrays.
    $flatten_options = OptGroup::flattenOptions($this->getPossibleOptions($account));
    return array_keys($flatten_options);
  }

  /**
   * {@inheritdoc}
   */
  public function getPossibleOptions(AccountInterface $account = NULL) {
    return $this->getSettableOptions($account);
  }

  /**
   * {@inheritdoc}
   */
  public function getSettableValues(AccountInterface $account = NULL) {
    // Flatten options firstly, because Settable Options may contain group
    // arrays.
    $flatten_options = OptGroup::flattenOptions($this->getSettableOptions($account));
    return array_keys($flatten_options);
  }


  /**
   * {@inheritdoc}
   */
  public function getSettableOptions(AccountInterface $account = NULL) {
    $field_definition = $this->getFieldDefinition();
    $enum_id = $field_definition->getSetting('enum');

    /** @var $enum EnumInterface */
    $enum = $this->getEnumService()->loadEnum($enum_id);
    $options = [];
    if ($enum instanceof EnumInterface) {
      $options = $enum->getEnumLabels();
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $allowed_options = options_allowed_values($field_definition->getFieldStorageDefinition());
    $values['value'] = array_rand($allowed_options);
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->value) && (string) $this->value !== '0';
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element['enum'] = [
      '#type' => 'select',
      '#title' => t('Enum type'),
      '#options' => $this->getEnumService()->getEnumLabels(),
      '#default_value' => $this->getSetting('enum'),
      '#required' => TRUE,
      '#disabled' => $has_data,
      '#size' => 1,
    ];

    return $element;
  }


  /**
   * Converts a value to the correct type.
   *
   * @param mixed $value
   *   The value to cast.
   *
   * @return mixed
   *   The casted value.
   */
  protected static function castAllowedValue($value) {
    return $value;
  }

}
