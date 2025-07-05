<template>
    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        {{ t('print_station.title') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ t('print_station.description') }}
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <div :class="[
                            'h-3 w-3 rounded-full',
                            isPolling ? 'bg-green-500' : 'bg-red-500'
                        ]"></div>
                        <span class="text-sm font-medium">
                            {{ isPolling ? t('print_station.polling_active') : t('print_station.polling_stopped') }}
                        </span>
                    </div>
                    <Button @click="togglePolling" :disabled="loading">
                        <Icon :name="isPolling ? 'Pause' : 'Play'" class="mr-2 h-4 w-4" />
                        {{ isPolling ? t('print_station.stop') : t('print_station.start') }}
                    </Button>
                    <Button @click="refreshJobs" :disabled="loading">
                        <Icon name="RefreshCw" class="mr-2 h-4 w-4" />
                        {{ t('print_station.refresh') }}
                    </Button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <!-- Auto-Print Status -->
                <div v-if="autoPrintEnabled" class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="animate-pulse h-3 w-3 bg-blue-500 rounded-full"></div>
                            <span class="font-medium text-blue-900 dark:text-blue-100">
                                {{ t('print_station.auto_print_active') }}
                            </span>
                            <span v-if="currentlyPrinting" class="text-sm text-blue-700 dark:text-blue-300">
                                {{ t('print_station.printing_job', { jobId: currentlyPrinting.job_id }) }}
                            </span>
                        </div>
                        <Button variant="outline" size="sm" @click="toggleAutoPrint">
                            {{ t('print_station.disable_auto_print') }}
                        </Button>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-yellow-100 text-yellow-600">
                                        <Icon name="Clock" class="h-5 w-5" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ t('print_station.pending_jobs') }}
                                    </dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ pendingJobs.length }}
                                    </dd>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-blue-100 text-blue-600">
                                        <Icon name="Printer" class="h-5 w-5" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ t('print_station.processing_jobs') }}
                                    </dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ processingJobs.length }}
                                    </dd>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-green-100 text-green-600">
                                        <Icon name="CheckCircle" class="h-5 w-5" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ t('print_station.completed_today') }}
                                    </dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ statistics.completed_today || 0 }}
                                    </dd>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-red-100 text-red-600">
                                        <Icon name="XCircle" class="h-5 w-5" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ t('print_station.failed_today') }}
                                    </dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ statistics.failed_today || 0 }}
                                    </dd>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Auto-Print Controls -->
                <div class="mb-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ t('print_station.auto_print_settings') }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium">{{ t('print_station.enable_auto_print') }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ t('print_station.auto_print_description') }}
                                    </p>
                                </div>
                                <Button @click="toggleAutoPrint" :variant="autoPrintEnabled ? 'destructive' : 'default'">
                                    {{ autoPrintEnabled ? t('print_station.disable') : t('print_station.enable') }}
                                </Button>
                            </div>
                            
                            <div v-if="!autoPrintEnabled" class="flex items-center space-x-2">
                                <input 
                                    type="checkbox" 
                                    id="useDefaultPrinter" 
                                    v-model="useDefaultPrinter"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                />
                                <Label for="useDefaultPrinter" class="font-medium">
                                    {{ t('print_station.use_default_printer') }}
                                </Label>
                            </div>
                            
                            <div v-if="!autoPrintEnabled && !useDefaultPrinter" class="space-y-2">
                                <Label for="installedPrinterName">{{ t('print_station.select_printer') }}</Label>
                                <select 
                                    id="installedPrinterName" 
                                    v-model="selectedPrinter"
                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="">{{ t('print_station.select_printer_option') }}</option>
                                    <option v-for="printer in availablePrinters" :key="printer" :value="printer">
                                        {{ printer }}
                                    </option>
                                </select>
                            </div>

                            <div v-if="printerStatus" class="text-sm text-gray-600 dark:text-gray-400">
                                {{ t('print_station.status') }}: {{ printerStatus }}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Print Queue -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Pending Jobs -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Icon name="Clock" class="h-5 w-5 text-yellow-600" />
                                {{ t('print_station.pending_queue') }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="pendingJobs.length === 0" class="text-center py-8">
                                <Icon name="Inbox" class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                                <p class="text-gray-500">{{ t('print_station.no_pending_jobs') }}</p>
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="job in pendingJobs"
                                    :key="job.id"
                                    class="border rounded-lg p-4 bg-yellow-50 dark:bg-yellow-900/10 border-yellow-200 dark:border-yellow-800"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <Badge :variant="getPriorityVariant(job.priority)">
                                                    {{ job.priority }}
                                                </Badge>
                                                <span class="text-sm font-medium">{{ job.job_id }}</span>
                                            </div>
                                            <div class="mt-2">
                                                <p class="font-semibold">{{ job.asset.asset_tag }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ job.asset.model_name }} - {{ job.company.name }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                                    {{ t('print_station.requested_by') }} {{ job.user.name }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex gap-2" v-if="!autoPrintEnabled">
                                            <Button size="sm" @click="processPrintJob(job)">
                                                <Icon name="Play" class="h-4 w-4" />
                                            </Button>
                                            <Button size="sm" variant="outline" @click="cancelPrintJob(job)">
                                                <Icon name="X" class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Processing Jobs -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Icon name="Printer" class="h-5 w-5 text-blue-600" />
                                {{ t('print_station.processing_queue') }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="processingJobs.length === 0" class="text-center py-8">
                                <Icon name="Printer" class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                                <p class="text-gray-500">{{ t('print_station.no_processing_jobs') }}</p>
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="job in processingJobs"
                                    :key="job.id"
                                    class="border rounded-lg p-4 bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <Badge variant="secondary">
                                                    {{ job.priority }}
                                                </Badge>
                                                <span class="text-sm font-medium">{{ job.job_id }}</span>
                                            </div>
                                            <div class="mt-2">
                                                <p class="font-semibold">{{ job.asset.asset_tag }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ job.asset.model_name }} - {{ job.company.name }}
                                                </p>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <div class="animate-spin h-3 w-3 border-2 border-blue-600 border-t-transparent rounded-full"></div>
                                                    <span class="text-xs text-blue-600">{{ t('print_station.printing') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <Button size="sm" variant="outline" @click="completePrintJob(job)">
                                                <Icon name="Check" class="h-4 w-4" />
                                            </Button>
                                            <Button size="sm" variant="destructive" @click="failPrintJob(job)">
                                                <Icon name="X" class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Recent Activity -->
                <div class="mt-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{ t('print_station.recent_activity') }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recentJobs.length === 0" class="text-center py-4">
                                <p class="text-gray-500">{{ t('print_station.no_recent_activity') }}</p>
                            </div>
                            <div v-else class="space-y-2">
                                <div
                                    v-for="job in recentJobs"
                                    :key="job.id"
                                    class="flex items-center justify-between p-2 rounded border"
                                >
                                    <div class="flex items-center gap-3">
                                        <Icon 
                                            :name="job.status === 'completed' ? 'CheckCircle' : 'XCircle'" 
                                            :class="job.status === 'completed' ? 'text-green-600' : 'text-red-600'"
                                            class="h-4 w-4"
                                        />
                                        <span class="font-medium">{{ job.asset.asset_tag }}</span>
                                        <Badge :variant="job.status === 'completed' ? 'default' : 'destructive'" size="sm">
                                            {{ job.status }}
                                        </Badge>
                                    </div>
                                    <span class="text-sm text-gray-500">
                                        {{ formatTime(job.completed_at || job.updated_at) }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import Icon from '@/components/Icon.vue';

// Type declarations for JSPrintManager
declare global {
    interface Window {
        JSPrintManager: {
            auto_reconnect: boolean;
            start: () => void;
            websocket_status: number;
            WS: {
                onStatusChanged: () => void;
            };
            getPrinters: () => Promise<string[]>;
        };
        WSStatus: {
            Open: number;
            Closed: number;
            Blocked: number;
        };
        ClientPrintJob: new () => {
            clientPrinter: any;
            printerCommands: string;
            sendToClient: () => void;
        };
        DefaultPrinter: new () => any;
        InstalledPrinter: new (printerName: string) => any;
    }
}

const { t } = useI18n();

interface PrintJob {
    id: number;
    job_id: string;
    asset: {
        id: string;
        asset_tag: string;
        serial_number: string;
        model_name: string;
        category: string;
    };
    company: {
        id: number;
        name: string;
    };
    user: {
        id: number;
        name: string;
    };
    priority: 'low' | 'normal' | 'high' | 'urgent';
    status: 'pending' | 'processing' | 'completed' | 'failed' | 'cancelled';
    print_data: any;
    requested_at: string;
    completed_at?: string;
    updated_at: string;
    created_at: string;
}

interface Statistics {
    pending: number;
    processing: number;
    completed_today: number;
    failed_today: number;
}

const allJobs = ref<PrintJob[]>([]);
const recentJobs = ref<PrintJob[]>([]);
const statistics = ref<Statistics>({
    pending: 0,
    processing: 0,
    completed_today: 0,
    failed_today: 0,
});

const loading = ref(false);
const isPolling = ref(false);
const autoPrintEnabled = ref(false);
const currentlyPrinting = ref<PrintJob | null>(null);

// Printer settings
const useDefaultPrinter = ref(true);
const selectedPrinter = ref('');
const availablePrinters = ref<string[]>([]);
const printerStatus = ref('');

// Polling interval
let pollingInterval: number | null = null;
let autoPrintInterval: number | null = null;

const pendingJobs = computed(() => 
    allJobs.value.filter(job => job.status === 'pending').sort((a, b) => {
        // Sort by priority (urgent > high > normal > low) then by created time
        const priorityOrder = { urgent: 4, high: 3, normal: 2, low: 1 };
        const aPriority = priorityOrder[a.priority] || 0;
        const bPriority = priorityOrder[b.priority] || 0;
        
        if (aPriority !== bPriority) {
            return bPriority - aPriority; // Higher priority first
        }
        
        return new Date(a.created_at).getTime() - new Date(b.created_at).getTime(); // Older first
    })
);

const processingJobs = computed(() => 
    allJobs.value.filter(job => job.status === 'processing')
);

const getPriorityVariant = (priority: string) => {
    switch (priority) {
        case 'urgent':
            return 'destructive';
        case 'high':
            return 'secondary';
        case 'normal':
            return 'outline';
        case 'low':
            return 'secondary';
        default:
            return 'outline';
    }
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString();
};

const refreshJobs = async () => {
    loading.value = true;
    try {
        const [jobsResponse, recentResponse, statsResponse] = await Promise.all([
            fetch('/api/print-jobs/pending'),
            fetch('/api/print-jobs/history?limit=10'),
            fetch('/api/print-jobs/statistics')
        ]);

        if (jobsResponse.ok) {
            allJobs.value = await jobsResponse.json();
        }
        
        if (recentResponse.ok) {
            recentJobs.value = await recentResponse.json();
        }
        
        if (statsResponse.ok) {
            statistics.value = await statsResponse.json();
        }
    } catch (error) {
        console.error('Failed to refresh jobs:', error);
    } finally {
        loading.value = false;
    }
};

const startPolling = () => {
    if (pollingInterval) return;
    
    isPolling.value = true;
    pollingInterval = window.setInterval(refreshJobs, 2000); // Poll every 2 seconds
    console.log('Started polling for print jobs every 2 seconds');
};

const stopPolling = () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
    isPolling.value = false;
    console.log('Stopped polling for print jobs');
};

const togglePolling = () => {
    if (isPolling.value) {
        stopPolling();
    } else {
        startPolling();
    }
};

const toggleAutoPrint = async () => {
    if (autoPrintEnabled.value) {
        // Disable auto-print
        autoPrintEnabled.value = false;
        currentlyPrinting.value = null;
        if (autoPrintInterval) {
            clearInterval(autoPrintInterval);
            autoPrintInterval = null;
        }
    } else {
        // Enable auto-print
        if (!await initializeJSPrintManager()) {
            alert('JSPrintManager is not available. Please install and start JSPrintManager.');
            return;
        }
        
        autoPrintEnabled.value = true;
        startAutoPrint();
    }
};

const startAutoPrint = () => {
    if (autoPrintInterval) return;
    
    autoPrintInterval = window.setInterval(async () => {
        if (currentlyPrinting.value) return; // Already printing something
        
        const nextJob = pendingJobs.value[0]; // Get highest priority pending job
        if (nextJob) {
            await processPrintJob(nextJob, true); // Auto-process
        }
    }, 1000); // Check every second for new jobs to print
};

const processPrintJob = async (job: PrintJob, isAutomatic = false) => {
    try {
        // Mark as processing
        const response = await fetch(`/api/print-jobs/${job.id}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                status: 'processing',
                print_station_id: 'AUTO_STATION_' + Date.now(),
            }),
        });

        if (response.ok) {
            // Update local job status
            const jobIndex = allJobs.value.findIndex(j => j.id === job.id);
            if (jobIndex !== -1) {
                allJobs.value[jobIndex].status = 'processing';
            }

            if (isAutomatic) {
                currentlyPrinting.value = job;
            }

            // Start printing
            await printAssetLabel(job);
        }
    } catch (error) {
        console.error('Failed to process print job:', error);
    }
};

const completePrintJob = async (job: PrintJob) => {
    try {
        const response = await fetch(`/api/print-jobs/${job.id}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                status: 'completed',
            }),
        });

        if (response.ok) {
            // Remove from active jobs
            allJobs.value = allJobs.value.filter(j => j.id !== job.id);
            statistics.value.completed_today++;
            
            if (currentlyPrinting.value?.id === job.id) {
                currentlyPrinting.value = null;
            }
            
            // Refresh recent jobs
            refreshJobs();
        }
    } catch (error) {
        console.error('Failed to complete print job:', error);
    }
};

const failPrintJob = async (job: PrintJob) => {
    try {
        const response = await fetch(`/api/print-jobs/${job.id}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                status: 'failed',
                error_message: 'Print job failed',
            }),
        });

        if (response.ok) {
            // Remove from active jobs
            allJobs.value = allJobs.value.filter(j => j.id !== job.id);
            statistics.value.failed_today++;
            
            if (currentlyPrinting.value?.id === job.id) {
                currentlyPrinting.value = null;
            }
            
            // Refresh recent jobs
            refreshJobs();
        }
    } catch (error) {
        console.error('Failed to fail print job:', error);
    }
};

const cancelPrintJob = async (job: PrintJob) => {
    try {
        const response = await fetch(`/api/print-jobs/${job.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        if (response.ok) {
            // Remove from jobs
            allJobs.value = allJobs.value.filter(j => j.id !== job.id);
        }
    } catch (error) {
        console.error('Failed to cancel print job:', error);
    }
};

// JSPrintManager functions
const loadScript = (src: string): Promise<void> => {
    return new Promise((resolve, reject) => {
        const existingScript = document.querySelector(`script[src="${src}"]`);
        if (existingScript) {
            resolve();
            return;
        }

        const script = document.createElement('script');
        script.src = src;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error(`Failed to load script: ${src}`));
        document.head.appendChild(script);
    });
};

const loadRequiredScripts = async (): Promise<void> => {
    try {
        printerStatus.value = 'Loading print libraries...';
        
        await loadScript('/js/bluebird.min.js');
        await loadScript('/js/jquery-3.2.1.slim.min.js');
        await loadScript('/js/jsmanager/JSPrintManager.js');
        
        printerStatus.value = 'Print libraries loaded successfully';
    } catch (error) {
        console.error('Failed to load scripts:', error);
        printerStatus.value = 'Failed to load print libraries';
        throw error;
    }
};

const initializeJSPrintManager = async (): Promise<boolean> => {
    try {
        if (typeof window.JSPrintManager === 'undefined') {
            await loadRequiredScripts();
        }

        if (typeof window.JSPrintManager === 'undefined') {
            return false;
        }

        window.JSPrintManager.auto_reconnect = true;
        window.JSPrintManager.start();
        
        // Wait for connection
        await new Promise<void>((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Connection timeout')), 5000);
            
            window.JSPrintManager.WS.onStatusChanged = function () {
                if (window.JSPrintManager.websocket_status === window.WSStatus.Open) {
                    clearTimeout(timeout);
                    printerStatus.value = 'Connected to JSPrintManager';
                    
                    // Get available printers
                    window.JSPrintManager.getPrinters().then((printers: string[]) => {
                        availablePrinters.value = printers;
                    });
                    
                    resolve();
                } else if (window.JSPrintManager.websocket_status === window.WSStatus.Closed) {
                    clearTimeout(timeout);
                    printerStatus.value = 'JSPrintManager not running';
                    reject(new Error('JSPrintManager not running'));
                } else if (window.JSPrintManager.websocket_status === window.WSStatus.Blocked) {
                    clearTimeout(timeout);
                    printerStatus.value = 'JSPrintManager blocked this website';
                    reject(new Error('JSPrintManager blocked'));
                }
            };
        });

        return true;
    } catch (error) {
        console.error('Failed to initialize JSPrintManager:', error);
        printerStatus.value = 'Failed to connect to JSPrintManager';
        return false;
    }
};

const printAssetLabel = async (job: PrintJob) => {
    try {
        if (typeof window.JSPrintManager === 'undefined') {
            throw new Error('JSPrintManager not available');
        }

        const printData = job.print_data;
        
        // Create print job
        const cpj = new window.ClientPrintJob();
        
        // Set printer
        if (useDefaultPrinter.value) {
            cpj.clientPrinter = new window.DefaultPrinter();
        } else {
            cpj.clientPrinter = new window.InstalledPrinter(selectedPrinter.value);
        }
        
        // Create ZPL content for label printer
        let cmds = "^XA";
        cmds += "^PW406";  // Print width 406 dots (2 inches * 203 DPI)
        cmds += "^LL203";  // Label length 203 dots (1 inch * 203 DPI)
        cmds += "^MD30";   // Media darkness
        cmds += "^SD15";   // Set darkness
        
        // Company logo
        cmds += "^FO10,50^GFA,329,329,7,,,,,,K0F1C,J01E3E,I039F1C4,I079E10F,I07BF03E,I073E17E,00213E7FC,00607CFF8,00F87CFF,00F8F9F800C,00FCF8J08,007EF,003E6,03BEK03F,039FK07F,039FK07F,07CFK03F8,008F,I0F8I018,I07J03C,03DK03E68,07FCJ01C7,03FFJ01E7,03FF8I01F3,017FEI09F,I0FE0018F8,0043E003CF8,00F1I03C7C,00F80807C3C,0070700F838,0020F00F008,I03E00F3,I07E01F38,I07C01E78,I03801E3,L01E,L01C,L01,,,,,^FS";
        
        // Company name
        cmds += "^FO70,30^A0N,18,18^FDFAHAD NAWAF ALZEER HOLDING CO.^FS";
        
        // Asset tag
        cmds += `^FO70,65^A0N,24,24^FD${printData.asset_tag}^FS`;
        
        // Barcode
        cmds += "^FO70,100^BY2,2,35";
        cmds += "^BCN,35,Y,N,N";
        cmds += `^FD${printData.serial_number || printData.asset_tag}^FS`;
        
        // QR Code
        cmds += "^FO320,50^BQN,2,4";
        cmds += `^FDMM,${printData.asset_tag}^FS`;
        
        // Date
        cmds += `^FO70,175^A0N,18,18^FD${new Date().toISOString().split('T')[0]}^FS`;
        
        cmds += "^XZ";
        
        cpj.printerCommands = cmds;
        cpj.sendToClient();

        // Auto-complete after 3 seconds if auto-print is enabled
        if (autoPrintEnabled.value) {
            setTimeout(() => {
                completePrintJob(job);
            }, 3000);
        }

    } catch (error) {
        console.error('Failed to print asset label:', error);
        await failPrintJob(job);
    }
};

onMounted(() => {
    // Initial load
    refreshJobs();
    
    // Start polling automatically
    startPolling();
    
    // Initialize JSPrintManager
    initializeJSPrintManager();
});

onUnmounted(() => {
    // Clean up intervals
    stopPolling();
    if (autoPrintInterval) {
        clearInterval(autoPrintInterval);
    }
});
</script> 