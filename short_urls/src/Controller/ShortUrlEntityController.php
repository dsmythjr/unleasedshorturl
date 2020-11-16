<?php

namespace Drupal\short_urls\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\short_urls\Entity\ShortUrlEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ShortUrlEntityController.
 *
 *  Returns responses for Short URL routes.
 */
class ShortUrlEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Short URL revision.
   *
   * @param int $short_url_entity_revision
   *   The Short URL revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($short_url_entity_revision) {
    $short_url_entity = $this->entityTypeManager()->getStorage('short_url_entity')
      ->loadRevision($short_url_entity_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('short_url_entity');

    return $view_builder->view($short_url_entity);
  }

  /**
   * Page title callback for a Short URL revision.
   *
   * @param int $short_url_entity_revision
   *   The Short URL revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($short_url_entity_revision) {
    $short_url_entity = $this->entityTypeManager()->getStorage('short_url_entity')
      ->loadRevision($short_url_entity_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $short_url_entity->label(),
      '%date' => $this->dateFormatter->format($short_url_entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Short URL.
   *
   * @param \Drupal\short_urls\Entity\ShortUrlEntityInterface $short_url_entity
   *   A Short URL object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ShortUrlEntityInterface $short_url_entity) {
    $account = $this->currentUser();
    $short_url_entity_storage = $this->entityTypeManager()->getStorage('short_url_entity');

    $build['#title'] = $this->t('Revisions for %title', ['%title' => $short_url_entity->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all short url revisions") || $account->hasPermission('administer short url entities')));
    $delete_permission = (($account->hasPermission("delete all short url revisions") || $account->hasPermission('administer short url entities')));

    $rows = [];

    $vids = $short_url_entity_storage->revisionIds($short_url_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\short_urls\ShortUrlEntityInterface $revision */
      $revision = $short_url_entity_storage->loadRevision($vid);
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $short_url_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.short_url_entity.revision', [
            'short_url_entity' => $short_url_entity->id(),
            'short_url_entity_revision' => $vid,
          ]));
        }
        else {
          $link = $short_url_entity->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => Url::fromRoute('entity.short_url_entity.revision_revert', [
                'short_url_entity' => $short_url_entity->id(),
                'short_url_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.short_url_entity.revision_delete', [
                'short_url_entity' => $short_url_entity->id(),
                'short_url_entity_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
    }

    $build['short_url_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
