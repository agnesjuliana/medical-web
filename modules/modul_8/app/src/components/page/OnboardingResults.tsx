import React from "react";
import { Check, Pencil, Heart, Leaf, UtensilsCrossed, Dna, Flame, Wheat, Beef, Droplets } from "lucide-react";
import FixedBottomBar from "../ui/FixedBottomBar";
import OnboardingHeader from "../header/OnboardingHeader";

// ─── Types ────────────────────────────────────────────────────────────────────

interface MacroData {
  label: string;
  value: number;
  unit: string;
  color: string;
  icon: React.ReactNode;
  percent: number;
}

interface GoalItemData {
  icon: React.ReactNode;
  iconBg: string;
  title: string;
  description: string;
}

interface SourceData {
  title: string;
  url: string;
}

interface PlanData {
  targetWeight: number;
  targetUnit: string;
  goalDate: string;
  macros: MacroData[];
  healthScore: number;
  healthScoreMax: number;
  goalItems: GoalItemData[];
  sources: SourceData[];
}

// ─── MacroCard ────────────────────────────────────────────────────────────────

function MacroCard({ macro }: { macro: MacroData }) {
  const r = 30;
  const circumference = 2 * Math.PI * r;
  const dashOffset =
    circumference - (Math.min(macro.percent, 100) / 100) * circumference;

  return (
    <div className="relative bg-white rounded-2xl p-4 shadow-sm flex flex-col gap-2">
      {/* Title + icon */}
      <div className="flex items-center gap-1.5 text-xs font-semibold text-slate-700">
        <span className="text-slate-500">{macro.icon}</span>
        {macro.label}
      </div>

      {/* Circular progress */}
      <div className="flex justify-center">
        <div className="relative">
          <svg width="76" height="76" viewBox="0 0 76 76">
            {/* Background track */}
            <circle
              cx="38"
              cy="38"
              r={r}
              fill="none"
              stroke="#f1f5f9"
              strokeWidth="8"
            />
            {/* Progress arc */}
            <circle
              cx="38"
              cy="38"
              r={r}
              fill="none"
              stroke={macro.color}
              strokeWidth="8"
              strokeDasharray={circumference}
              strokeDashoffset={dashOffset}
              strokeLinecap="round"
              transform="rotate(-90 38 38)"
              style={{ transition: "stroke-dashoffset 0.8s ease" }}
            />
          </svg>
          <div className="absolute inset-0 flex flex-col items-center justify-center">
            <span className="text-sm font-bold text-slate-900 leading-none">
              {macro.value}
            </span>
            <span className="text-[10px] text-slate-400">{macro.unit}</span>
          </div>
        </div>
      </div>

      {/* Edit icon */}
      <button
        aria-label={`Edit ${macro.label}`}
        className="absolute bottom-3 right-3 w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center hover:bg-slate-200 transition-colors"
      >
        <Pencil size={11} className="text-slate-500" />
      </button>
    </div>
  );
}

// ─── Health Score Card ────────────────────────────────────────────────────────

function HealthScoreCard({
  score,
  max,
}: {
  score: number;
  max: number;
}) {
  const pct = Math.round((score / max) * 100);

  return (
    <div className="bg-white rounded-2xl p-4 shadow-sm flex flex-col gap-3">
      <div className="flex items-center justify-between">
        <div>
          <p className="font-semibold text-slate-900 text-sm">Health score</p>
          <p className="text-xs text-slate-400">Based on your profile</p>
        </div>
        <span className="text-2xl font-bold text-slate-900">
          {score}
          <span className="text-base font-medium text-slate-400">/{max}</span>
        </span>
      </div>
      {/* Linear progress */}
      <div className="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
        <div
          className="h-full rounded-full transition-all duration-700"
          style={{
            width: `${pct}%`,
            background: "linear-gradient(to right, #10b981, #3b82f6)",
          }}
        />
      </div>
    </div>
  );
}

// ─── Goal Item ────────────────────────────────────────────────────────────────

function GoalItem({ item }: { item: GoalItemData }) {
  return (
    <div className="flex items-center gap-4 p-4 bg-white rounded-2xl shadow-sm">
      <div
        className="w-12 h-12 rounded-full flex items-center justify-center shrink-0"
        style={{ background: item.iconBg }}
      >
        {item.icon}
      </div>
      <div className="flex flex-col gap-0.5">
        <p className="text-sm font-semibold text-slate-900">{item.title}</p>
        <p className="text-xs text-slate-500 leading-relaxed">
          {item.description}
        </p>
      </div>
    </div>
  );
}

// ─── Default mock plan data ───────────────────────────────────────────────────

