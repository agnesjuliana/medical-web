import { Target, TrendingDown, TrendingUp, Minus, AlertTriangle } from 'lucide-react';

export interface StepTargetWeightProps {
  target_weight_kg: string;
  current_weight_kg: string;
  goal: string;
  onChange: (field: string, val: string) => void;
}

export function StepTargetWeight({ target_weight_kg, current_weight_kg, goal, onChange }: StepTargetWeightProps) {
  const currentWeight = parseFloat(current_weight_kg) || 0;
  const targetWeight = parseFloat(target_weight_kg) || 0;
  const difference = targetWeight - currentWeight;
  const absDifference = Math.abs(difference);
  const percentChange = currentWeight > 0 ? (absDifference / currentWeight) * 100 : 0;
  const isExtremeChange = percentChange > 30;

  // Rate: 0.5 kg/week for lose, 0.25 kg/week for gain
  const ratePerWeek = goal === 'lose' ? 0.5 : 0.25;
  const estimatedWeeks = goal === 'maintain' ? 0 : Math.ceil(absDifference / ratePerWeek);
  const estimatedMonths = Math.round(estimatedWeeks / 4.3);

  // Validation
  const isInvalidDirection = (goal === 'lose' && difference > 0) || (goal === 'gain' && difference < 0);

  // For maintain goal
  if (goal === 'maintain') {
    return (
      <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 w-full max-w-sm mx-auto">
        <div className="text-center space-y-3">
          <h2 className="text-4xl font-extrabold tracking-tight text-text-main">Pertahankan Berat</h2>
          <p className="text-text-muted font-medium text-sm max-w-xs mx-auto">Kami akan menghitung kalori harian untuk mempertahankan berat badan Anda.</p>
        </div>

        <div className="mt-8 px-2">
          <div className="bg-primary/10 rounded-[20px] p-6 border border-primary/30 text-center space-y-3">
            <div className="inline-flex p-3 bg-primary/20 rounded-2xl">
              <Minus className="w-8 h-8 text-primary" />
            </div>
            <div className="text-3xl font-black text-primary">{current_weight_kg} kg</div>
            <p className="text-primary/80 text-sm font-medium">Berat Anda saat ini akan dipertahankan</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 w-full max-w-sm mx-auto">
      <div className="text-center space-y-3">
        <h2 className="text-4xl font-extrabold tracking-tight text-text-main">Berat Impian?</h2>
        <p className="text-text-muted font-medium text-sm max-w-xs mx-auto">
          {goal === 'lose'
            ? 'Berapa target berat badan yang ingin Anda capai?'
            : 'Berapa target kenaikan berat badan Anda?'
          }
        </p>
      </div>

      <div className="mt-8 px-2 space-y-4">
        {/* iOS Grouped Style Input */}
        <div className="bg-black/5 dark:bg-white/5 backdrop-blur-md rounded-[20px] overflow-hidden border border-black/5 dark:border-white/10 flex flex-col divide-y divide-black/5 dark:divide-white/10">
          {/* Current Weight (readonly) */}
          <div className="relative flex items-center">
            <div className="pl-5 text-text-muted">
              {goal === 'lose'
                ? <TrendingDown className="w-5 h-5" />
                : <TrendingUp className="w-5 h-5" />
              }
            </div>
            <div className="flex-1 flex items-center">
              <span className="w-32 pl-4 text-[15px] font-semibold text-text-muted">Saat ini</span>
              <div className="flex-1 py-4 pr-4 text-right text-lg font-bold text-text-muted">
                {current_weight_kg || '—'}
              </div>
            </div>
            <div className="pr-5 text-[15px] font-medium text-text-muted">Kg</div>
          </div>

          {/* Target Weight (editable) */}
          <div className="relative flex items-center group bg-transparent transition-colors focus-within:bg-black/5 dark:focus-within:bg-white/5 min-h-[56px]">
            <div className="pl-5 text-text-muted group-focus-within:text-primary transition-colors">
              <Target className="w-5 h-5" />
            </div>
            <div className="flex-1 flex items-center min-h-[56px]">
              <label htmlFor="target-weight-input" className="w-32 pl-4 text-[15px] font-semibold text-text-main cursor-pointer">Target</label>
              <input
                id="target-weight-input"
                type="number"
                inputMode="decimal"
                placeholder={goal === 'lose' ? String(Math.max(currentWeight - 5, 1)) : String(currentWeight + 5)}
                value={target_weight_kg}
                onChange={(e) => onChange('target_weight_kg', e.target.value)}
                className="flex-1 bg-transparent py-4 pr-4 text-right text-lg font-bold text-text-main focus:outline-none min-w-0 relative z-10 appearance-none"
                style={{ WebkitAppearance: 'none', MozAppearance: 'textfield' }}
                autoComplete="off"
              />
            </div>
            <div className="pr-5 text-[15px] font-medium text-text-muted">Kg</div>
          </div>
        </div>

        {/* Difference Badge */}
        {targetWeight > 0 && !isInvalidDirection && (
          <div className={`rounded-2xl p-4 text-center space-y-1 transition-all duration-500 animate-in fade-in slide-in-from-bottom-2 ${
            isExtremeChange
              ? 'bg-amber-500/10 border border-amber-500/30'
              : 'bg-primary/10 border border-primary/30'
          }`}>
            <div className={`text-2xl font-black ${isExtremeChange ? 'text-amber-400' : 'text-primary'}`}>
              {goal === 'lose' ? '−' : '+'}{absDifference.toFixed(1)} kg
            </div>
            <p className={`text-sm font-medium ${isExtremeChange ? 'text-amber-400/80' : 'text-primary/80'}`}>
              {estimatedWeeks > 0
                ? `Estimasi ~${estimatedMonths > 1 ? `${estimatedMonths} bulan` : `${estimatedWeeks} minggu`} dengan cara sehat`
                : 'Anda sudah di target!'
              }
            </p>
          </div>
        )}

        {/* Invalid Direction Warning */}
        {targetWeight > 0 && isInvalidDirection && (
          <div className="rounded-2xl p-4 text-center space-y-1 bg-red-500/10 border border-red-500/30 animate-in fade-in">
            <div className="flex items-center justify-center gap-2 text-red-400">
              <AlertTriangle className="w-5 h-5" />
              <span className="font-bold">Target Tidak Sesuai</span>
            </div>
            <p className="text-sm text-red-400/80">
              {goal === 'lose'
                ? 'Target harus lebih rendah dari berat saat ini'
                : 'Target harus lebih tinggi dari berat saat ini'
              }
            </p>
          </div>
        )}

        {/* Extreme Change Warning */}
        {targetWeight > 0 && isExtremeChange && !isInvalidDirection && (
          <div className="rounded-2xl p-3 flex items-center gap-3 bg-amber-500/10 border border-amber-500/20 animate-in fade-in">
            <AlertTriangle className="w-5 h-5 text-amber-400 flex-shrink-0" />
            <p className="text-xs text-amber-400/80">
              Perubahan berat &gt;30% cukup besar. Pastikan berkonsultasi dengan profesional kesehatan.
            </p>
          </div>
        )}
      </div>
    </div>
  );
}
