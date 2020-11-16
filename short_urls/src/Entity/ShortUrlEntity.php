<?php

namespace Drupal\short_urls\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\link\LinkItemInterface;

/**
 * Defines the Short URL entity.
 *
 * @ingroup short_urls
 *
 * @ContentEntityType(
 *   id = "short_url_entity",
 *   label = @Translation("Short URL"),
 *   handlers = {
 *     "storage" = "Drupal\short_urls\ShortUrlEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\short_urls\ShortUrlEntityListBuilder",
 *     "views_data" = "Drupal\short_urls\Entity\ShortUrlEntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\short_urls\Form\ShortUrlEntityForm",
 *       "add" = "Drupal\short_urls\Form\ShortUrlEntityForm",
 *       "edit" = "Drupal\short_urls\Form\ShortUrlEntityForm",
 *       "delete" = "Drupal\short_urls\Form\ShortUrlEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\short_urls\ShortUrlEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\short_urls\ShortUrlEntityAccessControlHandler",
 *   },
 *   base_table = "short_url_entity",
 *   revision_table = "short_url_entity_revision",
 *   revision_data_table = "short_url_entity_field_revision",
 *   translatable = FALSE,
 *   admin_permission = "administer short url entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/short-url/{short_url_entity}",
 *     "add-form" = "/admin/structure/short_url_entity/add",
 *     "edit-form" = "/admin/structure/short_url_entity/{short_url_entity}/edit",
 *     "delete-form" = "/admin/structure/short_url_entity/{short_url_entity}/delete",
 *     "version-history" = "/admin/structure/short_url_entity/{short_url_entity}/revisions",
 *     "revision" = "/admin/structure/short_url_entity/{short_url_entity}/revisions/{short_url_entity_revision}/view",
 *     "revision_revert" = "/admin/structure/short_url_entity/{short_url_entity}/revisions/{short_url_entity_revision}/revert",
 *     "revision_delete" = "/admin/structure/short_url_entity/{short_url_entity}/revisions/{short_url_entity_revision}/delete",
 *     "collection" = "/admin/structure/short_url_entity",
 *   },
 *   field_ui_base_route = "short_url_entity.settings"
 * )
 */
class ShortUrlEntity extends EditorialContentEntityBase implements ShortUrlEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // If no revision author has been set explicitly,
    // make the short_url_entity owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   *
   */
  public function getOwnerId() {
    return true;
  }


  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Short URL entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Short URL is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['destination_url'] = BaseFieldDefinition::create('link')
      ->setLabel(t('Destination'))
      ->setDescription(t('The destination URL.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'link_type' => LinkItemInterface::LINK_GENERIC,
        'title' => DRUPAL_DISABLED,
      ])
      ->setDisplayOptions('form', [
        'type' => 'link_default',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setRequired(TRUE);

    $fields['redirect_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Redirect Count'))
      ->setDescription(t('How many times this has been redirected.'))
      ->setDefaultValue(0)
      ->setSetting('unsigned', TRUE);

    return $fields;
  }

}
