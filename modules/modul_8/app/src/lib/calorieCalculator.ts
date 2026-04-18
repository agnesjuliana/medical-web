// =============================================================================
// Calorie Calculator — Pure functions for BMR, TDEE, Target Calories & Macros
// Uses Mifflin-St Jeor equation (same as backend PHP)
// =============================================================================

export interface CalorieInput {
  gender: 'male' | 'female';
  weight_kg: number;
  height_cm: number;
  age: number;
  activity_level: string; // '1.2' | '1.375' | '1.55' | '1.725'
  goal: 'lose' | 'maintain' | 'gain';
  target_weight_kg: number;
}

export interface CalorieResult {
  bmr: number;
  tdee: number;
  daily_calorie_target: number;
  calorie_deficit_or_surplus: number;
  weight_difference_kg: number;
  estimated_weeks: number;
  protein_grams: number;
  carbs_grams: number;
  fat_grams: number;
  fiber_target_grams: number;
  sugar_limit_grams: number;
  sodium_limit_mg: number;
  bmi: number;
  bmi_category: string;
  current_weight_kg: number;
  target_weight_kg: number;
  goal: 'lose' | 'maintain' | 'gain';
}

/**
 * Calculate age from birth date components
 */
export function calculateAge(day: string, month: string, year: string): number {
  const birthDate = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
  const today = new Date();
  let age = today.getFullYear() - birthDate.getFullYear();
  const monthDiff = today.getMonth() - birthDate.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }
  return age;
}

/**
 * Get BMI category label in Bahasa Indonesia
 */
function getBmiCategory(bmi: number): string {
  if (bmi < 18.5) return 'Kekurangan BB';
  if (bmi < 25) return 'Normal';
  if (bmi < 30) return 'Kelebihan BB';
  return 'Obesitas';
}

/**
 * Main calculation function
 * Mirrors the PHP backend logic at api/onboarding.php
 */
export function calculateCalories(input: CalorieInput): CalorieResult {
  // Step 1: BMR (Mifflin-St Jeor) — matches backend PHP exactly
  let bmr = (10 * input.weight_kg) + (6.25 * input.height_cm) - (5 * input.age);
  if (input.gender === 'male') {
    bmr += 5;
  } else {
    bmr -= 161;
  }

  // Step 2: TDEE = BMR × activity modifier
  const activity_modifier = parseFloat(input.activity_level) || 1.2;
  const tdee = bmr * activity_modifier;

  // Step 3: Apply goal modifier (±500 kcal)
  let daily_calorie_target = tdee;
  let calorie_deficit_or_surplus = 0;

  if (input.goal === 'lose') {
    calorie_deficit_or_surplus = -500;
    daily_calorie_target = tdee - 500;
  } else if (input.goal === 'gain') {
    calorie_deficit_or_surplus = 500;
    daily_calorie_target = tdee + 500;
  }

  // Ensure minimum safe intake
  daily_calorie_target = Math.max(daily_calorie_target, 1200);

  // Step 4: Timeline estimation
  const weight_diff = Math.abs(input.weight_kg - input.target_weight_kg);
  const rate_per_week = input.goal === 'lose' ? 0.5 : 0.25;
  const estimated_weeks = input.goal === 'maintain'
    ? 0
    : Math.ceil(weight_diff / rate_per_week);

  // Step 5: Macro split — 40% carbs, 30% protein, 30% fat
  const protein_grams = Math.round((daily_calorie_target * 0.30) / 4);
  const carbs_grams = Math.round((daily_calorie_target * 0.40) / 4);
  const fat_grams = Math.round((daily_calorie_target * 0.30) / 9);

  // Step 6: Micronutrient guidelines (WHO & AHA)
  // Fiber: 14g per 1000 kcal, minimum 25g
  const fiber_target_grams = Math.max(25, Math.round((daily_calorie_target / 1000) * 14));
  // Sugar: < 10% of total daily energy intake. 1g sugar = 4 kcal.
  const sugar_limit_grams = Math.round((daily_calorie_target * 0.10) / 4);
  // Sodium: AHA recommends max 2300mg
  const sodium_limit_mg = 2300;

  // Step 7: BMI
  const height_m = input.height_cm / 100;
  const bmi = input.weight_kg / (height_m * height_m);

  return {
    bmr: Math.round(bmr),
    tdee: Math.round(tdee),
    daily_calorie_target: Math.round(daily_calorie_target),
    calorie_deficit_or_surplus,
    weight_difference_kg: Math.round(weight_diff * 10) / 10,
    estimated_weeks,
    protein_grams,
    carbs_grams,
    fat_grams,
    fiber_target_grams,
    sugar_limit_grams,
    sodium_limit_mg,
    bmi: Math.round(bmi * 10) / 10,
    bmi_category: getBmiCategory(bmi),
    current_weight_kg: input.weight_kg,
    target_weight_kg: input.target_weight_kg,
    goal: input.goal,
  };
}

/**
 * Validated Nutritional Quality Index (Health Score)
 * Score range: 0 - 100
 * Base: 50. Bonus for protein/fiber, penalties for sugar/sodium.
 */
export function calculateHealthScore(
  totalProtein: number,
  totalFiber: number,
  totalSugar: number,
  totalSodium: number,
  targets: CalorieResult
): number {
  if (totalProtein === 0 && totalFiber === 0 && totalSugar === 0 && totalSodium === 0) {
     return 0; // Or indicate N/A because no foods eaten
  }

  let score = 50; // Base index

  // Reward: Protein completeness (up to 15 pts)
  const proteinRatio = Math.min(totalProtein / targets.protein_grams, 1);
  score += proteinRatio * 15;

  // Reward: Fiber completeness (up to 20 pts)
  const fiberRatio = Math.min(totalFiber / targets.fiber_target_grams, 1);
  score += fiberRatio * 20;

  // Penalty: Excess Sugar (deduct up to 20 pts if double the limit)
  if (totalSugar > targets.sugar_limit_grams) {
    const sugarExcessRatio = (totalSugar - targets.sugar_limit_grams) / targets.sugar_limit_grams;
    score -= Math.min(sugarExcessRatio * 20, 20);
  }

  // Penalty: Excess Sodium (deduct up to 15 pts if double limit)
  if (totalSodium > targets.sodium_limit_mg) {
    const sodiumExcessRatio = (totalSodium - targets.sodium_limit_mg) / targets.sodium_limit_mg;
    score -= Math.min(sodiumExcessRatio * 15, 15);
  }

  // Bonus for keeping sodium & sugar low/moderate?
  if (totalSugar > 0 && totalSugar <= targets.sugar_limit_grams) score += 5;
  if (totalSodium > 0 && totalSodium <= targets.sodium_limit_mg) score += 5;

  return Math.min(Math.max(Math.round(score), 0), 100);
}
