import * as React from "react";
import { cva, type VariantProps } from "class-variance-authority";
import type { LucideIcon } from "lucide-react";
import { Slot } from "radix-ui";
import { cn } from "@/lib/utils";

const floatingButtonVariants = cva(
  "inline-flex items-center justify-center whitespace-nowrap transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 active:scale-90 shadow-lg hover:shadow-2xl hover:-translate-y-1",
  {
    variants: {
      size: {
        small: "w-[56px] h-[56px] rounded-[16px]",
        medium: "w-[80px] h-[80px] rounded-[20px]",
        large: "w-[96px] h-[96px] rounded-[28px]",
      },
      variant: {
        primary: "bg-primary text-primary-foreground hover:brightness-105",
        secondary:
          "bg-secondary text-secondary-foreground hover:bg-secondary/80",
        accent: "bg-accent text-accent-foreground hover:bg-accent/80",
        glass:
          "bg-white/10 backdrop-blur-xl border border-white/20 text-white hover:bg-white/20",
        gradient:
          "bg-gradient-to-tr from-indigo-600 via-purple-600 to-pink-600 text-white hover:brightness-110 shadow-purple-500/25",
        danger:
          "bg-destructive text-destructive-foreground hover:bg-destructive/90",
      },
    },
    defaultVariants: {
      size: "medium",
      variant: "primary",
    },
  },
);

const ICON_SIZE_MAP = {
  small: 24,
  medium: 28,
  large: 36,
} as const;

export interface FloatingButtonProps
  extends
    React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof floatingButtonVariants> {
  icon?: LucideIcon;
  asChild?: boolean;
}

const FloatingButton = React.forwardRef<HTMLButtonElement, FloatingButtonProps>(
  (
    {
      className,
      size = "medium",
      variant = "primary",
      icon: Icon,
      asChild = false,
      ...props
    },
    ref,
  ) => {
    const Comp = asChild ? Slot.Root : "button";
    const iconSize = ICON_SIZE_MAP[size || "medium"];

    return (
      <Comp
        ref={ref}
        data-slot="floating-button"
        data-variant={variant}
        data-size={size}
        aria-label={props["aria-label"] || "Floating action button"}
        className={cn(floatingButtonVariants({ size, variant, className }))}
        {...props}
      >
        {props.children || (Icon && <Icon size={iconSize} strokeWidth={2} />)}
      </Comp>
    );
  },
);

FloatingButton.displayName = "FloatingButton";

export { FloatingButton, floatingButtonVariants };
