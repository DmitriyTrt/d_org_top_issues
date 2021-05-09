<?php

namespace Drupal\d_org_top_issues\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\d_org_top_issues\DrupalOrg\ProjectProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays a block with the most active issues for a project on Drupal.org.
 *
 * @Block(
 *   id = "d_org_top_issues",
 *   admin_label = @Translation("Top issues from Drupal.org"),
 * )
 */
class TopIssuesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  const PROJECT_NAME_SETTING = 'project_name';

  const PROJECT_ID_SETTING = 'project_id';

  const LIMIT_SETTING = 'limit';

  /**
   * The tag added to every block, but never invalidates.
   *
   * This tag is added in order to be added to the auto-placeholder conditions.
   *
   * @link https://www.drupal.org/docs/drupal-apis/render-api/auto-placeholdering
   */
  const CACHE_TAG = 'd_org_top_issues_list';

  const PROJECT_NAME_MAX_LENGTH = ProjectProviderInterface::PROJECT_NAME_MAX_LENGTH;

  /**
   * The project provider.
   *
   * @var \Drupal\d_org_top_issues\DrupalOrg\ProjectProviderInterface
   */
  protected $projectProvider;

  /**
   * The issue provider.
   *
   * @var \Drupal\d_org_top_issues\DrupalOrg\TopIssuesProviderInterface
   */
  protected $issueProvider;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);

    $instance->projectProvider = $container->get(
      'd_org_top_issues.project_provider'
    );
    $instance->issueProvider = $container->get(
      'd_org_top_issues.top_issues_provider'
    );

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      static::PROJECT_NAME_SETTING => NULL,
      static::LIMIT_SETTING => 10,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form[static::PROJECT_NAME_SETTING] = [
      '#type' => 'machine_name',
      '#maxlength' => static::PROJECT_NAME_MAX_LENGTH,
      '#title' => $this->t('Project machine name'),
      '#description' => $this->t('Machine name of the project on Drupal.org.'),
      '#default_value' => $this->configuration[static::PROJECT_NAME_SETTING],
      '#machine_name' => [
        'exists' => [
          static::class,
          'machineNameExists',
        ],

        // Stop attempts to build it from a label element on the form.
        'source' => [],
      ],
    ];

    $form[static::LIMIT_SETTING] = [
      '#type' => 'number',
      '#required' => TRUE,
      '#min' => 1,
      '#max' => 50,
      '#title' => $this->t('Maximum number of issues to display'),
      '#default_value' => $this->configuration[static::LIMIT_SETTING],
    ];

    return $form;
  }

  /**
   * The callback for the machine name existence check.
   */
  public static function machineNameExists(
    $value,
    array $element,
    FormStateInterface $form_state
  ): bool {
    // Project name is not unique, so we should never trigger an error.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $project_name = $form_state->getValue(static::PROJECT_NAME_SETTING);
    $project_id = $this->projectProvider->getProjectId($project_name);
    if (!is_int($project_id)) {
      $form_state->setErrorByName(static::PROJECT_NAME_SETTING, $project_id);
    }
    else {
      $form_state->set(static::PROJECT_ID_SETTING, $project_id);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration[static::PROJECT_NAME_SETTING] = $form_state
      ->getValue(static::PROJECT_NAME_SETTING);
    $this->configuration[static::PROJECT_ID_SETTING] = $form_state
      ->get(static::PROJECT_ID_SETTING);
    $this->configuration[static::LIMIT_SETTING] = (int) $form_state
      ->getValue(static::LIMIT_SETTING);

    // Warm-up the cache.
    $this->issueProvider->getTopIssues(
      $this->configuration[static::PROJECT_ID_SETTING],
      $this->configuration[static::LIMIT_SETTING]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#cache' => [
        'tags' => [static::CACHE_TAG],
      ],
      '#create_placeholder' => TRUE,
      '#lazy_builder' => [
        'd_org_top_issues.top_issues_builder:build',
        [
          $this->configuration[static::PROJECT_ID_SETTING],
          $this->configuration[static::LIMIT_SETTING],
        ]
      ],
    ];
  }

}
