# Pre-Push Gate (BLOCKING)

Run this checklist BEFORE every `git push`, `gh pr create`, or PR update.

## Required Checks

- [ ] README.md updated to reflect this session's changes
- [ ] CLAUDE.md rules are current and accurate
- [ ] All `.claude/recommended/` files are up to date
- [ ] Character limits pass: `wc -c CLAUDE.md README.md .claude/recommended/*.md`
- [ ] No `custom/` files staged (run `git diff --cached --name-only | grep "^custom/"`)
- [ ] Conventional commit format used (`feat:`, `fix:`, `docs:`, `chore:`, `refactor:`)

## Commands

```bash
# Check char limits
wc -c CLAUDE.md README.md .claude/recommended/*.md

# Confirm no custom/ files staged
git diff --cached --name-only | grep "^custom/" && echo "BLOCKED: custom/ files staged"

# Verify conventional commit format on last commit
git log -1 --format="%s" | grep -E "^(feat|fix|docs|chore|refactor|style|test|perf)(\(.+\))?: .+"
```

Failing any check = BLOCKED. Fix before pushing.