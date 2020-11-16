<?php

namespace Drupal\short_urls\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\short_urls\Controller\Services\ShortUrlHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ShortUrlInfo extends ControllerBase {

  /**
   * The Short URL helper service
   *
   * @var \Drupal\short_urls\Controller\Services\ShortUrlHelper
   */
  protected $shortUrlHelper;

  public function __construct(ShortUrlHelper $short_url_helper) {
    $this->shortUrlHelper = $short_url_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('short_urls.short_url_helper')
    );
  }

  /**
   * Display the markup.
   *
   * @param null $short_url
   *
   * @return array
   */
  public function content($short_url = NULL) {


    // Outputting the short URL
    $data['short_url'] = \Drupal::request()->getSchemeAndHttpHost() . '/' .$short_url;

    // Load the Short URL Helper service
    $shortUrlHelper = $this->shortUrlHelper;

    // Load the entity if there is one
    $shortUrlEntity = $shortUrlHelper->loadEntityByShortUrl($short_url);

    // Get the number of times it has been redirected
    if($shortUrlEntity) {
      $data['redirect_count'] = $shortUrlHelper->getRedirectCount($shortUrlEntity->id());
    }

    $data['redirect_url'] = FALSE;
    if($shortUrlEntity) {
      // Add the URL value to the array so we can output it
      $data['redirect_url'] = $shortUrlEntity->get('destination_url')->getValue()[0]['uri'];
    }

    return [
      '#theme' => 'short_urls_info',
      '#data' => $data,
    ];
  }

}
