import Header from "@/components/header/Header";
import type { TabItem } from "@/components/navigation/tabBar";
import TabBar from "@/components/navigation/tabBar";
import {
  Home,
  Activity,
  Settings,
  Flame,
  Beef,
  Wheat,
  Droplets,
  Footprints,
  Bell,
  X,
  Heart,
} from "lucide-react";
import { useRef, useState } from "react";
import ProgressScreen from "./ProgressScreens";
import SettingsScreen from "./SettingsScreens";
import ScannerScreen from "./ScannerScreen";
import FoodDetailScreen from "./FoodDetailScreen";
import AccountDetailsScreen from "./AccountDetailsScreen";
import LogFood from "./LogFood";
import Daylist from "@/components/template/Daylist";
import Ring from "@/components/ui/Ring";
import { cn } from "@/lib/utils";
import {
  Drawer,
  DrawerContent,
  DrawerHeader,
  DrawerTitle,
  DrawerTrigger,
} from "@/components/ui/drawer";
import { Button } from "@/components/ui";

const NAV_TABS: TabItem[] = [
  { id: "home", label: "Home", icon: <Home size={20} /> },
  { id: "progress", label: "Progress", icon: <Activity size={20} /> },
  { id: "settings", label: "Settings", icon: <Settings size={20} /> },
];

// ── Food Log Types & Mock Data ────────────────────────────────────────────────
import FoodCard, { type FoodItem } from "@/components/template/FoodCard";

const MOCK_FOOD_LOGS: FoodItem[] = [
  {
    id: "1",
    status: "analyzing",
    imageUrl:
      "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&q=80",
    progress: 28,
  },
  {
    id: "2",
    status: "analyzed",
    name: "De Milan Wafer Cream Chocolate",
    time: "03:24 AM",
    calories: 150,
    protein: 2,
    carbs: 20,
    fats: 7,
    imageUrl:
      "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&q=80",
  },
  {
    id: "3",
    status: "analyzed",
    name: "Peanut Butter",
    time: "03:22 AM",
    calories: 94,
    protein: 4,
    carbs: 3,
    fats: 8,
  },
  {
    id: "4",
    status: "analyzed",
    name: "Peanut Butter",
    time: "03:22 AM",
    calories: 94,
    protein: 4,
    carbs: 3,
    fats: 8,
  },
];

// ── Stub data (replaced by API later) ────────────────────────────────────────
const DATA = {
  calories: { left: 2583, target: 2850 },
  protein: { leftG: 184, targetG: 213 },
  carbs: { leftG: 300, targetG: 356 },
  fats: { leftG: 71, targetG: 95 },
  fiber: { leftG: 38, targetG: 38 },
  sugar: { leftG: 59, targetG: 50 },
  sodium: { leftMg: 2300, targetMg: 2300 },
  steps: { count: 1109, goal: 10000 },
  caloriesBurned: 44,
  waterMl: 0,
};

function consumed(left: number, target: number) {
  return Math.min(Math.max((target - left) / target, 0), 1);
}

// ── Macro card ────────────────────────────────────────────────────────────────
type MacroCardProps = {
  amount: string;
  label: string;
  value: number;
  color: string;
  icon: React.ReactNode;
};

function MacroCard({ amount, label, value, color, icon }: MacroCardProps) {
  return (
    <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-sm p-3 flex flex-col">
      <p className="text-lg font-extrabold text-foreground leading-tight">
        {amount}
      </p>
      <p className="text-xs text-muted-foreground mt-0.5 flex-1">
        {label.split(" ")[0]}{" "}
        <span className="font-semibold text-foreground/70">
          {label.split(" ")[1]}
        </span>
      </p>
      <div className="flex justify-center mt-3">
        <Ring size={64} strokeWidth={7} value={value} color={color}>
          <span style={{ color }}>{icon}</span>
        </Ring>
      </div>
    </div>
  );
}

// ── Nutrient card (slide 2) ───────────────────────────────────────────────────
function NutrientCard({ amount, label, value, color, icon }: MacroCardProps) {
  return (
    <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-sm p-3 flex flex-col">
      <p className="text-lg font-extrabold text-foreground leading-tight">
        {amount}
      </p>
      <p className="text-xs text-muted-foreground mt-0.5 flex-1">
        {label.split(" ")[0]}{" "}
        <span className="font-semibold text-foreground/70">
          {label.split(" ")[1]}
        </span>
      </p>
      <div className="flex justify-center mt-3">
        <Ring size={64} strokeWidth={7} value={value} color={color}>
          <span style={{ color }}>{icon}</span>
        </Ring>
      </div>
    </div>
  );
}

