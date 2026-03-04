<?php declare(strict_types = 1);

// osfsl-/home/nckrtl/projects/laravel-toolbar/vendor/composer/../spatie/laravel-data/src/Contracts/BaseData.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Spatie\LaravelData\Contracts\BaseData
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-719bb3e596a3e6e22edd3acca42eb89186c7fb11ba7c4279c71db10418ff2fd2-8.4.18-6.65.0.9',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'filename' => '/home/nckrtl/projects/laravel-toolbar/vendor/composer/../spatie/laravel-data/src/Contracts/BaseData.php',
      ),
    ),
    'namespace' => 'Spatie\\LaravelData\\Contracts',
    'name' => 'Spatie\\LaravelData\\Contracts\\BaseData',
    'shortName' => 'BaseData',
    'isInterface' => true,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * @template TData
 * @template TValue of mixed
 * @template TKey of array-key
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 27,
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
      'optional' => 
      array (
        'name' => 'optional',
        'parameters' => 
        array (
          'payloads' => 
          array (
            'name' => 'payloads',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'mixed',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => true,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 32,
            'endLine' => 32,
            'startColumn' => 37,
            'endColumn' => 54,
            'parameterIndex' => 0,
            'isOptional' => true,
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
                  'name' => 'static',
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
        'docComment' => '/**
 * @return static|null
 */',
        'startLine' => 32,
        'endLine' => 32,
        'startColumn' => 5,
        'endColumn' => 65,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 17,
        'namespace' => 'Spatie\\LaravelData\\Contracts',
        'declaringClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'implementingClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'currentClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'aliasName' => NULL,
      ),
      'from' => 
      array (
        'name' => 'from',
        'parameters' => 
        array (
          'payloads' => 
          array (
            'name' => 'payloads',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'mixed',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => true,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 37,
            'endLine' => 37,
            'startColumn' => 33,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => true,
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
 * @return static
 */',
        'startLine' => 37,
        'endLine' => 37,
        'startColumn' => 5,
        'endColumn' => 60,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 17,
        'namespace' => 'Spatie\\LaravelData\\Contracts',
        'declaringClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'implementingClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'currentClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'aliasName' => NULL,
      ),
      'collect' => 
      array (
        'name' => 'collect',
        'parameters' => 
        array (
          'items' => 
          array (
            'name' => 'items',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'mixed',
                'isIdentifier' => true,
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
            'startColumn' => 36,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'into' => 
          array (
            'name' => 'into',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 44,
                'endLine' => 44,
                'startTokenPos' => 170,
                'startFilePos' => 2769,
                'endTokenPos' => 170,
                'endFilePos' => 2772,
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
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 44,
            'endLine' => 44,
            'startColumn' => 50,
            'endColumn' => 69,
            'parameterIndex' => 1,
            'isOptional' => true,
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
                  'name' => 'array',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'Spatie\\LaravelData\\DataCollection',
                  'isIdentifier' => false,
                ),
              ),
              2 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'Spatie\\LaravelData\\PaginatedDataCollection',
                  'isIdentifier' => false,
                ),
              ),
              3 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'Spatie\\LaravelData\\CursorPaginatedDataCollection',
                  'isIdentifier' => false,
                ),
              ),
              4 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'Illuminate\\Support\\Enumerable',
                  'isIdentifier' => false,
                ),
              ),
              5 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'Illuminate\\Pagination\\AbstractPaginator',
                  'isIdentifier' => false,
                ),
              ),
              6 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'Illuminate\\Contracts\\Pagination\\Paginator',
                  'isIdentifier' => false,
                ),
              ),
              7 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'Illuminate\\Pagination\\AbstractCursorPaginator',
                  'isIdentifier' => false,
                ),
              ),
              8 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'Illuminate\\Contracts\\Pagination\\CursorPaginator',
                  'isIdentifier' => false,
                ),
              ),
              9 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'Illuminate\\Support\\LazyCollection',
                  'isIdentifier' => false,
                ),
              ),
              10 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'Illuminate\\Support\\Collection',
                  'isIdentifier' => false,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param Collection<TKey, TValue>|EloquentCollection<TKey, TValue>|LazyCollection<TKey, TValue>|Enumerable|array<TKey, TValue>|AbstractPaginator|PaginatorContract|AbstractCursorPaginator|CursorPaginatorContract|DataCollection<TKey, TValue> $items
 *
 * @return ($into is \'array\' ? array<TKey, static> : ($into is class-string<EloquentCollection> ? Collection<TKey, static> : ($into is class-string<Collection> ? Collection<TKey, static> : ($into is class-string<LazyCollection> ? LazyCollection<TKey, static> : ($into is class-string<DataCollection> ? DataCollection<TKey, static> : ($into is class-string<PaginatedDataCollection> ? PaginatedDataCollection<TKey, static> : ($into is class-string<CursorPaginatedDataCollection> ? CursorPaginatedDataCollection<TKey, static> : ($items is EloquentCollection ? Collection<TKey, static> : ($items is Collection ? Collection<TKey, static> : ($items is LazyCollection ? LazyCollection<TKey, static> : ($items is Enumerable ? Enumerable<TKey, static> : ($items is array ? array<TKey, static> : ($items is AbstractPaginator ? AbstractPaginator : ($items is PaginatorContract ? PaginatorContract : ($items is AbstractCursorPaginator ? AbstractCursorPaginator : ($items is CursorPaginatorContract ? CursorPaginatorContract : ($items is DataCollection ? DataCollection<TKey, static> : ($items is CursorPaginator ? CursorPaginatedDataCollection<TKey, static> : ($items is Paginator ? PaginatedDataCollection<TKey, static> : DataCollection<TKey, static>)))))))))))))))))))
 */',
        'startLine' => 44,
        'endLine' => 44,
        'startColumn' => 5,
        'endColumn' => 268,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Spatie\\LaravelData\\Contracts',
        'declaringClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'implementingClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'currentClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'aliasName' => NULL,
      ),
      'factory' => 
      array (
        'name' => 'factory',
        'parameters' => 
        array (
          'creationContext' => 
          array (
            'name' => 'creationContext',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 49,
                'endLine' => 49,
                'startTokenPos' => 214,
                'startFilePos' => 3107,
                'endTokenPos' => 214,
                'endFilePos' => 3110,
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
                      'name' => 'Spatie\\LaravelData\\Support\\Creation\\CreationContext',
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
            'startLine' => 49,
            'endLine' => 49,
            'startColumn' => 36,
            'endColumn' => 75,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Spatie\\LaravelData\\Support\\Creation\\CreationContextFactory',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return CreationContextFactory<static>
 */',
        'startLine' => 49,
        'endLine' => 49,
        'startColumn' => 5,
        'endColumn' => 101,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Spatie\\LaravelData\\Contracts',
        'declaringClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'implementingClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'currentClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'aliasName' => NULL,
      ),
      'normalizers' => 
      array (
        'name' => 'normalizers',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 51,
        'endLine' => 51,
        'startColumn' => 5,
        'endColumn' => 48,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Spatie\\LaravelData\\Contracts',
        'declaringClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'implementingClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'currentClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'aliasName' => NULL,
      ),
      'prepareForPipeline' => 
      array (
        'name' => 'prepareForPipeline',
        'parameters' => 
        array (
          'properties' => 
          array (
            'name' => 'properties',
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
            'startLine' => 53,
            'endLine' => 53,
            'startColumn' => 47,
            'endColumn' => 63,
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
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 53,
        'endLine' => 53,
        'startColumn' => 5,
        'endColumn' => 72,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Spatie\\LaravelData\\Contracts',
        'declaringClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'implementingClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'currentClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'aliasName' => NULL,
      ),
      'pipeline' => 
      array (
        'name' => 'pipeline',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Spatie\\LaravelData\\DataPipeline',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 55,
        'endLine' => 55,
        'startColumn' => 5,
        'endColumn' => 52,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Spatie\\LaravelData\\Contracts',
        'declaringClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'implementingClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
        'currentClassName' => 'Spatie\\LaravelData\\Contracts\\BaseData',
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