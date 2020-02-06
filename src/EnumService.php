<?php


namespace Drupal\enum;


use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\enum\Entity\Enum;

class EnumService implements EnumServiceInterface {

  use StringTranslationTrait;

  /** @var EntityTypeManagerInterface */
  protected $entityTypeManager;

  /** @var EntityStorageInterface */
  protected $storage;

  protected $_types;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public function types() {
    if (empty($this->_types)) {
      $this->_types = $this->getTypes();
    }
    return $this->_types;
  }

  protected function getTypes() {
    return [
      'string' => $this->t('String'),
      'integer' => $this->t('Integer'),
      'float' => $this->t('Float'),
    ];
  }

  public function loadEnum($enum_id) {
    /** @var $enum Enum */

    $enum = $this->getStorage()->load($enum_id);

    return $enum;
  }

  protected function getStorage() {
    if (empty($this->storage)) {
      try {
        $this->storage = $this->entityTypeManager->getStorage('enum');
      } catch (InvalidPluginDefinitionException $e) {
      } catch (PluginNotFoundException $e) {
      }
    }
    return $this->storage;
  }

  public function getEnumLabels() {

    $enums = $this->getStorage()->loadMultiple();

    $options = [];
    foreach ($enums as $enum) {
      /** @var $enum Enum */
      $options[$enum->id()] = $enum->label();
    }
    return $options;
  }

  public function loadEnums($filters = []) {
    $ids = $this->getEntityIds($filters);
    return $this->getStorage()->loadMultiple($ids);
  }

  /**
   * Loads entity IDs using a pager sorted by the entity id.
   *
   * @return array
   *   An array of entity IDs.
   */
  protected function getEntityIds($filters = []) {
    $query = $this->getStorage()->getQuery()
      ->sort('id');
    foreach (array_filter($filters) as $field => $value) {

      $query->condition($field, $value);
    }
    return $query->execute();
  }
}
