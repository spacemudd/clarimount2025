import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import { updateI18nMessages } from './i18n';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Function to set document direction based on locale
function setDirection(locale: string) {
    const rtlLocales = ['ar', 'he', 'fa', 'ur'];
    const dir = rtlLocales.includes(locale) ? 'rtl' : 'ltr';
    document.documentElement.setAttribute('dir', dir);
    document.documentElement.setAttribute('lang', locale);
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // Get locale and translations from Laravel
        const userLanguage = (props.initialPage.props as any).locale || 'en';
        const translations = (props.initialPage.props as any).translations || {};
        
        // Create i18n instance with Laravel translations
        const i18n = updateI18nMessages(userLanguage, translations);
        
        // Set initial direction
        setDirection(userLanguage);

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18n);

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
