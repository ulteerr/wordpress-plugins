import { defineConfig } from "vite";
import path from "path";

export default defineConfig({
  root: "resource",
  server: {
    open: true,
  },
  build: {
    outDir: "dist",
    emptyOutDir: true,
    rollupOptions: {
      input: {
        main: path.resolve(__dirname, "resource/main.ts"),
        "main-admin": path.resolve(__dirname, "resource/main-admin.ts"),
      },
      output: {
        entryFileNames: "[name].js",
        dir: "resource/dist",
      },
    },
  },
});
