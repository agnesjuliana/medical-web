import { useEffect, useState } from "react";
import TabBar from "@/components/navigation/tabBar";
import Header from "@/components/header/Header";
import type { TabItem } from "@/components/navigation/tabBar";
import { Home, User, Settings, Bell } from "lucide-react";
import {
  Button,
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  Input,
  Label,
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "./components/ui";
import "./App.css";

interface User {
  id: number;
  name: string;
  email: string;
}

const NAV_TABS: TabItem[] = [
  { id: "home", label: "Home", icon: <Home size={20} /> },
  { id: "profile", label: "Profile", icon: <User size={20} /> },
  { id: "settings", label: "Settings", icon: <Settings size={20} /> },
  { id: "alerts", label: "Alerts", icon: <Bell size={20} /> },
];

function App() {
  const [user, setUser] = useState<User | null>(null);
  const [count, setCount] = useState(0);
  const [message, setMessage] = useState("");
  const [activeTab, setActiveTab] = useState("home");

  useEffect(() => {
    // Get user data passed from PHP
    const userData = (window as any).__USER__ as User | null;
    setUser(userData);
  }, []);

  return (
    <div className="min-h-screen p-4 pb-24 md:pb-4">
      <div className="max-w-4xl mx-auto">
        {/* TabBar — fixed bottom on mobile, top-centered on desktop */}
        <div className="mb-8">
          <TabBar
            tabs={NAV_TABS}
            activeTab={activeTab}
            onTabChange={setActiveTab}
            onSidebarToggle={() => console.log("sidebar toggled")}
            onSearch={() => console.log("search clicked")}
          />
        </div>

        {/* Header */}
        <Header
          title="Modul 8"
          subtitle="React + Vite + TypeScript with shadcn/ui"
        />

        {/* Grid Layout */}
        <div className="grid gap-6">
          {/* User Info Card */}
          {user && (
            <Card className="border-slate-700 bg-slate-800">
              <CardHeader>
                <CardTitle className="text-white">
                  Welcome, {user.name}!
                </CardTitle>
                <CardDescription>Your account information</CardDescription>
              </CardHeader>
              <CardContent className="space-y-2">
                <p className="text-slate-300">
                  <span className="font-medium">ID:</span> {user.id}
                </p>
                <p className="text-slate-300">
                  <span className="font-medium">Email:</span> {user.email}
                </p>
              </CardContent>
            </Card>
          )}

          {/* shadcn/ui Components Demo */}
          <Card className="border-slate-700 bg-slate-800">
            <CardHeader>
              <CardTitle className="text-white">shadcn/ui Components</CardTitle>
              <CardDescription>Interactive component examples</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              {/* Buttons */}
              <div className="space-y-2">
                <Label className="text-white text-base font-semibold">
                  Button Variants
                </Label>
                <div className="flex gap-2 flex-wrap">
                  <Button variant="default">Default</Button>
                  <Button variant="secondary">Secondary</Button>
                  <Button variant="outline">Outline</Button>
                  <Button variant="ghost">Ghost</Button>
                  <Button variant="destructive">Destructive</Button>
                </div>
              </div>

              {/* Input */}
              <div className="space-y-2">
                <Label htmlFor="message" className="text-white">
                  Message
                </Label>
                <Input
                  id="message"
                  placeholder="Type a message..."
                  value={message}
                  onChange={(e) => setMessage(e.target.value)}
                  className="bg-slate-700 border-slate-600"
                />
              </div>

              {/* Counter with Button */}
              <div className="space-y-2">
                <Label className="text-white text-base font-semibold">
                  Counter
                </Label>
                <Button
                  onClick={() => setCount((c) => c + 1)}
                  className="w-full"
                >
                  Count: {count}
                </Button>
              </div>

              {/* Dialog Example */}
              <Dialog>
                <DialogTrigger asChild>
                  <Button variant="outline" className="w-full">
                    Open Dialog
                  </Button>
                </DialogTrigger>
                <DialogContent className="bg-slate-800 border-slate-700">
                  <DialogHeader>
                    <DialogTitle className="text-white">
                      Dialog Example
                    </DialogTitle>
                    <DialogDescription>
                      This is a shadcn/ui dialog component
                    </DialogDescription>
                  </DialogHeader>
                  <p className="text-slate-300">
                    This dialog demonstrates the shadcn/ui Dialog component with
                    Radix UI.
                  </p>
                </DialogContent>
              </Dialog>

              {/* Dropdown Menu */}
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="outline" className="w-full">
                    Open Menu
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent className="bg-slate-800 border-slate-700">
                  <DropdownMenuItem className="text-slate-200 focus:bg-slate-700">
                    Profile
                  </DropdownMenuItem>
                  <DropdownMenuItem className="text-slate-200 focus:bg-slate-700">
                    Settings
                  </DropdownMenuItem>
                  <DropdownMenuItem className="text-slate-200 focus:bg-slate-700">
                    Logout
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </CardContent>
          </Card>

          {/* Info Card */}
          <Card className="border-blue-700 bg-blue-900/20">
            <CardHeader>
              <CardTitle className="text-blue-300">
                shadcn/ui Setup Complete
              </CardTitle>
            </CardHeader>
            <CardContent className="text-blue-200 space-y-2 text-sm">
              <p>✓ Installed Radix UI and dependencies</p>
              <p>✓ Created utility functions and components</p>
              <p>✓ Button, Card, Input, Label, Dialog, DropdownMenu</p>
              <p>✓ Full TypeScript support with Tailwind CSS</p>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}

export default App;
