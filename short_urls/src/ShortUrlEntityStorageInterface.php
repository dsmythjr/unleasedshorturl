<?php

namespace Drupal\short_urls;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\short_urls\Entity\ShortUrlEntityInterface;

/**
 * Defines the storage handler class for Short URL entities.
 *
 * This extends the base storage class, adding required special handling for
 * Short URL entities.
 *
 * @ingroup short_urls
 */
interface ShortUrlEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Short URL revision IDs for a specific Short URL.
   *
   * @param \Drupal\short_urls\Entity\ShortUrlEntityInterface $entity
   *   The Short URL entity.
   *
   * @return int[]
   *   Short URL revision IDs (in ascending order).
   */
  public function revisionIds(ShortUrlEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Short URL author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Short URL revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

}
