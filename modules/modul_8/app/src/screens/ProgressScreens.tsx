import Header from "@/components/header/Header";

export default function ProgressScreen() {
  return (
    <>
      <Header title="Progress" subtitle="Your progress tracking" />
      <div className="mt-8">
        <p className="text-gray-600 dark:text-gray-400">Progress content here</p>
      </div>
    </>
  );
}
