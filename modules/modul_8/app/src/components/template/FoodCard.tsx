import { Flame, Beef, Wheat, Droplets } from "lucide-react";
import Ring from "@/components/ui/Ring";

export type FoodStatus = "analyzing" | "analyzed" | "failed";

export interface FoodItem {
  id: string;
  status: FoodStatus;
  name?: string;
  time?: string;
  calories?: number;
  protein?: number;
  carbs?: number;
  fats?: number;
  imageUrl?: string;
  progress?: number;
}

export function AnalyzingFoodCard({ item }: { item: FoodItem }) {
  return (
    <div className="bg-white dark:bg-slate-800 rounded-3xl shadow-sm p-4 flex flex-col gap-4">
      <div className="flex gap-4">
        {item.imageUrl ? (
          <div className="relative size-24 rounded-2xl overflow-hidden shrink-0">
            <img
              src={item.imageUrl}
              alt="uploading"
              className="w-full h-full object-cover"
            />
            <div className="absolute inset-0 bg-black/20 flex items-center justify-center">
              <Ring
                size={56}
                strokeWidth={5}
                value={(item.progress || 0) / 100}
                color="black"
              >
                <span className="text-white text-sm font-bold">
                  {item.progress}%
                </span>
              </Ring>
            </div>
          </div>
        ) : (
          <div className="relative size-24 rounded-2xl bg-muted shrink-0 flex items-center justify-center">
            <Ring
              size={56}
              strokeWidth={5}
              value={(item.progress || 0) / 100}
              color="black"
            >
              <span className="text-sm font-bold">{item.progress}%</span>
            </Ring>
          </div>
        )}

        <div className="flex flex-col justify-center flex-1 py-1">
          <p className="text-sm font-semibold text-foreground mb-4">
            Separating ingredients...
          </p>
          <div className="flex flex-col gap-2.5">
            <div className="h-2 bg-muted-foreground/20 rounded-full w-[80%]"></div>
            <div className="flex gap-2.5">
              <div className="h-2 bg-muted-foreground/20 rounded-full w-[35%]"></div>
              <div className="h-2 bg-muted-foreground/20 rounded-full w-[35%]"></div>
              <div className="h-2 bg-muted-foreground/20 rounded-full w-[35%]"></div>
            </div>
          </div>
        </div>
      </div>
      <p className="text-xs text-muted-foreground font-medium">
        We'll notify you when done!
      </p>
    </div>
  );
}

export function AnalyzedFoodCard({
  item,
  onClick,
}: {
  item: FoodItem;
  onClick?: () => void;
}) {
  return (
    <div
      onClick={onClick}
      className="bg-white dark:bg-slate-800 rounded-3xl shadow-sm p-4 flex gap-4 cursor-pointer active:scale-[0.98] transition-transform"
    >
      {item.imageUrl && (
        <div className="size-24 rounded-2xl overflow-hidden shrink-0">
          <img
            src={item.imageUrl}
            alt={item.name}
            className="w-full h-full object-cover"
          />
        </div>
      )}
      <div className="flex flex-col flex-1 justify-center py-0.5">
        <div className="flex justify-between items-start mb-2.5">
          <p className="text-base font-bold text-foreground leading-tight pr-2">
            {item.name}
          </p>
          <span className="text-[10px] text-muted-foreground shrink-0 mt-0.5">
            {item.time}
          </span>
        </div>

        <div className="flex items-center gap-1.5 mb-4">
          <Flame size={16} className="text-foreground" fill="currentColor" />
          <p className="text-sm font-semibold text-foreground">
            {item.calories} calories
          </p>
        </div>

        <div className="flex items-center gap-3.5 text-xs font-semibold mt-auto">
          <div className="flex items-center gap-1.5">
            <Beef size={14} className="text-red-400" fill="currentColor" />
            <span className="text-foreground">{item.protein}g</span>
          </div>
          <div className="flex items-center gap-1.5">
            <Wheat size={14} className="text-orange-400" fill="currentColor" />
            <span className="text-foreground">{item.carbs}g</span>
          </div>
          <div className="flex items-center gap-1.5">
            <Droplets size={14} className="text-blue-500" fill="currentColor" />
            <span className="text-foreground">{item.fats}g</span>
          </div>
        </div>
      </div>
    </div>
  );
}

export default function FoodCard({
  item,
  onClick,
}: {
  item: FoodItem;
  onClick?: () => void;
}) {
  if (item.status === "analyzing") {
    return <AnalyzingFoodCard item={item} />;
  }
  return <AnalyzedFoodCard item={item} onClick={onClick} />;
}
