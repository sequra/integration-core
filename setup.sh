#!/bin/bash
if [ ! -f .env ]; then
    cp .env.sample .env
fi

docker compose up -d --build || exit 1