# Character Limits — .claude/ Governance Files

## Run Before Every Push

```bash
wc -c CLAUDE.md README.md .claude/recommended/*.md .claude/commands/*.md
```

## Hard Limits

| File | Max Chars |
|------|-----------|
| CLAUDE.md | 8,000 |
| .claude/recommended/*.md | 3,000 each |
| .claude/commands/*.md | 2,000 each |

## Enforcement

- Over limit → consolidate, merge, or move content to referenced external files
- NEVER delete rules to fit the limit — restructure instead
- After editing any `.claude/` file, re-run `wc -c` to confirm compliance
- Violations block push per the Documentation Gate in CLAUDE.md