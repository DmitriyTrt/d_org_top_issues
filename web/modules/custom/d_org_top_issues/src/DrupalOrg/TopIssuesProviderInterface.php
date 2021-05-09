<?php

namespace Drupal\d_org_top_issues\DrupalOrg;

/**
 * Provides cached list of top issues for a Drupal.org project.
 */
interface TopIssuesProviderInterface {

  /**
   * Loads top issues from Drupal.org.
   *
   * @param int $project_id
   *   The project ID on Drupal.org.
   * @param int $limit
   *   The limit.
   *
   * @return \Drupal\d_org_top_issues\DrupalOrg\IssueInterface[]|null
   *   The list of issues or NULL in case they're still loading from Drupal.org.
   */
  public function getTopIssues(int $project_id, int $limit): ?array;

  /**
   * Returns the cache tag to be used on render arrays displaying top issues.
   *
   * @param int $project_id
   *   The project ID on Drupal.org.
   *
   * @return string
   *   The cache tag.
   */
  public function getProjectCacheTag(int $project_id): string;

}
