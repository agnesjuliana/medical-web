import { useEffect, useState } from "react";
import ScreenHeader from "@/components/header/ScreenHeader";
import FoodCard, { type FoodItem } from "@/components/template/FoodCard";
import { getSavedFoods, toast } from "../services/api";

interface LogFoodProps {
  onClose?: () => void;
}

export default function LogFood({ onClose }: LogFoodProps) {
  const [foods, setFoods] = useState<FoodItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    setIsLoading(true);
    getSavedFoods()
      .then((res) => {
        // Map backend items to FoodItem
        const mapped = res.data.map((item: any) => ({
          id: String(item.id),
          status: "analyzed", // Saved foods are already analyzed
          name: item.name,
          time: new Date(item.created_at || Date.now()).toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
          }),
          calories: item.calories,
          protein: item.protein_g,
          carbs: item.carbs_g,
          fats: item.fats_g,
          imageUrl: item.photo_url || undefined,
        }));
        setFoods(mapped);
      })
      .catch((err) => {
        console.error(err);
        setError("Failed to load saved foods");
        toast.error("Failed to load saved foods");
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, []);

  return (
    <div className="fixed inset-0 z-[60] bg-app-bg flex flex-col animate-in slide-in-from-bottom-2 fade-in duration-300">
      <ScreenHeader title="Saved Food" onBack={onClose || (() => {})} />
      <main className="p-4 flex-1 overflow-y-auto">
        <div className="flex flex-col gap-3">
          {isLoading ? (
            <div className="py-12 flex justify-center">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
          ) : error ? (
            <div className="bg-destructive/10 text-destructive rounded-2xl p-6 text-center text-sm">
              {error}
            </div>
          ) : foods.length === 0 ? (
            <div className="bg-white dark:bg-slate-800 rounded-3xl shadow-sm p-8 flex flex-col items-center gap-2">
              <p className="text-sm text-muted-foreground text-center">
                No saved foods found.
              </p>
            </div>
          ) : (
            foods.map((item) => (
              <FoodCard key={item.id} item={item} />
            ))
          )}
        </div>
      </main>
    </div>
  );
}
