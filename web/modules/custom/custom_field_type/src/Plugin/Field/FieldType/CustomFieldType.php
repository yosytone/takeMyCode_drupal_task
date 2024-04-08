<?php

namespace Drupal\custom_field_type\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'custom_field_type' field type.
 *
 * @FieldType(
 *   id = "custom_field_type",
 *   label = @Translation("Custom Field Type"),
 *   category = @Translation("Custom"),
 *   default_widget = "custom_field_widget",
 *   default_formatter = "custom_field_formatter"
 * )
 */
class CustomFieldType extends FieldItemBase {

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
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')->setLabel(t('Value'));
    return $properties;
  }
}
