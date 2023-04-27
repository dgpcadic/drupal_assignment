<?php

namespace Drupal\chuck_norris\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the chuck norris entity edit forms.
 */
class ChuckNorrisForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $entity = $this->getEntity();

    $message_arguments = ['%label' => $entity->toLink()->toString()];
    $logger_arguments = [
      '%label' => $entity->getCid(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New chuck norris %label has been created.', $message_arguments));
        $this->logger('chuck_norris')->notice('Created new chuck norris %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The chuck norris %label has been updated.', $message_arguments));
        $this->logger('chuck_norris')->notice('Updated chuck norris %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.chuck_norris.canonical', ['chuck_norris' => $entity->id()]);

    return $result;
  }

}
