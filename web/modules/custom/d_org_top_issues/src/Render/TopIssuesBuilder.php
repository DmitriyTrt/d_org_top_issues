<?php

namespace Drupal\d_org_top_issues\Render;

use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\d_org_top_issues\DrupalOrg\TopIssuesProvider;
use Drupal\d_org_top_issues\DrupalOrg\TopIssuesProviderInterface;

/**
 * Provides lazy builder for the list of top issues.
 */
class TopIssuesBuilder implements TopIssuesBuilderInterface, TrustedCallbackInterface {

  const CACHE_TTL = TopIssuesProvider::CACHE_TTL;

  /**
   * The top issues provider.
   *
   * @var \Drupal\d_org_top_issues\DrupalOrg\TopIssuesProviderInterface
   */
  protected $topIssuesProvider;

  /**
   * The constructor.
   */
  public function __construct(TopIssuesProviderInterface $topIssuesProvider) {
    $this->topIssuesProvider = $topIssuesProvider;
  }

  /**
   * {@inheritdoc}
   */
  public function build(int $project_id, int $limit): array {
    $issues = $this->topIssuesProvider->getTopIssues($project_id, $limit);
    $tag = $this->topIssuesProvider->getProjectCacheTag($project_id);
    return [
      '#cache' => [
        'tags' => [$tag],
        'max-age' => static::CACHE_TTL,
      ],
      '#theme' => 'd_org_top_issues',
      '#issues' => $issues,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks(): array {
    return ['build'];
  }

}
