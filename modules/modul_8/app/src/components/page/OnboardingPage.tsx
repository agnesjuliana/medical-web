import { useState, useEffect } from "react";
import { Apple, Check, Flame, Wheat, Beef, Droplets, Loader2 } from "lucide-react";
import { saveProfile, toast } from "../../services/api";
import OnboardingHeader from "../header/OnboardingHeader";
import SelectionCard from "../ui/SelectionCard";
import ScrollPickerColumn from "../ui/ScrollPickerColumn";
import RulerPicker from "../ui/RulerPicker";
import FixedBottomBar from "../ui/FixedBottomBar";
import { Switch } from "../ui/switch";
import { cn } from "@/lib/utils";
import OnboardingResults from "./OnboardingResults";
import type {
  FormData,
  Step,
  StringKey,
  SelectOption,
} from "./onboarding-config";
import { STEPS } from "./onboarding-config";

// ─── Picker data ──────────────────────────────────────────────────────────────

const MONTHS = [
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December",
];
const DAYS = Array.from({ length: 31 }, (_, i) =>
  String(i + 1).padStart(2, "0"),
);
const YEARS = Array.from({ length: 71 }, (_, i) => String(1940 + i));
const CM_ITEMS = Array.from({ length: 151 }, (_, i) => `${100 + i} cm`);
const KG_ITEMS = Array.from({ length: 171 }, (_, i) => `${30 + i} kg`);
const FT_ITEMS = [
  "4'0\"",
  "4'1\"",
  "4'2\"",
  "4'3\"",
  "4'4\"",
  "4'5\"",
  "4'6\"",
  "4'7\"",
  "4'8\"",
  "4'9\"",
  "4'10\"",
  "4'11\"",
  "5'0\"",
  "5'1\"",
  "5'2\"",
  "5'3\"",
  "5'4\"",
  "5'5\"",
  "5'6\"",
  "5'7\"",
  "5'8\"",
  "5'9\"",
  "5'10\"",
  "5'11\"",
  "6'0\"",
  "6'1\"",
  "6'2\"",
  "6'3\"",
  "6'4\"",
  "6'5\"",
  "6'6\"",
  "6'7\"",
  "6'8\"",
  "6'9\"",
  "6'10\"",
  "6'11\"",
  "7'0\"",
];
const LBS_ITEMS = Array.from({ length: 221 }, (_, i) => `${60 + i} lbs`);

// ─── Safe-area padding helper ─────────────────────────────────────────────────

const safeH = {
  paddingLeft: "calc(1rem + env(safe-area-inset-left, 0px))",
  paddingRight: "calc(1rem + env(safe-area-inset-right, 0px))",
};

// ─── Sub-renderers ────────────────────────────────────────────────────────────

export function SingleSelectContent({
  options,
  value,
  onChange,
}: {
  options: SelectOption[];
  value: string;
  onChange: (v: string) => void;
}) {
  return (
    <div className="flex flex-col gap-3" style={safeH}>
      {options.map((opt) => (
        <SelectionCard
          key={opt.value}
          label={opt.label}
          description={opt.description}
          icon={opt.icon}
          selected={value === opt.value}
          onClick={() => onChange(opt.value)}
        />
      ))}
    </div>
  );
}

export function MultiSelectContent({
  options,
  values,
  onToggle,
}: {
  options: SelectOption[];
  values: string[];
  onToggle: (v: string) => void;
}) {
  return (
    <div className="flex flex-col gap-3" style={safeH}>
      {options.map((opt) => (
        <SelectionCard
          key={opt.value}
          label={opt.label}
          description={opt.description}
          icon={opt.icon}
          selected={values.includes(opt.value)}
          onClick={() => onToggle(opt.value)}
        />
      ))}
    </div>
  );
}

export function BodyPickerContent({
  form,
  onChange,
}: {
  form: FormData;
  onChange: (key: StringKey, value: string) => void;
}) {
  const [metric, setMetric] = useState(true);
  const heightItems = metric ? CM_ITEMS : FT_ITEMS;
  const weightItems = metric ? KG_ITEMS : LBS_ITEMS;
  const defaultH = metric ? "170 cm" : "5'7\"";
  const defaultW = metric ? "70 kg" : "154 lbs";

  return (
    <div className="flex flex-col items-center gap-6" style={safeH}>
      <div className="flex items-center gap-3">
        <span
          className={cn(
            "text-sm font-medium",
            !metric ? "text-foreground" : "text-muted-foreground",
          )}
        >
          Imperial
        </span>
        <Switch checked={metric} onCheckedChange={setMetric} />
        <span
          className={cn(
            "text-sm font-semibold",
            metric ? "text-foreground" : "text-muted-foreground",
          )}
        >
          Metric
        </span>
      </div>
      <div className="flex gap-6 w-full justify-center">
        <ScrollPickerColumn
          label="Height"
          items={heightItems}
          value={form.height || defaultH}
          onChange={(v) => onChange("height", v)}
          className="flex-1"
        />
        <ScrollPickerColumn
          label="Weight"
          items={weightItems}
          value={form.weight || defaultW}
          onChange={(v) => onChange("weight", v)}
          className="flex-1"
        />
      </div>
    </div>
  );
}