// ── Food Log Cards ────────────────────────────────────────────────────────────

// ── HomeContent ───────────────────────────────────────────────────────────────
function HomeContent({
  onFoodClick,
  onActionClick,
}: {
  onFoodClick: (item: FoodItem) => void;
  onActionClick?: (actionId: string) => void;
}) {
  const [selectedDate, setSelectedDate] = useState(new Date());
  const [slideIndex, setSlideIndex] = useState(0);
  const [bannerDismissed, setBannerDismissed] = useState(false);
  const [foodLogs, setFoodLogs] = useState<FoodItem[]>(MOCK_FOOD_LOGS);
  const scrollRef = useRef<HTMLDivElement>(null);

  function handleScroll() {
    const el = scrollRef.current;
    if (!el) return;
    setSlideIndex(Math.round(el.scrollLeft / el.offsetWidth));
  }

  const today = new Date();
  const isToday =
    selectedDate.getFullYear() === today.getFullYear() &&
    selectedDate.getMonth() === today.getMonth() &&
    selectedDate.getDate() === today.getDate();

  // Show real stub data for today; zeros for any other date
  const d = isToday
    ? DATA
    : {
        calories: { left: 0, target: DATA.calories.target },
        protein: { leftG: 0, targetG: DATA.protein.targetG },
        carbs: { leftG: 0, targetG: DATA.carbs.targetG },
        fats: { leftG: 0, targetG: DATA.fats.targetG },
        fiber: { leftG: 0, targetG: DATA.fiber.targetG },
        sugar: { leftG: 0, targetG: DATA.sugar.targetG },
        sodium: { leftMg: 0, targetMg: DATA.sodium.targetMg },
        steps: { count: 0, goal: DATA.steps.goal },
        caloriesBurned: 0,
        waterMl: 0,
      };

  return (
    <div className="flex flex-col gap-5">
      {/* Header */}
      <Header
        title="Home"
        subtitle={selectedDate.toLocaleDateString("en-US", {
          weekday: "long",
          day: "2-digit",
          month: "long",
          year: "numeric",
        })}
        onActionClick={onActionClick}
      />

      {/* Day strip */}
      <Daylist selectedDate={selectedDate} onDaySelect={setSelectedDate} />

      {/* ── Carousel ───────────────────────────────────────────────────────── */}
      <div>
        <div
          ref={scrollRef}
          onScroll={handleScroll}
          className="flex flex-row overflow-x-auto no-scrollbar snap-x snap-mandatory gap-4"
        >
          {/* Slide 1 — Calories + Macros */}
          <div className="snap-start shrink-0 w-full flex flex-col gap-3">
            {/* Calories */}
            <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-sm p-5 flex items-center justify-between">
              <div>
                <p className="text-5xl font-extrabold text-foreground">
                  {d.calories.left}
                </p>
                <p className="text-sm text-muted-foreground mt-1">
                  Calories left
                </p>
              </div>
              <Ring
                size={110}
                strokeWidth={13}
                value={consumed(d.calories.left, d.calories.target)}
                color="var(--color-cal)"
              >
                <Flame size={28} className="text-foreground/70" />
              </Ring>
            </div>

            {/* Macro row */}
            <div className="grid grid-cols-3 gap-3">
              <MacroCard
                amount={`${d.protein.leftG}g`}
                label="Protein left"
                value={consumed(d.protein.leftG, d.protein.targetG)}
                color="var(--color-protein)"
                icon={<Beef size={16} />}
              />
              <MacroCard
                amount={`${d.carbs.leftG}g`}
                label="Carbs left"
                value={consumed(d.carbs.leftG, d.carbs.targetG)}
                color="var(--color-carbs)"
                icon={<Wheat size={16} />}
              />
              <MacroCard
                amount={`${d.fats.leftG}g`}
                label="Fat left"
                value={consumed(d.fats.leftG, d.fats.targetG)}
                color="var(--color-fats)"
                icon={<Droplets size={16} />}
              />
            </div>
          </div>

          {/* Slide 2 — Nutrients + Health Score */}
          <div className="snap-start shrink-0 w-full flex flex-col gap-3">
            <div className="grid grid-cols-3 gap-3">
              <NutrientCard
                amount={`${d.fiber.leftG}g`}
                label="Fiber left"
                value={consumed(d.fiber.leftG, d.fiber.targetG)}
                color="var(--color-fiber)"
                icon={<Wheat size={16} />}
              />
              <NutrientCard
                amount={`${d.sugar.leftG}g`}
                label="Sugar left"
                value={consumed(d.sugar.leftG, d.sugar.targetG)}
                color="var(--color-sugar)"
                icon={<Droplets size={16} />}
              />
              <NutrientCard
                amount={`${d.sodium.leftMg}mg`}
                label="Sodium left"
                value={consumed(d.sodium.leftMg, d.sodium.targetMg)}
                color="var(--color-sodium)"
                icon={<Droplets size={16} />}
              />
            </div>

            {/* Health Score */}
            <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-sm p-4 flex items-start justify-between gap-4">
              <div className="flex flex-col gap-1.5 flex-1">
                <p className="text-base font-semibold text-foreground">
                  Health Score
                </p>
                <p className="text-xs text-muted-foreground leading-relaxed">
                  Track a few foods to generate your health score for today.
                  Your score reflects nutritional content and how processed your
                  meals are.
                </p>
              </div>
              <div className="flex items-center gap-1 shrink-0">
                <Heart size={16} className="text-muted-foreground" />
                <p className="text-base font-bold text-foreground">N/A</p>
              </div>
            </div>
          </div>

          {/* Slide 3 — Steps + Water */}
          <div className="snap-start shrink-0 w-full flex flex-col gap-3">
            <div className="grid grid-cols-2 gap-3">
              {/* Steps */}
              <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-sm p-4 flex flex-col gap-2">
                <p className="text-sm font-semibold text-foreground">Steps</p>
                <p className="text-xs text-muted-foreground">
                  {d.steps.count.toLocaleString()}/
                  {d.steps.goal.toLocaleString()}
                </p>
                <div className="flex justify-center mt-1">
                  <Ring
                    size={80}
                    strokeWidth={10}
                    value={Math.min(d.steps.count / d.steps.goal, 1)}
                    color="var(--color-steps)"
                  >
                    <Footprints size={20} className="text-muted-foreground" />
                  </Ring>
                </div>
              </div>

              {/* Calories burned */}
              <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-sm p-4 flex flex-col">
                <p className="text-sm font-semibold text-foreground">
                  Calories burned
                </p>
                <p className="text-3xl font-extrabold text-foreground mt-2">
                  {d.caloriesBurned}
                </p>
                <p className="text-xs text-muted-foreground">cal</p>
                <div className="flex items-center gap-1 mt-auto pt-3 text-muted-foreground">
                  <Footprints size={13} />
                  <p className="text-xs">Steps {d.caloriesBurned} cal</p>
                </div>
              </div>
            </div>

            {/* Water */}
            <div className="bg-white dark:bg-slate-800 rounded-2xl shadow-sm p-4 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div
                  className="size-10 rounded-full flex items-center justify-center"
                  style={{
                    backgroundColor:
                      "color-mix(in srgb, var(--color-water) 15%, white)",
                  }}
                >
                  <Droplets size={20} style={{ color: "var(--color-water)" }} />
                </div>
                <div>
                  <p className="text-sm font-semibold text-foreground">Water</p>
                  <p className="text-xs text-muted-foreground">
                    {d.waterMl} ml
                  </p>
                </div>
              </div>
              <Drawer>
                <DrawerTrigger asChild>
                  <button className="bg-foreground text-background text-sm font-semibold px-4 py-2 rounded-xl">
                    Log Water
                  </button>
                </DrawerTrigger>
                <DrawerContent className="rounded-t-3xl border-none">
                  <DrawerHeader className="text-center">
                    <DrawerTitle>Log Water Intake</DrawerTitle>
                  </DrawerHeader>
                  <div className="px-8 pb-10 flex flex-col items-center gap-8">
                    <div className="flex items-center justify-center size-32 rounded-full bg-blue-500/10">
                      <Droplets size={48} className="text-blue-500" />
                    </div>
                    <div className="grid grid-cols-3 gap-4 w-full">
                      {[250, 500, 750].map((ml) => (
                        <button
                          key={ml}
                          className="p-4 rounded-2xl bg-muted hover:bg-muted/80 transition-colors font-bold text-lg"
                        >
                          +{ml}ml
                        </button>
                      ))}
                    </div>
                    <Button className="w-full h-14 rounded-2xl text-lg font-bold">
                      Save Record
                    </Button>
                  </div>
                </DrawerContent>
              </Drawer>
            </div>
          </div>
        </div>

        {/* Dot indicators */}
        <div className="flex justify-center gap-1.5 mt-4">
          {[0, 1, 2].map((i) => (
            <div
              key={i}
              className={cn(
                "size-1.5 rounded-full transition-all duration-200",
                i === slideIndex
                  ? "bg-black"
                  : "border border-muted-foreground/30 bg-transparent",
              )}
            />
          ))}
        </div>
      </div>

      {/* ── Recently Uploaded ─────────────────────────────────────────────── */}
      <section className="flex flex-col gap-3 pb-4">
        <h2 className="text-xl font-bold text-foreground">Recently uploaded</h2>

        {/* Notification banner */}
        {!bannerDismissed && (
          <div className="bg-surface-muted dark:bg-slate-800/60 rounded-2xl p-4 flex items-start gap-3">
            <Bell size={18} className="text-muted-foreground shrink-0 mt-0.5" />
            <p className="text-sm text-muted-foreground flex-1 leading-relaxed">
              You can switch apps or turn off your phone. We'll notify you when
              the analysis is done.
            </p>
            <button
              onClick={() => setBannerDismissed(true)}
              aria-label="Dismiss"
            >
              <X size={16} className="text-muted-foreground" />
            </button>
          </div>
        )}

        {/* List or Empty state */}
        {foodLogs.length === 0 ? (
          <div className="bg-white dark:bg-slate-800 rounded-3xl shadow-sm p-8 flex flex-col items-center gap-2">
            <p className="text-sm text-muted-foreground text-center">
              No foods logged today.
            </p>
            <p className="text-xs text-muted-foreground/60 text-center">
              Tap + to add a meal.
            </p>
          </div>
        ) : (
          <div className="flex flex-col gap-3">
            {foodLogs.map((item) => (
              <FoodCard
                key={item.id}
                item={item}
                onClick={() => onFoodClick(item)}
              />
            ))}
          </div>
        )}
      </section>
    </div>
  );
}

