# Modul 8 — React + Vite + TypeScript

Modern React application integrated with the PHP authentication system.

## Setup

### 1. Install dependencies
```bash
cd modules/modul_8/app
npm install
```

### 2. Start development servers

**Terminal 1: PHP server (port 8000)**
```bash
php -S localhost:8000
```

**Terminal 2: Vite dev server (port 5173)**
```bash
cd modules/modul_8/app
npm run dev
```

### 3. Access the application
Visit: `http://localhost:8000/modules/modul_8/`

## How It Works

- **PHP** (`index.php`): Handles authentication and passes user data to React via `window.__USER__`
- **React** (`src/App.tsx`): Modern UI components with full TypeScript support
- **Vite**: Lightning-fast dev server with HMR (Hot Module Reload)

## Development

### Available Scripts

- `npm run dev` — Start Vite dev server with HMR
- `npm run build` — Build for production (outputs to `dist/`)
- `npm run preview` — Preview production build locally
- `npm run lint` — Run ESLint

### Structure

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

To call PHP APIs from React, use the `/api` prefix (proxied by Vite):

```typescript
const response = await fetch('/api/some-endpoint.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ data: 'value' })
})
```

## Production Build

```bash
npm run build
```

Files are built to `app/dist/`. The PHP `index.php` will automatically serve the built assets when `APP_ENV` is not set to `development`.

## Authentication

User data is automatically passed from PHP to React:

```typescript
const user = (window as any).__USER__ // { id, name, email }
```

Protected pages require login via PHP's `requireLogin()` function.
