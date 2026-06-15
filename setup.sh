#!/bin/bash
if [ ! -f .env ]; then
    cp .env.sample .env
fi

# Enable the shared git hooks (pre-commit and pre-push quality gates in .githooks/).
if git rev-parse --git-dir >/dev/null 2>&1; then
    chmod +x .githooks/* 2>/dev/null
    git config core.hooksPath .githooks
    echo "Git hooks enabled (core.hooksPath=.githooks)."
fi

docker compose up -d --build || exit 1