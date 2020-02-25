<?php


namespace Drupal\Tests\enum\Kernel;


use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormState;
use function is_null;

trait EnumFormTrait {

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface */
  protected $_entityTypeManager;

  /** @var EntityFormBuilderInterface    */
  protected $_entityFormBuilder;

  /** @var FormBuilderInterface    */
  protected $_formBuilder;

  /**
   * @var \Drupal\Core\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * @throws \Exception
   */
  public function getEntityTypeManager() {
    if (is_null($this->_entityTypeManager)) {
      $this->_entityTypeManager = $this->container->get('entity_type.manager');
    }
    return $this->_entityTypeManager;
  }

  public function getEntityFormBuilder() {
    if (is_null($this->_entityFormBuilder)) {
      $this->_entityFormBuilder = $this->container->get('entity.form_builder');
    }
    return $this->_entityFormBuilder;
  }

  public function getFormBuilder() {
    if (is_null($this->_formBuilder)) {
      $this->_formBuilder = $this->container->get('form_builder');
    }
    return $this->_formBuilder;
  }

  public function getFormObject() {

  }

  /**
   * @param $entity_type
   * @param $values
   *
   * @return mixed
   * @throws \Exception
   */
  public function createEntity($entity_type, $values) {
    return $this->getEntityTypeManager()->getStorage($entity_type)->create($values);
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param string $operation
   * @param array $form_state_additions
   *
   * @return array|mixed|\Symfony\Component\HttpFoundation\Response
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\Form\EnforcedResponseException
   * @throws \Drupal\Core\Form\FormAjaxException
   */
  public function getForm(EntityInterface $entity, $operation = 'default', array $form_state_additions = []) {
    $form_object = $this->getEntityTypeManager()->getFormObject($entity->getEntityTypeId(), $operation);
    $form_object->setEntity($entity);

    $form_state = (new FormState())->setFormState($form_state_additions);
    return $this->getFormBuilder()->buildForm($form_object, $form_state);
  }
}
