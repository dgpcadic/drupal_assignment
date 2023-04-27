<?php

namespace Drupal\chuck_norris;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a chuck norris entity type.
 */
interface ChuckNorrisInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
