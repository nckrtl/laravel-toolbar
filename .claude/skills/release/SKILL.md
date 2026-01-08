---
name: release
description: Publish a new release of nckrtl/laravel-toolbar to Packagist. Handles CHANGELOG updates, git tagging via GitHub CLI, and creating GitHub releases.
allowed-tools: Read, Write, Edit, Bash, Glob, Grep
---

# Release Workflow for nckrtl/laravel-toolbar

Publish new versions to Packagist via GitHub releases.

## Quick Release

```bash
# 1. Check current version
git fetch --tags
git tag --sort=-v:refname | head -3

# 2. Determine next version (see Version Types below)
VERSION="0.1.1"  # Set appropriately

# 3. Update CHANGELOG.md with new version section

# 4. Commit changes (if any uncommitted work)
git add -A
git commit -m "Release v$VERSION"
git push origin main

# 5. Create GitHub release (auto-publishes to Packagist)
gh release create "v$VERSION" --title "v$VERSION" --notes-file - <<EOF
## What's Changed

- List changes here
- See CHANGELOG.md for details

**Full Changelog**: https://github.com/nckrtl/laravel-toolbar/compare/v0.1.0...v$VERSION
EOF
```

## Version Types

| Type | When to Use | Example |
|------|-------------|---------|
| Patch (0.0.x) | Bug fixes, docs, minor changes | 0.1.0 → 0.1.1 |
| Minor (0.x.0) | New features, backwards compatible | 0.1.0 → 0.2.0 |
| Major (x.0.0) | Breaking changes | 0.1.0 → 1.0.0 |

## CHANGELOG Format

Always update `CHANGELOG.md` before releasing:

```markdown
## v0.1.1 - YYYY-MM-DD

### New Features
- Added feature X

### Bug Fixes
- Fixed issue Y

### Documentation
- Updated README
```

## How It Works

1. **GitHub Release**: Creating a release with `gh release create` creates a git tag
2. **Packagist Webhook**: Packagist is configured to auto-update when new tags are pushed
3. **Composer Update**: Users can now `composer update nckrtl/laravel-toolbar`

## IMPORTANT: Use GitHub CLI for All Git Operations

When the repository lacks local git user config, **always use `gh` CLI** instead of raw git commands:

### Creating Commits via GitHub API

```bash
# Get current commit SHA
CURRENT_SHA=$(gh api repos/nckrtl/laravel-toolbar/git/refs/heads/main --jq '.object.sha')

# Create a blob for each changed file
BLOB_SHA=$(gh api repos/nckrtl/laravel-toolbar/git/blobs \
  -f content="$(base64 -w0 path/to/file)" \
  -f encoding="base64" \
  --jq '.sha')

# Create tree, commit, and update ref...
# (Complex - prefer having local git config set up)
```

### Preferred: Ensure Local Git Config Exists

Each project should have local git config in `.git/config`:
```ini
[user]
    name = Your Name
    email = your@email.com
```

If missing, ask the user to set it up rather than configuring it yourself.

## Troubleshooting

### Packagist Not Updating

1. Check webhook is configured: GitHub repo → Settings → Webhooks
2. Verify tag was pushed: `git ls-remote --tags origin`
3. Manual trigger: Visit packagist.org and click "Update"

### Release Already Exists

```bash
# Delete and recreate if needed
gh release delete v0.1.1 --yes
gh release create v0.1.1 --title "v0.1.1" --notes "..."
```

### View Recent Releases

```bash
gh release list --limit 5
```

## Monitoring

### Check Release Status
```bash
gh release view v0.1.1
```

### View Release Assets
```bash
gh release view v0.1.1 --json assets
```
