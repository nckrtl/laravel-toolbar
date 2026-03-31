<?php declare(strict_types = 1);

// osfsl-/Users/nckrtl/Projects/packages/laravel-toolbar/vendor/composer/../laravel/framework/src/Illuminate/Foundation/Events/Dispatchable.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\Foundation\Events\Dispatchable
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-70c0e27c21cb8a278bedcd79eb6d2dc33bc12ac326d4e27f84a01eaa44759e7c-8.4.17-6.65.0.9',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\Foundation\\Events\\Dispatchable',
        'filename' => '/Users/nckrtl/Projects/packages/laravel-toolbar/vendor/composer/../laravel/framework/src/Illuminate/Foundation/Events/Dispatchable.php',
      ),
    ),
    'namespace' => 'Illuminate\\Foundation\\Events',
    'name' => 'Illuminate\\Foundation\\Events\\Dispatchable',
    'shortName' => 'Dispatchable',
    'isInterface' => false,
    'isTrait' => true,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 5,
    'endLine' => 56,
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
      'dispatch' => 
      array (
        'name' => 'dispatch',
        'parameters' => 
        array (
          'arguments' => 
          array (
            'name' => 'arguments',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => true,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 13,
            'endLine' => 13,
            'startColumn' => 37,
            'endColumn' => 49,
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
 * Dispatch the event with the given arguments.
 *
 * @param  mixed  ...$arguments
 * @return mixed
 */',
        'startLine' => 13,
        'endLine' => 16,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Foundation\\Events',
        'declaringClassName' => 'Illuminate\\Foundation\\Events\\Dispatchable',
        'implementingClassName' => 'Illuminate\\Foundation\\Events\\Dispatchable',
        'currentClassName' => 'Illuminate\\Foundation\\Events\\Dispatchable',
        'aliasName' => NULL,
      ),
      'dispatchIf' => 
      array (
        'name' => 'dispatchIf',
        'parameters' => 
        array (
          'boolean' => 
          array (
            'name' => 'boolean',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 25,
            'endLine' => 25,
            'startColumn' => 39,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'arguments' => 
          array (
            'name' => 'arguments',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => true,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 25,
            'endLine' => 25,
            'startColumn' => 49,
            'endColumn' => 61,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Dispatch the event with the given arguments if the given truth test passes.
 *
 * @param  bool  $boolean
 * @param  mixed  ...$arguments
 * @return mixed
 */',
        'startLine' => 25,
        'endLine' => 30,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Foundation\\Events',
        'declaringClassName' => 'Illuminate\\Foundation\\Events\\Dispatchable',
        'implementingClassName' => 'Illuminate\\Foundation\\Events\\Dispatchable',
        'currentClassName' => 'Illuminate\\Foundation\\Events\\Dispatchable',
        'aliasName' => NULL,
      ),
      'dispatchUnless' => 
      array (
        'name' => 'dispatchUnless',
        'parameters' => 
        array (
          'boolean' => 
          array (
            'name' => 'boolean',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 39,
            'endLine' => 39,
            'startColumn' => 43,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'arguments' => 
          array (
            'name' => 'arguments',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => true,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 39,
            'endLine' => 39,
            'startColumn' => 53,
            'endColumn' => 65,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Dispatch the event with the given arguments unless the given truth test passes.
 *
 * @param  bool  $boolean
 * @param  mixed  ...$arguments
 * @return mixed
 */',
        'startLine' => 39,
        'endLine' => 44,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Foundation\\Events',
        'declaringClassName' => 'Illuminate\\Foundation\\Events\\Dispatchable',
        'implementingClassName' => 'Illuminate\\Foundation\\Events\\Dispatchable',
        'currentClassName' => 'Illuminate\\Foundation\\Events\\Dispatchable',
        'aliasName' => NULL,
      ),
      'broadcast' => 
      array (
        'name' => 'broadcast',
        'parameters' => 
        array (
          'arguments' => 
          array (
            'name' => 'arguments',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => true,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 52,
            'endLine' => 52,
            'startColumn' => 38,
            'endColumn' => 50,
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
 * Broadcast the event with the given arguments.
 *
 * @param  mixed  ...$arguments
 * @return \\Illuminate\\Broadcasting\\PendingBroadcast
 */',
        'startLine' => 52,
        'endLine' => 55,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Foundation\\Events',
        'declaringClassName' => 'Illuminate\\Foundation\\Events\\Dispatchable',
        'implementingClassName' => 'Illuminate\\Foundation\\Events\\Dispatchable',
        'currentClassName' => 'Illuminate\\Foundation\\Events\\Dispatchable',
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