const DEFAULT_PLAN: PlanData = {
  targetWeight: 11.0,
  targetUnit: "lbs",
  goalDate: "28 May",
  macros: [
    {
      label: "Calories",
      value: 1644,
      unit: "kcal",
      color: "#1e293b",
      icon: <Flame size={12} />,
      percent: 75,
    },
    {
      label: "Carbs",
      value: 164,
      unit: "g",
      color: "#f97316",
      icon: <Wheat size={12} />,
      percent: 60,
    },
    {
      label: "Protein",
      value: 123,
      unit: "g",
      color: "#ef4444",
      icon: <Beef size={12} />,
      percent: 50,
    },
    {
      label: "Fats",
      value: 55,
      unit: "g",
      color: "#3b82f6",
      icon: <Droplets size={12} />,
      percent: 40,
    },
  ],
  healthScore: 7,
  healthScoreMax: 10,
  goalItems: [
    {
      icon: <Heart size={20} className="text-rose-600" />,
      iconBg: "#fee2e2",
      title: "Track your heart health",
      description:
        "Monitor your resting heart rate and keep cardio sessions consistent for long-term gains.",
    },
    {
      icon: <Leaf size={20} className="text-emerald-600" />,
      iconBg: "#d1fae5",
      title: "Eat whole, nutrient-dense foods",
      description:
        "Prioritize vegetables, legumes, and healthy fats like avocado to fuel your goals.",
    },
    {
      icon: <UtensilsCrossed size={20} className="text-amber-600" />,
      iconBg: "#fef3c7",
      title: "Follow your meal plan",
      description:
        "Stick to your daily calorie and macro targets — small deficits add up to big results.",
    },
    {
      icon: <Dna size={20} className="text-violet-600" />,
      iconBg: "#ede9fe",
      title: "Balance your macros",
      description:
        "Hit your protein, carb, and fat targets every day to preserve muscle while losing fat.",
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

// ─── Main Component ───────────────────────────────────────────────────────────

export interface OnboardingResultsProps {
  plan?: PlanData;
  step?: number;
  totalSteps?: number;
  onBack?: () => void;
  onContinue?: () => void;
}

export default function OnboardingResults({
  plan = DEFAULT_PLAN,
  step = 9,
  totalSteps = 10,
  onBack,
  onContinue,
}: OnboardingResultsProps) {
  return (
    <main className="min-h-screen flex flex-col bg-slate-50 pb-28">
      {/* ── Header ─────────────────────────────────────────────────────── */}
      <OnboardingHeader
        title=""
        step={step}
        totalSteps={totalSteps}
        onBack={onBack}
      />

      {/* ── Success checkmark + heading ────────────────────────────────── */}
      <section className="flex flex-col items-center text-center gap-4 px-6 pt-6 pb-2">
        {/* Checkmark */}
        <div className="w-16 h-16 rounded-full bg-slate-900 flex items-center justify-center shadow-lg">
          <Check size={28} className="text-white" strokeWidth={2.5} />
        </div>

        <h1 className="text-2xl font-bold text-slate-900 leading-snug max-w-xs">
          Congratulations your custom plan is ready!
        </h1>

        {/* Goal pill */}
        <span className="px-5 py-2 rounded-full bg-white shadow-sm border border-slate-100 text-sm font-semibold text-slate-700">
          {plan.targetWeight} {plan.targetUnit} by {plan.goalDate}
        </span>
      </section>

      {/* ── Daily Recommendation ───────────────────────────────────────── */}
      <section className="px-4 mt-5 flex flex-col gap-3">
        <div>
          <h2 className="font-bold text-slate-900 text-base">
            Daily Recommendation
          </h2>
          <p className="text-xs text-slate-400">You can edit this any time</p>
        </div>

        {/* 2×2 macro grid */}
        <div className="grid grid-cols-2 gap-3">
          {plan.macros.map((m) => (
            <MacroCard key={m.label} macro={m} />
          ))}
        </div>

        {/* Health score full-width card */}
        <HealthScoreCard score={plan.healthScore} max={plan.healthScoreMax} />
      </section>

      {/* ── How to reach your goals ────────────────────────────────────── */}
      <section className="px-4 mt-6 flex flex-col gap-3">
        <h2 className="font-bold text-slate-900 text-base">
          How to reach your goals
        </h2>
        {plan.goalItems.map((item, i) => (
          <GoalItem key={i} item={item} />
        ))}
      </section>

      {/* ── Sources footer ─────────────────────────────────────────────── */}
      <section className="px-4 mt-6">
        <div className="bg-white rounded-2xl p-5 shadow-sm">
          <p className="text-sm font-semibold text-slate-700 mb-3">
            Based on peer-reviewed research
          </p>
          <ul className="flex flex-col gap-2">
            {plan.sources.map((src, i) => (
              <li key={i}>
                <a
                  href={src.url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-xs text-slate-500 underline underline-offset-2 hover:text-slate-800 transition-colors"
                >
                  {src.title}
                </a>
              </li>
            ))}
          </ul>
        </div>
      </section>

      {/* ── Sticky Continue button ─────────────────────────────────────── */}
      <FixedBottomBar
        label="Continue"
        onContinue={onContinue ?? (() => {})}
      />
    </main>
  );
}
