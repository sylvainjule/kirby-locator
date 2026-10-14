import { defineConfig } from 'kirbyup/config'

export default defineConfig({
	vite: {
		build: {
			// keep native `light-dark()` instead of Lightning CSS' polyfill,
			// which relies on `color-scheme` being processed by Lightning CSS too
			cssTarget: ['chrome123', 'edge123', 'firefox120', 'safari17.5']
		}
	}
})
