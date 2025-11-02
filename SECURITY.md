# Security Policy

## Important Notice

**Laravel Toolbar is a development tool and should NEVER be enabled in production environments.**

The toolbar exposes sensitive application data including:
- Database queries and SQL statements
- Request/response headers
- Environment configuration
- Application internals and timing data

## Reporting a Vulnerability

If you discover a security vulnerability within Laravel Toolbar, please send an email to nick.retel@gmail.com.

**Please do not open public GitHub issues for security vulnerabilities.**

All security vulnerabilities will be promptly addressed. You will receive a response acknowledging your report within 48 hours.

## Security Best Practices

### For Users

1. **Never enable in production** - Only use this package in local/development environments
2. **Use require-dev** - Install as a dev dependency only:
   ```json
   "require-dev": {
       "nckrtl/laravel-toolbar": "^1.0"
   }
   ```
3. **Disable explicitly** if installed in shared environments:
   ```php
   if (app()->environment('production')) {
       $toolbarConfig->disable();
   }
   ```

### Built-in Safety Features

The toolbar includes automatic safety measures:
- Disabled when running in console
- Skips AJAX requests automatically
- Skips non-HTML responses
- Can be disabled via `LARAVEL_TOOLBAR_ENABLED=false` environment variable

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 0.0.x   | :white_check_mark: |
