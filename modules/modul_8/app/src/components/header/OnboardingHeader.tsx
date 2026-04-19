import { Button } from "../ui/button";
import { ArrowLeft } from "lucide-react";

export type OnboardingHeaderProps = {
  title: string;
  subtitle?: string;
  step: number;
  totalSteps: number;
  onBack?: () => void;
};

export default function OnboardingHeader({
  title,
  subtitle,
  step,
  totalSteps,
  onBack,
}: OnboardingHeaderProps) {
  const progress = Math.round((step / totalSteps) * 100);

  return (
    <header
      className="pb-6"
      style={{
        paddingTop: "calc(1rem + env(safe-area-inset-top, 0px))",
        paddingLeft: "calc(1rem + env(safe-area-inset-left, 0px))",
        paddingRight: "calc(1rem + env(safe-area-inset-right, 0px))",
      }}
    >
      <div className="flex items-center gap-3 mb-6">
        <Button
          variant="ghost"
          size="icon-lg"
          className="rounded-full bg-gray-100 shrink-0"
          onClick={onBack}
          aria-label="Go back"
        >
          <ArrowLeft />
        </Button>
        <div className="flex-1 h-1.5 rounded-full bg-gray-200 overflow-hidden">
          <div
            className="h-full rounded-full bg-black transition-all duration-300"
            style={{ width: `${progress}%` }}
          />
        </div>
      </div>
      <h1 className="text-2xl font-bold text-foreground">{title}</h1>
      {subtitle && (
        <p className="mt-1.5 text-sm text-muted-foreground">{subtitle}</p>
      )}
    </header>
  );
}
