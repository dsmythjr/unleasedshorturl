<?php

namespace Drupal\short_urls;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Short URL entity.
 *
 * @see \Drupal\short_urls\Entity\ShortUrlEntity.
 */
class ShortUrlEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\short_urls\Entity\ShortUrlEntityInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished short url entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published short url entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit short url entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete short url entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add short url entities');
  }


}
