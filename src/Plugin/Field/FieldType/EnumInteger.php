<?php

namespace Drupal\enum\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\enum\Plugin\Field\EnumFieldTypeBase;

/**
 * Plugin implementation of the 'enum_integer' field type.
 *
 * @FieldType(
 *   id = "enum_integer",
 *   label = @Translation("Enum(integer)"),
 *   description = @Translation("Enum field type string"),
 *   default_widget = "options_select",
 *   default_formatter = "list_default"
 * )
 */
class EnumInteger extends EnumFieldTypeBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('integer')
      ->setLabel(t('Integer value'))
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
          'type' => 'int',
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
    return (int) $value;
  }

}
