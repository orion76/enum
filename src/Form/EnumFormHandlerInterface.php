<?php


namespace Drupal\enum\Form;


interface EnumFormHandlerInterface {

  function save();

  function validate();

  function getSubmitCallback();

  public function getValidateCallback();


  public function getAjaxWrapperId();

  /**
   * @return EnumFormStateInterface
   */
  public function getState();

  public function getAjaxOptions($trigger_as = NULL);

}
