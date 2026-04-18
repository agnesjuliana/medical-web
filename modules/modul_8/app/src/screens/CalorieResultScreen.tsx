import { useCountUp } from '../hooks/useCountUp';
import type { CalorieResult } from '../lib/calorieCalculator';
import {
  Flame, Zap, Heart, Timer, Target,
  TrendingDown, TrendingUp, Minus,
  ArrowRight, Dumbbell, Wheat, Droplets,
} from 'lucide-react';

interface CalorieResultScreenProps {
  result: CalorieResult;
  onContinue: () => void;
}

// ---------- Animated Stat Card ----------
function StatCard({
  icon,
  label,
  value,
  unit,
  delay,
  color = 'primary',
}: {
  icon: React.ReactNode;
  label: string;
  value: number;
  unit: string;
  delay: number;
  color?: string;
}) {
  const animatedValue = useCountUp(value, 1800, delay);

  const colorMap: Record<string, string> = {
    primary: 'text-primary bg-primary/10 border-primary/20',
    blue: 'text-blue-400 bg-blue-400/10 border-blue-400/20',
    amber: 'text-amber-400 bg-amber-400/10 border-amber-400/20',
    emerald: 'text-emerald-400 bg-emerald-400/10 border-emerald-400/20',
  };
  const colorClasses = colorMap[color] || colorMap.primary;
  const [textColor] = colorClasses.split(' ');

  return (
    <div
      className="bg-black/5 dark:bg-white/5 backdrop-blur-md rounded-[20px] p-5 border border-black/5 dark:border-white/10 flex flex-col items-center gap-2 animate-in fade-in slide-in-from-bottom-4 duration-700"
      style={{ animationDelay: `${delay}ms`, animationFillMode: 'both' }}
    >
      <div className={`p-2.5 rounded-xl ${colorClasses}`}>
        {icon}
      </div>
      <div className={`text-2xl font-black ${textColor}`}>
        {animatedValue.toLocaleString()}
        <span className="text-sm font-semibold opacity-70 ml-1">{unit}</span>
      </div>
      <div className="text-xs font-medium text-text-muted">{label}</div>
    </div>
  );
}

// ---------- Macro Bar ----------
function MacroBar({
  label,
  grams,
  percent,
  color,
  icon,
  delay,
}: {
  label: string;
  grams: number;
  percent: number;
  color: string;
  icon: React.ReactNode;
  delay: number;
}) {
  const animatedGrams = useCountUp(grams, 1500, delay);

  return (
    <div
      className="animate-in fade-in slide-in-from-left-4 duration-700"
      style={{ animationDelay: `${delay}ms`, animationFillMode: 'both' }}
    >
      <div className="flex items-center justify-between mb-2">
        <div className="flex items-center gap-2">
          <div className={`p-1.5 rounded-lg ${color}`}>
            {icon}
          </div>
          <span className="text-sm font-bold text-text-main">{label}</span>
        </div>
        <span className="text-sm font-bold text-text-main">
          {animatedGrams}g
          <span className="text-text-muted font-medium ml-1">({percent}%)</span>
        </span>
      </div>
      <div className="h-2.5 bg-black/10 dark:bg-white/10 rounded-full overflow-hidden">
        <div
          className={`h-full rounded-full transition-all duration-[2000ms] ease-out ${color.replace('/10', '')}`}
          style={{
            width: `${percent}%`,
            transitionDelay: `${delay + 300}ms`,
          }}
        />
      </div>
    </div>
  );
}

