<?php
/**
 * sfCombineCombiner
 *
 * @package    sfCombinePlugin
 * @subpackage combiner
 * @author     Alexandre Mogères
 * @author     Kevin Dew <kev@dewsolutions.co.uk>
 *
 */
abstract class sfCombineCombiner
{
  /**
   * @var array
   */
  protected $_files = array();

  /**
   * @var array
   */
  protected $_fileContents = array();

  /**
   * @var array
   */
  protected $_config = array();

  /**
   * Method to take a path to an asset and change the path if needed
   * (i.e. prepend /js/ to relative js paths)
   *
   * @param   string  $file  A path to a file
   * @return  string
   */
  abstract public function getAssetPath($file);

  /**
   * Constructs object and loads files if possible
   *
   * If a key is used files will be tried to be loaded off that key, if base64
   * that will be loaded and finally the files string
   *
   * @param   array   $config      (Optional) An array of config options based
   *                               off app.yml
   * @param   string  $key         (Optional) A sha1 hash to the database table
   * @param   string  $base64      (Optional) A base64 string of files to load
   * @param   string  $fileString  (Optional) A string of files to load
   *
   * @return  void
   */
  public function __construct(
    $config = array(),
    $key = '',
    $base64 = '',
    $fileString = ''
  )
  {
    $this->setConfig($config);

    $files = array();

    if ($key) {
      $files = sfCombineUrl::getFilesByKey($key);
    } else if ($base64) {
      $files = sfCombineUrl::getFilesByBase64($base64);
    } else if ($fileString) {
      $files = sfCombineUrl::getFiles($fileString, true);
    }
    $this->setFiles($files);

  }

  /**
   * @param   array $files
   * @return  sfCombine
   */
  public function setFiles($files)
  {
    $this->_files = is_array($files) ? $files : array();
    return $this;
  }

  /**
   * @return  array
   */
  public function getFiles()
  {
    return $this->_files;
  }

  /**
   * @param   array $fileContents
   * @return  sfCombine
   */
  public function setFileContents($fileContents)
  {
    $this->_fileContents = is_array($fileContents) ? $fileContents : array();
    return $this;
  }

  /**
   * @return array
   */
  public function getFileContents()
  {
    return $this->_fileContents;
  }

  /**
   * @param   array $config
   * @return  sfCombine
   */
  public function setConfig($config)
  {
    $this->_config = is_array($config) ? $config : array();
    return $this;
  }

  /**
   * @return  array
   */
  public function getConfig()
  {
    return $this->_config;
  }

  /**
   * Load a particular config option, a default can be provided incase it
   * doesn't exist
   *
   * @param   string  $option
   * @param   mixed   $default (Optional)
   * @return  array
   */
  public function getConfigOption($option, $default = false)
  {
    $config = $this->getConfig();

    return (array_key_exists($option, $config) ? $config[$option] : $default);
  }

  /**
   * Set a particular config option
   *
   * @param  string $option
   * @param  mixed  $value
   * @return sfCombine
   */
  public function setConfigOption($option, $value)
  {
    $config = $this->getConfig();

    $config[$option] = $value;

    $this->setConfig($config);

    return $this;
  }

  /**
   * Builds the files into a single file and then creates the minified output
   *
   * @return  string
   */
  public function process()
  {
    $config = $this->getConfig();

    $this->_collateFileContents(
      $this->getConfigOption('include', false),
      $this->getConfigOption('include_suffixes', array()),
      $this->getConfigOption('include_skip', array())
    );

    return $this->_generateOutput(
      $this->getConfigOption('minify', false),
      $this->getConfigOption('minify_skip_suffixes', array()),
      $this->getConfigOption('minify_skip', array())
    );
  }

