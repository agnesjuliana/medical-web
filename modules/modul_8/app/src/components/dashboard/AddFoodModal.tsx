import { useState, useMemo } from 'react';
import { X, Search, Plus, Minus, ChevronRight, Bookmark, History, Sparkles } from 'lucide-react';
import { COMMON_FOODS } from '../../types/food';
import type { FoodEntry } from '../../types/food';

interface AddFoodModalProps {
  isOpen: boolean;
  onClose: () => void;
  onAdd: (entry: FoodEntry) => void;
  foodHistory?: FoodEntry[];
  onSaveToFavorites?: (food: Partial<FoodEntry>) => void;
  savedFoodIds?: string[];
}

export default function AddFoodModal({ 
  isOpen, 
  onClose, 
  onAdd, 
  foodHistory = [],
  onSaveToFavorites,
  savedFoodIds = []
}: AddFoodModalProps) {
  const [search, setSearch] = useState('');
  const [activeTab, setActiveTab] = useState<'quick' | 'history' | 'manual'>('quick');
  const [manualForm, setManualForm] = useState({
    name: '',
    calories: '',
    protein: '',
    carbs: '',
    fat: '',
    emoji: '🍽️',
  });

  if (!isOpen) return null;

  const filteredFoods = COMMON_FOODS.filter(f =>
    f.name.toLowerCase().includes(search.toLowerCase())
  );

  const historyItems = useMemo(() => {
    const seen = new Set();
    return foodHistory
      .filter(f => {
        if (seen.has(f.name.toLowerCase())) return false;
        seen.add(f.name.toLowerCase());
        return true;
      })
      .slice(0, 10);
  }, [foodHistory]);

  const handleQuickAdd = (food: typeof COMMON_FOODS[0]) => {
    onAdd({
      id: Date.now().toString() + Math.random().toString(36).slice(2),
      name: food.name,
      calories: food.calories,
      protein: food.protein,
      carbs: food.carbs,
      fat: food.fat,
      fiber: food.fiber || 0,
      sugar: food.sugar || 0,
      sodium: food.sodium || 0,
      emoji: food.emoji,
      timestamp: new Date(),
    });
    onClose();
    setSearch('');
  };

  const handleManualAdd = () => {
    if (!manualForm.name || !manualForm.calories) return;
    onAdd({
      id: Date.now().toString() + Math.random().toString(36).slice(2),
      name: manualForm.name,
      calories: parseInt(manualForm.calories) || 0,
      protein: parseInt(manualForm.protein) || 0,
      carbs: parseInt(manualForm.carbs) || 0,
      fat: parseInt(manualForm.fat) || 0,
      fiber: 0, // Default to 0 for manual basic logs
      sugar: 0,
      sodium: 0,
      emoji: manualForm.emoji,
      timestamp: new Date(),
    });
    onClose();
    setManualForm({ name: '', calories: '', protein: '', carbs: '', fat: '', emoji: '🍽️' });
    setShowManual(false);
  };

  return (
    <>
      {/* Backdrop */}
      <div
        className="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 animate-in fade-in duration-200"
        onClick={onClose}
      />

      {/* Sheet */}
      <div className="fixed inset-x-0 bottom-0 z-50 animate-in slide-in-from-bottom duration-300">
        <div className="bg-base border-t border-white/10 rounded-t-[28px] max-h-[85vh] flex flex-col">

          {/* Handle */}
          <div className="flex justify-center pt-3 pb-1">
            <div className="w-10 h-1 rounded-full bg-white/20" />
          </div>

          {/* Header */}
          <div className="flex items-center justify-between px-6 py-3">
            <h2 className="text-xl font-bold text-text-main">Tambah Makanan</h2>
            <button
              onClick={onClose}
              className="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center"
            >
              <X className="w-4 h-4 text-text-muted" />
            </button>
          </div>

          {/* Search */}
          <div className="px-6 pb-3">
            <div className="relative">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" />
              <input
                type="text"
                placeholder="Cari makanan..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="w-full bg-white/5 border border-white/10 rounded-2xl py-3 pl-11 pr-4 text-sm text-text-main placeholder:text-text-muted focus:outline-none focus:border-primary/50"
              />
            </div>
          </div>

          {/* Toggle: Quick / History / Manual */}
          <div className="px-6 pb-3 flex gap-1 bg-white/5 mx-6 mb-4 rounded-xl p-1">
            <button
              onClick={() => setActiveTab('quick')}
              className={`flex-1 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 ${
                activeTab === 'quick' ? 'bg-primary text-white shadow-sm' : 'text-text-muted hover:text-text-main'
              }`}
            >
              <Sparkles className="w-3 h-3" />
              Quick
            </button>
            <button
              onClick={() => setActiveTab('history')}
              className={`flex-1 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 ${
                activeTab === 'history' ? 'bg-primary text-white shadow-sm' : 'text-text-muted hover:text-text-main'
              }`}
            >
              <History className="w-3 h-3" />
              History
            </button>
            <button
              onClick={() => setActiveTab('manual')}
              className={`flex-1 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1 ${
                activeTab === 'manual' ? 'bg-primary text-white shadow-sm' : 'text-text-muted hover:text-text-main'
              }`}
            >
              <Plus className="w-3 h-3" />
              Manual
            </button>
          </div>

          {/* Content */}
          <div className="flex-1 overflow-y-auto px-6 pb-8">
            {activeTab === 'quick' && (
              /* Quick Pick List */
              <div className="space-y-2">
                {filteredFoods.map((food, idx) => (
                  <div key={idx} className="relative group">
                    <button
                      onClick={() => handleQuickAdd(food)}
                      className="w-full flex items-center gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all active:scale-[0.98] text-left"
                    >
                      <span className="text-2xl flex-shrink-0">{food.emoji}</span>
                      <div className="flex-1 min-w-0">
                        <div className="text-sm font-bold text-text-main truncate">{food.name}</div>
                        <div className="text-xs text-text-muted mt-0.5">
                          P: {food.protein}g · C: {food.carbs}g · F: {food.fat}g
                        </div>
                      </div>
                      <div className="flex items-center gap-2 flex-shrink-0 mr-8">
                        <span className="text-sm font-bold text-primary">{food.calories}</span>
                        <span className="text-xs text-text-muted">kkal</span>
                      </div>
                    </button>
                    <button 
                      onClick={(e) => {
                        e.stopPropagation();
                        onSaveToFavorites?.(food);
                      }}
                      className="absolute right-4 top-1/2 -translate-y-1/2 p-2 rounded-full hover:bg-white/10 transition-colors"
                    >
                      <Bookmark className={`w-4 h-4 ${savedFoodIds.includes(food.name) ? 'fill-primary text-primary' : 'text-text-muted'}`} />
                    </button>
                  </div>
                ))}
              </div>
            )}

            {activeTab === 'history' && (
              /* History List */
              <div className="space-y-2">
                {historyItems.map((food) => (
                  <button
                    key={food.id}
                    onClick={() => handleQuickAdd(food as any)}
                    className="w-full flex items-center gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all active:scale-[0.98] text-left"
                  >
                    <span className="text-2xl flex-shrink-0">{food.emoji}</span>
                    <div className="flex-1 min-w-0">
                      <div className="text-sm font-bold text-text-main truncate">{food.name}</div>
                      <div className="text-xs text-text-muted mt-0.5">
                        Terakhir dimakan
                      </div>
                    </div>
                    <div className="flex items-center gap-2 flex-shrink-0">
                      <span className="text-sm font-bold text-primary">{food.calories}</span>
                      <span className="text-xs text-text-muted">kkal</span>
                      <ChevronRight className="w-4 h-4 text-text-muted" />
                    </div>
                  </button>
                ))}
                {historyItems.length === 0 && (
                  <div className="text-center py-12">
                    <div className="w-12 h-12 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-3">
                      <History className="w-6 h-6 text-text-muted" />
                    </div>
                    <p className="text-sm text-text-muted">Belum ada riwayat makanan.</p>
                  </div>
                )}
              </div>
            )}

            {activeTab === 'manual' && (
              /* Manual Input Form */
              <div className="space-y-4">
                {/* Emoji picker mini */}
                <div className="flex gap-2 flex-wrap">
                  {['🍽️','🍚','🍗','🥚','🍜','🥗','🍞','🥛','🍌','☕','🍰','🍕'].map(e => (
                    <button
                      key={e}
                      onClick={() => setManualForm(p => ({ ...p, emoji: e }))}
                      className={`text-2xl p-2 rounded-xl transition-all ${
                        manualForm.emoji === e ? 'bg-primary/20 scale-110' : 'bg-white/5 hover:bg-white/10'
                      }`}
                    >
                      {e}
                    </button>
                  ))}
                </div>

                {/* Name */}
                <div className="bg-white/5 rounded-2xl border border-white/10 overflow-hidden">
                  <div className="flex items-center px-4">
                    <span className="text-sm font-semibold text-text-muted w-24">Nama</span>
                    <input
                      type="text"
                      placeholder="Nasi goreng spesial"
                      value={manualForm.name}
                      onChange={(e) => setManualForm(p => ({ ...p, name: e.target.value }))}
                      className="flex-1 bg-transparent py-3.5 text-sm text-text-main placeholder:text-text-muted/50 focus:outline-none"
                    />
                  </div>
                </div>

                {/* Macros */}
                <div className="bg-white/5 rounded-2xl border border-white/10 overflow-hidden divide-y divide-white/10">
                  {[
                    { key: 'calories', label: 'Kalori', unit: 'kkal', icon: '🔥' },
                    { key: 'protein', label: 'Protein', unit: 'g', icon: '💪' },
                    { key: 'carbs', label: 'Karbo', unit: 'g', icon: '🌾' },
                    { key: 'fat', label: 'Lemak', unit: 'g', icon: '🫧' },
                  ].map(field => (
                    <div key={field.key} className="flex items-center px-4">
                      <span className="text-base mr-2">{field.icon}</span>
                      <span className="text-sm font-semibold text-text-main w-16">{field.label}</span>
                      <div className="flex-1 flex items-center justify-end gap-2">
                        <button
                          onClick={() => {
                            const val = parseInt(manualForm[field.key as keyof typeof manualForm] as string) || 0;
                            if (val > 0) setManualForm(p => ({ ...p, [field.key]: String(val - (field.key === 'calories' ? 10 : 1)) }));
                          }}
                          className="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center"
                        >
                          <Minus className="w-3 h-3 text-text-muted" />
                        </button>
                        <input
                          type="number"
                          inputMode="numeric"
                          value={manualForm[field.key as keyof typeof manualForm]}
                          onChange={(e) => setManualForm(p => ({ ...p, [field.key]: e.target.value }))}
                          placeholder="0"
                          className="w-16 bg-transparent py-3.5 text-center text-sm font-bold text-text-main placeholder:text-text-muted/50 focus:outline-none appearance-none"
                          style={{ MozAppearance: 'textfield' } as React.CSSProperties}
                        />
                        <button
                          onClick={() => {
                            const val = parseInt(manualForm[field.key as keyof typeof manualForm] as string) || 0;
                            setManualForm(p => ({ ...p, [field.key]: String(val + (field.key === 'calories' ? 10 : 1)) }));
                          }}
                          className="w-7 h-7 rounded-full bg-white/10 flex items-center justify-center"
                        >
                          <Plus className="w-3 h-3 text-text-muted" />
                        </button>
                      </div>
                      <span className="text-xs text-text-muted ml-2 w-8">{field.unit}</span>
                    </div>
                  ))}
                </div>

                {/* Submit */}
                <button
                  onClick={handleManualAdd}
                  disabled={!manualForm.name || !manualForm.calories}
                  className={`w-full py-4 rounded-[20px] font-bold text-lg transition-all active:scale-[0.98] ${
                    manualForm.name && manualForm.calories
                      ? 'bg-primary text-white shadow-lg shadow-primary/20'
                      : 'bg-white/5 text-text-muted cursor-not-allowed border border-white/10'
                  }`}
                >
                  Tambahkan {manualForm.emoji}
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    </>
  );
}
