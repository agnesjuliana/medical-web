import { useState } from 'react';
import { StepGender } from './StepGender';
import { StepActivity } from './StepActivity';
import { StepMetrics } from './StepMetrics';
import { StepBirthDate } from './StepBirthDate';
import { StepGoal } from './StepGoal';
import { StepTargetWeight } from './StepTargetWeight';
import { ChevronLeft, ArrowRight, Loader2, Sparkles } from 'lucide-react';
import { calculateCalories, calculateAge } from '../../lib/calorieCalculator';
import type { CalorieResult } from '../../lib/calorieCalculator';

const TOTAL_STEPS = 6;

interface OnboardingWizardProps {
  onComplete: (result: CalorieResult) => void;
}

export function OnboardingWizard({ onComplete }: OnboardingWizardProps) {
  const [step, setStep] = useState(1);
  const [isSubmitting, setIsSubmitting] = useState(false);
  
  const [formData, setFormData] = useState({
    gender: '',
    activity_level: '',
    height_cm: '',
    weight_kg: '',
    birth_day: '',
    birth_month: '',
    birth_year: '',
    goal: '',
    target_weight_kg: '',
  });

  const updateField = (field: string, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const handleNext = async () => {
    if (step < TOTAL_STEPS) {
      setStep(prev => prev + 1);
    } else {
      // Final step — calculate and submit
      setIsSubmitting(true);

      try {
        // Calculate age
        const age = calculateAge(formData.birth_day, formData.birth_month, formData.birth_year);

        // Determine target weight
        const currentWeight = parseFloat(formData.weight_kg);
        const targetWeight = formData.goal === 'maintain'
          ? currentWeight
          : parseFloat(formData.target_weight_kg) || currentWeight;

        // Run calculation
        const result = calculateCalories({
          gender: formData.gender as 'male' | 'female',
          weight_kg: currentWeight,
          height_cm: parseFloat(formData.height_cm),
          age,
          activity_level: formData.activity_level,
          goal: formData.goal as 'lose' | 'maintain' | 'gain',
          target_weight_kg: targetWeight,
        });

        // Also send to backend (fire and forget, don't block UI)
        const birth_date = `${formData.birth_year}-${formData.birth_month}-${formData.birth_day}`;
        fetch('/api/onboarding.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            gender: formData.gender,
            activity_level: formData.activity_level,
            height_cm: parseFloat(formData.height_cm),
            weight_kg: currentWeight,
            birth_date,
            goal: formData.goal,
            target_weight_kg: targetWeight,
          })
        }).catch(e => console.warn('Backend sync skipped:', e));

        // Small delay for loading feel, then send result to parent
        setTimeout(() => {
          onComplete(result);
        }, 600);

      } catch (e) {
        console.error('Calculation error', e);
        setIsSubmitting(false);
      }
    }
  };

  const handleBack = () => {
    if (step > 1) {
      setStep(prev => prev - 1);
    }
  };

  const isStepValid = () => {
    if (step === 1) return formData.gender !== '';
    if (step === 2) return formData.activity_level !== '';
    if (step === 3) return formData.height_cm !== '' && formData.weight_kg !== '';
    if (step === 4) return formData.birth_day !== '' && formData.birth_month !== '' && formData.birth_year !== '';
    if (step === 5) return formData.goal !== '';
    if (step === 6) {
      // Maintain goal doesn't need target weight
      if (formData.goal === 'maintain') return true;
      if (formData.target_weight_kg === '') return false;
      // Validate direction
      const current = parseFloat(formData.weight_kg);
      const target = parseFloat(formData.target_weight_kg);
      if (formData.goal === 'lose') return target < current;
      if (formData.goal === 'gain') return target > current;
      return false;
    }
    return false;
  };

  return (
    <div className="min-h-screen bg-base text-text-main flex flex-col relative overflow-hidden">
      {/* Ambient Glows */}
      <div className="absolute top-[-20%] left-[-10%] w-[60%] h-[60%] bg-primary/20 rounded-full blur-[120px] pointer-events-none animate-pulse duration-[8000ms]" />
      <div className="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-blue-500/10 rounded-full blur-[100px] pointer-events-none" />
      
      {/* Header / Nav */}
      <div className="relative z-10 p-6 flex items-center justify-between max-w-4xl w-full mx-auto">
        {step > 1 ? (
          <button 
            onClick={handleBack}
            className="w-10 h-10 rounded-full bg-surface shadow-sm border border-black/5 dark:border-white/10 flex items-center justify-center hover:bg-surface-hover transition-all"
          >
            <ChevronLeft className="w-5 h-5 text-text-muted" />
          </button>
        ) : (
          <div className="w-10 h-10" />
        )}
        
        {/* Progress Pill Bar */}
        <div className="flex items-center gap-2 bg-surface shadow-sm px-4 py-2 rounded-full border border-black/5 dark:border-white/10">
          {Array.from({ length: TOTAL_STEPS }, (_, i) => i + 1).map(i => (
            <div 
              key={i} 
              className={`h-1.5 rounded-full transition-all duration-500 ${
                i === step ? 'w-8 bg-primary shadow-sm' : 
                i < step ? 'w-4 bg-primary/40' : 'w-2 bg-text-muted/30'
              }`} 
            />
          ))}
        </div>
        
        <div className="w-10 h-10 text-xs font-bold text-text-muted bg-surface shadow-sm rounded-full flex items-center justify-center border border-black/5 dark:border-white/10">
          {step}/{TOTAL_STEPS}
        </div>
      </div>

      {/* Main Content Area (Scrollable) */}
      <div className="relative z-10 flex-1 flex flex-col items-center justify-start p-6 pt-12 w-full max-w-4xl mx-auto overflow-y-auto pb-40">
        {step === 1 && (
          <StepGender value={formData.gender} onChange={v => updateField('gender', v)} />
        )}
        {step === 2 && (
          <StepActivity value={formData.activity_level} onChange={v => updateField('activity_level', v)} />
        )}
        {step === 3 && (
          <StepMetrics 
            height_cm={formData.height_cm} 
            weight_kg={formData.weight_kg} 
            onChange={updateField} 
          />
        )}
        {step === 4 && (
          <StepBirthDate 
            day={formData.birth_day} 
            month={formData.birth_month} 
            year={formData.birth_year} 
            onChange={updateField} 
          />
        )}
        {step === 5 && (
          <StepGoal value={formData.goal} onChange={v => updateField('goal', v)} />
        )}
        {step === 6 && (
          <StepTargetWeight
            target_weight_kg={formData.target_weight_kg}
            current_weight_kg={formData.weight_kg}
            goal={formData.goal}
            onChange={updateField}
          />
        )}
      </div>

      {/* Sticky Native Footer */}
      <div className="fixed bottom-0 left-0 right-0 z-30 p-6 bg-base/80 backdrop-blur-xl border-t border-black/5 dark:border-white/10 pb-safe">
        <div className="max-w-sm mx-auto space-y-4">
          <button
            onClick={handleNext}
            disabled={!isStepValid() || isSubmitting}
            className={`w-full py-4 rounded-[20px] font-bold flex items-center justify-center gap-2 transition-all active:scale-[0.98] ${
              isStepValid() 
                ? 'bg-primary text-white shadow-lg shadow-primary/20' 
                : 'bg-surface text-text-muted cursor-not-allowed border border-black/5 dark:border-white/10'
            }`}
          >
            {isSubmitting ? (
              <Loader2 className="w-5 h-5 animate-spin" />
            ) : (
              <>
                <span className="text-lg">
                  {step === TOTAL_STEPS ? 'Lihat Hasil' : 'Lanjutkan'}
                </span>
                {step < TOTAL_STEPS
                  ? <ArrowRight className="w-5 h-5 opacity-80" />
                  : <Sparkles className="w-5 h-5 opacity-80" />
                }
              </>
            )}
          </button>
        </div>
      </div>
    </div>
  );
}
