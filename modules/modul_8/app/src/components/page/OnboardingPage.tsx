import React, { useState, useEffect } from "react";
import {
  Mars,
  Venus,
  Dumbbell,
  TrendingDown,
  TrendingUp,
  Minus,
  BarChart2,
  Sandwich,
  HandshakeIcon,
  CalendarDays,
  Apple,
  Check,
  Flame,
  Wheat,
  Beef,
  Droplets,
  Pencil,
} from "lucide-react";
import OnboardingHeader from "../header/OnboardingHeader";
import SelectionCard from "../ui/SelectionCard";
import ScrollPickerColumn from "../ui/ScrollPickerColumn";
import RulerPicker from "../ui/RulerPicker";
import FixedBottomBar from "../ui/FixedBottomBar";
import { Switch } from "../ui/switch";
import { cn } from "@/lib/utils";
import OnboardingResults from "./OnboardingResults";

// ─── Types ────────────────────────────────────────────────────────────────────

type SelectOption = {
  value: string;
  label: string;
  description?: string;
  icon?: React.ReactNode;
};

type Step =
  | {
      id: string;
      title: string;
      subtitle?: string;
      type: "single-select";
      options: SelectOption[];
    }
  | {
      id: string;
      title: string;
      subtitle?: string;
      type: "multi-select";
      options: SelectOption[];
    }
  | { id: string; title: string; subtitle?: string; type: "body-picker" }
  | { id: string; title: string; subtitle?: string; type: "date-picker" }
  | {
      id: string;
      title: string;
      subtitle?: string;
      type: "ruler-picker";
      unit?: string;
      min?: number;
      max?: number;
    }
  | { id: string; title?: string; type: "info" }
  | { id: string; type: "loading" }
  | { id: string; type: "results" }
  | { id: string; title: string; type: "save-progress" };

type FormData = {
  gender: string;
  activity: string;
  height: string;
  weight: string;
  birthMonth: string;
  birthDay: string;
  birthYear: string;
  goal: string;
  desiredWeight: number;
  barriers: string[];
};

// Keys of FormData whose value is string (excludes number and string[])
type StringKey = {
  [K in keyof FormData]: FormData[K] extends string ? K : never;
}[keyof FormData];

// ─── Step Config ──────────────────────────────────────────────────────────────

const STEPS: Step[] = [
  {
    id: "gender",
    title: "Choose your Gender",
    subtitle: "This will be used to calibrate your custom plan.",
    type: "single-select",
    options: [
      { value: "male", label: "Male", icon: <Mars size={20} /> },
      { value: "female", label: "Female", icon: <Venus size={20} /> },
    ],
  },
  {
    id: "activity",
    title: "How many workouts\ndo you do per week?",
    subtitle: "This will be used to calibrate your custom plan.",
    type: "single-select",
    options: [
      {
        value: "beginner",
        label: "0–2",
        description: "Workouts now and then",
        icon: <Dumbbell size={16} className="opacity-40" />,
      },
      {
        value: "active",
        label: "3–5",
        description: "A few workouts per week",
        icon: <Dumbbell size={16} />,
      },
      {
        value: "athlete",
        label: "6+",
        description: "Dedicated athlete",
        icon: <Dumbbell size={16} className="text-yellow-400" />,
      },
    ],
  },
  {
    id: "body",
    title: "Height & weight",
    subtitle: "This will be used to calibrate your custom plan.",
    type: "body-picker",
  },
  {
    id: "birthdate",
    title: "When were you born?",
    subtitle: "This will be used to calibrate your custom plan.",
    type: "date-picker",
  },
  {
    id: "goal",
    title: "What is your goal?",
    subtitle: "This helps us generate a plan for your calorie intake.",
    type: "single-select",
    options: [
      { value: "lose", label: "Lose weight", icon: <TrendingDown size={20} /> },
      { value: "maintain", label: "Maintain", icon: <Minus size={20} /> },
      { value: "gain", label: "Gain weight", icon: <TrendingUp size={20} /> },
    ],
  },
  {
    id: "desired-weight",
    title: "What is your desired weight?",
    type: "ruler-picker",
    unit: "kg",
    min: 30,
    max: 200,
  },
  {
    id: "motivation",
    type: "info",
  },
  { id: "loading", type: "loading" },
  { id: "results", type: "results" },
  { id: "save-progress", title: "Save your progress", type: "save-progress" },
  {
    id: "barriers",
    title: "What's stopping you from\nreaching your goals?",
    type: "multi-select",
    options: [
      {
        value: "consistency",
        label: "Lack of consistency",
        icon: <BarChart2 size={18} />,
      },
      {
        value: "eating",
        label: "Unhealthy eating habits",
        icon: <Sandwich size={18} />,
      },
      {
        value: "support",
        label: "Lack of support",
        icon: <HandshakeIcon size={18} />,
      },
      {
        value: "schedule",
        label: "Busy schedule",
        icon: <CalendarDays size={18} />,
      },
      {
        value: "inspiration",
        label: "Lack of meal inspiration",
        icon: <Apple size={18} />,
      },
    ],
  },
];

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

