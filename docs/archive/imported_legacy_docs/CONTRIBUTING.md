# Contributing to APS Dream Home

Thank you for your interest in contributing to APS Dream Home! We appreciate your time and effort in helping us improve the system.

## Table of Contents

1. [Code of Conduct](#code-of-conduct)
2. [Getting Started](#getting-started)
3. [Development Workflow](#development-workflow)
4. [Coding Standards](#coding-standards)
5. [Commit Message Guidelines](#commit-message-guidelines)
6. [Pull Request Process](#pull-request-process)
7. [Reporting Bugs](#reporting-bugs)
8. [Feature Requests](#feature-requests)
9. [Testing](#testing)
10. [Documentation](#documentation)

## Code of Conduct

By participating in this project, you are expected to uphold our [Code of Conduct](CODE_OF_CONDUCT.md). Please report any unacceptable behavior to [conduct@apsdreamhome.com](mailto:conduct@apsdreamhome.com).

## Getting Started

1. **Fork** the repository on GitHub
2. **Clone** your fork locally
   ```bash
   git clone https://github.com/your-username/aps-dream-home.git
   cd aps-dream-home
   ```
3. **Set up** the development environment
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```
4. **Configure** your local environment
5. **Create** a branch for your changes
   ```bash
   git checkout -b feature/your-feature-name
   ```

## Development Workflow

1. **Fetch** the latest changes from upstream
   ```bash
   git fetch upstream
   git merge upstream/main
   ```
2. **Make** your changes
3. **Test** your changes
4. **Commit** your changes
5. **Push** to your fork
   ```bash
   git push origin feature/your-feature-name
   ```
6. **Open** a Pull Request

## Coding Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard
- Write meaningful variable and function names
- Add comments for complex logic
- Keep functions and methods small and focused
- Write unit tests for new features

## Commit Message Guidelines

Use the following format for commit messages:

```
<type>(<scope>): <subject>

[optional body]

[optional footer]
```

### Types
- **feat**: A new feature
- **fix**: A bug fix
- **docs**: Documentation only changes
- **style**: Changes that do not affect the meaning of the code
- **refactor**: A code change that neither fixes a bug nor adds a feature
- **perf**: A code change that improves performance
- **test**: Adding missing tests or correcting existing tests
- **chore**: Changes to the build process or auxiliary tools

### Examples
```
feat(auth): add password reset functionality
fix(api): resolve 500 error on user registration
docs(readme): update installation instructions
```

## Pull Request Process

1. Ensure your code follows the coding standards
2. Update the documentation as needed
3. Add tests for your changes
4. Ensure all tests pass
5. Submit your pull request with a clear description
6. Reference any related issues

## Reporting Bugs

1. Check if the bug has already been reported
2. Open a new issue with a clear title and description
3. Include steps to reproduce the issue
4. Add screenshots if applicable
5. Specify your environment (OS, PHP version, etc.)

## Feature Requests

1. Check if the feature has already been requested
2. Open a new issue with a clear description
3. Explain why this feature would be useful
4. Include any relevant examples or references

## Testing

Run the test suite:
```bash
phpunit
```

## Documentation

Update the relevant documentation in the `docs/` directory when making changes to the codebase.

## Questions?

Feel free to reach out to [dev@apsdreamhome.com](mailto:dev@apsdreamhome.com) with any questions.
