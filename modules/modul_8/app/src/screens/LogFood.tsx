import ScreenHeader from "@/components/header/ScreenHeader";
import FoodCard, { type FoodItem } from "@/components/template/FoodCard";
import React from "react";

const MOCK_FOOD_LIST: FoodItem[] = [
  {
    id: "1",
    status: "analyzing",
    imageUrl: "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&q=80",
    progress: 45,
  },
  {
    id: "2",
    status: "analyzed",
    name: "De Milan Wafer Cream Chocolate",
    time: "03:24 AM",
    calories: 150,
    protein: 2,
    carbs: 20,
    fats: 7,
    imageUrl: "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&q=80",
  },
  {
    id: "3",
    status: "analyzed",
    name: "Grilled Chicken Salad",
    time: "12:30 PM",
    calories: 320,
    protein: 35,
    carbs: 12,
    fats: 15,
    imageUrl: "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&q=80",
  },
  {
    id: "4",
    status: "analyzed",
    name: "Avocado Toast with Egg",
    time: "08:15 AM",
    calories: 420,
    protein: 18,
    carbs: 35,
    fats: 22,
    imageUrl: "https://images.unsplash.com/photo-1525351484163-7529414344d8?w=400&q=80",
  },
  {
    id: "5",
    status: "analyzed",
    name: "Protein Shake",
    time: "06:45 PM",
    calories: 210,
    protein: 30,
    carbs: 10,
    fats: 3,
  },
  {
    id: "6",
    status: "analyzed",
    name: "Salmon Sushi Roll",
    time: "01:00 PM",
    calories: 380,
    protein: 14,
    carbs: 55,
    fats: 10,
    imageUrl: "https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=400&q=80",
  },
  {
    id: "7",
    status: "analyzed",
    name: "Greek Yogurt with Berries",
    time: "10:00 AM",
    calories: 180,
    protein: 15,
    carbs: 22,
    fats: 4,
  },
  {
    id: "8",
    status: "analyzing",
    progress: 80,
  },
  {
    id: "9",
    status: "analyzed",
    name: "Oatmeal with Honey",
    time: "07:30 AM",
    calories: 250,
    protein: 6,
    carbs: 45,
    fats: 4,
    imageUrl: "https://images.unsplash.com/photo-1517673132405-a56a62b18caf?w=400&q=80",
  },
  {
    id: "10",
    status: "analyzed",
    name: "Almonds (Handful)",
    time: "04:00 PM",
    calories: 160,
    protein: 6,
    carbs: 6,
    fats: 14,
  },
];

interface LogFoodProps {
  onClose?: () => void;
}

export default function LogFood({ onClose }: LogFoodProps) {
  return (
    <div className="fixed inset-0 z-[60] bg-app-bg flex flex-col animate-in slide-in-from-bottom-2 fade-in duration-300">
      <ScreenHeader title="Saved Food" onBack={onClose || (() => {})} />
      <main className="p-4 flex-1 overflow-y-auto">
        <div className="flex flex-col gap-3">
          {MOCK_FOOD_LIST.filter((item) => item.status === "analyzed").map(
            (item) => (
              <FoodCard key={item.id} item={item} />
            ),
          )}
        </div>
      </main>
    </div>
  );
}
