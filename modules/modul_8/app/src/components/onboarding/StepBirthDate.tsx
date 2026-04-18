export interface StepBirthDateProps {
  day: string;
  month: string;
  year: string;
  onChange: (field: 'birth_day' | 'birth_month' | 'birth_year', val: string) => void;
}

export function StepBirthDate({ day, month, year, onChange }: StepBirthDateProps) {
  const days = Array.from({length: 31}, (_, i) => i + 1);
  const months = [
    { value: '01', label: 'Januari' }, { value: '02', label: 'Februari' }, { value: '03', label: 'Maret' },
    { value: '04', label: 'April' }, { value: '05', label: 'Mei' }, { value: '06', label: 'Juni' },
    { value: '07', label: 'Juli' }, { value: '08', label: 'Agustus' }, { value: '09', label: 'September' },
    { value: '10', label: 'Oktober' }, { value: '11', label: 'November' }, { value: '12', label: 'Desember' }
  ];
  const currentYear = new Date().getFullYear();
  const years = Array.from({length: 100}, (_, i) => currentYear - i);

  return (
    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500 w-full max-w-sm mx-auto">
      <div className="text-center space-y-3">
        <h2 className="text-4xl font-extrabold tracking-tight text-text-main">Kapan Lahir?</h2>
        <p className="text-text-muted font-medium text-sm max-w-xs mx-auto">Untuk menyesuaikan BMR harian berdasarkan usia Anda.</p>
      </div>
      <div className="mt-8 px-2">
        
        {/* iOS Grouped Style Container */}
        <div className="bg-black/5 dark:bg-white/5 backdrop-blur-md rounded-[20px] overflow-hidden border border-black/5 dark:border-white/10 flex flex-col divide-y divide-black/5 dark:divide-white/10">
          
          <div className="relative group flex items-center bg-transparent transition-colors focus-within:bg-black/5 dark:focus-within:bg-white/5">
            <span className="w-32 pl-5 text-[15px] font-semibold text-text-main">Bulan</span>
            <select 
              value={month} 
              onChange={(e) => onChange('birth_month', e.target.value)}
              className="flex-1 bg-transparent py-4 px-4 text-right text-lg text-text-main focus:outline-none focus:text-primary appearance-none cursor-pointer transition-colors"
            >
              <option value="" disabled className="text-gray-400">Pilih</option>
              {months.map((m) => (
                <option key={m.value} value={m.value} className="bg-surface text-text-main">{m.label}</option>
              ))}
            </select>
          </div>

          <div className="relative group flex items-center bg-transparent transition-colors focus-within:bg-black/5 dark:focus-within:bg-white/5">
            <span className="w-32 pl-5 text-[15px] font-semibold text-text-main">Tanggal</span>
            <select 
              value={day} 
              onChange={(e) => onChange('birth_day', e.target.value)}
              className="flex-1 bg-transparent py-4 px-4 text-right text-lg text-text-main focus:outline-none focus:text-primary appearance-none cursor-pointer transition-colors"
            >
              <option value="" disabled className="text-gray-400">Pilih</option>
              {days.map((d) => {
                const val = d.toString().padStart(2, '0');
                return <option key={val} value={val} className="bg-surface text-text-main">{val}</option>;
              })}
            </select>
          </div>

          <div className="relative group flex items-center bg-transparent transition-colors focus-within:bg-black/5 dark:focus-within:bg-white/5">
            <span className="w-32 pl-5 text-[15px] font-semibold text-text-main">Tahun</span>
            <select 
              value={year} 
              onChange={(e) => onChange('birth_year', e.target.value)}
              className="flex-1 bg-transparent py-4 px-4 text-right text-lg text-text-main focus:outline-none focus:text-primary appearance-none cursor-pointer transition-colors"
            >
              <option value="" disabled className="text-gray-400">Pilih</option>
              {years.map((y) => (
                <option key={y} value={String(y)} className="bg-surface text-text-main">{y}</option>
              ))}
            </select>
          </div>

        </div>
      </div>
    </div>
  );
}
