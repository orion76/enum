<?php

namespace Drupal\enum\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\enum\Entity\EnumInterface;
use function is_null;

/**
 * Class EnumFormHandler.
 */
class EnumFormState implements EnumFormStateInterface {

  /**
   * 1. [id]
   * 2. [label]
   * 3. [type]
   *
   * --
   * 4.  [items]
   *
   * 5.  [item_edit]
   * 6.  [item_edit][selected_item]
   * 7.  [item_edit][add_new]
   *
   * 8.  [item_edit][form]
   * 9.  [item_edit][form][id]
   * 10. [item_edit][form][label]
   * 11.  [item_edit][form][value]
   *
   * 12. [item_edit][actions]
   * 13. [item_edit][actions][save]
   * 14. [item_edit][actions][delete]
   * 15. [item_edit][actions][cancel]
   */

  const ITEMS = 'items';

  const ITEM_EDIT = 'item_edit';

  const SELECTED_ITEM = 'selected_item';

  const ADD_NEW = 'add_new';

  const ITEM_FORM = 'form';

  const ITEM_FORM__ID = 'id';

  const ITEM_FORM__LABEL = 'label';

  const ITEM_FORM__VALUE = 'value';

  const ITEM_ACTIONS = 'actions';

  const ITEM_ACTION__SAVE = 'save';

  const ITEM_ACTION__DELETE = 'delete';

  const ITEM_ACTION__CANCEL = 'cancel';

  const ITEM_SELECT_BUTTON = 'select_item_button';


  const AJAX_CALLBACK = '::ajaxItems';

  /** @var  FormStateInterface */
  protected $form_state;

  public function __construct(FormStateInterface $form_state) {
    $this->form_state = $form_state;
  }

  protected function updateValues($parents, $value) {
    $input = $this->form_state->getUserInput();
    NestedArray::setValue($input, $parents, $value);
    $this->form_state->setUserInput($input);

    $this->form_state->setValue($parents, $value);
  }

  public function getAddNew() {
    return $this->form_state->getValue('add_new', FALSE);
  }

  public function getEntity(): EnumInterface {
    return $this->form_state->getFormObject()->entity;
  }

  public function getEmptyItem() {
    return ['value' => '', 'label' => '', 'id' => 'empty'];
  }

  function getItem($id) {
    return $this->form_state->getValue(['items', $id]);
  }

  function setItem($item) {
    $this->updateValues(['item_form', 'item'], $item);

  }


  function getItems() {
    return $this->form_state->getValue(['items']);
  }

  function setItems($items) {
    $this->updateValues(['items'], $items);
  }

  function addItem($item) {
    $items = $this->getItems();
    $items[$item['value']] = $item;
    $this->setItems($items);
  }


  function updateItem($item) {
    $items = $this->getItems();
    $items[$item['old_value']] = $item;
    $this->setItems($items);
  }


  function deleteItem($item) {
    $items = $this->getItems();
    unset($items[$item['old_value']]);
    $this->setItems($items);
  }


  function setRebuild() {
    $this->form_state->setRebuild();
  }

  public function setSelectedItem($id) {
    $this->form_state->setValue(self::SELECTED_ITEM, $id);
  }

  public function hasSelectedItem() {
    $selected = $this->form_state->getValue(self::SELECTED_ITEM);
    return !empty($selected);
  }

  public function isAddNew() {
    $add_new = NestedArray::getValue($this->form_state->getUserInput(), [self::ADD_NEW]);
    return !empty($add_new);
  }

  public function getSelectedItem($field = NULL) {
    $selected_id = $this->form_state->getValue(self::SELECTED_ITEM);
    $item = $this->getItem($selected_id);

    if (!is_null($field)) {
      $return = isset($item[$field]) ? $item[$field] : NULL;
    }
    else {
      $return = $item;
    }
    return $return;
  }

  public function getEditedItem() {
    return $this->form_state->getValue([self::ITEM_EDIT, self::ITEM_FORM]);
  }

  public function setEditedItem($item) {
    return $this->form_state->setValue([self::ITEM_EDIT, self::ITEM_FORM], $item);
  }

  function setUserInput($parents, $value) {
    // TODO: Implement setUserInput() method.
  }

  function setValue($parents, $value) {
    // TODO: Implement setValue() method.
  }

}
