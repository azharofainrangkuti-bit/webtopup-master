declare module '@inertiaui/modal-vue' {
    import { DefineComponent, Plugin } from 'vue';

    export const Modal: DefineComponent<any, any, any>;
    export const ModalLink: DefineComponent<any, any, any>;
    export function putConfig(key: Record<string, any>, value?: any): void;
    export function renderApp(component: any, props: any): any;
    const plugin: Plugin;
    export default plugin;
}
