import { format, startOfWeek, addDays, isToday } from 'date-fns';
import { id as localeID } from 'date-fns/locale';
import { Footprints, Flame, Heart, TrendingUp, Info } from 'lucide-react';
import type { CalorieResult } from '../lib/calorieCalculator';

// ─── SVG Ring (reusable) ───
function CircleRing({
  size = 140,
  strokeWidth = 12,
  progress,
  color = 'var(--primary)',
  children,
}: {
  size?: number;
  strokeWidth?: number;
  progress: number;
  color?: string;
  children?: React.ReactNode;
}) {
  const radius = (size - strokeWidth) / 2;
  const circumference = 2 * Math.PI * radius;
  const clampedProgress = Math.min(Math.max(progress, 0), 1);
  const offset = circumference - clampedProgress * circumference;

  return (
    <div className="relative" style={{ width: size, height: size }}>
      <svg width={size} height={size} className="-rotate-90">
        <circle cx={size / 2} cy={size / 2} r={radius} fill="none" stroke="rgba(255,255,255,0.08)" strokeWidth={strokeWidth} />
        <circle cx={size / 2} cy={size / 2} r={radius} fill="none" stroke={color} strokeWidth={strokeWidth} strokeLinecap="round" strokeDasharray={circumference} strokeDashoffset={offset} className="transition-all duration-1000 ease-out" />
      </svg>
      {children && <div className="absolute inset-0 flex items-center justify-center">{children}</div>}
    </div>
  );
}

// ─── Weekly Calendar ───
function WeeklyCalendar() {
  const weekStart = startOfWeek(new Date(), { weekStartsOn: 1 });
  const days = Array.from({ length: 7 }, (_, i) => addDays(weekStart, i));
  return (
    <div className="flex items-center justify-between gap-1 px-2">
      {days.map((day) => {
        const active = isToday(day);
        return (
          <button key={day.toISOString()} className={`flex flex-col items-center gap-1 py-2 px-2.5 rounded-2xl transition-all flex-1 ${active ? 'bg-primary/15' : 'hover:bg-white/5'}`}>
            <span className={`text-xs font-bold uppercase ${active ? 'text-primary' : 'text-text-muted'}`}>{format(day, 'EEE', { locale: localeID }).slice(0, 3)}</span>
            <span className={`text-sm font-black w-8 h-8 flex items-center justify-center rounded-full ${active ? 'bg-primary text-white' : 'text-text-main'}`}>{format(day, 'd')}</span>
          </button>
        );
      })}
    </div>
  );
}

// ─── DUMMY DATA ───
const DUMMY_STEPS = 3247;
const STEPS_GOAL = 10000;
const DUMMY_CALORIES_BURNED = 180;
const DUMMY_HEALTH_SCORE = 72;

interface ProgressScreenProps {
  calorieResult?: CalorieResult;
}

