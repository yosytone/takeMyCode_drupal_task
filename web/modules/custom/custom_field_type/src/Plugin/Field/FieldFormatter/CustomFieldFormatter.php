<?php

namespace Drupal\custom_field_type\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Define the "custom field formatter".
 *
 * @FieldFormatter(
 *   id = "custom_field_formatter",
 *   label = @Translation("Custom Field Formatter"),
 *   description = @Translation("Desc for Custom Field Formatter"),
 *   field_types = {
 *     "custom_field_type"
 *   }
 * )
 */
class CustomFieldFormatter extends FormatterBase {
    /**
     * {@inheritdoc}
     */
    public function viewElements(FieldItemListInterface $items, $langcode) {
        $elements = [];

        foreach ($items as $delta => $item) {
            // Распаковываем сериализованную строку в массив.
            $ids = unserialize($item->value);

            // Загружаем соответствующие термины из базы данных.
            $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($ids);

            // Создаем ссылки на страницы тегов.
            $links = [];
            foreach ($terms as $term) {
                $url = $term->toUrl();
                $links[] = Link::fromTextAndUrl($term->label(), $url)->toString();
            }

            // Формируем разметку для каждого элемента.
            $elements[$delta] = [
                '#markup' => implode(', ', $links),
            ];
        }

        return $elements;
    }
}
