import { useEffect, useRef, useState, useCallback } from "react";
import Header from "@/components/header/Header";
import {
  Plus,
  Camera,
  Info,
  Flag,
  LineChart as LineChartIcon,
  Loader2,
} from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";
import { Button } from "@/components/ui/button";
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  BarChart,
  Bar,
} from "recharts";
import ChangeRow from "@/components/page/ChangeRow";
import {
  getWeightProgress,
  getWeeklyEnergy,
  getCalorieAverages,
  getProgressSummary,
  toast,
} from "@/services/api";

// ─── Types ────────────────────────────────────────────────────────────────────
type WeightRange = 90 | 180 | 365 | "all";
type WeekOffset = 0 | 1 | 2 | 3;

interface WeightProgress {
  current_weight: number;
  start_weight: number;
  goal_weight: number | null;
  goal_progress: number;
  height_cm: number;
  bmi: number;
  logs: Array<{ day: string; date: string; weight: number }>;
  deltas: { "3d": number; "7d": number; "30d": number };
}

interface WeeklyEnergy {
  week_start: string;
  week_end: string;
  days: Array<{ day: string; date: string; consumed_cal: number }>;
  total_consumed: number;
}

interface CalorieAverages {
  avg_7d: number | null;
  avg_30d: number | null;
  logs_7d: Array<{ log_date: string; calories: number }>;
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function bmiCategory(bmi: number): {
  label: string;
  color: string;
  badgeClass: string;
} {
  if (bmi < 18.5)
    return {
      label: "Underweight",
      color: "text-blue-500",
      badgeClass: "bg-blue-50 text-blue-600",
    };
  if (bmi < 25)
    return {
      label: "Normal",
      color: "text-green-500",
      badgeClass: "bg-green-50 text-green-600",
    };
  if (bmi < 30)
    return {
      label: "Overweight",
      color: "text-orange-500",
      badgeClass: "bg-orange-50 text-orange-600",
    };
  return {
    label: "Obese",
    color: "text-red-500",
    badgeClass: "bg-red-50 text-red-600",
  };
}

function deltaStatus(
  delta: number,
  goal: "lose" | "gain" | null
): "increase" | "decrease" | "none" {
  if (delta === 0) return "none";
  return delta > 0 ? "increase" : "decrease";
}

function formatDelta(d: number): string {
  if (d === 0) return "0.0 kg";
  return `${d > 0 ? "+" : ""}${d.toFixed(1)} kg`;
}

function deltaText(d: number): string {
  if (d === 0) return "No change";
  return d > 0 ? "Increase" : "Decrease";
}

function TrendSvg({ delta }: { delta: number }) {
  if (delta === 0) {
    return (
      <svg width="32" height="16" viewBox="0 0 32 16" fill="none">
        <path
          d="M2 8L16 8L30 8"
          stroke="#D1D1D6"
          strokeWidth="2"
          strokeLinecap="round"
        />
      </svg>
    );
  }
  if (delta < 0) {
    // downward
    return (
      <svg width="32" height="16" viewBox="0 0 32 16" fill="none">
        <path
          d="M2 4L10 8L18 10L30 14"
          stroke="#34C759"
          strokeWidth="2"
          strokeLinecap="round"
          strokeLinejoin="round"
        />
      </svg>
    );
  }
  // upward
  return (
    <svg width="32" height="16" viewBox="0 0 32 16" fill="none">
      <path
        d="M2 14L10 10L18 8L30 4"
        stroke="#F58A42"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  );
}

// ─── Skeleton ─────────────────────────────────────────────────────────────────
function SkeletonCard() {
  return (
    <Card className="bg-white border-none shadow-xs dark:bg-neutral-900">
      <CardContent className="space-y-3 pt-4">
        <div className="h-4 bg-gray-100 dark:bg-neutral-800 rounded w-1/3 animate-pulse" />
        <div className="h-8 bg-gray-100 dark:bg-neutral-800 rounded w-1/2 animate-pulse" />
        <div className="h-2 bg-gray-100 dark:bg-neutral-800 rounded w-full animate-pulse" />
      </CardContent>
    </Card>
  );
}

// ─── Main Component ───────────────────────────────────────────────────────────
export default function ProgressScreen() {
  const [weightRange, setWeightRange] = useState<WeightRange>(90);
  const [energyOffset, setEnergyOffset] = useState<WeekOffset>(0);

  const [weightData, setWeightData] = useState<WeightProgress | null>(null);
  const [energyData, setEnergyData] = useState<WeeklyEnergy | null>(null);
  const [calorieData, setCalorieData] = useState<CalorieAverages | null>(null);

  const [loadingWeight, setLoadingWeight] = useState(true);
  const [loadingEnergy, setLoadingEnergy] = useState(true);
  const [loadingCalories, setLoadingCalories] = useState(true);

  const initialLoaded = useRef(false);

  // Combined initial fetch — one request instead of three
  useEffect(() => {
    getProgressSummary()
      .then((res) => {
        setWeightData(res.data.weight);
        setEnergyData(res.data.energy);
        setCalorieData(res.data.calories);
        initialLoaded.current = true;
      })
      .catch((err) => toast.error(err.message || "Failed to load progress data"))
      .finally(() => {
        setLoadingWeight(false);
        setLoadingEnergy(false);
        setLoadingCalories(false);
      });
  }, []);

  // Fetch weight progress whenever range changes (skip initial load)
  const fetchWeightProgress = useCallback(async () => {
    setLoadingWeight(true);
    try {
      const res = await getWeightProgress(weightRange);
      setWeightData(res.data);
    } catch (err: any) {
      toast.error(err.message || "Failed to load weight data");
    } finally {
      setLoadingWeight(false);
    }
  }, [weightRange]);

  // Fetch weekly energy whenever offset changes (skip initial load)
  const fetchWeeklyEnergy = useCallback(async () => {
    setLoadingEnergy(true);
    try {
      const res = await getWeeklyEnergy(energyOffset);
      setEnergyData(res.data);
    } catch (err: any) {
      toast.error(err.message || "Failed to load energy data");
    } finally {
      setLoadingEnergy(false);
    }
  }, [energyOffset]);

  useEffect(() => {
    if (!initialLoaded.current) return;
    fetchWeightProgress();
  }, [fetchWeightProgress]);

  useEffect(() => {
    if (!initialLoaded.current) return;
    fetchWeeklyEnergy();
  }, [fetchWeeklyEnergy]);

  // Derived
  const bmi = weightData?.bmi ?? 0;
  const bmiInfo = bmiCategory(bmi);
  const bmiMarkerPos = bmi > 0 ? ((bmi - 15) / (40 - 15)) * 100 : 0;

  const rangeLabels: Record<WeightRange, string> = {
    90: "90D",
    180: "6M",
    365: "1Y",
    all: "ALL",
  };
  const weekLabels = ["This wk", "Last wk", "2 wk ago", "3 wk ago"];

  // Energy bar chart data
  const energyChartData =
    energyData?.days.map((d) => ({
      day: d.day,
      consumed: d.consumed_cal,
    })) ?? [];

  // Calorie trend line for "Daily Average" card
  const calorieTrendData =
    calorieData?.logs_7d.map((l) => ({
      day: new Date(l.log_date).toLocaleDateString("en", { weekday: "short" }),
      calories: l.calories,
    })) ?? [];

  return (
    <div>
      <Header title="Progress" subtitle="Track your health journey" />

      <div className="space-y-4 max-w-2xl mx-auto">
        {/* ── Current Weight Card ─────────────────────────────────────── */}
        {loadingWeight ? (
          <SkeletonCard />
        ) : weightData ? (
          <Card className="bg-white border-none shadow-xs dark:bg-neutral-900">
            <CardContent>
              <div className="flex justify-between items-center mb-4">
                <span className="text-sm text-gray-500 dark:text-gray-400 font-medium">
                  Current Weight
                </span>
                <Badge
                  variant="secondary"
                  className="bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-full px-3 py-1 text-[11px] font-normal"
                >
                  Goal: {weightData.goal_weight ? `${weightData.goal_weight} kg` : "—"}
                </Badge>
              </div>
              <h2 className="text-4xl font-bold mb-4 dark:text-white">
                {weightData.current_weight}{" "}
                <span className="text-xl font-normal text-gray-500">kg</span>
              </h2>
              <div className="space-y-3">
                <Progress
                  value={weightData.goal_progress}
                  className="h-1.5 bg-gray-100 dark:bg-gray-800"
                />
                <div className="flex justify-between text-xs text-gray-400 dark:text-gray-500">
                  <span>
                    Start:{" "}
                    <span className="text-black dark:text-white font-semibold">
                      {weightData.start_weight} kg
                    </span>
                  </span>
                  <span>
                    Goal:{" "}
                    <span className="text-black dark:text-white font-semibold">
                      {weightData.goal_weight ? `${weightData.goal_weight} kg` : "—"}
                    </span>
                  </span>
                </div>
              </div>
              {weightData.goal_weight && (
                <p className="mt-4 text-xs text-gray-500 dark:text-gray-400">
                  {weightData.goal_progress >= 100
                    ? "🎉 You've reached your goal!"
                    : `${weightData.goal_progress.toFixed(0)}% towards your goal.`}
                </p>
              )}
            </CardContent>
          </Card>
        ) : null}

        {/* ── Weight Progress Chart ───────────────────────────────────── */}
        <Card className="bg-white border-none shadow-xs dark:bg-neutral-900">
          <CardHeader className="flex flex-row items-center justify-between">
            <CardTitle className="text-lg font-bold dark:text-white">
              Weight Progress
            </CardTitle>
            {weightData && (
              <Badge
                variant="secondary"
                className="bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-full px-2 py-0.5 flex items-center gap-1 text-[11px]"
              >
                <Flag className="w-3 h-3" />
                {weightData.goal_progress.toFixed(0)}% of goal
              </Badge>
            )}
          </CardHeader>
          <CardContent>
            <div className="h-[200px] w-full mt-4">
              {loadingWeight ? (
                <div className="h-full flex items-center justify-center">
                  <Loader2 className="w-6 h-6 animate-spin text-gray-300" />
                </div>
              ) : (
                <ResponsiveContainer width="100%" height="100%">
                  <LineChart data={weightData?.logs ?? []}>
                    <CartesianGrid
                      vertical={false}
                      strokeDasharray="3 3"
                      stroke="#f0f0f0"
                    />
                    <XAxis
                      dataKey="day"
                      axisLine={false}
                      tickLine={false}
                      tick={{ fontSize: 12, fill: "#8E8E93" }}
                      dy={10}
                    />
                    <YAxis
                      domain={["dataMin - 2", "dataMax + 2"]}
                      axisLine={false}
                      tickLine={false}
                      tick={{ fontSize: 12, fill: "#8E8E93" }}
                      dx={-10}
                    />
                    <Tooltip
                      contentStyle={{
                        borderRadius: "12px",
                        border: "none",
                        boxShadow: "0 4px 12px rgba(0,0,0,0.1)",
                      }}
                      formatter={(v: number) => [`${v} kg`, "Weight"]}
                    />
                    <Line
                      type="monotone"
                      dataKey="weight"
                      stroke="#000"
                      strokeWidth={3}
                      dot={false}
                      activeDot={{ r: 6, fill: "#000", stroke: "#fff", strokeWidth: 2 }}
                    />
                  </LineChart>
                </ResponsiveContainer>
              )}
            </div>

            <div className="bg-gray-100 p-1 rounded-2xl flex mt-6 dark:bg-neutral-800">
              {(["90D", "6M", "1Y", "ALL"] as const).map((label, i) => {
                const rv = ([90, 180, 365, "all"] as const)[i];
                return (
                  <button
                    key={label}
                    onClick={() => setWeightRange(rv)}
                    className={`flex-1 py-2 text-xs font-medium rounded-xl transition-all ${
                      weightRange === rv
                        ? "bg-white shadow-xs text-black dark:bg-neutral-700 dark:text-white"
                        : "text-gray-500"
                    }`}
                  >
                    {label}
                  </button>
                );
              })}
            </div>
          </CardContent>
        </Card>

        {/* ── Weight Changes Card ─────────────────────────────────────── */}
        <Card className="bg-white border-none shadow-xs dark:bg-neutral-900">
          <CardHeader>
            <CardTitle className="text-lg font-bold dark:text-white">
              Weight Changes
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="divide-y divide-gray-100 dark:divide-gray-800">
              {loadingWeight ? (
                <div className="py-6 flex justify-center">
                  <Loader2 className="w-5 h-5 animate-spin text-gray-300" />
                </div>
              ) : (
                (["3d", "7d", "30d"] as const).map((key) => {
                  const delta = weightData?.deltas[key] ?? 0;
                  const label = key === "3d" ? "3 day" : key === "7d" ? "7 day" : "30 day";
                  return (
                    <ChangeRow
                      key={key}
                      timeframe={label}
                      trendIcon={<TrendSvg delta={delta} />}
                      value={formatDelta(delta)}
                      changeText={deltaText(delta)}
                      changeStatus={deltaStatus(delta, null)}
                    />
                  );
                })
              )}
            </div>
          </CardContent>
        </Card>

        {/* ── Progress Photos Card ────────────────────────────────────── */}
        <Card className="bg-white border-none shadow-xs dark:bg-neutral-900 overflow-hidden">
          <CardHeader>
            <CardTitle className="text-lg font-bold dark:text-white">
              Progress Photos
            </CardTitle>
          </CardHeader>
          <CardContent className="flex items-center gap-6">
            <div className="w-24 h-24 bg-gray-100 rounded-2xl flex items-center justify-center flex-shrink-0 dark:bg-neutral-800">
              <Camera className="w-10 h-10 text-gray-300" />
            </div>
            <div className="flex flex-col items-start gap-3">
              <p className="text-sm text-gray-500 dark:text-gray-400 leading-snug">
                Want to add a photo to track your progress?
              </p>
              <Button
                variant="outline"
                className="rounded-full border-gray-200 text-xs font-bold h-9 px-6 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-neutral-800"
              >
                <Plus className="w-4 h-4 mr-1" /> Upload a Photo
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* ── Weekly Energy Card ──────────────────────────────────────── */}
        <Card className="bg-white border-none shadow-xs dark:bg-neutral-900">
          <CardHeader>
            <CardTitle className="text-lg font-bold dark:text-white">
              Weekly Energy
            </CardTitle>
            {energyData && (
              <div className="flex gap-4 mt-2">
                <div className="flex items-center gap-1.5">
                  <div className="w-2.5 h-2.5 bg-green-500 rounded-full" />
                  <span className="text-[11px] text-gray-500">
                    <span className="text-black dark:text-white font-bold">
                      {energyData.total_consumed.toLocaleString()}
                    </span>{" "}
                    cal consumed
                  </span>
                </div>
              </div>
            )}
          </CardHeader>
          <CardContent>
            <div className="h-[180px] w-full mt-6">
              {loadingEnergy ? (
                <div className="h-full flex items-center justify-center">
                  <Loader2 className="w-6 h-6 animate-spin text-gray-300" />
                </div>
              ) : (
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart data={energyChartData}>
                    <XAxis
                      dataKey="day"
                      axisLine={false}
                      tickLine={false}
                      tick={{ fontSize: 11, fill: "#8E8E93" }}
                    />
                    <Tooltip
                      contentStyle={{
                        borderRadius: "12px",
                        border: "none",
                        boxShadow: "0 4px 12px rgba(0,0,0,0.1)",
                      }}
                      formatter={(v: number) => [`${v} cal`, "Consumed"]}
                    />
                    <Bar
                      dataKey="consumed"
                      fill="#22c55e"
                      radius={[4, 4, 0, 0]}
                      barSize={18}
                    />
                  </BarChart>
                </ResponsiveContainer>
              )}
            </div>

            <div className="bg-gray-100 p-1 rounded-2xl flex mt-6 dark:bg-neutral-800">
              {weekLabels.map((label, i) => (
                <button
                  key={label}
                  onClick={() => setEnergyOffset(i as WeekOffset)}
                  className={`flex-1 py-2 text-[10px] font-medium rounded-xl transition-all whitespace-nowrap ${
                    energyOffset === i
                      ? "bg-white shadow-xs text-black dark:bg-neutral-700 dark:text-white"
                      : "text-gray-500"
                  }`}
                >
                  {label}
                </button>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* ── Daily Average Calories Card ─────────────────────────────── */}
        <Card className="bg-white border-none shadow-xs dark:bg-neutral-900">
          <CardHeader>
            <CardTitle className="text-lg font-bold dark:text-white">
              Daily Average Calories
            </CardTitle>
          </CardHeader>
          <CardContent>
            {loadingCalories ? (
              <div className="flex justify-center py-8">
                <Loader2 className="w-6 h-6 animate-spin text-gray-300" />
              </div>
            ) : calorieData?.avg_7d ? (
              <div className="space-y-4">
                <div className="flex gap-6">
                  <div className="flex flex-col">
                    <span className="text-3xl font-bold dark:text-white">
                      {calorieData.avg_7d.toLocaleString()}
                    </span>
                    <span className="text-xs text-gray-500 mt-1">7-day avg</span>
                  </div>
                  {calorieData.avg_30d && (
                    <div className="flex flex-col border-l pl-6 border-gray-100 dark:border-gray-800">
                      <span className="text-3xl font-bold dark:text-white">
                        {calorieData.avg_30d.toLocaleString()}
                      </span>
                      <span className="text-xs text-gray-500 mt-1">30-day avg</span>
                    </div>
                  )}
                </div>

                {calorieTrendData.length > 0 && (
                  <div className="h-[100px] w-full mt-2">
                    <ResponsiveContainer width="100%" height="100%">
                      <LineChart data={calorieTrendData}>
                        <XAxis
                          dataKey="day"
                          axisLine={false}
                          tickLine={false}
                          tick={{ fontSize: 11, fill: "#8E8E93" }}
                        />
                        <Tooltip
                          contentStyle={{
                            borderRadius: "12px",
                            border: "none",
                            boxShadow: "0 4px 12px rgba(0,0,0,0.1)",
                          }}
                          formatter={(v: number) => [`${v} cal`, "Calories"]}
                        />
                        <Line
                          type="monotone"
                          dataKey="calories"
                          stroke="#000"
                          strokeWidth={2}
                          dot={false}
                        />
                      </LineChart>
                    </ResponsiveContainer>
                  </div>
                )}
              </div>
            ) : (
              <div className="flex flex-col items-center justify-center min-h-[180px] text-center">
                <div className="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 dark:bg-neutral-800">
                  <LineChartIcon className="w-8 h-8 text-gray-300" />
                </div>
                <h3 className="text-sm font-bold dark:text-white mb-1">
                  No data to show
                </h3>
                <p className="text-xs text-gray-500 dark:text-gray-400">
                  This will update as you log more food.
                </p>
              </div>
            )}
          </CardContent>
        </Card>

        {/* ── BMI Card ────────────────────────────────────────────────── */}
        <Card className="bg-white border-none shadow-xs dark:bg-neutral-900">
          <CardHeader className="flex flex-row items-center justify-between">
            <CardTitle className="text-lg font-bold dark:text-white">
              Your BMI
            </CardTitle>
            <Info className="w-5 h-5 text-gray-400 cursor-pointer" />
          </CardHeader>
          <CardContent>
            {loadingWeight ? (
              <div className="h-16 flex items-center justify-center">
                <Loader2 className="w-6 h-6 animate-spin text-gray-300" />
              </div>
            ) : (
              <>
                <div className="flex items-baseline gap-2">
                  <span className="text-4xl font-bold dark:text-white">{bmi}</span>
                  <div className="flex items-center gap-1">
                    <span className="text-sm text-gray-400">Your weight is </span>
                    <Badge
                      variant="secondary"
                      className={`${bmiInfo.badgeClass} rounded-lg px-2 py-0.5 text-xs font-semibold border-none`}
                    >
                      {bmiInfo.label}
                    </Badge>
                  </div>
                </div>

                {/* BMI Scale */}
                <div className="relative mt-10 mb-8">
                  <div className="flex h-2.5 w-full rounded-full overflow-hidden gap-0.5">
                    <div className="w-[18%] bg-blue-400 h-full" />
                    <div className="w-[22%] bg-green-500 h-full" />
                    <div className="w-[30%] bg-orange-400 h-full" />
                    <div className="w-[30%] bg-red-500 h-full" />
                  </div>
                  <div
                    className="absolute top-[-6px] h-6 w-0.5 bg-black dark:bg-white transition-all duration-500"
                    style={{ left: `${Math.min(bmiMarkerPos, 98)}%` }}
                  />
                </div>

                <div className="grid grid-cols-4 gap-2">
                  {[
                    { color: "bg-blue-400", label: "Underweight", range: "<18.5" },
                    { color: "bg-green-500", label: "Normal", range: "18.5-24.9" },
                    { color: "bg-orange-400", label: "Overweight", range: "25-29.9" },
                    { color: "bg-red-500", label: "Obese", range: ">30" },
                  ].map((item) => (
                    <div key={item.label} className="flex flex-col items-center">
                      <div className={`w-2 h-2 ${item.color} rounded-full mb-1`} />
                      <span className="text-[10px] text-gray-500 text-center leading-tight">
                        {item.label}
                        <br />
                        {item.range}
                      </span>
                    </div>
                  ))}
                </div>
              </>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