export default function ProgressScreen({ calorieResult }: ProgressScreenProps) {
  const stepsProgress = DUMMY_STEPS / STEPS_GOAL;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-2xl font-black text-text-main">Progress</h1>
        <p className="text-sm text-text-muted font-medium">
          Tracking langkah & kalori terbakar
        </p>
      </div>

      {/* Weekly Calendar */}
      <WeeklyCalendar />

      {/* Steps Ring */}
      <div className="bg-white/5 backdrop-blur-md rounded-[24px] border border-white/10 p-6 flex flex-col items-center gap-4">
        <CircleRing size={180} strokeWidth={14} progress={stepsProgress} color="var(--primary)">
          <div className="text-center">
            <div className="text-3xl font-black text-text-main tabular-nums">{DUMMY_STEPS.toLocaleString()}</div>
            <div className="text-xs font-semibold text-text-muted">/ {STEPS_GOAL.toLocaleString()}</div>
            <div className="text-[10px] text-primary font-bold mt-1">langkah</div>
          </div>
        </CircleRing>
        <div className="flex items-center gap-2 text-sm text-text-muted">
          <Footprints className="w-4 h-4 text-primary" />
          <span>Target harian: <span className="font-bold text-text-main">{STEPS_GOAL.toLocaleString()}</span> langkah</span>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-2 gap-3">
        {/* Calories Burned */}
        <div className="bg-white/5 backdrop-blur-md rounded-[20px] border border-white/10 p-5 space-y-3">
          <div className="flex items-center gap-2">
            <div className="p-2 rounded-xl bg-orange-400/10">
              <Flame className="w-5 h-5 text-orange-400" />
            </div>
            <span className="text-xs font-bold text-text-muted">Terbakar</span>
          </div>
          <div className="text-2xl font-black text-text-main">{DUMMY_CALORIES_BURNED} <span className="text-sm font-semibold text-text-muted">kkal</span></div>
          <div className="h-1.5 bg-white/10 rounded-full overflow-hidden">
            <div className="h-full bg-orange-400 rounded-full" style={{ width: `${Math.min((DUMMY_CALORIES_BURNED / 500) * 100, 100)}%` }} />
          </div>
        </div>

        {/* Health Score */}
        <div className="bg-white/5 backdrop-blur-md rounded-[20px] border border-white/10 p-5 space-y-3">
          <div className="flex items-center gap-2">
            <div className="p-2 rounded-xl bg-emerald-400/10">
              <Heart className="w-5 h-5 text-emerald-400" />
            </div>
            <span className="text-xs font-bold text-text-muted">Health Score</span>
          </div>
          <div className="text-2xl font-black text-text-main">{DUMMY_HEALTH_SCORE} <span className="text-sm font-semibold text-text-muted">/ 100</span></div>
          <div className="h-1.5 bg-white/10 rounded-full overflow-hidden">
            <div className="h-full bg-emerald-400 rounded-full" style={{ width: `${DUMMY_HEALTH_SCORE}%` }} />
          </div>
        </div>
      </div>

      {/* Weight Journey */}
      {calorieResult && calorieResult.goal !== 'maintain' && (
        <div className="bg-white/5 backdrop-blur-md rounded-[20px] border border-white/10 p-5 space-y-3">
          <div className="flex items-center gap-2">
            <div className="p-2 rounded-xl bg-blue-400/10">
              <TrendingUp className="w-5 h-5 text-blue-400" />
            </div>
            <span className="text-sm font-bold text-text-main">Perjalanan Berat Badan</span>
          </div>
          <div className="flex items-center justify-between">
            <div className="text-center">
              <div className="text-xs text-text-muted">Saat ini</div>
              <div className="text-xl font-black text-text-main">{calorieResult.current_weight_kg} kg</div>
            </div>
            <div className="flex-1 mx-4 h-2 bg-white/10 rounded-full overflow-hidden">
              <div className="h-full bg-gradient-to-r from-primary to-primary/50 rounded-full" style={{ width: '0%' }} />
            </div>
            <div className="text-center">
              <div className="text-xs text-text-muted">Target</div>
              <div className="text-xl font-black text-primary">{calorieResult.target_weight_kg} kg</div>
            </div>
          </div>
          <p className="text-xs text-text-muted text-center">
            Estimasi {calorieResult.estimated_weeks > 8 ? `${Math.round(calorieResult.estimated_weeks / 4.3)} bulan` : `${calorieResult.estimated_weeks} minggu`} lagi
          </p>
        </div>
      )}

      {/* Health Notification */}
      <div className="bg-primary/5 rounded-[20px] border border-primary/20 p-4 flex items-start gap-3">
        <div className="p-2 rounded-xl bg-primary/10 flex-shrink-0 mt-0.5">
          <Info className="w-4 h-4 text-primary" />
        </div>
        <div>
          <h4 className="text-sm font-bold text-text-main">Ringkasan Hari Ini</h4>
          <p className="text-xs text-text-muted mt-1 leading-relaxed">
            Anda sudah berjalan {DUMMY_STEPS.toLocaleString()} langkah dan membakar {DUMMY_CALORIES_BURNED} kkal.
            {DUMMY_STEPS < STEPS_GOAL / 2
              ? ' Coba tingkatkan aktivitas untuk mencapai target langkah harian!'
              : ' Bagus! Terus pertahankan aktivitas Anda.'}
          </p>
        </div>
      </div>

      <div className="h-24" />
    </div>
  );
}
