import { User, Settings, Moon, Sun, Monitor, ChevronRight, LogOut, Info } from 'lucide-react';
import { useTheme } from 'next-themes';
import type { CalorieResult } from '../lib/calorieCalculator';

interface SettingsScreenProps {
  calorieResult?: CalorieResult;
}

export default function SettingsScreen({ calorieResult }: SettingsScreenProps) {
  const { theme, setTheme } = useTheme();

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-2xl font-black text-text-main">Pengaturan</h1>
        <p className="text-sm text-text-muted font-medium">
          Kelola profil dan preferensi aplikasi
        </p>
      </div>

      {/* Profil User (Dummy) */}
      <div className="bg-white/5 backdrop-blur-md rounded-[24px] border border-white/10 p-5 flex items-center gap-4">
        <div className="w-16 h-16 rounded-full bg-gradient-to-tr from-primary to-blue-400 p-[2px]">
          <div className="w-full h-full bg-base rounded-full flex items-center justify-center overflow-hidden">
            <User className="w-8 h-8 text-primary/50" />
          </div>
        </div>
        <div className="flex-1 min-w-0">
          <h2 className="text-lg font-bold text-text-main truncate">Demo User</h2>
          <p className="text-sm text-text-muted truncate">user@demo.app</p>
        </div>
      </div>

      {/* Target Info */}
      {calorieResult && (
        <div className="bg-primary/10 rounded-[20px] border border-primary/20 p-5 space-y-4">
          <div className="flex items-center gap-2 mb-2">
            <Settings className="w-5 h-5 text-primary" />
            <h3 className="text-sm font-bold text-primary">Target Nutrisi Saat Ini</h3>
          </div>
          <div className="grid grid-cols-2 gap-y-4 gap-x-2">
            <div>
              <div className="text-xs text-text-muted mb-0.5">Kalori Harian</div>
              <div className="text-lg font-black text-text-main">{calorieResult.daily_calorie_target} <span className="text-xs font-semibold text-text-muted">kkal</span></div>
            </div>
            <div>
              <div className="text-xs text-text-muted mb-0.5">Tujuan</div>
              <div className="text-base font-bold text-text-main capitalize">
                {calorieResult.goal === 'lose' ? 'Turun Berat' : calorieResult.goal === 'gain' ? 'Naik Berat' : 'Maintain'}
              </div>
            </div>
            <div>
              <div className="text-xs text-text-muted mb-0.5">Berat Saat Ini</div>
              <div className="text-base font-bold text-text-main">{calorieResult.current_weight_kg} kg</div>
            </div>
            <div>
              <div className="text-xs text-text-muted mb-0.5">Berat Target</div>
              <div className="text-base font-bold text-text-main">{calorieResult.target_weight_kg} kg</div>
            </div>
          </div>
          <button
            onClick={() => window.location.reload()}
            className="w-full py-2.5 mt-2 bg-primary text-white text-sm font-bold rounded-xl active:scale-95 transition-transform"
          >
            Hitung Ulang Target
          </button>
        </div>
      )}

      {/* Preferensi */}
      <div className="space-y-2">
        <h3 className="text-sm font-bold text-text-main px-2 mb-3">Preferensi</h3>
        
        <div className="bg-white/5 backdrop-blur-md rounded-[20px] border border-white/10 overflow-hidden divide-y divide-white/10">
          
          {/* Tampilan */}
          <div className="p-4 flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                {theme === 'dark' ? <Moon className="w-4 h-4 text-text-main" /> : theme === 'light' ? <Sun className="w-4 h-4 text-text-main" /> : <Monitor className="w-4 h-4 text-text-main"/>}
              </div>
              <span className="text-sm font-medium text-text-main">Tema Tampilan</span>
            </div>
            <div className="flex bg-white/5 rounded-lg p-1 gap-1 border border-white/10">
              {['light', 'dark', 'system'].map((t) => (
                <button
                  key={t}
                  onClick={() => setTheme(t)}
                  className={`px-3 py-1 text-xs font-medium rounded-md capitalize transition-colors ${
                    theme === t ? 'bg-primary text-white' : 'text-text-muted hover:text-text-main'
                  }`}
                >
                  {t}
                </button>
              ))}
            </div>
          </div>

          {/* About */}
          <button className="w-full p-4 flex items-center justify-between active:bg-white/5 transition-colors">
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
                <Info className="w-4 h-4 text-text-main" />
              </div>
              <span className="text-sm font-medium text-text-main">Tentang Aplikasi</span>
            </div>
            <ChevronRight className="w-4 h-4 text-text-muted" />
          </button>

        </div>
      </div>

      {/* Akun */}
      <div className="space-y-2 pt-4">
        <button
          onClick={() => window.location.reload()}
          className="w-full bg-red-500/10 border border-red-500/20 rounded-[20px] p-4 flex items-center justify-center gap-2 active:scale-95 transition-transform"
        >
          <LogOut className="w-5 h-5 text-red-500" />
          <span className="text-sm font-bold text-red-500">Keluar (Reset Demo)</span>
        </button>
      </div>

      <div className="h-24" />
    </div>
  );
}
