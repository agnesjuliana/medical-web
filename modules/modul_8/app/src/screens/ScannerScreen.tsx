import { useEffect, useRef, useState } from "react";
import {
  X, Info, Camera, Zap, ScanLine,
  Apple, ScanBarcode, Tag, ImageIcon,
} from "lucide-react";
import { cn } from "@/lib/utils";

type ScanMode = "scan_food" | "barcode" | "food_label" | "gallery";

interface ScannerScreenProps {
  onClose: () => void;
  onCapture?: (mode: ScanMode, imageData: string) => void;
}

const SCAN_MODES: { id: ScanMode; label: string; Icon: React.ComponentType<{ size?: number; className?: string }> }[] = [
  { id: "scan_food",  label: "Scan Food",  Icon: Apple },
  { id: "barcode",    label: "Barcode",    Icon: ScanBarcode },
  { id: "food_label", label: "Food label", Icon: Tag },
  { id: "gallery",    label: "Gallery",    Icon: ImageIcon },
];

function haptic(ms = 50) {
  if ("vibrate" in navigator) navigator.vibrate(ms);
}

export default function ScannerScreen({ onClose, onCapture }: ScannerScreenProps) {
  const videoRef    = useRef<HTMLVideoElement>(null);
  const streamRef   = useRef<MediaStream | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const [cameraError, setCameraError] = useState<string | null>(null);
  const [scanMode,    setScanMode]    = useState<ScanMode>("scan_food");
  const [flashOn,     setFlashOn]     = useState(false);

  // ── Camera stream ──────────────────────────────────────────────────────────
  useEffect(() => {
    let cancelled = false;

    async function startCamera() {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: "environment", width: { ideal: 1920 }, height: { ideal: 1080 } },
          audio: false,
        });
        if (cancelled) { stream.getTracks().forEach(t => t.stop()); return; }
        streamRef.current = stream;
        if (videoRef.current) videoRef.current.srcObject = stream;
      } catch {
        if (!cancelled) setCameraError("Camera access denied. Please allow camera permission.");
      }
    }

    startCamera();
    return () => {
      cancelled = true;
      streamRef.current?.getTracks().forEach(t => t.stop());
    };
  }, []);

  // ── Viewport lock (prevent pull-to-refresh / overscroll) ──────────────────
  useEffect(() => {
    const prevent = (e: TouchEvent) => e.preventDefault();
    document.body.style.overflow = "hidden";
    document.addEventListener("touchmove", prevent, { passive: false });
    return () => {
      document.body.style.overflow = "";
      document.removeEventListener("touchmove", prevent);
    };
  }, []);

  // ── Screen wake lock ───────────────────────────────────────────────────────
  useEffect(() => {
    let wakeLock: WakeLockSentinel | null = null;
    async function requestWakeLock() {
      try { wakeLock = await navigator.wakeLock.request("screen"); } catch { /* unsupported */ }
    }
    requestWakeLock();
    return () => { wakeLock?.release(); };
  }, []);

  // ── Orientation lock ───────────────────────────────────────────────────────
  useEffect(() => {
    screen.orientation?.lock?.("portrait").catch(() => {});
    return () => { screen.orientation?.unlock?.(); };
  }, []);

  // ── Capture ────────────────────────────────────────────────────────────────
  function captureFrame(): string | null {
    const video = videoRef.current;
    if (!video) return null;
    const canvas = document.createElement("canvas");
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext("2d")?.drawImage(video, 0, 0);
    return canvas.toDataURL("image/jpeg", 0.85);
  }

  function handleShutter() {
    haptic();
    if (scanMode === "gallery") {
      fileInputRef.current?.click();
      return;
    }
    const imageData = captureFrame();
    if (imageData) onCapture?.(scanMode, imageData);
  }

  // ── Flash ──────────────────────────────────────────────────────────────────
  function applyTorch(on: boolean) {
    const track = streamRef.current?.getVideoTracks()[0];
    if (!track) return;
    const caps = track.getCapabilities() as MediaTrackCapabilities & { torch?: boolean };
    if (caps.torch) track.applyConstraints({ advanced: [{ torch: on } as MediaTrackConstraintSet] });
  }

  function toggleFlash() {
    const next = !flashOn;
    setFlashOn(next);
    applyTorch(next);
  }

  // ── Gallery file handler ───────────────────────────────────────────────────
  function handleFileChange(e: React.ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => onCapture?.("gallery", reader.result as string);
    reader.readAsDataURL(file);
  }

  return (
    <div
      className="fixed inset-0 z-[60] bg-black flex flex-col animate-in fade-in slide-in-from-bottom-4 duration-300"
      style={{ touchAction: "none" }}
    >
      {/* Live camera feed */}
      <video
        ref={videoRef}
        autoPlay
        playsInline
        muted
        className="absolute inset-0 w-full h-full object-cover"
      />

      {/* Error fallback */}
      {cameraError && (
        <div className="absolute inset-0 flex flex-col items-center justify-center gap-4 px-8 z-20">
          <Camera size={48} className="text-white/40" />
          <p className="text-white/60 text-center text-sm">{cameraError}</p>
          <button
            onClick={() => window.location.reload()}
            className="text-blue-400 font-semibold"
          >
            Try Again
          </button>
        </div>
      )}

      {/* Top action bar */}
      <div
        className="absolute top-0 left-0 right-0 px-4 py-3 flex justify-between items-center z-10"
        style={{ paddingTop: "max(12px, env(safe-area-inset-top))" }}
      >
        <button
          onClick={onClose}
          aria-label="Close scanner"
          className="w-10 h-10 rounded-full bg-black/40 backdrop-blur-md flex items-center justify-center active:scale-90 transition-transform"
        >
          <X size={20} className="text-white" />
        </button>
        <button
          aria-label="Scanner info"
          className="w-10 h-10 rounded-full bg-black/40 backdrop-blur-md flex items-center justify-center active:scale-90 transition-transform"
        >
          <Info size={20} className="text-white" />
        </button>
      </div>

      {/* Radial vignette */}
      <div
        className="absolute inset-0 pointer-events-none z-[5]"
        style={{ background: "radial-gradient(ellipse at center, transparent 35%, rgba(0,0,0,0.35) 100%)" }}
      />

      {/* Viewfinder brackets — centered in the actual visible camera area (below top bar, above bottom controls) */}
      <div
        role="img"
        aria-label="Camera viewfinder"
        className="absolute inset-x-0 flex items-center justify-center pointer-events-none z-6"
        style={{ top: '56px', bottom: '260px' }}
      >
        <div className="relative" style={{ width: 'min(72vw, 42vh)', height: 'min(72vw, 42vh)' }}>
          <span className="absolute top-0 left-0 w-10 h-10 border-t-[3px] border-l-[3px] border-white rounded-tl-lg [animation:bracket-pulse_2s_ease-in-out_infinite]" />
          <span className="absolute top-0 right-0 w-10 h-10 border-t-[3px] border-r-[3px] border-white rounded-tr-lg [animation:bracket-pulse_2s_ease-in-out_infinite]" />
          <span className="absolute bottom-0 left-0 w-10 h-10 border-b-[3px] border-l-[3px] border-white rounded-bl-lg [animation:bracket-pulse_2s_ease-in-out_infinite]" />
          <span className="absolute bottom-0 right-0 w-10 h-10 border-b-[3px] border-r-[3px] border-white rounded-br-lg [animation:bracket-pulse_2s_ease-in-out_infinite]" />
        </div>
      </div>

      {/* Scan mode selector */}
      <div className="absolute left-0 right-0 px-4 z-10" style={{ bottom: "164px" }}>
        <div className="flex flex-row gap-2 justify-center">
          {SCAN_MODES.map(({ id, label, Icon }) => (
            <button
              key={id}
              aria-pressed={scanMode === id}
              onClick={() => { setScanMode(id); haptic(25); }}
              className={cn(
                "flex flex-col items-center justify-center gap-1.5 bg-white rounded-2xl px-3 py-3 min-w-[76px] shadow-sm active:scale-95 transition-all duration-200",
                scanMode === id && "ring-2 ring-blue-400 bg-blue-50",
              )}
            >
              <Icon
                size={24}
                className={cn("text-slate-700", scanMode === id && "text-blue-500")}
              />
              <span className={cn("text-[11px] font-medium text-slate-700", scanMode === id && "text-blue-600")}>
                {label}
              </span>
            </button>
          ))}
        </div>
      </div>

      {/* Bottom controls */}
      <div
        className="absolute bottom-0 left-0 right-0 px-6 flex items-center justify-between z-10"
        style={{ paddingBottom: "max(24px, env(safe-area-inset-bottom))", paddingTop: "24px" }}
      >
        {/* Flash toggle */}
        <button
          onClick={toggleFlash}
          aria-label="Toggle flash"
          className="w-12 h-12 rounded-full bg-black/30 backdrop-blur flex items-center justify-center active:scale-90 transition-transform"
        >
          <Zap
            size={22}
            className={cn(flashOn ? "text-yellow-400 fill-yellow-400" : "text-white")}
          />
        </button>

        {/* Shutter button */}
        <div className="flex flex-col items-center gap-3">
          <button
            onClick={handleShutter}
            aria-label="Capture photo"
            className="w-[72px] h-[72px] rounded-full border-[4px] border-white flex items-center justify-center active:scale-95 transition-transform"
          >
            <div className="w-[58px] h-[58px] rounded-full bg-white" />
          </button>
          <ScanLine size={24} className="text-white/60" />
        </div>

        {/* Right spacer */}
        <div className="w-12" />
      </div>

      {/* Hidden gallery input */}
      <input
        ref={fileInputRef}
        type="file"
        accept="image/*"
        capture="environment"
        className="hidden"
        onChange={handleFileChange}
      />
    </div>
  );
}
