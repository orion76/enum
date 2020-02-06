<?php

namespace Drupal\enum\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\enum\Plugin\Field\EnumFieldTypeBase;
use function t;

/**
 * Plugin implementation of the 'enum_string' field type.
 *
 * @FieldType(
 *   id = "enum_string",
 *   label = @Translation("Enum(string)"),
 *   description = @Translation("Enum field type string"),
 *   category = @Translation("Text"),
 *   default_widget = "options_select",
 *   default_formatter = "list_default"
 * )
 */
class EnumString extends EnumFieldTypeBase {


  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Text value'))
      ->addConstraint('Length', ['max' => 255])
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
          'type' => 'varchar',
          'length' => 255,
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
    return (string) $value;
  }

}
