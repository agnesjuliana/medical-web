# Modul 8 — React + Vite + TypeScript

Modern React application integrated with the PHP authentication system.

## Quick Start

### 1. Build React app

```bash
cd modules/modul_8/app
npm install
npm run build
```

### 2. Start PHP server

```bash
php -S localhost:8000
```

### 3. Open in browser

Visit: `http://localhost:8000/modules/modul_8/`

## How It Works

- **PHP** (`index.php`): Handles authentication and passes user data to React via `window.__USER__`
- **React** (`src/App.tsx`): Modern UI components with full TypeScript support
- **Vite**: Lightning-fast build tool with HMR support

## Development

### Available Scripts

- `npm run build` — Build React app for production
- `npm run lint` — Run ESLint

### Project Structure

```
app/
├── src/
│   ├── App.tsx        — Main React component
│   ├── main.tsx       — React entry point
│   └── index.css      — Tailwind CSS
├── public/            — Static assets
├── vite.config.ts     — Vite configuration
├── tsconfig.json      — TypeScript configuration
└── package.json
```

## API Calls

To call PHP APIs from React:

```typescript
const response = await fetch('/api/some-endpoint.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ data: 'value' })
})
```

## Authentication

User data is automatically passed from PHP to React:

```typescript
const user = (window as any).__USER__ // { id, name, email }
```

Protected pages require login via PHP's `requireLogin()` function.
