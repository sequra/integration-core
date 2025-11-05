# Developer Guide

This guide provides comprehensive information for developers working with the SeQura Integration Core library. It covers the development tools, Docker setup, debugging, and running tests.

## Table of Contents

- [Available Development Tools](#available-development-tools)
- [Docker Setup](#docker-setup)
- [Debugging](#debugging)
- [Running Tests](#running-tests)
- [Development Workflow](#development-workflow)
- [Troubleshooting](#troubleshooting)

## Available Development Tools

The `bin/` directory contains several essential scripts for development and code quality. These scripts are symlinked to their corresponding vendor packages.

### 1. **bin/composer**
Package dependency manager for PHP projects.

**Purpose**: Manage project dependencies and autoloading

**Usage**:
```bash
# Install dependencies
./bin/composer install

# Require a new package
./bin/composer require vendor/package

# Update dependencies
./bin/composer update

# Show installed packages
./bin/composer show
```

**More Info**: [https://getcomposer.org/](https://getcomposer.org/)

---

### 2. **bin/php-syntax-check**
Validates PHP syntax across the project.

**Purpose**: Check for PHP syntax errors without executing the code

**Usage**:
```bash
# Check all PHP files in current PHP version
./bin/php-syntax-check

# Specify PHP version
./bin/php-syntax-check --php=8.1
./bin/php-syntax-check --php=7.4
```

**When to Use**:
- Before committing code
- In CI/CD pipelines
- Quick validation before full test suite

---

### 3. **bin/phpcs** - PHP CodeSniffer
Detects violations of coding standards and best practices.

**Purpose**: Enforce consistent code style and detect potential issues

**Usage**:
```bash
# Check all files against the project standard
./bin/phpcs
```

**Configuration**: See `.phpcs.xml.dist`

**Common Issues Found**:
- Improper spacing around control structures
- Missing documentation blocks
- Unused use statements
- Line length violations

---

### 4. **bin/phpcbf** - PHP Code Beautifier and Fixer
Automatically fixes coding standard violations.

**Purpose**: Auto-correct fixable code style issues

**Usage**:
```bash
# Fix all files
./bin/phpcbf
```

**Note**: Always review changes after running `phpcbf`

**Example Fixes**:
- Adds missing spaces
- Corrects indentation
- Removes trailing whitespace
- Organizes use statements

---

### 5. **bin/phpstan**
Static analysis tool for finding bugs and code smells.

**Purpose**: Detect type errors, undefined variables, and logical issues

**Usage**:
```bash
# Analyze entire src directory
./bin/phpstan
```

**Configuration**: See `phpstan.neon`

**Common Issues Detected**:
- Type mismatches
- Undefined variables
- Dead code
- Incorrect method signatures
- Logic errors

**Memory Usage**: The `--memory-limit=1G` flag is often necessary for larger projects.

---

### 6. **bin/phpunit**
Unit testing framework for PHP.

**Purpose**: Run automated tests to verify code behavior

**Usage**:
```bash
# Run all tests
./bin/phpunit
```

**Configuration**: See `phpunit.xml`

---

## Docker Setup

The project uses Docker to ensure consistency across different development environments.

### Prerequisites

- **Docker Desktop**: [https://www.docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop)
- **Docker Compose**: Usually included with Docker Desktop
- **macOS/Linux**: Recommended; if using Windows, WSL2 is highly recommended

### Container Stack

The `docker-compose.yml` defines a single PHP 7.2 service with XDebug pre-configured:

- **Service**: `php` - PHP 7.2 CLI with XDebug 2.9.8
- **Build Context**: `docker/Dockerfile`
- **Working Directory**: `/app` (mounted from project root)
- **Port Mapping**: XDebug port `9003`

### Initial Setup

**1. Create Environment File**

The setup script handles this automatically, but you can also do it manually:

```bash
# Copy from example
cp .env.sample .env

# Edit with your preferences (optional)
nano .env
```

Available environment variables:

```bash
# XDebug IDE key - use PHPSTORM for both VSCode and PhpStorm
XDEBUG_IDEKEY=PHPSTORM

# XDebug mode and other options
XDEBUG_CONFIG=idekey=PHPSTORM mode=req

# Server name for IDE configuration
PHP_IDE_CONFIG=serverName=localhost
```

**2. Start Docker Containers**

Use the provided setup script:

```bash
# Simple one-command setup
./setup.sh

# Or manually with docker-compose
docker compose up -d --build
```

This will:
- Build the PHP container with XDebug
- Mount project files
- Start the container in background

**3. Verify Setup**

```bash
# Check if container is running
docker compose ps

# See container logs
docker compose logs php

# Check PHP version and extensions
docker compose exec php php -v
docker compose exec php php -m | grep xdebug
```

### Stopping the Container

```bash
# Stop containers (data preserved)
docker compose stop

# Stop and remove containers
docker compose down

# Stop and remove everything including volumes
docker compose down -v
```

### Cleanup

```bash
# Remove orphaned containers
docker compose down --remove-orphans

# Remove all dangling images
docker image prune

# Full cleanup (use with caution)
./teardown.sh
```

---

## Debugging

This project is configured to work with both **VSCode** and **PhpStorm** for debugging PHP code in Docker containers using XDebug.

### Configuration Overview

- **XDebug Version**: 2.9.8 (legacy, compatible with PHP 7.2)
- **Debug Port**: 9003
- **IDE Key**: PHPSTORM (neutral, works with both IDEs)
- **Host Detection**: `host.docker.internal` (works on macOS and Linux with docker-compose)

### VSCode Setup

#### Prerequisites

1. Install the [**PHP Debug**](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug) extension

#### Configuration

Create or verify `.vscode/launch.json` in your project:

```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for XDebug (Docker)",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMapping": {
                "/app": "${workspaceFolder}"
            },
            "xdebugSettings": {
                "max_data": 65535,
                "show_hidden": 1,
                "max_children": 100
            }
        }
    ]
}
```

#### How to Debug

1. **Start the Docker container**:
   ```bash
   ./setup.sh
   # or
   docker compose up -d
   ```

2. **Set breakpoints** in your PHP code by clicking left of the line number

3. **Start debugging** by:
   - Opening `.vscode/launch.json` and clicking the play button, or
   - Using keyboard shortcut: `F5`
   - Or click the Debug button in the sidebar

4. **Trigger code execution**:
   - Run a CLI command: `docker compose exec php php vendor/bin/phpunit`
   - Or make an HTTP request to your endpoint

5. **Debugger will pause** at breakpoints, allowing you to:
   - Inspect variables
   - Step through code (Step Over: F10, Step Into: F11)
   - View call stack
   - Execute commands in Debug Console

#### Example: Debugging a Unit Test

```bash
# Terminal 1: Start container and ensure debugger is listening
docker compose up -d
# VSCode: Press F5 to start listening

# Terminal 2: Run specific test
docker compose exec php php vendor/bin/phpunit tests/BusinessLogic/AdminAPI/AdminAPITest.php --filter testConnectionValidation

# In VSCode: Execution pauses at breakpoints
# View variables in Debug panel
```

### PhpStorm Setup

#### Preferences Configuration

1. Go to **PhpStorm** → **Preferences** (macOS) or **Settings** (Linux/Windows)

2. Navigate to **Languages & Frameworks** → **PHP** → **Servers**

3. Click **+** to add a new server with these settings:
   - **Name**: `localhost` (or your preference)
   - **Host**: `localhost`
   - **Port**: `80` (or your app's HTTP port)
   - **Debugger**: `Xdebug`
   - **✓ Use path mappings**
     - **Absolute path on the server**: `/app`
     - **Local path**: Your project directory path (e.g., `/Users/username/projects/integration-core`)

4. Navigate to **Languages & Frameworks** → **PHP** → **Debug**
   - Verify **Debug Port**: `9003`
   - ✓ **Accept external connections** is checked

#### How to Debug

1. **Start the Docker container**:
   ```bash
   ./setup.sh
   # or
   docker compose up -d
   ```

2. **Start listening**:
   - Go to **Run** → **Edit Configurations**
   - Click **+** and select **PHP Remote Debug**
   - Set:
     - **Name**: `Docker XDebug`
     - **Server**: Select `localhost` (created above)
     - **IDE Key**: `PHPSTORM`
   - Click **Run** → **Debug** or press `Shift+F9`

3. **Set breakpoints** in your PHP code

4. **Trigger code execution** (CLI command or HTTP request)

5. **Debugger pauses** at breakpoints with full variable inspection

### Debugging Tips

#### Verify XDebug is Working

```bash
# Check if XDebug is loaded
docker compose exec php php -m | grep xdebug

# Check XDebug configuration
docker compose exec php php -i | grep xdebug

# Check XDebug log
docker compose exec php tail -f /tmp/xdebug.log
```

#### Common Issues and Solutions

**Breakpoints Not Stopping**
- Verify path mapping is correct in your IDE settings
- Check XDebug logs: `docker compose exec php tail -f /tmp/xdebug.log`
- Ensure IDE is listening (press F5 or Debug button)

**Connection Refused**
- Ensure Docker container is running: `docker compose ps`
- Verify port 9003 is available: `lsof -i :9003`
- Check IDE is listening on port 9003

**XDebug Not Enabled**
- Verify extension is loaded: `docker compose exec php php -m | grep xdebug`
- Check PHP configuration: `docker compose exec php php -i | grep xdebug`

**IDE Key Mismatch**
- Ensure environment variables match in `.env`
- Verify IDE debugger configuration uses `PHPSTORM` key

#### Debugging a Running Service

For long-running processes or async operations:

```bash
# Terminal 1: Start container and enable debugging
docker compose up -d

# Terminal 2: Start listening in IDE (F5)

# Terminal 3: Connect to container and run interactive shell
docker compose exec -it php bash
```

---

## Development Workflow

### Typical Development Process

1. **Start Development Environment**
   ```bash
   ./setup.sh
   ```

2. **Create/Edit Code**
   - Use your IDE with proper XDebug configuration
   - Follow PSR-12 coding standards

3. **Validate Code**
   ```bash
   # Quick syntax check
   ./bin/php-syntax-check 8.1

   # Code style check and auto-fix
   docker compose exec php ./bin/phpcbf
   docker compose exec php ./bin/phpcs

   # Static analysis
   docker compose exec php ./bin/phpstan analyse src/ phpstan.neon --memory-limit=512M
   ```

4. **Run Tests**
   ```bash
   # Run specific tests
   docker compose exec php php vendor/bin/phpunit tests/BusinessLogic/

   # Or full test suite
   ./run-tests.sh
   ```

5. **Debug Issues** (if needed)
   - Set breakpoints in VS Code or PhpStorm
   - Run tests with debugger attached
   - Inspect variables and stack traces

6. **Commit Changes**
   ```bash
   git add .
   git commit -m "feat: description of changes"
   ```

### Git Pre-commit Hook (Optional)

Create `.git/hooks/pre-commit` to validate before commits:

```bash
#!/bin/bash
echo "Running syntax check..."
./bin/php-syntax-check 8.1 || exit 1

echo "Running code style check..."
docker compose exec php ./bin/phpcs || exit 1

echo "Running static analysis..."
docker compose exec php ./bin/phpstan analyse src/ phpstan.neon --memory-limit=512M || exit 1

echo "All checks passed!"
```

Make it executable:
```bash
chmod +x .git/hooks/pre-commit
```

---

## Troubleshooting

### Docker Issues

**Container won't start**
```bash
# Check logs
docker compose logs php

# Rebuild container
docker compose up -d --build

# Reset everything
docker compose down -v
./setup.sh
```

**Port already in use**
```bash
# Find process using port 9003
lsof -i :9003

# Kill it or use different port in .env
```

### PHP/XDebug Issues

**XDebug not connecting**
```bash
# Check XDebug configuration
docker compose exec php php -i | grep xdebug

# Check XDebug log
docker compose exec php cat /tmp/xdebug.log

# Test connection
docker compose exec php php -r 'var_dump(extension_loaded("xdebug"));'
```

**Wrong PHP Version**
```bash
# Verify PHP version in container
docker compose exec php php -v

# Rebuild with correct version (edit docker-compose.yml)
docker compose down -v
docker compose up -d --build
```

**Code style violations**
```bash
# See what's wrong
bin/phpcs

# Auto-fix most issues
bin/phpcbf

# Review changes
git diff
```
---

## Additional Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
- [PHPStan Documentation](https://phpstan.org/)
- [XDebug Documentation](https://xdebug.org/docs/remote)
- [Docker Documentation](https://docs.docker.com/)
- [Composer Documentation](https://getcomposer.org/doc/)

---

## Getting Help

- Check the [Debugging](#debugging) section above for debugging help
- Review test output carefully for error messages
- Check Docker logs: `docker compose logs php`
- Consult tool documentation (links above)
- Ask team members or create an issue in the repository
