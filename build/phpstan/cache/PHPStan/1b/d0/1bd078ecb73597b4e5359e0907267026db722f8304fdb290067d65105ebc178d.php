<?php declare(strict_types = 1);

// odsl-/home/nckrtl/projects/laravel-toolbar/src/helpers.php-PHPStan\BetterReflection\Reflection\ReflectionFunction-profile
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.65.0.9-8.4.18-9efcaaba54f10cbd59730330fdf0a9b222f57c4b5d18740b2d7fee823382c3b2',
   'data' => 
  array (
    'name' => 'profile',
    'parameters' => 
    array (
      'label' => 
      array (
        'name' => 'label',
        'default' => NULL,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 22,
        'endLine' => 22,
        'startColumn' => 22,
        'endColumn' => 34,
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
        'name' => 'void',
        'isIdentifier' => true,
      ),
    ),
    'attributes' => 
    array (
    ),
    'docComment' => '/**
 * Record a profile marker for substage profiling.
 *
 * This function allows you to track timing within a request stage (like a controller).
 * Each call creates a marker with the current timestamp and memory usage.
 * The time between consecutive markers becomes a substage.
 *
 * Example usage in a controller:
 *   profile(\'Fetching articles\');
 *   $articles = Article::all();
 *   profile(\'Converting to DTOs\');
 *   $dtos = $articles->map(fn($a) => ArticleDto::from($a));
 *   profile(\'Done\');
 *
 * @param  string  $label  A descriptive label for this profile point
 */',
    'startLine' => 22,
    'endLine' => 25,
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
        'name' => 'profile',
        'filename' => '/home/nckrtl/projects/laravel-toolbar/src/helpers.php',
      ),
    ),
  ),
));