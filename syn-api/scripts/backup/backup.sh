#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="${1:-$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)}"
ENV_FILE="$PROJECT_ROOT/.env"
BACKUP_ROOT="$PROJECT_ROOT/storage/backups"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Arquivo .env não encontrado: $ENV_FILE" >&2
  exit 1
fi

get_env() {
  local key="$1"
  local value

  value="$(
    grep -E "^${key}=" "$ENV_FILE" \
      | tail -n 1 \
      | cut -d= -f2-
  )"

  value="${value%\"}"
  value="${value#\"}"
  value="${value%\'}"
  value="${value#\'}"

  printf '%s' "$value"
}

DB_HOST="$(get_env DB_HOST)"
DB_PORT="$(get_env DB_PORT)"
DB_NAME="$(get_env DB_NAME)"
DB_USER="$(get_env DB_USER)"
DB_PASS="$(get_env DB_PASS)"

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"

if [[ -z "$DB_NAME" || -z "$DB_USER" ]]; then
  echo "DB_NAME ou DB_USER ausente no .env." >&2
  exit 1
fi

if command -v mariadb-dump >/dev/null 2>&1; then
  DUMP_BIN="mariadb-dump"
elif command -v mysqldump >/dev/null 2>&1; then
  DUMP_BIN="mysqldump"
else
  echo "mariadb-dump/mysqldump não encontrado." >&2
  exit 1
fi

timestamp="$(date +%Y%m%d_%H%M%S)"
work_dir="$BACKUP_ROOT/syn_$timestamp"
archive="$BACKUP_ROOT/syn_$timestamp.tar.gz"

mkdir -p "$work_dir"

echo "Criando backup do banco..."

MYSQL_PWD="$DB_PASS" \
"$DUMP_BIN" \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --user="$DB_USER" \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  --default-character-set=utf8mb4 \
  "$DB_NAME" \
  > "$work_dir/database.sql"

test -s "$work_dir/database.sql"

echo "Copiando uploads..."

if [[ -d "$PROJECT_ROOT/public/uploads" ]]; then
  cp -a \
    "$PROJECT_ROOT/public/uploads" \
    "$work_dir/uploads"
else
  mkdir -p "$work_dir/uploads"
fi

cat > "$work_dir/manifest.txt" <<EOF
sistema=SYN
criado_em=$(date --iso-8601=seconds)
banco=$DB_NAME
host=$DB_HOST
porta=$DB_PORT
arquivo_banco=database.sql
pasta_uploads=uploads
EOF

echo "Compactando backup..."

tar \
  -C "$work_dir" \
  -czf "$archive" \
  .

rm -rf "$work_dir"

sha256sum "$archive" \
  > "$archive.sha256"

echo
echo "Backup concluído:"
echo "$archive"
