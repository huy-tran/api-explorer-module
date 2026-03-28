<div x-show="showFakerBrowser" @keydown.escape.window="showFakerBrowser = false" class="fixed inset-0 z-50 flex items-center justify-center" x-cloak>
    <!-- Backdrop -->
    <div @click="showFakerBrowser = false"
         class="absolute inset-0 backdrop-blur-sm bg-white/70 dark:bg-black/60"
         x-transition:enter="transition ease-out duration-50"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    <!-- Modal Panel -->
    <div class="relative w-full sm:max-w-3xl bg-white dark:bg-gray-900 border border-neutral-200 dark:border-gray-700 shadow-lg sm:rounded-lg max-h-[80vh] flex flex-col overflow-hidden z-10"
         x-transition:enter="transition ease-out duration-100 transform"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-100 transform"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Faker Methods</h2>
            <div class="flex-1 mx-4">
                <input
                    x-model="fakerSearch"
                    @input="fakerActiveCategory = 'all'"
                    placeholder="Search methods..."
                    autofocus
                    class="w-full h-9 px-3 py-1.5 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 placeholder:text-neutral-400 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none focus:outline-none"
                />
            </div>
            <button @click="showFakerBrowser = false"
                type="button"
                class="inline-flex items-center justify-center h-9 w-9 text-neutral-400 dark:text-gray-500 hover:text-neutral-600 dark:hover:text-gray-300 rounded-md hover:bg-neutral-100 dark:hover:bg-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Body: Categories left, Methods right -->
        <div class="flex flex-1 overflow-hidden">
            <!-- Categories (left sidebar) -->
            <nav class="w-40 overflow-y-auto border-r border-neutral-200 dark:border-gray-700 p-2 space-y-1 bg-gray-50 dark:bg-gray-800">
                <template x-for="cat in fakerCategories()" :key="cat.id">
                    <button
                        @click="fakerActiveCategory = cat.id"
                        :class="fakerActiveCategory === cat.id ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        class="w-full text-left px-3 py-2 text-sm rounded-md transition-colors"
                        x-text="cat.label"></button>
                </template>
            </nav>

            <!-- Methods grid (right panel) -->
            <div class="flex-1 overflow-y-auto p-4 grid grid-cols-2 gap-2 content-start">
                <template x-for="method in fakerFilteredMethods()" :key="method.expr">
                    <button
                        @click.stop="insertFakerExpr(method)"
                        type="button"
                        class="text-left p-3 rounded-lg border border-neutral-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-700 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-all focus:outline-none focus:outline-none">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="method.label"></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-1 truncate" x-text="method.expr" title="Click to insert"></div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1" x-text="method.desc"></div>
                    </button>
                </template>

                <p x-show="fakerFilteredMethods().length === 0" class="col-span-2 text-sm text-gray-500 dark:text-gray-400 py-8 text-center">No methods match your search.</p>
            </div>
        </div>

        <!-- Custom Expression -->
        <div class="border-t border-neutral-200 dark:border-gray-700 px-4 py-3 flex gap-2 items-center bg-gray-50 dark:bg-gray-800"
             x-data="{ customExpr: '' }">
            <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap font-medium">Custom:</span>
            <input
                x-model="customExpr"
                @keydown.enter.prevent="if (customExpr.trim()) { insertFakerExpr(customExpr.trim()); customExpr = ''; }"
                type="text"
                placeholder="faker.animal.bear()"
                class="flex-1 h-9 px-3 py-1.5 text-sm font-mono bg-white dark:bg-gray-700 border rounded-md border-neutral-300 dark:border-gray-600 placeholder:text-neutral-400 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none focus:outline-none"
            />
            <button
                @click="if (customExpr.trim()) { insertFakerExpr(customExpr.trim()); customExpr = ''; }"
                type="button"
                :disabled="!customExpr.trim()"
                class="shrink-0 h-9 px-4 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-md hover:bg-blue-100 dark:hover:bg-blue-900/50 disabled:opacity-40 disabled:cursor-not-allowed"
            >Insert</button>
        </div>
    </div>
</div>
