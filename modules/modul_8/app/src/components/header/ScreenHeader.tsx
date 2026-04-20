import { ChevronLeft } from "lucide-react";

interface ScreenHeaderProps {
  title: string;
  onBack: () => void;
  className?: string;
}

export default function ScreenHeader({ title, onBack, className = "" }: ScreenHeaderProps) {
  return (
    <div className={`flex items-center justify-center p-4 relative pt-12 shrink-0 ${className}`}>
      <button
        onClick={onBack}
        className="absolute left-4 size-10 rounded-full bg-white dark:bg-slate-800 flex items-center justify-center text-foreground shadow-[0_2px_12px_rgba(0,0,0,0.08)] dark:shadow-none hover:bg-slate-50 dark:hover:bg-slate-700 transition-all active:scale-95"
      >
        <ChevronLeft size={24} strokeWidth={2.5} className="ml-[-2px]" />
      </button>
      <h1 className="text-[17px] font-semibold text-foreground">{title}</h1>
    </div>
  );
}
