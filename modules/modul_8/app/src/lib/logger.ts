type Level = "debug" | "info" | "warn" | "error";

const isDev = import.meta.env.DEV;

function log(level: Level, module: string, message: string, data?: unknown) {
  const ts = new Date().toISOString();
  const prefix = `[${ts}] [${level.toUpperCase()}] [${module}]`;
  if (level === "error") {
    console.error(prefix, message, data ?? "");
  } else if (level === "warn") {
    console.warn(prefix, message, data ?? "");
  } else if (isDev) {
    console.log(prefix, message, data ?? "");
  }
}

export const logger = {
  debug: (mod: string, msg: string, data?: unknown) => log("debug", mod, msg, data),
  info:  (mod: string, msg: string, data?: unknown) => log("info",  mod, msg, data),
  warn:  (mod: string, msg: string, data?: unknown) => log("warn",  mod, msg, data),
  error: (mod: string, msg: string, data?: unknown) => log("error", mod, msg, data),
};

// Global unhandled error capture
if (typeof window !== "undefined") {
  window.addEventListener("error", (e) => {
    logger.error("global", e.message, { filename: e.filename, line: e.lineno });
  });
  window.addEventListener("unhandledrejection", (e) => {
    logger.error("global", "Unhandled promise rejection", e.reason);
  });
}
