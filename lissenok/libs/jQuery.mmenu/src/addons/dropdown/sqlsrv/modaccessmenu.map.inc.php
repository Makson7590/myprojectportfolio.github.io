<?php
/**
 * @package modx
 * @subpackage sqlsrv
 */
$xpdo_meta_map['modAccessMenu']= array (
  'package' => 'modx',
  'version' => '1.1',
  'table' => 'access_menus',
  'extends' => 'mo ccess',
  'fields' => 
  array (
  ),
  'fieldMeta' => 
  array (
  ),
  'aggregates' => 
  array (
    'Target' => 
    array (
      'class' => 'modMenu',
      'local' => 'target',
      'foreign' => 'text',
      'owner' => 'foreign',
      'cardinality' => 'one',
    ),
  ),
);
