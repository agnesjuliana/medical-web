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
    sugar_g?: number;
    sodium_mg?: number;
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

const BASE_URL = '/modules/modul_8/Backend/index.php';

export const getProfile = async (): Promise<{ data: Profile | null }> => {
  const response = await fetch(`${BASE_URL}?action=get_profile`);
  if (response.status === 404) return { data: null };
  if (!response.ok) {
    const errorText = await response.text();
    throw new Error(errorText || 'Failed to fetch profile');
  }
  return response.json();
};

export const saveProfile = async (payload: Partial<Profile>) => {
  const response = await fetch(`${BASE_URL}?action=save_profile`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  });

  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}));
    throw new Error(errorData.error || errorData.message || 'Failed to save profile');
  }

  return response.json();
};

export const getDashboard = async (date?: string, signal?: AbortSignal): Promise<{ data: DashboardData }> => {
  const url = date ? `${BASE_URL}?action=get_dashboard&date=${date}` : `${BASE_URL}?action=get_dashboard`;
  const response = await fetch(url, { signal });
  if (!response.ok) {
    const errorText = await response.text();
    throw new Error(errorText || 'Failed to fetch dashboard');
  }
  return response.json();
};

export const getSavedFoods = async (): Promise<{ data: any[] }> => {
  const response = await fetch(`${BASE_URL}?action=list_saved_foods`);
  if (!response.ok) throw new Error('Failed to fetch saved foods');
  return response.json();
};

export const saveFood = async (payload: {
  name: string;
  calories: number;
  protein_g?: number;
  carbs_g?: number;
  fats_g?: number;
  source?: 'manual' | 'database' | 'barcode';
}): Promise<{ data: { id: number } }> => {
  const response = await fetch(`${BASE_URL}?action=save_food`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ source: 'manual', ...payload }),
  });
  if (!response.ok) {
    const err = await response.json().catch(() => ({}));
    throw new Error(err.error || 'Failed to save food');
  }
  return response.json();
};

export const getAiQuota = async (): Promise<{ data: { quota_used: number; limit: number; remaining: number } }> => {
  const response = await fetch(`${BASE_URL}?action=get_ai_quota`);
  if (!response.ok) throw new Error('Failed to fetch AI quota');
  return response.json();
};

export const getUserInfo = async (): Promise<{ data: { id: number; name: string; email: string; initials: string } }> => {
  const response = await fetch(`${BASE_URL}?action=get_user_info`);
  if (!response.ok) throw new Error('Failed to fetch user info');
  return response.json();
};

export const logout = async (): Promise<{ data: { message: string } }> => {
  const response = await fetch(`${BASE_URL}?action=logout`, { method: 'POST' });
  if (!response.ok) throw new Error('Failed to logout');
  return response.json();
};

export const deleteAccount = async (): Promise<{ data: { message: string } }> => {
  const response = await fetch(`${BASE_URL}?action=delete_account`, { method: 'POST' });
  if (!response.ok) throw new Error('Failed to delete account');
  return response.json();
};

async function normalizeImageToJpeg(dataUrl: string, maxDim = 1024, quality = 0.85): Promise<string> {
  const img = await new Promise<HTMLImageElement>((resolve, reject) => {
    const el = new Image();
    el.onload = () => resolve(el);
    el.onerror = () => reject(new Error('Failed to decode image'));
    el.src = dataUrl;
  });
  const scale = Math.min(1, maxDim / Math.max(img.width, img.height));
  const w = Math.max(1, Math.round(img.width * scale));
  const h = Math.max(1, Math.round(img.height * scale));
  const canvas = document.createElement('canvas');
  canvas.width = w;
  canvas.height = h;
  const ctx = canvas.getContext('2d');
  if (!ctx) throw new Error('Canvas unsupported');
  ctx.drawImage(img, 0, 0, w, h);
  let out = canvas.toDataURL('image/jpeg', quality);
  const maxBytes = 2 * 1024 * 1024 * 1.37;
  for (let q = quality - 0.15; out.length > maxBytes && q >= 0.4; q -= 0.15) {
    out = canvas.toDataURL('image/jpeg', q);
  }
  return out;
}

export const scanFood = async (image_b64: string): Promise<{ data: any }> => {
  const normalized = await normalizeImageToJpeg(image_b64);
  const response = await fetch(`${BASE_URL}?action=ai_scan_food`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ image_b64: normalized }),
  });
  if (!response.ok) {
    const err = await response.json().catch(() => ({}));
    throw new Error(err.error || 'Failed to analyze food');
  }
  return response.json();
};

export const logMeal = async (payload: {
  meal_type: string;
  name: string;
  calories: number;
  protein_g: number;
  carbs_g: number;
  fats_g: number;
  photo_url?: string;
  source?: string;
  ai_confidence?: number;
}): Promise<{ data: any }> => {
  const response = await fetch(`${BASE_URL}?action=log_meal`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });
  if (!response.ok) {
    const err = await response.json().catch(() => ({}));
    throw new Error(err.error || 'Failed to log meal');
  }
  return response.json();
};

export const getWeightProgress = async (range: 90 | 180 | 365 | 'all' = 90): Promise<{
  data: {
    current_weight: number;
    start_weight: number;
    goal_weight: number | null;
    goal_progress: number;
    height_cm: number;
    bmi: number;
    logs: Array<{ day: string; date: string; weight: number }>;
    deltas: { '3d': number; '7d': number; '30d': number };
  };
}> => {
  const rangeParam = range === 'all' ? 3650 : range;
  const response = await fetch(`${BASE_URL}?action=get_weight_progress&range=${rangeParam}`);
  if (!response.ok) throw new Error('Failed to fetch weight progress');
  return response.json();
};

export const getWeeklyEnergy = async (offset = 0): Promise<{
  data: {
    week_start: string;
    week_end: string;
    days: Array<{ day: string; date: string; consumed_cal: number }>;
    total_consumed: number;
  };
}> => {
  const response = await fetch(`${BASE_URL}?action=get_weekly_energy&offset=${offset}`);
  if (!response.ok) throw new Error('Failed to fetch weekly energy');
  return response.json();
};

export const getCalorieAverages = async (): Promise<{
  data: {
    avg_7d: number | null;
    avg_30d: number | null;
    logs_7d: Array<{ log_date: string; calories: number }>;
  };
}> => {
  const response = await fetch(`${BASE_URL}?action=get_calorie_averages`);
  if (!response.ok) throw new Error('Failed to fetch calorie averages');
  return response.json();
};

export interface ProgressSummary {
  weight: {
    current_weight: number;
    start_weight: number;
    goal_weight: number | null;
    goal_progress: number;
    height_cm: number;
    bmi: number;
    logs: Array<{ day: string; date: string; weight: number }>;
    deltas: { '3d': number; '7d': number; '30d': number };
  };
  energy: {
    week_start: string;
    week_end: string;
    days: Array<{ day: string; date: string; consumed_cal: number }>;
    total_consumed: number;
  };
  calories: {
    avg_7d: number | null;
    avg_30d: number | null;
    logs_7d: Array<{ log_date: string; calories: number }>;
  };
}

export const getProgressSummary = async (signal?: AbortSignal): Promise<{ data: ProgressSummary }> => {
  const response = await fetch(`${BASE_URL}?action=get_progress_summary`, { signal });
  if (!response.ok) throw new Error('Failed to fetch progress summary');
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
