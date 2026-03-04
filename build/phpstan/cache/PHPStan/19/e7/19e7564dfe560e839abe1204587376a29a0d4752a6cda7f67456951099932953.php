<?php declare(strict_types = 1);

// osfsl-/home/nckrtl/projects/laravel-toolbar/vendor/composer/../laravel/framework/src/Illuminate/JsonSchema/Types/ObjectType.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\JsonSchema\Types\ObjectType
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-01a072559b73727a2fda81dff1bea9156d7c58cbf000c22d3f48e63a8ccd16d0-8.4.18-6.65.0.9',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
        'filename' => '/home/nckrtl/projects/laravel-toolbar/vendor/composer/../laravel/framework/src/Illuminate/JsonSchema/Types/ObjectType.php',
      ),
    ),
    'namespace' => 'Illuminate\\JsonSchema\\Types',
    'name' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
    'shortName' => 'ObjectType',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 5,
    'endLine' => 43,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\JsonSchema\\Types\\Type',
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
      'additionalProperties' => 
      array (
        'declaringClassName' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
        'implementingClassName' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
        'name' => 'additionalProperties',
        'modifiers' => 2,
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
                  'name' => 'bool',
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
        'default' => 
        array (
          'code' => 'null',
          'attributes' => 
          array (
            'startLine' => 10,
            'endLine' => 10,
            'startTokenPos' => 28,
            'startFilePos' => 189,
            'endTokenPos' => 28,
            'endFilePos' => 192,
          ),
        ),
        'docComment' => '/**
 * Whether additional properties are allowed.
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 10,
        'endLine' => 10,
        'startColumn' => 5,
        'endColumn' => 49,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'properties' => 
      array (
        'declaringClassName' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
        'implementingClassName' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
        'name' => 'properties',
        'modifiers' => 2,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 17,
        'endLine' => 17,
        'startColumn' => 33,
        'endColumn' => 64,
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
          'properties' => 
          array (
            'name' => 'properties',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 17,
                'endLine' => 17,
                'startTokenPos' => 47,
                'startFilePos' => 371,
                'endTokenPos' => 48,
                'endFilePos' => 372,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 17,
            'endLine' => 17,
            'startColumn' => 33,
            'endColumn' => 64,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create a new object type instance.
 *
 * @param  array<string, Type>  $properties
 */',
        'startLine' => 17,
        'endLine' => 20,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\JsonSchema\\Types',
        'declaringClassName' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
        'implementingClassName' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
        'currentClassName' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
        'aliasName' => NULL,
      ),
      'withoutAdditionalProperties' => 
      array (
        'name' => 'withoutAdditionalProperties',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'static',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Disallow additional properties.
 */',
        'startLine' => 25,
        'endLine' => 30,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\JsonSchema\\Types',
        'declaringClassName' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
        'implementingClassName' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
        'currentClassName' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
        'aliasName' => NULL,
      ),
      'default' => 
      array (
        'name' => 'default',
        'parameters' => 
        array (
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 37,
            'endLine' => 37,
            'startColumn' => 29,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'static',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Set the type\'s default value.
 *
 * @param  array<string, mixed>  $value
 */',
        'startLine' => 37,
        'endLine' => 42,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\JsonSchema\\Types',
        'declaringClassName' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
        'implementingClassName' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
        'currentClassName' => 'Illuminate\\JsonSchema\\Types\\ObjectType',
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