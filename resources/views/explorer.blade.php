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

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Dark Mode Config -->
    <script>tailwind.config = { darkMode: 'class' }</script>

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Fuse.js for fuzzy search -->
    <script src="https://cdn.jsdelivr.net/npm/fuse.js/dist/fuse.js"></script>

    <!-- Faker.js for generating fake data -->
    <script type="module">
        import { faker } from 'https://esm.sh/@faker-js/faker@8.3.1';
        window.faker = faker;
        window.fakerLoaded = true;
        document.dispatchEvent(new Event('fakerReady'));
    </script>
</head>
<body :class="dark ? 'bg-gray-950' : 'bg-gray-50'" x-data="apiExplorer()" x-init="waitForFaker().then(() => init())" @keydown.enter.ctrl="sendRequest()">
    <!-- Top Bar -->
    <header class="border-b border-gray-200 bg-white dark:bg-gray-900 dark:border-gray-700">
        <div class="mx-auto flex h-16 items-center justify-between px-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">API Explorer</h1>
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
                            type="button"
                            class="relative min-h-[38px] flex items-center justify-between w-full py-2 pl-3 pr-10 text-left bg-white border rounded-md shadow-sm cursor-default border-gray-300 dark:bg-gray-800 dark:border-gray-600 focus:outline-none text-sm">
                            <span x-text="envSelSelectedItem && envSelSelectedItem.value ? envSelSelectedItem.title : 'No Environment'" class="truncate text-gray-700 dark:text-gray-300"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </span>
                        </button>
                        <ul x-show="envSelOpen" @click.away="envSelOpen = false"
                            x-transition:enter="transition ease-out duration-50"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100"
                            :class="{ 'bottom-0 mb-10': envSelDropdownPosition === 'top', 'top-0 mt-10': envSelDropdownPosition === 'bottom' }"
                            class="absolute z-10 w-full py-1 overflow-auto text-sm bg-white dark:bg-gray-800 rounded-md shadow-md max-h-56 focus:outline-none"
                            x-cloak>
                            <template x-for="item in envSelItems" :key="item.value">
                                <li @click="envSelSelectedItem = item; envSelOpen = false;"
                                    :id="item.value + '-' + envSelId"
                                    :class="{ 'bg-neutral-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100': envSelIsActive(item) }"
                                    @mousemove="envSelActiveItem = item"
                                    class="relative flex items-center h-full py-2 pl-8 pr-4 text-gray-700 dark:text-gray-300 cursor-default select-none">
                                    <i x-show="envSelSelectedItem && envSelSelectedItem.value == item.value" class="fas fa-check absolute left-0 ml-2 text-neutral-400" style="font-size: 1rem;"></i>
                                    <span class="block font-medium truncate" x-text="item.title"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <button
                        @click="activeEnv ? openEditEnv(activeEnv) : openNewEnv()"
                        type="button"
                        title="Manage environments"
                        class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium tracking-wide text-neutral-500 transition-colors duration-100 rounded-md focus:outline-none bg-neutral-50 hover:text-neutral-600 hover:bg-neutral-100"
                    >
                        <i class="fas fa-cog"></i>
                    </button>
                </div>
                <button
                    @click="showHistoryModal = true"
                    type="button"
                    title="Request history"
                    class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium tracking-wide text-neutral-500 transition-colors duration-100 rounded-md focus:outline-none bg-neutral-50 hover:text-neutral-600 hover:bg-neutral-100 relative"
                >
                    <i class="fas fa-history"></i>
                    <template x-if="history.length > 0">
                        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-blue-600 rounded-full" x-text="history.length"></span>
                    </template>
                </button>
                <button @click="toggleDark()" type="button" title="Toggle dark mode"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-md text-neutral-500 hover:bg-neutral-100 dark:text-gray-400 dark:hover:bg-gray-800">
                    <i x-show="!dark" class="fas fa-moon"></i>
                    <i x-show="dark" x-cloak class="fas fa-sun"></i>
                </button>
                <div class="text-sm text-gray-600 dark:text-gray-400">
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
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-red-500 transition-colors duration-100 rounded-md focus:outline-none bg-red-50 hover:text-red-600 hover:bg-red-100">
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
                <div class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-6 py-3">
                    <div class="flex items-center gap-3">
                        <span :class="methodColor(active.method.split('|')[0])" class="rounded px-3 py-1 font-bold text-sm">
                            <span x-text="active.method.split('|')[0]"></span>
                        </span>
                        <code x-text="getDisplayUri()" class="text-sm font-mono text-gray-700 dark:text-gray-300"></code>
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

    <!-- Request History Modal -->
    <div
        x-show="showHistoryModal"
        @click.away="showHistoryModal = false"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
        x-cloak
    >
        <div class="flex min-h-screen items-center justify-center bg-black/50 px-4 py-8">
            <div
                @click.stop
                x-transition
                class="relative w-full max-w-2xl rounded-lg bg-white dark:bg-gray-900 shadow-xl"
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <i class="fas fa-history"></i>
                        Request History
                    </h2>
                    <button
                        @click="showHistoryModal = false"
                        type="button"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Content -->
                <div class="max-h-[60vh] overflow-y-auto">
                    <template x-if="history.length === 0">
                        <div class="flex items-center justify-center py-12 text-gray-500 dark:text-gray-400">
                            <div class="text-center">
                                <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                                <p>No requests yet. Make an API request to see history.</p>
                            </div>
                        </div>
                    </template>

                    <template x-if="history.length > 0">
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="entry in history" :key="entry.id">
                                <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <div class="flex items-center justify-between gap-3 mb-2">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <span :class="methodColor(entry.method)" class="rounded px-2.5 py-1 font-bold text-sm flex-shrink-0" x-text="entry.method"></span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="entry.name"></p>
                                                <code class="text-xs font-mono text-gray-600 dark:text-gray-400 truncate block" x-text="entry.path"></code>
                                            </div>
                                        </div>
                                        <span :class="statusColor(entry.status)" class="rounded px-2.5 py-1 font-bold text-sm flex-shrink-0" x-text="entry.status"></span>
                                    </div>

                                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-3">
                                        <span x-text="formatHistoryTime(entry.timestamp)"></span>
                                        <span x-text="`${entry.time}ms`"></span>
                                    </div>

                                    <div class="flex gap-2">
                                        <button
                                            @click="rerunFromHistory(entry); showHistoryModal = false;"
                                            type="button"
                                            class="flex-1 px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 dark:text-blue-400 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 rounded transition-colors"
                                        >
                                            <i class="fas fa-redo mr-2"></i> Re-run
                                        </button>
                                        <button
                                            @click="navigator.clipboard.writeText(JSON.stringify(entry.request, null, 2))"
                                            type="button"
                                            class="flex-1 px-3 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700 rounded transition-colors"
                                        >
                                            <i class="fas fa-copy mr-2"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400" x-text="`${history.length} / ${maxHistorySize} requests`"></span>
                    <button
                        @click="clearHistory()"
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 dark:text-red-400 dark:bg-red-900/20 dark:hover:bg-red-900/30 rounded transition-colors"
                    >
                        <i class="fas fa-trash mr-2"></i> Clear History
                    </button>
                </div>
            </div>
        </div>
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

        function waitForFaker() {
            return new Promise(resolve => {
                if (window.faker) {
                    resolve();
                } else {
                    document.addEventListener('fakerReady', resolve, { once: true });
                    setTimeout(() => {
                        // Timeout - faker didn't load, but continue anyway
                        resolve();
                    }, 5000);
                }
            });
        }

        const fakerMethods = [
            { category: 'person',   label: 'First Name',    expr: "faker.person.firstName()",           desc: 'e.g. John' },
            { category: 'person',   label: 'First Name ♀',  expr: "faker.person.firstName('female')",   desc: 'e.g. Maria' },
            { category: 'person',   label: 'First Name ♂',  expr: "faker.person.firstName('male')",     desc: 'e.g. James' },
            { category: 'person',   label: 'Last Name',     expr: "faker.person.lastName()",            desc: 'e.g. Smith' },
            { category: 'person',   label: 'Full Name',     expr: "faker.person.fullName()",            desc: 'e.g. John Smith' },
            { category: 'person',   label: 'Job Title',     expr: "faker.person.jobTitle()",            desc: 'e.g. Senior Developer' },
            { category: 'internet', label: 'Email',         expr: "faker.internet.email()",             desc: 'e.g. john@example.com' },
            { category: 'internet', label: 'Username',      expr: "faker.internet.username()",          desc: 'e.g. john_42' },
            { category: 'internet', label: 'URL',           expr: "faker.internet.url()",               desc: 'e.g. https://example.com' },
            { category: 'internet', label: 'IP Address',    expr: "faker.internet.ip()",                desc: 'e.g. 192.168.0.1' },
            { category: 'internet', label: 'Password',      expr: "faker.internet.password()",          desc: 'e.g. xK9#mP2!' },
            { category: 'phone',    label: 'Phone Number',  expr: "faker.phone.number()",               desc: 'e.g. +1-800-555-0100' },
            { category: 'location', label: 'City',          expr: "faker.location.city()",              desc: 'e.g. Sydney' },
            { category: 'location', label: 'Country',       expr: "faker.location.country()",           desc: 'e.g. Australia' },
            { category: 'location', label: 'Street',        expr: "faker.location.streetAddress()",     desc: 'e.g. 123 Main St' },
            { category: 'location', label: 'Zip Code',      expr: "faker.location.zipCode()",           desc: 'e.g. 90210' },
            { category: 'lorem',    label: 'Word',          expr: "faker.lorem.word()",                 desc: 'e.g. lorem' },
            { category: 'lorem',    label: 'Sentence',      expr: "faker.lorem.sentence()",             desc: 'e.g. Lorem ipsum...' },
            { category: 'lorem',    label: 'Paragraph',     expr: "faker.lorem.paragraph()",            desc: 'One paragraph' },
            { category: 'lorem',    label: 'Paragraphs',    expr: "faker.lorem.paragraph({ min: 1, max: 3 })", desc: '1–3 paragraphs' },
            { category: 'date',     label: 'Recent',        expr: "faker.date.recent().toISOString()",  desc: 'Recent date/time' },
            { category: 'date',     label: 'Past',          expr: "faker.date.past().toISOString()",    desc: 'Past date' },
            { category: 'date',     label: 'Future',        expr: "faker.date.future().toISOString()",  desc: 'Future date' },
            { category: 'finance',  label: 'Amount',        expr: "faker.finance.amount()",             desc: 'e.g. 123.45' },
            { category: 'finance',  label: 'Currency',      expr: "faker.finance.currencyCode()",       desc: 'e.g. USD' },
            { category: 'company',  label: 'Company Name',  expr: "faker.company.name()",               desc: 'e.g. Acme Corp' },
            { category: 'string',   label: 'UUID',          expr: "faker.string.uuid()",                desc: 'e.g. 550e8400-...' },
            { category: 'number',   label: 'Integer',       expr: "faker.number.int({ min: 1, max: 100 })", desc: '1–100' },
            { category: 'datatype', label: 'Boolean',       expr: "faker.datatype.boolean()",           desc: 'true or false' },
            // Extended existing categories
            { category: 'location', label: 'State',         expr: "faker.location.state()",             desc: 'e.g. California' },
            { category: 'location', label: 'Country Code',  expr: "faker.location.countryCode()",       desc: 'e.g. US' },
            { category: 'location', label: 'Latitude',      expr: "faker.location.latitude()",          desc: 'e.g. 34.0522' },
            { category: 'location', label: 'Longitude',     expr: "faker.location.longitude()",         desc: 'e.g. -118.2437' },
            { category: 'internet', label: 'Domain',        expr: "faker.internet.domainName()",        desc: 'e.g. example.com' },
            { category: 'internet', label: 'Emoji',         expr: "faker.internet.emoji()",             desc: 'e.g. 😊' },
            { category: 'string',   label: 'Alphanumeric',  expr: "faker.string.alphanumeric(10)",      desc: 'Random a-z0-9' },
            { category: 'string',   label: 'Nanoid',        expr: "faker.string.nanoid()",              desc: 'e.g. V1StGXR_Z5j' },
            { category: 'number',   label: 'Float',         expr: "faker.number.float()",               desc: 'e.g. 123.45' },
            { category: 'number',   label: 'Big Int',       expr: "faker.number.bigInt()",              desc: 'Large integer' },
            { category: 'date',     label: 'Birthdate',     expr: "faker.date.birthdate().toISOString()", desc: 'Past birth date' },
            // New categories
            { category: 'color',    label: 'Color Name',    expr: "faker.color.human()",                desc: 'e.g. red' },
            { category: 'color',    label: 'Color RGB',     expr: "faker.color.rgb()",                  desc: 'e.g. #ff5733' },
            { category: 'commerce', label: 'Product Name',  expr: "faker.commerce.productName()",       desc: 'e.g. Awesome Chair' },
            { category: 'commerce', label: 'Price',         expr: "faker.commerce.price()",             desc: 'e.g. 29.99' },
            { category: 'commerce', label: 'Department',    expr: "faker.commerce.department()",        desc: 'e.g. Electronics' },
            { category: 'commerce', label: 'Product Desc',  expr: "faker.commerce.productDescription()", desc: 'Short description' },
            { category: 'vehicle',  label: 'Vehicle',       expr: "faker.vehicle.vehicle()",            desc: 'e.g. Toyota Corolla' },
            { category: 'vehicle',  label: 'Manufacturer',  expr: "faker.vehicle.manufacturer()",       desc: 'e.g. Toyota' },
            { category: 'vehicle',  label: 'Model',         expr: "faker.vehicle.model()",              desc: 'e.g. Corolla' },
            { category: 'vehicle',  label: 'Type',          expr: "faker.vehicle.type()",               desc: 'e.g. SUV' },
            { category: 'vehicle',  label: 'VIN',           expr: "faker.vehicle.vin()",                desc: 'e.g. YV1MH682762184654' },
            { category: 'animal',   label: 'Dog Breed',     expr: "faker.animal.dog()",                 desc: 'e.g. Golden Retriever' },
            { category: 'animal',   label: 'Cat Breed',     expr: "faker.animal.cat()",                 desc: 'e.g. Ragamuffin' },
            { category: 'animal',   label: 'Animal Type',   expr: "faker.animal.type()",                desc: 'e.g. Bird' },
            { category: 'music',    label: 'Genre',         expr: "faker.music.genre()",                desc: 'e.g. Rock' },
            { category: 'music',    label: 'Song Name',     expr: "faker.music.songName()",             desc: 'e.g. Something in the Way' },
        ];

        function apiExplorer() {
            return {
                grouped: window.__apiExplorerEndpoints,
                allEndpoints: [],
                active: null,
                baseUrl: '',
                headers: [],
                queryParams: [],
                body: {},
                enabledFields: {},
                pathParams: {},
                endpointState: {},
                activeTab: 'body',
                responseTab: 'json',
                expandedTreeNodes: {},
                response: null,
                loading: false,
                sidebarWidth: 320,
                isResizing: false,
                resizeStartX: 0,

                // Dark mode
                dark: localStorage.getItem('apiExplorer.dark') === 'true',

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

                // Faker browser
                showFakerBrowser: false,
                fakerTargetField: null,
                fakerSearch: '',
                fakerActiveCategory: 'all',

                // Accept header select
                acceptHeaderOptions: [
                    { title: 'application/json', value: 'application/json' },
                    { title: 'application/xml', value: 'application/xml' },
                    { title: 'text/plain', value: 'text/plain' },
                    { title: 'text/html', value: 'text/html' },
                    { title: 'text/csv', value: 'text/csv' },
                    { title: 'application/pdf', value: 'application/pdf' },
                    { title: 'image/png', value: 'image/png' },
                    { title: 'image/jpeg', value: 'image/jpeg' },
                    { title: 'application/octet-stream', value: 'application/octet-stream' },
                    { title: '*/* (any type)', value: '*/*' },
                ],

                // Content-Type header select
                contentTypeOptions: [
                    { title: 'application/json', value: 'application/json' },
                    { title: 'application/xml', value: 'application/xml' },
                    { title: 'text/plain', value: 'text/plain' },
                    { title: 'text/html', value: 'text/html' },
                    { title: 'application/x-www-form-urlencoded', value: 'application/x-www-form-urlencoded' },
                    { title: 'multipart/form-data', value: 'multipart/form-data' },
                    { title: 'text/csv', value: 'text/csv' },
                    { title: 'application/pdf', value: 'application/pdf' },
                    { title: 'application/octet-stream', value: 'application/octet-stream' },
                ],

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
                openAccordions: new Set(),

                // Request/Response history
                history: [],
                showHistoryModal: false,
                maxHistorySize: 50,

                init() {
                    // Expose component to window for tree node click handlers
                    window.__apiExplorer = this;

                    // Apply dark mode immediately
                    if (this.dark) document.documentElement.classList.add('dark');

                    this.baseUrl = window.__apiExplorerAppUrl;
                    try {
                        this.headers = JSON.parse(localStorage.getItem('apiExplorer.headers') || '[]');
                    } catch {
                        this.headers = [];
                    }

                    // Ensure Content-Type and Accept headers exist with application/json default
                    const ensureHeader = (key, defaultValue = 'application/json') => {
                        const exists = this.headers.some(h => h.key === key);
                        if (!exists) {
                            this.headers.unshift({ key, value: defaultValue, enabled: true });
                        }
                    };
                    ensureHeader('Content-Type');
                    ensureHeader('Accept');
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

                    // Load history
                    this.loadHistory();
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

                isGroupOpen(groupName) {
                    // If searching, check if group is in openAccordions
                    if (this.searchQuery) {
                        return this.openAccordions.has(groupName);
                    }
                    // Otherwise use normal accordion state
                    return this.activeAccordion === groupName;
                },

                toggleGroupAccordion(groupName) {
                    // If searching, don't allow toggling (keep all open)
                    if (this.searchQuery) {
                        return;
                    }
                    // Otherwise toggle normally
                    this.setActiveAccordion(groupName);
                },

                performSearch() {
                    if (!this.searchQuery) {
                        this.filteredGrouped = {};
                        this.filteredEndpoints = [];
                        this.openAccordions.clear();
                        return;
                    }

                    const results = this.fuse.search(this.searchQuery);
                    this.filteredEndpoints = results.map(result => result.item);

                    // Build filtered tree structure maintaining hierarchy
                    this.filteredGrouped = this.buildFilteredTree(this.grouped, this.filteredEndpoints);

                    // Open all accordions in the filtered tree
                    this.openAccordions.clear();
                    for (const groupName of Object.keys(this.filteredGrouped)) {
                        this.openAccordions.add(groupName);
                    }
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

                        // Restore enabledFields (default to true if not saved)
                        this.enabledFields = {};
                        endpoint.fields.forEach(field => {
                            this.enabledFields[field.name] = saved.enabledFields?.[field.name] !== false;
                        });
                    } else {
                        this.body = defaultBody;
                        this.pathParams = defaultPathParams;
                        this.queryParams = [];

                        // Initialize enabledFields to true for all fields
                        this.enabledFields = {};
                        endpoint.fields.forEach(field => {
                            this.enabledFields[field.name] = true;
                        });
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

                repositionResponseTabMarker() {
                    const container = document.querySelector('[x-ref="responseTabsContainer"]');
                    if (!container) return;
                    const activeBtn = container.querySelector(`[data-tab='${this.responseTab}']`);
                    const marker = container.querySelector('[x-ref="responseTabMarker"]');
                    if (activeBtn && marker && activeBtn.offsetParent !== null) {
                        marker.style.width = activeBtn.offsetWidth + 'px';
                        marker.style.height = activeBtn.offsetHeight + 'px';
                        marker.style.left = activeBtn.offsetLeft + 'px';
                    }
                },

                toggleTreeNode(path) {
                    if (this.expandedTreeNodes[path]) {
                        delete this.expandedTreeNodes[path];
                    } else {
                        this.expandedTreeNodes[path] = true;
                    }
                },

                isTreeNodeExpanded(path) {
                    return !!this.expandedTreeNodes[path];
                },

                renderTreeValue(value, path = 'root') {
                    if (value === null) return '<span class="null">null</span>';
                    if (typeof value === 'boolean') return `<span class="boolean">${value}</span>`;
                    if (typeof value === 'number') return `<span class="number">${value}</span>`;
                    if (typeof value === 'string') return `<span class="string">"${value.replace(/"/g, '\\"')}"</span>`;
                    return '';
                },

                renderJsonTree(value, path = 'root', depth = 0) {
                    const isExpanded = this.isTreeNodeExpanded(path);
                    const indent = '&nbsp;'.repeat(depth * 2);
                    const nextIndent = '&nbsp;'.repeat((depth + 1) * 2);

                    if (value === null) {
                        return `${indent}<span class="null">null</span>`;
                    }

                    if (typeof value === 'boolean') {
                        return `${indent}<span class="boolean">${value}</span>`;
                    }

                    if (typeof value === 'number') {
                        return `${indent}<span class="number">${value}</span>`;
                    }

                    if (typeof value === 'string') {
                        return `${indent}<span class="string">"${value.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')}"</span>`;
                    }

                    if (Array.isArray(value)) {
                        const hasItems = value.length > 0;
                        const bracket = hasItems ?
                            `<span onclick="window.__apiExplorer.toggleTreeNode('${path}')" style="cursor: pointer; user-select: none;" title="Click to expand/collapse">
                                <i class="fas fa-chevron-${isExpanded ? 'down' : 'right'}" style="display: inline-block; width: 16px; text-align: center; color: currentColor; opacity: 0.6;"></i>
                                <span class="key">[</span><span style="opacity: 0.6;">${value.length}</span><span class="key">]</span>
                            </span>` :
                            `<span class="key">[]</span>`;

                        if (!hasItems) {
                            return `${indent}${bracket}`;
                        }

                        let html = `${indent}${bracket}`;
                        if (isExpanded) {
                            value.forEach((item, index) => {
                                const itemPath = `${path}[${index}]`;
                                html += `<div>${this.renderJsonTree(item, itemPath, depth + 1)}</div>`;
                            });
                            html += `${indent}<span class="key">]</span>`;
                        }
                        return html;
                    }

                    if (typeof value === 'object') {
                        const keys = Object.keys(value);
                        const hasItems = keys.length > 0;
                        const bracket = hasItems ?
                            `<span onclick="window.__apiExplorer.toggleTreeNode('${path}')" style="cursor: pointer; user-select: none;" title="Click to expand/collapse">
                                <i class="fas fa-chevron-${isExpanded ? 'down' : 'right'}" style="display: inline-block; width: 16px; text-align: center; color: currentColor; opacity: 0.6;"></i>
                                <span class="key">{</span><span style="opacity: 0.6;">${keys.length}</span><span class="key">}</span>
                            </span>` :
                            `<span class="key">{}</span>`;

                        if (!hasItems) {
                            return `${indent}${bracket}`;
                        }

                        let html = `${indent}${bracket}`;
                        if (isExpanded) {
                            keys.forEach((key, index) => {
                                const itemPath = `${path}.${key}`;
                                const item = value[key];
                                const isLast = index === keys.length - 1;
                                const isContainer = typeof item === 'object' && item !== null;

                                if (isContainer) {
                                    html += `<div>${nextIndent}<span class="key">"${key}"</span><span class="key">:</span> ${this.renderJsonTree(item, itemPath, depth + 1).replace(/^&nbsp;*/g, '')}</div>`;
                                } else {
                                    html += `<div>${nextIndent}<span class="key">"${key}"</span><span class="key">:</span> ${this.renderTreeValue(item, itemPath).replace(/^&nbsp;*/g, '')}</div>`;
                                }
                            });
                            html += `${indent}<span class="key">}</span>`;
                        }
                        return html;
                    }

                    return indent + JSON.stringify(value);
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
                            rawBody: data,
                        };

                        // Auto-detect and show token button
                        if (typeof parsedBody === 'object' && parsedBody !== null) {
                            const token = parsedBody.token || parsedBody.access_token || parsedBody.bearer_token;
                            if (token) {
                                this.response.token = token;
                            }
                        }

                        // Add to history
                        this.addToHistory({
                            method: this.active.method.split('|')[0],
                            path: this.active.path,
                            name: this.active.name,
                            status: response.status,
                            statusText: response.statusText,
                            time: this.response.time,
                            timestamp: new Date(),
                            request: {
                                body: this.getFilteredBody(),
                                pathParams: { ...this.pathParams },
                                queryParams: this.queryParams.filter(qp => qp.enabled !== false),
                            },
                            response: this.response,
                        });
                    } catch (error) {
                        this.response = {
                            error: error.message,
                            status: 0,
                        };
                    } finally {
                        this.loading = false;
                        Alpine.nextTick(() => {
                            this.repositionResponseTabMarker();
                        });
                    }
                },

                getNestedValue(parentKey, nestedKey) {
                    if (!this.body[parentKey]) {
                        return '';
                    }
                    return this.body[parentKey][nestedKey] || '';
                },

                setNestedValue(parentKey, nestedKey, value) {
                    if (!this.body[parentKey]) {
                        this.body[parentKey] = {};
                    }
                    this.body[parentKey][nestedKey] = value;
                    this.persistEndpointState();
                },

                getFilteredBody() {
                    const filtered = {};
                    for (const [key, value] of Object.entries(this.body)) {
                        if (this.enabledFields[key] !== false) {
                            filtered[key] = value;
                        }
                    }
                    return filtered;
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
                    const filteredBody = this.getFilteredBody();
                    if (['POST', 'PUT', 'PATCH'].includes(method)) {
                        const hasFile = this.hasFileField(this.active.fields);
                        if (hasFile) {
                            const formData = new FormData();
                            this.flattenObject(filteredBody, formData);
                            options.body = formData;
                            delete options.headers['Content-Type'];
                        } else {
                            options.headers['Content-Type'] = 'application/json';
                            options.body = JSON.stringify(this.applyVarsToObject(filteredBody));
                        }
                    } else if (method === 'GET') {
                        // Serialize body and query params
                        const params = new URLSearchParams();
                        this.flattenForQuery(filteredBody, params);
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
                    const header = this.headers[index];
                    if (header && ['Content-Type', 'Accept'].includes(header.key)) {
                        return; // Prevent deletion of required headers
                    }
                    this.headers.splice(index, 1);
                    this.persistHeaders();
                },

                canDeleteHeader(header) {
                    return !['Content-Type', 'Accept'].includes(header.key);
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

                toggleDark() {
                    this.dark = !this.dark;
                    document.documentElement.classList.toggle('dark', this.dark);
                    localStorage.setItem('apiExplorer.dark', String(this.dark));
                },

                addQueryParam() {
                    this.queryParams.push({ key: '', value: '', enabled: true });
                    this.persistEndpointState();
                },

                removeQueryParam(index) {
                    this.queryParams.splice(index, 1);
                    this.persistEndpointState();
                },

                addArrayItem(fieldName) {
                    if (!Array.isArray(this.body[fieldName])) {
                        this.body[fieldName] = [];
                    }
                    this.body[fieldName].push('');
                    this.persistEndpointState();
                },

                removeArrayItem(fieldName, index) {
                    this.body[fieldName].splice(index, 1);
                    this.persistEndpointState();
                },

                resetAllFields() {
                    if (!this.active) return;
                    if (!confirm('Are you sure you want to clear all fields?')) return;

                    this.body = {};
                    this.enabledFields = {};
                    this.queryParams = [];
                    this.persistEndpointState();
                },

                persistEndpointState() {
                    if (!this.active) return;
                    this.endpointState[this.active.name] = {
                        body: this.body,
                        pathParams: this.pathParams,
                        queryParams: this.queryParams,
                        enabledFields: this.enabledFields,
                    };
                    localStorage.setItem('apiExplorer.endpointState', JSON.stringify(this.endpointState));
                },

                // History management
                addToHistory(entry) {
                    entry.id = Date.now() + Math.random();
                    this.history.unshift(entry);

                    // Keep history size under control
                    if (this.history.length > this.maxHistorySize) {
                        this.history = this.history.slice(0, this.maxHistorySize);
                    }

                    this.saveHistory();
                },

                rerunFromHistory(entry) {
                    if (!this.active) return;

                    // Restore request state
                    this.body = entry.request.body || {};
                    this.pathParams = entry.request.pathParams || {};
                    this.queryParams = entry.request.queryParams || [];
                    this.persistEndpointState();

                    // Re-run request
                    this.sendRequest();
                },

                loadHistory() {
                    try {
                        const saved = localStorage.getItem('apiExplorer.history');
                        if (saved) {
                            this.history = JSON.parse(saved).map(entry => ({
                                ...entry,
                                timestamp: new Date(entry.timestamp),
                            }));
                        }
                    } catch {
                        this.history = [];
                    }
                },

                saveHistory() {
                    try {
                        localStorage.setItem('apiExplorer.history', JSON.stringify(this.history));
                    } catch (e) {
                        console.warn('Failed to save history to localStorage:', e);
                    }
                },

                clearHistory() {
                    if (!confirm('Clear all request history? This cannot be undone.')) return;
                    this.history = [];
                    localStorage.removeItem('apiExplorer.history');
                },

                formatHistoryTime(date) {
                    const now = new Date();
                    const diffMs = now - date;
                    const diffSeconds = Math.floor(diffMs / 1000);
                    const diffMinutes = Math.floor(diffSeconds / 60);
                    const diffHours = Math.floor(diffMinutes / 60);
                    const diffDays = Math.floor(diffHours / 24);

                    if (diffSeconds < 60) return 'just now';
                    if (diffMinutes < 60) return `${diffMinutes}m ago`;
                    if (diffHours < 24) return `${diffHours}h ago`;
                    if (diffDays < 7) return `${diffDays}d ago`;

                    return date.toLocaleDateString();
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

                extractRateLimit() {
                    if (!this.response || !this.response.headers) return null;

                    const headers = this.response.headers;
                    const headerKeys = Object.keys(headers);

                    // Try common rate limit header patterns
                    const limitKey = headerKeys.find(k => k.toLowerCase().includes('ratelimit') && k.toLowerCase().includes('limit'));
                    const remainingKey = headerKeys.find(k => k.toLowerCase().includes('ratelimit') && k.toLowerCase().includes('remaining'));
                    const resetKey = headerKeys.find(k => k.toLowerCase().includes('ratelimit') && k.toLowerCase().includes('reset'));

                    if (!limitKey || !remainingKey) return null;

                    const limit = parseInt(headers[limitKey], 10);
                    const remaining = parseInt(headers[remainingKey], 10);
                    const resetValue = resetKey ? headers[resetKey] : null;

                    if (isNaN(limit) || isNaN(remaining)) return null;

                    let resetTime = null;
                    let resetIn = null;

                    if (resetValue) {
                        // Try to parse as Unix timestamp (seconds)
                        const resetTimestamp = parseInt(resetValue, 10);
                        if (!isNaN(resetTimestamp)) {
                            const resetMs = resetTimestamp * 1000;
                            const now = Date.now();
                            if (resetMs > now) {
                                resetTime = new Date(resetMs);
                                resetIn = Math.ceil((resetMs - now) / 1000);
                            }
                        }
                    }

                    return {
                        limit,
                        remaining,
                        used: limit - remaining,
                        percentageUsed: Math.round((limit - remaining) / limit * 100),
                        resetTime,
                        resetIn,
                    };
                },

                formatResetTime(seconds) {
                    if (seconds < 60) return `${seconds}s`;
                    if (seconds < 3600) return `${Math.floor(seconds / 60)}m`;
                    return `${Math.floor(seconds / 3600)}h`;
                },

                copyToClipboard(text) {
                    navigator.clipboard.writeText(JSON.stringify(text, null, 2));
                },

                highlightJson(obj) {
                    // Handle non-object responses (strings, plain text, HTML)
                    if (typeof obj === 'string') {
                        // If it's already a string (non-JSON response), just escape HTML and return
                        const escaped = obj.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        return '<pre class="text-gray-600 whitespace-pre-wrap break-words">' + escaped + '</pre>';
                    }
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

                applyFaker(str) {
                    if (!str || typeof str !== 'string') return str;
                    // Strip @ prefix from @{{faker...}} before regex matching
                    while (str.includes(String.fromCharCode(64) + '{')) {
                        str = str.replace(String.fromCharCode(64) + '{', '{');
                    }
                    const charClass = '[^{' + '}]';
                    const regexStr = '\\{\\{(faker\\.' + charClass + '*(?:\\{' + charClass + '*\\}' + charClass + '*)*)\\}\\}';
                    const pattern = new RegExp(regexStr, 'g');
                    return str.replace(pattern, (match, expr) => {
                        try {
                            if (typeof window.faker === 'undefined') {
                                return '[faker.js not loaded]';
                            }
                            // expr already includes 'faker.' prefix from regex, so use it directly
                            return new Function('faker', 'return ' + expr)(window.faker);
                        } catch (e) {
                            return '[faker error: ' + e.message + ']';
                        }
                    });
                },

                applyVars(text) {
                    if (typeof text !== 'string') return text;
                    text = this.applyFaker(text); // resolve faker expressions first
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

                    try {
                        const res = await fetch(url, {
                            method: isNew ? 'POST' : 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ name: this.editingEnvName, baseUrl: this.editingEnvBaseUrl, vars }),
                        });

                        if (res.ok) {
                            const responseData = await res.json();
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

                async openEditEnv(name) {
                    // Load the specific environment's data (not just active env's)
                    try {
                        const res = await fetch(`{{ route("api-explorer.environments.index") }}/${encodeURIComponent(name)}`);
                        const data = await res.json();

                        this.editingEnv = name;
                        this.editingEnvName = name;
                        this.editingEnvBaseUrl = data.baseUrl || window.__apiExplorerAppUrl;
                        this.editingEnvVars = Object.entries(data.vars || {}).map(([key, value]) => ({ key, value }));
                        if (this.editingEnvVars.length === 0) {
                            this.editingEnvVars = [{ key: '', value: '' }];
                        }
                        this.showEnvManager = true;
                    } catch (error) {
                        console.error('Failed to load environment for editing:', error);
                    }
                },

                addEnvVar() {
                    this.editingEnvVars.push({ key: '', value: '' });
                },

                removeEnvVar(index) {
                    this.editingEnvVars.splice(index, 1);
                },

                insertFakerExpr(methodOrExpr) {
                    if (!this.fakerTargetField) {
                        return;
                    }

                    // Handle both method object and expression string
                    let expr = typeof methodOrExpr === 'string' ? methodOrExpr : (methodOrExpr && methodOrExpr.expr ? methodOrExpr.expr : '');

                    if (!expr) {
                        return;
                    }

                    const currentValue = this.body[this.fakerTargetField] || '';
                    const exprStr = '@{{' + expr + '}}';
                    this.body[this.fakerTargetField] = currentValue ? `${currentValue} ${exprStr}` : exprStr;
                    this.persistEndpointState();

                    this.showFakerBrowser = false;
                    this.fakerSearch = '';
                    this.fakerActiveCategory = 'all';
                },

                fakerCategories() {
                    const seen = new Set();
                    return [{ id: 'all', label: 'All' }, ...fakerMethods
                        .map(m => ({ id: m.category, label: m.category.charAt(0).toUpperCase() + m.category.slice(1) }))
                        .filter(c => seen.has(c.id) ? false : seen.add(c.id))];
                },

                fakerFilteredMethods() {
                    return fakerMethods.filter(m => {
                        const matchesCategory = this.fakerActiveCategory === 'all' || m.category === this.fakerActiveCategory;
                        const q = this.fakerSearch.toLowerCase();
                        const matchesSearch = !q || m.label.toLowerCase().includes(q) || m.category.toLowerCase().includes(q) || m.expr.toLowerCase().includes(q);
                        return matchesCategory && matchesSearch;
                    });
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

        /* Dark mode syntax highlighting */
        html.dark .json-viewer pre { background: #1a1a2e; }
        html.dark .json-viewer .string { color: #6adf73; }
        html.dark .json-viewer .number { color: #79b8ff; }
        html.dark .json-viewer .boolean { color: #f97583; }
        html.dark .json-viewer .null { color: #b392f0; }
        html.dark .json-viewer .key { color: #e1e4e8; }
    </style>

    <!-- Faker Browser -->
    @include('api-explorer::partials.faker-browser')

    <!-- Environment Manager -->
    @include('api-explorer::partials.environments')
</body>
</html>
