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
      'link_hierarchy' => 'default',
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

    $form['link_hierarchy'] = [
      '#title' => $this->t('Link Hierarchy'),
      '#type' => 'select',
      '#options' => [
        'child' => $this->t('Child'),
        'parent' => $this->t('Parent'),
        'default' => $this->t('Default'),
      ],
      '#default_value' => $this->getSetting('link_hierarchy'),
      '#description' => $this->t('Choose whether linking should be based on the child term, the parent term, or follow the default per-term behavior.'),
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

    $summary[] = $this->t('Link hierarchy: @mode', [
      '@mode' => $this->t(ucfirst((string) $this->getSetting('link_hierarchy'))),
    ]);

    return $summary;
  }

  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $link = $this->getSetting('link');
    $alternative_link_pattern = trim((string) $this->getSetting('alternative_link_pattern'));
    $link_hierarchy = (string) $this->getSetting('link_hierarchy');
 
    foreach ($items as $delta => $item) {
      $term = $item->entity;

      if (!$term) {
        continue;
      }

      // Build hierarchy (parents up to root)
      $hierarchy = $this->buildHierarchy($term);

      if ($link_hierarchy === 'child' || $link_hierarchy === 'parent') {
        $selected_term = $link_hierarchy === 'child' ? end($hierarchy) : reset($hierarchy);
        $hierarchy_text = implode(' / ', array_map(function ($hierarchy_term) {
          return htmlspecialchars($hierarchy_term->label(), ENT_QUOTES, 'UTF-8');
        }, $hierarchy));

        $rendered_hierarchy = $this->buildTermMarkup($hierarchy_text, $selected_term, $link, $alternative_link_pattern);

        $elements[$delta] = [
          '#markup' => '<div class="taxonomy umd-lib body body--content wysiwyg-editor s-margin-general-medium">' . $rendered_hierarchy . '</div>',
          '#allowed_tags' => ['span', 'div', 'a'],
        ];
        continue;
      }
 
      // Render each term wrapped in span
      $rendered_terms = [];
      foreach ($hierarchy as $parent_term) {
        $term_label = $this->buildTermMarkup($parent_term->label(), $parent_term, $link, $alternative_link_pattern);

        $rendered_terms[] = '<span class="term">' . $term_label . '</span>';
      }

      // Join with separator
      $elements[$delta] = [
        '#markup' => '<div class="taxonomy umd-lib body body--content wysiwyg-editor s-margin-general-medium">' . implode(' / ', $rendered_terms) . '</div>',
        '#allowed_tags' => ['span', 'div', 'a'],
      ];
    }
    return $elements;
  }
 
  /**
   * Build the hierarchy of terms up to root.
   */
  private function buildTermMarkup($text, $term, $link, $alternative_link_pattern) {
    $escaped_text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    if ($alternative_link_pattern !== '') {
      $url = str_replace('{placeholder}', $term->label(), $alternative_link_pattern);
      return \Drupal\Core\Link::fromTextAndUrl(
        $escaped_text,
        $this->buildUrlFromPattern($url)
      )->toString();
    }

    if ($link) {
      return \Drupal\Core\Link::fromTextAndUrl(
        $escaped_text,
        $term->toUrl()
      )->toString();
    }

    return $escaped_text;
  }

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