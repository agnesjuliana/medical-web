import { useState } from "react";
import Header from "@/components/header/Header";
import {
  Plus,
  Camera,
  Info,
  Flag,
  LineChart as LineChartIcon,
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

// Mock data for charts
const weightData = [
  { day: "Mon", weight: 75.5 },
  { day: "Tue", weight: 75.2 },
  { day: "Wed", weight: 74.8 },
  { day: "Thu", weight: 74.5 },
  { day: "Fri", weight: 74.2 },
  { day: "Sat", weight: 74.0 },
  { day: "Sun", weight: 74.0 },
];

const energyData = [
  { day: "Sun", burned: 200, consumed: 150 },
  { day: "Mon", burned: 450, consumed: 0 },
  { day: "Tue", burned: 300, consumed: 0 },
  { day: "Wed", burned: 500, consumed: 0 },
  { day: "Thu", burned: 400, consumed: 0 },
  { day: "Fri", burned: 600, consumed: 0 },
  { day: "Sat", burned: 350, consumed: 0 },
];

export default function ProgressScreen() {
  const [timeRange, setTimeRange] = useState("90D");
  const [energyRange, setEnergyRange] = useState("This wk");

  const bmi = 25.6;
  const bmiMarkerPos = ((bmi - 15) / (40 - 15)) * 100; // Simplified scaling for 15-40 range

  return (
    <div className="">
      <Header title="Progress" subtitle="Track your health journey" />

      <div className="space-y-4 max-w-2xl mx-auto">
        {/* Phase 3: Current Weight Card */}
        <Card className="bg-white border-none shadow-xs dark:bg-neutral-900">
          <CardContent className="">
            <div className="flex justify-between items-center mb-4">
              <span className="text-sm text-gray-500 dark:text-gray-400 font-medium">
                Current Weight
              </span>
              <Badge
                variant="secondary"
                className="bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-full px-3 py-1 text-[11px] font-normal"
              >
                Next weigh-in: 7d
              </Badge>
            </div>
            <h2 className="text-4xl font-bold mb-4 dark:text-white">
              74 <span className="text-xl font-normal text-gray-500">kg</span>
            </h2>
            <div className="space-y-3">
              <Progress
                value={20}
                className="h-1.5 bg-gray-100 dark:bg-gray-800"
              />
              <div className="flex justify-between text-xs text-gray-400 dark:text-gray-500">
                <span>
                  Start:{" "}
                  <span className="text-black dark:text-white font-semibold">
                    74 kg
                  </span>
                </span>
                <span>
                  Goal:{" "}
                  <span className="text-black dark:text-white font-semibold">
                    69 kg
                  </span>
                </span>
              </div>
            </div>
            <p className="mt-4 text-xs text-gray-500 dark:text-gray-400">
              At your goal by{" "}
              <span className="text-black dark:text-white">Jun 26, 2026.</span>
            </p>
          </CardContent>
        </Card>

        {/* Phase 4: Weight Progress Chart */}
        <Card className="bg-white border-none shadow-xs dark:bg-neutral-900">
          <CardHeader className="flex flex-row items-center justify-between">
            <CardTitle className="text-lg font-bold dark:text-white">
              Weight Progress
            </CardTitle>
            <Badge
              variant="secondary"
              className="bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-full px-2 py-0.5 flex items-center gap-1 text-[11px]"
            >
              <Flag className="w-3 h-3" />
              0% of goal
            </Badge>
          </CardHeader>
          <CardContent className="">
            <div className="h-[200px] w-full mt-4">
              <ResponsiveContainer width="100%" height="100%">
                <LineChart data={weightData}>
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
                  />
                  <Line
                    type="monotone"
                    dataKey="weight"
                    stroke="#000"
                    strokeWidth={3}
                    dot={false}
                    activeDot={{
                      r: 6,
                      fill: "#000",
                      stroke: "#fff",
                      strokeWidth: 2,
                    }}
                  />
                </LineChart>
              </ResponsiveContainer>
            </div>

            <div className="bg-gray-100 p-1 rounded-2xl flex mt-6 dark:bg-neutral-800">
              {["90D", "6M", "1Y", "ALL"].map((range) => (
                <button
                  key={range}
                  onClick={() => setTimeRange(range)}
                  className={`flex-1 py-2 text-xs font-medium rounded-xl transition-all ${
                    timeRange === range
                      ? "bg-white shadow-xs text-black dark:bg-neutral-700 dark:text-white"
                      : "text-gray-500"
                  }`}
                >
                  {range}
                </button>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Phase 5: Weight Changes Card */}
        <Card className="bg-white border-none shadow-xs dark:bg-neutral-900">
          <CardHeader className="">
            <CardTitle className="text-lg font-bold dark:text-white">
              Weight Changes
            </CardTitle>
          </CardHeader>
          <CardContent className="">
            <div className="divide-y divide-gray-100 dark:divide-gray-800">
              <ChangeRow
                timeframe="3 day"
                trendIcon={
                  <svg width="32" height="16" viewBox="0 0 32 16" fill="none">
                    <path
                      d="M2 14L10 14L18 14L30 14"
                      stroke="#D1D1D6"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                }
                value="0.0 kg"
                changeText="No change"
                changeStatus="none"
              />
              <ChangeRow
                timeframe="7 day"
                trendIcon={
                  <svg width="32" height="16" viewBox="0 0 32 16" fill="none">
                    <path
                      d="M2 14L10 14L18 14L30 14"
                      stroke="#D1D1D6"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                }
                value="0.0 kg"
                changeText="No change"
                changeStatus="none"
              />
              <ChangeRow
                timeframe="30 day"
                trendIcon={
                  <svg width="32" height="16" viewBox="0 0 32 16" fill="none">
                    <path
                      d="M2 14L10 14L18 14L30 14"
                      stroke="#D1D1D6"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                }
                value="0.0 kg"
                changeText="No change"
                changeStatus="none"
              />
            </div>
          </CardContent>
        </Card>

        {/* Phase 5: Expenditure Changes Card */}
        <Card className="bg-white border-none shadow-xs dark:bg-neutral-900">
          <CardHeader className="">
            <CardTitle className="text-lg font-bold dark:text-white">
              Expenditure Changes
            </CardTitle>
          </CardHeader>
          <CardContent className="">
            <div className="divide-y divide-gray-100 dark:divide-gray-800">
              <ChangeRow
                timeframe="3 day"
                trendIcon={
                  <svg width="32" height="16" viewBox="0 0 32 16" fill="none">
                    <path
                      d="M2 8L10 12L18 10L30 14"
                      stroke="#F58A42"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                }
                value="-137.7 cal"
                changeText="Decrease"
                changeStatus="decrease"
              />
              <ChangeRow
                timeframe="7 day"
                trendIcon={
                  <svg width="32" height="16" viewBox="0 0 32 16" fill="none">
                    <path
                      d="M2 4L10 8L18 6L30 10"
                      stroke="#F58A42"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                }
                value="-54.0 cal"
                changeText="Decrease"
                changeStatus="decrease"
              />
              <ChangeRow
                timeframe="30 day"
                trendIcon={
                  <svg width="32" height="16" viewBox="0 0 32 16" fill="none">
                    <path
                      d="M2 14L10 8L18 10L30 2"
                      stroke="#34C759"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                }
                value="+210.5 cal"
                changeText="Increase"
                changeStatus="increase"
              />
            </div>
          </CardContent>
        </Card>

        {/* Phase 6: Progress Photos Card */}
        <Card className="bg-white border-none shadow-xs dark:bg-neutral-900 overflow-hidden">
          <CardHeader className="">
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

        {/* Phase 7: Weekly Energy Card */}
        <Card className="bg-white border-none shadow-xs dark:bg-neutral-900">
          <CardHeader className="">
            <CardTitle className="text-lg font-bold dark:text-white">
              Weekly Energy
            </CardTitle>
            <div className="flex gap-4 mt-2">
              <div className="flex items-center gap-1.5">
                <div className="w-2.5 h-2.5 bg-orange-500 rounded-full" />
                <span className="text-[11px] text-gray-500">
                  <span className="text-black dark:text-white font-bold">
                    905
                  </span>{" "}
                  cal
                </span>
              </div>
              <div className="flex items-center gap-1.5">
                <div className="w-2.5 h-2.5 bg-green-500 rounded-full" />
                <span className="text-[11px] text-gray-500">
                  <span className="text-black dark:text-white font-bold">
                    0
                  </span>{" "}
                  cal
                </span>
              </div>
              <div className="flex items-center gap-1.5">
                <div className="w-2.5 h-2.5 bg-gray-300 rounded-full" />
                <span className="text-[11px] text-gray-500">
                  <span className="text-black dark:text-white font-bold">
                    -905
                  </span>{" "}
                  cal
                </span>
              </div>
            </div>
          </CardHeader>
          <CardContent className="">
            <div className="h-[180px] w-full mt-6">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={energyData}>
                  <XAxis
                    dataKey="day"
                    axisLine={false}
                    tickLine={false}
                    tick={{ fontSize: 11, fill: "#8E8E93" }}
                  />
                  <Bar
                    dataKey="burned"
                    fill="#f97316"
                    radius={[4, 4, 0, 0]}
                    barSize={8}
                  />
                  <Bar
                    dataKey="consumed"
                    fill="#22c55e"
                    radius={[4, 4, 0, 0]}
                    barSize={8}
                  />
                </BarChart>
              </ResponsiveContainer>
            </div>

            <div className="bg-gray-100 p-1 rounded-2xl flex mt-6 dark:bg-neutral-800">
              {["This wk", "Last wk", "2 wk ago", "3 wk ago"].map((range) => (
                <button
                  key={range}
                  onClick={() => setEnergyRange(range)}
                  className={`flex-1 py-2 text-[10px] font-medium rounded-xl transition-all whitespace-nowrap ${
                    energyRange === range
                      ? "bg-white shadow-xs text-black dark:bg-neutral-700 dark:text-white"
                      : "text-gray-500"
                  }`}
                >
                  {range}
                </button>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Phase 7: Daily Average Calories Card */}
        <Card className="bg-white border-none shadow-xs dark:bg-neutral-900">
          <CardHeader className="">
            <CardTitle className="text-lg font-bold dark:text-white">
              Daily Average Calories
            </CardTitle>
          </CardHeader>
          <CardContent className="flex flex-col items-center justify-center min-h-[200px] text-center">
            <div className="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 dark:bg-neutral-800">
              <LineChartIcon className="w-8 h-8 text-gray-300" />
            </div>
            <h3 className="text-sm font-bold dark:text-white mb-1">
              No data to show
            </h3>
            <p className="text-xs text-gray-500 dark:text-gray-400">
              This will update as you log more food.
            </p>
          </CardContent>
        </Card>

        {/* Phase 8: Your BMI Card */}
        <Card className="bg-white border-none shadow-xs dark:bg-neutral-900">
          <CardHeader className="flex flex-row items-center justify-between">
            <CardTitle className="text-lg font-bold dark:text-white">
              Your BMI
            </CardTitle>
            <Info className="w-5 h-5 text-gray-400 cursor-pointer" />
          </CardHeader>
          <CardContent className="">
            <div className="flex items-baseline gap-2">
              <span className="text-4xl font-bold dark:text-white">{bmi}</span>
              <div className="flex items-center gap-1">
                <span className="text-sm text-gray-400">Your weight is </span>
                <Badge
                  variant="secondary"
                  className="bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400 rounded-lg px-2 py-0.5 text-xs font-semibold border-none"
                >
                  Overweight
                </Badge>
              </div>
            </div>

            {/* BMI Scale Bar */}
            <div className="relative mt-10 mb-8">
              <div className="flex h-2.5 w-full rounded-full overflow-hidden gap-0.5">
                <div className="w-[18%] bg-blue-400 h-full" />
                <div className="w-[22%] bg-green-500 h-full" />
                <div className="w-[30%] bg-orange-400 h-full" />
                <div className="w-[30%] bg-red-500 h-full" />
              </div>
              {/* Marker */}
              <div
                className="absolute top-[-6px] h-6 w-0.5 bg-black dark:bg-white transition-all duration-500"
                style={{ left: `${bmiMarkerPos}%` }}
              />
            </div>

            <div className="grid grid-cols-4 gap-2">
              <div className="flex flex-col items-center">
                <div className="w-2 h-2 bg-blue-400 rounded-full mb-1" />
                <span className="text-[10px] text-gray-500 text-center leading-tight">
                  Underweight
                  <br />
                  &lt;18.5
                </span>
              </div>
              <div className="flex flex-col items-center">
                <div className="w-2 h-2 bg-green-500 rounded-full mb-1" />
                <span className="text-[10px] text-gray-500 text-center leading-tight">
                  Normal
                  <br />
                  18.5-24.9
                </span>
              </div>
              <div className="flex flex-col items-center">
                <div className="w-2 h-2 bg-orange-400 rounded-full mb-1" />
                <span className="text-[10px] text-gray-500 text-center leading-tight">
                  Overweight
                  <br />
                  25-29.9
                </span>
              </div>
              <div className="flex flex-col items-center">
                <div className="w-2 h-2 bg-red-500 rounded-full mb-1" />
                <span className="text-[10px] text-gray-500 text-center leading-tight">
                  Obese
                  <br />
                  &gt;30
                </span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
