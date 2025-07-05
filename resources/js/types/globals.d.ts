import { AppPageProps } from '@/types/index';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        Pusher: typeof Pusher;
        JSPM: {
            JSPrintManager: {
                start(): Promise<void>;
                getPrinters(): Promise<string[]>;
            };
            ClientPrintJob: new () => {
                clientPrinter: any;
                printerCommands: string;
                sendToClient(): Promise<void>;
            };
            InstalledPrinter: new (printerName: string) => any;
        };
    }
}

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
    }
}
