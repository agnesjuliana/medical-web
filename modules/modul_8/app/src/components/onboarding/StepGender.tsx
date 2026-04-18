import { UserRound } from 'lucide-react';

export interface StepGenderProps {
  value: string;
  onChange: (val: string) => void;
}

export function StepGender({ value, onChange }: StepGenderProps) {
  return (
    <div className="space-y-8 animate-in fade-in zoom-in-95 duration-500 w-full max-w-lg mx-auto">
      <div className="text-center space-y-3">
        <h2 className="text-4xl font-extrabold tracking-tight text-text-main">Pilih Gender Anda</h2>
        <p className="text-text-muted font-medium text-sm max-w-xs mx-auto">Informasi ini membantu kami menghitung BMR Anda secara akurat.</p>
      </div>
      
      <div className="grid grid-cols-2 gap-4 mt-8 px-4">
        {[
          { id: 'male', label: 'Pria' },
          { id: 'female', label: 'Wanita' }
        ].map((g) => {
          const isSelected = value === g.id;
          return (
            <button
              key={g.id}
              onClick={() => onChange(g.id)}
              className={`relative group overflow-hidden flex flex-col items-center justify-center gap-4 py-10 rounded-[24px] border transition-all duration-300 ${
                isSelected 
                  ? `bg-primary/10 border-primary scale-[0.98]` 
                  : `bg-black/5 dark:bg-white/5 border-black/5 dark:border-white/10 backdrop-blur-md hover:bg-black/10 dark:hover:bg-white/10 hover:-translate-y-1`
              }`}
            >
              <div className={`p-4 rounded-full transition-all duration-300 ${isSelected ? 'bg-primary/20 text-primary scale-110' : 'bg-black/5 dark:bg-white/5 text-text-muted group-hover:bg-black/10 dark:group-hover:bg-white/10 group-hover:scale-105 group-hover:text-text-main'}`}>
                <UserRound className="w-10 h-10" />
              </div>
              <span className={`text-xl font-bold transition-colors duration-300 ${isSelected ? 'text-primary' : 'text-text-muted group-hover:text-text-main'}`}>
                {g.label}
              </span>
            </button>
          );
        })}
      </div>
    </div>
  );
}
