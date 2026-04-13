import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

// Load .env file
function loadEnv() {
  const envPath = path.join(__dirname, '.env');
  const envContent = fs.readFileSync(envPath, 'utf8');
  const env = {};

  envContent.split('\n').forEach(line => {
    const trimmed = line.trim();
    if (trimmed && !trimmed.startsWith('#')) {
      const [key, ...valueParts] = trimmed.split('=');
      const value = valueParts.join('=').replace(/^["']|["']$/g, '');
      env[key.trim()] = value;
    }
  });

  return env;
}

async function runMigration() {
  try {
    console.log('=== Database Migration ===\n');

    const env = loadEnv();
    let databaseUrl = env.PRISMA_DATABASE_URL || env.DATABASE_URL || env.POSTGRES_URL;

    if (!databaseUrl) {
      throw new Error('DATABASE_URL or POSTGRES_URL not found in .env');
    }

    // Dynamic import to avoid early error if pg is not installed
    let pg;
    try {
      pg = await import('pg');
    } catch (e) {
      console.error('✗ Error: pg package not installed');
      console.error('   Run: npm install pg');
      process.exit(1);
    }

    const { Client } = pg.default;

    console.log(`Using connection string: ${databaseUrl.substring(0, 80)}...`);
    console.log('Connecting to database (timeout: 30s)...\n');

    const client = new Client({
      connectionString: databaseUrl,
      connectionTimeoutMillis: 30000,
    });
    await client.connect();
    console.log('✓ Connected successfully!\n');

    // Read schema
    const schema = fs.readFileSync(
      path.join(__dirname, 'config/schema.sql'),
      'utf8'
    );

    const statements = schema
      .split(';')
      .map(s => s.trim())
      .filter(s => s.length > 0);

    console.log(`Running ${statements.length} migration statement(s)...\n`);

    for (let i = 0; i < statements.length; i++) {
      const stmt = statements[i];
      console.log(`  [${i + 1}] Executing: ${stmt.substring(0, 50)}...`);
      await client.query(stmt);
    }

    await client.end();
    console.log('\n✓ Migration completed successfully!');
    process.exit(0);

  } catch (error) {
    console.error(`\n✗ Error: ${error.message}`);
    process.exit(1);
  }
}

runMigration();