export function DatePickerContent({
  form,
  onChange,
}: {
  form: FormData;
  onChange: (key: StringKey, value: string) => void;
}) {
  return (
    <div className="flex gap-2 w-full justify-center" style={safeH}>
      <ScrollPickerColumn
        label="Month"
        items={MONTHS}
        value={form.birthMonth || "January"}
        onChange={(v) => onChange("birthMonth", v)}
        className="flex-1"
      />
      <ScrollPickerColumn
        label="Day"
        items={DAYS}
        value={form.birthDay || "01"}
        onChange={(v) => onChange("birthDay", v)}
        className="flex-1"
      />
      <ScrollPickerColumn
        label="Year"
        items={YEARS}
        value={form.birthYear || "2000"}
        onChange={(v) => onChange("birthYear", v)}
        className="flex-1"
      />
    </div>
  );
}

export function RulerPickerContent({
  form,
  step,
  onChange,
}: {
  form: FormData;
  step: Extract<Step, { type: "ruler-picker" }>;
  onChange: (value: number) => void;
}) {
  const goalLabel =
    form.goal === "gain"
      ? "Gain weight"
      : form.goal === "lose"
        ? "Lose weight"
        : "Maintain weight";

  return (
    <div className="flex flex-col items-center gap-6 w-full" style={safeH}>
      <p className="text-sm text-muted-foreground font-medium">{goalLabel}</p>
      <RulerPicker
        min={step.min}
        max={step.max}
        unit={step.unit}
        value={form.desiredWeight || 70}
        onChange={onChange}
      />
    </div>
  );
}

function MotivationContent({ form }: { form: FormData }) {
  const currentKg = parseWeightKg(form.weight);
  const diff = Math.abs(form.desiredWeight - currentKg);

  const action =
    form.goal === "gain"
      ? "Gaining"
      : form.goal === "lose"
        ? "Losing"
        : "Maintaining";

  const headline =
    form.goal === "maintain"
      ? `Maintaining your weight is a smart choice. Consistency is key!`
      : `${action} ${diff} kg is a realistic target. It's not hard at all!`;

  return (
    <div className="flex flex-col items-center justify-center flex-1 px-8 text-center gap-6">
      <h2 className="text-3xl font-bold leading-snug text-foreground">
        {form.goal !== "maintain" ? (
          <>
            {action} <span className="text-orange-400">{diff} kg</span> is a
            realistic target.{"\n"}It's not hard at all!
          </>
        ) : (
          headline
        )}
      </h2>
      <p className="text-sm text-muted-foreground leading-relaxed">
        90% of users say that the change is obvious after using the app and it
        is not easy to rebound.
      </p>
    </div>
  );
}

// ─── Unit Conversion helpers ──────────────────────────────────────────────────

function parseHeightCm(heightStr: string): number {
  if (!heightStr) return 170;
  if (heightStr.includes("'")) {
    const [feet, inches] = heightStr.split("'").map((s) => parseInt(s) || 0);
    return Math.round(feet * 30.48 + inches * 2.54);
  }
  return parseInt(heightStr) || 170;
}

function parseWeightKg(weightStr: string): number {
  if (!weightStr) return 70;
  if (weightStr.includes("lbs")) {
    return Math.round((parseInt(weightStr) || 154) / 2.20462);
  }
  return parseInt(weightStr) || 70;
}

// ─── Health score helpers ─────────────────────────────────────────────────────

function computeHealthScore(form: FormData): number {
  const kg = parseWeightKg(form.weight);
  const cm = parseHeightCm(form.height);
  const bmi = kg / Math.pow(cm / 100, 2);
  const diff = Math.abs(form.desiredWeight - kg);

  const bmiScore =
    bmi >= 18.5 && bmi <= 24.9 ? 3 : bmi >= 17 && bmi <= 29.9 ? 1 : 0;
  const activityScore =
    form.activity === "athlete" ? 3 : form.activity === "active" ? 2 : 1;
  const goalScore = diff <= 5 ? 2 : diff <= 15 ? 1 : 0;

  return Math.min(10, Math.max(1, 2 + bmiScore + activityScore + goalScore));
}

