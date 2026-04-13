const { Client } = require('pg');
const fs = require('fs');

const client = new Client({
  host: 'db.prisma.io',
  port: 5432,
  database: 'postgres',
  user: 'f1fb86379bd7e0130f6da8596ef6d437fb4024cf24a16fd4fe72b7a7934562ce',
  password: 'sk_YtUQWogCL57ctIWQXhPid',
  ssl: { rejectUnauthorized: false },
  connectionTimeoutMillis: 10000,
  statement_timeout: 10000
});

async function runMigration() {
  try {
    console.log('Connecting to PostgreSQL at db.prisma.io:5432...');
    console.log('Timeout set to 10 seconds...');

    await client.connect();
    console.log('✓ Connected successfully!');

    const schema = fs.readFileSync(__dirname + '/schema.sql', 'utf8');
    const statements = schema
      .split(';')
      .map(s => s.trim())
      .filter(s => s.length > 0);

    console.log('Running migration...');
    for (const statement of statements) {
      console.log(`Executing: ${statement.substring(0, 50)}...`);
      await client.query(statement);
    }

    console.log('✓ Migration completed successfully!');
    process.exit(0);
  } catch (error) {
    console.error('✗ Migration failed:', error.message);
    process.exit(1);
  } finally {
    await client.end();
  }
}

runMigration();
