<?php

namespace Drupal\d_org_top_issues\DrupalOrg;

use Hussainweb\DrupalApi\Entity\Node;

/**
 * Represents an issue on Drupal.org.
 */
class Issue implements IssueInterface {

  /**
   * The issue URL.
   *
   * @var string
   */
  protected $url;

  /**
   * The title.
   *
   * @var string
   */
  protected $title;

  /**
   * The timestamp when the issue was last updated.
   *
   * @var int
   */
  protected $lastUpdated;

  /**
   * The number of comments on the issue.
   *
   * @var int
   */
  protected $commentCount;

  /**
   * {@inheritdoc}
   */
  public static function fromNode(Node $node) {
    $instance = new static();

    $instance->url = (string) $node->url;
    $instance->title = (string) $node->title;
    $instance->lastUpdated = (int) $node->changed;
    $instance->commentCount = (int) $node->comment_count;

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl(): string {
    return $this->url;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return $this->title;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastUpdated(): int {
    return $this->lastUpdated;
  }

  /**
   * {@inheritdoc}
   */
  public function getCommentCount(): int {
    return $this->commentCount;
  }

}
