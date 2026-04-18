import { useEffect, useState } from "react";
import { AuthLayout } from "./components/auth/AuthLayout";
import { LoginForm } from "./components/auth/LoginForm";
import { RegisterForm } from "./components/auth/RegisterForm";
import { OnboardingWizard } from "./components/onboarding/OnboardingWizard";
import CalorieResultScreen from "./screens/CalorieResultScreen";
import HomeScreen from "./screens/Home";
import type { CalorieResult } from "./lib/calorieCalculator";
import "./App.css";

interface User {
  id: number;
  name: string;
  email: string;
}

// Default data for immediate dashboard access if none exists
const DEFAULT_CALORIE_RESULT: CalorieResult = {
  bmr: 1750,
  tdee: 2100,
  daily_calorie_target: 1800,
  calorie_deficit_or_surplus: -300,
  weight_difference_kg: 5,
  estimated_weeks: 10,
  protein_grams: 135,
  carbs_grams: 225,
  fat_grams: 45,
  fiber_target_grams: 25,
  sugar_limit_grams: 45,
  sodium_limit_mg: 2300,
  bmi: 24.2,
  bmi_category: 'Normal',
  current_weight_kg: 70,
  target_weight_kg: 65,
  goal: 'lose',
};

function App() {
  const [, _setUser] = useState<User | null>(null);
  const [authView, setAuthView] = useState<"login" | "register">("login");
  
  // Persistent states
  const [isAuthenticated, setIsAuthenticated] = useState(() => {
    return localStorage.getItem('nutritrack_auth') === 'true';
  });
  const [isOnboarded, setIsOnboarded] = useState(() => {
    return localStorage.getItem('nutritrack_onboarded') === 'true';
  });
  const [calorieResult, setCalorieResult] = useState<CalorieResult | null>(() => {
    const saved = localStorage.getItem('nutritrack_calorie_result');
    return saved ? JSON.parse(saved) : DEFAULT_CALORIE_RESULT;
  });
  const [showCalorieResult, setShowCalorieResult] = useState(false);

  // Sync with localStorage
  useEffect(() => {
    localStorage.setItem('nutritrack_auth', isAuthenticated.toString());
  }, [isAuthenticated]);

  useEffect(() => {
    localStorage.setItem('nutritrack_onboarded', isOnboarded.toString());
  }, [isOnboarded]);

  useEffect(() => {
    if (calorieResult) {
      localStorage.setItem('nutritrack_calorie_result', JSON.stringify(calorieResult));
    }
  }, [calorieResult]);

  // Demo Login Handler
  const handleAuthSuccess = () => {
    setIsAuthenticated(true);
  };

  // --- Screen: Auth ---
  if (!isAuthenticated) {
    return (
      <AuthLayout>
        {authView === "login" ? (
          <LoginForm
            onToggleForm={() => setAuthView("register")}
            onSubmit={handleAuthSuccess}
          />
        ) : (
          <RegisterForm
            onToggleForm={() => setAuthView("login")}
            onSubmit={handleAuthSuccess}
          />
        )}
      </AuthLayout>
    );
  }

  // --- Screen: Onboarding ---
  if (isAuthenticated && !isOnboarded && !showCalorieResult) {
    return (
      <OnboardingWizard
        onComplete={(result: CalorieResult) => {
          setCalorieResult(result);
          setShowCalorieResult(true);
        }}
      />
    );
  }

  // --- Screen: Calorie Result (between onboarding and dashboard) ---
  if (isAuthenticated && showCalorieResult && calorieResult) {
    return (
      <CalorieResultScreen
        result={calorieResult}
        onContinue={() => {
          setShowCalorieResult(false);
          setIsOnboarded(true);
        }}
      />
    );
  }

  const finalResult = calorieResult || DEFAULT_CALORIE_RESULT;
  return <HomeScreen calorieResult={finalResult} />;
}

export default App;
