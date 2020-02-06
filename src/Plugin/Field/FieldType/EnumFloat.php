<?php

namespace Drupal\enum\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\enum\Plugin\Field\EnumFieldTypeBase;

/**
 * Plugin implementation of the 'enum_float' field type.
 *
 * @FieldType(
 *   id = "enum_float",
 *   label = @Translation("Enum(float)"),
 *   description = @Translation("Enum field type float"),
 *   default_widget = "options_select",
 *   default_formatter = "list_default"
 * )
 */
class EnumFloat extends EnumFieldTypeBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('float')
      ->setLabel(t('Float value'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'float',
        ],
      ],
      'indexes' => [
        'value' => ['value'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected static function castAllowedValue($value) {
    return (float) $value;
  }

}
