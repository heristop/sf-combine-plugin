<?php

/**
 * Subclass for representing a row from the 'sf_combine' table.
 *
 * 
 *
 * @package plugins.sfCombinePlugin.lib.model
 */ 
class sfCombine extends BasesfCombine
{
  public function getFiles()
  {
    $files = unserialize(base64_decode(parent::getFiles()));
    if (!is_array($files))
    {
      $files = array();
    }
    
    return $files;
  }
}
