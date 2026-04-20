import { cn } from "@/lib/utils";

export type RingProps = {
  value: number; // 0–1 fill ratio
  size: number;
  strokeWidth: number;
  color?: string;
  trackColor?: string;
  children?: React.ReactNode;
  className?: string;
};

export default function Ring({
  value,
  size,
  strokeWidth,
  color = "var(--color-cal)",
  trackColor = "var(--color-ring-track)",
  children,
  className,
}: RingProps) {
  const r = (size - strokeWidth) / 2;
  const c = 2 * Math.PI * r;
  const offset = c * (1 - Math.min(Math.max(value, 0), 1));

  return (
    <div
      className={cn("relative inline-flex items-center justify-center shrink-0", className)}
      style={{ width: size, height: size }}
    >
      <svg width={size} height={size} style={{ transform: "rotate(-90deg)" }}>
        <circle
          cx={size / 2} cy={size / 2} r={r}
          fill="none" stroke={trackColor} strokeWidth={strokeWidth}
        />
        {value > 0 && (
          <circle
            cx={size / 2} cy={size / 2} r={r}
            fill="none" stroke={color} strokeWidth={strokeWidth}
            strokeDasharray={c} strokeDashoffset={offset}
            strokeLinecap="round"
          />
        )}
      </svg>
      {children && (
        <div className="absolute inset-0 flex items-center justify-center">
          {children}
        </div>
      )}
    </div>
  );
}
