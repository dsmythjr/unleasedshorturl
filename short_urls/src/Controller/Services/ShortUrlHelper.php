<?php

namespace Drupal\short_urls\Controller\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\path_alias\Entity\PathAlias;
//use Drupal\pathauto\AliasManagerInterface;
use Drupal\short_urls\Entity\ShortUrlEntity;
use Drupal\Component\Utility\Random;


class ShortUrlHelper {

  /**
   * The Entity type manager
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Short URLs entity
   *
   * @var \Drupal\short_urls\Entity\ShortUrlEntity
   */
  protected $shortUrlEntity;


  /**
   * ShortUrlHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
  }


  public function createShortUrlEntity($destination, $custom = FALSE) {

    $shortUrl = $custom ? $custom : $this->generateShortUrl();

    $entity = ShortUrlEntity::create([
      'name' => $shortUrl,
      'type' => 'short_url_entity',
      'created' => time(),
      'changed' => time(),
      'status' => 1,
      'destination_url' => [
        'uri' => $destination
      ],
      'path' => [
        'alias' => '/' . $shortUrl,
      ],
    ]);

    $entity->save();

    \Drupal::service('path.alias_storage')->save("/short-url/" . $entity->id(), "/" . $shortUrl, "en");

    return $shortUrl;

  }

  public function loadEntityByShortUrl($shortUrl) {

    return current(\Drupal::entityTypeManager()->getStorage('short_url_entity')
      ->loadByProperties(['name' => $shortUrl])
    );

  }

  public function loadEntityByFullUrl($fullUrl) {

    return $this->entityTypeManager->getStorage('short_url_entity')->getQuery()
      ->condition('destination_url__uri',$fullUrl,'=')
      ->condition('status',1,'=')
      ->execute();

  }

  public function loadEntityById($id) {
    return current(\Drupal::entityTypeManager()->getStorage('short_url_entity')
      ->loadByProperties(['id' => $id])
    );
  }

  public function createPath($id, $shortCode) {
    $path_alias = PathAlias::create([
      'path' => '/admin/structure/short_url_entity/' . $id,
      'alias' => '/' . $shortCode,
      'langcode' => 'en',
    ]);

    $path_alias->save();
  }

  public function generateShortUrl() {
    $random = new Random();
    return $random->name(9,TRUE);
  }

  public function getRedirectCount($shortUrlEntityId) {
    $entity = $this->loadEntityById($shortUrlEntityId);
    return $entity->get('redirect_count')->getValue()[0]['value'];
  }

  public function addToRedirect($shortUrlEntityId) {
    $entity = $this->loadEntityById($shortUrlEntityId);
    $currentCount = $entity->get('redirect_count')->getValue()[0]['value'];
    $entity->set('redirect_count', $currentCount + 1);
    $entity->save();
    return TRUE;

  }



}
