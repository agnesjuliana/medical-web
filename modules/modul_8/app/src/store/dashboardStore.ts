import { create } from "zustand";
import { getDashboard, type DashboardData } from "../services/api";

interface DashboardState {
  data: DashboardData | null;
  isLoading: boolean;
  error: string | null;
  selectedDate: Date;
  setSelectedDate: (date: Date) => void;
  fetchDashboard: (dateStr: string) => Promise<void>;
}

export const useDashboardStore = create<DashboardState>((set) => ({
  data: null,
  isLoading: true,
  error: null,
  selectedDate: new Date(),
  setSelectedDate: (date) => set({ selectedDate: date }),
  fetchDashboard: async (dateStr) => {
    set({ isLoading: true, error: null });
    try {
      const res = await getDashboard(dateStr);
      set({ data: res.data, isLoading: false });
    } catch (err: unknown) {
      if (err instanceof Error && err.name === "AbortError") return;
      set({ error: "Failed to load dashboard data", isLoading: false });
    }
  },
}));
