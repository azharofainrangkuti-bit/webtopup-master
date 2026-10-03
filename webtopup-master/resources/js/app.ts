import '../css/app.css';

import { createInertiaApp, router, usePage } from '@inertiajs/vue3';
import { putConfig, renderApp } from '@inertiaui/modal-vue';
import { VueQueryPlugin } from '@tanstack/vue-query';
import dayjs from 'dayjs';
import 'dayjs/locale/id';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp } from 'vue';
import { initializeTheme } from './composables/useAppearance';
import { revealDirective } from './directives/reveal';

dayjs.locale('id');

const app = usePage();

router.on('navigate', () => {
    if (typeof window.fbq === 'function') {
        window.fbq('track', 'PageView');
    }
});

createInertiaApp({
    title: (title) => (title ? `${title} - ${app.props.name}` : app.props.name),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: renderApp(App, props) })
            .use(plugin)
            .use(VueQueryPlugin)
            .directive('reveal', revealDirective)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

putConfig({
    type: 'modal',
    navigate: false,
    modal: {
        closeButton: true,
        closeExplicitly: false,
        maxWidth: '2xl',
        paddingClasses: 'p-4 sm:p-6',
        panelClasses: 'bg-(--background) rounded dark:bg-gray-800',
        position: 'center',
    },
    slideover: {
        closeButton: true,
        closeExplicitly: false,
        maxWidth: '2xl',
        paddingClasses: 'p-4 sm:p-6',
        panelClasses: 'bg-(--background) min-h-screen border-l',
        position: 'right',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
