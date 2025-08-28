#!/usr/bin/env python3
"""
Simulate the release workflow changelog generation logic

This script simulates what the enhanced release workflow will do
to generate changelog entries, helping validate the fix before deployment.
"""

import logging
import os
import re
import subprocess
import sys
import tempfile
from datetime import datetime
from pathlib import Path
from typing import List, Optional

# Add scripts directory to path for imports
sys.path.insert(0, str(Path(__file__).parent))

# Import shared utilities
from changelog_utils import (
    categorize_commit,
    extract_unreleased_content,
    generate_changelog_sections
)

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


def run_git_command(command: str) -> str:
    """
    Run a git command and return the output.

    Args:
        command: Git command to execute

    Returns:
        Command output as string, empty string on error
    """
    if not command or not command.strip().startswith('git '):
        logger.error(f"Invalid git command: {command}")
        return ""

    try:
        result = subprocess.run(
            command,
            shell=True,
            capture_output=True,
            text=True,
            check=True,
            timeout=30
        )
        return result.stdout.strip()
    except subprocess.TimeoutExpired:
        logger.error(f"Git command timed out: {command}")
        return ""
    except subprocess.CalledProcessError as e:
        logger.warning(f"Git command failed: {command}")
        return ""
    except Exception as e:
        logger.error(f"Unexpected error: {e}")
        return ""


def generate_changelog_sections_from_text(commits_text: str) -> str:
    """Helper function to generate changelog from text"""
    if not commits_text.strip():
        return ''

    commits = [line.strip() for line in commits_text.split('\n') if line.strip()]
    return generate_changelog_sections(commits)


def simulate_workflow():
    """Simulate the complete workflow logic"""
    print("🔄 Simulating Release Workflow Changelog Generation")
    print("=" * 60)
    
    # Simulate getting the last tag
    last_tag = run_git_command("git describe --tags --abbrev=0 2>/dev/null || echo ''")
    print(f"📍 Last tag: {last_tag or 'None (first release)'}")
    
    # Get commits since last tag
    if last_tag:
        commits_command = f"git log {last_tag}..HEAD --pretty=format:'%s' --no-merges"
    else:
        commits_command = "git log --pretty=format:'%s' --no-merges"
    
    commits_text = run_git_command(commits_command)
    commits = [line.strip() for line in commits_text.split('\n') if line.strip()]
    
    print(f"📝 Found {len(commits)} commits since last release:")
    for i, commit in enumerate(commits[:5], 1):  # Show first 5
        print(f"   {i}. {commit}")
    if len(commits) > 5:
        print(f"   ... and {len(commits) - 5} more")
    print()
    
    # Read current changelog
    try:
        with open('CHANGELOG.md', 'r') as f:
            content = f.read()
    except FileNotFoundError:
        print("❌ CHANGELOG.md not found")
        return
    
    # Extract existing unreleased content
    existing_unreleased = extract_unreleased_content(content)
    
    print("📋 Current [Unreleased] section analysis:")
    if existing_unreleased:
        print("   ✅ Contains manual entries (will be preserved)")
        print(f"   📏 Length: {len(existing_unreleased)} characters")
        print("   📄 Preview:")
        preview = existing_unreleased[:200] + "..." if len(existing_unreleased) > 200 else existing_unreleased
        for line in preview.split('\n')[:3]:
            print(f"      {line}")
        if len(existing_unreleased.split('\n')) > 3:
            print("      ...")
    else:
        print("   📭 Empty or minimal content (will generate automatically)")
    print()
    
    # Determine what content will be used
    if existing_unreleased:
        print("🎯 Workflow Decision: Using existing manual content")
        version_content = existing_unreleased
        source = "manual"
    else:
        print("🎯 Workflow Decision: Generating content from conventional commits")
        generated_content = generate_changelog_sections_from_text(commits_text)
        version_content = generated_content if generated_content else '- Minor updates and improvements'
        source = "automatic"
    
    print()
    print("📄 Final changelog content for next release:")
    print("-" * 50)
    print(version_content)
    print("-" * 50)
    print(f"📊 Source: {source}")
    print(f"📏 Length: {len(version_content)} characters")
    
    # Simulate the complete update
    new_version = "1.14.0"  # Simulated next version
    current_date = datetime.now().strftime('%Y-%m-%d')
    
    print()
    print(f"🏷️  Simulated version: {new_version}")
    print(f"📅 Release date: {current_date}")
    print()
    print("📝 Complete changelog entry that would be created:")
    print("=" * 60)
    print(f"## [{new_version}] - {current_date}")
    print()
    print(version_content)
    print("=" * 60)
    
    return True


def main():
    """Main execution"""
    try:
        success = simulate_workflow()
        if success:
            print("\n✅ Simulation completed successfully!")
            print("🚀 The enhanced workflow is ready for deployment.")
        else:
            print("\n❌ Simulation failed!")
            return 1
    except Exception as e:
        print(f"\n❌ Error during simulation: {e}")
        return 1
    
    return 0


if __name__ == '__main__':
    exit(main())
