import { useState } from "react";
import { Mail, Lock } from "lucide-react";

interface LoginFormProps {
  onToggleForm: () => void;
  onSubmit: () => void;
}

export function LoginForm({ onToggleForm, onSubmit }: LoginFormProps) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const handleSignIn = (e: React.FormEvent) => {
    e.preventDefault();
    if (email.trim() && password.trim()) {
      onSubmit();
    }
  };

  const isValid = email.trim() !== '' && password.trim() !== '';

  return (
    <div className="w-full flex flex-col space-y-6">
      <div className="space-y-1">
        <h2 className="text-3xl font-extrabold tracking-tight text-text-main">Masuk</h2>
        <p className="text-text-muted font-medium">Lanjutkan perjalanan hidup sehatmu.</p>
      </div>

      <form className="space-y-6" onSubmit={handleSignIn}>
        <div className="space-y-3">
          {/* Email */}
          <div className="relative group">
            <Mail className="absolute left-4 top-1/2 -translate-y-1/2 text-text-muted group-focus-within:text-primary w-5 h-5 transition-colors" />
            <input 
              type="email" 
              placeholder="Email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full bg-black/5 dark:bg-white/5 border border-black/5 dark:border-white/10 rounded-[18px] py-4 pl-12 pr-4 text-text-main placeholder-text-muted focus:outline-none focus:border-primary transition-all font-medium"
            />
          </div>

          {/* Password */}
          <div className="space-y-2">
            <div className="relative group">
              <Lock className="absolute left-4 top-1/2 -translate-y-1/2 text-text-muted group-focus-within:text-primary w-5 h-5 transition-colors" />
              <input 
                type="password" 
                placeholder="Kata Sandi"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="w-full bg-black/5 dark:bg-white/5 border border-black/5 dark:border-white/10 rounded-[18px] py-4 pl-12 pr-4 text-text-main placeholder-text-muted focus:outline-none focus:border-primary transition-all font-medium"
              />
            </div>
            <div className="flex justify-end">
               <a href="#" className="text-xs font-bold text-primary hover:text-primary-hover transition-colors">Lupa kata sandi?</a>
            </div>
          </div>
        </div>

        <button 
          type="submit"
          disabled={!isValid}
          className={`w-full font-bold py-4 rounded-[20px] transition-all active:scale-[0.98] ${
            isValid
              ? 'bg-primary text-white shadow-lg shadow-primary/20'
              : 'bg-surface text-text-muted cursor-not-allowed border border-black/5 dark:border-white/10'
          }`}
        >
          Masuk Sekarang
        </button>
      </form>

      <div className="text-center">
        <p className="text-text-muted text-sm font-medium">
          Belum punya akun?{" "}
          <button onClick={onToggleForm} className="text-primary hover:text-primary-hover font-bold transition-colors">
            Daftar Sekarang
          </button>
        </p>
      </div>
    </div>
  );
}
