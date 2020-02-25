<?php

namespace Drupal\enum\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\enum\EnumServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function array_shift;
use function func_get_args;
use function implode;

/**
 * Class EnumForm.
 */
class EnumForm extends EntityForm {

  protected $enumService;

  /** @var EnumFormHandlerInterface */
  protected $_handler;

  const ITEM_FORM_MODE_OPEN = 'form_open';

  const ITEM_FORM_MODE_CLOSE = 'form_close';

  const ITEM_SAVE = 'item_save';

  const ITEM_DELETE = 'item_delete';

  public function __construct(EnumServiceInterface $enumService) {
    $this->enumService = $enumService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('enum.service')
    );
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\enum\Form\EnumFormHandlerInterface
   */
  protected function getHandler() {
    return $this->_handler;
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    if (!$form_state->hasTemporaryValue('form_handler')) {
      $form_state->setTemporaryValue('form_handler', new EnumFormHandler($form_state));
    }
    $this->_handler = $form_state->getTemporaryValue('form_handler');
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * -- MainForm
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
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $handler = $this->getHandler();
    $form['#attributes']['novalidate'] = 'novalidate';
    $form['#attributes']['id'] = $handler->getAjaxWrapperId();
    $form['#attached']['library'] = ['enum/enum'];

    //    $item_form_wrapper_id = Html::getId('enums-item-form-wrapper');

    //    $selected_item_id = $form_state->getValue('selected_item', 'empty');

    $enum = $this->entity;
    if ($form_state->hasValue('items')) {
      $items = $form_state->getValue('items');
    }
    else {
      $items = $enum->getItems();
    }


    $form += $this->buildFormMain($enum);

    $form[EnumFormState::ITEMS] = ['#type' => 'value', '#default_value' => $items];

    $form[EnumFormState::ITEM_EDIT] = [
      '#type' => 'container',
      //      '#id' => $handler->getAjaxWrapperId(),
    ];


    $form[EnumFormState::ITEM_EDIT]['selected_item'] = $this->buildTable($items);

    $is_add_new = $handler->getState()->isAddNew();
    if ($handler->getState()->hasSelectedItem() || $is_add_new) {
      $item = $handler->getState()->getSelectedItem();
      $form[EnumFormState::ITEM_EDIT]['form']=$this->buildItemForm($item);
    }
    else {
      $form['item_edit'][EnumFormState::ADD_NEW] = [
        '#type' => 'radio',
        '#title' => $this->t('Add new'),
        '#return_value' => 1,
        '#ajax' => $handler->getAjaxOptions(EnumFormState::ITEM_SELECT_BUTTON),
      ];
    }

    $form[EnumFormState::ITEM_SELECT_BUTTON] = [
      '#attributes' => ['style' => 'display:none'],
      '#type' => 'submit',
      '#value' => 'Select',
      '#name' => EnumFormState::ITEM_SELECT_BUTTON,
      '#submit' => ['::ajaxSubmitItemSelect'],
      '#ajax' => $handler->getAjaxOptions(),
    ];


    return $form;
  }

  protected function buildFormMain($enum) {

    $element['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $enum->label(),
      '#description' => $this->t("Label for the Enum."),
      '#required' => TRUE,
    ];

    $element['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $enum->id(),
      '#machine_name' => [
        'exists' => '\Drupal\enum\Entity\Enum::load',
      ],
      '#disabled' => !$enum->isNew(),
    ];


    $element['type'] = [
      '#type' => 'select',
      '#default_value' => $enum->getType(),
      '#options' => $this->enumService->types(),
    ];
    return $element;

  }

  protected function buildItemActions($is_add_new, $item = NULL) {

    $handler = $this->getHandler();

    $actions['save'] = [
      '#type' => 'submit',
      '#value' => $is_add_new ? $this->t('Add') : $this->t('Save'),
      '#name' => 'item_action[save]',
      '#submit' => ['::ajaxSubmitItemSave'],
      '#ajax' => $handler->getAjaxOptions(),
    ];

    if (!$is_add_new) {
      $actions['delete'] = [
        '#type' => 'submit',
        '#value' => $this->t('Delete'),
        '#name' => 'item_action[delete]',
        '#submit' => ['::ajaxSubmitItemDelete'],
        '#ajax' => $handler->getAjaxOptions(),
      ];
    }

    $actions['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#name' => 'item_action[cancel]',
      '#submit' => ['::ajaxSubmitItemCancel'],
      '#ajax' => $handler->getAjaxOptions(),
    ];
    return $actions;
  }

  protected function buildItemForm($item = NULL) {
    $handler = $this->getHandler();
    $add_new = $handler->getState()->isAddNew();


    $element = [
      '#type' => 'details',
      '#open' => TRUE,
      '#tree' => TRUE,
    ];

    if ($add_new) {
      $item = ['label' => '', 'value' => ''];
    }

    $element['item'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];


    $element['item']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $item['label'],
    ];

