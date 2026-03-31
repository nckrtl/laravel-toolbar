<?php declare(strict_types = 1);

// odsl-/Users/nckrtl/Projects/packages/laravel-toolbar/vendor/composer/../laravel/framework/src/Illuminate/Support/helpers.php-PHPStan\BetterReflection\Reflection\ReflectionFunction-throw_if
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.65.0.9-8.4.17-03e08f2db7a9486e64b7887f9a1c50b544b68fe8794284259fa985a40534a5b8',
   'data' => 
  array (
    'name' => 'throw_if',
    'parameters' => 
    array (
      'condition' => 
      array (
        'name' => 'condition',
        'default' => NULL,
        'type' => NULL,
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 410,
        'endLine' => 410,
        'startColumn' => 23,
        'endColumn' => 32,
        'parameterIndex' => 0,
        'isOptional' => false,
      ),
      'exception' => 
      array (
        'name' => 'exception',
        'default' => 
        array (
          'code' => '\'RuntimeException\'',
          'attributes' => 
          array (
            'startLine' => 410,
            'endLine' => 410,
            'startTokenPos' => 1754,
            'startFilePos' => 10410,
            'endTokenPos' => 1754,
            'endFilePos' => 10427,
          ),
        ),
        'type' => NULL,
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 410,
        'endLine' => 410,
        'startColumn' => 35,
        'endColumn' => 65,
        'parameterIndex' => 1,
        'isOptional' => true,
      ),
      'parameters' => 
      array (
        'name' => 'parameters',
        'default' => NULL,
        'type' => NULL,
        'isVariadic' => true,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 410,
        'endLine' => 410,
        'startColumn' => 68,
        'endColumn' => 81,
        'parameterIndex' => 2,
        'isOptional' => true,
      ),
    ),
    'returnsReference' => false,
    'returnType' => NULL,
    'attributes' => 
    array (
    ),
    'docComment' => '/**
 * Throw the given exception if the given condition is true.
 *
 * @template TValue
 * @template TParams of mixed
 * @template TException of \\Throwable
 * @template TExceptionValue of TException|class-string<TException>|string
 *
 * @param  TValue  $condition
 * @param  Closure(TParams): TExceptionValue|TExceptionValue  $exception
 * @param  TParams  ...$parameters
 * @return ($condition is true ? never : ($condition is non-empty-mixed ? never : TValue))
 *
 * @throws TException
 */',
    'startLine' => 410,
    'endLine' => 425,
    'startColumn' => 5,
    'endColumn' => 5,
    'couldThrow' => false,
    'isClosure' => false,
    'isGenerator' => false,
    'isVariadic' => true,
    'isStatic' => false,
    'namespace' => NULL,
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'throw_if',
        'filename' => '/Users/nckrtl/Projects/packages/laravel-toolbar/vendor/composer/../laravel/framework/src/Illuminate/Support/helpers.php',
      ),
    ),
  ),
));