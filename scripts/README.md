# Changelog Generation Scripts

This directory contains scripts for automated changelog generation and maintenance following the Keep a Changelog format with conventional commit parsing.

## Scripts Overview

### Core Scripts

#### `changelog_utils.py`
**Shared utilities module** - Contains common functions and constants used across all changelog scripts.

**Key Features:**
- Conventional commit parsing and categorization
- Changelog section generation
- Input validation and error handling
- Type annotations for better maintainability

**Functions:**
- `categorize_commit(commit_msg)` - Categorize conventional commits
- `extract_unreleased_content(content)` - Extract [Unreleased] section content
- `generate_changelog_sections(commits)` - Generate formatted changelog sections
- `validate_version_format(version)` - Validate semantic version format
- `clean_commit_message(commit_msg)` - Clean commit messages

#### `generate-changelog.py`
**Standalone changelog generator** - Manual tool for generating and updating changelog entries.

**Usage:**
```bash
# Update [Unreleased] section with recent commits
python3 scripts/generate-changelog.py --update-unreleased

# Generate changelog from commit range
python3 scripts/generate-changelog.py --from-commits v1.0.0..HEAD

# Generate changelog since specific tag
python3 scripts/generate-changelog.py --since-tag v1.0.0

# Output to file
python3 scripts/generate-changelog.py --from-commits v1.0.0..HEAD --output preview.md
```

**Features:**
- Input validation and security checks
- Comprehensive error handling and logging
- Timeout protection for git commands
- Multiple output formats

#### `test-changelog-generation.py`
**Test suite** - Comprehensive tests for changelog generation functionality.

**Usage:**
```bash
python3 scripts/test-changelog-generation.py
```

**Test Coverage:**
- Commit message categorization
- Changelog section generation
- Unreleased content extraction
- Version format validation
- Edge cases and error conditions

#### `simulate-release-workflow.py`
**Workflow simulator** - Simulates the release workflow changelog generation process.

**Usage:**
```bash
python3 scripts/simulate-release-workflow.py
```

**Features:**
- Simulates complete workflow logic
- Shows decision-making process
- Previews generated content
- Validates workflow behavior

## Architecture

### Shared Utilities Pattern

All scripts use the `changelog_utils.py` module to ensure consistency and avoid code duplication:

```python
from changelog_utils import (
    categorize_commit,
    extract_unreleased_content,
    generate_changelog_sections
)
```

### Error Handling Strategy

- **Input Validation**: All inputs are validated before processing
- **Security Checks**: Git commands are validated to prevent injection
- **Timeout Protection**: Commands have 30-second timeouts
- **Comprehensive Logging**: Detailed error messages and progress tracking
- **Graceful Degradation**: Scripts continue operation when possible

### Type Safety

All scripts use Python type annotations for better maintainability:

```python
def categorize_commit(commit_msg: str) -> Tuple[str, str]:
def generate_changelog_sections(commits: List[str]) -> str:
def validate_version_format(version: str) -> bool:
```

## Conventional Commit Mapping

The scripts automatically map conventional commit types to changelog categories:

| Commit Type | Changelog Section | Examples |
|-------------|------------------|----------|
| `feat:`, `feature:`, `add:` | **Added** | New features |
| `fix:`, `bugfix:`, `hotfix:` | **Fixed** | Bug fixes |
| `docs:`, `doc:` | **Documentation** | Documentation changes |
| `style:`, `refactor:`, `perf:`, `test:`, `chore:`, `ci:`, `build:` | **Changed** | Improvements |
| `security:` | **Security** | Security fixes |
| `feat!:`, `fix!:` (breaking) | **Changed** | Breaking changes with `**BREAKING:**` prefix |

## Usage Examples

### Basic Workflow

1. **Development**: Use conventional commits during development
2. **Manual Enhancement**: Optionally add detailed entries to `[Unreleased]` section
3. **Release**: Workflow automatically generates or preserves changelog content
4. **Validation**: Use test suite to verify functionality

### Manual Changelog Generation

```bash
# Generate preview of what would be in next release
python3 scripts/generate-changelog.py --since-tag v1.0.0 --output next-release.md

# Update unreleased section with recent commits
python3 scripts/generate-changelog.py --update-unreleased

# Test the functionality
python3 scripts/test-changelog-generation.py
```

### Workflow Simulation

```bash
# Simulate what the release workflow will do
python3 scripts/simulate-release-workflow.py
```

## Best Practices

### For Developers

1. **Use Conventional Commits**: Follow the conventional commit format for automatic categorization
2. **Add Manual Entries**: For major features, add detailed descriptions to `[Unreleased]` section
3. **Run Tests**: Use the test suite to validate changes
4. **Simulate Workflow**: Preview what the release workflow will generate

### For Maintainers

1. **Regular Testing**: Run test suite after any changes to scripts
2. **Validate Workflow**: Use simulation script before releases
3. **Monitor Logs**: Check workflow logs for any issues
4. **Update Documentation**: Keep documentation current with any changes

## Security Considerations

- **Command Validation**: All git commands are validated before execution
- **Input Sanitization**: User inputs are sanitized and validated
- **Timeout Protection**: Commands have timeouts to prevent hanging
- **Path Validation**: File paths are validated to prevent directory traversal
- **Error Handling**: Sensitive information is not exposed in error messages

## Troubleshooting

### Common Issues

**Script Import Errors:**
```bash
# Ensure you're running from repository root
cd /path/to/repository
python3 scripts/script-name.py
```

**Git Command Failures:**
- Check that you're in a git repository
- Verify git is installed and accessible
- Check network connectivity for remote operations

**Permission Errors:**
- Ensure scripts have execute permissions: `chmod +x scripts/*.py`
- Check file system permissions for CHANGELOG.md

### Debug Mode

Enable debug logging by setting the log level:

```python
import logging
logging.basicConfig(level=logging.DEBUG)
```

## Contributing

When modifying these scripts:

1. **Update Shared Utilities**: Add common functionality to `changelog_utils.py`
2. **Add Tests**: Update test suite for any new functionality
3. **Update Documentation**: Keep this README and other docs current
4. **Validate Changes**: Run full test suite before committing
5. **Follow Patterns**: Maintain consistency with existing code style

## Dependencies

- **Python 3.7+**: Required for type annotations and modern features
- **Git**: Required for repository operations
- **Standard Library Only**: No external dependencies for maximum compatibility

All scripts are designed to work with the Python standard library only to ensure maximum compatibility and minimal setup requirements.
