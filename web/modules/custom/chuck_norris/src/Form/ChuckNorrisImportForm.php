<?php

namespace Drupal\chuck_norris\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\FormBase;
use Drupal\chuck_norris\Import;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Url;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the chuck norris entity import forms.
 */
class ChuckNorrisImportForm extends FormBase {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $httpClient;

  /**
   * Drupal logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $drupalLogger;

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Messenger.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Import.
   *
   * @var \Drupal\chuck_norris\Import
   */
  protected $import;

  /**
   * Constructs a ApiSettingsForm object.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP client.
   */
  public function __construct(ClientInterface $httpClient, LoggerChannelFactoryInterface $logger, Import $import, ConfigFactory $configFactory, Messenger $messenger) {
    $this->httpClient = $httpClient;
    $this->drupalLogger = $logger;
    $this->import = $import;
    $this->configFactory = $configFactory;
    $this->messenger = $messenger;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('logger.factory'),
      $container->get('chuck_norris.import'),
      $container->get('config.factory'),
      $container->get('messenger')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'chuck_norris_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $options = [
      'endpoint' => $this->t('Single import'),
      'endpoint_multiple' => $this->t('Multiple import'),
    ];
    $form['importer']['option'] = [
      '#type' => 'select',
      '#title' => $this->t('Which data to import.'),
      '#options' => $options,
    ];
    $form['importer']['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => 'Proceed',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->get('chuck_norris.settings');
    if (!$config->get('endpoint_multiple')||!$config->get('endpoint')) {
      $link = Url::fromRoute('chuck_norris.settings_form');
      $form_state->setErrorByName('option', $this->t('The Config is not valid. An absolute url endpoint has to be provided. Please back to  <a href="@link">config page</a>',['@link' => $link]));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $option = $form_state->getValue('option');
    try {
      $config = $this->configFactory->get('chuck_norris.settings');
      $endpoint = $config->get($option);
      $request = $this->httpClient->request('GET', $endpoint);
      if ($request->getStatusCode() > 200) {
        $form_state->setErrorByName('option', $this->t('Cannot request to endpoint server.'));
      }
      $data = $request->getBody()->getContents();
      $json_decoded = Json::decode($data);
      switch ($option) {
        case 'endpoint':
          $data = $this->import->mappingData($json_decoded);
          $entity = $this->import::createEntity($data);
          $this->messenger->addMessage('Imported Chuck norris ID: ' . $entity->id());
          break;
        default:
          if($json_decoded['total']) {
            $data = [];
            foreach ($json_decoded['result'] as $item) {
              $data[] = $this->import->mappingData($item);
            }
            $total = $json_decoded['total'];
            $this->import->process($data);
            $this->messenger->addMessage('Imported ' . $total . ' entities!');
          }
      }
    } catch (\Exception $e) {
      $this->drupalLogger->get('chuck_norris')->error($e->getMessage());
    }
    $form_state->setRebuild(TRUE);
  }



}
