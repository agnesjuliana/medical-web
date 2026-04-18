import { useState, useEffect, useRef } from 'react';
import { X, Camera, Scan as ScanIcon, Zap, AlertCircle, RefreshCw, Plus } from 'lucide-react';
import { Html5QrcodeScanner } from 'html5-qrcode';
import type { FoodEntry } from '../../types/food';

interface ScanFoodModalProps {
  isOpen: boolean;
  onClose: () => void;
  onAdd: (entry: FoodEntry) => void;
}

export default function ScanFoodModal({ isOpen, onClose, onAdd }: ScanFoodModalProps) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [scannedProduct, setScannedProduct] = useState<any>(null);
  const scannerRef = useRef<Html5QrcodeScanner | null>(null);

  useEffect(() => {
    if (isOpen && !scannerRef.current) {
      // Small delay to ensure the container is rendered
      const timeout = setTimeout(() => {
        const scanner = new Html5QrcodeScanner(
          "reader",
          { fps: 10, qrbox: { width: 250, height: 150 } },
          /* verbose= */ false
        );
        
        scanner.render(onScanSuccess, onScanFailure);
        scannerRef.current = scanner;
      }, 300);

      return () => {
        clearTimeout(timeout);
        if (scannerRef.current) {
          scannerRef.current.clear().catch(err => console.error("Failed to clear scanner", err));
          scannerRef.current = null;
        }
      };
    }
  }, [isOpen]);

  async function onScanSuccess(decodedText: string) {
    if (loading) return;
    setLoading(true);
    setError(null);
    
    // Stop scanner after success to show result
    if (scannerRef.current) {
      try {
        await scannerRef.current.pause(true);
      } catch (e) {
        console.error(e);
      }
    }

    try {
      const resp = await fetch(`https://world.openfoodfacts.org/api/v0/product/${decodedText}.json`);
      const data = await resp.json();

      if (data.status === 1) {
        setScannedProduct(data.product);
      } else {
        setError("Produk tidak ditemukan di database OpenFoodFacts.");
        if (scannerRef.current) scannerRef.current.resume();
      }
    } catch (err) {
      setError("Gagal terhubung ke server. Periksa koneksi internet Anda.");
      if (scannerRef.current) scannerRef.current.resume();
    } finally {
      setLoading(false);
    }
  }

  function onScanFailure(error: any) {
    // We ignore failures as they occur frequently while searching
  }

  const handleAdd = () => {
    if (!scannedProduct) return;
    
    const nuts = scannedProduct.nutriments;
    const calories = Math.round(nuts['energy-kcal_100g'] || (nuts['energy_100g'] / 4.184) || 0);
    
    onAdd({
      id: Date.now().toString(),
      name: scannedProduct.product_name || "Produk Tanpa Nama",
      calories: calories,
      protein: Math.round(nuts.proteins_100g || 0),
      carbs: Math.round(nuts.carbohydrates_100g || 0),
      fat: Math.round(nuts.fat_100g || 0),
      fiber: Math.round(nuts.fiber_100g || 0),
      sugar: Math.round(nuts.sugars_100g || 0),
      sodium: Math.round(nuts.sodium_100g * 1000 || 0), // OFF is usually in grams
      emoji: "📦",
      timestamp: new Date(),
    });
    
    onClose();
  };

  const handleRetry = () => {
    setScannedProduct(null);
    setError(null);
    if (scannerRef.current) {
      scannerRef.current.resume();
    }
  };

  if (!isOpen) return null;

  return (
    <>
      <div className="fixed inset-0 bg-black/80 backdrop-blur-md z-[60]" onClick={onClose} />
      
      <div className="fixed inset-x-0 bottom-0 z-[70] animate-in slide-in-from-bottom duration-300">
        <div className="bg-base border-t border-white/10 rounded-t-[28px] max-h-[90vh] flex flex-col">
          {/* Handle */}
          <div className="flex justify-center pt-3 pb-1">
            <div className="w-10 h-1 rounded-full bg-white/20" />
          </div>

          {/* Header */}
          <div className="flex items-center justify-between px-6 py-3">
            <h2 className="text-xl font-bold text-text-main flex items-center gap-2">
              <ScanIcon className="w-5 h-5 text-primary" />
              Scan Barcode
            </h2>
            <button onClick={onClose} className="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center">
              <X className="w-4 h-4 text-text-muted" />
            </button>
          </div>

          <div className="px-6 pb-10 flex-1 overflow-y-auto">
            {!scannedProduct && !error && (
              <div className="space-y-6 flex flex-col items-center">
                <div className="w-full aspect-square max-w-[320px] bg-black/40 rounded-3xl border-2 border-dashed border-white/20 overflow-hidden relative">
                   <div id="reader" className="w-full h-full" />
                   {loading && (
                     <div className="absolute inset-0 bg-black/60 flex items-center justify-center">
                       <RefreshCw className="w-10 h-10 text-primary animate-spin" />
                     </div>
                   )}
                </div>
                <p className="text-sm text-text-muted text-center px-4">
                  Arahkan kamera ke barcode pada kemasan makanan. Pastikan pencahayaan cukup.
                </p>
              </div>
            )}

            {error && (
              <div className="py-12 flex flex-col items-center text-center space-y-4">
                <div className="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center">
                  <AlertCircle className="w-8 h-8 text-red-400" />
                </div>
                <h3 className="text-text-main font-bold">Terjadi Kesalahan</h3>
                <p className="text-sm text-text-muted px-8">{error}</p>
                <button 
                  onClick={handleRetry}
                  className="px-6 py-2 bg-white/10 rounded-full text-sm font-bold text-text-main"
                >
                  Coba Lagi
                </button>
              </div>
            )}

            {scannedProduct && (
              <div className="animate-in fade-in zoom-in duration-300 space-y-6">
                <div className="bg-white/5 rounded-3xl border border-white/10 p-5 flex items-start gap-4">
                  <div className="w-20 h-20 bg-white/10 rounded-2xl overflow-hidden flex-shrink-0">
                    {scannedProduct.image_url ? (
                      <img src={scannedProduct.image_url} alt={scannedProduct.product_name} className="w-full h-full object-cover" />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center text-3xl">📦</div>
                    )}
                  </div>
                  <div className="flex-1 min-w-0">
                    <h3 className="text-lg font-bold text-text-main truncate">{scannedProduct.product_name}</h3>
                    <p className="text-sm text-text-muted truncate">{scannedProduct.brands}</p>
                    <div className="mt-2 text-xs font-bold text-primary bg-primary/10 inline-block px-2 py-1 rounded-md">
                      {Math.round(scannedProduct.nutriments['energy-kcal_100g'] || 0)} kkal / 100g
                    </div>
                  </div>
                </div>

                <div className="grid grid-cols-3 gap-3">
                  <div className="bg-white/5 rounded-2xl p-4 border border-white/10 text-center">
                    <div className="text-xs text-text-muted mb-1">Protein</div>
                    <div className="text-lg font-black text-blue-400">{Math.round(scannedProduct.nutriments.proteins_100g || 0)}g</div>
                  </div>
                  <div className="bg-white/5 rounded-2xl p-4 border border-white/10 text-center">
                    <div className="text-xs text-text-muted mb-1">Karbo</div>
                    <div className="text-lg font-black text-yellow-400">{Math.round(scannedProduct.nutriments.carbohydrates_100g || 0)}g</div>
                  </div>
                  <div className="bg-white/5 rounded-2xl p-4 border border-white/10 text-center">
                    <div className="text-xs text-text-muted mb-1">Lemak</div>
                    <div className="text-lg font-black text-emerald-400">{Math.round(scannedProduct.nutriments.fat_100g || 0)}g</div>
                  </div>
                </div>

                <div className="flex gap-3">
                  <button 
                    onClick={handleRetry}
                    className="flex-1 py-4 bg-white/5 border border-white/10 rounded-2xl font-bold text-text-muted"
                  >
                    Scan Lainnya
                  </button>
                  <button 
                    onClick={handleAdd}
                    className="flex-[2] py-4 bg-primary text-white rounded-2xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-primary/20"
                  >
                    <Plus className="w-5 h-5" />
                    Tambah Log
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>

      <style>{`
        #reader { border: none !important; }
        #reader__dashboard_section_swaplink { display: none !important; }
        #reader__status_span { display: none !important; }
        #reader__camera_selection { 
          background: rgba(255,255,255,0.05) !important; 
          color: white !important; 
          border: 1px solid rgba(255,255,255,0.1) !important;
          border-radius: 8px !important;
          margin-bottom: 10px !important;
          padding: 8px !important;
        }
        #reader button {
          background: var(--primary) !important;
          color: white !important;
          border: none !important;
          padding: 10px 20px !important;
          border-radius: 12px !important;
          font-weight: bold !important;
          cursor: pointer !important;
        }
      `}</style>
    </>
  );
}
