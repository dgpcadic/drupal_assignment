<?php

namespace Drupal\chuck_norris;

/**
 * Importer manager interface.
 */
interface ImportInterface {

  /**
   * Add content.
   *
   * @param mixed $content
   *   CSV content.
   * @param array $context
   *   The batch context array.
   *
   * @return array
   *   Prepared data.
   */
  public static function add($content, array &$context);

  /**
   * Batch finish handler.
   *
   * @param bool $success
   *   A boolean indicating whether the batch has completed successfully.
   * @param array $results
   *   The value set in $context['results'] by callback_batch_operation().
   * @param array $operations
   *   Contains the operations that remained unprocessed.
   *
   * @return array
   *   Prepared data.
   */
  public static function finished(bool $success, array $results, array $operations);

  /**
   * Run batch operations.
   */
  public function process($data);

}
