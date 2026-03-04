<?php declare(strict_types = 1);

// odsl-/home/nckrtl/projects/laravel-toolbar/src/Observers/FetchesStackTrace.php-PHPStan\BetterReflection\Reflection\ReflectionClass-NckRtl\Toolbar\Observers\FetchesStackTrace
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.65.0.9-8.4.18-ba2d0a4218a91771e5bd797744aca7cb1c499fb2fc6fb77d90ac1595a31316ba',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'NckRtl\\Toolbar\\Observers\\FetchesStackTrace',
        'filename' => '/home/nckrtl/projects/laravel-toolbar/src/Observers/FetchesStackTrace.php',
      ),
    ),
    'namespace' => 'NckRtl\\Toolbar\\Observers',
    'name' => 'NckRtl\\Toolbar\\Observers\\FetchesStackTrace',
    'shortName' => 'FetchesStackTrace',
    'isInterface' => false,
    'isTrait' => true,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 7,
    'endLine' => 53,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
      'getCallerFromStackTrace' => 
      array (
        'name' => 'getCallerFromStackTrace',
        'parameters' => 
        array (
          'forgetLines' => 
          array (
            'name' => 'forgetLines',
            'default' => 
            array (
              'code' => '0',
              'attributes' => 
              array (
                'startLine' => 14,
                'endLine' => 14,
                'startTokenPos' => 30,
                'startFilePos' => 307,
                'endTokenPos' => 30,
                'endFilePos' => 307,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 14,
            'endLine' => 14,
            'startColumn' => 48,
            'endColumn' => 63,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
          'data' => 
          array (
            'types' => 
            array (
              0 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'array',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Find the first frame in the stack trace outside of Telescope/Laravel.
 *
 * @param  int|string|array  $forgetLines
 */',
        'startLine' => 14,
        'endLine' => 25,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'NckRtl\\Toolbar\\Observers',
        'declaringClassName' => 'NckRtl\\Toolbar\\Observers\\FetchesStackTrace',
        'implementingClassName' => 'NckRtl\\Toolbar\\Observers\\FetchesStackTrace',
        'currentClassName' => 'NckRtl\\Toolbar\\Observers\\FetchesStackTrace',
        'aliasName' => NULL,
      ),
      'ignoredPaths' => 
      array (
        'name' => 'ignoredPaths',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the file paths that should not be used by backtraces.
 */',
        'startLine' => 30,
        'endLine' => 36,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'NckRtl\\Toolbar\\Observers',
        'declaringClassName' => 'NckRtl\\Toolbar\\Observers\\FetchesStackTrace',
        'implementingClassName' => 'NckRtl\\Toolbar\\Observers\\FetchesStackTrace',
        'currentClassName' => 'NckRtl\\Toolbar\\Observers\\FetchesStackTrace',
        'aliasName' => NULL,
      ),
      'ignoredVendorPath' => 
      array (
        'name' => 'ignoredVendorPath',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Choose the frame outside of either Telescope / Laravel or all packages.
 *
 * Returns empty string to ignore all vendor packages, or \'laravel\' to only ignore Laravel packages.
 */',
        'startLine' => 43,
        'endLine' => 52,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'NckRtl\\Toolbar\\Observers',
        'declaringClassName' => 'NckRtl\\Toolbar\\Observers\\FetchesStackTrace',
        'implementingClassName' => 'NckRtl\\Toolbar\\Observers\\FetchesStackTrace',
        'currentClassName' => 'NckRtl\\Toolbar\\Observers\\FetchesStackTrace',
        'aliasName' => NULL,
      ),
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
      ),
    ),
  ),
));