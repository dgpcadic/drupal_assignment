<?php

namespace Drupal\chuck_norris\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Url;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Custom Api Import settings for this site.
 */
class ApiSettingsForm extends ConfigFormBase {
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
  protected LoggerChannelFactoryInterface $drupal_logger;

  /**
   * Constructs a ApiSettingsForm object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(ClientInterface $http_client, LoggerChannelFactoryInterface $logger) {
    $this->httpClient = $http_client;
    $this->drupal_logger = $logger;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('logger.factory')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'chuck_norris_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['chuck_norris.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Endpoint Single import'),
      '#default_value' => $this->config('chuck_norris.settings')->get('endpoint'),
    ];
    $form['endpoint_multiple'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Endpoint Multiple import'),
      '#default_value' => $this->config('chuck_norris.settings')->get('endpoint_multiple'),
    ];
    $options = [
      'categories' => 'categories',
      'created_at' => 'created_at',
      'icon_url' => 'icon_url',
      'id' => 'id',
      'url' => 'url',
      'value' => 'value',
      'updated_at' => 'updated_at',
    ];
    $form['mapping'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Mapping'),
    ];

    $form['mapping']['cid'] = [
      '#type' => 'select',
      '#title' => $this->t('cId'),
      '#options' => $options,
      '#default_value' => $this->config('chuck_norris.settings')->get('cid'),
    ];
    $form['mapping']['icon_url'] = [
      '#type' => 'select',
      '#title' => $this->t('icon_url'),
      '#options' => $options,
      '#default_value' => $this->config('chuck_norris.settings')->get('icon_url'),
    ];
    $form['mapping']['url'] = [
      '#type' => 'select',
      '#title' => $this->t('url'),
      '#options' => $options,
      '#default_value' => $this->config('chuck_norris.settings')->get('url'),
    ];
    $form['mapping']['created'] = [
      '#type' => 'select',
      '#title' => $this->t('created'),
      '#options' => $options,
      '#default_value' => $this->config('chuck_norris.settings')->get('created'),
    ];
    $form['mapping']['changed'] = [
      '#type' => 'select',
      '#title' => $this->t('changed'),
      '#options' => $options,
      '#default_value' => $this->config('chuck_norris.settings')->get('changed'),
    ];
    $form['mapping']['value'] = [
      '#type' => 'select',
      '#title' => $this->t('value'),
      '#options' => $options,
      '#default_value' => $this->config('chuck_norris.settings')->get('value'),
    ];
    $form['mapping']['field_categories'] = [
      '#type' => 'select',
      '#title' => $this->t('field_categories'),
      '#options' => $options,
      '#default_value' => $this->config('chuck_norris.settings')->get('field_categories'),
    ];
    $form['actions']['import'] = [
      '#type' => 'link',
      '#weight' => 100,
      '#title' => $this->t('Go to Import'),
      '#attributes' => [
        'class' => 'button button--secondary'
      ],
      '#url' => Url::fromRoute('chuck_norris.import_form'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    if (!UrlHelper::isValid($form_state->getValue('endpoint'), TRUE)) {
      $form_state->setErrorByName('endpoint', $this->t('The url is not valid. An absolute url has to be provided.'));
    }
    if (!UrlHelper::isValid($form_state->getValue('endpoint_multiple'), TRUE)) {
      $form_state->setErrorByName('endpoint_multiple', $this->t('The url is not valid. An absolute url has to be provided.'));
    }
    try {
      $request = $this->httpClient->request('GET', $form_state->getValue('endpoint_multiple'));
      if ($request->getStatusCode() > 200) {
        $form_state->setErrorByName('endpoint', $this->t('Cannot request to endpoint server.'));
      }
      $request = $this->httpClient->request('GET', $form_state->getValue('endpoint_multiple'));
      if ($request->getStatusCode() > 200) {
        $form_state->setErrorByName('endpoint_multiple', $this->t('Cannot request to endpoint server.'));
      }
    } catch (\Exception $e) {
      $this->drupal_logger->get('chuck_norris')->error($e->getMessage());
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('chuck_norris.settings')
      ->set('endpoint', $form_state->getValue('endpoint'))
      ->set('endpoint_multiple', $form_state->getValue('endpoint_multiple'))
      ->set('field_categories', $form_state->getValue('field_categories'))
      ->set('changed', $form_state->getValue('changed'))
      ->set('created', $form_state->getValue('created'))
      ->set('url', $form_state->getValue('url'))
      ->set('cid', $form_state->getValue('cid'))
      ->set('icon_url', $form_state->getValue('icon_url'))
      ->set('value', $form_state->getValue('value'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
