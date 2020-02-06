<?php


namespace Drupal\enum;


interface EnumServiceInterface {

  public function types();

  public function getEnumLabels();

  public function loadEnum($enum_id);

  public function loadEnums($filters = []);
}
