<?php declare(strict_types = 1);

// odsl-/Users/nckrtl/Projects/packages/laravel-toolbar/vendor/composer/../laravel/framework/src/Illuminate/Support/helpers.php-PHPStan\BetterReflection\Reflection\ReflectionFunction-optional
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.65.0.9-8.4.17-03e08f2db7a9486e64b7887f9a1c50b544b68fe8794284259fa985a40534a5b8',
   'data' => 
  array (
    'name' => 'optional',
    'parameters' => 
    array (
      'value' => 
      array (
        'name' => 'value',
        'default' => 
        array (
          'code' => '\\null',
          'attributes' => 
          array (
            'startLine' => 270,
            'endLine' => 270,
            'startTokenPos' => 1185,
            'startFilePos' => 6668,
            'endTokenPos' => 1185,
            'endFilePos' => 6671,
          ),
        ),
        'type' => NULL,
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 270,
        'endLine' => 270,
        'startColumn' => 23,
        'endColumn' => 35,
        'parameterIndex' => 0,
        'isOptional' => true,
      ),
      'callback' => 
      array (
        'name' => 'callback',
        'default' => 
        array (
          'code' => '\\null',
          'attributes' => 
          array (
            'startLine' => 270,
            'endLine' => 270,
            'startTokenPos' => 1195,
            'startFilePos' => 6696,
            'endTokenPos' => 1195,
            'endFilePos' => 6699,
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
                  'name' => 'callable',
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
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 270,
        'endLine' => 270,
        'startColumn' => 38,
        'endColumn' => 63,
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
 * Provide access to optional objects.
 *
 * @template TValue
 * @template TReturn
 *
 * @param  TValue  $value
 * @param  (callable(TValue): TReturn)|null  $callback
 * @return ($callback is null ? \\Illuminate\\Support\\Optional : ($value is null ? null : TReturn))
 */',
    'startLine' => 270,
    'endLine' => 277,
    'startColumn' => 5,
    'endColumn' => 5,
    'couldThrow' => false,
    'isClosure' => false,
    'isGenerator' => false,
    'isVariadic' => false,
    'isStatic' => false,
    'namespace' => NULL,
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'optional',
        'filename' => '/Users/nckrtl/Projects/packages/laravel-toolbar/vendor/composer/../laravel/framework/src/Illuminate/Support/helpers.php',
      ),
    ),
  ),
));