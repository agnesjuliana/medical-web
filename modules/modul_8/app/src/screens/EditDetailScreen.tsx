import { useState } from "react";
import ScreenHeader from "@/components/header/ScreenHeader";
import {
  SingleSelectContent,
  BodyPickerContent,
  DatePickerContent,
  RulerPickerContent,
} from "@/components/page/OnboardingPage";
import { STEPS } from "@/components/page/onboarding-config";
import type { FormData, Step } from "@/components/page/onboarding-config";
import { Button } from "@/components/ui";

export type EditFieldType =
  | "Goal Weight"
  | "Current Weight"
  | "Height"
  | "Date of birth"
  | "Gender"
  | "Daily Step Goal";

interface EditDetailScreenProps {
  field: EditFieldType;
  initialData: { form: FormData; stepGoal: number };
  onClose: () => void;
  onSave: (data: { form: FormData; stepGoal: number }) => void;
}

export default function EditDetailScreen({
  field,
  initialData,
  onClose,
  onSave,
}: EditDetailScreenProps) {
  const [form, setForm] = useState<FormData>(initialData.form);
  const [stepGoal, setStepGoal] = useState(initialData.stepGoal);

  function setField<K extends keyof FormData>(key: K, value: FormData[K]) {
    setForm((prev: FormData) => ({ ...prev, [key]: value }));
  }

  function renderContent() {
    switch (field) {
      case "Gender": {
        const step = STEPS.find((s: Step) => s.id === "gender") as Step & {
          type: "single-select";
        };
        return (
          <SingleSelectContent
            options={step.options}
            value={form.gender}
            onChange={(v) => setField("gender", v)}
          />
        );
      }
      case "Current Weight":
      case "Height":
        return (
          <BodyPickerContent form={form} onChange={(k, v) => setField(k, v)} />
        );
      case "Date of birth":
        return (
          <DatePickerContent form={form} onChange={(k, v) => setField(k, v)} />
        );
      case "Goal Weight": {
        const step = STEPS.find((s: Step) => s.id === "desired-weight") as Extract<
          Step,
          { type: "ruler-picker" }
        >;
        return (
          <RulerPickerContent
            form={form}
            step={step}
            onChange={(v) => setField("desiredWeight", v)}
          />
        );
      }
      case "Daily Step Goal": {
        // Create a custom ruler step for steps
        const step: Extract<Step, { type: "ruler-picker" }> = {
          id: "step-goal",
          title: "Daily Step Goal",
          type: "ruler-picker",
          min: 1000,
          max: 30000,
          unit: "steps",
        };
        // Fake form data for desiredWeight to map to stepGoal
        const fakeForm = { ...form, goal: "maintain", desiredWeight: stepGoal };
        return (
          <RulerPickerContent
            form={fakeForm}
            step={step}
            onChange={setStepGoal}
          />
        );
      }
      default:
        return null;
    }
  }

  return (
    <div className="fixed inset-0 z-[70] bg-app-bg flex flex-col animate-in slide-in-from-right-2 fade-in duration-300">
      <ScreenHeader title="Personal details" onBack={onClose} />

      {/* Content */}
      <div className="flex-1 overflow-y-auto p-4 flex flex-col pt-10">
        {renderContent()}

        <div className="mt-auto pt-8 pb-8">
          <Button
            onClick={() => onSave({ form, stepGoal })}
            className="w-full h-14 rounded-full text-lg font-bold bg-black text-white"
          >
            Save Changes
          </Button>
        </div>
      </div>
    </div>
  );
}
