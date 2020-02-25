<?php

namespace Drupal\enum\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use function get_class;

/**
 * Class EnumFormHandler.
 */
class EnumFormHandler implements EnumFormHandlerInterface {

  /** @var  EnumFormStateInterface */
  protected $state;

  protected $ajax_wrapper_id;

  public function __construct(FormStateInterface $form_state) {
    $this->state = new EnumFormState($form_state);
  }

  public function getSubmitCallback() {
    return [$this, 'submit'];
  }

  public function getValidateCallback() {
    return [$this, 'validate'];
  }

  /**
   * 1. Взять edited item
   * 2. Положить его в массив items
   * 3. Очистить форму редактирования Item
   */
  public function save() {
    $item = $this->state->getEditedItem();
    $this->state->setItem($item);
    $this->state->setEditedItem(NULL);

  }

  /**
   * 1.Взять selected item
   * 2.Положить его в форму редактирования
   */
  public function select() {
    $item = $this->state->getSelectedItem();
    $this->state->setEditedItem($item);

  }

  /**
   * 1.Очистить форму релактирования
   * 2.Очистить selected item
   */
  public function cancel() {
    $this->state->setEditedItem(NULL);
    $this->state->setSelectedItem(NULL);

  }


  public function validate() {
  }

  /**
   * @return EnumFormStateInterface
   */
  public function getState() {
    return $this->state;
  }

  public function getAjaxOptions($trigger_as = NULL) {
    $trigger = is_null($trigger_as) ? [] : ['trigger_as' => ['name' => $trigger_as]];
    return [
        'wrapper' => $this->getAjaxWrapperId(),
        'callback' => EnumFormState::AJAX_CALLBACK,
      ] + $trigger;
  }

  public function getAjaxWrapperId() {
    if (empty($this->ajax_wrapper_id)) {
      $this->ajax_wrapper_id='enum-edit-form';
//      $this->ajax_wrapper_id = Html::getId('enums-item-form-wrapper');
    }
    return $this->ajax_wrapper_id;
  }


}
