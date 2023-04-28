<?php

namespace Drupal\chuck_norris\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\chuck_norris\ChuckNorrisInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the chuck norris entity class.
 *
 * @ContentEntityType(
 *   id = "chuck_norris",
 *   label = @Translation("Chuck norris"),
 *   label_collection = @Translation("Chuck norriss"),
 *   label_singular = @Translation("chuck norris"),
 *   label_plural = @Translation("chuck norriss"),
 *   label_count = @PluralTranslation(
 *     singular = "@count chuck norriss",
 *     plural = "@count chuck norriss",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\chuck_norris\ChuckNorrisListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\chuck_norris\ChuckNorrisAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\chuck_norris\Form\ChuckNorrisForm",
 *       "edit" = "Drupal\chuck_norris\Form\ChuckNorrisForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "chuck_norris",
 *   admin_permission = "administer chuck norris",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/chuck-norris",
 *     "add-form" = "/chuck-norris/add",
 *     "canonical" = "/chuck-norris/{chuck_norris}",
 *     "edit-form" = "/chuck-norris/{chuck_norris}/edit",
 *     "delete-form" = "/chuck-norris/{chuck_norris}/delete",
 *   },
 *   field_ui_base_route = "entity.chuck_norris.settings",
 * )
 */
class ChuckNorris extends ContentEntityBase implements ChuckNorrisInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCid() {
    return $this->get('cid')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the chuck norris was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the chuck norris was last edited.'));

    $fields['cid'] = BaseFieldDefinition::create('string')
      ->setLabel(t('cId'))
      ->setDescription(t('The Id of the Chuck norris entity.'))
      ->setSettings([
        'max_length' => 250,
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

    $fields['icon_url'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Icon url'))
      ->setDescription(t('The Icon url of the Chuck norris entity.'))
      ->setSettings([
        'max_length' => 250,
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

    $fields['url'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Url'))
      ->setDescription(t('The  Url of the Chuck norris entity.'))
      ->setSettings([
        'max_length' => 250,
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

    $fields['value'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Value'))
      ->setDescription(t('The Value of the Chuck norris entity.'))
      ->setDefaultValue('')
      ->setRequired(FALSE)
      ->setDisplayOptions('view', [
        'label' => 'visible',
        'type' => 'basic_string',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 5,
        'settings' => ['rows' => 4],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
