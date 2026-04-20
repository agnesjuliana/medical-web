import { useEffect, useState } from "react";
import { Avatar, AvatarFallback, AvatarImage } from "../ui/avatar";
import {
  Sheet,
  SheetClose,
  SheetContent,
  SheetTitle,
  SheetTrigger,
} from "../ui/sheet";
import { X, ChevronRight } from "lucide-react";
import { getUserInfo, logout, deleteAccount, toast } from "../../services/api";

declare global {
  interface Window {
    __USER__?: { id: number; name: string; email: string; initials?: string };
  }
}

function computeInitials(name: string): string {
  const parts = name.trim().split(/\s+/);
  if (parts.length >= 2) return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  return (parts[0]?.[0] ?? '?').toUpperCase();
}

interface HeaderProps {
  title: string;
  subtitle: string;
  onActionClick?: (actionId: string) => void;
}

const MENU_GROUPS = [
  {
    id: "account-group",
    title: "Account",
    items: [{ id: "account-details", label: "Account Details" }],
  },
  {
    id: "actions-group",
    title: "Actions",
    items: [
      { id: "logout", label: "Logout" },
      { id: "delete-account", label: "Delete Account" },
    ],
  },
];

export default function Header({
  title,
  subtitle,
  onActionClick,
}: HeaderProps) {
  const bootstrapUser = window.__USER__;
  const [initials, setInitials] = useState<string>(() => {
    if (bootstrapUser?.initials) return bootstrapUser.initials;
    if (bootstrapUser?.name) return computeInitials(bootstrapUser.name);
    return '?';
  });

  useEffect(() => {
    if (bootstrapUser?.initials || bootstrapUser?.name) return;
    getUserInfo()
      .then((res) => setInitials(res.data.initials))
      .catch((err) => console.error("Failed to load user info", err));
  }, []);

  const handleAction = async (actionId: string) => {
    if (actionId === "logout") {
      try {
        await logout();
        window.location.href = "/auth/login.php";
      } catch (err: any) {
        toast.error(err.message || "Failed to logout");
      }
      return;
    }

    if (actionId === "delete-account") {
      if (!confirm("Are you sure you want to delete your account? This action cannot be undone.")) return;
      try {
        await deleteAccount();
        window.location.href = "/auth/login.php";
      } catch (err: any) {
        toast.error(err.message || "Failed to delete account");
      }
      return;
    }

    onActionClick?.(actionId);
  };

  return (
    <div className="mb-2.5 flex flex-row justify-between items-center">
      <div className="text-left">
        <h1 className="text-2xl font-bold text-foreground mb-0.5">{title}</h1>
        <p className="text-xs text-muted-foreground">{subtitle}</p>
      </div>

      <Sheet>
        <SheetTrigger asChild>
          <Avatar size="lg" className="cursor-pointer">
            <AvatarImage />
            <AvatarFallback>{initials}</AvatarFallback>
          </Avatar>
        </SheetTrigger>
        <SheetContent
          side="bottom"
          showCloseButton={false}
          className="border-none bg-app-bg shadow-none rounded-t-3xl pt-2 pb-8 px-0"
        >
          {/* Header area with Title and Close Button */}
          <div className="relative flex items-center justify-center pt-2 pb-6 px-4">
            <SheetTitle className="text-lg font-bold">Account</SheetTitle>
            <SheetClose className="absolute right-4 top-0 rounded-full bg-muted p-2 text-foreground hover:bg-muted/80 transition-colors">
              <X size={20} strokeWidth={2.5} />
            </SheetClose>
          </div>

          <div className="flex flex-col items-center pb-8 px-4">
            <Avatar className="h-[96px] w-[96px]">
              <AvatarImage src="/avatar-placeholder.png" />
              <AvatarFallback className="text-3xl font-medium bg-blue-500 text-white">
                {initials}
              </AvatarFallback>
            </Avatar>
          </div>

          <div className="px-4 space-y-6">
            {MENU_GROUPS.map((group) => (
              <div key={group.id} className="space-y-2">
                <h3 className="text-[15px] font-medium text-muted-foreground px-2">
                  {group.title}
                </h3>
                <div className="rounded-[16px] bg-gray-200 overflow-hidden">
                  {group.items.map((item, index) => (
                    <div key={item.id}>
                      <SheetClose asChild>
                        <button
                          onClick={() => handleAction(item.id)}
                          className="w-full flex items-center justify-between px-4 py-4 bg-transparent transition-colors"
                        >
                          <span className="text-base text-foreground">
                            {item.label}
                          </span>
                          <ChevronRight
                            size={20}
                            className="text-muted-foreground/40"
                            strokeWidth={2.5}
                          />
                        </button>
                      </SheetClose>
                      {index < group.items.length - 1 && (
                        <div className="h-[1px] bg-border/40 ml-4" />
                      )}
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </SheetContent>
      </Sheet>
    </div>
  );
}
