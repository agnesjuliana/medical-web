import { useState, useMemo, useEffect, useCallback } from 'react';
import { Home, Activity, Settings, Plus, Flame, Trash2, UtensilsCrossed, Droplets } from 'lucide-react';
import useEmblaCarousel from 'embla-carousel-react';
import { format, startOfWeek, addDays, isToday } from 'date-fns';
import { id as localeID } from 'date-fns/locale';
import { calculateHealthScore } from '../lib/calorieCalculator';
import type { CalorieResult } from '../lib/calorieCalculator';
import type { FoodEntry } from '../types/food';
import type { ExerciseEntry } from '../types/exercise';
import type { TabItem } from '@/components/navigation/tabBar';
import TabBar from '@/components/navigation/tabBar';
import AddFoodModal from '@/components/dashboard/AddFoodModal';
import ActionMenuModal from '@/components/dashboard/ActionMenuModal';
import LogExerciseModal from '@/components/dashboard/LogExerciseModal';
import SavedFoodsModal from '@/components/dashboard/SavedFoodsModal';
import ScanFoodModal from '@/components/dashboard/ScanFoodModal';
import ProgressScreen from './ProgressScreens';
import SettingsScreen from './SettingsScreens';

// ─────────────────────────────────────────────────────────
// NAV TABS
// ─────────────────────────────────────────────────────────
const NAV_TABS: TabItem[] = [
  { id: 'home', label: 'Home', icon: <Home size={20} /> },
  { id: 'progress', label: 'Progress', icon: <Activity size={20} /> },
  { id: 'settings', label: 'Settings', icon: <Settings size={20} /> },
];

// ─────────────────────────────────────────────────────────
// SVG Circular Progress Ring
// ─────────────────────────────────────────────────────────
function CircleRing({
  size = 140,
  strokeWidth = 12,
  progress,
  color = 'var(--primary)',
  bgColor = 'rgba(255,255,255,0.08)',
  children,
}: {
  size?: number;
  strokeWidth?: number;
  progress: number; // 0 to 1
  color?: string;
  bgColor?: string;
  children?: React.ReactNode;
}) {
  const radius = (size - strokeWidth) / 2;
  const circumference = 2 * Math.PI * radius;
  const clampedProgress = Math.min(Math.max(progress, 0), 1);
  const offset = circumference - clampedProgress * circumference;

  return (
    <div className="relative" style={{ width: size, height: size }}>
      <svg width={size} height={size} className="-rotate-90">
        {/* Background ring */}
        <circle
          cx={size / 2}
          cy={size / 2}
          r={radius}
          fill="none"
          stroke={bgColor}
          strokeWidth={strokeWidth}
        />
        {/* Progress ring */}
        <circle
          cx={size / 2}
          cy={size / 2}
          r={radius}
          fill="none"
          stroke={color}
          strokeWidth={strokeWidth}
          strokeLinecap="round"
          strokeDasharray={circumference}
          strokeDashoffset={offset}
          className="transition-all duration-1000 ease-out"
        />
      </svg>
      {/* Center content */}
      {children && (
        <div className="absolute inset-0 flex items-center justify-center">
          {children}
        </div>
      )}
    </div>
  );
}

// ─────────────────────────────────────────────────────────
// Mini Ring (for macro cards)
// ─────────────────────────────────────────────────────────
function MiniRing({
  progress,
  color,
  icon,
}: {
  progress: number;
  color: string;
  icon: React.ReactNode;
}) {
  return (
    <CircleRing size={48} strokeWidth={5} progress={progress} color={color} bgColor="rgba(255,255,255,0.06)">
      <div style={{ color }} className="flex items-center justify-center">
        {icon}
      </div>
    </CircleRing>
  );
}

