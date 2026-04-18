// =============================================================================
// Food tracking type definitions
// =============================================================================

export interface FoodEntry {
  id: string;
  name: string;
  calories: number;
  protein: number;   // grams
  carbs: number;     // grams
  fat: number;       // grams
  fiber?: number;    // grams 
  sugar?: number;    // grams
  sodium?: number;   // milligrams
  emoji: string;
  timestamp: Date;
}

export interface DailyLog {
  date: string;       // YYYY-MM-DD
  entries: FoodEntry[];
  totalCalories: number;
  totalProtein: number;
  totalCarbs: number;
  totalFat: number;
  totalFiber: number;
  totalSugar: number;
  totalSodium: number;
}

// Common Indonesian foods for quick add (dummy fiber, sugar, sodium estimations)
export const COMMON_FOODS: Omit<FoodEntry, 'id' | 'timestamp'>[] = [
  { name: 'Nasi Putih (1 porsi)', calories: 204, protein: 4, carbs: 44, fat: 0, fiber: 0.6, sugar: 0.1, sodium: 1, emoji: '🍚' },
  { name: 'Ayam Goreng (1 potong)', calories: 260, protein: 27, carbs: 3, fat: 15, fiber: 0, sugar: 0, sodium: 450, emoji: '🍗' },
  { name: 'Telur Rebus (1 butir)', calories: 78, protein: 6, carbs: 1, fat: 5, fiber: 0, sugar: 0.6, sodium: 62, emoji: '🥚' },
  { name: 'Tempe Goreng (2 potong)', calories: 150, protein: 10, carbs: 8, fat: 9, fiber: 4, sugar: 0.5, sodium: 180, emoji: '🫘' },
  { name: 'Sayur Bayam (1 mangkuk)', calories: 36, protein: 3, carbs: 4, fat: 1, fiber: 2.5, sugar: 1, sodium: 200, emoji: '🥬' },
  { name: 'Tahu Goreng (2 potong)', calories: 140, protein: 8, carbs: 3, fat: 10, fiber: 2, sugar: 0.5, sodium: 150, emoji: '🧈' },
  { name: 'Ikan Bakar (1 potong)', calories: 200, protein: 30, carbs: 0, fat: 8, fiber: 0, sugar: 0, sodium: 300, emoji: '🐟' },
  { name: 'Mie Goreng (1 piring)', calories: 380, protein: 8, carbs: 52, fat: 15, fiber: 2, sugar: 6, sodium: 850, emoji: '🍜' },
  { name: 'Salad Buah (1 mangkuk)', calories: 120, protein: 1, carbs: 30, fat: 1, fiber: 4, sugar: 20, sodium: 5, emoji: '🥗' },
  { name: 'Susu (1 gelas)', calories: 150, protein: 8, carbs: 12, fat: 8, fiber: 0, sugar: 12, sodium: 105, emoji: '🥛' },
  { name: 'Roti Gandum (2 lembar)', calories: 140, protein: 6, carbs: 24, fat: 2, fiber: 4, sugar: 3, sodium: 280, emoji: '🍞' },
  { name: 'Pisang (1 buah)', calories: 89, protein: 1, carbs: 23, fat: 0, fiber: 2.6, sugar: 12, sodium: 1, emoji: '🍌' },
  { name: 'Kopi Susu (1 gelas)', calories: 120, protein: 4, carbs: 16, fat: 4, fiber: 0, sugar: 15, sodium: 60, emoji: '☕' },
  { name: 'Sate Ayam (5 tusuk)', calories: 250, protein: 20, carbs: 5, fat: 16, fiber: 1, sugar: 6, sodium: 550, emoji: '🍢' },
  { name: 'Gado-gado (1 porsi)', calories: 300, protein: 12, carbs: 25, fat: 18, fiber: 8, sugar: 10, sodium: 600, emoji: '🥜' },
];
