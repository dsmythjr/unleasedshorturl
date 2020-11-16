<?php
/**
 * @file
 *
 */
namespace Drupal\short_urls\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\short_urls\Controller\Services\ShortUrlHelper;
use Drupal\Core\Url;



class CreateShortUrl extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'create_short_url_form';
  }

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
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {


    $form['description_text'] = [
      '#markup' => $this->t('<p>Use this tool to create a short URL to your favorite website.</p>'),
    ];

    $form['destination_url'] = [
      '#type' => 'url',
      '#title' => t('URL'),
      '#required' => TRUE,
      '#description' => t('Enter the URL you want to create a short URL to.'),
    ];

    $form['choose_your_own'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Check this box to choose your own URL'),
    ];

    $form['custom_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enter your own code.'),
      '#description' => t('Must be between 5-9 characters. Letters and numbers only.'),
      '#length' => 9,
      '#max_length' => 9,
      '#states' => [
        'visible' => [
          ':input[name="choose_your_own"]' => [
            'checked' => TRUE
          ],
        ],
        'required' => [
          ':input[name="choose_your_own"]' => [
            'checked' => TRUE
          ],
        ],
      ],
    ];


    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check to see if it's valid
    if(!UrlHelper::isValid($form_state->getValue('destination_url'), $absolute = FALSE)) {
      $form_state->setErrorByName('invalid_url', $this->t('The URL is invalid. Please try again.'));
    }

    // Check to see if the URL exists
    if(!$this->doesUrlExist($form_state->getValue('destination_url'))) {
      $form_state->setErrorByName('url_does_not_exists', $this->t('The URL does not exist'));
    }

    if(!empty($form_state->getValue('custom_code'))) {
      $input = $form_state->getValue('custom_code');
      if (!preg_match('/^[A-Za-z0-9 ]{5,9}$/', $input)) {
        $form_state->setErrorByName('code_length', $this->t('Your code must be between 5 and 9 characters, no spaces and no special characters.'));
      }
    }

    // Check to see if the Short URL is already in the system by code
    if(!empty($form_state->getValue('custom_code')) &&
      $this->shortUrlHelper->loadEntityByShortUrl($form_state->getValue('custom_code'))) {
      $form_state->setErrorByName('code_exists', $this->t('The short code already exists'));
    }




    // Check if the URL itself is already redirected
    if($destination = $form_state->getValue('destination_url')) {
      if($entity = $this->shortUrlHelper->loadEntityByFullUrl($destination)) {
        $shortUrlEntity = $this->shortUrlHelper->loadEntityById(array_key_first($entity));
        $shortCode = $shortUrlEntity->label();
        $form_state->setErrorByName('redirect_exists',
          $this->t('The url already exists as a redirect using /' . $shortCode));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $shortUrlService = $this->shortUrlHelper;


    $custom = !empty($form_state->getValue('custom_code')) ? $form_state->getValue('custom_code') : FALSE;

    $shortUrl = $shortUrlService->createShortUrlEntity($form_state->getValue('destination_url'),$custom);

    $short_url_entity = $shortUrlService->loadEntityByShortUrl($shortUrl);

    $this->messenger()
      ->addStatus($this->t('Your shortened URL for @destination_url has been created. Short URL is @short_url',
        ['@destination_url' => $form_state->getValue('destination_url'),'@short_url' => $shortUrl]));

    $this->messenger()
      ->addStatus($this->t('To use your URL, copy this link @host/@short_url',
        ['@host' => \Drupal::request()->getSchemeAndHttpHost(),'@short_url' => $shortUrl]));
    $url = Url::fromRoute('short_urls.info_view', ['short_url' => $short_url_entity->label()]);
    return $form_state->setRedirectUrl($url);
  }

  /**
   * Simple check to see if URL exists
   */
  public function doesUrlExist($url) {
    $headers = @get_headers($url);
    if(!$headers || $headers[0] == 'HTTP/1.1 404 Not Found') {
      return false;
    }
    else {
      return true;
    }
  }
}
