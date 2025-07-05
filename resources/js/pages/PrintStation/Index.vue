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
                            isConnected ? 'bg-green-500' : 'bg-red-500'
                        ]"></div>
                        <span class="text-sm font-medium">
                            {{ isConnected ? t('print_station.connected') : t('print_station.disconnected') }}
                        </span>
                    </div>
                    <Button @click="refreshJobs" :disabled="loading">
                        <Icon name="RefreshCw" class="mr-2 h-4 w-4" />
                        {{ t('print_station.refresh') }}
                    </Button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
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
                                        <Icon name="Play" class="h-5 w-5" />
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
                                        <div class="flex gap-2">
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
                                <Icon name="Play" class="h-5 w-5 text-blue-600" />
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
import { Icon } from 'lucide-vue-next';
import echo from '@/echo';

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
    created_at: string;
}

interface Statistics {
    pending: number;
    processing: number;
    completed_today: number;
    failed_today: number;
}

const props = defineProps<{
    pusherConfig: {
        key: string;
        cluster: string;
        encrypted: boolean;
    };
}>();

const allJobs = ref<PrintJob[]>([]);
const statistics = ref<Statistics>({
    pending: 0,
    processing: 0,
    completed_today: 0,
    failed_today: 0,
});
const loading = ref(false);
const isConnected = ref(false);

const pendingJobs = computed(() => 
    allJobs.value.filter(job => job.status === 'pending')
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

const refreshJobs = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/print-jobs/pending');
        const jobs = await response.json();
        allJobs.value = jobs;

        const statsResponse = await fetch('/api/print-jobs/statistics');
        const stats = await statsResponse.json();
        statistics.value = stats;
    } catch (error) {
        console.error('Failed to refresh jobs:', error);
    } finally {
        loading.value = false;
    }
};

const processPrintJob = async (job: PrintJob) => {
    try {
        const response = await fetch(`/api/print-jobs/${job.id}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                status: 'processing',
                print_station_id: 'STATION_' + Date.now(),
            }),
        });

        if (response.ok) {
            // Update the job status locally
            const jobIndex = allJobs.value.findIndex(j => j.id === job.id);
            if (jobIndex !== -1) {
                allJobs.value[jobIndex].status = 'processing';
            }

            // Trigger actual printing using JSPrintManager
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
            // Remove the job from the list
            allJobs.value = allJobs.value.filter(j => j.id !== job.id);
            statistics.value.completed_today++;
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
                error_message: 'Print job failed manually',
            }),
        });

        if (response.ok) {
            // Remove the job from the list
            allJobs.value = allJobs.value.filter(j => j.id !== job.id);
            statistics.value.failed_today++;
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
            // Remove the job from the list
            allJobs.value = allJobs.value.filter(j => j.id !== job.id);
        }
    } catch (error) {
        console.error('Failed to cancel print job:', error);
    }
};

const printAssetLabel = async (job: PrintJob) => {
    try {
        // Check if JSPrintManager is available
        if (typeof window.JSPrintManager === 'undefined') {
            console.error('JSPrintManager is not available');
            await failPrintJob(job);
            return;
        }

        const printData = job.print_data;
        
        // Create label content
        const labelContent = `
Asset Tag: ${printData.asset_tag}
Model: ${printData.model_name}
Serial: ${printData.serial_number}
Company: ${printData.company_name}
Location: ${printData.location_name}
        `.trim();

        // Create print job
        const cpj = new window.JSPrintManager.ClientPrintJob();
        cpj.clientPrinter = new window.JSPrintManager.DefaultPrinter();
        
        // Create ZPL content for label printer
        const zplContent = `
^XA
^FO50,50^A0N,30,30^FDAsset Tag: ${printData.asset_tag}^FS
^FO50,100^A0N,25,25^FDModel: ${printData.model_name}^FS
^FO50,150^A0N,25,25^FDSerial: ${printData.serial_number}^FS
^FO50,200^A0N,25,25^FDCompany: ${printData.company_name}^FS
^FO50,250^A0N,25,25^FDLocation: ${printData.location_name}^FS
^XZ
        `;

        cpj.printerCommands = zplContent;
        cpj.sendToClient();

        // Mark as completed after a short delay
        setTimeout(() => {
            completePrintJob(job);
        }, 2000);

    } catch (error) {
        console.error('Failed to print asset label:', error);
        await failPrintJob(job);
    }
};

onMounted(() => {
    // Initial load
    refreshJobs();

    // Listen for new print jobs
    echo.channel('print-station')
        .listen('.print-job.created', (event: any) => {
            console.log('New print job received:', event);
            allJobs.value.unshift(event);
            statistics.value.pending++;
        });

    // Check connection status
    echo.connector.pusher.connection.bind('connected', () => {
        isConnected.value = true;
        console.log('Connected to Pusher');
    });

    echo.connector.pusher.connection.bind('disconnected', () => {
        isConnected.value = false;
        console.log('Disconnected from Pusher');
    });

    // Set initial connection status
    isConnected.value = echo.connector.pusher.connection.state === 'connected';
});

onUnmounted(() => {
    // Clean up listeners
    echo.leaveChannel('print-station');
});
</script> 