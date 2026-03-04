<?php declare(strict_types = 1);

// osfsl-/home/nckrtl/projects/laravel-toolbar/vendor/composer/../laravel/framework/src/Illuminate/Contracts/JsonSchema/JsonSchema.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\Contracts\JsonSchema\JsonSchema
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-ee6c62ad053c99a8db24e9d0083c15d2e582c8313b316804188a6e45b93cdaad-8.4.18-6.65.0.9',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'filename' => '/home/nckrtl/projects/laravel-toolbar/vendor/composer/../laravel/framework/src/Illuminate/Contracts/JsonSchema/JsonSchema.php',
      ),
    ),
    'namespace' => 'Illuminate\\Contracts\\JsonSchema',
    'name' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
    'shortName' => 'JsonSchema',
    'isInterface' => true,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 7,
    'endLine' => 51,
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
      'object' => 
      array (
        'name' => 'object',
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
                'startLine' => 15,
                'endLine' => 15,
                'startTokenPos' => 34,
                'startFilePos' => 414,
                'endTokenPos' => 35,
                'endFilePos' => 415,
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
                      'name' => 'Closure',
                      'isIdentifier' => false,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'array',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 15,
            'endLine' => 15,
            'startColumn' => 28,
            'endColumn' => 57,
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
 * Create a new object schema instance.
 *
 * @param  (Closure(JsonSchema): array<string, \\Illuminate\\JsonSchema\\Types\\Type>)|array<string, \\Illuminate\\JsonSchema\\Types\\Type>  $properties
 * @return \\Illuminate\\JsonSchema\\Types\\ObjectType
 */',
        'startLine' => 15,
        'endLine' => 15,
        'startColumn' => 5,
        'endColumn' => 59,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\JsonSchema',
        'declaringClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'implementingClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'currentClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'aliasName' => NULL,
      ),
      'array' => 
      array (
        'name' => 'array',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create a new array property instance.
 *
 * @return \\Illuminate\\JsonSchema\\Types\\ArrayType
 */',
        'startLine' => 22,
        'endLine' => 22,
        'startColumn' => 5,
        'endColumn' => 28,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\JsonSchema',
        'declaringClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'implementingClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'currentClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'aliasName' => NULL,
      ),
      'string' => 
      array (
        'name' => 'string',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create a new string property instance.
 *
 * @return \\Illuminate\\JsonSchema\\Types\\StringType
 */',
        'startLine' => 29,
        'endLine' => 29,
        'startColumn' => 5,
        'endColumn' => 29,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\JsonSchema',
        'declaringClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'implementingClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'currentClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'aliasName' => NULL,
      ),
      'integer' => 
      array (
        'name' => 'integer',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create a new integer property instance.
 *
 * @return \\Illuminate\\JsonSchema\\Types\\IntegerType
 */',
        'startLine' => 36,
        'endLine' => 36,
        'startColumn' => 5,
        'endColumn' => 30,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\JsonSchema',
        'declaringClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'implementingClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'currentClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'aliasName' => NULL,
      ),
      'number' => 
      array (
        'name' => 'number',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create a new number property instance.
 *
 * @return \\Illuminate\\JsonSchema\\Types\\NumberType
 */',
        'startLine' => 43,
        'endLine' => 43,
        'startColumn' => 5,
        'endColumn' => 29,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\JsonSchema',
        'declaringClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'implementingClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'currentClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'aliasName' => NULL,
      ),
      'boolean' => 
      array (
        'name' => 'boolean',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create a new boolean property instance.
 *
 * @return \\Illuminate\\JsonSchema\\Types\\BooleanType
 */',
        'startLine' => 50,
        'endLine' => 50,
        'startColumn' => 5,
        'endColumn' => 30,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Contracts\\JsonSchema',
        'declaringClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'implementingClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
        'currentClassName' => 'Illuminate\\Contracts\\JsonSchema\\JsonSchema',
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