function SingleSelectContent({
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

function MultiSelectContent({
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

function BodyPickerContent({
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

function DatePickerContent({
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

function RulerPickerContent({
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
  const currentKg = parseInt(form.weight) || 70;
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

// ─── TDEE helpers ─────────────────────────────────────────────────────────────

function computeTDEE(form: FormData) {
  const kg = parseInt(form.weight) || 70;
  const cm = parseInt(form.height) || 170;
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
  const diff = Math.abs(form.desiredWeight - (parseInt(form.weight) || 70));
  const weeks = Math.max(1, Math.round(diff / 0.5));
  const d = new Date();
  d.setDate(d.getDate() + weeks * 7);
  return d.toLocaleDateString("en-US", { month: "long", day: "numeric" });
}

// ─── MacroRing ────────────────────────────────────────────────────────────────

function MacroRing({
  label,
  value,
  unit,
  icon,
  color,
  percent,
}: {
  label: string;
  value: number;
  unit: string;
  icon: React.ReactNode;
  color: string;
  percent: number;
}) {
  const r = 28;
  const circumference = 2 * Math.PI * r;
  const dashOffset =
    circumference - (Math.min(percent, 100) / 100) * circumference;
  return (
    <div className="flex-1 rounded-2xl bg-muted/40 p-3 flex flex-col gap-1">
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-1.5 text-xs font-semibold text-foreground">
          {icon} {label}
        </div>
        <Pencil size={11} className="text-muted-foreground" />
      </div>
      <div className="flex justify-center">
        <div className="relative">
          <svg width="72" height="72" viewBox="0 0 72 72">
            <circle
              cx="36"
              cy="36"
              r={r}
              fill="none"
              stroke="currentColor"
              strokeOpacity={0.15}
              strokeWidth="8"
            />
            <circle
              cx="36"
              cy="36"
              r={r}
              fill="none"
              stroke={color}
              strokeWidth="8"
              strokeDasharray={circumference}
              strokeDashoffset={dashOffset}
              strokeLinecap="round"
              transform="rotate(-90 36 36)"
            />
          </svg>
          <div className="absolute inset-0 flex flex-col items-center justify-center">
            <span className="text-sm font-bold text-foreground leading-none">
              {value}
            </span>
            <span className="text-[10px] text-muted-foreground">{unit}</span>
          </div>
        </div>
      </div>
    </div>
  );
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

// ─── Results content ──────────────────────────────────────────────────────────

function ResultsContent({ form }: { form: FormData }) {
  const { calories, protein, carbs, fats } = computeTDEE(form);
  const diff = Math.abs(form.desiredWeight - (parseInt(form.weight) || 70));
  const action =
    form.goal === "gain" ? "Gain" : form.goal === "lose" ? "Lose" : "Maintain";
  const goalDate = computeGoalDate(form);

  return (
    <div className="flex flex-col gap-5 w-full" style={safeH}>
      <div className="flex flex-col items-center text-center gap-3">
        <div className="size-14 rounded-full bg-black flex items-center justify-center">
          <Check size={26} className="text-white" />
        </div>
        <h2 className="text-2xl font-bold text-foreground leading-snug">
          Congratulations
          <br />
          your custom plan is ready!
        </h2>
        <p className="text-sm text-muted-foreground font-medium">
          You should {action.toLowerCase()}:
        </p>
        <span className="px-5 py-2 rounded-full bg-muted text-sm font-medium text-foreground">
          {action} {diff} kg by {goalDate}
        </span>
      </div>
      <div className="rounded-2xl bg-muted/30 p-4 flex flex-col gap-3">
        <div>
          <p className="font-bold text-foreground">Daily recommendation</p>
          <p className="text-xs text-muted-foreground">
            You can edit this anytime
          </p>
        </div>
        <div className="flex gap-2">
          <MacroRing
            label="Calories"
            value={calories}
            unit="kcal"
            icon={<Flame size={12} />}
            color="#1f2937"
            percent={75}
          />
          <MacroRing
            label="Carbs"
            value={carbs}
            unit="g"
            icon={<Wheat size={12} />}
            color="#f97316"
            percent={60}
          />
        </div>
        <div className="flex gap-2">
          <MacroRing
            label="Protein"
            value={protein}
            unit="g"
            icon={<Beef size={12} />}
            color="#ef4444"
            percent={50}
          />
          <MacroRing
            label="Fats"
            value={fats}
            unit="g"
            icon={<Droplets size={12} />}
            color="#3b82f6"
            percent={40}
          />
        </div>
      </div>
    </div>
  );
}

// ─── Save progress content ────────────────────────────────────────────────────

function SaveProgressContent({ onSkip }: { onSkip: () => void }) {
  return (
    <div className="flex flex-col gap-3 w-full" style={safeH}>
      <button
        type="button"
        className="w-full h-14 rounded-full bg-black text-white text-base font-semibold flex items-center justify-center gap-3"
        onClick={() => console.log("Sign in with Apple")}
      >
        <Apple size={20} />
        Sign in with Apple
      </button>
      <button
        type="button"
        className="w-full h-14 rounded-full border-2 border-foreground bg-transparent text-foreground text-base font-semibold flex items-center justify-center gap-3"
        onClick={() => console.log("Sign in with Google")}
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
          onClick={onSkip}
          className="font-semibold text-foreground underline underline-offset-2"
        >
          Skip
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

export default function OnboardingPage({ onComplete }: { onComplete?: () => void }) {
  const [stepIndex, setStepIndex] = useState(0);
  const [form, setForm] = useState<FormData>(INITIAL_FORM);

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

  function handleContinue() {
    if (stepIndex < totalSteps - 2) {
      setStepIndex((i) => i + 1);
    } else {
      onComplete?.();
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
        return <SaveProgressContent onSkip={handleContinue} />;
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
    const { calories, protein, carbs, fats } = computeTDEE(form);
    const diff = Math.abs(form.desiredWeight - (parseInt(form.weight) || 70));
    const action =
      form.goal === "gain" ? "Gain" : form.goal === "lose" ? "Lose" : "Maintain";
    const goalDate = computeGoalDate(form);

    const plan = {
      goalLbs: parseFloat((diff * 2.20462).toFixed(1)),
      goalDate,
      macros: [
        { label: "Calories", value: calories, unit: "kcal", color: "#1e293b", icon: <Flame size={12} />, percent: 75 },
        { label: "Carbs",    value: carbs,    unit: "g",    color: "#f97316", icon: <Wheat size={12} />, percent: 60 },
        { label: "Protein",  value: protein,  unit: "g",    color: "#ef4444", icon: <Beef size={12} />,  percent: 50 },
        { label: "Fats",     value: fats,     unit: "g",    color: "#3b82f6", icon: <Droplets size={12} />, percent: 40 },
      ],
      healthScore: 7,
      healthScoreMax: 10,
      goalItems: [
        {
          icon: <Check size={20} className="text-rose-600" />,
          iconBg: "#fee2e2",
          title: `${action} ${diff} kg`,
          description: "Stick to your daily calorie target to hit your goal by the deadline.",
        },
        {
          icon: <Check size={20} className="text-emerald-600" />,
          iconBg: "#d1fae5",
          title: "Eat whole, nutrient-dense foods",
          description: "Prioritize vegetables, legumes, and healthy fats to fuel your goals.",
        },
        {
          icon: <Check size={20} className="text-amber-600" />,
          iconBg: "#fef3c7",
          title: "Follow your meal plan",
          description: "Small consistent deficits add up to big results over time.",
        },
        {
          icon: <Check size={20} className="text-violet-600" />,
          iconBg: "#ede9fe",
          title: "Balance your macros",
          description: "Hit your protein, carb, and fat targets to preserve muscle while you transform.",
        },
      ],
      sources: [
        { title: "Mifflin-St Jeor Equation — Accuracy of BMR prediction", url: "https://pubmed.ncbi.nlm.nih.gov/2305711/" },
        { title: "Dietary macronutrient distribution and health outcomes", url: "https://pubmed.ncbi.nlm.nih.gov/26160327/" },
        { title: "Physical activity and weight-loss maintenance", url: "https://pubmed.ncbi.nlm.nih.gov/19927148/" },
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
          disabled={!canContinue()}
          label={stepIndex === totalSteps - 2 ? "Finish" : "Continue"}
        />
      )}
    </div>
  );
}