// ─── TDEE helpers ─────────────────────────────────────────────────────────────

function computeTDEE(form: FormData) {
  const kg = parseWeightKg(form.weight);
  const cm = parseHeightCm(form.height);
  const age = new Date().getFullYear() - parseInt(form.birthYear || "2000");
  const bmr =
    form.gender === "male"
      ? 10 * kg + 6.25 * cm - 5 * age + 5
      : 10 * kg + 6.25 * cm - 5 * age - 161;
  const factor =
    (
      { beginner: 1.375, active: 1.55, athlete: 1.725 } as Record<
        string,
        number
      >
    )[form.activity] ?? 1.375;
  const target = Math.max(
    1200,
    Math.round(bmr * factor) +
      (form.goal === "gain" ? 500 : form.goal === "lose" ? -500 : 0),
  );
  return {
    calories: target,
    protein: Math.round((target * 0.3) / 4),
    carbs: Math.round((target * 0.4) / 4),
    fats: Math.round((target * 0.3) / 9),
  };
}

function computeGoalDate(form: FormData): string {
  const diff = Math.abs(form.desiredWeight - parseWeightKg(form.weight));
  const weeks = Math.max(1, Math.round(diff / 0.5));
  const d = new Date();
  d.setDate(d.getDate() + weeks * 7);
  return d.toLocaleDateString("en-US", { month: "long", day: "numeric" });
}

// ─── Loading content ──────────────────────────────────────────────────────────

const LOADING_MSGS = [
  "Customizing health plan...",
  "Calculating your macros...",
  "Setting up daily goals...",
  "Finalizing your profile...",
];

function LoadingContent({ onComplete }: { onComplete: () => void }) {
  const [percent, setPercent] = useState(0);

  useEffect(() => {
    let p = 0;
    const id = setInterval(() => {
      p += 1;
      setPercent(p);
      if (p >= 100) {
        clearInterval(id);
        setTimeout(onComplete, 500);
      }
    }, 50);
    return () => clearInterval(id);
  }, [onComplete]);

  const msgIdx = Math.min(
    Math.floor((percent / 100) * LOADING_MSGS.length),
    LOADING_MSGS.length - 1,
  );

  return (
    <div
      className="flex flex-col items-center justify-center min-h-dvh px-6 gap-8 bg-background"
      style={safeH}
    >
      <div className="flex flex-col items-center gap-3 text-center">
        <p className="text-7xl font-bold text-foreground tabular-nums">
          {percent}%
        </p>
        <h2 className="text-2xl font-bold text-foreground leading-snug">
          We're setting everything
          <br />
          up for you
        </h2>
      </div>
      <div className="w-full flex flex-col gap-2">
        <div className="w-full h-2.5 rounded-full bg-muted overflow-hidden">
          <div
            className="h-full rounded-full transition-all duration-75"
            style={{
              width: `${percent}%`,
              background:
                "linear-gradient(to right, #f472b6, #a855f7, #3b82f6)",
            }}
          />
        </div>
        <p className="text-sm text-muted-foreground text-center">
          {LOADING_MSGS[msgIdx]}
        </p>
      </div>
      <div className="w-full rounded-2xl bg-muted/40 p-5">
        <p className="font-semibold text-foreground mb-3">
          Daily recommendation for
        </p>
        {["Calories", "Carbs", "Protein", "Fats", "Health Score"].map(
          (item) => (
            <p key={item} className="text-sm text-foreground py-0.5">
              · {item}
            </p>
          ),
        )}
      </div>
    </div>
  );
}

// ─── Save progress content ────────────────────────────────────────────────────

function SaveProgressContent({
  onComplete,
  isSaving,
}: {
  onComplete: () => void;
  isSaving: boolean;
}) {
  return (
    <div className="flex flex-col gap-3 w-full" style={safeH}>
      <button
        type="button"
        className="w-full h-14 rounded-full bg-black text-white text-base font-semibold flex items-center justify-center gap-3 disabled:opacity-70"
        onClick={onComplete}
        disabled={isSaving}
      >
        <Apple size={20} />
        Sign in with Apple
      </button>
      <button
        type="button"
        className="w-full h-14 rounded-full border-2 border-foreground bg-transparent text-foreground text-base font-semibold flex items-center justify-center gap-3 disabled:opacity-70"
        onClick={onComplete}
        disabled={isSaving}
      >
        <span
          className="font-bold text-[18px] leading-none"
          style={{ color: "#4285F4" }}
        >
          G
        </span>
        Sign in with Google
      </button>
      <p className="text-center text-sm text-muted-foreground mt-2">
        Would you like to sign in later?{" "}
        <button
          type="button"
          onClick={onComplete}
          disabled={isSaving}
          className="font-semibold text-foreground underline underline-offset-2 disabled:opacity-70"
        >
          {isSaving ? "Saving..." : "Skip"}
        </button>
      </p>
    </div>
  );
}

