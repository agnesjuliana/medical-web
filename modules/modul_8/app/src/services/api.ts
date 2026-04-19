/**
 * API Service for Modul 8
 */
import { toast } from "sonner";

export interface Profile {
  user_id: number;
  gender: 'male' | 'female';
  birth_date: string;
  height_cm: number;
  weight_kg: number;
  activity_level: 'beginner' | 'active' | 'athlete';
  goal: 'lose' | 'maintain' | 'gain';
  goal_weight_kg: number | null;
  step_goal: number;
  barriers: string[];
  daily_calorie_target: number;
  daily_protein_g: number;
  daily_carbs_g: number;
  daily_fats_g: number;
  onboarded_at: string;
}

export interface DashboardData {
  date: string;
  targets: {
    calories: number;
    protein_g: number;
    carbs_g: number;
    fats_g: number;
  };
  consumed: {
    calories: number;
    protein_g: number;
    carbs_g: number;
    fats_g: number;
    fiber_g: number;
    water_ml: number;
  };
  remaining: {
    calories: number;
    protein_g: number;
    carbs_g: number;
    fats_g: number;
  };
  recent_meals: Array<{
    id: number | string;
    meal_type: string;
    name: string;
    calories: number;
    protein_g: number;
    carbs_g: number;
    fats_g: number;
    photo_url: string | null;
    source: string;
    created_at: string;
    status?: 'analyzing' | 'analyzed';
    progress?: number;
  }>;
  health_score: number | null;
}

const BASE_URL = '/api.php';

export const getProfile = async (): Promise<{ data: Profile }> => {
  const response = await fetch(`${BASE_URL}?action=get_profile`);
  if (!response.ok) {
    const errorText = await response.text();
    throw new Error(errorText || 'Failed to fetch profile');
  }
  return response.json();
};

export const saveProfile = async (payload: Partial<Profile> & { action?: string }) => {
  const response = await fetch(BASE_URL, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      ...payload,
      action: 'save_profile',
    }),
  });

  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}));
    throw new Error(errorData.message || 'Failed to save profile');
  }

  return response.json();
};

export const getDashboard = async (date?: string): Promise<{ data: DashboardData }> => {
  const url = date ? `${BASE_URL}?action=get_dashboard&date=${date}` : `${BASE_URL}?action=get_dashboard`;
  const response = await fetch(url);
  if (!response.ok) {
    const errorText = await response.text();
    throw new Error(errorText || 'Failed to fetch dashboard');
  }
  return response.json();
};

// ─── Unit Conversion helpers ──────────────────────────────────────────────────

export function parseHeightCm(heightStr: string): number {
  if (!heightStr) return 170;
  if (typeof heightStr === 'number') return heightStr;
  if (heightStr.includes("'")) {
    const [feet, inches] = heightStr.split("'").map((s) => parseInt(s) || 0);
    return Math.round(feet * 30.48 + inches * 2.54);
  }
  return parseInt(heightStr) || 170;
}

export function parseWeightKg(weightStr: string): number {
  if (!weightStr) return 70;
  if (typeof weightStr === 'number') return weightStr;
  if (weightStr.includes("lbs")) {
    return Math.round((parseInt(weightStr) || 154) / 2.20462);
  }
  return parseInt(weightStr) || 70;
}

export { toast };
