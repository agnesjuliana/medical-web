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
import { useEffect, useRef, useState } from "react";
import { getDashboard, type DashboardData } from "../services/api";
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

// ── Food Log Types ───────────────────────────────────────────────────────────
import FoodCard, { type FoodItem } from "@/components/template/FoodCard";

// ── Stub data ────────────────────────────────────────────────────────────────

function consumed(left: number, target: number) {
  if (!target || target === 0) return 0;
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
  const [dashboardData, setDashboardData] = useState<DashboardData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const scrollRef = useRef<HTMLDivElement>(null);

  function handleScroll() {
    const el = scrollRef.current;
    if (!el) return;
    setSlideIndex(Math.round(el.scrollLeft / el.offsetWidth));
  }

  useEffect(() => {
    const dateStr = selectedDate.toISOString().split("T")[0];
    setIsLoading(true);
    setError(null);
    getDashboard(dateStr)
      .then((res) => {
        setDashboardData(res.data);
      })
      .catch((err) => {
        console.error(err);
        setError("Failed to load dashboard data");
      })
      .finally(() => {
        setIsLoading(false);
      });
  }, [selectedDate]);

  const d = dashboardData
    ? {
        calories: {
          left: dashboardData.remaining.calories,
          target: dashboardData.targets.calories,
        },
        protein: {
          leftG: dashboardData.remaining.protein_g,
          targetG: dashboardData.targets.protein_g,
        },
        carbs: {
          leftG: dashboardData.remaining.carbs_g,
          targetG: dashboardData.targets.carbs_g,
        },
        fats: {
          leftG: dashboardData.remaining.fats_g,
          targetG: dashboardData.targets.fats_g,
        },
        fiber: {
          leftG: dashboardData.consumed.fiber_g,
          targetG: 38, // API doesn't provide fiber target yet
        },
        sugar: { leftG: 0, targetG: 50 },
        sodium: { leftMg: 0, targetMg: 2300 },
        steps: { count: 0, goal: 10000 },
        caloriesBurned: 0,
        waterMl: dashboardData.consumed.water_ml,
        healthScore: dashboardData.health_score,
      }
    : {
        calories: { left: 0, target: 1 },
        protein: { leftG: 0, targetG: 1 },
        carbs: { leftG: 0, targetG: 1 },
        fats: { leftG: 0, targetG: 1 },
        fiber: { leftG: 0, targetG: 1 },
        sugar: { leftG: 0, targetG: 1 },
        sodium: { leftMg: 0, targetMg: 1 },
        steps: { count: 0, goal: 10000 },
        caloriesBurned: 0,
        waterMl: 0,
        healthScore: null,
      };

  const foodLogs: FoodItem[] = (dashboardData?.recent_meals || []).map((m) => ({
    id: String(m.id),
    status: m.status || "analyzed",
    name: m.name,
    time: new Date(m.created_at).toLocaleTimeString([], {
      hour: "2-digit",
      minute: "2-digit",
    }),
    calories: m.calories,
    protein: m.protein_g,
    carbs: m.carbs_g,
    fats: m.fats_g,
    imageUrl: m.photo_url || undefined,
    progress: m.progress,
  }));

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
                <p className="text-base font-bold text-foreground">
                  {d.healthScore || "N/A"}
                </p>
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
        {isLoading ? (
          <div className="py-12 flex justify-center">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
          </div>
        ) : error ? (
          <div className="bg-destructive/10 text-destructive rounded-2xl p-6 text-center text-sm">
            {error}
          </div>
        ) : foodLogs.length === 0 ? (
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
          onCapture={async (mode, imageData) => {
            setShowScanner(false);
            const { scanFood, toast } = await import("../services/api");
            const loadingId = toast.loading("Analyzing food with AI...");
            try {
              const res = await scanFood(imageData);
              toast.dismiss(loadingId);
              toast.success("Analysis complete!");
              // Map backend result to FoodItem
              setSelectedFood({
                id: "scanned_" + Date.now(),
                status: "analyzed",
                name: res.data.name || "Unknown Food",
                calories: res.data.calories || 0,
                protein: res.data.protein_g || 0,
                carbs: res.data.carbs_g || 0,
                fats: res.data.fats_g || 0,
                imageUrl: imageData, // Use captured image for display
              });
            } catch (err: any) {
              toast.dismiss(loadingId);
              toast.error(err.message || "Failed to analyze food");
            }
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
