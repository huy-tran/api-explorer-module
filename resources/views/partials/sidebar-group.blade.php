<!-- Module Accordion (Pines Style) -->
<div :class="{ 'border-neutral-200 dark:border-gray-700 bg-blue-50/30 dark:bg-blue-950/20' : isGroupOpen(groupName), 'border-transparent dark:border-transparent hover:border-neutral-100 dark:hover:border-gray-700' : !isGroupOpen(groupName) }" class="duration-200 ease-out bg-white dark:bg-gray-900 border rounded-md cursor-pointer group">
    <button @click="toggleGroupAccordion(groupName)" class="flex items-center justify-between w-full px-4 py-3 font-semibold text-left select-none">
        <span x-text="groupName" class="text-sm text-gray-700 dark:text-gray-300"></span>
        <!-- Pines-style icon (+ that rotates to x) -->
        <div :class="{ 'rotate-90': isGroupOpen(groupName) }" class="relative flex items-center justify-center w-2.5 h-2.5 duration-300 ease-out flex-shrink-0">
            <div class="absolute w-0.5 h-full bg-gray-400 dark:bg-gray-500 group-hover:bg-gray-600 dark:group-hover:bg-gray-300 rounded-full"></div>
            <div :class="{ 'rotate-90': isGroupOpen(groupName) }" class="absolute w-full h-0.5 ease duration-500 bg-gray-400 dark:bg-gray-500 group-hover:bg-gray-600 dark:group-hover:bg-gray-300 rounded-full"></div>
        </div>
    </button>

    <!-- Accordion Content -->
    <div x-show="isGroupOpen(groupName)" x-cloak class="overflow-hidden">
        <div class="px-4 pb-3 space-y-1">
            <!-- Render endpoints at this level -->
            <template x-if="group.__endpoints && group.__endpoints.length">
                <template x-for="endpoint in group.__endpoints" :key="endpoint.name">
                    <button
                        @click="selectEndpoint(endpoint)"
                        :class="{
                            'bg-blue-50 dark:bg-blue-950/30 border-l-4 border-blue-500 text-gray-900 dark:text-gray-100': active && active.name === endpoint.name,
                            'hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300': !(active && active.name === endpoint.name)
                        }"
                        class="w-full text-left px-2 py-2 rounded text-xs transition-colors flex items-center gap-2"
                    >
                        <span :class="methodColor(endpoint.method.split('|')[0])" class="px-2 py-0.5 rounded text-xs font-bold whitespace-nowrap flex-shrink-0">
                            <span x-text="endpoint.method.split('|')[0]"></span>
                        </span>
                        <span x-text="endpoint.name" class="font-mono flex-1 truncate text-xs"></span>
                    </button>
                </template>
            </template>

            <!-- Render nested groups (sub-resources) -->
            <template x-for="(nestedGroup, nestedGroupName) in Object.fromEntries(Object.entries(group).filter(([k]) => k !== '__endpoints'))" :key="nestedGroupName">
                <div x-data="{ nestedOpen: false }" :class="{ 'border-neutral-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50' : nestedOpen, 'border-transparent dark:border-transparent' : !nestedOpen }" class="duration-200 ease-out bg-white dark:bg-gray-900 border rounded-md cursor-pointer group mt-1">
                    <button @click="nestedOpen = !nestedOpen" class="flex items-center justify-between w-full px-3 py-2 font-medium text-left select-none">
                        <span x-text="nestedGroupName" class="text-sm text-gray-600 dark:text-gray-400"></span>
                        <!-- Pines-style icon -->
                        <div :class="{ 'rotate-90': nestedOpen }" class="relative flex items-center justify-center w-2 h-2 duration-300 ease-out flex-shrink-0">
                            <div class="absolute w-0.5 h-full bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                            <div :class="{ 'rotate-90': nestedOpen }" class="absolute w-full h-0.5 ease duration-500 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                        </div>
                    </button>

                    <div x-show="nestedOpen" x-cloak class="overflow-hidden">
                        <div class="px-3 pb-2 space-y-1">
                            <template x-if="nestedGroup.__endpoints && nestedGroup.__endpoints.length">
                                <template x-for="endpoint in nestedGroup.__endpoints" :key="endpoint.name">
                                    <button
                                        @click="selectEndpoint(endpoint)"
                                        :class="{
                                            'bg-blue-50 dark:bg-blue-950/30 border-l-4 border-blue-500 text-gray-900 dark:text-gray-100': active && active.name === endpoint.name,
                                            'hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300': !(active && active.name === endpoint.name)
                                        }"
                                        class="w-full text-left px-2 py-1.5 rounded text-xs transition-colors flex items-center gap-2"
                                    >
                                        <span :class="methodColor(endpoint.method.split('|')[0])" class="px-2 py-0.5 rounded text-xs font-bold whitespace-nowrap flex-shrink-0">
                                            <span x-text="endpoint.method.split('|')[0]"></span>
                                        </span>
                                        <span x-text="endpoint.name" class="font-mono flex-1 truncate text-xs"></span>
                                    </button>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
