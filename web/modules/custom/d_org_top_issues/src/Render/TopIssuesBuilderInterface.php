<?php

namespace Drupal\d_org_top_issues\Render;

/**
 * Provides lazy builder for the list of top issues.
 */
interface TopIssuesBuilderInterface {

  /**
   * Builds the list of most active issues from Drupal.org.
   *
   * @param int $project_id
   *   The project ID on Drupal.org.
   * @param int $limit
   *   The limit.
   *
   * @return array
   *   The render array.
   */
  public function build(int $project_id, int $limit): array;

}
