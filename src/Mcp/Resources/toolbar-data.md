# Laravel Toolbar Data
Laravel Toolbar data contains meta data for a specific http request. The data is being collected by different data collectors. Each collector collects its own unique request meta data:

## Request collector
Collects request data

collector_id: request

collects:
- http method
- URI
- Route name
- Applied middleware groups
- The controller/action + method used

## Response collector
Collects response data of a request

collector_id: response

collects:
- Response code
- Response size

## Queries collector
Collects database queries.

collector_id: queries

collects:
- Amount of queries performed
- Amount of slow queries performed
- Amount of duplicate queries performed
- Total duration of performed queries
- Database driver, connection and database name
- For each query it collects
  - The raw query
  - Time spent
  - Memory used
  - In which class and on which line it is performed

## Laravel collector
Collects app data

collector_id: laravel

Collects:
- Laravel version
- Environment
- Timezone
- Locale
- Debug true/false

## PHP collector
Collects PHP data

collector_id: php

Collects:
- PHP version
- Memory limit
- Max execution time

## Vue collector
Collects VueJS data

collector_id: vue

Collects:
- Vue version

## Profiler collector
Collects request profiler data

collector_id: profiler

Collects:
- Diagnostics, with time spent on collecting the data
- All stages of a request
- Each stage contains:
  - Label
  - Start:
    - Unix timestamp with microseconds
    - Memory used (real)
    - Memory used (allocated)
  - End:
    - Unix timestamp with microseconds
    - Memory used (real)
    - Memory used (allocated)
  - Duration in milliseconds
  - Percentage of the total time spent


## Collector data availability
Only enabled data collectors will run. The ids of enabled collectors can be fetched the GetCollectorsTool.