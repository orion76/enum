<?php


namespace Drupal\enum\Form;


use Drupal\enum\Entity\EnumInterface;

interface EnumFormStateInterface {


  function getItem($id);

  function setRebuild();

  public function setEditedItem($item);

  public function getEditedItem();

  public function hasSelectedItem();

  function getSelectedItem($field = NULL);

  public function isAddNew();

  public function setSelectedItem($id);

  function setUserInput($parents, $value);

  function setValue($parents, $value);

  public function getAddNew();

  public function getEmptyItem();

  public function getEntity(): EnumInterface;

}
