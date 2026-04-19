import React, { useRef, useEffect, useCallback } from "react";
import { cn } from "@/lib/utils";

export type RulerPickerProps = {
  min?: number;
  max?: number;
  step?: number;
  value: number;
  onChange: (value: number) => void;
  unit?: string;
  label?: string;
  className?: string;
};

// px between each tick mark
const TICK_SPACING = 14;

// Tick hierarchy levels
const getTickLevel = (index: number): 1 | 2 | 3 => {
  if (index % 10 === 0) return 3; // Tallest (every 10 units)
  if (index % 5 === 0)  return 2; // Medium (every 5 units)
  return 1;                        // Shortest (every 1 unit)
};

export default function RulerPicker({
  min = 30,
  max = 200,
  step = 1,
  value,
  onChange,
  unit = "kg",
  label,
  className,
}: RulerPickerProps) {
  const scrollRef = useRef<HTMLDivElement>(null);
  const scrollTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
  const isTouching = useRef(false);

  const valueToLeft = useCallback(
    (v: number) => Math.round(((v - min) / step) * TICK_SPACING),
    [min, step]
  );

  const leftToValue = useCallback(
    (left: number) => {
      const raw = (left / TICK_SPACING) * step + min;
      const snapped = Math.round(raw / step) * step;
      return Math.max(min, Math.min(max, snapped));
    },
    [min, max, step]
  );

  // Sync scroll position when value changes externally
  useEffect(() => {
    if (!scrollRef.current || isTouching.current) return;
    scrollRef.current.scrollLeft = valueToLeft(value);
  }, [value, valueToLeft]);

  const handleScroll = () => {
    if (!scrollRef.current) return;
    const snapped = leftToValue(scrollRef.current.scrollLeft);
    onChange(snapped);

    if (scrollTimer.current) clearTimeout(scrollTimer.current);
    scrollTimer.current = setTimeout(() => {
      if (!scrollRef.current) return;
      scrollRef.current.scrollTo({
        left: valueToLeft(snapped),
        behavior: "smooth",
      });
    }, 120);
  };

  const tickCount = Math.round((max - min) / step) + 1;

  return (
    <div className={cn("flex flex-col items-center w-full gap-3", className)}>
      {label && (
        <p className="text-sm text-muted-foreground font-medium">{label}</p>
      )}

      <p className="text-5xl font-bold text-foreground tabular-nums tracking-tight">
        {value.toFixed(1)}{" "}
        <span className="text-3xl">{unit}</span>
      </p>

      {/* ruler container */}
      <div className="relative w-full h-24 bg-muted/30 overflow-hidden">
        {/* fixed center indicator line */}
        <div className="pointer-events-none absolute inset-y-0 left-1/2 -translate-x-1/2 w-px bg-foreground z-20" />

        {/* scrollable ticks */}
        <div
          ref={scrollRef}
          onScroll={handleScroll}
          onTouchStart={() => { isTouching.current = true; }}
          onTouchEnd={() => { isTouching.current = false; }}
          className="absolute inset-0 overflow-x-scroll no-scrollbar flex items-center"
          style={{ scrollSnapType: "x mandatory" }}
        >
          {/* left spacer so first tick centres */}
          <div className="shrink-0" style={{ width: "50vw" }} />

          {Array.from({ length: tickCount }, (_, i) => {
            const tickValue = min + i * step;
            const level = getTickLevel(i);

            const tickHeights = {
              1: "h-2",   // Minor: very short
              2: "h-4",   // Medium
              3: "h-8",   // Major: tall
            };

            const showLabel = level === 3;

            return (
              <div
                key={tickValue}
                className="shrink-0 flex flex-col items-center"
                style={{
                  width: TICK_SPACING,
                  scrollSnapAlign: "start",
                }}
              >
                <div
                  className={cn(
                    "w-px rounded-full",
                    tickHeights[level],
                    level === 3
                      ? "bg-foreground"
                      : level === 2
                      ? "bg-muted-foreground/60"
                      : "bg-muted-foreground/30"
                  )}
                />
                {showLabel && (
                  <span className="mt-1.5 text-[9px] font-medium text-foreground/70 tabular-nums select-none">
                    {tickValue}
                  </span>
                )}
              </div>
            );
          })}

          {/* right spacer */}
          <div className="shrink-0" style={{ width: "50vw" }} />
        </div>
      </div>
    </div>
  );
}
