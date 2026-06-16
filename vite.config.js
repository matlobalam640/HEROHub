import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const assetUrl = (env.ASSET_URL || '').replace(/\/$/, '');
    /* Relative ./ so @font-face URLs resolve when the app is not at the domain root (e.g. /HEROHub/public/). */
    const base = assetUrl ? `${assetUrl}/build/` : './';

    return {
        base,
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                ],
                refresh: true,
            }),
        ],
    };
});
