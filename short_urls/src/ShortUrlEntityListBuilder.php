<?php

namespace Drupal\short_urls;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Short URL entities.
 *
 * @ingroup short_urls
 */
class ShortUrlEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Short URL ID');
    $header['name'] = $this->t('Name');
    $header['destination'] = $this->t('Destination');
    $header['created'] = $this->t('Created');
    $header['redirect_count'] = $this->t('Redirect Count');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\short_urls\Entity\ShortUrlEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'short_urls.info_view',
      ['short_url' => $entity->label()]
    );
    $row['destination'] = !empty($entity->get('destination_url')->getValue()) ? $entity->get('destination_url')->getValue()[0]['uri'] : '';
    $row['created'] = date('M d, Y',$entity->getCreatedTime());
    $row['redirect_count'] = $entity->get('redirect_count')->getValue()[0]['value'];
    return $row + parent::buildRow($entity);
  }

}
