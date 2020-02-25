<?php

namespace Drupal\Tests\enum\Kernel;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Exception;

/**
 * Verifies that the field order in user account forms is compatible with
 * password managers of web browsers.
 *
 */
class EnumFormTest extends KernelTestBase {

  use EnumFormTrait;



  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'enum', 'field'];

  const FORM_CLASS = '\Drupal\enum\Form\EnumForm';

  /**
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @group __active
   */
  public function testEnumEditForm() {
    $this->installConfig(['enum']);
    \Drupal::service('router.builder')->rebuild();
    $values = $this->getEnumData();
    $form = $this->buildEnumForm('add', $values);
    $this->assertFormDefault($form);

  }


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
  protected function getFormMap() {
    return [
      'id' => ['id'],
      'label' => ['label'],
      'type' => ['type'],
      'items' => ['id' => ['id'], 'label' => ['label'], 'value' => ['value']],
      'item_edit__selected_item' => ['item_edit', 'selected_item'],
      'item_edit__add_new' => ['item_edit', 'add_new'],
      // form
      'item_edit__form__id' => ['item_edit', 'form', 'id'],
      'item_edit__form__label' => ['item_edit', 'form', 'label'],
      'item_edit__form__value' => ['item_edit', 'form', 'value'],
      // actions
      'item_edit__actions__save' => ['item_edit', 'actions', 'save'],
      'item_edit__actions__delete' => ['item_edit', 'actions', 'delete'],
      'item_edit__actions__cancel' => ['item_edit', 'actions', 'cancel'],
    ];
  }

  protected function getEnumData() {
    return [
      'id' => 'id-enum',
      'label' => 'label enum',
      'type' => 'type_enum',
      'items' => [
        'one' => ['id' => 'one', 'label' => 'label one', 'value' => 'value one'],
        'two' => ['id' => 'two', 'label' => 'label two', 'value' => 'value two'],
        'three' => ['id' => 'three', 'label' => 'label three', 'value' => 'value three'],
      ],
    ];
  }

  /**
   * Asserts that the 'name' form element is directly before the 'pass' element.
   *
   * @param array $form
   *   A form array section that contains the user account form elements.
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
  protected function assertFormDefault(array $form) {

    $this->assertTrue(isset($form['id']), "isset field 'id'");
    $this->assertTrue(isset($form['label']), "isset field 'label'");
    $this->assertTrue(isset($form['type']), "isset field 'type'");
    $this->assertTrue((bool) NestedArray::getValue($form, ['item_edit']), "isset section 'item_edit'");
    $this->assertTrue((bool) NestedArray::getValue($form, [
      'item_edit',
      'selected_item',
    ]), "isset section 'item_edit->selected_item'");

    $this->assertTrue((bool) NestedArray::getValue($form, [
      'item_edit',
      'add_new',
    ]), "isset section 'item_edit->add_new'");

    // assertFalse

    $this->assertFalse((bool) NestedArray::getValue($form, [
      'item_edit',
      'form',
    ]),
      "isset section 'item_edit->form'");

    $this->assertFalse((bool) NestedArray::getValue($form, [
      'item_edit',
      'form',
      'id',
    ]),
      "isset section 'item_edit->form->id'");
    $this->assertFalse((bool) NestedArray::getValue($form, [
      'item_edit',
      'form',
      'label',
    ]),
      "isset section 'item_edit->form->label'");
    $this->assertFalse((bool) NestedArray::getValue($form, [
      'item_edit',
      'form',
      'value',
    ]),
      "isset section 'item_edit->form->value'");

  }

  /**
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Form\EnforcedResponseException
   * @throws \Drupal\Core\Form\FormAjaxException
   *
   * @group active
   */
  public function testSubmitSelectItem() {
    // Programmatically submit the form.
    $values = $this->getEnumData();
    $form_state = new FormState();
    $form_state->setValues($values);

    $form_builder = $this->container->get('form_builder');
    $form_builder->submitForm(self::FORM_CLASS, $form_state);
    $form = $form_state->getCompleteForm();
    $n = 0;
    $this->assertFormDefault($form);
  }

  /**
   * @param $operation
   * @param array $values
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function buildEnumForm($operation, $values = []) {
    // @see HtmlEntityFormController::getFormObject()
    $entity_type = 'enum';

    $entity = $this->createEntity($entity_type, $values);
    $form_obj=$this->getEntityTypeManager()->getFormObject($entity_type, $operation);
    $form_obj->setEntity($entity);
    return $this->getEntityFormBuilder()->getForm($entity, $operation);

  }

}
