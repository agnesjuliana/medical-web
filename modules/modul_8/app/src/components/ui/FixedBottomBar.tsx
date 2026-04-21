import type { ReactNode } from "react";
import { Button } from "./button";
import { cn } from "@/lib/utils";

export type FixedBottomBarProps = {
  label?: ReactNode;
  onContinue: () => void;
  disabled?: boolean;
  className?: string;
};

export default function FixedBottomBar({
  label = "Continue",
  onContinue,
  disabled = false,
  className,
}: FixedBottomBarProps) {
  return (
    <div
      className={cn(
        "fixed bottom-0 inset-x-0 px-4 pt-4 bg-background/80 backdrop-blur-sm z-50",
        className,
      )}
      style={{
        paddingBottom: "calc(2rem + env(safe-area-inset-bottom, 0px))",
      }}
    >
      <Button
        size="lg"
        onClick={onContinue}
        disabled={disabled}
        className="w-full h-14 rounded-full text-base font-semibold bg-black text-white hover:bg-black/90 disabled:bg-muted disabled:text-muted-foreground"
      >
        {label}
      </Button>
    </div>
  );
}
