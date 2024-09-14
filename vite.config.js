/** @type {import('vite').UserConfig} */
export default {
    build: {
        outDir: "build/output",
        emptyOutDir: false,
        rollupOptions: {
            input: ["resources/script.js"],
            output: {
                assetFileNames: "[name][extname]",
                entryFileNames: "[name].js",
            },
        },
    },
}
