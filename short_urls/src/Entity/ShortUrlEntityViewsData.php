<?php

namespace Drupal\short_urls\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Short URL entities.
 */
class ShortUrlEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
