<?php

namespace Drupal\d_org_top_issues\DrupalOrg;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Hussainweb\DrupalApi\Entity\Collection\EntityCollection;
use Hussainweb\DrupalApi\Entity\Node;
use Hussainweb\DrupalApi\Request\Collection\NodeCollectionRequest;

/**
 * Provides cached information about a Drupal.org project.
 */
class ProjectProvider implements ProjectProviderInterface {

  use StringTranslationTrait;

  const CACHE_PREFIX = 'project:';

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
   * The constructor.
   */
  public function __construct(
    CacheBackendInterface $cache,
    ClientFactoryInterface $clientFactory
  ) {
    $this->cache = $cache;
    $this->clientFactory = $clientFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function getProjectId(string $name) {
    $cid = static::CACHE_PREFIX . $name;
    $cache = $this->cache->get($cid);
    if ($cache) {
      return $cache->data;
    }

    $project_id = $this->loadProjectId($name);
    $this->cache->set($cid, $project_id);
    return $project_id;
  }

  /**
   * Validates the project name and loads its ID from Drupal.org.
   *
   * @param string $name
   *   The project machine name.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|int
   *   The project ID or the text of a validation error.
   */
  protected function loadProjectId(string $name) {
    $name = trim($name);
    if ($name === '') {
      return $this->t('The project name must not be empty.');
    }

    if (mb_strlen($name) > static::PROJECT_NAME_MAX_LENGTH) {
      return $this->t('The project name is too long, it must be not longer than @count characters.', [
        '@count' => static::PROJECT_NAME_MAX_LENGTH,
      ]);
    }

    $client = $this->clientFactory->createClient();
    $request = new NodeCollectionRequest([
      'field_project_machine_name' => $name,
      'limit' => 1,
    ]);
    $collection = $client->getEntity($request);
    if (!$collection instanceof EntityCollection) {
      throw new \UnexpectedValueException(sprintf(
        'Expected %s, got %s',
        EntityCollection::class,
        is_object($collection) ? get_class($collection) : gettype($collection)
      ));
    }

    if (!$collection->count()) {
      return $this->t("The project doesn't exist on Drupal.org.");
    }

    foreach ($collection as $item) {
      if (!$item instanceof Node) {
        throw new \UnexpectedValueException(sprintf(
          'Expected %s, got %s',
          Node::class,
          is_object($item) ? get_class($item) : gettype($item)
        ));
      }

      if (empty($item->field_project_has_issue_queue)) {
        return $this->t("The project doesn't have an issue queue.");
      }

      $id = $item->getId();
      if (empty($id) || !is_numeric($id)) {
        throw new \UnexpectedValueException(sprintf(
          'The project ID is not valid: %s',
          $id
        ));
      }

      break;
    }
    return (int) $id;
  }

}
