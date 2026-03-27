<aside
    class="overflow-y-auto border-r border-gray-200 bg-white p-4 flex flex-col select-none relative"
    :style="`width: ${sidebarWidth}px`"
    x-data="{ activeAccordion: '' }"
>
    <!-- Search Input -->
    <div class="mb-4">
        <input
            x-model="searchQuery"
            @input="performSearch()"
            @keydown.escape="searchQuery = ''"
            type="text"
            placeholder="Search endpoints..."
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
        <div x-show="searchQuery" class="mt-1 text-xs text-gray-500">
            <span x-text="`Found: ${filteredEndpoints.length} endpoint(s)`"></span>
        </div>
    </div>

    <div class="space-y-2 flex-1">
        <!-- Show grouped view (filtered when searching) -->
        <template x-for="(group, groupName) in (searchQuery ? filteredGrouped : grouped)" :key="groupName">
            @include('api-explorer::partials.sidebar-group')
        </template>

        <!-- Show empty state when search yields no results -->
        <template x-if="searchQuery && Object.keys(filteredGrouped).length === 0">
            <div class="text-center py-6">
                <p class="text-xs text-gray-500">No endpoints found matching your search.</p>
            </div>
        </template>
    </div>

    <!-- Resizable Handle -->
    <div
        @mousedown="startResize($event)"
        class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize hover:bg-blue-500 hover:w-1.5 transition-colors"
    ></div>
</aside>
