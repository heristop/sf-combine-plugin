<?php

class sfCombineJs extends sfCombiner
{
  /**
   * Processes the assets corresponding to a hash
   * 
   * @param string $key Key to a list of asset files in the `sf_combine` table
   *
   * @return string Processed Javascript code
   */
  public function process($key)
  {
    $files = $this->getContents($key);
    
    $config = sfConfig::get('app_sfCombinePlugin_js', array());
    
    if (isset($config['minify']) && $config['minify'])
    {
      // minification
      $skipMinify = isset($config['minify_skip']) ? $config['minify_skip'] : array();
      
      foreach ($files as $filePath => $content)
      {
        if (!skip_asset($filePath, $skipMinify))
        {
          $files[$filePath] = $this->minify($content);
        }
      }
    }
    
    // packing
    if (isset($config['pack']) && $config['pack'])
    {
      $skipPack = isset($config['pack_skip']) ? $config['pack_skip'] : array();
      if(!$skipPack)
      {
        // simple: pack everything together
        $finalContent = $this->pack($this->merge($files));
      }
      else
      {
        // less simple: pack groups of files, avoiding the ones that should not be packed
        $finalContent = '';
        $toProcess = '';
        foreach ($files as $filePath => $content)
        {
          if (skip_asset($filePath, $skipPack))
          {
            $finalContent .= $this->pack($toProcess);
            $finalContent .= $content;
            $toProcess = '';
          }
          else
          {
            $toProcess .= $content;
          }
        }
        if ($toProcess)
        {
          $packer = new JavaScriptPacker($toProcess, 'Normal', true, false);
          $finalContent .= $packer->pack();
        }
      }
    }
    else
    {
      // no packing at all, simply merge
      $includeComment = isset($config['minify']) ? !$config['minify'] : true;
      $finalContent = $this->merge($files, $includeComment);
    }
    
    return $finalContent;
  }
  
  /**
   * Minify content
   *
   * @param string $content Content to be minified
   * 
   * @return string Minified content
   */
  protected function minify($content)
  {
    $packer = new JavaScriptPacker($content, 'None', true, false);
    
    return $packer->pack();
  }

  /**
   * Pack content
   *
   * @param string $content Content to be packed
   * 
   * @return string Packed content
   */
  protected function pack($content)
  {
    $packer = new JavaScriptPacker($content, 'Normal', true, false);
    
    return $packer->pack();
  }
  
  protected function getAssetPath($file)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('Asset');

    return javascript_path($file);
  }
}