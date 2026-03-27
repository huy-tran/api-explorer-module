<aside
    class="overflow-y-auto border-r border-gray-200 bg-white p-4 flex flex-col select-none relative"
    :style="`width: ${sidebarWidth}px`"
    x-data="{ activeAccordion: '' }"
>
    <div class="space-y-2 flex-1">
        <template x-for="(group, groupName) in grouped" :key="groupName">
            @include('api-explorer::partials.sidebar-group')
        </template>
    </div>

    <!-- Resizable Handle -->
    <div
        @mousedown="startResize($event)"
        class="absolute right-0 top-0 bottom-0 w-1 cursor-col-resize hover:bg-blue-500 hover:w-1.5 transition-colors"
    ></div>
</aside>
