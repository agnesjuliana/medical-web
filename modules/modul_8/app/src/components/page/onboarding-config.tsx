import React from "react";
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
} from "lucide-react";

export type SelectOption = {
  value: string;
  label: string;
  description?: string;
  icon?: React.ReactNode;
};

export type Step =
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

export type FormData = {
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

export type StringKey = {
  [K in keyof FormData]: FormData[K] extends string ? K : never;
}[keyof FormData];

export const STEPS: Step[] = [
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
