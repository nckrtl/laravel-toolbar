<?php declare(strict_types = 1);

// odsl-/home/nckrtl/projects/laravel-toolbar/src/Data/Configurations/RequestConfig.php-PHPStan\BetterReflection\Reflection\ReflectionClass-NckRtl\Toolbar\Data\Configurations\RequestConfig
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.65.0.9-8.4.18-cd87ad94fa535d8d4020b7eda2648eb415ea597b47bde1392827a4b934eaf853',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'NckRtl\\Toolbar\\Data\\Configurations\\RequestConfig',
        'filename' => '/home/nckrtl/projects/laravel-toolbar/src/Data/Configurations/RequestConfig.php',
      ),
    ),
    'namespace' => 'NckRtl\\Toolbar\\Data\\Configurations',
    'name' => 'NckRtl\\Toolbar\\Data\\Configurations\\RequestConfig',
    'shortName' => 'RequestConfig',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 8,
    'endLine' => 19,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Spatie\\LaravelData\\Data',
    'implementsClassNames' => 
    array (
      0 => 'NckRtl\\Toolbar\\Data\\Configurations\\CollectorConfig',
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'enabled' => 
      array (
        'declaringClassName' => 'NckRtl\\Toolbar\\Data\\Configurations\\RequestConfig',
        'implementingClassName' => 'NckRtl\\Toolbar\\Data\\Configurations\\RequestConfig',
        'name' => 'enabled',
        'modifiers' => 1,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 11,
        'endLine' => 11,
        'startColumn' => 9,
        'endColumn' => 35,
        'isPromoted' => true,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'dataProvider' => 
      array (
        'declaringClassName' => 'NckRtl\\Toolbar\\Data\\Configurations\\RequestConfig',
        'implementingClassName' => 'NckRtl\\Toolbar\\Data\\Configurations\\RequestConfig',
        'name' => 'dataProvider',
        'modifiers' => 1,
        'type' => 
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
                  'name' => 'NckRtl\\Toolbar\\Enums\\DataProvider',
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
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 12,
        'endLine' => 12,
        'startColumn' => 9,
        'endColumn' => 49,
        'isPromoted' => true,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
          'enabled' => 
          array (
            'name' => 'enabled',
            'default' => 
            array (
              'code' => 'true',
              'attributes' => 
              array (
                'startLine' => 11,
                'endLine' => 11,
                'startTokenPos' => 46,
                'startFilePos' => 249,
                'endTokenPos' => 46,
                'endFilePos' => 252,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'bool',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 11,
            'endLine' => 11,
            'startColumn' => 9,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'dataProvider' => 
          array (
            'name' => 'dataProvider',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 12,
                'endLine' => 12,
                'startTokenPos' => 58,
                'startFilePos' => 300,
                'endTokenPos' => 58,
                'endFilePos' => 303,
              ),
            ),
            'type' => 
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
                      'name' => 'NckRtl\\Toolbar\\Enums\\DataProvider',
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
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 12,
            'endLine' => 12,
            'startColumn' => 9,
            'endColumn' => 49,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 10,
        'endLine' => 13,
        'startColumn' => 5,
        'endColumn' => 8,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'NckRtl\\Toolbar\\Data\\Configurations',
        'declaringClassName' => 'NckRtl\\Toolbar\\Data\\Configurations\\RequestConfig',
        'implementingClassName' => 'NckRtl\\Toolbar\\Data\\Configurations\\RequestConfig',
        'currentClassName' => 'NckRtl\\Toolbar\\Data\\Configurations\\RequestConfig',
        'aliasName' => NULL,
      ),
      'isEnabled' => 
      array (
        'name' => 'isEnabled',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 15,
        'endLine' => 18,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'NckRtl\\Toolbar\\Data\\Configurations',
        'declaringClassName' => 'NckRtl\\Toolbar\\Data\\Configurations\\RequestConfig',
        'implementingClassName' => 'NckRtl\\Toolbar\\Data\\Configurations\\RequestConfig',
        'currentClassName' => 'NckRtl\\Toolbar\\Data\\Configurations\\RequestConfig',
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