  /**
   * Tries to open or include various files and put their contents together in
   * an array
   *
   * @param   bool  $allowIncludes    (Optional) Whether to allow php includes
   *                                  of files
   * @param   array $includeSuffixes  (Optional) if includes are allowed a
   *                                  suffix of file endings to allow (basically
   *                                  extensions like .php)
   * @param   array $dontInclude      (Optional) An array of filenames that
   *                                  should not be included
   * @return  array
   */
  protected function _collateFileContents(
    $allowIncludes = false,
    $includeSuffixes = array(),
    $dontInclude = array()
  )
  {
    // get old contents
    $oldFileContents = $this->getFileContents();

    $fileContents = array();

    foreach ($this->getFiles() as $file)
    {
      if (array_key_exists($file, $oldFileContents)) {
          $fileContents[$file] = $oldFileContents[$file];
          continue;
      }

      $assetPath = $this->getAssetPath($file);

      // break off any query string in the name
      $fileParts = explode('?', $assetPath);
      $fileName = $fileParts[0];

      try {

        $filePath = sfCombineUtility::getFilePath($fileName);

        if ( ! $filePath) {
          throw new Exception($fileName . ' does not exist');
        }

        if (! is_readable($filePath)) {
          throw new Exception($filePath . ' is not readable');
        }

        $includeFile = false;

        if ($allowIncludes) {
          // check to see if we are to include this file or get contents
          if (is_array($includeSuffixes)) {
            foreach ($includeSuffixes as $suffix) {
              if ((strlen($filePath) > strlen($suffix))
              && (substr($filePath, strlen($filePath) - strlen($suffix)) == $suffix)
              ) {
                $includeFile = true;
                break;
              }
            }
          }


          // check if file is blacklisted from being included
          if (is_array($dontInclude)) {
            $nonAssetParts = explode('?', $file);
            $nonAssetName = $nonAssetParts[0];

            foreach ($dontInclude as $name) {
              if (($name == $fileName)
              || ($name == $assetPath)
              || ($name == $file)
              || ($name == $nonAssetName)) {
                $includeFile = false;
                break;
              }
            }
          }
        }

        if ($includeFile) {

          // set gets
          $gets = array();

          if (isset($fileParts[1])) {
            parse_str($fileParts[1], $gets);
          }

          $contents = self::getIncludeFileContents($filePath, true, $gets);

        } else {
          $contents = @file_get_contents($filePath);

          if ($contents === false) {
            throw new Exception('Could not open ' . $filePath);
          }
        }

        $fileContents[$file] = $contents;

      } catch (Exception $e) {
        sfContext::getInstance()->getLogger()->err(
            'sfCombine exception: ' . $e->getMessage()
        );
      }
    }

    $this->setFileContents($fileContents);

    return $this->getFileContents();
  }

  /**
   * Method to generate the final output of the collated files. It adds each
   * minified chunk to a file cache
   *
   * @param   bool  $minify             (Optional) Whether to minify output
   * @param   array $minifySkipSuffixes (Optional) if minify is allowed a
   *                                    suffix of file endings to skip
   *                                    (basically extensions like .min.js)
   * @param   array $minifySkip         (Optional) An array of filenames that
   *                                    should not be minified
   * @return  string
   */
  protected function _generateOutput(
    $minify = false,
    $minifySkipSuffixes = array(),
    $minifySkip = array()
  )
  {
    $minifiable = $this->_groupMinifableFiles(
      $minify,
      $minifySkipSuffixes,
      $minifySkip,
      $this->getConfigOption('group_files', true)
    );

    $cache = false;
    if ($this->getConfigOption('cache_minified_files', false)) {
      $cache = new sfFileCache(
          array(
            'cache_dir' => $this->getCacheDir()
          )
      );
    }

    $output = '';

    $fileNameComments = $this->getConfigOption('filename_comments', false);

    foreach ($minifiable as $files) {

      if ($fileNameComments) {
        if ($output) {
          $output .= PHP_EOL;
        }
        $output .= $this->_addFilenameComments($files['files']);
      }
      
      if (!$files['minify']) {
        $output .= $files['contents'] . PHP_EOL;
      } else {

        $cachedMinifiedContents = $minifiedContents = false;

        if ($cache) {
          $key = sha1($files['contents']);
          if ($cache->has($key)) {
            $cachedMinifiedContents = $minifiedContents = $cache->get($key);
          }
        }

        if ($minifiedContents === false) {
          $minifiedContents = $this->minify($files['contents']);
        }

        $output .= $minifiedContents . PHP_EOL;

        if ($cache) {
          if ($minifiedContents != $cachedMinifiedContents) {
            $cache->set($key, $minifiedContents);
          }
        }

      }
    }

    return $output;

  }

