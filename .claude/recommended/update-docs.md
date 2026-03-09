# Update Docs Rule (BLOCKING)

ALWAYS update README.md + CLAUDE.md before any push. This is a hard gate equivalent to code-safety rules.

## What to Update

### README.md
- New features or capabilities added this session
- Changed directory structure or file layout
- New environment variables or configuration options
- Updated testing commands

### CLAUDE.md
- New rules or constraints discovered this session
- Changes to architecture boundaries or module conventions
- Updated commit or workflow guidance

### .claude/recommended/
- Any changes to enforcement rules or pre-push gate steps
- New character limit adjustments

## Enforcement

Treat doc updates as BLOCKING — same weight as a failing test:
1. Before commit: verify README.md + CLAUDE.md are accurate
2. Before push: run the pre-push-gate checklist
3. If docs are stale → update them FIRST, then push

Do NOT defer doc updates to a follow-up commit.