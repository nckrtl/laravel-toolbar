<?php declare(strict_types = 1);

// osfsl-/home/nckrtl/projects/laravel-toolbar/vendor/composer/../laravel/framework/src/Illuminate/View/Engines/CompilerEngine.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\View\Engines\CompilerEngine
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-df18edf5dbfa297144e166def5cdd3905c15f6358fb4bc83ea373e45f8579c63-8.4.18-6.65.0.9',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'filename' => '/home/nckrtl/projects/laravel-toolbar/vendor/composer/../laravel/framework/src/Illuminate/View/Engines/CompilerEngine.php',
      ),
    ),
    'namespace' => 'Illuminate\\View\\Engines',
    'name' => 'Illuminate\\View\\Engines\\CompilerEngine',
    'shortName' => 'CompilerEngine',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 15,
    'endLine' => 151,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\View\\Engines\\PhpEngine',
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
      'compiler' => 
      array (
        'declaringClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'implementingClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'name' => 'compiler',
        'modifiers' => 2,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The Blade compiler instance.
 *
 * @var \\Illuminate\\View\\Compilers\\CompilerInterface
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 22,
        'endLine' => 22,
        'startColumn' => 5,
        'endColumn' => 24,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'lastCompiled' => 
      array (
        'declaringClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'implementingClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'name' => 'lastCompiled',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 29,
            'endLine' => 29,
            'startTokenPos' => 77,
            'startFilePos' => 721,
            'endTokenPos' => 78,
            'endFilePos' => 722,
          ),
        ),
        'docComment' => '/**
 * A stack of the last compiled templates.
 *
 * @var array
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 29,
        'endLine' => 29,
        'startColumn' => 5,
        'endColumn' => 33,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'compiledOrNotExpired' => 
      array (
        'declaringClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'implementingClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'name' => 'compiledOrNotExpired',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 36,
            'endLine' => 36,
            'startTokenPos' => 89,
            'startFilePos' => 899,
            'endTokenPos' => 90,
            'endFilePos' => 900,
          ),
        ),
        'docComment' => '/**
 * The view paths that were compiled or are not expired, keyed by the path.
 *
 * @var array<string, true>
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 36,
        'endLine' => 36,
        'startColumn' => 5,
        'endColumn' => 41,
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
          'compiler' => 
          array (
            'name' => 'compiler',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\View\\Compilers\\CompilerInterface',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 44,
            'endLine' => 44,
            'startColumn' => 33,
            'endColumn' => 59,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'files' => 
          array (
            'name' => 'files',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 44,
                'endLine' => 44,
                'startTokenPos' => 113,
                'startFilePos' => 1188,
                'endTokenPos' => 113,
                'endFilePos' => 1191,
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
                      'name' => 'Illuminate\\Filesystem\\Filesystem',
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
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 44,
            'endLine' => 44,
            'startColumn' => 62,
            'endColumn' => 86,
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
 * Create a new compiler engine instance.
 *
 * @param  \\Illuminate\\View\\Compilers\\CompilerInterface  $compiler
 * @param  \\Illuminate\\Filesystem\\Filesystem|null  $files
 */',
        'startLine' => 44,
        'endLine' => 49,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\View\\Engines',
        'declaringClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'implementingClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'currentClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'aliasName' => NULL,
      ),
      'get' => 
      array (
        'name' => 'get',
        'parameters' => 
        array (
          'path' => 
          array (
            'name' => 'path',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 60,
            'endLine' => 60,
            'startColumn' => 25,
            'endColumn' => 29,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'data' => 
          array (
            'name' => 'data',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 60,
                'endLine' => 60,
                'startTokenPos' => 161,
                'startFilePos' => 1547,
                'endTokenPos' => 162,
                'endFilePos' => 1548,
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
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 60,
            'endLine' => 60,
            'startColumn' => 32,
            'endColumn' => 47,
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
 * Get the evaluated contents of the view.
 *
 * @param  string  $path
 * @param  array  $data
 * @return string
 *
 * @throws \\Illuminate\\View\\ViewException
 */',
        'startLine' => 60,
        'endLine' => 96,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\View\\Engines',
        'declaringClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'implementingClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'currentClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'aliasName' => NULL,
      ),
      'handleViewException' => 
      array (
        'name' => 'handleViewException',
        'parameters' => 
        array (
          'e' => 
          array (
            'name' => 'e',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Throwable',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 107,
            'endLine' => 107,
            'startColumn' => 44,
            'endColumn' => 55,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'obLevel' => 
          array (
            'name' => 'obLevel',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 107,
            'endLine' => 107,
            'startColumn' => 58,
            'endColumn' => 65,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Handle a view exception.
 *
 * @param  \\Throwable  $e
 * @param  int  $obLevel
 * @return void
 *
 * @throws \\Throwable
 */',
        'startLine' => 107,
        'endLine' => 119,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\View\\Engines',
        'declaringClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'implementingClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'currentClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'aliasName' => NULL,
      ),
      'getMessage' => 
      array (
        'name' => 'getMessage',
        'parameters' => 
        array (
          'e' => 
          array (
            'name' => 'e',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Throwable',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 127,
            'endLine' => 127,
            'startColumn' => 35,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the exception message for an exception.
 *
 * @param  \\Throwable  $e
 * @return string
 */',
        'startLine' => 127,
        'endLine' => 130,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\View\\Engines',
        'declaringClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'implementingClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'currentClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'aliasName' => NULL,
      ),
      'getCompiler' => 
      array (
        'name' => 'getCompiler',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Get the compiler implementation.
 *
 * @return \\Illuminate\\View\\Compilers\\CompilerInterface
 */',
        'startLine' => 137,
        'endLine' => 140,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\View\\Engines',
        'declaringClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'implementingClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'currentClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'aliasName' => NULL,
      ),
      'forgetCompiledOrNotExpired' => 
      array (
        'name' => 'forgetCompiledOrNotExpired',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Clear the cache of views that were compiled or not expired.
 *
 * @return void
 */',
        'startLine' => 147,
        'endLine' => 150,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\View\\Engines',
        'declaringClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'implementingClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
        'currentClassName' => 'Illuminate\\View\\Engines\\CompilerEngine',
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