    $element['item']['id'] = [
      '#type' => 'value',
      '#value' => $item['id'],
    ];

    $element['item']['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value'),
      '#default_value' => $item['value'],
      '#validate' => ['::validateItemValue'],
    ];


    $element['actions'] = [
        '#type' => 'actions',
      ] + $this->buildItemActions($add_new, $item);


    if ($add_new) {
      $element['#title'] = $this->t('New');

    }
    else {
      $element['#title'] = $this->t('Edit @name', ['@name' => $item['label']]);

    }

    return $element;
  }

  protected function createName() {
    $parents = [];
    array_walk_recursive(func_get_args(), function ($a) use (&$parents) {
      $parents[] = $a;
    });

    $name = array_shift($parents);
    if (!empty($parents)) {
      $name .= "[" . implode($parents, '][') . "]";
    }
    return $name;
  }

  protected function setValueAndInput(FormStateInterface $form_state, array $parents, $value) {
    $input = $form_state->getUserInput();
    NestedArray::setValue($input, $parents, $value);
    $form_state->setUserInput($input);

    $form_state->setValue($parents, $value);

  }

  public function ajaxValidateItemFormOpen(FormStateInterface $form_state) {

    if ($form_state->hasAnyErrors()) {
      $form_state->clearErrors();
    }

  }

  public function ajaxSubmitItemSelect(array $form, FormStateInterface $form_state) {
    $this->getHandler()->select();
    $form_state->setRebuild();
  }

  public function ajaxSubmitItemCancel(array $form, FormStateInterface $form_state) {
    $this->getHandler()->cancel();
    $form_state->setRebuild();
    return $form;
  }

  public function ajaxSubmitItemSave(array $form, FormStateInterface $form_state) {
    $this->getHandler()->save();
    $form_state->setRebuild();
    return $form;
  }

  protected function clearItemForm(FormStateInterface $form_state, $items) {
    $item = ['value' => '', 'label' => ''];
    NestedArray::setValue($input, ['item_form', 'item'], $item);
    NestedArray::setValue($input, ['items'], $items);

    $form_state->setValue(['item_form', 'item'], $item);
    $form_state->setValue(['items'], $items);
  }

  protected function updateItem($action, FormStateInterface $form_state) {
    $input = &$form_state->getUserInput();
    $item_data = NestedArray::getValue($input, ['item_form', 'item']);

    $items = $form_state->getValue('items');
    switch ($action) {
      case 'add':
        $items[$item_data['id']] = $item_data;
        break;
      case 'update':
        $items[$item_data['id']] = $item_data;
        break;
      case 'delete':
        unset($items[$item_data['id']]);

        break;
    }
    $this->clearItemForm($form_state, $items);
  }

  public function ajaxSubmitItemDelete(array $form, FormStateInterface $form_state) {

    $form_state->setRebuild();

    $this->updateItem('delete', $form_state);
    return $form;
  }

  protected function buildTable($items) {
    $handler = $this->getHandler();

    $header = [
      'value' => $this->t('Value'),
      'label' => $this->t('Label'),
    ];
    $options['test'] = ['value' => 'Test', 'label' => 'Test Label', '#attributes' => ['style' => 'display:none']];
    $options += array_map(function ($item) {
      $item['#attributes'] = ['class' => ['enum-items-table-row', 'enum-item']];
      return $item;
    }, $items);


    $table = [
      '#type' => 'tableselect',
      '#title' => $this->t('Items'),
      '#id' => 'selected-item',
      '#header' => $header,
      '#options' => $options,
      '#multiple' => FALSE,
      '#default_value' => $handler->getState()->getSelectedItem('value'),
      '#empty' => $this->t('Items is empty'),
      '#ajax' => $handler->getAjaxOptions(EnumFormState::ITEM_SELECT_BUTTON),

      '#prefix' => '<div class="enum-items-table">',
      '#suffix' => '</div>',
    ];

    return $table;
  }

  protected function clearValues(&$element) {
    foreach (Element::children($element) as $key) {
      if (isset($element[$key]['#default_value'])) {
        $element[$key]['#value'] = $element[$key]['#default_value'];
        $this->clearValues($element[$key]);
      }
    }
  }

  public function ajaxItems(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->ajaxValidateItemFormOpen($form_state);
    //    NestedArray::setValue($form_state->getUserInput(),['item_form','item']);
    //    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $enum = $this->entity;
    $status = $enum->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Enum.', [
          '%label' => $enum->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Enum.', [
          '%label' => $enum->label(),
        ]));
    }
    $form_state->setRedirectUrl($enum->toUrl('collection'));
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }

  /**
   * Element specific validator for the Add language button.
   */
  public function validateItemValue($form, FormStateInterface $form_state) {
    $value = $form_state->getValue(['item_form', 'item', 'value']);
    $n = 0;
  }

}
