#!/usr/bin/env python3
"""
Shared utilities for changelog generation

This module provides common functions and constants used across
changelog generation scripts to avoid code duplication.
"""

import re
from typing import Tuple, Dict, List

# Constants
MINIMAL_CONTENT_LENGTH = 10
CONVENTIONAL_COMMIT_PATTERN = re.compile(r'^(\w+)(\(.+\))?(!)?: (.+)$')

# Conventional commit type mapping to changelog categories
COMMIT_TYPE_MAPPING = {
    'feat': 'added',
    'feature': 'added',
    'add': 'added',
    'fix': 'fixed',
    'bugfix': 'fixed',
    'hotfix': 'fixed',
    'docs': 'documentation',
    'doc': 'documentation',
    'style': 'changed',
    'refactor': 'changed',
    'perf': 'changed',
    'test': 'changed',
    'chore': 'changed',
    'ci': 'changed',
    'build': 'changed',
    'revert': 'fixed',
    'security': 'security'
}

# Changelog section titles
SECTION_TITLES = {
    'added': 'Added',
    'changed': 'Changed',
    'fixed': 'Fixed',
    'documentation': 'Documentation',
    'security': 'Security'
}

# Section order for consistent output
SECTION_ORDER = ['added', 'changed', 'fixed', 'documentation', 'security']


def categorize_commit(commit_msg: str) -> Tuple[str, str]:
    """
    Categorize commit message based on conventional commit format.
    
    Args:
        commit_msg: The commit message to categorize
        
    Returns:
        Tuple of (category, description) where category is one of:
        'added', 'changed', 'fixed', 'documentation', 'security'
    """
    if not commit_msg or not isinstance(commit_msg, str):
        return 'changed', commit_msg or ''
    
    commit_msg = commit_msg.strip()
    
    # Parse conventional commit format: type(scope): description
    match = CONVENTIONAL_COMMIT_PATTERN.match(commit_msg)
    
    if not match:
        return 'changed', commit_msg
    
    commit_type = match.group(1).lower()
    description = match.group(4)
    is_breaking = match.group(3) == '!'
    
    category = COMMIT_TYPE_MAPPING.get(commit_type, 'changed')
    
    # Breaking changes always go to 'changed' with special notation
    if is_breaking:
        category = 'changed'
        description = f'**BREAKING:** {description}'
    
    return category, description


def extract_unreleased_content(content: str) -> str:
    """
    Extract content from [Unreleased] section of changelog.
    
    Args:
        content: The full changelog content
        
    Returns:
        The content of the [Unreleased] section, or empty string if minimal/missing
    """
    if not content:
        return ''
    
    # Find the [Unreleased] section
    unreleased_pattern = r'## \[Unreleased\](.*?)(?=## \[|\Z)'
    match = re.search(unreleased_pattern, content, re.DOTALL)
    
    if not match:
        return ''
    
    unreleased_content = match.group(1).strip()
    
    # Check if it's just empty lines or minimal content
    if not unreleased_content or len(unreleased_content.strip()) < MINIMAL_CONTENT_LENGTH:
        return ''
    
    return unreleased_content


def generate_changelog_sections(commits: List[str]) -> str:
    """
    Generate categorized changelog sections from commit messages.
    
    Args:
        commits: List of commit messages to categorize
        
    Returns:
        Formatted changelog sections as a string
    """
    if not commits:
        return ''
    
    # Initialize categories
    categories: Dict[str, List[str]] = {category: [] for category in SECTION_ORDER}
    
    # Categorize each commit
    for commit in commits:
        if not commit or not commit.strip():
            continue
            
        category, description = categorize_commit(commit.strip())
        if category in categories:
            categories[category].append(f'- {description}')
    
    # Generate sections
    sections = []
    
    for category in SECTION_ORDER:
        if categories[category]:
            sections.append(f'### {SECTION_TITLES[category]}')
            sections.extend(categories[category])
            sections.append('')
    
    return '\n'.join(sections).rstrip()


def validate_version_format(version: str) -> bool:
    """
    Validate semantic version format.
    
    Args:
        version: Version string to validate
        
    Returns:
        True if version follows semantic versioning format
    """
    if not version:
        return False
    
    # Semantic version pattern: MAJOR.MINOR.PATCH
    version_pattern = re.compile(r'^\d+\.\d+\.\d+$')
    return bool(version_pattern.match(version))


def clean_commit_message(commit_msg: str) -> str:
    """
    Clean commit message by removing PR numbers and commit hashes.
    
    Args:
        commit_msg: Raw commit message
        
    Returns:
        Cleaned commit message
    """
    if not commit_msg:
        return ''
    
    # Remove PR numbers and commit hashes
    cleaned = re.sub(r'\s*\(#\d+\)\s*\([a-f0-9]+\)$', '', commit_msg)
    cleaned = re.sub(r'\s*\([a-f0-9]+\)$', '', cleaned)
    
    return cleaned.strip()
