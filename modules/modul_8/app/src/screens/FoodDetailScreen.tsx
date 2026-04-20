import {
  ArrowLeft,
  Share,
  MoreHorizontal,
  Bookmark,
  Flame,
  Beef,
  Wheat,
  Droplets,
  Minus,
  Plus,
  Sparkles,
  HeartCrack,
  Apple,
  Utensils,
  Droplet,
  Crosshair,
  ThumbsDown,
  ThumbsUp,
} from "lucide-react";
import { useRef, useState } from "react";
import { Button } from "@/components/ui";
import { cn } from "@/lib/utils";
import type { FoodItem } from "@/components/template/FoodCard";

function foodHealthScore(item: FoodItem): number {
  if (!item.calories || item.calories === 0) return 5;
  const pPct = (item.protein * 4) / item.calories;
  const cPct = (item.carbs * 4) / item.calories;
  const fPct = (item.fats * 9) / item.calories;
  const deviation = Math.abs(pPct - 0.3) + Math.abs(cPct - 0.4) + Math.abs(fPct - 0.3);
  return Math.min(10, Math.max(1, Math.round(10 - deviation * 10)));
}

export default function FoodDetailScreen({
  item,
  onClose,
  onLogged,
}: {
  item: FoodItem;
  onClose: () => void;
  onLogged?: () => void;
}) {
  const scrollRef = useRef<HTMLDivElement>(null);
  const [slideIndex, setSlideIndex] = useState(0);

  function handleScroll() {
    const el = scrollRef.current;
    if (!el) return;
    setSlideIndex(Math.round(el.scrollLeft / el.offsetWidth));
  }

  return (
    <div className="fixed inset-0 z-50 bg-background flex flex-col overflow-hidden animate-in slide-in-from-bottom-2 fade-in duration-300">
      {/* Top Image Section */}
      <div className="relative h-[40%] w-full bg-muted shrink-0">
        {item.imageUrl && (
          <img
            src={item.imageUrl}
            alt={item.name}
            className="w-full h-full object-cover"
          />
        )}

        {/* Header Overlay */}
        <div className="absolute top-0 inset-x-0 p-4 pt-12 flex items-center justify-between bg-gradient-to-b from-black/50 to-transparent">
          <button
            onClick={onClose}
            className="size-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white"
          >
            <ArrowLeft size={20} />
          </button>
          <span className="text-white font-bold text-lg drop-shadow-md">
            Selected food
          </span>
          <div className="flex items-center gap-2">
            <button className="size-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
              <Share size={18} />
            </button>
            <button className="size-10 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white">
              <MoreHorizontal size={20} />
            </button>
          </div>
        </div>
      </div>

      {/* Bottom Content Sheet */}
      <div className="flex-1 bg-app-bg -mt-8 rounded-t-[2rem] relative z-10 p-5 flex flex-col overflow-y-auto pb-32 shadow-[0_-8px_30px_rgba(0,0,0,0.12)]">
        <div className="flex items-center gap-3 mb-4">
          <button className="text-foreground">
            <Bookmark size={20} />
          </button>
          <div className="bg-muted px-3 py-1 rounded-full text-xs font-semibold text-muted-foreground">
            {item.time || "03:24 AM"}
          </div>
        </div>

        <div className="flex justify-between items-start gap-4 mb-6">
          <h1 className="text-2xl font-bold text-foreground leading-tight flex-1">
            {item.name || "Unknown Food"}
          </h1>
          <div className="flex items-center gap-4 bg-white dark:bg-slate-800  shadow-sm rounded-full px-3 py-2 shrink-0">
            <button className="text-muted-foreground hover:text-foreground">
              <Minus size={16} />
            </button>
            <span className="font-bold text-base w-4 text-center">1</span>
            <button className="text-foreground hover:text-foreground">
              <Plus size={16} />
            </button>
          </div>
        </div>

        {/* Carousel Area */}
        <div>
          <div
            ref={scrollRef}
            onScroll={handleScroll}
            className="flex flex-row overflow-x-auto no-scrollbar snap-x snap-mandatory gap-4 mb-3"
          >
            {/* Slide 1 */}
            <div className="snap-start shrink-0 w-full flex flex-col gap-4">
              {/* Calories Card */}
              <div className="bg-white dark:bg-slate-800  shadow-sm rounded-[1.5rem] p-4 flex items-center gap-4">
                <div className="size-14 rounded-2xl bg-muted/50 flex items-center justify-center shrink-0">
                  <Flame
                    size={24}
                    className="text-foreground"
                    fill="currentColor"
                  />
                </div>
                <div>
                  <p className="text-sm text-muted-foreground font-medium">
                    Calories
                  </p>
                  <p className="text-3xl font-extrabold text-foreground leading-none mt-1">
                    {item.calories}
                  </p>
                </div>
              </div>

              {/* Macros Row */}
              <div className="grid grid-cols-3 gap-3">
                <div className="bg-white dark:bg-slate-800  shadow-sm rounded-2xl p-3 flex flex-col items-center justify-center gap-1.5">
                  <div className="flex items-center gap-1.5">
                    <Beef
                      size={14}
                      className="text-red-400"
                      fill="currentColor"
                    />
                    <span className="text-xs text-muted-foreground font-medium">
                      Protein
                    </span>
                  </div>
                  <p className="text-base font-bold text-foreground">
                    {item.protein}g
                  </p>
                </div>
                <div className="bg-white dark:bg-slate-800  shadow-sm rounded-2xl p-3 flex flex-col items-center justify-center gap-1.5">
                  <div className="flex items-center gap-1.5">
                    <Wheat
                      size={14}
                      className="text-orange-400"
                      fill="currentColor"
                    />
                    <span className="text-xs text-muted-foreground font-medium">
                      Carbs
                    </span>
                  </div>
                  <p className="text-base font-bold text-foreground">
                    {item.carbs}g
                  </p>
                </div>
                <div className="bg-white dark:bg-slate-800  shadow-sm rounded-2xl p-3 flex flex-col items-center justify-center gap-1.5">
                  <div className="flex items-center gap-1.5">
                    <Droplets
                      size={14}
                      className="text-blue-500"
                      fill="currentColor"
                    />
                    <span className="text-xs text-muted-foreground font-medium">
                      Fats
                    </span>
                  </div>
                  <p className="text-base font-bold text-foreground">
                    {item.fats}g
                  </p>
                </div>
              </div>
            </div>

            {/* Slide 2 */}
            <div className="snap-start shrink-0 w-full flex flex-col gap-4">
              {/* Micronutrients Row */}
              <div className="grid grid-cols-3 gap-3">
                <div className="bg-white dark:bg-slate-800  shadow-sm rounded-2xl p-3 flex flex-col items-start justify-center gap-1">
                  <div className="flex items-center gap-1.5">
                    <Apple
                      size={14}
                      className="text-purple-500"
                      fill="currentColor"
                    />
                    <span className="text-xs text-muted-foreground font-medium">
                      Fiber
                    </span>
                  </div>
                  <p className="text-base font-bold text-foreground">1g</p>
                </div>
                <div className="bg-white dark:bg-slate-800  shadow-sm rounded-2xl p-3 flex flex-col items-start justify-center gap-1">
                  <div className="flex items-center gap-1.5">
                    <Utensils size={14} className="text-pink-500" />
                    <span className="text-xs text-muted-foreground font-medium">
                      Sugar
                    </span>
                  </div>
                  <p className="text-base font-bold text-foreground">10g</p>
                </div>
                <div className="bg-white dark:bg-slate-800  shadow-sm rounded-2xl p-3 flex flex-col items-start justify-center gap-1">
                  <div className="flex items-center gap-1.5">
                    <Droplet
                      size={14}
                      className="text-orange-400"
                      fill="currentColor"
                    />
                    <span className="text-xs text-muted-foreground font-medium">
                      Sodium
                    </span>
                  </div>
                  <p className="text-base font-bold text-foreground">60mg</p>
                </div>
              </div>

              {/* Health Score Card */}
              <div className="bg-white dark:bg-slate-800  shadow-sm rounded-[1.5rem] p-4 flex items-center gap-4">
                <div className="size-14 rounded-2xl bg-pink-50 dark:bg-pink-500/10 flex items-center justify-center shrink-0">
                  <HeartCrack
                    size={24}
                    className="text-pink-500"
                    fill="currentColor"
                  />
                </div>
                <div className="flex-1">
                  <div className="flex justify-between items-center mb-3">
                    <p className="text-sm font-medium text-foreground">
                      Health score
                    </p>
                    <p className="text-sm font-bold text-foreground">{foodHealthScore(item)}/10</p>
                  </div>
                  <div className="h-1.5 w-full bg-muted rounded-full overflow-hidden">
                    <div
                      className="h-full bg-[#4ADE80] rounded-full"
                      style={{ width: `${foodHealthScore(item) * 10}%` }}
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Pagination Dots */}
          <div className="flex justify-center gap-1.5 mb-8">
            {[0, 1].map((i) => (
              <div
                key={i}
                className={cn(
                  "size-2 rounded-full transition-all duration-200",
                  i === slideIndex
                    ? "bg-black"
                    : "border border-muted-foreground/30 bg-transparent",
                )}
              />
            ))}
          </div>
        </div>

        {/* Ingredients & Feedback Widget */}
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-lg font-bold text-foreground">Ingredients</h2>
          <button className="text-muted-foreground text-sm font-semibold flex items-center gap-1">
            <Plus size={14} /> Add
          </button>
        </div>

        {/* Feedback Widget */}
        <div className="bg-white dark:bg-slate-800  shadow-sm rounded-3xl p-4 flex items-center justify-between mb-8">
          <div className="flex items-center gap-3">
            <div className="size-10 rounded-full bg-muted/50 flex items-center justify-center">
              <Crosshair size={18} className="text-foreground" />
            </div>
            <p className="text-sm font-bold text-foreground">
              How did Cal AI do?
            </p>
          </div>
          <div className="flex gap-2">
            <button className="size-10 rounded-full  flex items-center justify-center hover:bg-muted text-foreground">
              <ThumbsDown size={18} />
            </button>
            <button className="size-10 rounded-full  flex items-center justify-center hover:bg-muted text-foreground">
              <ThumbsUp size={18} />
            </button>
          </div>
        </div>
      </div>

      {/* Bottom Action Bar */}
      <div className="absolute bottom-0 inset-x-0 bg-background/80 backdrop-blur-xl p-4 flex gap-3 z-20 pb-8">
        <Button
          variant="outline"
          className="flex-1 rounded-full h-14 font-bold text-base gap-2 bg-background border-transparent"
        >
          <Sparkles size={18} /> Fix Results
        </Button>
        <Button 
          className="flex-1 rounded-full h-14 font-bold text-base text-white bg-black"
          onClick={async () => {
            try {
              const { logMeal, toast } = await import("../services/api");
              const { savePhoto } = await import("../lib/photoStorage");
              const { logger } = await import("../lib/logger");
              const loadingId = toast.loading("Logging meal...");
              const res = await logMeal({
                meal_type: "snack",
                name: item.name,
                calories: item.calories,
                protein_g: item.protein,
                carbs_g: item.carbs,
                fats_g: item.fats,
                source: "ai_scan",
                ai_confidence: item.confidence ?? 0.9,
              });
              const mealId = res?.data?.id;
              if (mealId && item.imageUrl) {
                await savePhoto(String(mealId), item.imageUrl).catch((e) =>
                  logger.warn("FoodDetail", "Photo save failed", e)
                );
              }
              toast.dismiss(loadingId);
              toast.success("Meal logged successfully!");
              logger.info("FoodDetail", "Meal logged", { mealId, name: item.name });
              if (onLogged) { onLogged(); } else { onClose(); }
            } catch (err: unknown) {
              const { toast } = await import("../services/api");
              const msg = err instanceof Error ? err.message : "Failed to log meal";
              toast.error(msg);
            }
          }}
        >
          Done
        </Button>
      </div>
    </div>
  );
}
