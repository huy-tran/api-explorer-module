<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Explorer — {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Noto+Serif+JP:wght@200..900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Fuse.js for fuzzy search -->
    <script src="https://cdn.jsdelivr.net/npm/fuse.js/dist/fuse.js"></script>
</head>
<body class="bg-gray-50" x-data="apiExplorer()" x-init="init()" @keydown.enter.ctrl="sendRequest()">
    <!-- Top Bar -->
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex h-16 items-center justify-between px-6">
            <h1 class="text-2xl font-bold text-gray-900">API Explorer</h1>
            <div class="flex items-center gap-4">
                <!-- Environment Selector (Pines) -->
                <div class="flex items-center gap-2">
                    <div
                        @keydown.escape="if (envSelOpen) envSelOpen = false"
                        @keydown.down.prevent="if (envSelOpen) { envSelActiveNext(); } else { envSelOpen = true; }"
                        @keydown.up.prevent="if (envSelOpen) { envSelActivePrevious(); } else { envSelOpen = true; }"
                        @keydown.enter="envSelSelectedItem = envSelActiveItem; envSelOpen = false;"
                        @keydown="envSelKeydown($event)"
                        class="relative w-56"
                    >
                        <button @click="envSelOpen = !envSelOpen"
                            :class="{ 'ring-2 ring-offset-2 ring-neutral-400': !envSelOpen }"
                            type="button"
                            class="relative min-h-[38px] flex items-center justify-between w-full py-2 pl-3 pr-10 text-left bg-white border rounded-md shadow-sm cursor-default border-gray-300 focus:outline-none text-sm">
                            <span x-text="envSelSelectedItem && envSelSelectedItem.value ? envSelSelectedItem.title : 'No Environment'" class="truncate text-gray-700"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 text-gray-400"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.55.24l3.25 3.5a.75.75 0 11-1.1 1.02L10 4.852 7.3 7.76a.75.75 0 01-1.1-1.02l3.25-3.5A.75.75 0 0110 3zm-3.76 9.2a.75.75 0 011.06.04l2.7 2.908 2.7-2.908a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0l-3.25-3.5a.75.75 0 01.04-1.06z" clip-rule="evenodd"/></svg>
                            </span>
                        </button>
                        <ul x-show="envSelOpen" @click.away="envSelOpen = false"
                            x-transition:enter="transition ease-out duration-50"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100"
                            :class="{ 'bottom-0 mb-10': envSelDropdownPosition === 'top', 'top-0 mt-10': envSelDropdownPosition === 'bottom' }"
                            class="absolute z-10 w-full py-1 overflow-auto text-sm bg-white rounded-md shadow-md max-h-56 ring-1 ring-black ring-opacity-5 focus:outline-none"
                            x-cloak>
                            <template x-for="item in envSelItems" :key="item.value">
                                <li @click="envSelSelectedItem = item; envSelOpen = false;"
                                    :id="item.value + '-' + envSelId"
                                    :class="{ 'bg-neutral-100 text-gray-900': envSelIsActive(item) }"
                                    @mousemove="envSelActiveItem = item"
                                    class="relative flex items-center h-full py-2 pl-8 pr-4 text-gray-700 cursor-default select-none">
                                    <svg x-show="envSelSelectedItem && envSelSelectedItem.value == item.value" class="absolute left-0 w-4 h-4 ml-2 stroke-current text-neutral-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span class="block font-medium truncate" x-text="item.title"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <button
                        @click="activeEnv ? openEditEnv(activeEnv) : openNewEnv()"
                        type="button"
                        title="Manage environments"
                        class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium tracking-wide text-neutral-500 transition-colors duration-100 rounded-md focus:ring-2 focus:ring-offset-2 focus:ring-neutral-100 bg-neutral-50 hover:text-neutral-600 hover:bg-neutral-100"
                    >
                        ⚙
                    </button>
                </div>
                <div class="text-sm text-gray-600">
                    @if ($cacheEnabled)
                        Last scanned: <span x-text="formatTime()"></span>
                    @else
                        Cache disabled (live scan)
                    @endif
                </div>
                @if ($cacheEnabled)
                    <form method="POST" action="{{ route('api-explorer.cache.purge') }}" style="display: inline;">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-red-500 transition-colors duration-100 rounded-md focus:ring-2 focus:ring-offset-2 focus:ring-red-100 bg-red-50 hover:text-red-600 hover:bg-red-100">
                            Purge Cache
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex h-[calc(100vh-64px)]">
        <!-- Sidebar -->
        @include('api-explorer::partials.sidebar')

        <!-- Main Panel -->
        <main class="flex flex-1 flex-col overflow-hidden">
            <!-- Endpoint Header -->
            <template x-if="active">
                <div class="border-b border-gray-200 bg-white px-6 py-3">
                    <div class="flex items-center gap-3">
                        <span :class="methodColor(active.method.split('|')[0])" class="rounded px-3 py-1 font-bold text-sm">
                            <span x-text="active.method.split('|')[0]"></span>
                        </span>
                        <code x-text="getDisplayUri()" class="text-sm font-mono text-gray-700"></code>
                    </div>
                </div>
            </template>

            <!-- Request and Response Columns -->
            <div class="flex flex-1 overflow-hidden">
                <!-- Request Panel (Left) -->
                @include('api-explorer::partials.request')

                <!-- Response Panel (Right) -->
                @include('api-explorer::partials.response')
            </div>
        </main>
    </div>

    <!-- Data Injection -->
    <script>
        window.__apiExplorerEndpoints = @json($endpoints);
        window.__apiExplorerAppUrl = @json($appUrl);
        window.__apiExplorerScannedAt = @json($scannedAt?->toIso8601String());
        window.__apiExplorerCacheEnabled = @json($cacheEnabled);
    </script>

    <!-- Alpine.js Component -->
    <script>
        function piinesSelect(items, initialValue = null) {
            return {
                selectOpen: false,
                selectedItem: items.find(i => i.value == initialValue) || null,
                selectableItems: items,
                selectableItemActive: null,
                selectId: Math.random().toString(36).substr(2, 9),
                selectKeydownValue: '',
                selectKeydownTimeout: 1000,
                selectKeydownClearTimeout: null,
                selectDropdownPosition: 'bottom',

                selectableItemIsActive(item) {
                    return this.selectableItemActive && this.selectableItemActive.value == item.value;
                },
                selectableItemActiveNext() {
                    let index = this.selectableItems.indexOf(this.selectableItemActive);
                    if (index < this.selectableItems.length - 1) {
                        this.selectableItemActive = this.selectableItems[index + 1];
                        this.selectScrollToActiveItem();
                    }
                },
                selectableItemActivePrevious() {
                    let index = this.selectableItems.indexOf(this.selectableItemActive);
                    if (index > 0) {
                        this.selectableItemActive = this.selectableItems[index - 1];
                        this.selectScrollToActiveItem();
                    }
                },
                selectScrollToActiveItem() {
                    if (this.selectableItemActive) {
                        const activeEl = document.getElementById(this.selectableItemActive.value + '-' + this.selectId);
                        if (!activeEl) return;
                        const newScrollPos = (activeEl.offsetTop + activeEl.offsetHeight) - this.$refs.selectableItemsList.offsetHeight;
                        this.$refs.selectableItemsList.scrollTop = newScrollPos > 0 ? newScrollPos : 0;
                    }
                },
                selectKeydown(event) {
                    if (event.keyCode >= 65 && event.keyCode <= 90) {
                        this.selectKeydownValue += event.key;
                        const best = this.selectItemsFindBestMatch();
                        if (best) {
                            if (this.selectOpen) {
                                this.selectableItemActive = best;
                                this.selectScrollToActiveItem();
                            } else {
                                this.selectedItem = this.selectableItemActive = best;
                            }
                        }
                        clearTimeout(this.selectKeydownClearTimeout);
                        this.selectKeydownClearTimeout = setTimeout(() => {
                            this.selectKeydownValue = '';
                        }, this.selectKeydownTimeout);
                    }
                },
                selectItemsFindBestMatch() {
                    const typed = this.selectKeydownValue.toLowerCase();
                    let bestMatch = null, bestMatchIndex = -1;
                    for (let i = 0; i < this.selectableItems.length; i++) {
                        if (this.selectableItems[i].disabled) continue;
                        const idx = this.selectableItems[i].title.toLowerCase().indexOf(typed);
                        if (idx > -1 && (bestMatchIndex === -1 || idx < bestMatchIndex)) {
                            bestMatch = this.selectableItems[i];
                            bestMatchIndex = idx;
                        }
                    }
                    return bestMatch;
                },
                selectPositionUpdate() {
                    const bottomPos = this.$refs.selectButton.getBoundingClientRect().top
                        + this.$refs.selectButton.offsetHeight
                        + parseInt(window.getComputedStyle(this.$refs.selectableItemsList).maxHeight);
                    this.selectDropdownPosition = window.innerHeight < bottomPos ? 'top' : 'bottom';
                },
            };
        }

        function apiExplorer() {
            return {
                grouped: window.__apiExplorerEndpoints,
                allEndpoints: [],
                active: null,
                baseUrl: '',
                headers: [],
                queryParams: [],
                body: {},
                pathParams: {},
                endpointState: {},
                activeTab: 'body',
                response: null,
                loading: false,
                sidebarWidth: 320,
                isResizing: false,
                resizeStartX: 0,

                // Environment variables
                environments: [],
                activeEnv: localStorage.getItem('apiExplorer.activeEnv') || null,
                activeEnvBaseUrl: null,
                envVars: {},
                showEnvManager: false,
                editingEnv: null,
                editingEnvName: '',
                editingEnvBaseUrl: '',
                editingEnvVars: [],

                // Environment selector (Pines)
                envSelOpen: false,
                envSelActiveItem: null,
                envSelItems: [],
                envSelSelectedItem: null,
                envSelKeydownValue: '',
                envSelKeydownTimeout: 1000,
                envSelKeydownClearTimeout: null,
                envSelDropdownPosition: 'bottom',
                envSelId: Math.random().toString(36).substr(2, 9),

                // Search functionality
                searchQuery: '',
                filteredEndpoints: [],
                filteredGrouped: {},
                fuse: null,

                init() {
                    this.baseUrl = window.__apiExplorerAppUrl;
                    try {
                        this.headers = JSON.parse(localStorage.getItem('apiExplorer.headers') || '[]');
                    } catch {
                        this.headers = [];
                    }
                    try {
                        this.endpointState = JSON.parse(localStorage.getItem('apiExplorer.endpointState') || '{}');
                    } catch {
                        this.endpointState = {};
                    }
                    // Migrate old global queryParams (remove it - global params have no endpoint context)
                    localStorage.removeItem('apiExplorer.queryParams');
                    this.queryParams = [];
                    // Restore sidebar width from localStorage
                    const savedWidth = localStorage.getItem('apiExplorer.sidebarWidth');
                    if (savedWidth) {
                        this.sidebarWidth = parseInt(savedWidth, 10);
                    }
                    // Sort the grouped data
                    this.grouped = this.sortGroupedData(this.grouped);
                    // Flatten nested structure for lookup
                    this.allEndpoints = this.flattenEndpoints(this.grouped);

                    // Initialize Fuse.js for fuzzy search
                    this.fuse = new Fuse(this.allEndpoints, {
                        keys: ['name', 'method', 'path'],
                        threshold: 0.3,
                        includeScore: true,
                    });

                    // Load environments
                    this.loadEnvironments();
                    if (this.activeEnv) {
                        this.loadEnvVars(this.activeEnv);
                    }

                    // Initialize environment selector (Pines)
                    this.envSelUpdateItems();
                    if (this.activeEnv) {
                        this.envSelSelectedItem = { title: this.activeEnv, value: this.activeEnv };
                    } else {
                        this.envSelSelectedItem = { title: 'No Environment', value: '' };
                    }
                    // Watch for selection changes
                    this.$watch('envSelSelectedItem', (item) => {
                        if (item) {
                            this.selectEnv(item.value);
                        }
                    });
                    // Watch for open state to manage active item
                    this.$watch('envSelOpen', () => {
                        if (this.envSelOpen) {
                            if (!this.envSelActiveItem) {
                                this.envSelActiveItem = this.envSelItems[0] || null;
                            }
                        }
                    });
                },

                sortGroupedData(grouped) {
                    const sorted = {};
                    const sortedKeys = Object.keys(grouped).sort();

                    for (const key of sortedKeys) {
                        const group = grouped[key];
                        sorted[key] = {};

                        // Sort endpoints if they exist
                        if (group.__endpoints && Array.isArray(group.__endpoints)) {
                            sorted[key].__endpoints = this.sortEndpoints(group.__endpoints);
                        }

                        // Sort and recursively process nested groups
                        const nestedKeys = Object.keys(group)
                            .filter(k => k !== '__endpoints')
                            .sort();

                        for (const nestedKey of nestedKeys) {
                            const nestedGroup = group[nestedKey];
                            sorted[key][nestedKey] = {};

                            // Sort endpoints in nested group
                            if (nestedGroup.__endpoints && Array.isArray(nestedGroup.__endpoints)) {
                                sorted[key][nestedKey].__endpoints = this.sortEndpoints(nestedGroup.__endpoints);
                            }
                        }
                    }

                    return sorted;
                },

                sortEndpoints(endpoints) {
                    const methodOrder = {
                        'GET': 1,
                        'POST': 2,
                        'PUT': 3,
                        'PATCH': 4,
                        'DELETE': 5
                    };

                    return [...endpoints].sort((a, b) => {
                        const methodA = a.method.split('|')[0];
                        const methodB = b.method.split('|')[0];

                        const orderA = methodOrder[methodA] || 99;
                        const orderB = methodOrder[methodB] || 99;

                        // First sort by HTTP method (REST order: GET, POST, PUT, PATCH, DELETE)
                        if (orderA !== orderB) {
                            return orderA - orderB;
                        }

                        // Then sort alphabetically by name
                        return a.name.localeCompare(b.name);
                    });
                },

                flattenEndpoints(grouped) {
                    const endpoints = [];
                    const traverse = (obj) => {
                        if (obj.__endpoints && Array.isArray(obj.__endpoints)) {
                            endpoints.push(...obj.__endpoints);
                        }
                        for (const [key, value] of Object.entries(obj)) {
                            if (key !== '__endpoints' && typeof value === 'object' && value !== null) {
                                traverse(value);
                            }
                        }
                    };
                    traverse(grouped);
                    return endpoints;
                },

                setActiveAccordion(id) {
                    this.activeAccordion = (this.activeAccordion == id) ? '' : id;
                },

                performSearch() {
                    if (!this.searchQuery) {
                        this.filteredGrouped = {};
                        this.filteredEndpoints = [];
                        return;
                    }

                    const results = this.fuse.search(this.searchQuery);
                    this.filteredEndpoints = results.map(result => result.item);

                    // Build filtered tree structure maintaining hierarchy
                    this.filteredGrouped = this.buildFilteredTree(this.grouped, this.filteredEndpoints);
                },

                buildFilteredTree(grouped, matchingEndpoints) {
                    const matchingEndpointsSet = new Set(matchingEndpoints.map(ep => ep.name));
                    const filtered = {};

                    for (const [groupName, groupData] of Object.entries(grouped)) {
                        const filteredGroup = {};
                        let hasMatchingEndpoints = false;

                        // Filter endpoints at this level
                        if (groupData.__endpoints && Array.isArray(groupData.__endpoints)) {
                            const filtered__endpoints = groupData.__endpoints.filter(ep =>
                                matchingEndpointsSet.has(ep.name)
                            );
                            if (filtered__endpoints.length > 0) {
                                filteredGroup.__endpoints = filtered__endpoints;
                                hasMatchingEndpoints = true;
                            }
                        }

                        // Recursively filter nested groups
                        for (const [nestedKey, nestedData] of Object.entries(groupData)) {
                            if (nestedKey === '__endpoints') continue;

                            if (typeof nestedData === 'object' && nestedData !== null) {
                                const filteredNested = this.filterNestedGroup(nestedData, matchingEndpointsSet);
                                if (Object.keys(filteredNested).length > 0) {
                                    filteredGroup[nestedKey] = filteredNested;
                                    hasMatchingEndpoints = true;
                                }
                            }
                        }

                        // Only include group if it has matching endpoints
                        if (hasMatchingEndpoints) {
                            filtered[groupName] = filteredGroup;
                        }
                    }

                    return filtered;
                },

                filterNestedGroup(nestedData, matchingEndpointsSet) {
                    const filtered = {};

                    if (nestedData.__endpoints && Array.isArray(nestedData.__endpoints)) {
                        const filtered__endpoints = nestedData.__endpoints.filter(ep =>
                            matchingEndpointsSet.has(ep.name)
                        );
                        if (filtered__endpoints.length > 0) {
                            filtered.__endpoints = filtered__endpoints;
                        }
                    }

                    // Recursively process deeper nested groups
                    for (const [key, value] of Object.entries(nestedData)) {
                        if (key === '__endpoints') continue;

                        if (typeof value === 'object' && value !== null) {
                            const filteredNested = this.filterNestedGroup(value, matchingEndpointsSet);
                            if (Object.keys(filteredNested).length > 0) {
                                filtered[key] = filteredNested;
                            }
                        }
                    }

                    return filtered;
                },

                startResize(e) {
                    this.isResizing = true;
                    this.resizeStartX = e.clientX;
                    const startWidth = this.sidebarWidth;

                    const handleMouseMove = (moveEvent) => {
                        const delta = moveEvent.clientX - this.resizeStartX;
                        const newWidth = Math.max(200, startWidth + delta);
                        this.sidebarWidth = newWidth;
                        localStorage.setItem('apiExplorer.sidebarWidth', newWidth);
                    };

                    const handleMouseUp = () => {
                        this.isResizing = false;
                        document.removeEventListener('mousemove', handleMouseMove);
                        document.removeEventListener('mouseup', handleMouseUp);
                    };

                    document.addEventListener('mousemove', handleMouseMove);
                    document.addEventListener('mouseup', handleMouseUp);
                },

                selectEndpoint(endpoint) {
                    // Save current endpoint state before switching
                    if (this.active) {
                        this.persistEndpointState();
                    }

                    // Build fresh defaults for the new endpoint
                    const defaultBody = this.buildDefaultBody(endpoint.fields);
                    const defaultPathParams = this.extractPathParams(endpoint.uri);

                    // Load saved state for the new endpoint (if any) and merge over defaults
                    const saved = this.endpointState[endpoint.name];
                    if (saved) {
                        // Merge body: defaults define schema, saved values fill in
                        this.body = Object.assign({}, defaultBody, saved.body ?? {});

                        // Merge pathParams: only restore values for keys still in the URI
                        const merged = { ...defaultPathParams };
                        if (saved.pathParams) {
                            for (const key of Object.keys(merged)) {
                                if (saved.pathParams[key] !== undefined) {
                                    merged[key] = saved.pathParams[key];
                                }
                            }
                        }
                        this.pathParams = merged;

                        // Restore queryParams exactly
                        this.queryParams = Array.isArray(saved.queryParams) ? saved.queryParams : [];
                    } else {
                        this.body = defaultBody;
                        this.pathParams = defaultPathParams;
                        this.queryParams = [];
                    }

                    this.active = endpoint;
                    this.response = null;
                    this.activeTab = Object.keys(this.pathParams).length > 0 ? 'params' : 'body';
                    Alpine.nextTick(() => {
                        this.repositionTabMarker();
                    });
                },

                repositionTabMarker() {
                    const tabsContainer = document.querySelector('[x-ref="tabsContainer"]');
                    if (!tabsContainer) return;
                    const activeBtn = tabsContainer.querySelector(`[data-tab='${this.activeTab}']`);
                    const marker = tabsContainer.querySelector('[x-ref="tabMarker"]');
                    if (activeBtn && marker && activeBtn.offsetParent !== null) {
                        marker.style.width = activeBtn.offsetWidth + 'px';
                        marker.style.height = activeBtn.offsetHeight + 'px';
                        marker.style.left = activeBtn.offsetLeft + 'px';
                    }
                },

                extractPathParams(uri) {
                    const params = {};
                    const matches = uri.match(/\{(\w+)\}/g) || [];
                    matches.forEach(m => {
                        const key = m.slice(1, -1);
                        params[key] = '';
                    });
                    return params;
                },

                resolveUri() {
                    if (!this.active) return '';
                    let uri = this.active.uri;
                    for (const [key, value] of Object.entries(this.pathParams)) {
                        const resolved = this.applyVars(value);
                        uri = uri.replace(`{${key}}`, resolved ? encodeURIComponent(resolved) : `{${key}}`);
                    }
                    return uri;
                },

                getDisplayUri() {
                    let uri = this.resolveUri();
                    const parts = [];
                    // Add enabled query params with variable substitution (unencoded for display)
                    this.queryParams.forEach(qp => {
                        if (qp.enabled !== false && qp.key && qp.value) {
                            const resolvedKey = this.applyVars(qp.key);
                            const resolvedValue = this.applyVars(qp.value);
                            parts.push(`${resolvedKey}=${resolvedValue}`);
                        }
                    });
                    if (parts.length > 0) {
                        uri += '?' + parts.join('&');
                    }
                    return uri;
                },

                buildDefaultBody(fields, prefix = '') {
                    const body = {};
                    fields.forEach(field => {
                        const fullName = prefix ? `${prefix}.${field.name}` : field.name;
                        if (field.isNested) {
                            body[field.name] = this.buildDefaultBody(field.nestedFields, fullName);
                        } else if (field.defaultValue !== null) {
                            body[field.name] = field.defaultValue;
                        } else if (field.inputType === 'checkbox') {
                            body[field.name] = false;
                        } else if (field.isArray) {
                            body[field.name] = [];
                        } else {
                            body[field.name] = '';
                        }
                    });
                    return body;
                },

                async sendRequest() {
                    if (!this.active) return;

                    this.loading = true;
                    this.response = null;

                    try {
                        const startTime = performance.now();
                        const options = this.buildRequestOptions();

                        const uri = this.resolveUri() + (options.url || '');
                        const resolvedBaseUrl = this.applyVars(this.baseUrl);
                        const response = await fetch(resolvedBaseUrl + uri, options);
                        const endTime = performance.now();
                        const data = await response.text();

                        let parsedBody;
                        try {
                            parsedBody = JSON.parse(data);
                        } catch {
                            parsedBody = data;
                        }

                        const headers = {};
                        response.headers.forEach((value, key) => {
                            headers[key] = value;
                        });

                        this.response = {
                            status: response.status,
                            statusText: response.statusText,
                            time: Math.round(endTime - startTime),
                            headers,
                            body: parsedBody,
                        };

                        // Auto-detect and show token button
                        if (typeof parsedBody === 'object' && parsedBody !== null) {
                            const token = parsedBody.token || parsedBody.access_token || parsedBody.bearer_token;
                            if (token) {
                                this.response.token = token;
                            }
                        }
                    } catch (error) {
                        this.response = {
                            error: error.message,
                            status: 0,
                        };
                    } finally {
                        this.loading = false;
                    }
                },

                buildRequestOptions() {
                    const method = this.active.method.split('|')[0];
                    const options = {
                        method,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        },
                    };

                    // Add custom headers (with variable substitution)
                    this.headers.forEach(h => {
                        if (h.enabled !== false && h.key && h.value) {
                            const resolvedKey = this.applyVars(h.key);
                            const resolvedValue = this.applyVars(h.value);
                            options.headers[resolvedKey] = resolvedValue;
                        }
                    });

                    // Handle body for POST/PUT/PATCH
                    if (['POST', 'PUT', 'PATCH'].includes(method)) {
                        const hasFile = this.hasFileField(this.active.fields);
                        if (hasFile) {
                            const formData = new FormData();
                            this.flattenObject(this.body, formData);
                            options.body = formData;
                            delete options.headers['Content-Type'];
                        } else {
                            options.headers['Content-Type'] = 'application/json';
                            options.body = JSON.stringify(this.applyVarsToObject(this.body));
                        }
                    } else if (method === 'GET') {
                        // Serialize body and query params
                        const params = new URLSearchParams();
                        this.flattenForQuery(this.body, params);
                        // Add explicit query params with variable substitution
                        this.queryParams.forEach(qp => {
                            if (qp.enabled !== false && qp.key && qp.value) {
                                const resolvedKey = this.applyVars(qp.key);
                                const resolvedValue = this.applyVars(qp.value);
                                params.append(resolvedKey, resolvedValue);
                            }
                        });
                        if (params.toString()) {
                            options.url = '?' + params.toString();
                        }
                    }

                    return options;
                },

                flattenObject(obj, formData, prefix = '') {
                    Object.keys(obj).forEach(key => {
                        const value = obj[key];
                        const fullKey = prefix ? `${prefix}[${key}]` : key;

                        if (value instanceof File) {
                            formData.append(fullKey, value);
                        } else if (Array.isArray(value)) {
                            value.forEach((item, index) => {
                                formData.append(`${fullKey}[${index}]`, item);
                            });
                        } else if (typeof value === 'object' && value !== null) {
                            this.flattenObject(value, formData, fullKey);
                        } else {
                            formData.append(fullKey, value ?? '');
                        }
                    });
                },

                flattenForQuery(obj, params, prefix = '') {
                    Object.keys(obj).forEach(key => {
                        const value = obj[key];
                        const fullKey = prefix ? `${prefix}[${key}]` : key;

                        if (Array.isArray(value)) {
                            value.forEach((item, index) => {
                                params.append(`${fullKey}[${index}]`, item);
                            });
                        } else if (typeof value === 'object' && value !== null) {
                            this.flattenForQuery(value, params, fullKey);
                        } else {
                            params.append(fullKey, value ?? '');
                        }
                    });
                },

                hasFileField(fields) {
                    return fields.some(f => f.inputType === 'file' || (f.isNested && this.hasFileField(f.nestedFields)));
                },

                addHeader() {
                    this.headers.push({ key: '', value: '', enabled: true });
                    this.persistHeaders();
                },

                removeHeader(index) {
                    this.headers.splice(index, 1);
                    this.persistHeaders();
                },

                useAsToken(token) {
                    const existingIndex = this.headers.findIndex(h => h.key === 'Authorization');
                    if (existingIndex >= 0) {
                        this.headers[existingIndex].value = `Bearer ${token}`;
                        this.headers[existingIndex].enabled = true;
                    } else {
                        this.headers.push({ key: 'Authorization', value: `Bearer ${token}`, enabled: true });
                    }
                    this.persistHeaders();
                },

                persistHeaders() {
                    localStorage.setItem('apiExplorer.headers', JSON.stringify(this.headers));
                },

                addQueryParam() {
                    this.queryParams.push({ key: '', value: '', enabled: true });
                    this.persistEndpointState();
                },

                removeQueryParam(index) {
                    this.queryParams.splice(index, 1);
                    this.persistEndpointState();
                },

                persistEndpointState() {
                    if (!this.active) return;
                    this.endpointState[this.active.name] = {
                        body: this.body,
                        pathParams: this.pathParams,
                        queryParams: this.queryParams,
                    };
                    localStorage.setItem('apiExplorer.endpointState', JSON.stringify(this.endpointState));
                },

                formatTime() {
                    if (!window.__apiExplorerScannedAt) return 'never';
                    const scannedAt = new Date(window.__apiExplorerScannedAt);
                    const now = new Date();
                    const diffMs = now - scannedAt;
                    const diffMins = Math.floor(diffMs / 60000);
                    if (diffMins < 1) return 'just now';
                    if (diffMins < 60) return `${diffMins} min ago`;
                    const diffHours = Math.floor(diffMins / 60);
                    if (diffHours < 24) return `${diffHours} hour(s) ago`;
                    const diffDays = Math.floor(diffHours / 24);
                    return `${diffDays} day(s) ago`;
                },

                methodColor(method) {
                    const colors = {
                        GET: 'bg-green-100 text-green-700',
                        POST: 'bg-blue-100 text-blue-700',
                        PUT: 'bg-amber-100 text-amber-700',
                        PATCH: 'bg-purple-100 text-purple-700',
                        DELETE: 'bg-red-100 text-red-700',
                    };
                    return colors[method] || 'bg-gray-100 text-gray-700';
                },

                statusColor(status) {
                    if (status >= 200 && status < 300) return 'bg-green-100 text-green-700';
                    if (status >= 300 && status < 400) return 'bg-amber-100 text-amber-700';
                    return 'bg-red-100 text-red-700';
                },

                copyToClipboard(text) {
                    navigator.clipboard.writeText(JSON.stringify(text, null, 2));
                },

                highlightJson(obj) {
                    const json = JSON.stringify(obj, null, 2);
                    return this.syntaxHighlight(json);
                },

                syntaxHighlight(json) {
                    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                        var cls = 'number';
                        if (/^"/.test(match)) {
                            if (/:$/.test(match)) {
                                cls = 'key';
                            } else {
                                cls = 'string';
                            }
                        } else if (/true|false/.test(match)) {
                            cls = 'boolean';
                        } else if (/null/.test(match)) {
                            cls = 'null';
                        }
                        return '<span class="' + cls + '">' + match + '</span>';
                    });
                },

                // Environment management methods
                async loadEnvironments() {
                    try {
                        const res = await fetch('{{ route("api-explorer.environments.index") }}');
                        this.environments = await res.json();
                        this.envSelUpdateItems();
                    } catch (error) {
                        console.error('Failed to load environments:', error);
                    }
                },

                async loadEnvVars(name) {
                    if (!name) {
                        this.envVars = {};
                        this.activeEnvBaseUrl = null;
                        return;
                    }
                    try {
                        const res = await fetch(`{{ route("api-explorer.environments.index") }}/${encodeURIComponent(name)}`);
                        const data = await res.json();
                        this.envVars = data.vars;
                        this.activeEnvBaseUrl = data.baseUrl || null;
                        this.baseUrl = data.baseUrl || window.__apiExplorerAppUrl;
                    } catch (error) {
                        console.error('Failed to load environment:', error);
                        this.envVars = {};
                        this.activeEnvBaseUrl = null;
                    }
                },

                async selectEnv(name) {
                    this.activeEnv = name;
                    localStorage.setItem('apiExplorer.activeEnv', name || '');
                    if (!name) {
                        this.baseUrl = window.__apiExplorerAppUrl;
                        this.activeEnvBaseUrl = null;
                    }
                    await this.loadEnvVars(name);
                    // Update Pines selector only if it actually changed
                    const newItem = name
                        ? { title: name, value: name }
                        : { title: 'No Environment', value: '' };

                    if (this.envSelSelectedItem.value !== newItem.value) {
                        this.envSelSelectedItem = newItem;
                    }
                },

                // Environment Selector (Pines) methods
                envSelUpdateItems() {
                    this.envSelItems = [
                        { title: 'No Environment', value: '' },
                        ...this.environments.map(e => ({ title: e, value: e }))
                    ];
                },

                envSelIsActive(item) {
                    return this.envSelActiveItem && this.envSelActiveItem.value === item.value;
                },

                envSelActiveNext() {
                    let index = this.envSelItems.indexOf(this.envSelActiveItem);
                    if (index < this.envSelItems.length - 1) {
                        this.envSelActiveItem = this.envSelItems[index + 1];
                    }
                },

                envSelActivePrevious() {
                    let index = this.envSelItems.indexOf(this.envSelActiveItem);
                    if (index > 0) {
                        this.envSelActiveItem = this.envSelItems[index - 1];
                    }
                },

                envSelKeydown(event) {
                    if (event.keyCode >= 65 && event.keyCode <= 90) {
                        this.envSelKeydownValue += event.key;
                        const best = this.envSelFindBestMatch();
                        if (best) {
                            if (this.envSelOpen) {
                                this.envSelActiveItem = best;
                            } else {
                                this.envSelSelectedItem = this.envSelActiveItem = best;
                            }
                        }
                        clearTimeout(this.envSelKeydownClearTimeout);
                        this.envSelKeydownClearTimeout = setTimeout(() => {
                            this.envSelKeydownValue = '';
                        }, this.envSelKeydownTimeout);
                    }
                },

                envSelFindBestMatch() {
                    const typed = this.envSelKeydownValue.toLowerCase();
                    let bestMatch = null, bestMatchIndex = -1;
                    for (let i = 0; i < this.envSelItems.length; i++) {
                        const idx = this.envSelItems[i].title.toLowerCase().indexOf(typed);
                        if (idx > -1 && (bestMatchIndex === -1 || idx < bestMatchIndex)) {
                            bestMatch = this.envSelItems[i];
                            bestMatchIndex = idx;
                        }
                    }
                    return bestMatch;
                },

                applyVars(text) {
                    if (typeof text !== 'string') return text;
                    return text.replace(/\{\{(\w+)\}\}/g, (match, key) => {
                        return this.envVars[key] !== undefined ? this.envVars[key] : match;
                    });
                },

                applyVarsToObject(obj) {
                    if (typeof obj === 'string') return this.applyVars(obj);
                    if (Array.isArray(obj)) return obj.map(v => this.applyVarsToObject(v));
                    if (typeof obj === 'object' && obj !== null) {
                        const result = {};
                        for (const [k, v] of Object.entries(obj)) {
                            result[k] = this.applyVarsToObject(v);
                        }
                        return result;
                    }
                    return obj;
                },

                async saveEditingEnv() {
                    const vars = {};
                    this.editingEnvVars.forEach(row => {
                        if (row.key.trim()) {
                            vars[row.key.trim()] = row.value;
                        }
                    });

                    const isNew = !this.editingEnv;
                    const url = isNew
                        ? '{{ route("api-explorer.environments.store") }}'
                        : `{{ route("api-explorer.environments.index") }}/${encodeURIComponent(this.editingEnv)}`;

                    const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
                    console.log('Saving environment:', { isNew, name: this.editingEnvName, url, csrfToken: !!csrfToken });

                    try {
                        const res = await fetch(url, {
                            method: isNew ? 'POST' : 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ name: this.editingEnvName, baseUrl: this.editingEnvBaseUrl, vars }),
                        });

                        console.log('Save response status:', res.status);

                        if (res.ok) {
                            const responseData = await res.json();
                            console.log('Environment saved successfully:', responseData);
                            await this.loadEnvironments();
                            if (this.activeEnv === this.editingEnv || this.activeEnv === this.editingEnvName) {
                                await this.loadEnvVars(this.editingEnvName);
                            }
                            this.showEnvManager = false;
                            this.editingEnv = null;
                        } else {
                            const text = await res.text();
                            console.error('Save failed with status', res.status, ':', text);
                        }
                    } catch (error) {
                        console.error('Failed to save environment:', error.message);
                    }
                },

                async deleteEnv(name) {
                    try {
                        await fetch(`{{ route("api-explorer.environments.index") }}/${encodeURIComponent(name)}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        });

                        if (this.activeEnv === name) {
                            this.activeEnv = null;
                            this.envVars = {};
                            localStorage.removeItem('apiExplorer.activeEnv');
                        }
                        await this.loadEnvironments();
                    } catch (error) {
                        console.error('Failed to delete environment:', error);
                    }
                },

                async duplicateEnv(name) {
                    try {
                        // First load the environment data
                        await this.loadEnvVars(name);

                        // Prepare new environment with "[copy]" appended to name
                        this.editingEnv = null;
                        this.editingEnvName = `${name} [copy]`;
                        this.editingEnvBaseUrl = this.activeEnvBaseUrl || window.__apiExplorerAppUrl;
                        this.editingEnvVars = Object.entries(this.envVars).map(([key, value]) => ({ key, value }));

                        if (this.editingEnvVars.length === 0) {
                            this.editingEnvVars = [{ key: '', value: '' }];
                        }

                        this.showEnvManager = true;
                    } catch (error) {
                        console.error('Failed to duplicate environment:', error);
                    }
                },

                openNewEnv() {
                    this.editingEnv = null;
                    this.editingEnvName = '';
                    this.editingEnvBaseUrl = window.__apiExplorerAppUrl;
                    this.editingEnvVars = [{ key: '', value: '' }];
                    this.showEnvManager = true;
                },

                openEditEnv(name) {
                    this.editingEnv = name;
                    this.editingEnvName = name;
                    this.editingEnvBaseUrl = this.activeEnvBaseUrl || window.__apiExplorerAppUrl;
                    this.editingEnvVars = Object.entries(this.envVars).map(([key, value]) => ({ key, value }));
                    if (this.editingEnvVars.length === 0) {
                        this.editingEnvVars = [{ key: '', value: '' }];
                    }
                    this.showEnvManager = true;
                },

                addEnvVar() {
                    this.editingEnvVars.push({ key: '', value: '' });
                },

                removeEnvVar(index) {
                    this.editingEnvVars.splice(index, 1);
                },
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        [x-cloak] { display: none !important; }
        .json-viewer pre { background: #f5f5f5; padding: 1rem; border-radius: 0.375rem; overflow-x: auto; }
        .json-viewer .string { color: #22863a; }
        .json-viewer .number { color: #005cc5; }
        .json-viewer .boolean { color: #d73a49; }
        .json-viewer .null { color: #6f42c1; }
        .json-viewer .key { color: #24292e; font-weight: bold; }
    </style>

    <!-- Environment Manager -->
    @include('api-explorer::partials.environments')
</body>
</html>
