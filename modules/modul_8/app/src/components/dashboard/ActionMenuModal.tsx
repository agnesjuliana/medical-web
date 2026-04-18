import { Dumbbell, Bookmark, Search, Scan } from 'lucide-react';

interface ActionMenuModalProps {
  isOpen: boolean;
  onClose: () => void;
  onOpenFoodDatabase: () => void;
  onOpenLogExercise: () => void;
  onOpenSavedFoods: () => void;
  onOpenScanFood: () => void;
}

const MENU_ITEMS = [
  {
    id: 'log-exercise',
    label: 'Log exercise',
    icon: Dumbbell,
    color: '#f97316', // orange
    bgColor: 'rgba(249, 115, 22, 0.12)',
    action: 'logExercise' as const,
  },
  {
    id: 'saved-foods',
    label: 'Saved foods',
    icon: Bookmark,
    color: '#a78bfa', // violet
    bgColor: 'rgba(167, 139, 250, 0.12)',
    action: 'savedFoods' as const,
  },
  {
    id: 'food-database',
    label: 'Food Database',
    icon: Search,
    color: '#22d3ee', // cyan
    bgColor: 'rgba(34, 211, 238, 0.12)',
    action: 'foodDatabase' as const,
  },
  {
    id: 'scan-food',
    label: 'Scan food',
    icon: Scan,
    color: '#34d399', // emerald
    bgColor: 'rgba(52, 211, 153, 0.12)',
    action: 'scanFood' as const,
  },
];

export default function ActionMenuModal({
  isOpen,
  onClose,
  onOpenFoodDatabase,
  onOpenLogExercise,
  onOpenSavedFoods,
  onOpenScanFood,
}: ActionMenuModalProps) {
  if (!isOpen) return null;

  const handleAction = (action: string) => {
    onClose();
    // Use a small timeout to allow ActionMenu to close before opening the next modal
    setTimeout(() => {
      switch (action) {
        case 'foodDatabase':
          onOpenFoodDatabase();
          break;
        case 'logExercise':
          onOpenLogExercise();
          break;
        case 'savedFoods':
          onOpenSavedFoods();
          break;
        case 'scanFood':
          onOpenScanFood();
          break;
      }
    }, 100);
  };

  return (
    <>
      {/* Backdrop */}
      <div
        className="fixed inset-0 bg-black/60 backdrop-blur-sm z-40"
        onClick={onClose}
        style={{ animation: 'fadeIn 0.2s ease-out' }}
      />

      {/* Menu Container - positioned above FAB */}
      <div
        className="fixed inset-x-0 bottom-28 z-50 px-6 flex justify-center pointer-events-none"
        style={{ animation: 'slideUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)' }}
      >
        <div className="grid grid-cols-2 gap-3 w-full max-w-sm pointer-events-auto">
          {MENU_ITEMS.map((item) => {
            const Icon = item.icon;
            return (
              <button
                key={item.id}
                onClick={() => handleAction(item.action)}
                className="bg-white/8 backdrop-blur-xl rounded-[24px] border border-white/10 p-6 flex flex-col items-center justify-center gap-3 transition-all duration-200 active:scale-[0.94] hover:bg-white/12 hover:border-white/20"
              >
                <div
                  className="w-14 h-14 rounded-2xl flex items-center justify-center"
                  style={{ backgroundColor: item.bgColor }}
                >
                  <Icon className="w-7 h-7" style={{ color: item.color }} />
                </div>
                <span className="font-bold text-sm text-text-main">{item.label}</span>
              </button>
            );
          })}
        </div>
      </div>

      {/* Inline keyframes */}
      <style>{`
        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }
        @keyframes slideUp {
          from { opacity: 0; transform: translateY(24px) scale(0.95); }
          to { opacity: 1; transform: translateY(0) scale(1); }
        }
      `}</style>
    </>
  );
}
