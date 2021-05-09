<?php

namespace Drupal\d_org_top_issues\DrupalOrg;

use Hussainweb\DrupalApi\Client;

/**
 * Factory of Drupal.org clients.
 */
interface ClientFactoryInterface {

  /**
   * Creates a client instance.
   */
  public function createClient(): Client;

}
