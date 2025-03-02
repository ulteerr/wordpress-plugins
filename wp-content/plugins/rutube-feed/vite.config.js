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
				main: path.resolve(__dirname, "resource/main.js"),
				"main-admin": path.resolve(__dirname, "resource/main-admin.js"),
			},
			output: {
				entryFileNames: "[name].js", // Имена файлов остаются такими же
				dir: "resource/dist", // Сборка в resource/dist
			},
		},
	},
});
