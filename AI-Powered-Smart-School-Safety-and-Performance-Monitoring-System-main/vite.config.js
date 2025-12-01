import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/sass/app.scss",
                "resources/js/app.js",

                // Common JavaScript files
                "resources/js/common/imagePreview.js",
                "resources/js/common/confirm.js",
                "resources/js/common/viewModal.js",

                // Admin CSS files
                "resources/css/admin/dashboard.css",
                "resources/css/admin/forms.css",
                "resources/css/admin/tables.css",
                "resources/css/admin/attendance-dashboard.css",
                "resources/css/admin/common-forms.css",
                "resources/css/admin/profile.css",
                "resources/css/admin/timetables.css",
                "resources/css/admin/school-setup.css",
                "resources/css/admin/settings.css",
                "resources/css/admin/audio-threat.css",

                // Authentication CSS files
                "resources/css/auth/login.css",

                // Component CSS files
                "resources/css/components/notifications.css",
                "resources/css/components/utilities.css",

                // Admin JavaScript files
                "resources/js/admin/dashboard.js",
                "resources/js/admin/student-form.js",
                "resources/js/admin/student-table.js",
                "resources/js/admin/notifications.js",
                "resources/js/admin/audio-threat.js",

                // Student JavaScript files
                "resources/js/student/homework-attempt.js",

                // Component JavaScript files
                "resources/js/components/notifications.js",
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            vue: "vue/dist/vue.esm-bundler.js",
        },
    },
});
