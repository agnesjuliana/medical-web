interface HeaderProps {
  title: string;
  subtitle: string;
}

export default function Header({ title, subtitle }: HeaderProps) {
  return (
    <div className="m-2.5 text-left md:text-center">
      <h1 className="text-lg font-bold text-slate-900 dark:text-white">
        {title}
      </h1>
      <p className="text-slate-600 text-sm dark:text-slate-300">{subtitle}</p>
    </div>
  );
}
