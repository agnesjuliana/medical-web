import { useEffect, useState } from 'react'
import './App.css'

interface User {
  id: number
  name: string
  email: string
}

function App() {
  const [user, setUser] = useState<User | null>(null)
  const [count, setCount] = useState(0)

  useEffect(() => {
    // Get user data passed from PHP
    const userData = (window as any).__USER__ as User | null
    setUser(userData)
  }, [])

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-900 to-slate-800">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {/* Header */}
        <div className="mb-12">
          <h1 className="text-4xl font-bold text-white mb-2">Modul 8</h1>
          <p className="text-gray-400">React + Vite + TypeScript Application</p>
        </div>

        {/* User Info Card */}
        {user && (
          <div className="bg-slate-700 rounded-lg p-6 mb-8 border border-slate-600">
            <h2 className="text-xl font-semibold text-white mb-4">Welcome, {user.name}!</h2>
            <div className="space-y-2 text-gray-300">
              <p><span className="font-medium text-white">ID:</span> {user.id}</p>
              <p><span className="font-medium text-white">Email:</span> {user.email}</p>
            </div>
          </div>
        )}

        {/* Counter Demo */}
        <div className="bg-slate-700 rounded-lg p-6 border border-slate-600">
          <h2 className="text-xl font-semibold text-white mb-4">Interactive Demo</h2>
          <button
            onClick={() => setCount((c) => c + 1)}
            className="px-6 py-3 bg-cyan-600 hover:bg-cyan-700 text-white font-medium rounded-lg transition-colors"
          >
            Count: {count}
          </button>
          <p className="text-gray-400 mt-4 text-sm">
            Try hot module replacement (HMR): edit this file and save to see changes instantly!
          </p>
        </div>

        {/* Dev Tips */}
        <div className="mt-8 bg-blue-900/30 border border-blue-700 rounded-lg p-6">
          <h3 className="text-lg font-semibold text-blue-300 mb-3">Development Tips</h3>
          <ul className="text-blue-200 space-y-2 text-sm">
            <li>✓ Run <code className="bg-slate-800 px-2 py-1 rounded">npm run dev</code> to start Vite dev server on port 5173</li>
            <li>✓ Main PHP server runs on port 8000</li>
            <li>✓ PHP will proxy requests to Vite for HMR</li>
            <li>✓ User authentication handled by PHP backend</li>
          </ul>
        </div>
      </div>
    </div>
  )
}

export default App
