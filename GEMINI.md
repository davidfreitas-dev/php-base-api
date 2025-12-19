# Project: PHP Base API

## Environment Setup
- **Docker-based development**: All PHP tools run through Docker containers
- **No local Composer**: Composer commands must use Docker
- **PHP Version**: 8.3
- **Framework**: Slim Framework 4.x

## General Instructions

- When you generate new PHP code, follow the existing coding style in the project
- Follow PSR-12 (PHP Standards Recommendations) for code formatting and style
- Use type hints for function parameters and return types whenever possible
- Prefer strict type declarations (declare(strict_types=1);) at the beginning of files
- Use class constants instead of magic strings when appropriate
- Avoid global functions; prefer class methods or namespaced functions

## Documentation

- Ensure all new functions, methods, and classes have PHPDoc comment blocks
- Include @param, @return, @throws, and @var annotations in PHPDoc blocks
- Document the purpose and behavior of complex classes and methods
- Add usage examples in comments when functionality is complex

## Performance and Best Practices

- Prefer composition over inheritance when appropriate
- Use dependency injection(PHP-DI) instead of instantiating objects directly
- Implement caching where appropriate (Redis, Memcached, or file cache)
- Use PSR-4 autoloading for class loading

## Code Structure

- Organize code following SOLID principles
- Keep methods small and focused (Single Responsibility Principle)
- Use interfaces to define contracts between components
- Group related functionality in appropriate namespaces
- Separate business logic from presentation logic

## Code Quality

- Use code formatters (PHP-CS-Fixer) to maintain consistent style
- Avoid deep nesting; refactor complex conditionals into separate methods
- Keep cyclomatic complexity low for maintainability
- Always use Constructor Property Promotion to simplify class property declarations.

## Docker Commands Reference

### Composer Commands
```bash
# Install dependencies
docker run --rm --interactive --tty \
  --volume $PWD:/app \
  composer install

# Update dependencies
docker run --rm --interactive --tty \
  --volume $PWD:/app \
  composer update

# Require new package
docker run --rm --interactive --tty \
  --volume $PWD:/app \
  composer require package/name

# Remove package
docker run --rm --interactive --tty \
  --volume $PWD:/app \
  composer remove package/name
```

## Security
- Never hardcode credentials
- Always use environment variables for sensitive data
- JWT tokens for API authentication
- Input validation and sanitization required

## File Structure
```
project/
├── public/
│   └── index.php
├── src/
│   ├── Clients/
│   ├── Config/
│   ├── DB/
│   ├── Enums/
│   ├── Handlers/
│   ├── Interfaces/
│   ├── Logging/
│   ├── Mail/
│   ├── Middleware/
│   ├── Models/
│   ├── Routes/
│   ├── Services/
│   └── Utils/
├── res/
│   └── db/
│       └── schema.sql
├── vendor/
├── .env.example
├── composer.json
├── docker-compose.yml
├── Dockerfile
```

## Important Rules

1. **NEVER suggest running Composer locally** - Always provide Docker commands
2. **Always show complete Docker commands** - Include volume mounts
3. **Consider Docker context** - File permissions, paths, etc.
4. **Provide working code** - Test commands before suggesting
5. **Use environment variables** - Never hardcode configuration
6. **Follow PSR standards** - PSR-4, PSR-12, PSR-7

## When Refactoring
- Maintain existing functionality
- Preserve environment variable usage
- Keep Docker compatibility
- Update composer.json if needed (with Docker command)