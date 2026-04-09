<?php

namespace Drupal\umdlib_ds_layout_tools\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;
use Drupal\Core\Render\Markup;
use Drupal\Component\Render\MarkupInterface;

/**
 * Twig extension providing custom functionalities.
 *
 * @package Drupal\umdlib_ds_layout_tools\TwigExtension
 */
class ComponentTwigExtension extends AbstractExtension {
  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'umdlib_ds_layout_tools.twig_extension';
  }

  public function getFilters() {
    return [
      new TwigFilter(
        'strip_inline_styles', [
        $this,
        'stripInlineStyles'
      ])
    ];
  }

  /**
   * Strips inline styles from the given HTML string.
   *
   * @param string $field
   *   The input HTML string.
   *
   * @return string
   *   The processed HTML string with inline styles removed.
   */
  public function stripInlineStyles($field) {
    dsm($field, 'strip_inline_styles input');
    // Implementation for stripping inline styles
    $value = $this->getUIPatternFieldValue($field);
    $processedValue = preg_replace('/style="[^"]*"/i', '', $value);
    dsm($processedValue, 'strip_inline_styles output');
    return $processedValue;
  }

  protected function getUIPatternFieldValue($field) {
    // At this point, Views would have already formatted the value
    // into a string. We just need to dig it out of the render array.
    if ($field instanceof Markup) {
      $field = $field->__toString();
    } elseif (is_array($field)) {
      $field = reset($field);
      foreach ($field as $k => $v) {
        if (is_string($v)) {
          $field = $v;
          break;
        }
      }
    }
    return trim($field);
  }

}