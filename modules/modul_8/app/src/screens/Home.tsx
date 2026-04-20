import Header from "@/components/header/Header";
import type { TabItem } from "@/components/navigation/tabBar";
import TabBar from "@/components/navigation/tabBar";
import { Home, Activity, Settings } from "lucide-react";
import { useState } from "react";
import ActivityRings from "react-activity-rings";
import ProgressScreen from "./ProgressScreens";
import SettingsScreen from "./SettingsScreens";
import type {
  ActivityRingsConfig,
  ActivityRingsData,
} from "react-activity-rings";
import { Card, CardContent } from "@/components/ui";

const NAV_TABS: TabItem[] = [
  { id: "home", label: "Home", icon: <Home size={20} /> },
  { id: "progress", label: "Progress", icon: <Activity size={20} /> },
  { id: "settings", label: "Settings", icon: <Settings size={20} /> },
];

// function get date now
const getDateNow = () => {
  return new Date();
};

const DAY_LABELS = ["M", "T", "W", "T", "F", "S", "S"];

const generateRandomValue = () => Math.round(Math.random() * 100) / 100;

// Generate 30 days of data
const weeklyData = Array.from({ length: 30 }, (_, i) => ({
  day: DAY_LABELS[i % 7],
  activityData: [{ value: generateRandomValue() }] as ActivityRingsData[],
}));

const activityConfig: ActivityRingsConfig = {
  width: 100,
  height: 100,
};

const calActivityConfig = {
  width: 150,
  height: 150,
  radius: 55,
  ringSize: 24,
} as ActivityRingsConfig;

const calActivityData: ActivityRingsData[] = [{ value: 0.75 }];

const RingOtherConfig = {
  width: 90,
  height: 90,
  radius: 90 / 2 - 12,
  ringSize: 10,
} as ActivityRingsConfig;

const RingOtherItem = [
  { label: "Protein", value: 0.5 },
  { label: "Carbs", value: 0.3 },
  { label: "Fats", value: 0.2 },
];

function HomeContent() {
  return (
    <div>
      {/* Header */}
      <Header
        title="Home"
        subtitle={`${getDateNow().toLocaleDateString("en-US", {
          weekday: "long",
          day: "2-digit",
          month: "long",
          year: "numeric",
        })}`}
      />
      {/* daylist */}
      <div className="w-full flex flex-row flex-nowrap items-center justify-start overflow-x-auto pb-2 scrollbar-hide">
        {weeklyData.map((item, index) => (
          <div
            key={index}
            className="flex flex-col items-center justify-center shrink-0 gap-0 w-15"
          >
            {/* day (M T W T F S S) */}
            <p className="text-white font-semibold">{item.day}</p>
            <div className="scale-50 -mt-4">
              <ActivityRings data={item.activityData} config={activityConfig} />
            </div>
          </div>
        ))}
      </div>

      <div className="flex flex-col w-full gap-4">
        <Card className="bg-slate-800">
          <CardContent className="grid-rows-2 flex flex-row items-center justify-between">
            <div className="flex flex-col items-start text-white w-full">
              <p className="text-xl font-medium">Calories</p>
              <p className="text-lg font-bold">266 kkal</p>
            </div>
            <div style={{ width: "150px", height: "150px" }}>
              <ActivityRings
                data={calActivityData}
                config={calActivityConfig}
              />
            </div>
          </CardContent>
        </Card>

        <div className="grid grid-cols-3 gap-4">
          {RingOtherItem.map((item) => (
            <Card key={item.label} className="bg-slate-800">
              <CardContent className="flex items-center justify-center relative aspect-square w-full h-20">
                <ActivityRings
                  data={[{ value: item.value }]}
                  config={RingOtherConfig}
                />
                <p className="absolute text-sm text-white font-medium">
                  {item.label}
                </p>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </div>
  );
}

export default function HomeScreen() {
  const [activeTab, setActiveTab] = useState("home");

  const handleTabChange = (tabId: string) => {
    setActiveTab(tabId);
  };

  return (
    <div className="min-h-screen p-4 pb-24 md:pb-4 bg-slate-50 dark:bg-slate-950 transition-colors">
      <div className="max-w-4xl mx-auto">
        {/* TabBar — fixed bottom on mobile, top-centered on desktop */}
        <div className="mb-8">
          <TabBar
            tabs={NAV_TABS}
            activeTab={activeTab}
            onTabChange={handleTabChange}
            onSidebarToggle={() => console.log("sidebar toggled")}
            onSearch={() => console.log("search clicked")}
          />
        </div>

        {/* Screen Navigation */}
        {activeTab === "home" && <HomeContent />}
        {activeTab === "progress" && <ProgressScreen />}
        {activeTab === "settings" && <SettingsScreen />}
      </div>
    </div>
  );
}
