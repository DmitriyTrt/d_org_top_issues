<?php

namespace Drupal\d_org_top_issues\DrupalOrg;

interface ProjectProviderInterface {

  const PROJECT_NAME_MAX_LENGTH = 128;

  /**
   * @param string $name
   *
   * @return int|\Drupal\Component\Render\MarkupInterface
   */
  public function getProjectId(string $name);

}
