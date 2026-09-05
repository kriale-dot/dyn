#!/usr/bin/env bash
set -euo pipefail

if [[ "${1:-}" != "--confirmar-restauracao" ]]; then
  cat >&2 <<'EOF'
Restauração bloqueada por segurança.

Uso:
  ./restore.sh --confirmar-restauracao ARQUIVO_BACKUP [PROJECT_ROOT]

ATENÇÃO: o banco atual será sobrescrito pelos dados do backup.
EOF
  exit 1
fi

BACKUP_FILE="${2:-}"
PROJECT_ROOT="${3:-$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)}"
ENV_FILE="$PROJECT_ROOT/.env"

if [[ -z "$BACKUP_FILE" || ! -f "$BACKUP_FILE" ]]; then
  echo "Backup não encontrado: $BACKUP_FILE" >&2
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

if command -v mariadb >/dev/null 2>&1; then
  MYSQL_BIN="mariadb"
elif command -v mysql >/dev/null 2>&1; then
  MYSQL_BIN="mysql"
else
  echo "mariadb/mysql não encontrado." >&2
  exit 1
fi

temp_dir="$(mktemp -d)"

cleanup() {
  rm -rf "$temp_dir"
}

trap cleanup EXIT

tar \
  -xzf "$BACKUP_FILE" \
  -C "$temp_dir"

if [[ ! -s "$temp_dir/database.sql" ]]; then
  echo "database.sql ausente ou vazio." >&2
  exit 1
fi

echo "Restaurando banco..."

MYSQL_PWD="$DB_PASS" \
"$MYSQL_BIN" \
  --host="$DB_HOST" \
  --port="$DB_PORT" \
  --user="$DB_USER" \
  "$DB_NAME" \
  < "$temp_dir/database.sql"

if [[ -d "$temp_dir/uploads" ]]; then
  echo "Restaurando uploads..."

  rm -rf "$PROJECT_ROOT/public/uploads"

  cp -a \
    "$temp_dir/uploads" \
    "$PROJECT_ROOT/public/uploads"
fi

echo
echo "Restauração concluída."
