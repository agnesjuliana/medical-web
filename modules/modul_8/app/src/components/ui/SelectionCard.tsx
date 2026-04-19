import React from "react";
import { cn } from "@/lib/utils";

export type SelectionCardProps = {
  label: string;
  description?: string;
  icon?: React.ReactNode;
  selected: boolean;
  onClick: () => void;
  className?: string;
};

export default function SelectionCard({
  label,
  description,
  icon,
  selected,
  onClick,
  className,
}: SelectionCardProps) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={cn(
        "w-full flex items-center gap-4 px-5 py-4 rounded-2xl text-left transition-colors",
        selected
          ? "bg-black text-white"
          : "bg-gray-100 text-foreground hover:bg-muted/70",
        className,
      )}
    >
      {icon && (
        <span
          className={cn(
            "flex size-10 shrink-0 items-center justify-center rounded-full text-lg",
            selected ? "bg-white/20" : "bg-muted",
          )}
        >
          {icon}
        </span>
      )}
      <span className="flex flex-col">
        <span className="text-base font-semibold leading-tight">{label}</span>
        {description && (
          <span
            className={cn(
              "text-sm mt-0.5",
              selected ? "text-white/70" : "text-muted-foreground",
            )}
          >
            {description}
          </span>
        )}
      </span>
    </button>
  );
}
