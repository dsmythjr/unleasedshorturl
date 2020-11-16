<?php

namespace Drupal\short_urls\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Provides an interface for defining Short URL entities.
 *
 * @ingroup short_urls
 */
interface ShortUrlEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Short URL name.
   *
   * @return string
   *   Name of the Short URL.
   */
  public function getName();

  /**
   * Sets the Short URL name.
   *
   * @param string $name
   *   The Short URL name.
   *
   * @return \Drupal\short_urls\Entity\ShortUrlEntityInterface
   *   The called Short URL entity.
   */
  public function setName($name);

  /**
   * Gets the Short URL creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Short URL.
   */
  public function getCreatedTime();

  /**
   * Sets the Short URL creation timestamp.
   *
   * @param int $timestamp
   *   The Short URL creation timestamp.
   *
   * @return \Drupal\short_urls\Entity\ShortUrlEntityInterface
   *   The called Short URL entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Short URL revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Short URL revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\short_urls\Entity\ShortUrlEntityInterface
   *   The called Short URL entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Short URL revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Short URL revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\short_urls\Entity\ShortUrlEntityInterface
   *   The called Short URL entity.
   */
  public function setRevisionUserId($uid);

}
