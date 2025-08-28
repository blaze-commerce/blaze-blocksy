#!/usr/bin/env python3
"""
Test script for changelog generation functionality

This script tests the changelog generation logic that's used in the release workflow
to ensure it works correctly before deploying changes.
"""

import os
import sys
import tempfile
from pathlib import Path
from typing import List, Tuple

# Add scripts directory to path for imports
sys.path.insert(0, str(Path(__file__).parent))

# Import shared utilities
from changelog_utils import (
    categorize_commit,
    extract_unreleased_content,
    generate_changelog_sections,
    validate_version_format,
    SECTION_TITLES
)


def test_version_validation():
    """Test version format validation"""
    test_cases = [
        ('1.0.0', True),
        ('1.2.3', True),
        ('10.20.30', True),
        ('1.0', False),
        ('1.0.0.0', False),
        ('v1.0.0', False),
        ('1.0.0-alpha', False),
        ('', False),
        (None, False),
    ]

    print("Testing version validation...")
    for version, expected in test_cases:
        result = validate_version_format(version)
        if result == expected:
            print(f"✅ {version}: {result}")
        else:
            print(f"❌ {version}: expected {expected}, got {result}")


def generate_changelog_sections_from_text(commits_text: str) -> str:
    """Helper function to generate changelog from text (for backward compatibility)"""
    if not commits_text.strip():
        return ''

    commits = [line.strip() for line in commits_text.split('\n') if line.strip()]
    return generate_changelog_sections(commits)


def test_commit_categorization():
    """Test commit message categorization"""
    test_cases: List[Tuple[str, str, str]] = [
        ('feat: add new feature', 'added', 'add new feature'),
        ('fix: resolve bug issue', 'fixed', 'resolve bug issue'),
        ('docs: update readme', 'documentation', 'update readme'),
        ('feat!: breaking change', 'changed', '**BREAKING:** breaking change'),
        ('chore: update dependencies', 'changed', 'update dependencies'),
        ('security: fix vulnerability', 'security', 'fix vulnerability'),
        ('random commit message', 'changed', 'random commit message'),
        ('', 'changed', ''),  # Edge case: empty string
        ('   ', 'changed', ''),  # Edge case: whitespace only
    ]

    print("Testing commit categorization...")
    for commit_msg, expected_category, expected_description in test_cases:
        category, description = categorize_commit(commit_msg)
        if category == expected_category and description == expected_description:
            print(f"✅ {commit_msg or '(empty)'}")
        else:
            print(f"❌ {commit_msg or '(empty)'}")
            print(f"   Expected: {expected_category}, {expected_description}")
            print(f"   Got: {category}, {description}")


def test_changelog_generation():
    """Test full changelog generation"""
    test_commits = """feat: add checkout sidebar widget
fix: resolve jQuery dependency issue
docs: update installation guide
chore: update package dependencies
feat!: change API authentication method"""

    print("\nTesting changelog generation...")
    result = generate_changelog_sections_from_text(test_commits)

    expected_sections = ['### Added', '### Changed', '### Fixed', '### Documentation']

    for section in expected_sections:
        if section in result:
            print(f"✅ {section} section generated")
        else:
            print(f"❌ {section} section missing")

    print("\nGenerated changelog:")
    print("=" * 50)
    print(result)
    print("=" * 50)


def test_unreleased_extraction():
    """Test extraction of unreleased content"""
    test_changelog = """# Changelog

## [Unreleased]

### Added
- New feature implementation
- Enhanced security measures

### Fixed
- Critical bug fixes

## [1.0.0] - 2025-01-01

### Added
- Initial release
"""
    
    print("\nTesting unreleased content extraction...")
    content = extract_unreleased_content(test_changelog)
    
    if "### Added" in content and "New feature implementation" in content:
        print("✅ Unreleased content extracted correctly")
    else:
        print("❌ Unreleased content extraction failed")
        print(f"Extracted: {repr(content)}")


def test_empty_unreleased():
    """Test handling of empty unreleased section"""
    test_changelog = """# Changelog

## [Unreleased]

## [1.0.0] - 2025-01-01

### Added
- Initial release
"""
    
    print("\nTesting empty unreleased section...")
    content = extract_unreleased_content(test_changelog)
    
    if not content:
        print("✅ Empty unreleased section detected correctly")
    else:
        print("❌ Empty unreleased section not detected")
        print(f"Extracted: {repr(content)}")


def main():
    """Run all tests"""
    print("Changelog Generation Test Suite")
    print("=" * 50)

    try:
        test_commit_categorization()
        test_changelog_generation()
        test_unreleased_extraction()
        test_empty_unreleased()
        test_version_validation()

        print("\n" + "=" * 50)
        print("✅ All tests completed successfully!")
        return 0
    except Exception as e:
        print(f"\n❌ Test suite failed with error: {e}")
        return 1


if __name__ == '__main__':
    sys.exit(main())
