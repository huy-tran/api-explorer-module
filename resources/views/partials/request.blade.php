<div class="flex-1 overflow-hidden border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex flex-col">
    <div x-show="!active" class="flex h-full items-center justify-center text-gray-500 dark:text-gray-400">
        <p>Select an endpoint from the sidebar</p>
    </div>

    <div x-show="active" class="flex h-full flex-col">

        <!-- Pines-style Tabs -->
        <div
            @keydown.escape="if (envSelOpen) envSelOpen = false"
            class="relative w-full p-2"
            x-ref="tabsContainer"
        >
            <div class="relative inline-flex items-center justify-start w-full h-10 p-0.5 text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-lg select-none">
                <button
                    data-tab="headers"
                    @click="activeTab = 'headers'; repositionTabMarker()"
                    :class="activeTab === 'headers' ? 'text-gray-900 dark:text-gray-100 font-semibold' : 'text-gray-600 dark:text-gray-400'"
                    type="button"
                    class="relative z-20 inline-flex items-center justify-center h-9 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                >
                    Headers
                </button>
                <button
                    data-tab="params"
                    @click="activeTab = 'params'; repositionTabMarker()"
                    x-show="Object.keys(pathParams).length > 0"
                    :class="activeTab === 'params' ? 'text-gray-900 dark:text-gray-100 font-semibold' : 'text-gray-600 dark:text-gray-400'"
                    type="button"
                    class="relative z-20 inline-flex items-center justify-center h-9 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                >
                    Params
                </button>
                <button
                    data-tab="body"
                    @click="activeTab = 'body'; repositionTabMarker()"
                    :class="activeTab === 'body' ? 'text-gray-900 dark:text-gray-100 font-semibold' : 'text-gray-600 dark:text-gray-400'"
                    type="button"
                    class="relative z-20 inline-flex items-center justify-center h-9 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                >
                    Body
                </button>
                <button
                    data-tab="query"
                    @click="activeTab = 'query'; repositionTabMarker()"
                    :class="activeTab === 'query' ? 'text-gray-900 dark:text-gray-100 font-semibold' : 'text-gray-600 dark:text-gray-400'"
                    type="button"
                    class="relative z-20 inline-flex items-center justify-center h-9 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                >
                    Query
                </button>
                <button
                    data-tab="auth"
                    @click="activeTab = 'auth'; repositionTabMarker()"
                    :class="activeTab === 'auth' ? 'text-gray-900 dark:text-gray-100 font-semibold' : 'text-gray-600 dark:text-gray-400'"
                    type="button"
                    class="relative z-20 inline-flex items-center justify-center h-9 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap"
                >
                    Auth
                </button>
                <div x-ref="tabMarker" class="absolute left-0.5 z-10 h-9 duration-300 ease-out" x-cloak>
                    <div class="w-full h-full bg-white dark:bg-gray-700 rounded-md shadow-sm dark:shadow-black/20"></div>
                </div>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="flex-1 overflow-y-auto">
            <!-- Headers Tab -->
            <div id="tab-headers" role="tabpanel" x-show="activeTab === 'headers'" class="p-6 space-y-3">
                <!-- File Upload Notice -->
                <template x-if="active && active.fields && hasFileField(active.fields)">
                    <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-3 flex gap-3">
                        <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5"></i>
                        <div class="text-sm text-blue-800 dark:text-blue-300">
                            <p class="font-medium">File upload detected</p>
                            <p class="text-xs mt-1">Content-Type will automatically be set to <code class="bg-blue-100 dark:bg-blue-800 px-1.5 py-0.5 rounded">multipart/form-data</code></p>
                        </div>
                    </div>
                </template>

                <div class="space-y-2">
                    <template x-for="(header, index) in headers" :key="index">
                        <div class="flex gap-2 items-center">
                            <input
                                x-model="header.enabled"
                                @change="persistHeaders()"
                                type="checkbox"
                                class="w-4 h-4 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded text-neutral-900 dark:text-neutral-300 focus:outline-none"
                            />
                            <input
                                x-model="header.key"
                                :readonly="['Content-Type', 'Accept'].includes(header.key)"
                                @change="
                                    if (header.key === 'Content-Type' && !header.value) {
                                        header.value = 'application/json';
                                    }
                                    persistHeaders();
                                "
                                type="text"
                                placeholder="Key"
                                :class="['Content-Type', 'Accept'].includes(header.key) ? 'bg-gray-50 cursor-not-allowed' : ''"
                                class="flex-1 h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none focus:outline-none"
                            />
                            <template x-if="header.key === 'Accept' || header.key === 'Content-Type'">
                                <div
                                    x-data="{
                                        selectOpen: false,
                                        optionsList: header.key === 'Accept' ? acceptHeaderOptions : contentTypeOptions,
                                        selectedItem: (header.key === 'Accept' ? acceptHeaderOptions : contentTypeOptions).find(o => o.value === header.value) || null
                                    }"
                                    @click.away="selectOpen = false"
                                    class="relative flex-1"
                                >
                                    <button @click="selectOpen = !selectOpen"
                                        type="button"
                                        :disabled="header.key === 'Content-Type' && active && active.fields && hasFileField(active.fields)"
                                        :title="header.key === 'Content-Type' && active && active.fields && hasFileField(active.fields) ? 'Automatically set to multipart/form-data for file uploads' : ''"
                                        :class="header.key === 'Content-Type' && active && active.fields && hasFileField(active.fields) ? 'opacity-50 cursor-not-allowed bg-gray-50 dark:bg-gray-800' : ''"
                                        class="relative w-full h-10 flex items-center justify-between px-3 py-2 text-left bg-white dark:bg-gray-800 border rounded-md cursor-default border-neutral-300 dark:border-gray-600 focus:outline-none text-sm">
                                        <span x-text="selectedItem ? selectedItem.title : '-- Select --'" class="truncate text-gray-700 dark:text-gray-300"></span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                            <i class="fas fa-chevron-down text-gray-400"></i>
                                        </span>
                                    </button>
                                    <ul x-show="selectOpen && !(header.key === 'Content-Type' && active && active.fields && hasFileField(active.fields))"
                                        x-transition:enter="transition ease-out duration-50"
                                        x-transition:enter-start="opacity-0 -translate-y-1"
                                        x-transition:enter-end="opacity-100"
                                        class="absolute z-10 w-full py-1 overflow-auto text-sm bg-white dark:bg-gray-800 rounded-md shadow-md dark:shadow-black/20 max-h-56 ring-1 ring-black dark:ring-white ring-opacity-5 dark:ring-opacity-10 focus:outline-none top-full mt-1"
                                        x-cloak>
                                        <template x-for="option in optionsList" :key="option.value">
                                            <li @click="header.value = option.value; selectedItem = option; selectOpen = false; persistHeaders()"
                                                :class="{ 'bg-neutral-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100': selectedItem && selectedItem.value === option.value }"
                                                class="relative flex items-center h-full py-2 pl-8 pr-4 text-gray-700 dark:text-gray-300 cursor-default select-none hover:bg-gray-100 dark:hover:bg-gray-700">
                                                <i x-show="selectedItem && selectedItem.value === option.value" class="fas fa-check absolute left-0 ml-2 text-neutral-400" style="font-size: 1rem;"></i>
                                                <span class="block font-medium truncate" x-text="option.title"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </template>
                            <template x-if="header.key !== 'Accept' && header.key !== 'Content-Type'">
                                <input
                                    x-model="header.value"
                                    @change="persistHeaders()"
                                    type="text"
                                    placeholder="Value"
                                    class="flex-1 h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none focus:outline-none"
                                />
                            </template>
                            <button
                                @click="removeHeader(index)"
                                :disabled="!canDeleteHeader(header)"
                                type="button"
                                :class="!canDeleteHeader(header) ? 'opacity-50 cursor-not-allowed' : 'hover:text-red-600 hover:bg-red-100'"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-red-500 transition-colors duration-100 rounded-md focus:outline-none bg-red-50"
                                :title="!canDeleteHeader(header) ? 'This header cannot be deleted' : 'Delete header'"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </template>
                </div>
                <button
                    @click="addHeader()"
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-neutral-500 transition-colors duration-100 rounded-md focus:outline-none bg-neutral-50 hover:text-neutral-600 hover:bg-neutral-100"
                >
                    + Add Header
                </button>
            </div>

            <!-- Params Tab -->
            <div id="tab-params" role="tabpanel" x-show="activeTab === 'params'" class="p-6 space-y-3">
                <template x-for="(value, key) in pathParams" :key="key">
                    <div class="flex items-center gap-3">
                        <label class="w-32 text-sm font-medium text-gray-700 dark:text-gray-300 font-mono" x-text="`{${key}}`"></label>
                        <input
                            x-model="pathParams[key]"
                            @input="persistEndpointState()"
                            type="text"
                            :placeholder="key"
                            class="flex-1 h-10 px-3 py-2 text-sm font-mono bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
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
                                class="w-4 h-4 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded text-neutral-900 dark:text-neutral-300 focus:outline-none"
                            />
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span x-text="field.name"></span>
                                <span x-show="field.required" class="text-red-600">*</span>
                                <span x-show="!field.required" class="text-gray-500 dark:text-gray-500 font-normal">(optional)</span>
                            </label>
                        </div>
                        <template x-if="field.inputType === 'text' && !field.isArray">
                            <div class="flex gap-2 items-center">
                                <input
                                    x-model="body[field.name]"
                                    @input="persistEndpointState()"
                                    type="text"
                                    :placeholder="field.validationHint || 'Enter ' + field.name"
                                    class="flex-1 h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none focus:outline-none"
                                />
                                <button
                                    type="button"
                                    @click="fakerTargetField = field.name; showFakerBrowser = true; fakerSearch = ''; fakerActiveCategory = 'all'"
                                    title="Insert faker expression"
                                    class="shrink-0 h-10 px-3 text-sm text-neutral-500 bg-neutral-50 border border-neutral-200 rounded-md hover:bg-neutral-100 hover:text-neutral-600"
                                ><i class="fas fa-bolt"></i></button>
                            </div>
                        </template>
                        <template x-if="field.isArray">
                            <div class="space-y-2">
                                <template x-for="(item, index) in (body[field.name] || [])" :key="index">
                                    <div class="flex gap-2 items-center">
                                        <input
                                            :value="item"
                                            @input="body[field.name][index] = $event.target.value; persistEndpointState()"
                                            type="text"
                                            :placeholder="field.validationHint || 'Enter value'"
                                            class="flex-1 h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none focus:outline-none"
                                        />
                                        <button
                                            @click="removeArrayItem(field.name, index)"
                                            type="button"
                                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-red-500 transition-colors duration-100 rounded-md focus:outline-none bg-red-50 hover:text-red-600 hover:bg-red-100"
                                        ><i class="fas fa-times"></i></button>
                                    </div>
                                </template>
                                <button
                                    @click="addArrayItem(field.name)"
                                    type="button"
                                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-neutral-500 transition-colors duration-100 rounded-md focus:outline-none bg-neutral-50 hover:text-neutral-600 hover:bg-neutral-100"
                                >+ Add Item</button>
                            </div>
                        </template>
                        <template x-if="field.inputType === 'textarea'">
                            <div class="flex gap-2">
                                <textarea
                                    x-model="body[field.name]"
                                    @input="persistEndpointState()"
                                    :placeholder="field.validationHint || 'Enter ' + field.name"
                                    rows="4"
                                    class="flex-1 px-3 py-2 text-sm font-mono bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                                ></textarea>
                                <button
                                    type="button"
                                    @click="fakerTargetField = field.name; showFakerBrowser = true; fakerSearch = ''; fakerActiveCategory = 'all'"
                                    title="Insert faker expression"
                                    class="shrink-0 h-10 px-3 text-sm text-neutral-500 bg-neutral-50 border border-neutral-200 rounded-md hover:bg-neutral-100 hover:text-neutral-600 self-start"
                                ><i class="fas fa-bolt"></i></button>
                            </div>
                        </template>
                        <template x-if="field.inputType === 'number'">
                            <input
                                x-model="body[field.name]"
                                @input="persistEndpointState()"
                                type="number"
                                :placeholder="field.validationHint || 'Enter ' + field.name"
                                class="w-full h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                            />
                        </template>
                        <template x-if="field.inputType === 'checkbox'">
                            <input
                                x-model="body[field.name]"
                                @change="persistEndpointState()"
                                type="checkbox"
                                class="h-4 w-4 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:checked:bg-blue-600"
                            />
                        </template>
                        <template x-if="field.inputType === 'datetime-local'">
                            <input
                                x-model="body[field.name]"
                                @input="persistEndpointState()"
                                type="datetime-local"
                                class="w-full h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                            />
                        </template>
                        <template x-if="field.inputType === 'file'">
                            <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
                                <input
                                    @change="body[field.name] = $event.target.files[0]; persistEndpointState()"
                                    type="file"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                />
                                <div class="text-center pointer-events-none">
                                    <i class="fas fa-cloud-arrow-up text-gray-400 text-2xl mb-2"></i>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <template x-if="!body[field.name]">
                                            Click to browse or drag file here
                                        </template>
                                        <template x-if="body[field.name]">
                                            <span x-text="body[field.name].name" class="font-medium text-gray-700 dark:text-gray-300"></span>
                                        </template>
                                    </p>
                                </div>
                            </div>
                        </template>
                        <!-- Simple select for In attribute fields -->
                        <template x-if="field.inputType === 'select' && field.isInAttribute">
                            <select
                                x-model="body[field.name]"
                                @change="persistEndpointState()"
                                class="w-full h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                            >
                                <option value="">-- Select --</option>
                                <template x-for="enumCase in field.enumCases || []" :key="enumCase">
                                    <option :value="enumCase" x-text="enumCase"></option>
                                </template>
                            </select>
                        </template>

                        <!-- Pines select for BackedEnum fields -->
                        <template x-if="field.inputType === 'select' && !field.isInAttribute">
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
                                    type="button"
                                    class="relative min-h-[38px] flex items-center justify-between w-full py-2 pl-3 pr-10 text-left bg-white dark:bg-gray-800 border rounded-md shadow-sm cursor-default border-gray-300 dark:border-gray-600 dark:text-gray-300 focus:outline-none text-sm">
                                    <span x-text="selectedItem && selectedItem.value ? selectedItem.title : '-- Select --'" class="truncate text-gray-500"></span>
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </span>
                                </button>
                                <ul x-show="selectOpen" x-ref="selectableItemsList" @click.away="selectOpen = false"
                                    x-transition:enter="transition ease-out duration-50"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100"
                                    :class="{ 'bottom-0 mb-10': selectDropdownPosition === 'top', 'top-0 mt-10': selectDropdownPosition === 'bottom' }"
                                    class="absolute z-10 w-full py-1 overflow-auto text-sm bg-white dark:bg-gray-800 rounded-md shadow-md max-h-56 ring-1 ring-black dark:ring-white ring-opacity-5 dark:ring-opacity-10 focus:outline-none"
                                    x-cloak>
                                    <template x-for="item in selectableItems" :key="item.value">
                                        <li @click="selectedItem = item; selectOpen = false; $refs.selectButton.focus();"
                                            :id="item.value + '-' + selectId"
                                            :class="{ 'bg-neutral-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100': selectableItemIsActive(item) }"
                                            @mousemove="selectableItemActive = item"
                                            class="relative flex items-center h-full py-2 pl-8 pr-4 text-gray-700 dark:text-gray-300 cursor-default select-none">
                                            <i x-show="selectedItem && selectedItem.value == item.value" class="fas fa-check absolute left-0 ml-2 text-neutral-400 dark:text-neutral-500" style="font-size: 1rem;"></i>
                                            <span class="block font-medium truncate" x-text="item.title"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>
                        <template x-if="field.isNested">
                            <details class="rounded border border-gray-300 dark:border-gray-600 p-3 dark:bg-gray-800">
                                <summary class="cursor-pointer font-medium text-gray-700 dark:text-gray-300">Expand <span x-text="field.nestedDtoClass"></span></summary>
                                <div class="mt-3 space-y-4 pl-4">
                                    <template x-for="nestedField in field.nestedFields || []" :key="nestedField.name">
                                        <div>
                                            <div class="flex items-center gap-2 mb-2">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    <span x-text="nestedField.name"></span>
                                                    <span x-show="nestedField.required" class="text-red-600">*</span>
                                                    <span x-show="!nestedField.required" class="text-gray-500 dark:text-gray-500 font-normal">(optional)</span>
                                                </label>
                                            </div>

                                            <template x-if="nestedField.inputType === 'text'">
                                                <input
                                                    :value="getNestedValue(field.name, nestedField.name)"
                                                    @input="setNestedValue(field.name, nestedField.name, $event.target.value)"
                                                    type="text"
                                                    :placeholder="nestedField.validationHint || 'Enter ' + nestedField.name"
                                                    class="w-full h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                                                />
                                            </template>

                                            <template x-if="nestedField.inputType === 'textarea'">
                                                <textarea
                                                    :value="getNestedValue(field.name, nestedField.name)"
                                                    @input="setNestedValue(field.name, nestedField.name, $event.target.value)"
                                                    :placeholder="nestedField.validationHint || 'Enter ' + nestedField.name"
                                                    rows="3"
                                                    class="w-full px-3 py-2 text-sm font-mono bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                                                ></textarea>
                                            </template>

                                            <template x-if="nestedField.inputType === 'number'">
                                                <input
                                                    :value="getNestedValue(field.name, nestedField.name)"
                                                    @input="setNestedValue(field.name, nestedField.name, $event.target.value)"
                                                    type="number"
                                                    :placeholder="nestedField.validationHint || 'Enter ' + nestedField.name"
                                                    class="w-full h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                                                />
                                            </template>

                                            <template x-if="nestedField.inputType === 'checkbox'">
                                                <input
                                                    :checked="getNestedValue(field.name, nestedField.name)"
                                                    @change="setNestedValue(field.name, nestedField.name, $event.target.checked)"
                                                    type="checkbox"
                                                    class="h-4 w-4 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:checked:bg-blue-600"
                                                />
                                            </template>

                                            <template x-if="nestedField.inputType === 'datetime-local'">
                                                <input
                                                    :value="getNestedValue(field.name, nestedField.name)"
                                                    @input="setNestedValue(field.name, nestedField.name, $event.target.value)"
                                                    type="datetime-local"
                                                    class="w-full h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                                                />
                                            </template>

                                            <template x-if="nestedField.inputType === 'file'">
                                                <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
                                                    <input
                                                        @change="setNestedValue(field.name, nestedField.name, $event.target.files[0])"
                                                        type="file"
                                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                                    />
                                                    <div class="text-center pointer-events-none">
                                                        <i class="fas fa-cloud-arrow-up text-gray-400 text-2xl mb-2"></i>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                                            <template x-if="!getNestedValue(field.name, nestedField.name)">
                                                                Click to browse or drag file here
                                                            </template>
                                                            <template x-if="getNestedValue(field.name, nestedField.name)">
                                                                <span x-text="getNestedValue(field.name, nestedField.name).name" class="font-medium text-gray-700 dark:text-gray-300"></span>
                                                            </template>
                                                        </p>
                                                    </div>
                                                </div>
                                            </template>

                                            <template x-if="nestedField.inputType === 'select'">
                                                <select
                                                    :value="getNestedValue(field.name, nestedField.name)"
                                                    @change="setNestedValue(field.name, nestedField.name, $event.target.value)"
                                                    class="w-full h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 dark:text-gray-200"
                                                >
                                                    <option value="">-- Select --</option>
                                                    <template x-for="enumCase in nestedField.enumCases || []" :key="enumCase">
                                                        <option :value="enumCase" x-text="enumCase"></option>
                                                    </template>
                                                </select>
                                            </template>
                                        </div>
                                    </template>
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
                                class="w-4 h-4 bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded text-neutral-900 dark:text-neutral-300 focus:outline-none"
                            />
                            <input
                                x-model="param.key"
                                @change="persistEndpointState()"
                                type="text"
                                placeholder="Key"
                                class="flex-1 h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none focus:outline-none"
                            />
                            <input
                                x-model="param.value"
                                @change="persistEndpointState()"
                                type="text"
                                placeholder="Value"
                                class="flex-1 h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 ring-offset-background placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none focus:outline-none"
                            />
                            <button
                                @click="removeQueryParam(index)"
                                type="button"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-red-500 transition-colors duration-100 rounded-md focus:outline-none bg-red-50 hover:text-red-600 hover:bg-red-100"
                            >
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </template>
                </div>
                <button
                    @click="addQueryParam()"
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-neutral-500 transition-colors duration-100 rounded-md focus:outline-none bg-neutral-50 hover:text-neutral-600 hover:bg-neutral-100"
                >
                    + Add Query Param
                </button>
            </div>

            <!-- Auth Tab -->
            <div id="tab-auth" role="tabpanel" x-show="activeTab === 'auth'" class="p-6 space-y-4">
                <!-- Auth Type Selector -->
                <div class="flex gap-2">
                    <button
                        @click="auth.type = 'none'"
                        type="button"
                        :class="auth.type === 'none' ? 'bg-neutral-200 dark:bg-gray-600 text-gray-900 dark:text-gray-100 font-semibold' : 'bg-neutral-50 dark:bg-gray-800 text-neutral-500 dark:text-gray-400 hover:bg-neutral-100 dark:hover:bg-gray-700'"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide transition-colors duration-100 rounded-md focus:outline-none"
                    >
                        None
                    </button>
                    <button
                        @click="auth.type = 'bearer'"
                        type="button"
                        :class="auth.type === 'bearer' ? 'bg-neutral-200 dark:bg-gray-600 text-gray-900 dark:text-gray-100 font-semibold' : 'bg-neutral-50 dark:bg-gray-800 text-neutral-500 dark:text-gray-400 hover:bg-neutral-100 dark:hover:bg-gray-700'"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide transition-colors duration-100 rounded-md focus:outline-none"
                    >
                        Bearer
                    </button>
                    <button
                        @click="auth.type = 'basic'"
                        type="button"
                        :class="auth.type === 'basic' ? 'bg-neutral-200 dark:bg-gray-600 text-gray-900 dark:text-gray-100 font-semibold' : 'bg-neutral-50 dark:bg-gray-800 text-neutral-500 dark:text-gray-400 hover:bg-neutral-100 dark:hover:bg-gray-700'"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide transition-colors duration-100 rounded-md focus:outline-none"
                    >
                        Basic Auth
                    </button>
                </div>

                <!-- Bearer Token -->
                <div x-show="auth.type === 'bearer'" class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Token</label>
                    <input
                        x-model="auth.bearer"
                        type="text"
                        placeholder="Enter token (without Bearer prefix)"
                        class="w-full h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                    />
                </div>

                <!-- Basic Auth -->
                <div x-show="auth.type === 'basic'" class="space-y-3">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                        <input
                            x-model="auth.basicUsername"
                            type="text"
                            placeholder="Username"
                            class="w-full h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                        />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <input
                            x-model="auth.basicPassword"
                            type="password"
                            placeholder="Password"
                            class="w-full h-10 px-3 py-2 text-sm bg-white dark:bg-gray-800 border rounded-md border-neutral-300 dark:border-gray-600 placeholder:text-neutral-500 dark:placeholder:text-gray-500 dark:text-gray-200 focus:border-neutral-300 focus:outline-none"
                        />
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex items-center gap-3">
                    <button
                        @click="applyAuth()"
                        x-show="authHasChanges() || authJustSaved"
                        type="button"
                        :class="authJustSaved ? 'text-green-600 bg-green-50 hover:text-green-700 hover:bg-green-100' : 'text-blue-500 bg-blue-50 hover:text-blue-600 hover:bg-blue-100'"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide transition-colors duration-100 rounded-md focus:outline-none"
                        x-cloak
                    >
                        <span x-show="!authJustSaved">Save</span>
                        <span x-show="authJustSaved">Saved!</span>
                    </button>
                </div>
            </div>

        </div>

        <!-- Action Buttons -->
        <div class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-6 py-4 flex gap-3">
            <button
                @click="sendRequest()"
                :disabled="loading"
                type="button"
                class="flex-1 inline-flex items-center justify-center px-4 py-3 text-sm font-medium tracking-wide text-green-500 transition-colors duration-100 rounded-md focus:outline-none bg-green-50 hover:text-green-600 hover:bg-green-100 disabled:opacity-50"
            >
                <span x-show="!loading">Send Request</span>
                <span x-show="loading">Sending...</span>
            </button>
            <button
                @click="resetAllFields()"
                type="button"
                title="Clear all form fields"
                class="inline-flex items-center justify-center px-4 py-3 text-sm font-medium tracking-wide text-gray-500 transition-colors duration-100 rounded-md focus:outline-none bg-gray-100 hover:text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300"
            >
                <i class="fas fa-refresh"></i>
            </button>
        </div>
    </div>
</div>
