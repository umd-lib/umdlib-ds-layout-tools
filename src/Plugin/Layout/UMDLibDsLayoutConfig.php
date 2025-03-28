<?php

namespace Drupal\umdlib_ds_layout_tools\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Form\FormState;

class UMDLibDsLayoutConfig extends LayoutDefault implements PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'sidebar_region' => FALSE,
      'section_width' => FALSE,
      'num_rows' => 1,
      'row_1_cols' => 1,
      'row_2_cols' => 0,
      'row_3_cols' => 0,
      'row_4_cols' => 0,
      'row_5_cols' => 0,
      'row_6_cols' => 0,
      'row_7_cols' => 0,
      'row_8_cols' => 0,
      'row_9_cols' => 0,
      'row_10_cols' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    if ($form_state instanceof SubformStateInterface) {
        $form_state = $form_state->getCompleteFormState();
    }
    $configuration = $this->getConfiguration();
    $rows = [
      1 => $this->t('One'),
      2 => $this->t('Two'),
      3 => $this->t('Three'),
      4 => $this->t('Four'),
      5 => $this->t('Five'),
      6 => $this->t('Six'),
      7 => $this->t('Seven'),
      8 => $this->t('Eight'),
      9 => $this->t('Nine'),
      10 => $this->t('Ten'),
    ];
    $options = [
      1 => $this->t('One'),
      2 => $this->t('Two'),
      3 => $this->t('Three'),
      4 => $this->t('Four'),
    ];
    $sizes = [
      'default' => $this->t('Default'),
      'large' => $this->t('Large'),
      'small' => $this->t('Small')
    ];
    $form['sidebar_region'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add Sidebar Region'),
      '#default_value' => $configuration['sidebar_region'],
    ];
    $form['section_width'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Full Page Width'),
       '#default_value' => $configuration['section_width'],
    ];
    $form['num_rows'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of Rows'),
      '#options' => $rows,
      '#required' => TRUE,
      '#default_value' => $configuration['num_rows'],
    ];
    $is_open_required = TRUE;
    foreach ($rows as $key => $value) {
      $machine_name = 'row_' . $key;
      $friendly_name = $this->t('Row') . ' ' .  $value;
      $form[$machine_name] = [
        '#type' => 'details',
        '#open' => $is_open_required,
        '#title' => $friendly_name,
      ];
      $form[$machine_name][$machine_name . '_cols'] = [
        '#type' => 'select',
        '#title' => $friendly_name . ' ' . $this->t('Columns'),
        '#options' => $options,
        '#default_value' => !empty($configuration['column_config'][$machine_name]['cols']) ? 
                             $configuration['column_config'][$machine_name]['cols'] : 1,
        '#required' => $is_open_required,
      ];
      $form[$machine_name][$machine_name . '_horizontal'] = [
        '#type' => 'select',
        '#title' => $friendly_name . ' ' . $this->t('Horizontal Spacing'),
        '#options' => $sizes,
        '#default_value' => !empty($configuration['column_config'][$machine_name]['horizontal']) ? 
                             $configuration['column_config'][$machine_name]['horizontal'] : 'default',
        '#required' => $is_open_required,
      ];
      $form[$machine_name][$machine_name . '_vertical'] = [
        '#type' => 'select',
        '#title' => $friendly_name . ' ' . $this->t('Vertical Spacing'),
        '#options' => $sizes,
        '#default_value' => !empty($configuration['column_config'][$machine_name]['vertical']) ? 
                             $configuration['column_config'][$machine_name]['vertical'] : 'default',
        '#required' => $is_open_required,
      ];
      $is_open_required = FALSE;
    }
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // any additional form validation that is required
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $rows = [
      1 => $this->t('One'),
      2 => $this->t('Two'),
      3 => $this->t('Three'),
      4 => $this->t('Four'),
      5 => $this->t('Five'),
      6 => $this->t('Six'),
      7 => $this->t('Seven'),
      8 => $this->t('Eight'),
      9 => $this->t('Nine'),
      10 => $this->t('Ten'),
    ];
    $vals = $form_state->getValues();

    $this->configuration['sidebar_region'] = $form_state->getValue('sidebar_region');
    $this->configuration['section_width'] = $form_state->getValue('section_width');
    $num_rows = $form_state->getValue('num_rows');
    $this->configuration['num_rows'] = $num_rows;

    $column_info = [];
    $i = 1;
    foreach ($rows as $key => $value) {
      $machine_name = 'row_' . $key;
      $row_cols = 0;
      if ($i <= $num_rows) {
        $row_cols = $vals[$machine_name][$machine_name . '_cols'];
      }
      // $this->configuration[$machine_name . '_cols'] = $row_cols;
      $column_info[$machine_name]['cols'] = $row_cols;
      $column_info[$machine_name]['horizontal'] = $vals[$machine_name][$machine_name . '_horizontal'];
      $column_info[$machine_name]['vertical'] = $vals[$machine_name][$machine_name . '_vertical'];
      $i++;
    }
    $this->configuration['column_config'] = $column_info;
    \Drupal::logger('layouts')->info(json_encode($vals));
  }
}
