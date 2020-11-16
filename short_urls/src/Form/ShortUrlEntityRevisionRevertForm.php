<?php

namespace Drupal\short_urls\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\short_urls\Entity\ShortUrlEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting a Short URL revision.
 *
 * @ingroup short_urls
 */
class ShortUrlEntityRevisionRevertForm extends ConfirmFormBase {

  /**
   * The Short URL revision.
   *
   * @var \Drupal\short_urls\Entity\ShortUrlEntityInterface
   */
  protected $revision;

  /**
   * The Short URL storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $shortUrlEntityStorage;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->shortUrlEntityStorage = $container->get('entity_type.manager')->getStorage('short_url_entity');
    $instance->dateFormatter = $container->get('date.formatter');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'short_url_entity_revision_revert_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to revert to the revision from %revision-date?', [
      '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.short_url_entity.version_history', ['short_url_entity' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Revert');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $short_url_entity_revision = NULL) {
    $this->revision = $this->ShortUrlEntityStorage->loadRevision($short_url_entity_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // The revision timestamp will be updated when the revision is saved. Keep
    // the original one for the confirmation message.
    $original_revision_timestamp = $this->revision->getRevisionCreationTime();

    $this->revision = $this->prepareRevertedRevision($this->revision, $form_state);
    $this->revision->revision_log = $this->t('Copy of the revision from %date.', [
      '%date' => $this->dateFormatter->format($original_revision_timestamp),
    ]);
    $this->revision->save();

    $this->logger('content')->notice('Short URL: reverted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Short URL %title has been reverted to the revision from %revision-date.', ['%title' => $this->revision->label(), '%revision-date' => $this->dateFormatter->format($original_revision_timestamp)]));
    $form_state->setRedirect(
      'entity.short_url_entity.version_history',
      ['short_url_entity' => $this->revision->id()]
    );
  }

  /**
   * Prepares a revision to be reverted.
   *
   * @param \Drupal\short_urls\Entity\ShortUrlEntityInterface $revision
   *   The revision to be reverted.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\short_urls\Entity\ShortUrlEntityInterface
   *   The prepared revision ready to be stored.
   */
  protected function prepareRevertedRevision(ShortUrlEntityInterface $revision, FormStateInterface $form_state) {
    $revision->setNewRevision();
    $revision->isDefaultRevision(TRUE);
    $revision->setRevisionCreationTime(REQUEST_TIME);

    return $revision;
  }

}
