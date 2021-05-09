<?php

namespace Drupal\d_org_top_issues\DrupalOrg;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Hussainweb\DrupalApi\Request\Collection\NodeCollectionRequest;

/**
 * Provides cached list of top issues for a Drupal.org project.
 */
class TopIssuesProvider implements TopIssuesProviderInterface {

  const CACHE_TTL = 1800;

  const CACHE_ID_PREFIX = 'issues';

  const CACHE_TAG_PREFIX = 'd_org_top_issues';

  const CACHE_ID_SEPARATOR = ':';

  const LOCK_NAME_PREFIX = 'd_org_top_issues:';

  /**
   * The Drupal.org client factory.
   *
   * @var \Drupal\d_org_top_issues\DrupalOrg\ClientFactoryInterface
   */
  protected $clientFactory;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The lock backend.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * The time provider.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $timeProvider;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * The constructor.
   */
  public function __construct(
    ClientFactoryInterface $client_factory,
    CacheBackendInterface $cache,
    LockBackendInterface $lock,
    TimeInterface $time_provider,
    CacheTagsInvalidatorInterface $cache_tags_invalidator
  ) {
    $this->clientFactory = $client_factory;
    $this->cache = $cache;
    $this->lock = $lock;
    $this->timeProvider = $time_provider;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * {@inheritdoc}
   */
  public function getProjectCacheTag(int $project_id): string {
    return implode(static::CACHE_ID_SEPARATOR, [
      static::CACHE_TAG_PREFIX,
      $project_id,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getTopIssues(int $project_id, int $limit): ?array {
    $cid = implode(static::CACHE_ID_SEPARATOR, [
      static::CACHE_ID_PREFIX,
      $project_id,
      $limit,
    ]);

    $cache = $this->cache->get($cid, TRUE);
    $result = $cache->data ?? NULL;

    if (!$cache || !$cache->valid) {
      $lock_name = static::LOCK_NAME_PREFIX . $project_id;

      $lock_acquired = $this->lock->acquire($lock_name);
      if ($lock_acquired) {
        // Load issues and put store them to cache.
        $issues = $this->loadTopIssues($project_id, $limit);
        $expires = $this->timeProvider->getCurrentTime() + static::CACHE_TTL;
        $this->cache->set($cid, $issues, $expires);

        // We're done with the loading at this point and cache entry is ready
        // for other requests, so we release the lock.
        $this->lock->release($lock_name);

        // Invalidate all the tagged entries, since we updated the issues.
        $this->cacheTagsInvalidator->invalidateTags([
          $this->getProjectCacheTag($project_id),
        ]);

        $result = $issues;
      }
    }

    return $result;
  }

  /**
   * Loads top issues from Drupal.org.
   *
   * @param int $project_id
   *   The project ID on Drupal.org.
   * @param int $limit
   *   The limit.
   *
   * @return \Drupal\d_org_top_issues\DrupalOrg\IssueInterface[]
   *   The list of issues.
   */
  protected function loadTopIssues(int $project_id, int $limit): array {
    $client = $this->clientFactory->createClient();
    $request = new NodeCollectionRequest([
      'type' => 'project_issue',
      'field_project' => $project_id,
      'direction' => 'DESC',
      'limit' => $limit,

      'sort' => 'changed',
      // @todo Must be: 'sort' => 'comment_count',
      //   but drupal.org crashes on such requests.
      //   https://www.drupal.org/api-d7/node.json?type=project_issue&field_project=764442&sort=comment_count&direction=DESC&limit=5
    ]);
    $collection = $client->getEntity($request);

    $result = [];
    foreach ($collection as $node) {
      $result[] = Issue::fromNode($node);
    }
    return $result;
  }

}
