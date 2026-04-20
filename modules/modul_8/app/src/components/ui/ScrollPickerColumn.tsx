import { useRef, useEffect, useCallback } from "react";
import { cn } from "@/lib/utils";

export type ScrollPickerColumnProps = {
  items: string[];
  value: string;
  onChange: (value: string) => void;
  label?: string;
  className?: string;
};

const ITEM_HEIGHT = 48;
const VISIBLE_ITEMS = 5; // odd number so center is the selected item
const CONTAINER_HEIGHT = ITEM_HEIGHT * VISIBLE_ITEMS;
const PADDING = ITEM_HEIGHT * Math.floor(VISIBLE_ITEMS / 2);

export default function ScrollPickerColumn({
  items,
  value,
  onChange,
  label,
  className,
}: ScrollPickerColumnProps) {
  const containerRef = useRef<HTMLDivElement>(null);
  const isScrolling = useRef(false);
  const scrollTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

  const scrollToIndex = useCallback(
    (index: number, smooth = true) => {
      containerRef.current?.scrollTo({
        top: index * ITEM_HEIGHT,
        behavior: smooth ? "smooth" : "instant",
      });
    },
    []
  );

  // Sync scroll position when value changes externally
  useEffect(() => {
    const idx = items.indexOf(value);
    if (idx >= 0 && !isScrolling.current) {
      scrollToIndex(idx, false);
    }
  }, [value, items, scrollToIndex]);

  const handleScroll = () => {
    if (!containerRef.current) return;
    isScrolling.current = true;

    if (scrollTimer.current) clearTimeout(scrollTimer.current);
    scrollTimer.current = setTimeout(() => {
      if (!containerRef.current) return;
      const rawIndex = containerRef.current.scrollTop / ITEM_HEIGHT;
      const snappedIndex = Math.round(rawIndex);
      const clamped = Math.max(0, Math.min(snappedIndex, items.length - 1));

      scrollToIndex(clamped);
      onChange(items[clamped]);
      isScrolling.current = false;
    }, 100);
  };

  return (
    <div className={cn("flex flex-col items-center gap-2", className)}>
      {label && (
        <span className="text-sm font-semibold text-foreground">{label}</span>
      )}

      <div className="relative" style={{ height: CONTAINER_HEIGHT }}>
        {/* selection highlight */}
        <div
          className="pointer-events-none absolute inset-x-0 rounded-xl bg-muted z-10"
          style={{
            top: PADDING,
            height: ITEM_HEIGHT,
          }}
        />

        <div
          ref={containerRef}
          onScroll={handleScroll}
          className="h-full overflow-y-scroll no-scrollbar relative"
          style={{ scrollSnapType: "y mandatory" }}
        >
          {/* top spacer */}
          <div style={{ height: PADDING }} />

          {items.map((item) => {
            const isSelected = item === value;
            return (
              <div
                key={item}
                style={{ height: ITEM_HEIGHT, scrollSnapAlign: "center" }}
                className={cn(
                  "flex items-center justify-center px-4 transition-all duration-150 text-base select-none",
                  isSelected
                    ? "font-semibold text-foreground"
                    : "text-muted-foreground/50 text-sm"
                )}
              >
                {item}
              </div>
            );
          })}

          {/* bottom spacer */}
          <div style={{ height: PADDING }} />
        </div>
      </div>
    </div>
  );
}
