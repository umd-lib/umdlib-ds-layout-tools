<?php

namespace Drupal\umdlib_ds_layout_tools\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Template\Attribute;

class UMDLibDsLayoutConfig extends LayoutDefault implements PluginFormInterface, ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Construct new Drupal\umdlib_ds_layout_tools\Plugin\Layout\UMDLibDsLayoutConfig.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // @phpstan-ignore new.static
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'sidebar_region' => FALSE,
      'section_width' => FALSE,
      'top_margin' => FALSE,
      'section_vertical_spacing' => 'default',
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
      'use_search' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
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
      'small' => $this->t('Small'),
      'none' => $this->t('None'),
    ];

    $form['#attached']['library'][] = 'umdlib_ds_layout_tools/webform.forked';

    $form['sidebar_region'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable sidebar region'),
      '#default_value' => !empty($configuration['sidebar_region']) ? $configuration['sidebar_region'] : FALSE,
    ];

    $form['section_width'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use full page width'),
      '#states' => [
        'disabled' => [
          ':input[name="layout_settings[sidebar_region]"]' => ['checked' => TRUE],
        ]
      ],
      '#default_value' => !empty($configuration['section_width']) ? $configuration['section_width'] : FALSE,
    ];

    $form['top_margin'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add top margin (no hero)'),
      '#default_value' => !empty($configuration['top_margin']) ? $configuration['top_margin'] : FALSE,
    ];

    $form['section_vertical_spacing'] = [
      '#type' => 'select',
      '#title' => $this->t('Spacing between Sections'),
      '#options' => $sizes,
      '#required' => TRUE,
      '#default_value' => !empty($configuration['section_vertical_spacing']) ? $configuration['section_vertical_spacing'] : 'default',
    ];

    $form['num_rows'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of Rows'),
      '#options' => $rows,
      '#required' => TRUE,
      '#default_value' => !empty($configuration['num_rows']) ? $configuration['num_rows'] : 1,
    ];

    $is_open_required = TRUE;
    foreach ($rows as $key => $value) {
      $machine_name = 'row_' . $key;
      $friendly_name = $this->t('Row') . ' ' .  $value;
      $form[$machine_name] = [
        '#type' => 'details',
        '#open' => $is_open_required,
        '#title' => $friendly_name,
        '#states' => [
          'visible' => [
            ':input[name="layout_settings[num_rows]"]' => ['value' => ['greater_equal' => $key ]],
          ],
        ],
      ];
      $form[$machine_name]['card_group'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Arrange as card group'),
        '#default_value' => !empty($configuration['column_config'][$machine_name]['card_group']) ? 
                 $configuration['column_config'][$machine_name]['card_group'] : FALSE,
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
        '#title' => $this->t('Column Spacing'),
        '#options' => array_filter($sizes, function ($key) {
        return in_array($key, ['default', 'none']); // Only show 'default' and 'large'
      }, ARRAY_FILTER_USE_KEY),
        '#default_value' => !empty($configuration['column_config'][$machine_name]['horizontal']) ? 
                             $configuration['column_config'][$machine_name]['horizontal'] : 'default',
        '#required' => $is_open_required,
      ];
      $form[$machine_name][$machine_name . '_vertical'] = [
        '#type' => 'select',
        '#title' => $this->t('Row Spacing'),
        '#options' => $sizes,
        '#default_value' => !empty($configuration['column_config'][$machine_name]['vertical']) ? 
                             $configuration['column_config'][$machine_name]['vertical'] : 'default',
        '#required' => $is_open_required,
      ];
      $is_open_required = FALSE;
    }

    if (\Drupal::moduleHandler()->moduleExists('search_web_components_layout')) {

      $endpoints = $this->entityTypeManager->getStorage('search_api_endpoint')->loadMultiple();

      $endpointOptions = [];
      foreach ($endpoints as $endpoint) {
        $endpointOptions[$endpoint->id()] = $endpoint->label();
      }
      $form['use_search'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Use this layout for search?'),
        '#default_value' => !empty($configuration['use_search']) ? $configuration['use_search'] : FALSE,
      ];

      $form['search_components'] = [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $this->t('Search Components Configuration'),
        '#states' => [
          'visible' => [
            ':input[name="layout_settings[use_search]"]' => ['checked' => TRUE],
          ]
        ],
      ];

      $form['search_components']['endpoint'] = [
        '#title' => 'Search Endpoint',
        '#type' => 'select',
        '#required' => TRUE,
        '#options' => $endpointOptions,
        '#default_value' => $this->configuration['endpoint'] ?? array_key_first($endpointOptions),
      ];

      $form['search_components']['additionalParams'] = [
        '#type' => 'textfield',
        '#maxlength' => NULL,
        '#title' => $this->t('Additional search params'),
        '#default_value' => $this->configuration['additionalParams'],
        '#description' => $this->t("A valid search query parameter string starting with '?'. These parameters will be added to all searches but will not be added to the page url."),
      ];

      $form['search_components']['defaultPerPage'] = [
        '#type' => 'number',
        '#title' => $this->t('Default results per page'),
        '#default_value' => $this->configuration['defaultPerPage'],
        '#description' => $this->t('The default number of results to show per page.'),
      ];

      $form['search_components']['defaultResultDisplay'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Default result display'),
        '#default_value' => $this->configuration['defaultResultDisplay'],
      ];

      $form['search_components']['updateUrl'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Update the page url with parameters from the search'),
        '#default_value' => $this->configuration['updateUrl'],
      ];
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
    $section_vertical_spacing = $form_state->getValue('section_vertical_spacing');
    $this->configuration['section_vertical_spacing'] = $section_vertical_spacing;

    $sidebar_region = $form_state->getValue('sidebar_region'); 

    $this->configuration['sidebar_region'] = $sidebar_region;
    if (!empty($sidebar_region)) {
      $this->configuration['section_width'] = FALSE;
    } else {
      $this->configuration['section_width'] = $form_state->getValue('section_width');
    }
    $num_rows = $form_state->getValue('num_rows');

    $top_margin = $form_state->getValue('top_margin');
    $this->configuration['top_margin'] = $top_margin;

    $this->configuration['num_rows'] = $num_rows;
    
    $vals = $form_state->getValues();

    $column_info = [];
    $i = 1;
    foreach ($rows as $key => $value) {
      $machine_name = 'row_' . $key;
      $row_cols = 0;
      if ($i <= $num_rows) {
        $row_cols = $vals[$machine_name][$machine_name . '_cols'];
      }
      // $this->configuration[$machine_name . '_cols'] = $row_cols;
      $column_info[$machine_name]['card_group'] = !empty($vals[$machine_name]['card_group']) ? (bool) $vals[$machine_name]['card_group'] : FALSE;
      $column_info[$machine_name]['cols'] = $row_cols;
      $column_info[$machine_name]['horizontal'] = $vals[$machine_name][$machine_name . '_horizontal'];
      $column_info[$machine_name]['vertical'] = $vals[$machine_name][$machine_name . '_vertical'];
      $i++;
    }
    $this->configuration['column_config'] = $column_info;

    $use_search = $form_state->getValue('use_search');

    $this->configuration['use_search'] = $use_search;
    
    if ($use_search && !empty($vals['search_components'])) {
      $s_components = $vals['search_components'];
      $this->configuration['endpoint'] = !empty($s_components['endpoint']) ? $s_components['endpoint'] : NULL;
      $this->configuration['defaultPerPage'] = !empty($s_components['defaultPerPage']) ? $s_components['defaultPerPage'] : NULL;
      $this->configuration['defaultResultDisplay'] = !empty($s_components['defaultResultDisplay']) ? $s_components['defaultResultDisplay'] : NULL;
      $this->configuration['updateUrl'] = !empty($s_components['updateUrl']) ? $s_components['updateUrl'] : NULL;
      $this->configuration['additionalParams'] = !empty($s_components['additionalParams']) ? $s_components['additionalParams'] : NULL;
    }

    \Drupal::logger('layouts')->info(json_encode($vals));
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);

    if (!$this->configuration['use_search']) {
      return $build;
    }

    $endpointId = $this->configuration['endpoint'];

    if ($endpointId) {
      $endpoint = $this->entityTypeManager->getStorage('search_api_endpoint')->load($this->configuration['endpoint']);
      if ($endpoint) {
        $url = $endpoint->getBaseUrl()->toString();
      }
      else {
        $this->getLogger('search_web_components_layout')->error('Failed to load Decoupled Search Endpoint @id', ['@id' => $this->configuration['endpoint']]);
      }
    }
    else {
      $this->getLogger('search_web_components_layout')->error('No endpoint provided for search_web_component_layout One Column layout.');
    }

    $build['#settings']['search_root_attributes'] = new Attribute([
      'class' => ['endpoint-' . $this->configuration['endpoint']],
      'url' => $url,
      'defaultPerPage' => $this->configuration['defaultPerPage'],
      'defaultResultDisplay' => $this->configuration['defaultResultDisplay'],
    ]);

    if (!$this->configuration['updateUrl']) {
      $build['#settings']['search_root_attributes']['noPageUrlUpdate'] = !$this->configuration['updateUrl'];
    }

    if ($this->configuration['additionalParams']) {
      $build['#settings']['search_root_attributes']['additionalParams'] = !$this->configuration['additionalParams'];
    }
    return $build;
  } 
  
}
