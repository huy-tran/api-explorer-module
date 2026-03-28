<!-- Environment Manager Modal -->
<div
    x-show="showEnvManager"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center w-screen h-screen"
    @keydown.escape.window="showEnvManager = false"
>
    <!-- Backdrop -->
    <div
        x-show="showEnvManager"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="showEnvManager = false"
        class="absolute inset-0 w-full h-full backdrop-blur-sm bg-white/70 dark:bg-black/60"
    ></div>

    <!-- Modal -->
    <div
        x-show="showEnvManager"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 -translate-y-2 sm:scale-95"
        class="relative w-full bg-white dark:bg-gray-900 border shadow-lg border-gray-200 dark:border-gray-700 sm:max-w-4xl sm:rounded-lg max-h-[90vh] overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="flex items-center justify-between px-7 py-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editingEnv ? `Edit: ${editingEnv}` : 'New Environment'"></h2>
            <button @click="showEnvManager = false" class="flex justify-center items-center w-8 h-8 text-neutral-500 dark:text-gray-400 rounded-md hover:text-neutral-600 dark:hover:text-gray-300 hover:bg-neutral-100 dark:hover:bg-gray-700 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Content: 2 Column Layout -->
        <div class="flex flex-1 overflow-hidden">
            <!-- Left Column: All Environments -->
            <div class="w-64 border-r border-gray-200 dark:border-gray-700 overflow-y-auto flex flex-col">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">All Environments</p>
                </div>
                <div class="flex-1 overflow-y-auto flex flex-col">
                    <div class="space-y-1 p-3 flex-1">
                        <template x-for="env in environments" :key="env">
                            <div class="flex items-center justify-between rounded px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800 group cursor-pointer" @click="selectEnv(env); openEditEnv(env)">
                                <span class="text-sm font-mono text-gray-700 dark:text-gray-300 flex-1 truncate" x-text="env"></span>
                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button
                                        @click.stop="duplicateEnv(env)"
                                        type="button"
                                        title="Duplicate environment"
                                        class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium tracking-wide text-purple-500 transition-colors duration-100 rounded focus:outline-none bg-purple-50 hover:text-purple-600 hover:bg-purple-100"
                                    >
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button
                                        @click.stop="deleteEnv(env); await loadEnvironments()"
                                        type="button"
                                        title="Delete environment"
                                        class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium tracking-wide text-red-500 transition-colors duration-100 rounded focus:outline-none bg-red-50 hover:text-red-600 hover:bg-red-100"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <p x-show="environments.length === 0" class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No environments yet.</p>
                    </div>
                    <!-- Add Environment Button -->
                    <div class="p-3 border-t border-gray-200 dark:border-gray-700">
                        <button
                            @click="openNewEnv()"
                            type="button"
                            class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-blue-500 transition-colors duration-100 rounded-md focus:outline-none bg-blue-50 hover:text-blue-600 hover:bg-blue-100"
                        >
                            + Add Environment
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Form -->
            <div class="flex-1 overflow-y-auto flex flex-col">
                <div class="px-7 py-6 space-y-3 flex-1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                        <input
                            x-model="editingEnvName"
                            type="text"
                            placeholder="e.g. local, staging, HuyHomePc"
                            class="w-full h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Base URL</label>
                        <input
                            x-model="editingEnvBaseUrl"
                            type="text"
                            placeholder="e.g. https://myapp.test"
                            class="w-full h-10 px-3 py-2 text-sm font-mono bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Variables</label>
                        <div class="space-y-2">
                            <template x-for="(variable, index) in editingEnvVars" :key="index">
                                <div class="flex gap-2">
                                    <input
                                        x-model="variable.key"
                                        type="text"
                                        placeholder="Key"
                                        class="flex-1 h-10 px-3 py-2 text-sm font-mono bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                                    />
                                    <input
                                        x-model="variable.value"
                                        type="text"
                                        placeholder="Value"
                                        class="flex-1 h-10 px-3 py-2 text-sm font-mono bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                                    />
                                    <button
                                        @click="removeEnvVar(index)"
                                        type="button"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-red-500 transition-colors duration-100 rounded-md focus:outline-none bg-red-50 hover:text-red-600 hover:bg-red-100"
                                    >
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <button
                            @click="addEnvVar()"
                            type="button"
                            class="mt-2 inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-neutral-500 transition-colors duration-100 rounded-md focus:outline-none bg-neutral-50 hover:text-neutral-600 hover:bg-neutral-100"
                        >
                            + Add Variable
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="px-7 py-4 border-t border-gray-200">
                    <button
                        @click="saveEditingEnv()"
                        type="button"
                        class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-green-500 transition-colors duration-100 rounded-md focus:outline-none bg-green-50 hover:text-green-600 hover:bg-green-100"
                    >
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
