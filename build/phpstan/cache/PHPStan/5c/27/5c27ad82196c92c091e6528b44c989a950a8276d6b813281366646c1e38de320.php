<?php declare(strict_types = 1);

// osfsl-/home/nckrtl/projects/laravel-toolbar/vendor/composer/../laravel/framework/src/Illuminate/Database/Events/QueryExecuted.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\Database\Events\QueryExecuted
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-c5c247dac5624ef08c0ba33210978084e8e7c084a8cabce1dd9574d561e60513-8.4.18-6.65.0.9',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'filename' => '/home/nckrtl/projects/laravel-toolbar/vendor/composer/../laravel/framework/src/Illuminate/Database/Events/QueryExecuted.php',
      ),
    ),
    'namespace' => 'Illuminate\\Database\\Events',
    'name' => 'Illuminate\\Database\\Events\\QueryExecuted',
    'shortName' => 'QueryExecuted',
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
    'endLine' => 80,
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
      'sql' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'implementingClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'name' => 'sql',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The SQL query that was executed.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 12,
        'endLine' => 12,
        'startColumn' => 5,
        'endColumn' => 16,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'bindings' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'implementingClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'name' => 'bindings',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The array of query bindings.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 19,
        'endLine' => 19,
        'startColumn' => 5,
        'endColumn' => 21,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'time' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'implementingClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'name' => 'time',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The number of milliseconds it took to execute the query.
 *
 * @var float
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 26,
        'endLine' => 26,
        'startColumn' => 5,
        'endColumn' => 17,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'connection' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'implementingClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'name' => 'connection',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The database connection instance.
 *
 * @var \\Illuminate\\Database\\Connection
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 33,
        'endLine' => 33,
        'startColumn' => 5,
        'endColumn' => 23,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'connectionName' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'implementingClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'name' => 'connectionName',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The database connection name.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 40,
        'endLine' => 40,
        'startColumn' => 5,
        'endColumn' => 27,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'readWriteType' => 
      array (
        'declaringClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'implementingClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'name' => 'readWriteType',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The PDO read / write type for the executed query.
 *
 * @var null|\'read\'|\'write\'
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 47,
        'endLine' => 47,
        'startColumn' => 5,
        'endColumn' => 26,
        'isPromoted' => false,
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
          'sql' => 
          array (
            'name' => 'sql',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 58,
            'endLine' => 58,
            'startColumn' => 33,
            'endColumn' => 36,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'bindings' => 
          array (
            'name' => 'bindings',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 58,
            'endLine' => 58,
            'startColumn' => 39,
            'endColumn' => 47,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'time' => 
          array (
            'name' => 'time',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 58,
            'endLine' => 58,
            'startColumn' => 50,
            'endColumn' => 54,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
          'connection' => 
          array (
            'name' => 'connection',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 58,
            'endLine' => 58,
            'startColumn' => 57,
            'endColumn' => 67,
            'parameterIndex' => 3,
            'isOptional' => false,
          ),
          'readWriteType' => 
          array (
            'name' => 'readWriteType',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 58,
                'endLine' => 58,
                'startTokenPos' => 79,
                'startFilePos' => 1122,
                'endTokenPos' => 79,
                'endFilePos' => 1125,
              ),
            ),
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 58,
            'endLine' => 58,
            'startColumn' => 70,
            'endColumn' => 90,
            'parameterIndex' => 4,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Create a new event instance.
 *
 * @param  string  $sql
 * @param  array  $bindings
 * @param  float|null  $time
 * @param  \\Illuminate\\Database\\Connection  $connection
 * @param  null|\'read\'|\'write\'  $readWriteType
 */',
        'startLine' => 58,
        'endLine' => 66,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Events',
        'declaringClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'implementingClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'currentClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'aliasName' => NULL,
      ),
      'toRawSql' => 
      array (
        'name' => 'toRawSql',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the raw SQL representation of the query with embedded bindings.
 *
 * @return string
 */',
        'startLine' => 73,
        'endLine' => 79,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Database\\Events',
        'declaringClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'implementingClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
        'currentClassName' => 'Illuminate\\Database\\Events\\QueryExecuted',
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