// ── HomeScreen ────────────────────────────────────────────────────────────────
export default function HomeScreen() {
  const [activeTab, setActiveTab] = useState("home");
  const [showScanner, setShowScanner] = useState(false);
  const [showAccountDetails, setShowAccountDetails] = useState(false);
  const [selectedFood, setSelectedFood] = useState<FoodItem | null>(null);
  const [showLogFood, setShowLogFood] = useState(false);

  function handleMenuItemClick(id: string) {
    if (id === "scan_food") setShowScanner(true);
    else if (id === "saved_foods") setShowLogFood(true);
    else console.log("fab item:", id);
  }

  return (
    <div className="min-h-screen p-4 pb-24 md:pb-4 bg-app-bg transition-colors">
      <div className="max-w-4xl mx-auto">
        <div className="mb-8">
          <TabBar
            tabs={NAV_TABS}
            activeTab={activeTab}
            onTabChange={setActiveTab}
            onSidebarToggle={() => console.log("sidebar toggled")}
            onSearch={() => console.log("search clicked")}
            onMenuItemClick={handleMenuItemClick}
          />
        </div>

        {activeTab === "home" && (
          <HomeContent 
            onFoodClick={setSelectedFood} 
            onActionClick={(id) => {
              if (id === "account-details") setShowAccountDetails(true);
            }} 
          />
        )}
        {activeTab === "progress" && <ProgressScreen />}
        {activeTab === "settings" && <SettingsScreen />}
      </div>

      {showScanner && (
        <ScannerScreen
          onClose={() => setShowScanner(false)}
          onCapture={(mode, imageData) => {
            setShowScanner(false);
            console.log("captured", mode, imageData.slice(0, 40));
          }}
        />
      )}

      {selectedFood && (
        <FoodDetailScreen
          item={selectedFood}
          onClose={() => setSelectedFood(null)}
        />
      )}

      {showAccountDetails && (
        <AccountDetailsScreen onClose={() => setShowAccountDetails(false)} />
      )}

      {showLogFood && (
        <LogFood onClose={() => setShowLogFood(false)} />
      )}
    </div>
  );
}
