<?php

namespace Drupal\chuck_norris;

use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides  class for Import service.
 *
 */
class Import  implements ImportInterface {

  use StringTranslationTrait;


  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * The logger factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs ImporterBase object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config service.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The messenger service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   */
  public function __construct( EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config, Messenger $messenger, LoggerChannelFactoryInterface $logger_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->config = $config;
    $this->messenger = $messenger;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function add($content, array &$context) {

    try {
      $entity = self::createEntity($content);
      $context['results'][] = $entity->getCid();
      $context['message'] = t('Imported Chuck norris : @id ', [
        '@id' => $entity->getCid(),
      ]);
    }
    catch (\Exception $e) {
      \Drupal::logger('chuck_norris')->error($e->getMessage());
    }
  }


  /**
   * {@inheritdoc}
   */
  public static function finished(bool $success, array $results, array $operations) {
    $message = '';

    if ($success) {
      $message = t('@count_added content added and @count_updated updated', [
        '@count_added' => isset($results['added']) ? count($results['added']) : 0,
        '@count_updated' => isset($results['updated']) ? count($results['updated']) : 0,
      ]);
    }

    \Drupal::messenger()->addMessage($message);
  }

  /**
   * {@inheritdoc}
   */
  public function process($data) {
    $process = [
      'title' =>  $this->t('Import Chuck norris'),
      'operations' => [],
      'init_message' =>  $this->t('Import Chuck norris is starting.'),
      'progress_message' =>  $this->t('Processed @current out of @total. Estimated time: @estimate.'),
      'error_message' =>  $this->t('The process has encountered an error.'),
    ];
    foreach ($data as $item) {
      $process['operations'][] = [
        ['\Drupal\chuck_norris\Import','add'],
        [$item],
      ];
    }
    $process['finished'] = ['\Drupal\chuck_norris\Import', 'finished'];
    batch_set($process);
  }
  /**
   * {}
   */
  public static function createCategory($name) {
    try {
      $termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
      $term =  $termStorage->getQuery()
        ->condition('vid','chuck_norris_category')
        ->condition('name',$name)
        ->accessCheck(False)
        ->execute();
      $tid = reset($term);
      if(!$tid) {
        $term = $termStorage->create([
          'name' => $name,
          'vid' => 'chuck_norris_category',
        ]);
        $term->save();
      }else {
        $term = $termStorage->load($tid);
      }
      return [
        'target_id' => $term->id(),
        'target_type' => 'taxonomy_term'
      ];
    } catch (\Exception $e) {
      \Drupal::logger('chuck_norris')->error($e->getMessage());
      return [];
    }
  }

  /**
   * {}
   */
  public static function createEntity($data) {
    try {
      $entityStorage = \Drupal::entityTypeManager()->getStorage('chuck_norris');
      $query = $entityStorage->getQuery()
        ->condition('cid',$data['id'])
        ->accessCheck(False)
        ->execute();
      $id =  reset($query);
      if(!$id) {
        $entity = $entityStorage->create([
          'cid'        => $data['id'],
        ]);
      }else{
        $entity = $entityStorage->load($id);
      }
      $entity->url->value = $data['url'];
      $entity->value->value = $data['value'];
      $entity->created = strtotime($data['created']);
      $entity->changed = strtotime($data['changed']);
      $entity->icon_url->value = $data['icon_url'];
      $entity->field_categories = [];
      foreach ($data['field_categories'] as $category) {
        if ($category) {
          $categoryArr = self::createCategory($category);
          $entity->field_categories[] = $categoryArr;
        }
      }
      $entity->save();
      return $entity;
    }
    catch (\Exception $e) {
      \Drupal::logger('chuck_norris')->error($e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function mappingData($data) {
    $config = $this->config->get('chuck_norris.settings');
    return [
      'url' => $data[$config->get('url')],
      'field_categories' => $data[$config->get('field_categories')],
      'changed' => $data[$config->get('changed')],
      'created' => $data[$config->get('url')],
      'cid' => $data[$config->get('cid')],
      'icon_url' => $data[$config->get('icon_url')],
      'value' => $data[$config->get('value')],
    ];
  }
}
