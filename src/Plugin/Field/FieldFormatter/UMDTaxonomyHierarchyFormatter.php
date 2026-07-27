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
      'alternative_link_pattern' => '',
    ] + parent::defaultSettings();
  }
 
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['link'] = [
      '#title' => $this->t('Link term names to the term page'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('link'),
    ];

    $form['alternative_link_pattern'] = [
      '#title' => $this->t('Alternative Link Pattern'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('alternative_link_pattern'),
      '#description' => $this->t('If set, terms will link to this URL with the term label replacing the {placeholder} token. This overrides the link setting above.'),
    ];

    return $form;
  }
 
  public function settingssummary() {
    $summary = [];
    $summary[] = $this->t('Link terms: @link', [
      '@link' => $this->getSetting('link') ? $this->t('Yes') : $this->t('No'),
    ]);

    $pattern = trim((string) $this->getSetting('alternative_link_pattern'));
    if ($pattern !== '') {
      $summary[] = $this->t('Alternative link pattern: @pattern', [
        '@pattern' => $pattern,
      ]);
    }

    return $summary;
  }

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $link = $this->getSetting('link');
    $alternative_link_pattern = trim((string) $this->getSetting('alternative_link_pattern'));
 
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
 
        if ($alternative_link_pattern !== '') {
          $url = str_replace('{placeholder}', $term_label, $alternative_link_pattern);
          $term_label = \Drupal\Core\Link::fromTextAndUrl(
            $term_label,
            $this->buildUrlFromPattern($url)
          )->toString();
        }
        elseif ($link) {
          $term_label = $parent_term->toLink()->toString();
        }
        else {
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
  private function buildUrlFromPattern($pattern) {
    if (preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:/', $pattern)) {
      return \Drupal\Core\Url::fromUri($pattern);
    }

    if (str_starts_with($pattern, '/')) {
      return \Drupal\Core\Url::fromUserInput($pattern);
    }

    return \Drupal\Core\Url::fromUserInput('/' . ltrim($pattern, '/'));
  }

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