<?php

namespace Drupal\short_urls\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Short URL revision.
 *
 * @ingroup short_urls
 */
class ShortUrlEntityRevisionDeleteForm extends ConfirmFormBase {

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
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->shortUrlEntityStorage = $container->get('entity_type.manager')->getStorage('short_url_entity');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'short_url_entity_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
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
    return $this->t('Delete');
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
    $this->ShortUrlEntityStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Short URL: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Revision from %revision-date of Short URL %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.short_url_entity.canonical',
       ['short_url_entity' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {short_url_entity_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.short_url_entity.version_history',
         ['short_url_entity' => $this->revision->id()]
      );
    }
  }

}
