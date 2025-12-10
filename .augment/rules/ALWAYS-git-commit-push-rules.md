# ALWAYS: Git Commit and Push Rules

**Priority:** ALWAYS (Automatically applied to every commit and push operation)
**Scope:** All git commit and push commands
**Owner:** Development Team

## Overview

This rule defines standard procedures for git commit and push operations in this repository. It ensures consistency and prevents accidental inclusion of local-only files.

## 🚫 Excluded Folders and Files

### The `custom/` Folder

**NEVER include any files from the `custom/` folder in commits and pushes.**

The `custom/` folder contains local customizations that are specific to individual development environments and should NOT be pushed to the remote repository.

#### Files in `custom/` folder:
- `custom/custom.php` - Local PHP customizations
- `custom/custom-v2.php` - Local PHP customizations v2
- `custom/custom.css` - Local CSS customizations
- `custom/custom.js` - Local JavaScript customizations
- `custom/index.php` - Index file

### Commit and Push Procedure

When user requests a commit and push, follow these steps:

#### Step 1: Check Git Status
```bash
git status
```

#### Step 2: Identify Changes
Review all modified/added/deleted files and categorize them:
- ✅ **Allowed**: Files outside `custom/` folder
- ❌ **Excluded**: Files inside `custom/` folder

#### Step 3: Stage Only Allowed Files
```bash
# Stage specific files (excluding custom/ folder)
git add <file1> <file2> ...

# OR use pathspec to exclude custom folder
git add . -- ':!custom/*'
```

#### Step 4: Verify Staged Files
```bash
git status
```
Confirm that NO files from `custom/` folder are staged.

#### Step 5: Commit with Descriptive Message
```bash
git commit -m "<type>(<scope>): <description>"
```

#### Step 6: Push to Remote
```bash
git push origin <branch-name>
```

## ⚠️ Warning Messages

If changes in `custom/` folder are detected, display this warning:

```
⚠️ PERHATIAN: Ditemukan perubahan di folder `custom/`
File-file berikut TIDAK akan disertakan dalam commit:
- custom/file1.php
- custom/file2.css
...

Folder `custom/` adalah untuk kustomisasi lokal dan tidak boleh di-push ke repository.
```

## 📋 Checklist Before Commit

- [ ] Run `git status` to see all changes
- [ ] Identify any changes in `custom/` folder
- [ ] Stage ONLY files outside `custom/` folder
- [ ] Verify staged files do not include `custom/*`
- [ ] Write descriptive commit message following conventional commits
- [ ] Push to the correct branch

## 🔄 Example Workflow

### Scenario: User has changes in multiple locations including `custom/`

```bash
# 1. Check status
$ git status
modified: assets/css/style.css
modified: custom/custom.css      # ❌ EXCLUDE
modified: includes/functions.php
modified: custom/custom.php      # ❌ EXCLUDE

# 2. Stage only allowed files
$ git add assets/css/style.css includes/functions.php

# 3. Verify
$ git status
Changes to be committed:
  modified: assets/css/style.css
  modified: includes/functions.php

Changes not staged for commit:
  modified: custom/custom.css    # ✅ Correctly excluded
  modified: custom/custom.php    # ✅ Correctly excluded

# 4. Commit and push
$ git commit -m "feat: update styles and functions"
$ git push origin <branch>
```

## 📝 Notes

- This rule applies to ALL commit and push operations
- The `custom/` folder is for local development only
- Never use `git add .` or `git add -A` without excluding `custom/`
- Always verify staged files before committing

