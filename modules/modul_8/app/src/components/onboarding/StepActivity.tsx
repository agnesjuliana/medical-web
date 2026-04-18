import { MousePointerClick, Zap, Flame, Trophy } from 'lucide-react';

export interface StepActivityProps {
  value: string;
  onChange: (val: string) => void;
}

export function StepActivity({ value, onChange }: StepActivityProps) {
  const activities = [
    { id: '1.2', label: 'Jarang Olahraga', desc: 'Aktivitas minim, banyak duduk', icon: <MousePointerClick className="w-6 h-6" /> },
    { id: '1.375', label: '1 - 3x Seminggu', desc: 'Olahraga ringan, jalan kaki', icon: <Zap className="w-6 h-6" /> },
    { id: '1.55', label: '3 - 5x Seminggu', desc: 'Olahraga sedang, lari santai', icon: <Flame className="w-6 h-6" /> },
    { id: '1.725', label: '6 - 7x Seminggu', desc: 'Olahraga berat, gym rutin', icon: <Trophy className="w-6 h-6" /> }
  ];

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 w-full max-w-lg mx-auto">
      <div className="text-center space-y-3">
        <h2 className="text-4xl font-extrabold tracking-tight text-text-main">Aktivitas Harian?</h2>
        <p className="text-text-muted font-medium text-sm max-w-xs mx-auto">Ini menentukan total pembakaran kalori harian (TDEE).</p>
      </div>
      
      <div className="flex flex-col gap-3 mt-6 px-2">
        {activities.map((act) => {
          const isSelected = value === act.id;
          return (
            <button
              key={act.id}
              onClick={() => onChange(act.id)}
              className={`relative group overflow-hidden flex items-center gap-5 p-5 pr-6 rounded-[20px] border transition-all duration-300 text-left ${
                isSelected 
                  ? 'bg-primary/10 border-primary scale-[0.99]' 
                  : 'bg-black/5 dark:bg-white/5 border-black/5 dark:border-white/10 backdrop-blur-md hover:bg-black/10 dark:hover:bg-white/10'
              }`}
            >
              <div className={`flex-shrink-0 p-3 rounded-2xl transition-all duration-300 ${
                isSelected ? 'bg-primary/20 text-primary' : 'bg-black/5 dark:bg-white/5 text-text-muted group-hover:bg-black/10 dark:group-hover:bg-white/10 group-hover:text-text-main'
              }`}>
                {act.icon}
              </div>
              
              <div className="flex-1">
                <div className={`font-bold text-lg transition-colors duration-300 ${isSelected ? 'text-primary' : 'text-text-main'}`}>
                  {act.label}
                </div>
                <div className={`text-sm mt-0.5 transition-colors duration-300 ${isSelected ? 'text-primary/80' : 'text-text-muted'}`}>
                  {act.desc}
                </div>
              </div>
              
              {/* Native Radio Indicator */}
              <div className={`w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-300 ${
                isSelected ? 'border-primary' : 'border-text-muted/30 group-hover:border-text-muted/60'
              }`}>
                <div className={`w-3 h-3 rounded-full bg-primary transition-all duration-300 ${isSelected ? 'scale-100' : 'scale-0'}`} />
              </div>
            </button>
          );
        })}
      </div>
    </div>
  );
}
