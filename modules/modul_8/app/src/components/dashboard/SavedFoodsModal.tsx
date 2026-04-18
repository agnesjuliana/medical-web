import { X, Bookmark, Plus, ChevronRight, Trash2 } from 'lucide-react';
import type { FoodEntry } from '../../types/food';

interface SavedFoodsModalProps {
  isOpen: boolean;
  onClose: () => void;
  savedFoods: Partial<FoodEntry>[];
  onAdd: (food: Partial<FoodEntry>) => void;
  onRemove: (name: string) => void;
}

export default function SavedFoodsModal({ 
  isOpen, 
  onClose, 
  savedFoods, 
  onAdd, 
  onRemove 
}: SavedFoodsModalProps) {
  if (!isOpen) return null;

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
            <h2 className="text-xl font-bold text-text-main flex items-center gap-2">
              <Bookmark className="w-5 h-5 text-primary fill-primary" />
              Makanan Tersimpan
            </h2>
            <button
              onClick={onClose}
              className="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center"
            >
              <X className="w-4 h-4 text-text-muted" />
            </button>
          </div>

          {/* Content */}
          <div className="flex-1 overflow-y-auto px-6 pb-8">
            <div className="space-y-3 pt-2">
              {savedFoods.map((food, idx) => (
                <div 
                  key={idx}
                  className="w-full flex items-center gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-all group"
                >
                  <span className="text-2xl flex-shrink-0">{food.emoji}</span>
                  <div className="flex-1 min-w-0">
                    <div className="text-sm font-bold text-text-main truncate">{food.name}</div>
                    <div className="text-xs text-text-muted mt-0.5">
                      P: {food.protein}g · C: {food.carbs}g · F: {food.fat}g
                    </div>
                  </div>
                  
                  <div className="flex items-center gap-2">
                    <button
                      onClick={() => onRemove(food.name || '')}
                      className="p-2 rounded-xl bg-red-500/10 text-red-400 opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                    <button
                      onClick={() => onAdd(food)}
                      className="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:shadow-lg hover:shadow-primary/20 transition-all active:scale-95"
                    >
                      <Plus className="w-3 h-3" />
                      Tambah
                    </button>
                  </div>
                </div>
              ))}

              {savedFoods.length === 0 && (
                <div className="text-center py-16">
                  <div className="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Bookmark className="w-8 h-8 text-text-muted" />
                  </div>
                  <h3 className="text-text-main font-bold mb-1">Belum ada makanan favorit</h3>
                  <p className="text-xs text-text-muted px-12 leading-relaxed">
                    Tap ikon bookmark pada makanan saat mencari untuk menyimpannya di sini.
                  </p>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
