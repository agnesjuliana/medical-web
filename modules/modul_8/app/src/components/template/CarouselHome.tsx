import React from "react";
import {
  Flame,
  Beef,
  Wheat,
  Droplet,
  Leaf,
  Candy,
  Salad,
  Footprints,
} from "lucide-react";
import Ring from "@/components/ui/Ring";
import { cn } from "@/lib/utils";

interface MacroData {
  calories: number;
  protein: number;
  carbs: number;
  fat: number;
  fiber: number;
  sugar: number;
  sodium: number;
}

interface CarouselHomeProps {
  macroData: MacroData;
  healthScore: number;
  steps: number;
  caloriesBurned: number;
  waterIntake: number;
  onLogWater: () => void;
}

const CarouselCard = ({
  children,
  className,
}: {
  children: React.ReactNode;
  className?: string;
}) => (
  <div
    className={cn(
      "flex-none w-[85%] scroll-snap-align-center bg-white rounded-[24px] p-5 flex flex-col shadow-[0px_4px_20px_rgba(0,0,0,0.05)]",
      className,
    )}
    style={{ scrollSnapAlign: "center" }}
  >
    {children}
  </div>
);

const MacroRow = ({
  label,
  value,
  icon: Icon,
  color,
  unit = "g",
  ratio = 0.7,
}: {
  label: string;
  value: number;
  icon: any;
  color: string;
  unit?: string;
  ratio?: number;
}) => (
  <div className="flex items-center gap-3">
    <Ring value={ratio} size={32} strokeWidth={3} color={color}>
      <Icon size={14} color={color} />
    </Ring>
    <div className="flex flex-col">
      <span className="text-[12px] text-[#8E8E93]">{label}</span>
      <span className="text-[14px] font-semibold text-[#1C1C1E]">
        {value}
        {unit}
      </span>
    </div>
  </div>
);

export const CarouselHome: React.FC<CarouselHomeProps> = ({
  macroData,
  healthScore,
  steps,
  caloriesBurned,
  waterIntake,
  onLogWater,
}) => {
  return (
    <div className="w-full">
      <div
        className="flex overflow-x-auto snap-x snap-mandatory gap-4 px-5 py-4 scrollbar-hide"
        aria-label="Health and Nutrition Carousel"
      >
        {/* Slide 1: Daily Macros */}
        <CarouselCard>
          <h3 className="text-[16px] font-semibold text-[#1C1C1E]">
            Daily Macros
          </h3>
          <div className="flex flex-row justify-between items-center mt-4">
            {/* Calories Section */}
            <div className="flex flex-col items-center">
              <Ring
                value={0.75}
                size={80}
                strokeWidth={8}
                color="var(--color-cal)"
              >
                <Flame
                  size={32}
                  color="var(--color-cal)"
                  fill="var(--color-cal)"
                  fillOpacity={0.1}
                />
              </Ring>
              <div className="mt-2 text-center">
                <span className="text-[24px] font-bold text-[#1C1C1E]">
                  {macroData.calories}
                </span>
                <span className="text-[14px] text-[#8E8E93] ml-1">kcal</span>
              </div>
            </div>

            {/* Macros List */}
            <div className="flex flex-col gap-3 w-1/2">
              <MacroRow
                label="Protein"
                value={macroData.protein}
                icon={Beef}
                color="var(--color-protein)"
              />
              <MacroRow
                label="Carbs"
                value={macroData.carbs}
                icon={Wheat}
                color="var(--color-carbs)"
              />
              <MacroRow
                label="Fat"
                value={macroData.fat}
                icon={Droplet}
                color="var(--color-fats)"
              />
            </div>
          </div>
        </CarouselCard>

        {/* Slide 2: Nutrition Details */}
        <CarouselCard className="h-full grid grid-cols-2 gap-4">
          <h3 className="text-[16px] font-semibold text-[#1C1C1E]">
            Nutrition Details
          </h3>
          <div className="flex flex-col gap-4 mt-4">
            <MacroRow
              label="Fiber"
              value={macroData.fiber}
              icon={Leaf}
              color="var(--color-fiber)"
            />
            <MacroRow
              label="Sugar"
              value={macroData.sugar}
              icon={Candy}
              color="var(--color-sugar)"
            />
            <MacroRow
              label="Sodium"
              value={macroData.sodium}
              icon={Salad}
              color="var(--color-sodium)"
              unit="mg"
            />
          </div>
        </CarouselCard>

        {/* Slide 3: Activity & Water */}
        <CarouselCard>
          <h3 className="text-[16px] font-semibold text-[#1C1C1E]">
            Activity & Water
          </h3>
          <div className="grid grid-cols-2 gap-4 mt-4">
            <div className="flex flex-col p-3 rounded-2xl bg-[#F2F2F7]">
              <span className="text-[12px] text-[#8E8E93]">Health Score</span>
              <span
                className={cn(
                  "text-[24px] font-bold mt-1",
                  healthScore > 80 ? "text-[#34C759]" : "text-[#FF9500]",
                )}
              >
                {healthScore}
              </span>
            </div>
            <div className="flex flex-col p-3 rounded-2xl bg-[#F2F2F7]">
              <div className="flex items-center gap-2">
                <Footprints size={16} className="text-[#6C63FF]" />
                <span className="text-[12px] text-[#8E8E93]">Steps</span>
              </div>
              <span className="text-[20px] font-bold mt-1 text-[#1C1C1E]">
                {steps}
              </span>
            </div>
            <div className="flex flex-col p-3 rounded-2xl bg-[#F2F2F7]">
              <div className="flex items-center gap-2">
                <Flame size={16} className="text-[#FF6B2B]" />
                <span className="text-[12px] text-[#8E8E93]">Burned</span>
              </div>
              <span className="text-[20px] font-bold mt-1 text-[#1C1C1E]">
                {caloriesBurned} kcal
              </span>
            </div>
          </div>

          <div className="mt-4 pt-4 border-t border-[#F2F2F7] flex justify-between items-center">
            <div className="flex flex-col">
              <span className="text-[14px] text-[#1C1C1E]">
                Water: <span className="font-semibold">{waterIntake} L</span>
              </span>
            </div>
            <button
              onClick={onLogWater}
              className="bg-[#007AFF] text-white rounded-[16px] px-3 py-1.5 text-[14px] font-semibold border-none cursor-pointer active:scale-95 transition-transform"
            >
              + Log
            </button>
          </div>
        </CarouselCard>
      </div>
    </div>
  );
};

export default CarouselHome;
