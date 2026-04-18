import { useState, useMemo } from 'react';
import { ArrowLeft, Dumbbell, Timer, Zap, Flame, Minus, Plus } from 'lucide-react';
import {
  EXERCISE_TYPES,
  DURATION_PRESETS,
  calculateCaloriesBurned,
} from '../../types/exercise';
import type {
  ExerciseEntry,
  ExerciseType,
  IntensityLevel,
} from '../../types/exercise';

interface LogExerciseModalProps {
  isOpen: boolean;
  onClose: () => void;
  onLog: (entry: ExerciseEntry) => void;
  userWeightKg: number;
}

type Step = 'select' | 'configure' | 'manual';

export default function LogExerciseModal({
  isOpen,
  onClose,
  onLog,
  userWeightKg,
}: LogExerciseModalProps) {
  const [step, setStep] = useState<Step>('select');
  const [selectedType, setSelectedType] = useState<ExerciseType | null>(null);
  const [intensity, setIntensity] = useState<IntensityLevel>('medium');
  const [duration, setDuration] = useState(15);
  const [manualCalories, setManualCalories] = useState('');
  const [manualLabel, setManualLabel] = useState('');

  if (!isOpen) return null;

  const resetAndClose = () => {
    setStep('select');
    setSelectedType(null);
    setIntensity('medium');
    setDuration(15);
    setManualCalories('');
    setManualLabel('');
    onClose();
  };

  const goBack = () => {
    if (step === 'configure' || step === 'manual') {
      setStep('select');
      setSelectedType(null);
    } else {
      resetAndClose();
    }
  };

  const handleSelectType = (type: ExerciseType) => {
    setSelectedType(type);
    setIntensity('medium');
    setDuration(15);
    setStep('configure');
  };

  const currentExercise = selectedType ? EXERCISE_TYPES[selectedType] : null;
  const currentMet = currentExercise
    ? currentExercise.intensities[intensity].met
    : 0;

  const effectiveWeight = userWeightKg || 70; // Fallback weight

  const estimatedCalories = useMemo(
    () => calculateCaloriesBurned(currentMet, effectiveWeight, duration),
    [currentMet, effectiveWeight, duration]
  );

  const handleConfirm = () => {
    if (!selectedType || !currentExercise) return;
    onLog({
      id: Date.now().toString() + Math.random().toString(36).slice(2),
      type: selectedType,
      label: currentExercise.label,
      intensity,
      durationMinutes: duration,
      caloriesBurned: estimatedCalories,
      emoji: currentExercise.emoji,
      timestamp: new Date(),
    });
    resetAndClose();
  };

  const handleManualConfirm = () => {
    const cal = parseInt(manualCalories) || 0;
    if (cal <= 0) return;
    onLog({
      id: Date.now().toString() + Math.random().toString(36).slice(2),
      type: 'manual',
      label: manualLabel || 'Aktivitas lainnya',
      intensity: 'medium',
      durationMinutes: 0,
      caloriesBurned: cal,
      emoji: '🔥',
      timestamp: new Date(),
    });
    resetAndClose();
  };

  const intensityLevels: IntensityLevel[] = ['high', 'medium', 'low'];

  return (
    <>
      {/* Backdrop */}
      <div
        className="fixed inset-0 bg-black/60 backdrop-blur-sm z-40"
        onClick={resetAndClose}
      />

      {/* Sheet */}
      <div className="fixed inset-x-0 bottom-0 z-50 animate-in slide-in-from-bottom duration-300">
        <div className="bg-base border-t border-white/10 rounded-t-[28px] max-h-[90vh] flex flex-col">
          {/* Handle */}
          <div className="flex justify-center pt-3 pb-1">
            <div className="w-10 h-1 rounded-full bg-white/20" />
          </div>

          {/* Header */}
          <div className="flex items-center gap-3 px-6 py-3">
            <button
              onClick={goBack}
              className="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center"
            >
              <ArrowLeft className="w-4 h-4 text-text-muted" />
            </button>
            <div className="flex-1">
              <h2 className="text-xl font-bold text-text-main">
                {step === 'select' && 'Log Exercise'}
                {step === 'configure' && currentExercise && (
                  <span className="flex items-center gap-2">
                    <span>{currentExercise.emoji}</span>
                    {currentExercise.label}
                  </span>
                )}
                {step === 'manual' && '🔥 Input Manual'}
              </h2>
            </div>
          </div>

          {/* Content */}
          <div className="flex-1 overflow-y-auto px-6 pb-8 space-y-4">
            {/* ──────── STEP 1: SELECT TYPE ──────── */}
            {step === 'select' && (
              <div className="space-y-3">
                {/* Run */}
                <button
                  onClick={() => handleSelectType('run')}
                  className="w-full flex items-center gap-4 p-5 rounded-[20px] bg-white/5 border border-white/10 hover:bg-white/10 transition-all active:scale-[0.98] text-left"
                >
                  <div className="w-12 h-12 bg-orange-500/12 rounded-2xl flex items-center justify-center">
                    <span className="text-2xl">🏃</span>
                  </div>
                  <div>
                    <div className="font-bold text-text-main">Lari</div>
                    <div className="text-sm text-text-muted">
                      Lari, jogging, sprint, dll.
                    </div>
                  </div>
                </button>

                {/* Weight Lifting */}
                <button
                  onClick={() => handleSelectType('weight_lifting')}
                  className="w-full flex items-center gap-4 p-5 rounded-[20px] bg-white/5 border border-white/10 hover:bg-white/10 transition-all active:scale-[0.98] text-left"
                >
                  <div className="w-12 h-12 bg-violet-500/12 rounded-2xl flex items-center justify-center">
                    <Dumbbell className="w-6 h-6 text-violet-400" />
                  </div>
                  <div>
                    <div className="font-bold text-text-main">Angkat Beban</div>
                    <div className="text-sm text-text-muted">
                      Mesin, free weights, dll.
                    </div>
                  </div>
                </button>

                {/* Manual */}
                <button
                  onClick={() => setStep('manual')}
                  className="w-full flex items-center gap-4 p-5 rounded-[20px] bg-white/5 border border-white/10 hover:bg-white/10 transition-all active:scale-[0.98] text-left"
                >
                  <div className="w-12 h-12 bg-emerald-500/12 rounded-2xl flex items-center justify-center">
                    <Flame className="w-6 h-6 text-emerald-400" />
                  </div>
                  <div>
                    <div className="font-bold text-text-main">Manual</div>
                    <div className="text-sm text-text-muted">
                      Masukkan kalori terbakar secara langsung
                    </div>
                  </div>
                </button>
              </div>
            )}

            {/* ──────── STEP 2: CONFIGURE ──────── */}
            {step === 'configure' && currentExercise && (
              <div className="space-y-6">
                {/* Intensity Selector */}
                <div>
                  <div className="flex items-center gap-2 mb-3">
                    <Zap className="w-4 h-4 text-yellow-400" />
                    <span className="text-sm font-bold text-text-main">
                      Set Intensity
                    </span>
                  </div>
                  <div className="bg-white/5 rounded-[20px] border border-white/10 overflow-hidden divide-y divide-white/5">
                    {intensityLevels.map((level) => {
                      const info = currentExercise.intensities[level];
                      const isSelected = intensity === level;
                      return (
                        <button
                          key={level}
                          onClick={() => setIntensity(level)}
                          className={`w-full text-left px-5 py-4 transition-all ${
                            isSelected
                              ? 'bg-primary/10'
                              : 'hover:bg-white/5'
                          }`}
                        >
                          <div
                            className={`font-bold text-sm ${
                              isSelected ? 'text-primary' : 'text-text-main'
                            }`}
                          >
                            {info.label}
                          </div>
                          <div className="text-xs text-text-muted mt-0.5">
                            {info.description}
                          </div>
                          {isSelected && (
                            <div className="mt-2 h-1 w-full bg-white/10 rounded-full overflow-hidden">
                              <div
                                className="h-full bg-primary rounded-full transition-all duration-500"
                                style={{
                                  width:
                                    level === 'low'
                                      ? '33%'
                                      : level === 'medium'
                                      ? '66%'
                                      : '100%',
                                }}
                              />
                            </div>
                          )}
                        </button>
                      );
                    })}
                  </div>
                </div>

                {/* Duration */}
                <div>
                  <div className="flex items-center gap-2 mb-3">
                    <Timer className="w-4 h-4 text-blue-400" />
                    <span className="text-sm font-bold text-text-main">
                      Duration
                    </span>
                  </div>

                  {/* Preset chips */}
                  <div className="flex gap-2 mb-3">
                    {DURATION_PRESETS.map((d) => (
                      <button
                        key={d}
                        onClick={() => setDuration(d)}
                        className={`flex-1 py-2.5 rounded-full text-sm font-bold transition-all ${
                          duration === d
                            ? 'bg-primary text-white'
                            : 'bg-white/5 text-text-muted border border-white/10 hover:bg-white/10'
                        }`}
                      >
                        {d} min
                      </button>
                    ))}
                  </div>

                  {/* Custom duration input */}
                  <div className="bg-white/5 rounded-2xl border border-white/10 p-3 flex items-center justify-between">
                    <button
                      onClick={() =>
                        setDuration((d) => Math.max(5, d - 5))
                      }
                      className="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center"
                    >
                      <Minus className="w-4 h-4 text-text-muted" />
                    </button>
                    <div className="flex items-baseline gap-1">
                      <input
                        type="number"
                        inputMode="numeric"
                        value={duration}
                        onChange={(e) =>
                          setDuration(
                            Math.max(1, parseInt(e.target.value) || 1)
                          )
                        }
                        className="w-16 bg-transparent text-center text-2xl font-black text-text-main focus:outline-none appearance-none"
                        style={
                          { MozAppearance: 'textfield' } as React.CSSProperties
                        }
                      />
                      <span className="text-sm text-text-muted font-medium">
                        menit
                      </span>
                    </div>
                    <button
                      onClick={() => setDuration((d) => d + 5)}
                      className="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center"
                    >
                      <Plus className="w-4 h-4 text-text-muted" />
                    </button>
                  </div>
                </div>

                {/* Calorie estimation preview */}
                <div className="bg-gradient-to-r from-orange-500/10 to-red-500/10 rounded-[20px] border border-orange-500/20 p-5 text-center">
                  <div className="text-sm text-text-muted font-semibold mb-1">
                    Estimasi Kalori Terbakar
                  </div>
                  <div className="text-4xl font-black text-orange-400 tabular-nums">
                    {estimatedCalories}
                  </div>
                  <div className="text-xs text-text-muted mt-1">
                    kkal • MET {currentMet} × {userWeightKg}kg ×{' '}
                    {duration} mnt
                  </div>
                </div>

                {/* Confirm Button */}
                <button
                  onClick={handleConfirm}
                  className="w-full py-4 rounded-[20px] bg-primary text-white font-bold text-lg shadow-lg shadow-primary/20 active:scale-[0.98] transition-transform"
                >
                  Simpan Aktivitas 🔥
                </button>
              </div>
            )}

            {/* ──────── MANUAL INPUT ──────── */}
            {step === 'manual' && (
              <div className="space-y-4">
                {/* Label */}
                <div className="bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
                  <div className="flex items-center px-4">
                    <span className="text-sm font-semibold text-text-muted w-24">
                      Aktivitas
                    </span>
                    <input
                      type="text"
                      placeholder="Contoh: Yoga, Berenang..."
                      value={manualLabel}
                      onChange={(e) => setManualLabel(e.target.value)}
                      className="flex-1 bg-transparent py-3.5 text-sm text-text-main placeholder:text-text-muted/50 focus:outline-none"
                    />
                  </div>
                </div>

                {/* Calories */}
                <div className="bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
                  <div className="flex items-center px-4">
                    <span className="text-sm font-semibold text-text-muted w-24">
                      Kalori
                    </span>
                    <input
                      type="number"
                      inputMode="numeric"
                      placeholder="0"
                      value={manualCalories}
                      onChange={(e) => setManualCalories(e.target.value)}
                      className="flex-1 bg-transparent py-3.5 text-sm font-bold text-text-main placeholder:text-text-muted/50 focus:outline-none appearance-none"
                      style={
                        {
                          MozAppearance: 'textfield',
                        } as React.CSSProperties
                      }
                    />
                    <span className="text-xs text-text-muted">kkal</span>
                  </div>
                </div>

                {/* Submit */}
                <button
                  onClick={handleManualConfirm}
                  disabled={!manualCalories || parseInt(manualCalories) <= 0}
                  className={`w-full py-4 rounded-[20px] font-bold text-lg transition-all active:scale-[0.98] ${
                    manualCalories && parseInt(manualCalories) > 0
                      ? 'bg-primary text-white shadow-lg shadow-primary/20'
                      : 'bg-white/5 text-text-muted cursor-not-allowed border border-white/10'
                  }`}
                >
                  Simpan 🔥
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    </>
  );
}
