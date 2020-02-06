<?php

namespace Drupal\enum\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\enum\EnumServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function array_filter;
use function get_class;

/**
 * Class EnumForm.
 */
class EnumForm extends EntityForm {

  protected $enumService;

  public function __construct(EnumServiceInterface $enumService) {
    $this->enumService = $enumService;
  }


  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('enum.service')
    );
  }

  protected function getTrigger(FormStateInterface $form_state) {
    if ($triggering_element = $form_state->getTriggeringElement()) {
      $trigger['name'] = end($triggering_element['#array_parents']);
      switch ($trigger['name']) {
        case 'delete_item':
          $trigger['delta'] = $triggering_element['#array_parents'][1];
          break;
        case 'add_item':
          break;
      }
      return $trigger;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $enum = $this->entity;
    $items = $enum->getItems();
    if ($trigger = $this->getTrigger($form_state)) {
      switch ($trigger['name']) {
        case 'button_add':
          $new_item = $form_state->getValue(['add_item', 'item']);
          $items[] = $new_item;
          break;

        case 'delete_item':
          unset($items[$trigger['delta']]);
          break;
      }
    }
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $enum->label(),
      '#description' => $this->t("Label for the Enum."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $enum->id(),
      '#machine_name' => [
        'exists' => '\Drupal\enum\Entity\Enum::load',
      ],
      '#disabled' => !$enum->isNew(),
    ];


    $form['type'] = [
      '#type' => 'select',
      '#default_value' => $enum->getType(),
      '#options' => $this->enumService->types(),
    ];

    $form['items'] = [
      '#type' => 'value',
      '#value' => $items,
    ];
    $items_id = Html::getId('enum-items');
    $form['table'] = [
      '#type' => 'details',
      '#title' => $this->t('List'),
      '#open' => TRUE,
      '#prefix' => '<div id="' . $items_id . '">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];
    $form['table'] = $this->buildTable($items_id, $items);

    $form['add_item'] = [
        '#type' => 'details',
        '#title' => $this->t('New'),
        '#open' => TRUE,
        '#tree' => TRUE,
      ] + $this->buildAddItem($items_id);
    //    $element['delete_item'] = [
    //      '#type' => 'button',
    //      '#name' => "delete-" . $item['value'],
    //      '#value' => $this->t('Delete'),
    //      '#ajax' => [
    //        'wrapper' => $items_id,
    //        'callback' => '::ajaxItems',
    //      ],
    //
    //    ];
    return $form;
  }

  protected function buildAddItem($items_id) {
    $item = ['label' => '', 'value' => ''];
    $element['item'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];
    $element['item']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $item['label'],
    ];
    $element['item']['value'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Value'),
      '#default_value' => $item['value'],
      '#machine_name' => [
        'exists' => get_class($this) . '::existsItem',
      ],
    ];
    $element['button_add'] = [
      '#type' => 'button',
      '#value' => $this->t('Add'),
      '#name' => 'button-add-item',
      '#ajax' => [
        'wrapper' => $items_id,
        'callback' => '::ajaxItems',
      ],
    ];
    return $element;
  }

  protected function buildTable($items_id, $items) {
    $header = [
      'value' => $this->t('Value'),
      'label' => $this->t('Label'),
    ];

    $table = [
      '#type' => 'tableselect',
      '#title' => $this->t('Items'),
      '#header' => $header,
      '#options' => $items,
      '#empty' => $this->t('Items is empty'),
      '#prefix' => '<div id="' . $items_id . '">',
      '#suffix' => '</div>',
    ];
    return $table;
  }

  public function ajaxItems(array &$form, FormStateInterface $form_state) {
    return $form['table'];
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($trigger = $this->getTrigger($form_state)) {
      if ($form_state->hasAnyErrors()) {
        $form_state->clearErrors();
      }
    }
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
  public static function existsItem($value, $element, FormStateInterface $form_state) {
    /*
    * @TODO
    */
    $items = $form_state->getValue('items');
    return (boolean) array_filter($items, function ($item) use ($value) {
      return $item['value'] === $value;
    });
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $n = 0;
  }
}
