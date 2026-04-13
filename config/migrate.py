#!/usr/bin/env python3
import psycopg2
import sys
import time

# Database connection parameters
conn_params = {
    'host': 'db.prisma.io',
    'port': 5432,
    'database': 'postgres',
    'user': 'f1fb86379bd7e0130f6da8596ef6d437fb4024cf24a16fd4fe72b7a7934562ce',
    'password': 'sk_YtUQWogCL57ctIWQXhPid',
    'sslmode': 'require',
    'connect_timeout': 10
}

def run_migration():
    try:
        print('Connecting to PostgreSQL at db.prisma.io:5432...')
        conn = psycopg2.connect(**conn_params)
        cursor = conn.cursor()
        print('✓ Connected successfully!')

        # Read schema file
        with open(__file__.replace('migrate.py', 'schema.sql'), 'r') as f:
            schema = f.read()

        # Split by semicolon
        statements = [s.strip() for s in schema.split(';') if s.strip()]

        print('Running migration...')
        for statement in statements:
            print(f"Executing: {statement[:50]}...")
            cursor.execute(statement)

        conn.commit()
        cursor.close()
        conn.close()
        print('✓ Migration completed successfully!')
        sys.exit(0)

    except psycopg2.OperationalError as e:
        print(f'✗ Connection failed: {e}')
        sys.exit(1)
    except Exception as e:
        print(f'✗ Migration failed: {e}')
        sys.exit(1)

if __name__ == '__main__':
    run_migration()
