#!/usr/bin/env python3
"""
Changelog Generation Script for Blaze Commerce Projects

This script helps generate and update CHANGELOG.md files following the Keep a Changelog format.
It can automatically categorize conventional commits and generate proper changelog sections.

Usage:
    python3 scripts/generate-changelog.py --help
    python3 scripts/generate-changelog.py --from-commits v1.0.0..HEAD
    python3 scripts/generate-changelog.py --update-unreleased
"""

import argparse
import logging
import re
import subprocess
import sys
from datetime import datetime
from pathlib import Path
from typing import List, Optional

# Import shared utilities
from changelog_utils import (
    categorize_commit,
    extract_unreleased_content,
    generate_changelog_sections,
    validate_version_format,
    clean_commit_message
)

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


def run_git_command(command: str) -> str:
    """
    Run a git command and return the output.

    Args:
        command: Git command to execute

    Returns:
        Command output as string, empty string on error
    """
    if not command or not isinstance(command, str):
        logger.error("Invalid git command provided")
        return ""

    try:
        # Validate that this is actually a git command for security
        if not command.strip().startswith('git '):
            logger.error(f"Command must start with 'git ': {command}")
            return ""

        result = subprocess.run(
            command,
            shell=True,
            capture_output=True,
            text=True,
            check=True,
            timeout=30  # Add timeout for safety
        )
        return result.stdout.strip()
    except subprocess.TimeoutExpired:
        logger.error(f"Git command timed out: {command}")
        return ""
    except subprocess.CalledProcessError as e:
        logger.error(f"Error running git command: {command}")
        logger.error(f"Error: {e.stderr}")
        return ""
    except Exception as e:
        logger.error(f"Unexpected error running git command: {command}")
        logger.error(f"Error: {str(e)}")
        return ""


def get_commits_since_tag(tag_range: Optional[str]) -> List[str]:
    """
    Get commit messages since a specific tag or range.

    Args:
        tag_range: Git tag range (e.g., 'v1.0.0..HEAD') or None for all commits

    Returns:
        List of commit messages
    """
    if not tag_range:
        # Get all commits if no tag specified
        command = "git log --pretty=format:'%s' --no-merges"
    else:
        # Validate tag range format for security
        if not re.match(r'^[a-zA-Z0-9._-]+\.\.[a-zA-Z0-9._-]+$', tag_range):
            logger.error(f"Invalid tag range format: {tag_range}")
            return []
        command = f"git log {tag_range} --pretty=format:'%s' --no-merges"

    commits_output = run_git_command(command)
    if not commits_output:
        return []

    return [line.strip() for line in commits_output.split('\n') if line.strip()]


def read_changelog() -> Optional[str]:
    """
    Read the current CHANGELOG.md file.

    Returns:
        Changelog content as string, or None if file not found
    """
    changelog_path = Path('CHANGELOG.md')
    if not changelog_path.exists():
        logger.error("CHANGELOG.md not found")
        return None

    try:
        with open(changelog_path, 'r', encoding='utf-8') as f:
            return f.read()
    except IOError as e:
        logger.error(f"Error reading CHANGELOG.md: {e}")
        return None


def update_unreleased_section(commits: List[str]) -> bool:
    """
    Update the [Unreleased] section with new commits.

    Args:
        commits: List of commit messages to add

    Returns:
        True if successful, False otherwise
    """
    content = read_changelog()
    if not content:
        return False

    # Check if [Unreleased] section exists
    if '## [Unreleased]' not in content:
        logger.error("[Unreleased] section not found in CHANGELOG.md")
        return False

    # Generate new content
    new_content = generate_changelog_sections(commits)
    if not new_content:
        logger.info("No new commits to add to changelog")
        return True

    # Extract existing unreleased content
    existing_content = extract_unreleased_content(content)

    # Combine existing and new content
    if existing_content:
        combined_content = f"{existing_content}\n\n{new_content}"
    else:
        combined_content = new_content

    # Replace [Unreleased] section
    pattern = r'(## \[Unreleased\])(.*?)(?=## \[|\Z)'
    replacement = f'\\1\n\n{combined_content}\n\n'

    updated_content = re.sub(pattern, replacement, content, flags=re.DOTALL)

    # Write back to file
    try:
        with open('CHANGELOG.md', 'w', encoding='utf-8') as f:
            f.write(updated_content)
        logger.info(f"Updated [Unreleased] section with {len(commits)} new commits")
        return True
    except IOError as e:
        logger.error(f"Error writing to CHANGELOG.md: {e}")
        return False


def main():
    parser = argparse.ArgumentParser(description='Generate changelog entries from conventional commits')
    parser.add_argument('--from-commits', help='Generate changelog from commit range (e.g., v1.0.0..HEAD)')
    parser.add_argument('--update-unreleased', action='store_true', help='Update [Unreleased] section with recent commits')
    parser.add_argument('--since-tag', help='Generate changelog since specific tag')
    parser.add_argument('--output', help='Output to file instead of stdout')
    
    args = parser.parse_args()
    
    if args.update_unreleased:
        # Get commits since last tag
        last_tag = run_git_command("git describe --tags --abbrev=0 2>/dev/null || echo ''")
        if last_tag:
            commits = get_commits_since_tag(f"{last_tag}..HEAD")
        else:
            commits = get_commits_since_tag("HEAD")
        
        success = update_unreleased_section(commits)
        sys.exit(0 if success else 1)
    
    elif args.from_commits:
        commits = get_commits_since_tag(args.from_commits)
        changelog_content = generate_changelog_sections(commits)
        
        if args.output:
            with open(args.output, 'w') as f:
                f.write(changelog_content)
            print(f"Changelog written to {args.output}")
        else:
            print(changelog_content)
    
    elif args.since_tag:
        commits = get_commits_since_tag(f"{args.since_tag}..HEAD")
        changelog_content = generate_changelog_sections(commits)
        
        if args.output:
            with open(args.output, 'w') as f:
                f.write(changelog_content)
            print(f"Changelog written to {args.output}")
        else:
            print(changelog_content)
    
    else:
        parser.print_help()


if __name__ == '__main__':
    main()
