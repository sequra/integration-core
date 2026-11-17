#!/usr/bin/env bash
set -uo pipefail

cd "$(git rev-parse --show-toplevel 2>/dev/null)" || { echo "pre-pr-gate: COULD NOT RUN — not inside a git repository"; exit 2; }

usage() {
  cat <<'USAGE'
Usage: pre-pr-gate.sh [--head] [--base <ref>]
       pre-pr-gate.sh --stamp-only [--head]

Runs the checks of integration-core inside this worktree's PHP 7.2 container (docker compose service
php): the PHP 7.2 syntax check and phpcs on the changed PHP files, the full phpstan analysis and the
full phpunit suite. A green run stamps the fingerprint of the tree it checked, and a later run on the
same tree returns green without running anything again.

  --head        Push mode: the tree being pushed is HEAD, so refuse when the working tree differs.
  --stamp-only  Run no check: exit 0 when the tree is the one the gate stamped green, 1 otherwise.
  --base <ref>  Diff base for the changed files. Default: the merge base of HEAD with origin/HEAD,
                or with origin/master when origin/HEAD is not set.

Exit: 0 green, 1 a check failed, 2 the gate could not run. The last line starts with
"pre-pr-gate: GREEN", "pre-pr-gate: RED" or "pre-pr-gate: COULD NOT RUN".
USAGE
}

HEAD_MODE=0
STAMP_ONLY=0
BASE=""
while [ $# -gt 0 ]; do
  case "$1" in
    --head) HEAD_MODE=1; shift ;;
    --stamp-only) STAMP_ONLY=1; shift ;;
    --base) BASE="${2:-}"; shift 2 || shift ;;
    -h|--help) usage; exit 0 ;;
    *) usage; echo "pre-pr-gate: COULD NOT RUN — unknown argument $1"; exit 2 ;;
  esac
done

could_not_run() {
  echo "pre-pr-gate: COULD NOT RUN — $1"
  exit 2
}

STAMP="$(git rev-parse --git-path pre-pr-gate-stamp)"
NOT_SHIPPED=(CLAUDE.md CLAUDE.local.md .claude)
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

fingerprint() {
  cp -p "$(git rev-parse --git-path index)" "$WORK/index" &&
    GIT_INDEX_FILE="$WORK/index" git add -A &&
    GIT_INDEX_FILE="$WORK/index" git reset -q -- "${NOT_SHIPPED[@]}" &&
    GIT_INDEX_FILE="$WORK/index" git write-tree
}

TREE="$(fingerprint)" || could_not_run "could not fingerprint the working tree"

if [ "$HEAD_MODE" = 1 ] && [ "$TREE" != "$(git rev-parse 'HEAD^{tree}')" ]; then
  echo "The working tree differs from HEAD, so the tree on disk is not the tree being pushed."
  echo "Commit or discard these changes, then run the gate again:"
  git status --short
  could_not_run "the working tree differs from HEAD"
fi

tree_stamped() { [ -f "$STAMP" ] && [ "$(cat "$STAMP")" = "$TREE" ]; }

if [ "$STAMP_ONLY" = 1 ]; then
  if tree_stamped; then
    echo "pre-pr-gate: GREEN — tree $TREE already passed the gate"
    exit 0
  fi
  echo "pre-pr-gate: RED — tree $TREE is not the tree the gate last passed green"
  exit 1
fi

if tree_stamped; then
  echo "pre-pr-gate: GREEN — tree $TREE already passed the gate"
  exit 0
fi

[ -n "$BASE" ] || BASE="$(git merge-base origin/HEAD HEAD 2>/dev/null || git merge-base origin/master HEAD 2>/dev/null)"
[ -n "$BASE" ] || could_not_run "no merge base with origin/HEAD or origin/master; pass --base <ref>"

docker compose exec -T php true >/dev/null 2>&1 ||
  could_not_run "the php service of this worktree is not running; run ./setup.sh and ./bin/composer install in $(pwd)"
docker compose exec -T php test -x vendor/bin/phpunit ||
  could_not_run "vendor/ is not installed in this worktree; run ./bin/composer install in $(pwd)"

FAILED=()
check() {
  local name="$1" started=$SECONDS out code
  shift
  out="$("$@" 2>&1)"
  code=$?
  if [ "$code" -eq 0 ]; then
    echo "== $name: ok ($((SECONDS - started))s)"
    return 0
  fi
  echo "== $name: FAILED (exit $code, $((SECONDS - started))s)"
  printf '%s\n' "$out" | tail -n 80
  FAILED+=("$name")
}

CHANGED_PHP=()
while IFS= read -r f; do
  [ -f "$f" ] && CHANGED_PHP+=("$f")
done < <(
  git diff --name-only --diff-filter=d "$BASE" -- '*.php'
  git ls-files --others --exclude-standard -- '*.php'
)

if [ ${#CHANGED_PHP[@]} -eq 0 ]; then
  echo "== syntax 7.2: ok (no changed PHP files)"
  echo "== phpcs: ok (no changed PHP files)"
else
  check "syntax 7.2" docker compose exec -T php sh -c 'rc=0; for f in "$@"; do php -l -n "$f" >/dev/null || rc=1; done; exit $rc' _ "${CHANGED_PHP[@]}"
  check phpcs docker compose exec -T php sh -c 'vendor/bin/phpcs --standard=.phpcs.xml.dist --warning-severity=0 --no-colors --report=emacs "$@"' _ "${CHANGED_PHP[@]}"
  check "phpcbf clean" docker compose exec -T php sh -c 'out="$(vendor/bin/phpcs --standard=.phpcs.xml.dist --report=diff -q --no-colors "$@" 2>&1)"; [ -z "$out" ] && exit 0; printf "%s\n" "$out"; echo "The pre-commit hook runs phpcbf, which would change these files and so the committed tree: run vendor/bin/phpcbf --standard=.phpcs.xml.dist on them."; exit 1' _ "${CHANGED_PHP[@]}"
fi
check phpstan docker compose exec -T php sh -c 'vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=1G --no-progress --error-format=raw'
check phpunit docker compose exec -T php sh -c 'vendor/bin/phpunit --configuration phpunit.xml --colors=never'

if [ ${#FAILED[@]} -gt 0 ]; then
  echo "pre-pr-gate: RED — failed: ${FAILED[*]}"
  exit 1
fi
if [ "$(fingerprint)" != "$TREE" ]; then
  echo "The checks changed the working tree, so the checked tree is gone; not stamping:"
  git status --short
  could_not_run "the checks changed the working tree"
fi
printf '%s\n' "$TREE" > "$STAMP"
echo "pre-pr-gate: GREEN — stamped tree $TREE"
