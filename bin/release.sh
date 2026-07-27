#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

check_deps() {
  if ! command -v git-cliff >/dev/null 2>&1; then
    echo "Error: git-cliff is not installed. Install it with 'brew install git-cliff'." >&2
    exit 1
  fi
}

check_clean() {
  local status
  status=$(jj status 2>/dev/null || echo "not a jj repo")
  if echo "$status" | grep -q "Working copy changes"; then
    echo "Error: Working copy has uncommitted changes. Commit or shelve them first." >&2
    exit 1
  fi
  if echo "$status" | grep -q "There are unresolved conflicts"; then
    echo "Error: There are unresolved conflicts." >&2
    exit 1
  fi
}

check_unreleased() {
  local last_tag
  last_tag=$(git describe --tags --abbrev=0 2>/dev/null) || return 0
  if [ -z "$(git log "$last_tag"..HEAD --oneline)" ]; then
    echo "Error: No commits since $last_tag. Nothing to release." >&2
    exit 1
  fi
}

bumped_version() {
  git-cliff --bumped-version 2>/dev/null || {
    echo "Error: git-cliff failed to determine next version." >&2
    exit 1
  }
}

generate_changelog() {
  local version=$1
  git-cliff --unreleased --tag "$version" --prepend CHANGELOG.md
}

main() {
  check_deps
  check_clean
  check_unreleased

  local version
  version=$(bumped_version)
  echo "Next version: $version"
  echo ""

  generate_changelog "$version"

  echo "=== CHANGELOG.md changes ==="
  git diff CHANGELOG.md 2>/dev/null | head -80
  echo ""

  read -r -p "Review CHANGELOG.md, then type 'yes' to tag: " confirm
  if [ "$confirm" != "yes" ]; then
    echo "Aborted."
    exit 1
  fi

  jj describe -m "chore: release $version"
  jj tag set "$version"

  echo ""
  echo "Tagged $version."
  echo ""
  echo "Next steps:"
  echo "  1. Push: jj git push --remote origin --allow-private --change '@'"
  echo "  2. Push tags: jj git push --remote origin --tags"
}

main "$@"
