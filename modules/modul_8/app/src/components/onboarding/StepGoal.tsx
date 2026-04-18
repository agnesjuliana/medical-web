import { TrendingDown, Minus, TrendingUp } from 'lucide-react';

export interface StepGoalProps {
  value: string;
  onChange: (val: string) => void;
}

export function StepGoal({ value, onChange }: StepGoalProps) {
  const goals = [
    { id: 'lose', label: 'Turunkan Berat Badan', desc: 'Defisit 500 kalori/hari', icon: <TrendingDown className="w-6 h-6" /> },
    { id: 'maintain', label: 'Pertahankan Berat', desc: 'Sesuai dengan TDEE', icon: <Minus className="w-6 h-6" /> },
    { id: 'gain', label: 'Naikkan Berat Badan', desc: 'Surplus 500 kalori/hari', icon: <TrendingUp className="w-6 h-6" /> }
  ];

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 w-full max-w-lg mx-auto">
      <div className="text-center space-y-3">
        <h2 className="text-4xl font-extrabold tracking-tight text-text-main">Apa Tujuan Anda?</h2>
        <p className="text-text-muted font-medium text-sm max-w-xs mx-auto">Ini akan disesuaikan dengan target nutrisi Anda.</p>
      </div>
      
      <div className="flex flex-col gap-3 mt-6 px-2">
        {goals.map((g) => {
          const isSelected = value === g.id;
          return (
            <button
              key={g.id}
              onClick={() => onChange(g.id)}
              className={`relative group overflow-hidden flex items-center justify-between p-5 px-6 rounded-[20px] border transition-all duration-300 text-left ${
                isSelected 
                  ? 'bg-primary/10 border-primary scale-[0.99]' 
                  : 'bg-black/5 dark:bg-white/5 border-black/5 dark:border-white/10 backdrop-blur-md hover:bg-black/10 dark:hover:bg-white/10'
              }`}
            >
              <div className="flex items-center gap-5 z-10">
                <div className={`p-3 rounded-2xl transition-all duration-300 ${
                  isSelected ? 'bg-primary/20 text-primary' : 'bg-black/5 dark:bg-white/5 text-text-muted group-hover:bg-black/10 dark:group-hover:bg-white/10 group-hover:text-text-main'
                }`}>
                  {g.icon}
                </div>
                <div>
                  <div className={`font-bold text-lg transition-colors duration-300 ${isSelected ? 'text-primary' : 'text-text-main'}`}>
                    {g.label}
                  </div>
                  <div className={`text-sm mt-0.5 transition-colors duration-300 ${isSelected ? 'text-primary/80' : 'text-text-muted'}`}>
                    {g.desc}
                  </div>
                </div>
              </div>
              
              <div className={`w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-300 z-10 ${
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
