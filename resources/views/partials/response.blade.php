<div class="flex-1 overflow-y-auto bg-gray-50 p-6">
    <div x-show="!response" class="flex h-full items-center justify-center text-gray-500">
        <p>Response will appear here</p>
    </div>

    <div x-show="response && response.error">
        <div class="rounded bg-red-50 p-4 text-red-700">
            <p class="font-medium">Error</p>
            <p x-text="response.error"></p>
        </div>
    </div>

    <div x-show="response && !response.error" class="space-y-4">
        <!-- Status Line -->
        <div class="rounded bg-white p-4 shadow">
            <div class="flex items-center gap-4">
                <span :class="statusColor(response.status)" class="rounded px-3 py-1 font-bold text-sm">
                    <span x-text="response.status"></span>
                    <span x-text="response.statusText"></span>
                </span>
                <span class="text-sm text-gray-600" x-text="`${response.time}ms`"></span>
            </div>
        </div>

        <!-- Response Headers -->
        <details class="rounded bg-white p-4 shadow">
            <summary class="cursor-pointer font-medium text-gray-700">Response Headers</summary>
            <div class="mt-3 space-y-1 text-sm font-mono">
                <template x-for="(value, key) in response.headers" :key="key">
                    <div class="border-t border-gray-200 py-1 pt-2">
                        <span class="font-medium text-gray-700" x-text="key + ':'" ></span>
                        <span class="text-gray-600" x-text="value"></span>
                    </div>
                </template>
            </div>
        </details>

        <!-- Response Body -->
        <div class="rounded bg-white p-4 shadow">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-medium text-gray-700">Response Body</h3>
                <button
                    @click="copyToClipboard(response.body)"
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-neutral-500 transition-colors duration-100 rounded-md focus:ring-2 focus:ring-offset-2 focus:ring-neutral-100 bg-neutral-50 hover:text-neutral-600 hover:bg-neutral-100"
                >
                    Copy
                </button>
            </div>
            <pre class="overflow-x-auto rounded bg-gray-50 p-3 text-sm font-mono text-gray-800"><span x-html="highlightJson(response.body)"></span></pre>
        </div>

        <!-- Use as Token Button -->
        <button
            x-show="response.token"
            @click="useAsToken(response.token)"
            type="button"
            class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-yellow-600 transition-colors duration-100 rounded-md focus:ring-2 focus:ring-offset-2 focus:ring-yellow-100 bg-yellow-50 hover:text-yellow-700 hover:bg-yellow-100"
        >
            Use as Token
        </button>
    </div>
</div>