// ─────────────────────────────────────────────────────────
// Weekly Calendar Strip
// ─────────────────────────────────────────────────────────
function WeeklyCalendar({ selectedDate }: { selectedDate: Date }) {
  const weekStart = startOfWeek(selectedDate, { weekStartsOn: 1 });
  const days = Array.from({ length: 7 }, (_, i) => addDays(weekStart, i));

  return (
    <div className="flex items-center justify-between gap-1 px-2">
      {days.map((day) => {
        const active = isToday(day);
        return (
          <button
            key={day.toISOString()}
            className={`flex flex-col items-center gap-1 py-2 px-2.5 rounded-2xl transition-all flex-1 ${
              active
                ? 'bg-primary/15'
                : 'hover:bg-white/5'
            }`}
          >
            <span className={`text-xs font-bold uppercase ${active ? 'text-primary' : 'text-text-muted'}`}>
              {format(day, 'EEE', { locale: localeID }).slice(0, 3)}
            </span>
            <span className={`text-sm font-black w-8 h-8 flex items-center justify-center rounded-full ${
              active ? 'bg-primary text-white' : 'text-text-main'
            }`}>
              {format(day, 'd')}
            </span>
          </button>
        );
      })}
    </div>
  );
}

// ─────────────────────────────────────────────────────────
// HOME CONTENT WITH CAROUSEL
// ─────────────────────────────────────────────────────────
function HomeContent({
  calorieResult,
  foodLog,
  onRemoveFood,
  waterIntake,
  setWaterIntake,
  exerciseLog,
}: {
  calorieResult: CalorieResult;
  foodLog: FoodEntry[];
  onRemoveFood: (id: string) => void;
  waterIntake: number;
  setWaterIntake: (val: number) => void;
  exerciseLog: ExerciseEntry[];
}) {
  const [emblaRef, emblaApi] = useEmblaCarousel({ loop: false });
  const [selectedIndex, setSelectedIndex] = useState(0);

  const onSelect = useCallback(() => {
    if (!emblaApi) return;
    setSelectedIndex(emblaApi.selectedScrollSnap());
  }, [emblaApi]);

  useEffect(() => {
    if (!emblaApi) return;
    emblaApi.on('select', onSelect);
    onSelect();
  }, [emblaApi, onSelect]);

  const consumed = useMemo(() => ({
    calories: foodLog.reduce((s, f) => s + f.calories, 0),
    protein: foodLog.reduce((s, f) => s + f.protein, 0),
    carbs: foodLog.reduce((s, f) => s + f.carbs, 0),
    fat: foodLog.reduce((s, f) => s + f.fat, 0),
    fiber: foodLog.reduce((s, f) => s + (f.fiber || 0), 0),
    sugar: foodLog.reduce((s, f) => s + (f.sugar || 0), 0),
    sodium: foodLog.reduce((s, f) => s + (f.sodium || 0), 0),
  }), [foodLog]);

  const totalCaloriesBurned = useMemo(
    () => exerciseLog.reduce((s, e) => s + e.caloriesBurned, 0),
    [exerciseLog]
  );

  const remaining = {
    calories: Math.max(calorieResult.daily_calorie_target - consumed.calories + (totalCaloriesBurned || 0), 0),
    protein: Math.max(calorieResult.protein_grams - consumed.protein, 0),
    carbs: Math.max(calorieResult.carbs_grams - consumed.carbs, 0),
    fat: Math.max(calorieResult.fat_grams - consumed.fat, 0),
    fiber: Math.max((calorieResult.fiber_target_grams || 25) - consumed.fiber, 0),
    sugar: Math.max((calorieResult.sugar_limit_grams || 35) - consumed.sugar, 0),
    sodium: Math.max((calorieResult.sodium_limit_mg || 2300) - consumed.sodium, 0),
  };

  const calorieProgress = calorieResult.daily_calorie_target > 0
    ? consumed.calories / calorieResult.daily_calorie_target
    : 0;

  const healthScore = useMemo(() => {
    return calculateHealthScore(
      consumed.protein, consumed.fiber, consumed.sugar, consumed.sodium, calorieResult
    );
  }, [consumed, calorieResult]);

  const macros = [
    { label: 'Protein', consumed: consumed.protein, target: calorieResult.protein_grams, remaining: remaining.protein, color: '#60a5fa', icon: '💪' },
    { label: 'Karbo', consumed: consumed.carbs, target: calorieResult.carbs_grams, remaining: remaining.carbs, color: '#fbbf24', icon: '🌾' },
    { label: 'Lemak', consumed: consumed.fat, target: calorieResult.fat_grams, remaining: remaining.fat, color: '#34d399', icon: '🫧' },
  ];

  const micros = [
    { label: 'Serat', consumed: consumed.fiber, target: calorieResult.fiber_target_grams || 25, remaining: remaining.fiber, color: '#a78bfa', icon: '🫐' },
    { label: 'Gula', consumed: consumed.sugar, target: calorieResult.sugar_limit_grams || 35, remaining: remaining.sugar, color: '#f472b6', icon: '🥄' },
    { label: 'Natrium', consumed: consumed.sodium, target: calorieResult.sodium_limit_mg || 2300, remaining: remaining.sodium, color: '#fb923c', icon: '🧂', unit: 'mg' },
  ];

  const steps = 1109;
  const stepsTarget = 10000;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-black text-text-main">NutriTrack</h1>
          <p className="text-sm text-text-muted font-medium">
            {format(new Date(), 'EEEE, d MMMM yyyy', { locale: localeID })}
          </p>
        </div>
        <div className="flex items-center gap-1.5 bg-white/5 border border-white/10 rounded-full px-3 py-1.5">
          <Flame className="w-4 h-4 text-orange-400" />
          <span className="text-sm font-bold text-text-main">{totalCaloriesBurned}</span>
        </div>
      </div>

      {/* Weekly Calendar */}
      <WeeklyCalendar selectedDate={new Date()} />

      {/* CAROUSEL WRAPPER */}
      <div className="relative">
        <div className="overflow-hidden" ref={emblaRef}>
          <div className="flex">
            
            {/* SLIDE 1: MAIN MACROS */}
            <div className="flex-[0_0_100%] min-w-0 pr-1 space-y-3">
              <div className="bg-white/5 backdrop-blur-md rounded-[24px] border border-white/10 p-6">
                <div className="flex items-center justify-between">
                  <div className="space-y-1">
                    <div className="text-5xl font-black text-text-main tabular-nums">
                      {remaining.calories.toLocaleString()}
                    </div>
                    <div className="text-sm font-semibold text-text-muted">Kalori tersisa</div>
                    <div className="flex items-center gap-3 mt-3 text-xs text-text-muted">
                      <span className="flex items-center gap-1">
                        <span className="w-2 h-2 rounded-full bg-primary" />
                        Dikonsumsi: <span className="font-bold text-text-main">{consumed.calories}</span>
                      </span>
                    </div>
                  </div>
                  <CircleRing size={120} strokeWidth={10} progress={calorieProgress} color={calorieProgress > 1 ? '#ef4444' : 'var(--primary)'}>
                    <div className="text-center">
                      <div className="text-lg font-black text-primary">{Math.round(calorieProgress * 100)}%</div>
                    </div>
                  </CircleRing>
                </div>
              </div>

              <div className="grid grid-cols-3 gap-3">
                {macros.map((m) => (
                  <div key={m.label} className="bg-white/5 backdrop-blur-md rounded-[20px] border border-white/10 p-4 flex flex-col items-center gap-2">
                    <MiniRing progress={m.target > 0 ? m.consumed / m.target : 0} color={m.color} icon={<span className="text-sm">{m.icon}</span>} />
                    <div className="text-center">
                      <div className="text-lg font-black text-text-main">{Math.round(m.remaining)}g</div>
                      <div className="text-[11px] font-semibold text-text-muted">{m.label} sisa</div>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* SLIDE 2: MICROS & HEALTH SCORE */}
            <div className="flex-[0_0_100%] min-w-0 px-2 space-y-3">
              <div className="bg-white/5 backdrop-blur-md rounded-[24px] border border-white/10 p-5 space-y-3">
                <div className="flex items-center gap-2 justify-between">
                  <div className="text-sm font-bold text-text-main">Health Score</div>
                  <div className="text-xl font-black text-primary">{foodLog.length === 0 ? 'N/A' : healthScore}</div>
                </div>
                <div className="h-1.5 bg-white/10 rounded-full overflow-hidden">
                   <div className="h-full bg-gradient-to-r from-red-500 via-yellow-500 to-green-500 rounded-full transition-all" style={{ width: `${foodLog.length === 0 ? 0 : healthScore}%` }} />
                </div>
                <p className="text-xs text-text-muted leading-relaxed">
                  Skor Anda mencerminkan kualitas nutrisi dari makanan hari ini berdasarkan pedoman gizi WHO.
                </p>
              </div>

              <div className="grid grid-cols-3 gap-3">
                {micros.map((m) => {
                  const progress = m.target > 0 ? Math.min(m.consumed / m.target, 1) : 0;
                  return (
                    <div key={m.label} className="bg-white/5 backdrop-blur-md rounded-[20px] border border-white/10 p-4 flex flex-col items-center gap-2">
                      <MiniRing progress={progress} color={m.color} icon={<span className="text-sm">{m.icon}</span>} />
                      <div className="text-center">
                        <div className="text-lg font-black text-text-main">{Math.round(m.remaining)}{m.unit || 'g'}</div>
                        <div className="text-[11px] font-semibold text-text-muted">{m.label} sisa</div>
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>

            {/* SLIDE 3: ACTIVITY & WATER */}
            <div className="flex-[0_0_100%] min-w-0 pl-1 space-y-3">
              <div className="grid grid-cols-2 gap-3 h-[180px]">
                {/* Steps Card */}
                <div className="bg-white/5 backdrop-blur-md rounded-[24px] border border-white/10 p-5 flex flex-col">
                  <div className="text-xs font-semibold text-text-muted mb-1">Steps</div>
                  <div className="text-2xl font-black text-text-main tabular-nums mb-auto">
                    {steps.toLocaleString()}<span className="text-xs font-medium text-text-muted">/{stepsTarget.toLocaleString()}</span>
                  </div>
                  <div className="self-center">
                    <CircleRing size={70} strokeWidth={6} progress={steps / stepsTarget} color="#cbd5e1">
                      <div className="text-xl">🚶</div>
                    </CircleRing>
                  </div>
                </div>

                {/* Calories Burned Card */}
                <div className="bg-white/5 backdrop-blur-md rounded-[24px] border border-white/10 p-5 flex flex-col">
                  <div className="text-xs font-semibold text-text-muted mb-1">Calories burned</div>
                  <div className="text-xl font-black text-orange-400 tabular-nums mb-4">{totalCaloriesBurned} <span className="text-xs font-medium text-text-muted">cal</span></div>
                  {exerciseLog.length > 0 ? (
                    <div className="space-y-1 overflow-y-auto max-h-[60px]">
                      {exerciseLog.slice(0, 3).map((e) => (
                        <div key={e.id} className="flex gap-2 items-center text-sm text-text-muted">
                          <span className="text-base">{e.emoji}</span>
                          <div className="leading-tight">
                            <div className="font-semibold text-text-main text-xs">{e.label}</div>
                            <div className="text-[10px]">{e.caloriesBurned} cal</div>
                          </div>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <div className="text-xs text-text-muted">Belum ada olahraga</div>
                  )}
                </div>
              </div>

              {/* Water Tracker Card */}
              <div className="bg-white/5 backdrop-blur-md rounded-[24px] border border-white/10 p-5 flex items-center justify-between">
                <div className="flex items-center gap-4">
                  <div className="w-12 h-12 bg-blue-500/10 rounded-full flex items-center justify-center">
                    <Droplets className="w-6 h-6 text-blue-400" />
                  </div>
                  <div>
                     <div className="text-xs font-semibold text-text-muted">Air / Water</div>
                     <div className="text-xl font-black text-text-main">{waterIntake} <span className="text-sm font-medium">ml</span></div>
                  </div>
                </div>
                <button
                  onClick={() => {
                    const amount = 250; // Default cup size
                    setWaterIntake(waterIntake + amount);
                  }}
                  className="px-4 py-2 bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/20 rounded-full text-blue-400 text-sm font-bold transition-colors"
                >
                  +250ml
                </button>
              </div>
            </div>

          </div>
        </div>

        {/* Dots Pagination */}
        <div className="flex justify-center gap-2 mt-4 mb-2">
          {[0, 1, 2].map((idx) => (
            <button
              key={idx}
              onClick={() => emblaApi?.scrollTo(idx)}
              className={`w-2 h-2 rounded-full transition-colors ${
                selectedIndex === idx ? 'bg-primary' : 'bg-white/20'
              }`}
            />
          ))}
        </div>
      </div>

      {/* Recently Eaten */}
      <div>
        <h3 className="text-lg font-bold text-text-main mb-3">Makanan Hari Ini</h3>
        {foodLog.length === 0 ? (
          <div className="bg-white/5 rounded-[20px] border border-white/10 border-dashed p-8 text-center">
            <div className="text-4xl mb-3">🍽️</div>
            <p className="text-sm text-text-muted font-medium">Belum ada makanan hari ini</p>
            <p className="text-xs text-text-muted/70 mt-1">Tap tombol <span className="text-primary font-bold">+</span> untuk menambahkan</p>
          </div>
        ) : (
          <div className="space-y-2">
            {foodLog.map((entry) => (
              <div key={entry.id} className="flex items-center gap-3 bg-white/5 rounded-2xl border border-white/10 p-4 group">
                <span className="text-2xl flex-shrink-0">{entry.emoji}</span>
                <div className="flex-1 min-w-0">
                  <div className="text-sm font-bold text-text-main truncate">{entry.name}</div>
                  <div className="text-xs text-text-muted mt-0.5">
                    P: {entry.protein}g · C: {entry.carbs}g · F: {entry.fat}g
                  </div>
                </div>
                <div className="text-right flex-shrink-0">
                  <div className="text-sm font-bold text-primary">{entry.calories} kkal</div>
                  <div className="text-[10px] text-text-muted">{format(entry.timestamp, 'HH:mm')}</div>
                </div>
                <button
                  onClick={() => onRemoveFood(entry.id)}
                  className="opacity-0 group-hover:opacity-100 w-7 h-7 rounded-full bg-red-500/10 flex items-center justify-center transition-opacity flex-shrink-0"
                >
                  <Trash2 className="w-3.5 h-3.5 text-red-400" />
                </button>
              </div>
            ))}

            {/* Summary bar */}
            <div className="bg-primary/10 rounded-2xl border border-primary/20 p-3 flex items-center justify-between mt-4">
              <div className="flex items-center gap-2">
                <UtensilsCrossed className="w-4 h-4 text-primary" />
                <span className="text-sm font-bold text-primary">Total hari ini</span>
              </div>
              <span className="text-sm font-black text-primary">{consumed.calories} kkal</span>
            </div>
          </div>
        )}
      </div>

      {/* Bottom spacer for tab bar */}
      <div className="h-24" />
    </div>
  );
}

// ─────────────────────────────────────────────────────────
// HOME SCREEN (top-level with tabs)
// ─────────────────────────────────────────────────────────
interface HomeScreenProps {
  calorieResult: CalorieResult;
}

export default function HomeScreen({ calorieResult }: HomeScreenProps) {
  const [activeTab, setActiveTab] = useState('home');
  const [foodLog, setFoodLog] = useState<FoodEntry[]>(() => {
    const saved = localStorage.getItem('nutritrack_food_log');
    return saved ? JSON.parse(saved) : [];
  });
  const [savedFoods, setSavedFoods] = useState<Partial<FoodEntry>[]>(() => {
    const saved = localStorage.getItem('nutritrack_saved_foods');
    return saved ? JSON.parse(saved) : [];
  });
  const [exerciseLog, setExerciseLog] = useState<ExerciseEntry[]>(() => {
    const saved = localStorage.getItem('nutritrack_exercise_log');
    return saved ? JSON.parse(saved) : [];
  });
  const [showAddFood, setShowAddFood] = useState(false);
  const [showActionMenu, setShowActionMenu] = useState(false);
  const [showLogExercise, setShowLogExercise] = useState(false);
  const [showSavedFoods, setShowSavedFoods] = useState(false);
  const [showScanFood, setShowScanFood] = useState(false);
  const [waterIntake, setWaterIntake] = useState(() => {
    const saved = localStorage.getItem('nutritrack_water_intake');
    return saved ? parseInt(saved) : 0;
  });

  // Persist to localStorage
  useEffect(() => {
    localStorage.setItem('nutritrack_food_log', JSON.stringify(foodLog));
  }, [foodLog]);

  useEffect(() => {
    localStorage.setItem('nutritrack_exercise_log', JSON.stringify(exerciseLog));
  }, [exerciseLog]);

  useEffect(() => {
    localStorage.setItem('nutritrack_saved_foods', JSON.stringify(savedFoods));
  }, [savedFoods]);

  useEffect(() => {
    localStorage.setItem('nutritrack_water_intake', waterIntake.toString());
  }, [waterIntake]);

  const handleAddFood = (entry: FoodEntry) => {
    setFoodLog(prev => [entry, ...prev]);
  };

  const handleRemoveFood = (id: string) => {
    setFoodLog(prev => prev.filter(f => f.id !== id));
  };

  const handleToggleFavorite = (food: Partial<FoodEntry>) => {
    setSavedFoods(prev => {
      const isExist = prev.some(f => f.name === food.name);
      if (isExist) {
        return prev.filter(f => f.name !== food.name);
      }
      return [food, ...prev];
    });
  };

  return (
    <div className="min-h-screen bg-base text-text-main relative">
      {/* Ambient Glows */}
      <div className="fixed top-[-20%] left-[-10%] w-[50%] h-[50%] bg-primary/10 rounded-full blur-[120px] pointer-events-none" />
      <div className="fixed bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-500/5 rounded-full blur-[100px] pointer-events-none" />

      {/* Content */}
      <div className="relative z-10 p-5 pt-8 max-w-lg mx-auto">
        {activeTab === 'home' && (
          <HomeContent
            calorieResult={calorieResult}
            foodLog={foodLog}
            onRemoveFood={handleRemoveFood}
            waterIntake={waterIntake}
            setWaterIntake={setWaterIntake}
            exerciseLog={exerciseLog}
          />
        )}
        {activeTab === 'progress' && <ProgressScreen calorieResult={calorieResult} />}
        {activeTab === 'settings' && <SettingsScreen calorieResult={calorieResult} />}
      </div>

      {/* Tab Bar */}
      <TabBar
        tabs={NAV_TABS}
        activeTab={activeTab}
        onTabChange={setActiveTab}
      />

      {/* FAB Button */}
      <button
        onClick={() => setShowActionMenu(true)}
        className={`fixed bottom-8 right-6 z-50 w-14 h-14 rounded-full bg-primary text-white shadow-lg shadow-primary/30 flex items-center justify-center transition-all hover:shadow-xl hover:shadow-primary/40 ${showActionMenu ? 'rotate-45 scale-90' : 'rotate-0 scale-100'}`}
      >
        <Plus className="w-6 h-6" />
      </button>

      {/* Action Menu Modal */}
      <ActionMenuModal
        isOpen={showActionMenu}
        onClose={() => setShowActionMenu(false)}
        onOpenFoodDatabase={() => setShowAddFood(true)}
        onOpenLogExercise={() => setShowLogExercise(true)}
        onOpenSavedFoods={() => setShowSavedFoods(true)}
        onOpenScanFood={() => setShowScanFood(true)}
      />

      {/* Log Exercise Modal */}
      <LogExerciseModal
        isOpen={showLogExercise}
        onClose={() => setShowLogExercise(false)}
        onLog={(entry) => setExerciseLog(prev => [entry, ...prev])}
        userWeightKg={calorieResult.current_weight_kg}
      />

      {/* Saved Foods Modal */}
      <SavedFoodsModal
        isOpen={showSavedFoods}
        onClose={() => setShowSavedFoods(false)}
        savedFoods={savedFoods}
        onAdd={(food) => {
          handleAddFood({
            id: Date.now().toString(),
            name: food.name || '',
            calories: food.calories || 0,
            protein: food.protein || 0,
            carbs: food.carbs || 0,
            fat: food.fat || 0,
            fiber: food.fiber || 0,
            sugar: food.sugar || 0,
            sodium: food.sodium || 0,
            emoji: food.emoji || '🍽️',
            timestamp: new Date(),
          });
          setShowSavedFoods(false);
        }}
        onRemove={(name) => setSavedFoods(prev => prev.filter(f => f.name !== name))}
      />

      {/* Scan Food Modal */}
      <ScanFoodModal
        isOpen={showScanFood}
        onClose={() => setShowScanFood(false)}
        onAdd={handleAddFood}
      />

      {/* Add Food Modal */}
      <AddFoodModal
        isOpen={showAddFood}
        onClose={() => setShowAddFood(false)}
        onAdd={handleAddFood}
        foodHistory={foodLog}
        onSaveToFavorites={handleToggleFavorite}
        savedFoodIds={savedFoods.map(f => f.name || '')}
      />
    </div>
  );
}
