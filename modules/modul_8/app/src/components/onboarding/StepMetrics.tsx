import { Ruler, Scale } from 'lucide-react';

export interface StepMetricsProps {
  height_cm: string;
  weight_kg: string;
  onChange: (field: string, val: string) => void;
}

export function StepMetrics({ height_cm, weight_kg, onChange }: StepMetricsProps) {
  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 w-full max-w-sm mx-auto">
      <div className="text-center space-y-3">
        <h2 className="text-4xl font-extrabold tracking-tight text-text-main">Metrik Tubuh</h2>
        <p className="text-text-muted font-medium text-sm max-w-xs mx-auto">Beritahu kami metrik tubuh Anda saat ini.</p>
      </div>
      
      <div className="mt-8 px-2">
        {/* iOS Grouped Style Container */}
        <div className="bg-black/5 dark:bg-white/5 backdrop-blur-md rounded-[20px] overflow-hidden border border-black/5 dark:border-white/10 flex flex-col divide-y divide-black/5 dark:divide-white/10">
          
          {/* Tinggi Badan */}
          <div className="relative flex items-center group bg-transparent transition-colors focus-within:bg-black/5 dark:focus-within:bg-white/5">
            <div className="pl-5 text-text-muted group-focus-within:text-primary transition-colors">
              <Ruler className="w-5 h-5" />
            </div>
            <div className="flex-1 flex items-center">
              <span className="w-32 pl-4 text-[15px] font-semibold text-text-main">Tinggi</span>
              <input 
                type="number"
                placeholder="170"
                value={height_cm}
                onChange={(e) => onChange('height_cm', e.target.value)}
                className="flex-1 bg-transparent py-4 pr-4 text-right text-lg font-bold text-text-main placeholder-text-muted focus:outline-none"
              />
            </div>
            <div className="pr-5 text-[15px] font-medium text-text-muted">Cm</div>
          </div>

          {/* Berat Badan */}
          <div className="relative flex items-center group bg-transparent transition-colors focus-within:bg-black/5 dark:focus-within:bg-white/5">
            <div className="pl-5 text-text-muted group-focus-within:text-primary transition-colors">
              <Scale className="w-5 h-5" />
            </div>
            <div className="flex-1 flex items-center">
              <span className="w-32 pl-4 text-[15px] font-semibold text-text-main">Berat</span>
              <input 
                type="number"
                placeholder="65"
                value={weight_kg}
                onChange={(e) => onChange('weight_kg', e.target.value)}
                className="flex-1 bg-transparent py-4 pr-4 text-right text-lg font-bold text-text-main placeholder-text-muted focus:outline-none"
              />
            </div>
            <div className="pr-5 text-[15px] font-medium text-text-muted">Kg</div>
          </div>

        </div>
      </div>
    </div>
  );
}
