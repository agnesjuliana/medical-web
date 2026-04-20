import { Pencil } from "lucide-react";
import ScreenHeader from "@/components/header/ScreenHeader";
import { useEffect, useState } from "react";
import { getProfile } from "../services/api";
import EditDetailScreen from "./EditDetailScreen";
import type { EditFieldType } from "./EditDetailScreen";
import type { FormData } from "@/components/page/onboarding-config";

interface AccountDetailsScreenProps {
  onClose: () => void;
  initialData?: FormData;
}

export default function AccountDetailsScreen({
  onClose,
  initialData,
}: AccountDetailsScreenProps) {
  const [editField, setEditField] = useState<EditFieldType | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [form, setForm] = useState<FormData>(
    initialData || {
      gender: "male",
      activity: "active",
      height: "170 cm",
      weight: "70 kg",
      birthMonth: "February",
      birthDay: "02",
      birthYear: "2001",
      goal: "lose",
      desiredWeight: 107,
      barriers: [],
    },
  );
  const [stepGoal, setStepGoal] = useState(10000);

  useEffect(() => {
    if (initialData) {
      setIsLoading(false);
      return;
    }
    setIsLoading(true);
    setError(null);
    getProfile()
      .then((res) => {
        const p = res.data;
        const [year, month, day] = p.birth_date.split("-");
        const monthName = [
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
        ][parseInt(month) - 1];

        setForm({
          gender: p.gender,
          activity: p.activity_level,
          height: `${p.height_cm} cm`,
          weight: `${p.weight_kg} kg`,
          birthMonth: monthName,
          birthDay: day,
          birthYear: year,
          goal: p.goal,
          desiredWeight: p.goal_weight_kg || 70,
          barriers: p.barriers,
        });
        setStepGoal(p.step_goal);
      })
      .catch((err) => {
        console.error(err);
        setError("Failed to load profile data");
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, []);

  const DETAILS = [
    { label: "Current Weight", value: form.weight },
    { label: "Height", value: form.height },
    {
      label: "Date of birth",
      value: `${form.birthMonth} ${form.birthDay}, ${form.birthYear}`,
    },
    {
      label: "Gender",
      value: form.gender.charAt(0).toUpperCase() + form.gender.slice(1),
    },
  ];

  return (
    <div className="fixed inset-0 z-[60] bg-app-bg flex flex-col animate-in slide-in-from-bottom-2 fade-in duration-300">
      <ScreenHeader title="Personal details" onBack={onClose} />

      {/* Content */}
      <div className="flex-1 overflow-y-auto p-4 space-y-6 pb-24">
        {isLoading ? (
          <div className="py-24 flex justify-center">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          </div>
        ) : error ? (
          <div className="bg-destructive/10 text-destructive rounded-2xl p-6 text-center text-sm">
            {error}
          </div>
        ) : (
          <>
            {/* Goal Weight Card */}
            <div className="bg-white dark:bg-slate-800 rounded-[1.5rem] p-5 shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between">
              <div className="flex flex-col gap-1">
                <span className="text-sm font-medium text-muted-foreground">
                  Goal Weight
                </span>
                <span className="text-xl font-extrabold text-foreground">
                  {form.desiredWeight} kg
                </span>
              </div>
              <button
                onClick={() => setEditField("Goal Weight")}
                className="bg-slate-900 dark:bg-slate-700 text-white px-4 py-2.5 rounded-full text-xs font-semibold hover:bg-slate-800 transition-colors"
              >
                Change Goal
              </button>
            </div>

            {/* Personal Details List Card */}
            <div className="bg-white dark:bg-slate-800 rounded-[1.5rem] shadow-sm border border-slate-100 dark:border-slate-700 px-5 py-2">
              {DETAILS.map((item, index) => (
                <div key={item.label}>
                  <div className="w-full flex items-center justify-between py-4 bg-transparent">
                    <span className="text-[15px] text-muted-foreground">
                      {item.label}
                    </span>
                    <div className="flex items-center gap-3">
                      <span className="text-[15px] font-bold text-foreground">
                        {item.value}
                      </span>
                      <button
                        onClick={() => setEditField(item.label as EditFieldType)}
                        className="hover:opacity-80 transition-opacity"
                      >
                        <Pencil size={16} className="text-muted-foreground" />
                      </button>
                    </div>
                  </div>
                  {index < DETAILS.length - 1 && (
                    <div className="h-[1px] bg-border/40" />
                  )}
                </div>
              ))}
            </div>
          </>
        )}
      </div>

      {editField && (
        <EditDetailScreen
          field={editField}
          initialData={{ form, stepGoal }}
          onSave={(data) => {
            setForm(data.form);
            setStepGoal(data.stepGoal);
            setEditField(null);
          }}
          onClose={() => setEditField(null)}
        />
      )}
    </div>
  );
}
