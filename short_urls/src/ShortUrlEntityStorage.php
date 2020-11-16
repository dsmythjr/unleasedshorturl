<?php

namespace Drupal\short_urls;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class ShortUrlEntityStorage extends SqlContentEntityStorage implements ShortUrlEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ShortUrlEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {short_url_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {short_url_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

}