  /**
   * Takes the various files and turns them into groups that can be minified
   * together. for instance if we had a.css, b.min.css, c.css and d.css, and
   * we weren't too minify b it'd wokr out to just combine c and d and output
   * a and b one after each other first.
   *
   * @param   bool  $minify             (Optional) Whether to minify output
   * @param   array $minifySkipSuffixes (Optional) if minify is allowed a
   *                                    suffix of file endings to skip
   *                                    (basically extensions like .min.js)
   * @param   array $minifySkip         (Optional) An array of filenames that
   *                                    should not be minified
   * @param   bool  $groupFiles         Whether files should be grouped together
   * @return  array in the form of array(
   *                  files => array(filenames),
   *                  contents => string of combined contents
   *                  minify => whether this group can be minified
   *                )
   */
  protected function _groupMinifableFiles(
    $minify = false,
    $minifySkipSuffixes = array(),
    $minifySkip = array(),
    $groupFiles = false
  )
  {
    $fileContents = $this->getFileContents();
    
    $return = array();
    
    $groupedMinify = array();
    $groupedContents = '';
    
    foreach ($fileContents as $file => $contents) {
      
      $canMinify = true;
      
      // check if we can minify file
      
      if (!$minify) {
        $canMinify = false;
      }

      // check suffix
      if ($canMinify && is_array($minifySkipSuffixes)) {
          $fileParts = explode('?', $file);
          $fileName = $fileParts[0];

          foreach ($minifySkipSuffixes as $suffix) {
            if ((strlen($fileName) > strlen($suffix))
            && (substr($fileName, strlen($fileName) - strlen($suffix)) == $suffix)
            ) {
              $canMinify = false;
              break;
            }
          }

      }

      // check file isn't to be skipped
      if ($canMinify && is_array($minifySkip)) {

        $assetPath = $this->getAssetPath($file);

        // break off any query string in the name
        $fileParts = explode('?', $assetPath);
        $assetPathFileName = $fileParts[0];

        foreach ($minifySkip as $name) {
          if (($name == $fileName)
          || ($name == $file)
          || ($name == $assetPathFileName)
          || ($name == $assetPath)) {
            $canMinify = false;
            break;
          }
        }
      }
      
      
      // add file to return array
      if ($canMinify && $groupFiles) {
        $groupedMinify[] = $file;

        if ($groupedContents) {
          $groupedContents .= PHP_EOL;
        }

        $groupedContents .= $contents;

      } else {
        
        if ($groupedMinify) {
          // add groups to return
          
          $return[] = array(
            'files' => $groupedMinify,
            'contents' => $groupedContents,
            'minify' => true            
          );
          
          //reset groups
          
          $groupedMinify = array();
          $groupedContents = '';
        }
        
        $return[] = array(
          'files' => array($file),
          'contents' => $contents,
          'minify' => $canMinify
        );
        
      }
      
    }
    
    if ($groupedMinify) {
      $return[] = array(
        'files' => $groupedMinify,
        'contents' => $groupedContents,
        'minify' => true
      );
    }

    return $return;

  }

  /**
   * @see sfCombineUtility::getCacheDir()
   */
  static public function getCacheDir()
  {
      return sfCombineUtility::getCacheDir();
  }

  /**
   * Take a file path and include the file, dynamically change the value of
   * $_GET and symfony request parameters to try somewhat replicate environment
   * of accessing a php file outside of this
   *
   * Note can only add to symfony request parameters
   *
   * @param   string  $filePath
   * @param   bool    $setGets  (Optional) Whether to set the $_GET and symfony
   *                            request parameters
   * @param   array   $getArray (Optional) An array of the query string for the
   *                            gets to use
   * @return  string  The included file output
   */
  static public function getIncludeFileContents(
    $filePath,
    $setGets = false,
    $getArray = array()
  )
  {
    // change what we can of gets
    if ($setGets) {
      $request = sfContext::getInstance()->getRequest();

      $currentGet = $_GET;
      $requestParameters = $request->getRequestParameters();

      $_GET = $getArray;
      $request->addRequestParameters($getArray);
    }

    ob_start();

    @include $filePath;
    $contents = ob_get_contents();

    ob_end_clean();

    // reset gets
    if ($setGets) {
          $_GET = $currentGet;
          $request->addRequestParameters($requestParameters);
    }

    if ($contents == false) {
      throw new Exception('Could not output buffer ' . $filePath);
    }

    return $contents;
  }

  /**
   * Calls a method to minify the content, returns the minified output. If
   * methods weren't callable returns the content unminified
   *
   * @param   string        $content
   * @param   string|array  $minifyMethod (Optional) A string or array of a
   *                        function method that can be called by call_user_func
   *                        to minify the content
   * @param   array         $minifyMethodOptions (Optional) Options for above method
   * @return  string
   */
  public function minify(
    $content, $minifyMethod = false, array $minifyMethodOptions = array()
  )
  {
    if ($minifyMethod && is_callable($minifyMethod)) {
      return call_user_func($minifyMethod, $content, $minifyMethodOptions);
    }

    throw new Exception('Minify method could not be called');
  }

  /**
   * Create a string of filenames each on a new line with css comments
   * surrounding
   *
   * @param   array $files An array of filenames
   * @return  string
   */
  protected function _addFilenameComments($files)
  {
    $return = '';

    foreach ($files as $fileName) {
      $return .= '/* ' . $fileName . ' */'. PHP_EOL;
    }

    return $return;
  }
}