// ---------- Main Screen ----------
export default function CalorieResultScreen({ result, onContinue }: CalorieResultScreenProps) {
  const animatedCalories = useCountUp(result.daily_calorie_target, 2500, 400);

  const goalLabel = {
    lose: 'Turunkan Berat',
    maintain: 'Pertahankan Berat',
    gain: 'Naikkan Berat',
  }[result.goal];

  const GoalIcon = {
    lose: TrendingDown,
    maintain: Minus,
    gain: TrendingUp,
  }[result.goal];

  const totalMacroGrams = result.protein_grams + result.carbs_grams + result.fat_grams;
  const proteinPercent = Math.round((result.protein_grams / totalMacroGrams) * 100);
  const carbsPercent = Math.round((result.carbs_grams / totalMacroGrams) * 100);
  const fatPercent = 100 - proteinPercent - carbsPercent;

  return (
    <div className="min-h-screen bg-base text-text-main relative overflow-x-hidden">
      {/* Ambient Glows */}
      <div className="fixed top-[-30%] left-[-20%] w-[70%] h-[70%] bg-primary/15 rounded-full blur-[150px] pointer-events-none animate-pulse" />
      <div className="fixed bottom-[-20%] right-[-15%] w-[60%] h-[60%] bg-blue-500/8 rounded-full blur-[120px] pointer-events-none" />
      <div className="fixed top-[30%] right-[-10%] w-[40%] h-[40%] bg-emerald-500/5 rounded-full blur-[100px] pointer-events-none" />

      {/* Scrollable Content */}
      <div className="relative z-10 p-6 pb-32 max-w-lg mx-auto space-y-8">

        {/* ================= Section 1: Hero ================= */}
        <div
          className="text-center pt-8 space-y-4 animate-in fade-in slide-in-from-bottom-6 duration-700"
          style={{ animationDelay: '100ms', animationFillMode: 'both' }}
        >
          <div className="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-1.5 rounded-full text-sm font-bold border border-primary/20">
            <GoalIcon className="w-4 h-4" />
            {goalLabel}
          </div>
          <h1 className="text-3xl sm:text-4xl font-black tracking-tight leading-tight">
            Rencana Anda<br />
            <span className="text-primary">Siap!</span> 🎉
          </h1>
          <p className="text-text-muted text-sm max-w-xs mx-auto leading-relaxed">
            Berdasarkan profil Anda, berikut target harian yang kami rekomendasikan untuk mencapai berat impian.
          </p>
        </div>

        {/* ================= Section 2: Hero Number ================= */}
        <div
          className="text-center animate-in fade-in zoom-in-95 duration-700"
          style={{ animationDelay: '300ms', animationFillMode: 'both' }}
        >
          <div className="relative inline-block">
            {/* Glow behind */}
            <div className="absolute inset-0 bg-primary/20 blur-[60px] rounded-full scale-150 animate-pulse" />
            <div className="relative bg-black/5 dark:bg-white/5 backdrop-blur-md rounded-[28px] p-8 px-12 border border-primary/20">
              <div className="text-6xl sm:text-7xl font-black text-primary tracking-tight tabular-nums">
                {animatedCalories.toLocaleString()}
              </div>
              <div className="text-text-muted text-sm font-bold mt-1 tracking-widest uppercase">
                kkal / hari
              </div>
              {result.calorie_deficit_or_surplus !== 0 && (
                <div className={`mt-3 text-xs font-bold px-3 py-1 rounded-full inline-flex items-center gap-1 ${
                  result.calorie_deficit_or_surplus < 0
                    ? 'bg-blue-400/10 text-blue-400'
                    : 'bg-emerald-400/10 text-emerald-400'
                }`}>
                  {result.calorie_deficit_or_surplus < 0 ? 'Defisit' : 'Surplus'}{' '}
                  {Math.abs(result.calorie_deficit_or_surplus)} kkal dari TDEE
                </div>
              )}
            </div>
          </div>
        </div>

        {/* ================= Section 3: Stat Cards ================= */}
        <div className="grid grid-cols-2 gap-3">
          <StatCard
            icon={<Flame className="w-5 h-5" />}
            label="BMR"
            value={result.bmr}
            unit="kkal"
            delay={600}
            color="primary"
          />
          <StatCard
            icon={<Zap className="w-5 h-5" />}
            label="TDEE"
            value={result.tdee}
            unit="kkal"
            delay={750}
            color="blue"
          />
          <StatCard
            icon={<Heart className="w-5 h-5" />}
            label="BMI"
            value={result.bmi}
            unit={result.bmi_category}
            delay={900}
            color="amber"
          />
          <StatCard
            icon={<Timer className="w-5 h-5" />}
            label="Estimasi Waktu"
            value={result.estimated_weeks}
            unit={result.estimated_weeks === 0 ? '—' : 'minggu'}
            delay={1050}
            color="emerald"
          />
        </div>

        {/* ================= Section 4: Macro Split ================= */}
        <div
          className="bg-black/5 dark:bg-white/5 backdrop-blur-md rounded-[20px] p-6 border border-black/5 dark:border-white/10 space-y-5 animate-in fade-in slide-in-from-bottom-4 duration-700"
          style={{ animationDelay: '1100ms', animationFillMode: 'both' }}
        >
          <h3 className="text-lg font-bold text-text-main">Distribusi Makro Harian</h3>
          <MacroBar
            label="Protein"
            grams={result.protein_grams}
            percent={proteinPercent}
            color="bg-blue-400/10 text-blue-400"
            icon={<Dumbbell className="w-3.5 h-3.5" />}
            delay={1200}
          />
          <MacroBar
            label="Karbohidrat"
            grams={result.carbs_grams}
            percent={carbsPercent}
            color="bg-amber-400/10 text-amber-400"
            icon={<Wheat className="w-3.5 h-3.5" />}
            delay={1350}
          />
          <MacroBar
            label="Lemak"
            grams={result.fat_grams}
            percent={fatPercent}
            color="bg-emerald-400/10 text-emerald-400"
            icon={<Droplets className="w-3.5 h-3.5" />}
            delay={1500}
          />
        </div>

        {/* ================= Section 5: Timeline Card ================= */}
        <div
          className="bg-black/5 dark:bg-white/5 backdrop-blur-md rounded-[20px] p-6 border border-black/5 dark:border-white/10 animate-in fade-in slide-in-from-bottom-4 duration-700"
          style={{ animationDelay: '1600ms', animationFillMode: 'both' }}
        >
          <div className="flex items-center gap-3 mb-4">
            <div className="p-2 rounded-xl bg-primary/10">
              <Target className="w-5 h-5 text-primary" />
            </div>
            <h3 className="text-lg font-bold text-text-main">Perjalanan Anda</h3>
          </div>

          {result.goal === 'maintain' ? (
            <div className="text-center py-4 space-y-2">
              <div className="text-2xl font-black text-primary">{result.current_weight_kg} kg</div>
              <p className="text-sm text-text-muted">Pertahankan berat badan Anda saat ini dengan asupan kalori seimbang.</p>
            </div>
          ) : (
            <>
              {/* Weight Progress */}
              <div className="flex items-center justify-between mb-3">
                <div className="text-center">
                  <div className="text-xs text-text-muted font-medium">Saat ini</div>
                  <div className="text-xl font-black text-text-main">{result.current_weight_kg} kg</div>
                </div>
                <div className="flex-1 mx-4 relative">
                  <div className="h-2 bg-black/10 dark:bg-white/10 rounded-full overflow-hidden">
                    <div
                      className="h-full bg-gradient-to-r from-primary to-primary/50 rounded-full transition-all duration-[2500ms] ease-out"
                      style={{ width: '100%', transitionDelay: '1800ms' }}
                    />
                  </div>
                  <div className="absolute top-1/2 -translate-y-1/2 left-0 w-3 h-3 bg-primary rounded-full border-2 border-base shadow-lg shadow-primary/40" />
                  <div className="absolute top-1/2 -translate-y-1/2 right-0 w-3 h-3 bg-primary/60 rounded-full border-2 border-base" />
                </div>
                <div className="text-center">
                  <div className="text-xs text-text-muted font-medium">Target</div>
                  <div className="text-xl font-black text-primary">{result.target_weight_kg} kg</div>
                </div>
              </div>

              {/* Summary */}
              <div className="bg-primary/5 rounded-2xl p-4 text-center mt-4">
                <p className="text-sm font-medium text-text-muted">
                  {result.goal === 'lose' ? 'Turunkan' : 'Naikkan'}{' '}
                  <span className="text-primary font-bold">{result.weight_difference_kg} kg</span>{' '}
                  dalam sekitar{' '}
                  <span className="text-primary font-bold">
                    {result.estimated_weeks > 8
                      ? `${Math.round(result.estimated_weeks / 4.3)} bulan`
                      : `${result.estimated_weeks} minggu`
                    }
                  </span>
                </p>
                <p className="text-xs text-text-muted/70 mt-1">
                  Dengan rate sehat {result.goal === 'lose' ? '0.5' : '0.25'} kg/minggu
                </p>
              </div>
            </>
          )}
        </div>
      </div>

      {/* ================= Section 6: CTA Button ================= */}
      <div className="fixed bottom-0 left-0 right-0 z-30 p-6 bg-base/80 backdrop-blur-xl border-t border-black/5 dark:border-white/10 pb-safe">
        <div className="max-w-sm mx-auto">
          <button
            onClick={onContinue}
            className="w-full py-4 rounded-[20px] font-bold bg-primary text-white shadow-lg shadow-primary/20 flex items-center justify-center gap-2 transition-all active:scale-[0.98] hover:shadow-xl hover:shadow-primary/30 animate-in fade-in slide-in-from-bottom-4 duration-700"
            style={{ animationDelay: '2000ms', animationFillMode: 'both' }}
          >
            <span className="text-lg">Mulai Perjalanan Anda</span>
            <ArrowRight className="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>
  );
}
