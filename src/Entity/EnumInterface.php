<?php

namespace Drupal\enum\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Enum entities.
 */
interface EnumInterface extends ConfigEntityInterface {

  public function setType($type);

  public function getType();

  public function setItems($items);

  public function getItems();

  public function getEnumLabels();
}
