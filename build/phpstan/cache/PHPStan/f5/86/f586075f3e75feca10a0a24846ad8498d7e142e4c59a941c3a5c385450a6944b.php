<?php declare(strict_types = 1);

// odsl-/Users/nckrtl/Projects/packages/laravel-toolbar/src/Collectors/LaravelCollector.php-PHPStan\BetterReflection\Reflection\ReflectionClass-NckRtl\Toolbar\Collectors\LaravelCollector
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.65.0.9-8.4.17-1f25ef06d8ccc234978a8195890adc1ce691b6697a010135afe5726ca881e9d7',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'NckRtl\\Toolbar\\Collectors\\LaravelCollector',
        'filename' => '/Users/nckrtl/Projects/packages/laravel-toolbar/src/Collectors/LaravelCollector.php',
      ),
    ),
    'namespace' => 'NckRtl\\Toolbar\\Collectors',
    'name' => 'NckRtl\\Toolbar\\Collectors\\LaravelCollector',
    'shortName' => 'LaravelCollector',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @property LaravelConfig $config
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 14,
    'endLine' => 69,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'NckRtl\\Toolbar\\Collectors\\Collector',
    'implementsClassNames' => 
    array (
      0 => 'NckRtl\\Toolbar\\Collectors\\CollectorInterface',
    ),
    'traitClassNames' => 
    array (
      0 => 'NckRtl\\Toolbar\\Traits\\ResolvesConfigSource',
      1 => 'Illuminate\\Foundation\\Concerns\\ResolvesDumpSource',
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
      'key' => 
      array (
        'name' => 'key',
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
        'docComment' => NULL,
        'startLine' => 19,
        'endLine' => 22,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'NckRtl\\Toolbar\\Collectors',
        'declaringClassName' => 'NckRtl\\Toolbar\\Collectors\\LaravelCollector',
        'implementingClassName' => 'NckRtl\\Toolbar\\Collectors\\LaravelCollector',
        'currentClassName' => 'NckRtl\\Toolbar\\Collectors\\LaravelCollector',
        'aliasName' => NULL,
      ),
      'configClass' => 
      array (
        'name' => 'configClass',
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
        'docComment' => NULL,
        'startLine' => 24,
        'endLine' => 27,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'NckRtl\\Toolbar\\Collectors',
        'declaringClassName' => 'NckRtl\\Toolbar\\Collectors\\LaravelCollector',
        'implementingClassName' => 'NckRtl\\Toolbar\\Collectors\\LaravelCollector',
        'currentClassName' => 'NckRtl\\Toolbar\\Collectors\\LaravelCollector',
        'aliasName' => NULL,
      ),
      'collectData' => 
      array (
        'name' => 'collectData',
        'parameters' => 
        array (
          'collectorManager' => 
          array (
            'name' => 'collectorManager',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'NckRtl\\Toolbar\\CollectorManager',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 29,
            'endLine' => 29,
            'startColumn' => 33,
            'endColumn' => 66,
            'parameterIndex' => 0,
            'isOptional' => false,
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
                  'name' => 'NckRtl\\Toolbar\\Data\\LaravelData',
                  'isIdentifier' => false,
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
        'docComment' => NULL,
        'startLine' => 29,
        'endLine' => 49,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'NckRtl\\Toolbar\\Collectors',
        'declaringClassName' => 'NckRtl\\Toolbar\\Collectors\\LaravelCollector',
        'implementingClassName' => 'NckRtl\\Toolbar\\Collectors\\LaravelCollector',
        'currentClassName' => 'NckRtl\\Toolbar\\Collectors\\LaravelCollector',
        'aliasName' => NULL,
      ),
      'getLaravelVersionEditorUrl' => 
      array (
        'name' => 'getLaravelVersionEditorUrl',
        'parameters' => 
        array (
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
                  'name' => 'string',
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
        'docComment' => NULL,
        'startLine' => 51,
        'endLine' => 68,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'NckRtl\\Toolbar\\Collectors',
        'declaringClassName' => 'NckRtl\\Toolbar\\Collectors\\LaravelCollector',
        'implementingClassName' => 'NckRtl\\Toolbar\\Collectors\\LaravelCollector',
        'currentClassName' => 'NckRtl\\Toolbar\\Collectors\\LaravelCollector',
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