import { create } from "zustand";
import { getProfile, saveProfile, type Profile } from "../services/api";

interface ProfileState {
  data: Profile | null;
  isLoading: boolean;
  error: string | null;
  fetchProfile: () => Promise<void>;
  updateProfile: (payload: Partial<Profile>) => Promise<void>;
}

export const useProfileStore = create<ProfileState>((set, get) => ({
  data: null,
  isLoading: false,
  error: null,
  fetchProfile: async () => {
    if (get().isLoading) return;
    set({ isLoading: true, error: null });
    try {
      const res = await getProfile();
      set({ data: res.data, isLoading: false });
    } catch (err: unknown) {
      const msg = err instanceof Error ? err.message : "Failed to load profile";
      set({ error: msg, isLoading: false });
    }
  },
  updateProfile: async (payload) => {
    await saveProfile(payload);
    set((state) => ({
      data: state.data ? { ...state.data, ...payload } : null,
    }));
  },
}));
