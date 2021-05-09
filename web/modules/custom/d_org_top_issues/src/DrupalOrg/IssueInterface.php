<?php

namespace Drupal\d_org_top_issues\DrupalOrg;

use Hussainweb\DrupalApi\Entity\Node;

/**
 * Represents an issue on Drupal.org.
 */
interface IssueInterface {

  /**
   * Constructs an issue from the project issue node retrieved from Drupal.org.
   *
   * @param \Hussainweb\DrupalApi\Entity\Node $node
   *   The project issue node.
   *
   * @return static
   */
  public static function fromNode(Node $node);

  /**
   * Returns the issue URL.
   */
  public function getUrl(): string;

  /**
   * Returns the issue title.
   */
  public function getTitle(): string;

  /**
   * Returns the issue last updated timestamp.
   */
  public function getLastUpdated(): int;

  /**
   * Returns the number of comments on the issue.
   */
  public function getCommentCount(): int;

}
