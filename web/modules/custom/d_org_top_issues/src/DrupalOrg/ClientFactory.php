<?php

namespace Drupal\d_org_top_issues\DrupalOrg;

use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Hussainweb\DrupalApi\Client;

/**
 * Factory of Drupal.org clients.
 */
class ClientFactory implements ClientFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function createClient(): Client {
    // @todo It may be a good idea to make it configurable.
    $config = [
      'timeout' => 10,
    ];
    $adapter = GuzzleAdapter::createWithConfig($config);

    return new Client($adapter);
  }

}