// ─── Initial Form ─────────────────────────────────────────────────────────────

const INITIAL_FORM: FormData = {
  gender: "",
  activity: "",
  height: "",
  weight: "",
  birthMonth: "",
  birthDay: "",
  birthYear: "",
  goal: "",
  desiredWeight: 70,
  barriers: [],
};

// ─── Main Page ────────────────────────────────────────────────────────────────

export default function OnboardingPage({
  onComplete,
}: {
  onComplete?: () => void;
}) {
  const [stepIndex, setStepIndex] = useState(0);
  const [form, setForm] = useState<FormData>(INITIAL_FORM);
  const [isSaving, setIsSaving] = useState(false);

  const step = STEPS[stepIndex];
  const totalSteps = STEPS.length;

  function setField<K extends keyof FormData>(key: K, value: FormData[K]) {
    setForm((prev) => ({ ...prev, [key]: value }));
  }

  function toggleBarrier(value: string) {
    setForm((prev) => ({
      ...prev,
      barriers: prev.barriers.includes(value)
        ? prev.barriers.filter((b) => b !== value)
        : [...prev.barriers, value],
    }));
  }

  function canContinue(): boolean {
    switch (step.id) {
      case "gender":
        return !!form.gender;
      case "activity":
        return !!form.activity;
      case "body":
        return !!form.height && !!form.weight;
      case "birthdate":
        return !!form.birthMonth && !!form.birthDay && !!form.birthYear;
      case "goal":
        return !!form.goal;
      case "desired-weight":
        return true;
      case "motivation":
        return true;
      case "barriers":
        return form.barriers.length > 0;
      default:
        return true;
    }
  }

  async function handleContinue() {
    if (stepIndex < totalSteps - 1) {
      setStepIndex((i) => i + 1);
    } else {
      setIsSaving(true);
      try {
        const monthIdx = MONTHS.indexOf(form.birthMonth);
        const birth_date = `${form.birthYear}-${String(monthIdx + 1).padStart(2, "0")}-${String(form.birthDay).padStart(2, "0")}`;

        await saveProfile({
          gender: form.gender as "male" | "female",
          birth_date,
          height_cm: parseHeightCm(form.height),
          weight_kg: parseWeightKg(form.weight),
          activity_level: form.activity as any,
          goal: form.goal as any,
          goal_weight_kg: form.desiredWeight,
          step_goal: 10000,
          barriers: form.barriers,
        });

        toast.success("Profile saved!");
        onComplete?.();
      } catch (err: any) {
        console.error(err);
        toast.error(err.message || "Failed to save profile");
      } finally {
        setIsSaving(false);
      }
    }
  }

  function handleBack() {
    if (stepIndex > 0) setStepIndex((i) => i - 1);
  }

  function renderContent() {
    switch (step.type) {
      case "single-select":
        return (
          <SingleSelectContent
            options={step.options}
            value={form[step.id as keyof FormData] as string}
            onChange={(v) => setField(step.id as StringKey, v)}
          />
        );
      case "multi-select":
        return (
          <MultiSelectContent
            options={step.options}
            values={form.barriers}
            onToggle={toggleBarrier}
          />
        );
      case "body-picker":
        return (
          <BodyPickerContent form={form} onChange={(k, v) => setField(k, v)} />
        );
      case "date-picker":
        return (
          <DatePickerContent form={form} onChange={(k, v) => setField(k, v)} />
        );
      case "ruler-picker":
        return (
          <RulerPickerContent
            form={form}
            step={step}
            onChange={(v) => setField("desiredWeight", v)}
          />
        );
      case "info":
        return <MotivationContent form={form} />;
      case "loading":
        return <LoadingContent onComplete={handleContinue} />;
      case "results":
        return null; // handled as early return below
      case "save-progress":
        return <SaveProgressContent onComplete={handleContinue} isSaving={isSaving} />;
      default:
        return null;
    }
  }

  const isLoading = step.type === "loading";
  const isInfo = step.type === "info";
  const isSaveProgress = step.type === "save-progress";
  const isResults = step.type === "results";
  const showHeader = !isLoading;
  const showFooter = !isLoading && !isSaveProgress;

  const headerTitle =
    isInfo || isLoading ? "" : "title" in step ? step.title : "";
  const headerSubtitle =
    isInfo || isLoading || !("subtitle" in step) ? undefined : step.subtitle;

  // ── Results screen: render as full-page standalone component ──
  if (isResults) {
    const isMetric = !form.weight.includes("lbs");
    const { calories, protein, carbs, fats } = computeTDEE(form);
    const weightNow = parseWeightKg(form.weight);
    const diff = Math.abs(form.desiredWeight - weightNow);
    const action =
      form.goal === "gain"
        ? "Gain"
        : form.goal === "lose"
          ? "Lose"
          : "Maintain";
    const goalDate = computeGoalDate(form);

    const targetWeight = isMetric
      ? form.desiredWeight
      : parseFloat((form.desiredWeight * 2.20462).toFixed(1));
    const targetUnit = isMetric ? "kg" : "lbs";

    const diffWeight = isMetric
      ? diff
      : parseFloat((diff * 2.20462).toFixed(1));

    const plan = {
      targetWeight,
      targetUnit,
      goalDate,
      macros: [
        {
          label: "Calories",
          value: calories,
          unit: "kcal",
          color: "#1e293b",
          icon: <Flame size={12} />,
          percent: 100,
        },
        {
          label: "Carbs",
          value: carbs,
          unit: "g",
          color: "#f97316",
          icon: <Wheat size={12} />,
          percent: Math.round(((carbs * 4) / calories) * 100),
        },
        {
          label: "Protein",
          value: protein,
          unit: "g",
          color: "#ef4444",
          icon: <Beef size={12} />,
          percent: Math.round(((protein * 4) / calories) * 100),
        },
        {
          label: "Fats",
          value: fats,
          unit: "g",
          color: "#3b82f6",
          icon: <Droplets size={12} />,
          percent: Math.round(((fats * 9) / calories) * 100),
        },
      ],
      healthScore: computeHealthScore(form),
      healthScoreMax: 10,
      goalItems: [
        {
          icon: <Check size={20} className="text-rose-600" />,
          iconBg: "#fee2e2",
          title: `${action} ${diffWeight} ${targetUnit}`,
          description:
            "Stick to your daily calorie target to hit your goal by the deadline.",
        },
        {
          icon: <Check size={20} className="text-emerald-600" />,
          iconBg: "#d1fae5",
          title: "Eat whole, nutrient-dense foods",
          description:
            "Prioritize vegetables, legumes, and healthy fats to fuel your goals.",
        },
        {
          icon: <Check size={20} className="text-amber-600" />,
          iconBg: "#fef3c7",
          title: "Follow your meal plan",
          description:
            "Small consistent deficits add up to big results over time.",
        },
        {
          icon: <Check size={20} className="text-violet-600" />,
          iconBg: "#ede9fe",
          title: "Balance your macros",
          description:
            "Hit your protein, carb, and fat targets to preserve muscle while you transform.",
        },
      ],
      sources: [
        {
          title: "Mifflin-St Jeor Equation — Accuracy of BMR prediction",
          url: "https://pubmed.ncbi.nlm.nih.gov/2305711/",
        },
        {
          title: "Dietary macronutrient distribution and health outcomes",
          url: "https://pubmed.ncbi.nlm.nih.gov/26160327/",
        },
        {
          title: "Physical activity and weight-loss maintenance",
          url: "https://pubmed.ncbi.nlm.nih.gov/19927148/",
        },
      ],
    };

    return (
      <OnboardingResults
        plan={plan}
        step={stepIndex + 1}
        totalSteps={totalSteps}
        onBack={handleBack}
        onContinue={handleContinue}
      />
    );
  }

  return (
    <div className="h-dvh bg-background flex flex-col overflow-hidden">
      {showHeader && (
        <OnboardingHeader
          title={headerTitle}
          subtitle={headerSubtitle}
          step={stepIndex + 1}
          totalSteps={totalSteps}
          onBack={stepIndex > 0 ? handleBack : undefined}
        />
      )}

      <main className="flex-1 min-h-0 overflow-y-auto no-scrollbar flex flex-col justify-center w-full">
        {renderContent()}
      </main>

      {showFooter && (
        <FixedBottomBar
          onContinue={handleContinue}
          disabled={!canContinue() || isSaving}
          label={
            isSaving ? (
              <div className="flex items-center gap-2">
                <Loader2 className="animate-spin" size={18} />
                Saving...
              </div>
            ) : stepIndex === totalSteps - 1 ? (
              "Finish"
            ) : (
              "Continue"
            )
          }
        />
      )}
    </div>
  );
}
