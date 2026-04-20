import { create } from "zustand";
import {
  getWeightProgress,
  getWeeklyEnergy,
  getProgressSummary,
  toast,
} from "../services/api";

export type WeightRange = 90 | 180 | 365 | "all";
export type WeekOffset = 0 | 1 | 2 | 3;

export interface WeightProgress {
  current_weight: number;
  start_weight: number;
  goal_weight: number | null;
  goal_progress: number;
  height_cm: number;
  bmi: number;
  logs: Array<{ day: string; date: string; weight: number }>;
  deltas: { "3d": number; "7d": number; "30d": number };
}

export interface WeeklyEnergy {
  week_start: string;
  week_end: string;
  days: Array<{ day: string; date: string; consumed_cal: number }>;
  total_consumed: number;
}

export interface CalorieAverages {
  avg_7d: number | null;
  avg_30d: number | null;
  logs_7d: Array<{ log_date: string; calories: number }>;
}

interface ProgressState {
  weightData: WeightProgress | null;
  energyData: WeeklyEnergy | null;
  calorieData: CalorieAverages | null;
  loadingWeight: boolean;
  loadingEnergy: boolean;
  loadingCalories: boolean;
  weightRange: WeightRange;
  energyOffset: WeekOffset;
  initialLoaded: boolean;
  setWeightRange: (range: WeightRange) => void;
  setEnergyOffset: (offset: WeekOffset) => void;
  fetchSummary: () => Promise<void>;
  fetchWeightProgress: () => Promise<void>;
  fetchWeeklyEnergy: () => Promise<void>;
}

export const useProgressStore = create<ProgressState>((set, get) => ({
  weightData: null,
  energyData: null,
  calorieData: null,
  loadingWeight: true,
  loadingEnergy: true,
  loadingCalories: true,
  weightRange: 90,
  energyOffset: 0,
  initialLoaded: false,

  setWeightRange: (range) => set({ weightRange: range }),
  setEnergyOffset: (offset) => set({ energyOffset: offset }),

  fetchSummary: async () => {
    try {
      const res = await getProgressSummary();
      set({
        weightData: res.data.weight,
        energyData: res.data.energy,
        calorieData: res.data.calories,
        initialLoaded: true,
      });
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Failed to load progress data";
      toast.error(msg);
      set({ initialLoaded: true });
    } finally {
      set({ loadingWeight: false, loadingEnergy: false, loadingCalories: false });
    }
  },

  fetchWeightProgress: async () => {
    set({ loadingWeight: true });
    try {
      const res = await getWeightProgress(get().weightRange);
      set({ weightData: res.data });
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Failed to load weight data";
      toast.error(msg);
    } finally {
      set({ loadingWeight: false });
    }
  },

  fetchWeeklyEnergy: async () => {
    set({ loadingEnergy: true });
    try {
      const res = await getWeeklyEnergy(get().energyOffset);
      set({ energyData: res.data });
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Failed to load energy data";
      toast.error(msg);
    } finally {
      set({ loadingEnergy: false });
    }
  },
}));
