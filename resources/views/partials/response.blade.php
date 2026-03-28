<div class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-950 p-6">
    <div x-show="!response" class="flex h-full items-center justify-center text-gray-500 dark:text-gray-400">
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
        <div class="rounded bg-white dark:bg-gray-900 p-4 shadow dark:shadow-black/20">
            <div class="flex items-center gap-4">
                <span :class="statusColor(response.status)" class="rounded px-3 py-1 font-bold text-sm">
                    <span x-text="response.status"></span>
                    <span x-text="response.statusText"></span>
                </span>
                <span class="text-sm text-gray-600 dark:text-gray-400" x-text="`${response.time}ms`"></span>
            </div>
        </div>

        <!-- Response Tabs Card -->
        <div class="rounded bg-white dark:bg-gray-900 shadow dark:shadow-black/20">
            <!-- Pines-style Tabs -->
            <div class="relative w-full p-2 border-b border-gray-200 dark:border-gray-700" x-ref="responseTabsContainer">
                <div class="relative inline-flex items-center justify-start w-full h-10 p-0.5 text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-lg select-none">
                    <button
                        data-tab="json"
                        @click="responseTab = 'json'; repositionResponseTabMarker()"
                        :class="responseTab === 'json' ? 'text-gray-900 dark:text-gray-100 font-semibold' : 'text-gray-600 dark:text-gray-400'"
                        type="button"
                        class="relative z-20 inline-flex items-center justify-center h-9 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                    >
                        JSON
                    </button>
                    <button
                        data-tab="tree"
                        @click="responseTab = 'tree'; repositionResponseTabMarker()"
                        :class="responseTab === 'tree' ? 'text-gray-900 dark:text-gray-100 font-semibold' : 'text-gray-600 dark:text-gray-400'"
                        type="button"
                        class="relative z-20 inline-flex items-center justify-center h-9 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                    >
                        Tree
                    </button>
                    <button
                        data-tab="raw"
                        @click="responseTab = 'raw'; repositionResponseTabMarker()"
                        :class="responseTab === 'raw' ? 'text-gray-900 dark:text-gray-100 font-semibold' : 'text-gray-600 dark:text-gray-400'"
                        type="button"
                        class="relative z-20 inline-flex items-center justify-center h-9 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                    >
                        Raw
                    </button>
                    <button
                        data-tab="headers"
                        @click="responseTab = 'headers'; repositionResponseTabMarker()"
                        :class="responseTab === 'headers' ? 'text-gray-900 dark:text-gray-100 font-semibold' : 'text-gray-600 dark:text-gray-400'"
                        type="button"
                        class="relative z-20 inline-flex items-center justify-center h-9 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                    >
                        Headers
                    </button>
                    <div x-ref="responseTabMarker" class="absolute left-0.5 z-10 h-9 duration-300 ease-out" x-cloak>
                        <div class="w-full h-full bg-white dark:bg-gray-700 rounded-md shadow-sm dark:shadow-black/20"></div>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- JSON Tab -->
                <div x-show="responseTab === 'json'" class="space-y-3">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-medium text-gray-700 dark:text-gray-300">Response Body</h3>
                        <button
                            @click="copyToClipboard(response.body)"
                            type="button"
                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-neutral-500 transition-colors duration-100 rounded-md focus:outline-none bg-neutral-50 hover:text-neutral-600 hover:bg-neutral-100"
                        >
                            Copy
                        </button>
                    </div>
                    <div class="json-viewer">
                        <pre class="overflow-x-auto rounded bg-gray-50 dark:bg-gray-950 p-3 text-sm font-mono text-gray-800 dark:text-gray-200"><span x-html="highlightJson(response.body)"></span></pre>
                    </div>
                </div>

                <!-- Tree Tab -->
                <div x-show="responseTab === 'tree'" class="space-y-2">
                    <template x-if="typeof response.body === 'object' && response.body !== null">
                        <div class="json-viewer">
                            <div class="overflow-x-auto rounded bg-gray-50 dark:bg-gray-950 p-3 text-sm font-mono text-gray-800 dark:text-gray-200" x-html="renderJsonTree(response.body, 'root', 0)"></div>
                        </div>
                    </template>
                    <template x-if="typeof response.body !== 'object' || response.body === null">
                        <div class="text-gray-600 dark:text-gray-400 text-sm p-3">
                            This is not a JSON object or array and cannot be displayed as a tree.
                        </div>
                    </template>
                </div>

                <!-- Raw Tab -->
                <div x-show="responseTab === 'raw'" class="space-y-3">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-medium text-gray-700 dark:text-gray-300">Raw Response</h3>
                        <button
                            @click="navigator.clipboard.writeText(response.rawBody)"
                            type="button"
                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-neutral-500 transition-colors duration-100 rounded-md focus:outline-none bg-neutral-50 hover:text-neutral-600 hover:bg-neutral-100"
                        >
                            Copy
                        </button>
                    </div>
                    <pre class="overflow-x-auto rounded bg-gray-50 dark:bg-gray-950 p-3 text-sm font-mono text-gray-800 dark:text-gray-200 whitespace-pre-wrap break-words" x-text="response.rawBody"></pre>
                </div>

                <!-- Headers Tab -->
                <div x-show="responseTab === 'headers'" class="space-y-1 text-sm font-mono">
                    <template x-for="(value, key) in response.headers" :key="key">
                        <div class="border-b border-gray-200 dark:border-gray-700 py-2 last:border-b-0">
                            <span class="font-medium text-gray-700 dark:text-gray-300" x-text="key + ':'" ></span>
                            <span class="text-gray-600 dark:text-gray-400 ml-2" x-text="value"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Use as Token Button -->
        <button
            x-show="response.token"
            @click="useAsToken(response.token)"
            type="button"
            class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-yellow-600 transition-colors duration-100 rounded-md focus:outline-none bg-yellow-50 hover:text-yellow-700 hover:bg-yellow-100"
        >
            Use as Token
        </button>
    </div>
</div>
