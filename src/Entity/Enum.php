<?php

namespace Drupal\enum\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use function is_null;

/**
 * Defines the Enum entity.
 *
 * @ConfigEntityType(
 *   id = "enum",
 *   label = @Translation("Enum"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\enum\EnumListBuilder",
 *     "form" = {
 *       "add" = "Drupal\enum\Form\EnumForm",
 *       "edit" = "Drupal\enum\Form\EnumForm",
 *       "delete" = "Drupal\enum\Form\EnumDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\enum\EnumHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "enum",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/enum/{enum}",
 *     "add-form" = "/admin/structure/enum/add",
 *     "edit-form" = "/admin/structure/enum/{enum}/edit",
 *     "delete-form" = "/admin/structure/enum/{enum}/delete",
 *     "collection" = "/admin/structure/enum"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "type",
 *     "group",
 *     "items"
 *   },
 *   lookup_keys = {
 *     "label",
 *     "type",
 *     "group",
 *   }
 * )
 */
class Enum extends ConfigEntityBase implements EnumInterface {

  /**
   * The Enum ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Enum label.
   *
   * @var string
   */
  protected $label;

  protected $type;

  protected $group;

  protected $items = [];

  public function getType() {
    return $this->type;
  }


  public function setType($type) {
    $this->type = $type;
  }

  public function getItems() {
    return is_null($this->items) ? [] : $this->items;
  }

  public function getEnumLabels() {
    $options = [];
    foreach ($this->items as $item) {
      $options[$item['value']] = $item['label'];
    }
    return $options;
  }


  public function setItems($items) {
    $this->items = $items;
  }

  /**
   * @return mixed
   */
  public function getGroup() {
    return $this->group;
  }

  /**
   * @param mixed $group
   */
  public function setGroup($group): void {
    $this->group = $group;
  }

}
