import { cn } from "@/lib/utils";

export type DaylistProps = {
  selectedDate?: Date;
  onDaySelect?: (date: Date) => void;
  className?: string;
};

const DAY_LABELS = ["S", "M", "T", "W", "T", "F", "S"];

// Rolling 7-day window: 5 days before today … today … 1 day ahead
function getRollingDays(today: Date): Date[] {
  return Array.from({ length: 7 }, (_, i) => {
    const d = new Date(today);
    d.setDate(today.getDate() - 5 + i);
    return d;
  });
}

function isSameDay(a: Date, b: Date) {
  return (
    a.getFullYear() === b.getFullYear() &&
    a.getMonth() === b.getMonth() &&
    a.getDate() === b.getDate()
  );
}

export default function Daylist({
  selectedDate,
  onDaySelect,
  className,
}: DaylistProps) {
  const today = new Date();
  const selected = selectedDate ?? today;
  const days = getRollingDays(today);

  return (
    <div
      className={cn(
        "w-full flex flex-row items-end justify-between overflow-x-auto no-scrollbar",
        className,
      )}
    >
      {days.map((day) => {
        const isSelected = isSameDay(day, selected);
        const isToday = isSameDay(day, today);
        const isFuture = day > today && !isToday;

        return (
          <button
            key={day.toISOString()}
            type="button"
            onClick={() => onDaySelect?.(day)}
            className={cn(
              "flex flex-col items-center gap-1.5 px-1 select-none cursor-pointer",
              isFuture && "opacity-40",
            )}
          >
            <span
              className={cn(
                "text-xs",
                isSelected
                  ? "font-bold text-foreground"
                  : "font-medium text-muted-foreground",
              )}
            >
              {DAY_LABELS[day.getDay()]}
            </span>
            <span
              className={cn(
                "size-9 rounded-full flex items-center justify-center text-sm border-2 border-dashed transition-colors",
                isSelected
                  ? "border-foreground text-foreground font-bold"
                  : "border-muted-foreground/30 text-foreground font-medium",
              )}
            >
              {day.getDate()}
            </span>
          </button>
        );
      })}
    </div>
  );
}
