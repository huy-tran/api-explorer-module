<div class="flex-1 overflow-hidden border-r border-gray-200 bg-white flex flex-col">
    <div x-show="!active" class="flex h-full items-center justify-center text-gray-500">
        <p>Select an endpoint from the sidebar</p>
    </div>

    <div x-show="active" class="flex h-full flex-col">

        <!-- Pines-style Tabs -->
        <div
            @keydown.escape="if (envSelOpen) envSelOpen = false"
            class="relative w-full p-2"
            x-ref="tabsContainer"
        >
            <div class="relative inline-flex items-center justify-start w-full h-10 p-0.5 text-gray-600 bg-gray-100 rounded-lg select-none">
                <button
                    data-tab="headers"
                    @click="activeTab = 'headers'; repositionTabMarker()"
                    :class="activeTab === 'headers' ? 'text-gray-900 font-semibold' : 'text-gray-600'"
                    type="button"
                    class="relative z-20 inline-flex items-center justify-center h-9 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                >
                    Headers
                </button>
                <button
                    data-tab="params"
                    @click="activeTab = 'params'; repositionTabMarker()"
                    x-show="Object.keys(pathParams).length > 0"
                    :class="activeTab === 'params' ? 'text-gray-900 font-semibold' : 'text-gray-600'"
                    type="button"
                    class="relative z-20 inline-flex items-center justify-center h-9 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                >
                    Params
                </button>
                <button
                    data-tab="body"
                    @click="activeTab = 'body'; repositionTabMarker()"
                    :class="activeTab === 'body' ? 'text-gray-900 font-semibold' : 'text-gray-600'"
                    type="button"
                    class="relative z-20 inline-flex items-center justify-center h-9 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                >
                    Body
                </button>
                <button
                    data-tab="query"
                    @click="activeTab = 'query'; repositionTabMarker()"
                    :class="activeTab === 'query' ? 'text-gray-900 font-semibold' : 'text-gray-600'"
                    type="button"
                    class="relative z-20 inline-flex items-center justify-center h-9 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                >
                    Query
                </button>
                <div x-ref="tabMarker" class="absolute left-0.5 z-10 h-9 duration-300 ease-out" x-cloak>
                    <div class="w-full h-full bg-white rounded-md shadow-sm"></div>
                </div>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="flex-1 overflow-y-auto">
            <!-- Headers Tab -->
            <div id="tab-headers" role="tabpanel" x-show="activeTab === 'headers'" class="p-6 space-y-3">
                <div class="space-y-2">
                    <template x-for="(header, index) in headers" :key="index">
                        <div class="flex gap-2 items-center">
                            <input
                                x-model="header.enabled"
                                @change="persistHeaders()"
                                type="checkbox"
                                class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-neutral-900 focus:ring-neutral-900"
                            />
                            <input
                                x-model="header.key"
                                @change="persistHeaders()"
                                type="text"
                                placeholder="Key"
                                class="flex-1 h-10 px-3 py-2 text-sm bg-white border rounded-md border-neutral-300 ring-offset-background placeholder:text-neutral-500 focus:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-400"
                            />
                            <input
                                x-model="header.value"
                                @change="persistHeaders()"
                                type="text"
                                placeholder="Value"
                                class="flex-1 h-10 px-3 py-2 text-sm bg-white border rounded-md border-neutral-300 ring-offset-background placeholder:text-neutral-500 focus:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-400"
                            />
                            <button
                                @click="removeHeader(index)"
                                type="button"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-red-500 transition-colors duration-100 rounded-md focus:ring-2 focus:ring-offset-2 focus:ring-red-100 bg-red-50 hover:text-red-600 hover:bg-red-100"
                            >
                                ✕
                            </button>
                        </div>
                    </template>
                </div>
                <button
                    @click="addHeader()"
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-neutral-500 transition-colors duration-100 rounded-md focus:ring-2 focus:ring-offset-2 focus:ring-neutral-100 bg-neutral-50 hover:text-neutral-600 hover:bg-neutral-100"
                >
                    + Add Header
                </button>
            </div>

            <!-- Params Tab -->
            <div id="tab-params" role="tabpanel" x-show="activeTab === 'params'" class="p-6 space-y-3">
                <template x-for="(value, key) in pathParams" :key="key">
                    <div class="flex items-center gap-3">
                        <label class="w-32 text-sm font-medium text-gray-700 font-mono" x-text="`{${key}}`"></label>
                        <input
                            x-model="pathParams[key]"
                            @input="persistEndpointState()"
                            type="text"
                            :placeholder="key"
                            class="flex-1 h-10 px-3 py-2 text-sm font-mono bg-white border rounded-md border-neutral-300 ring-offset-background placeholder:text-neutral-500 focus:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-400"
                        />
                    </div>
                </template>
            </div>

            <!-- Body Tab -->
            <div id="tab-body" role="tabpanel" x-show="activeTab === 'body'" class="p-6 space-y-4">
                <template x-for="field in active && active.fields ? active.fields : []" :key="field.name">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <input
                                x-model="enabledFields[field.name]"
                                @change="persistEndpointState()"
                                type="checkbox"
                                class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-neutral-900 focus:ring-neutral-900"
                            />
                            <label class="block text-sm font-medium text-gray-700">
                                <span x-text="field.name"></span>
                                <span x-show="field.required" class="text-red-600">*</span>
                                <span x-show="!field.required" class="text-gray-500 font-normal">(optional)</span>
                            </label>
                        </div>
                        <template x-if="field.inputType === 'text'">
                            <div class="flex gap-2 items-center">
                                <input
                                    x-model="body[field.name]"
                                    @input="persistEndpointState()"
                                    type="text"
                                    :placeholder="field.validationHint || 'Enter ' + field.name"
                                    class="flex-1 h-10 px-3 py-2 text-sm bg-white border rounded-md border-neutral-300 ring-offset-background placeholder:text-neutral-500 focus:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-400"
                                />
                                <button
                                    type="button"
                                    @click="fakerTargetField = field.name; showFakerBrowser = true; fakerSearch = ''; fakerActiveCategory = 'all'"
                                    title="Insert faker expression"
                                    class="shrink-0 h-10 px-3 text-sm text-neutral-500 bg-neutral-50 border border-neutral-200 rounded-md hover:bg-neutral-100 hover:text-neutral-600"
                                >⚡</button>
                            </div>
                        </template>
                        <template x-if="field.inputType === 'textarea'">
                            <div class="flex gap-2">
                                <textarea
                                    x-model="body[field.name]"
                                    @input="persistEndpointState()"
                                    :placeholder="field.validationHint || 'Enter ' + field.name"
                                    rows="4"
                                    class="flex-1 px-3 py-2 text-sm font-mono bg-white border rounded-md border-neutral-300 ring-offset-background placeholder:text-neutral-500 focus:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-400"
                                ></textarea>
                                <button
                                    type="button"
                                    @click="fakerTargetField = field.name; showFakerBrowser = true; fakerSearch = ''; fakerActiveCategory = 'all'"
                                    title="Insert faker expression"
                                    class="shrink-0 h-10 px-3 text-sm text-neutral-500 bg-neutral-50 border border-neutral-200 rounded-md hover:bg-neutral-100 hover:text-neutral-600 self-start"
                                >⚡</button>
                            </div>
                        </template>
                        <template x-if="field.inputType === 'number'">
                            <input
                                x-model="body[field.name]"
                                @input="persistEndpointState()"
                                type="number"
                                :placeholder="field.validationHint || 'Enter ' + field.name"
                                class="w-full h-10 px-3 py-2 text-sm bg-white border rounded-md border-neutral-300 ring-offset-background placeholder:text-neutral-500 focus:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-400"
                            />
                        </template>
                        <template x-if="field.inputType === 'checkbox'">
                            <input
                                x-model="body[field.name]"
                                @change="persistEndpointState()"
                                type="checkbox"
                                class="h-4 w-4 rounded border border-gray-300"
                            />
                        </template>
                        <template x-if="field.inputType === 'datetime-local'">
                            <input
                                x-model="body[field.name]"
                                @input="persistEndpointState()"
                                type="datetime-local"
                                class="w-full h-10 px-3 py-2 text-sm bg-white border rounded-md border-neutral-300 ring-offset-background placeholder:text-neutral-500 focus:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-400"
                            />
                        </template>
                        <template x-if="field.inputType === 'file'">
                            <input
                                @change="body[field.name] = $event.target.files[0]"
                                type="file"
                                class="w-full"
                            />
                        </template>
                        <template x-if="field.inputType === 'select'">
                            <div
                                x-data="piinesSelect(
                                    [{ title: '-- Select --', value: '' }, ...field.enumCases.map(c => ({ title: c, value: c }))],
                                    body[field.name]
                                )"
                                x-init="
                                    $watch('selectedItem', (item) => {
                                        $parent.body[field.name] = item ? item.value : '';
                                        $parent.persistEndpointState();
                                    });
                                    $watch('selectOpen', () => {
                                        if (!selectedItem) { selectableItemActive = selectableItems[0] || null; }
                                        else { selectableItemActive = selectedItem; }
                                        setTimeout(() => selectScrollToActiveItem(), 10);
                                        selectPositionUpdate();
                                        window.addEventListener('resize', () => selectPositionUpdate());
                                    });
                                "
                                @keydown.escape="if (selectOpen) selectOpen = false"
                                @keydown.down.prevent="if (selectOpen) { selectableItemActiveNext(); } else { selectOpen = true; }"
                                @keydown.up.prevent="if (selectOpen) { selectableItemActivePrevious(); } else { selectOpen = true; }"
                                @keydown.enter="selectedItem = selectableItemActive; selectOpen = false;"
                                @keydown="selectKeydown($event)"
                                class="relative w-full"
                            >
                                <button x-ref="selectButton" @click="selectOpen = !selectOpen"
                                    :class="{ 'ring-2 ring-offset-2 ring-neutral-400': !selectOpen }"
                                    type="button"
                                    class="relative min-h-[38px] flex items-center justify-between w-full py-2 pl-3 pr-10 text-left bg-white border rounded-md shadow-sm cursor-default border-gray-300 focus:outline-none text-sm">
                                    <span x-text="selectedItem && selectedItem.value ? selectedItem.title : '-- Select --'" class="truncate text-gray-500"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-gray-400"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z" clip-rule="evenodd"/></svg>
                                    </span>
                                </button>
                                <ul x-show="selectOpen" x-ref="selectableItemsList" @click.away="selectOpen = false"
                                    x-transition:enter="transition ease-out duration-50"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100"
                                    :class="{ 'bottom-0 mb-10': selectDropdownPosition === 'top', 'top-0 mt-10': selectDropdownPosition === 'bottom' }"
                                    class="absolute z-10 w-full py-1 overflow-auto text-sm bg-white rounded-md shadow-md max-h-56 ring-1 ring-black ring-opacity-5 focus:outline-none"
                                    x-cloak>
                                    <template x-for="item in selectableItems" :key="item.value">
                                        <li @click="selectedItem = item; selectOpen = false; $refs.selectButton.focus();"
                                            :id="item.value + '-' + selectId"
                                            :class="{ 'bg-neutral-100 text-gray-900': selectableItemIsActive(item) }"
                                            @mousemove="selectableItemActive = item"
                                            class="relative flex items-center h-full py-2 pl-8 pr-4 text-gray-700 cursor-default select-none">
                                            <svg x-show="selectedItem && selectedItem.value == item.value" class="absolute left-0 w-4 h-4 ml-2 stroke-current text-neutral-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            <span class="block font-medium truncate" x-text="item.title"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>
                        <template x-if="field.isNested">
                            <details class="rounded border border-gray-300 p-3">
                                <summary class="cursor-pointer font-medium text-gray-700">Expand <span x-text="field.nestedDtoClass"></span></summary>
                                <div class="mt-3 space-y-3 pl-4">
                                    <!-- Nested fields would go here - simplified for now -->
                                    <p class="text-sm text-gray-500">Nested DTO fields</p>
                                </div>
                            </details>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Query Params Tab -->
            <div id="tab-query" role="tabpanel" x-show="activeTab === 'query'" class="p-6 space-y-3">
                <div class="space-y-2">
                    <template x-for="(param, index) in queryParams" :key="index">
                        <div class="flex gap-2 items-center">
                            <input
                                x-model="param.enabled"
                                @change="persistEndpointState()"
                                type="checkbox"
                                class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-neutral-900 focus:ring-neutral-900"
                            />
                            <input
                                x-model="param.key"
                                @change="persistEndpointState()"
                                type="text"
                                placeholder="Key"
                                class="flex-1 h-10 px-3 py-2 text-sm bg-white border rounded-md border-neutral-300 ring-offset-background placeholder:text-neutral-500 focus:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-400"
                            />
                            <input
                                x-model="param.value"
                                @change="persistEndpointState()"
                                type="text"
                                placeholder="Value"
                                class="flex-1 h-10 px-3 py-2 text-sm bg-white border rounded-md border-neutral-300 ring-offset-background placeholder:text-neutral-500 focus:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-400"
                            />
                            <button
                                @click="removeQueryParam(index)"
                                type="button"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-red-500 transition-colors duration-100 rounded-md focus:ring-2 focus:ring-offset-2 focus:ring-red-100 bg-red-50 hover:text-red-600 hover:bg-red-100"
                            >
                                ✕
                            </button>
                        </div>
                    </template>
                </div>
                <button
                    @click="addQueryParam()"
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-neutral-500 transition-colors duration-100 rounded-md focus:ring-2 focus:ring-offset-2 focus:ring-neutral-100 bg-neutral-50 hover:text-neutral-600 hover:bg-neutral-100"
                >
                    + Add Query Param
                </button>
            </div>

        </div>

        <!-- Send Button -->
        <div class="border-t border-gray-200 bg-white px-6 py-4">
            <button
                @click="sendRequest()"
                :disabled="loading"
                type="button"
                class="w-full inline-flex items-center justify-center px-4 py-3 text-sm font-medium tracking-wide text-green-500 transition-colors duration-100 rounded-md focus:ring-2 focus:ring-offset-2 focus:ring-green-100 bg-green-50 hover:text-green-600 hover:bg-green-100 disabled:opacity-50"
            >
                <span x-show="!loading">Send Request</span>
                <span x-show="loading">Sending...</span>
            </button>
        </div>
    </div>
</div>
