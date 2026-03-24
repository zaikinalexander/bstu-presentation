#!/usr/bin/env bash

set -euo pipefail

if [[ $# -ne 1 ]]; then
  echo "Usage: $0 <demo|prod>" >&2
  exit 1
fi

contour="$1"

case "$contour" in
  demo)
    remote_path="/opt/bstupresentation-demo/app"
    target_url="https://demo.devfit.ru/"
    ;;
  prod)
    remote_path="/opt/bstupresentation/app"
    target_url="https://presentation.bstu.ru/"
    ;;
  *)
    echo "Unknown contour: $contour" >&2
    exit 1
    ;;
esac

if ! command -v expect >/dev/null 2>&1; then
  echo "expect is required" >&2
  exit 1
fi

if [[ -z "${BSTU_SERVER_PASSWORD:-}" ]]; then
  echo "Set BSTU_SERVER_PASSWORD before deploy" >&2
  exit 1
fi

server_host="${BSTU_SERVER_HOST:-5.42.116.114}"
server_user="${BSTU_SERVER_USER:-root}"
repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
timestamp="$(date +%Y%m%d-%H%M%S)"
archive="/tmp/bstupresentation-${contour}-${timestamp}.tar.gz"
remote_archive="/tmp/$(basename "$archive")"

cleanup() {
  rm -f "$archive"
}

trap cleanup EXIT

COPYFILE_DISABLE=1 tar \
  -C "$repo_root" \
  --exclude=".DS_Store" \
  --exclude=".env" \
  --exclude="storage/database.sqlite" \
  --exclude="public/storage/database.sqlite" \
  --exclude="public/uploads/manual" \
  --exclude="public/uploads/presentations" \
  -czf "$archive" \
  app \
  public \
  bstupresent \
  database \
  scripts \
  README.md \
  .env.example \
  .gitignore \
  deploy

export BSTU_SERVER_PASSWORD

expect <<EOF
set timeout 1200
set password "$BSTU_SERVER_PASSWORD"
spawn scp -o StrictHostKeyChecking=no "$archive" "${server_user}@${server_host}:${remote_archive}"
expect "password:" { send "\$password\r" }
expect eof
EOF

expect <<EOF
set timeout 1200
set password "$BSTU_SERVER_PASSWORD"
spawn ssh -o StrictHostKeyChecking=no "${server_user}@${server_host}" "set -e; tar -xzf ${remote_archive} -C ${remote_path}; chown -R bstupresentation:bstupresentation ${remote_path}; rm -f ${remote_archive}"
expect "password:" { send "\$password\r" }
expect eof
EOF

curl -I -s "$target_url" >/dev/null
echo "Deploy completed: ${contour} -> ${target_url}"
