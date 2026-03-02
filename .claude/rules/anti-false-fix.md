# Anti-False-Fix Rules (CRITICAL)
**Problem:** Claude says "fixed" but issue persists.
## Rules
1. **NEVER claim "fixed" without verification** — say "changes applied, please verify" if you can't test
2. **Show evidence** — always show test output or screenshot when claiming verification
3. **Check the right location** — read project structure, don't assume file paths
4. **When fix doesn't work:** re-read context, check caching, check for overrides before re-attempting
## Language
❌ "This should fix it" → ✅ "Changes applied, testing now..."
❌ "I've fixed the issue" → ✅ "Changes made. Verified working." or "Please verify"
