<?php

namespace Drupal\custom_field_type\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Plugin implementation of the 'custom_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "custom_field_widget",
 *   label = @Translation("Custom Field Widget"),
 *   field_types = {
 *     "custom_field_type"
 *   }
 * )
 */
class CustomFieldWidget extends OptionsSelectWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $node = \Drupal::routeMatch()->getParameter('node');
    $field_name = $this->fieldDefinition->getName();

    $loaded_value = [];
    if ($node instanceof \Drupal\node\NodeInterface && !$node->get($field_name)->isEmpty()) {
      $field_value = $node->get($field_name)->getValue();
      $loaded_value = unserialize($field_value[0]['value']);
    }

    $loaded_value_names = [];
    if ($loaded_value) {
      //получаем сохраненные значения 
      $loaded_value_names = $this->getTerms($loaded_value);
    }

    $host = \Drupal::request()->getSchemeAndHttpHost();
    $element['select'] = [
      '#type' => 'select2',
      '#multiple' => 'multiple',
      '#default_value' => $loaded_value,
      '#options' => $loaded_value_names,
      '#title' => t('Tags'),
      '#required' => FALSE,
      '#validated' => TRUE,
      '#select2' => [
        'ajax' => [
            'url' => $host . '/get-tags',
            'dataType' => 'json',
            'delay' => 250,
            'data' => "function (params) {
                return {
                    q: params.term,
                    page: params.page
                };
            }"
        ],
        'placeholder' => 'Search for a tags',
        'minimumInputLength' => 1,
      ],
    ];

    return ['value' => $element];
  }

  /**
   * Получайте названия тегов из их идентификаторов.
   */
  public function getTerms($term_ids) {
    $term_names = [];

    foreach ($term_ids as $term_id) {
      $term = Term::load($term_id);
      if ($term) {
        $term_names[$term_id] = $term->getName();
      }
    }

    return $term_names;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $ids = [];

    foreach ($values as $value) {
      // Получаем значение 'select' из массива значений
      $select_values = $value['value']['select'];
      
      if (is_array($select_values)) {
        $ids = array_merge($ids, $select_values);
      } elseif (is_string($select_values)) {
        $ids[] = $select_values;
      }
    }

    $str = serialize($ids);
    return $str;
  }
}
