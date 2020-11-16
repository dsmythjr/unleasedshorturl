<?php

/**
 * @file
 * Contains short_url_entity.page.inc.
 *
 * Page callback for Short URL entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Short URL templates.
 *
 * Default template: short_url_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_short_url_entity(array &$variables) {
  // Fetch ShortUrlEntity Entity Object.
  $short_url_entity = $variables['elements']['#short_url_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
