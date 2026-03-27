<div x-show="showFakerBrowser" @keydown.escape.window="showFakerBrowser = false" class="fixed inset-0 z-50 flex items-center justify-center" x-cloak>
    <!-- Backdrop -->
    <div @click="showFakerBrowser = false"
         class="absolute inset-0 backdrop-blur-sm bg-white/70"
         x-transition:enter="transition ease-out duration-50"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    <!-- Modal Panel -->
    <div class="relative w-full sm:max-w-3xl bg-white border border-neutral-200 shadow-lg sm:rounded-lg max-h-[80vh] flex flex-col overflow-hidden z-10"
         x-transition:enter="transition ease-out duration-100 transform"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-100 transform"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200">
            <h2 class="text-lg font-semibold text-gray-900">Faker Methods</h2>
            <div class="flex-1 mx-4">
                <input
                    x-model="fakerSearch"
                    @input="fakerActiveCategory = 'all'"
                    placeholder="Search methods..."
                    autofocus
                    class="w-full h-9 px-3 py-1.5 text-sm bg-white border rounded-md border-neutral-300 placeholder:text-neutral-400 focus:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-neutral-400"
                />
            </div>
            <button @click="showFakerBrowser = false"
                type="button"
                class="inline-flex items-center justify-center h-9 w-9 text-neutral-400 hover:text-neutral-600 rounded-md hover:bg-neutral-100">
                ✕
            </button>
        </div>

        <!-- Body: Categories left, Methods right -->
        <div class="flex flex-1 overflow-hidden">
            <!-- Categories (left sidebar) -->
            <nav class="w-40 overflow-y-auto border-r border-neutral-200 p-2 space-y-1 bg-gray-50">
                <template x-for="cat in fakerCategories()" :key="cat.id">
                    <button
                        @click="fakerActiveCategory = cat.id"
                        :class="fakerActiveCategory === cat.id ? 'bg-blue-100 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-100'"
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
                        class="text-left p-3 rounded-lg border border-neutral-200 hover:border-blue-300 hover:bg-blue-50 transition-all focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-blue-400">
                        <div class="text-sm font-medium text-gray-900" x-text="method.label"></div>
                        <div class="text-xs text-gray-500 font-mono mt-1 truncate" x-text="method.expr" title="Click to insert"></div>
                        <div class="text-xs text-gray-400 mt-1" x-text="method.desc"></div>
                    </button>
                </template>

                <p x-show="fakerFilteredMethods().length === 0" class="col-span-2 text-sm text-gray-500 py-8 text-center">No methods match your search.</p>
            </div>
        </div>
    </div>
</div>
