<?php

namespace Drupal\umdlib_ds_layout_tools\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'umd_taxonomy_hierarchy' formatter.
 *
 * @FieldFormatter(
 *   id = "umd_taxonomy_hierarchy",
 *   label = @Translation("Full hierarchy (wrapped)"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */

class UMDTaxonomyHierarchyFormatter extends FormatterBase {
 
  public static function defaultSettings() {
    return [
      'link' => TRUE,
    ] + parent::defaultSettings();
  }
 
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['link'] = [
      '#title' => $this->t('Link term names to the term page'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('link'),
    ];

    return $form;
  }
 
  public function settingsummary() {
    $summary = [];
    $summary[] = $this->t('Link terms: @link', [
      '@link' => $this->getSetting('link') ? $this->t('Yes') : $this->t('No'),
    ]);
    return $summary;
  }

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $link = $this->getSetting('link');
 
    foreach ($items as $delta => $item) {
      $term = $item->entity;

      if (!$term) {
        continue;
      }

      // Build hierarchy (parents up to root)
      $hierarchy = $this->buildHierarchy($term);
 
      // Render each term wrapped in span
      $rendered_terms = [];
      foreach ($hierarchy as $parent_term) {
        $term_label = $parent_term->label();
 
        if ($link) {
          $term_label = $parent_term->toLink()->toString();
        } else {
          // Escape the label for safety
          $term_label = htmlspecialchars($term_label, ENT_QUOTES, 'UTF-8');
        }

        $rendered_terms[] = '<span class="term">' . $term_label . '</span>';
      }

      // Join with separator
      $elements[$delta] = [
        '#markup' => '<div class="taxonomy umd-lib body body--content wysiwyg-editor s-margin-general-medium">' . implode(' / ', $rendered_terms) . '</div>',
        '#allowed_tags' => ['span', 'div'],
      ];
    }
    return $elements;
  }
 
  /**
   * Build the hierarchy of terms up to root.
   */
  private function buildHierarchy($term) {
    $hierarchy = [];
    $current = $term;
 
    while ($current) {
      array_unshift($hierarchy, $current);
      $parent_ids = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadParents($current->id());
      $current = reset($parent_ids) ?: NULL;
    }

    return $hierarchy;
  }
}