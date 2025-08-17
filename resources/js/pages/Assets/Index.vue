<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import Icon from '@/components/Icon.vue';
import Heading from '@/components/Heading.vue';
import { useI18n } from 'vue-i18n';
import { computed, ref, watch, nextTick, onMounted } from 'vue';
import type { Asset, Company, AssetCategory, Location, BreadcrumbItem } from '@/types';
import { PrintService } from '@/services/PrintService';

const { t } = useI18n();

// Print service instance
const printService = PrintService.getInstance();

interface Props {
    assets: {
        data: Asset[];
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        from: number;
        to: number;
        total: number;
        last_page: number;
        current_page: number;
    };
    categories: AssetCategory[];
    locations: Location[];
    filters?: {
        search?: string;
        category_id?: string;
        location_id?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const categoryFilter = ref(props.filters?.category_id || '');
const locationFilter = ref(props.filters?.location_id || '');

const selectedAssets = ref<Set<number>>(new Set());
const selectAllChecked = ref(false);

// Track failed images
const failedAssetImages = ref<Set<number>>(new Set());
const failedTemplateImages = ref<Set<number>>(new Set());

const barcodeDialog = ref({
  show: false,
  printing: false,
  useDefaultPrinter: false,
  selectedPrinter: '',
  availablePrinters: [] as string[],
  status: '',
  currentAsset: null as Asset | null,
  selectedAssets: [] as Asset[]
});

const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: t('nav.dashboard'),
        href: '/dashboard',
    },
    {
        title: t('nav.assets'),
        href: '/assets',
    },
]);

const sendToPrintDialog = ref({
  show: false,
  asset: null as Asset | null,
  comment: '',
  priority: 'normal',
  sending: false,
});

const showSendToPrintDialog = (asset: Asset) => {
  sendToPrintDialog.value.asset = asset;
  sendToPrintDialog.value.comment = '';
  sendToPrintDialog.value.priority = 'normal';
  sendToPrintDialog.value.show = true;
};

const sendToPrintMachine = async () => {
  if (!sendToPrintDialog.value.asset) return;
  
  sendToPrintDialog.value.sending = true;
  
  try {
    const response = await fetch('/api/print-jobs', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        asset_id: sendToPrintDialog.value.asset.id,
        priority: sendToPrintDialog.value.priority,
        comment: sendToPrintDialog.value.comment || null,
      }),
    });

    if (response.ok) {
      const result = await response.json();
      // Show success notification
      alert(`Print job ${result.print_job.job_id} sent to print machine successfully!`);
      sendToPrintDialog.value.show = false;
    } else {
      const error = await response.json();
      alert(`Failed to send print job: ${error.error || 'Unknown error'}`);
    }
  } catch (error) {
    console.error('Failed to send print job:', error);
    alert('Failed to send print job. Please try again.');
  } finally {
    sendToPrintDialog.value.sending = false;
  }
};

// Assignment tracking functionality
const assignmentDialog = ref({
  show: false,
  asset: null as Asset | null,
  assignments: [] as any[],
  loading: false,
  currentAction: null as string | null,
  uploadingDocument: false,
  selectedAssignmentId: null as number | null,
});

const showAssignmentDialog = async (asset: Asset) => {
  assignmentDialog.value.asset = asset;
  assignmentDialog.value.show = true;
  assignmentDialog.value.loading = true;
  
  try {
    const response = await fetch(`/api/assets/${asset.id}/assignments`);
    if (response.ok) {
      const data = await response.json();
      assignmentDialog.value.assignments = data.assignments || [];
    } else {
      console.error('Failed to fetch assignments');
      assignmentDialog.value.assignments = [];
    }
  } catch (error) {
    console.error('Error fetching assignments:', error);
    assignmentDialog.value.assignments = [];
  } finally {
    assignmentDialog.value.loading = false;
  }
};

const printAssignmentDocument = async (assignmentId: number, type: 'assignment' | 'return') => {
  if (!assignmentDialog.value.asset) return;
  
  try {
    assignmentDialog.value.currentAction = `printing_${type}_${assignmentId}`;
    
    const response = await fetch(`/api/assets/${assignmentDialog.value.asset.id}/assignments/${assignmentId}/print-document`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        type: type,
        asset_id: assignmentDialog.value.asset.id,
        assignment_id: assignmentId,
      }),
    });

    if (response.ok) {
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `${type}_document_${assignmentDialog.value.asset.asset_tag}_${assignmentId}.html`;
      document.body.appendChild(link);
      link.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(link);
      
      // Also open in new tab for printing
      const printWindow = window.open(url, '_blank');
      if (printWindow) {
        printWindow.onload = () => {
          printWindow.print();
        };
      }
    } else {
      const error = await response.json();
      alert(`Failed to generate document: ${error.error || 'Unknown error'}`);
    }
  } catch (error) {
    console.error('Error printing document:', error);
    alert('Failed to generate document. Please try again.');
  } finally {
    assignmentDialog.value.currentAction = null;
  }
};

const uploadSignedDocument = async (assignmentId: number, type: 'assignment' | 'return') => {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = '.pdf,.jpg,.jpeg,.png,.gif';
  input.multiple = false;
  
  input.onchange = async (e) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    
    if (file.size > 5 * 1024 * 1024) { // 5MB limit
      alert('File size must be less than 5MB');
      return;
    }
    
    const formData = new FormData();
    formData.append('document', file);
    formData.append('type', type);
    formData.append('assignment_id', assignmentId.toString());
    
    try {
      assignmentDialog.value.uploadingDocument = true;
      assignmentDialog.value.currentAction = `uploading_${type}_${assignmentId}`;
      
      const response = await fetch(`/api/assets/${assignmentDialog.value.asset?.id}/assignments/${assignmentId}/upload-document`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: formData,
      });

      if (response.ok) {
        const result = await response.json();
        alert('Document uploaded successfully!');
        
        // Refresh assignments data
        await showAssignmentDialog(assignmentDialog.value.asset!);
      } else {
        const error = await response.json();
        alert(`Failed to upload document: ${error.error || 'Unknown error'}`);
      }
    } catch (error) {
      console.error('Error uploading document:', error);
      alert('Failed to upload document. Please try again.');
    } finally {
      assignmentDialog.value.uploadingDocument = false;
      assignmentDialog.value.currentAction = null;
    }
  };
  
  input.click();
};

const downloadSignedDocument = async (assignmentId: number, type: 'assignment' | 'return') => {
  try {
    const response = await fetch(`/api/assets/${assignmentDialog.value.asset?.id}/assignments/${assignmentId}/download-document?type=${type}`);
    
    if (response.ok) {
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      
      const contentDisposition = response.headers.get('content-disposition');
      let filename = `${type}_document_${assignmentDialog.value.asset?.asset_tag}_${assignmentId}`;
      
      if (contentDisposition) {
        const filenameMatch = contentDisposition.match(/filename="(.+)"/);
        if (filenameMatch) {
          filename = filenameMatch[1];
        }
      }
      
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(link);
    } else {
      alert('Document not found or failed to download');
    }
  } catch (error) {
    console.error('Error downloading document:', error);
    alert('Failed to download document. Please try again.');
  }
};

const formatDate = (date: string | null) => {
  if (!date) return '—';
  return new Date(date).toLocaleDateString();
};

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'available':
      return 'default';
    case 'assigned':
      return 'secondary';
    case 'maintenance':
      return 'destructive';
    case 'retired':
      return 'outline';
    case 'active':
      return 'default';
    case 'returned':
      return 'secondary';
    case 'lost':
      return 'destructive';
    case 'damaged':
      return 'destructive';
    default:
      return 'outline';
  }
};

// Debounced search
let searchTimeout: number;
watch([search, categoryFilter, locationFilter], () => {
    clearTimeout(searchTimeout);
    searchTimeout = window.setTimeout(() => {
        router.get('/assets', {
            search: search.value || undefined,
            category_id: categoryFilter.value || undefined,
            location_id: locationFilter.value || undefined,
        }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const clearFilters = () => {
    search.value = '';
    categoryFilter.value = '';
    locationFilter.value = '';
};

const handlePagination = (url: string | null) => {
    if (!url) return;
    router.get(url, {}, { preserveState: true });
};

const handleAssetImageError = (event: Event, asset: Asset) => {
    failedAssetImages.value.add(asset.id);
};

const handleTemplateImageError = (event: Event, asset: Asset) => {
    failedTemplateImages.value.add(asset.id);
};

// Print service status callback
const handlePrintServiceStatus = (status: any) => {
  console.log('Print service status:', status);
  barcodeDialog.value.status = status.status;
  if (status.isConnected && status.availablePrinters.length > 0) {
    barcodeDialog.value.availablePrinters = status.availablePrinters;
    
    // Auto-select printer if none selected
    if (!barcodeDialog.value.selectedPrinter) {
      const zDesignerPrinter = status.availablePrinters.find((printer: string) => 
        printer.toLowerCase().includes('zdesigner')
      );
      
      if (zDesignerPrinter) {
        barcodeDialog.value.selectedPrinter = zDesignerPrinter;
        console.log('Auto-selected ZDesigner printer via callback:', zDesignerPrinter);
      } else if (status.availablePrinters.length > 0) {
        barcodeDialog.value.selectedPrinter = status.availablePrinters[0];
        console.log('Auto-selected first available printer via callback:', status.availablePrinters[0]);
      }
    }
  }
};

// Asset selection functions
const toggleAssetSelection = (assetId: number) => {
  if (selectedAssets.value.has(assetId)) {
    selectedAssets.value.delete(assetId)
  } else {
    selectedAssets.value.add(assetId)
  }
  updateSelectAllState()
}

const toggleSelectAll = () => {
  if (selectAllChecked.value) {
    selectedAssets.value.clear()
  } else {
    props.assets.data.forEach(asset => {
      selectedAssets.value.add(asset.id)
    })
  }
  selectAllChecked.value = !selectAllChecked.value
}

const updateSelectAllState = () => {
  const totalAssets = props.assets.data.length
  const selectedCount = selectedAssets.value.size
  selectAllChecked.value = totalAssets > 0 && selectedCount === totalAssets
}

const getSelectedAssetsData = (): Asset[] => {
  return props.assets.data.filter(asset => selectedAssets.value.has(asset.id))
}

const showBarcodeDialog = async (asset?: Asset) => {
  if (asset) {
    // Single asset print
    barcodeDialog.value.currentAsset = asset
    barcodeDialog.value.selectedAssets = [asset]
  } else {
    // Multiple assets print
    barcodeDialog.value.currentAsset = null
    barcodeDialog.value.selectedAssets = getSelectedAssetsData()
  }
  
  barcodeDialog.value.show = true
  
  // Subscribe to print service status updates
  printService.onStatusChange(handlePrintServiceStatus);
  
  // Initialize JSPrintManager with retry mechanism
  await initializePrintersWithRetry();
}

const initializePrintersWithRetry = async (maxRetries: number = 3, delay: number = 2000) => {
  for (let attempt = 1; attempt <= maxRetries; attempt++) {
    try {
      console.log(`Printer initialization attempt ${attempt}/${maxRetries}...`);
      barcodeDialog.value.status = `Connecting to JSPrintManager (attempt ${attempt}/${maxRetries})...`;
      
      // Add delay before each attempt (except the first one)
      if (attempt > 1) {
        await new Promise(resolve => setTimeout(resolve, delay));
      }
      
      await printService.initializeJSPrintManager();
      
      // Check if we actually got printers
      const printers = await printService.getAvailablePrinters();
      console.log(`Attempt ${attempt}: Found ${printers.length} printers:`, printers);
      
      if (printers.length > 0) {
        barcodeDialog.value.availablePrinters = printers;
        
        // Auto-select first ZDesigner printer or first available printer
        if (!barcodeDialog.value.selectedPrinter) {
          const zDesignerPrinter = printers.find((printer: string) => 
            printer.toLowerCase().includes('zdesigner')
          );
          
          if (zDesignerPrinter) {
            barcodeDialog.value.selectedPrinter = zDesignerPrinter;
            console.log('Auto-selected ZDesigner printer:', zDesignerPrinter);
          } else if (printers.length > 0) {
            barcodeDialog.value.selectedPrinter = printers[0];
            console.log('Auto-selected first available printer:', printers[0]);
          }
        }
        
        barcodeDialog.value.status = `Connected! Found ${printers.length} printer(s)`;
        console.log('Printer initialization successful!');
        return; // Success, exit the retry loop
      } else if (attempt < maxRetries) {
        console.log(`Attempt ${attempt}: No printers found, retrying...`);
        barcodeDialog.value.status = `No printers found, retrying in ${delay/1000} seconds...`;
      } else {
        console.log('No printers found after all attempts');
        barcodeDialog.value.status = 'Connected to JSPrintManager but no printers found';
      }
      
    } catch (error) {
      console.error(`Printer initialization attempt ${attempt} failed:`, error);
      
      if (attempt < maxRetries) {
        barcodeDialog.value.status = `Connection failed, retrying in ${delay/1000} seconds... (${attempt}/${maxRetries})`;
      } else {
        barcodeDialog.value.status = 'Failed to connect to JSPrintManager: ' + (error instanceof Error ? error.message : String(error));
      }
    }
  }
}

// These functions are now handled by the PrintService

// Check for bulk print on mount
onMounted(async () => {
  const urlParams = new URLSearchParams(window.location.search)
  if (urlParams.get('show_bulk_print') === 'true') {
    // Fetch bulk created assets and show print dialog
    try {
      const response = await fetch('/api/assets/bulk-created')
      if (response.ok) {
        const bulkAssets = await response.json()
        if (bulkAssets.length > 0) {
          // Auto-select the bulk created assets
          bulkAssets.forEach((asset: any) => {
            selectedAssets.value.add(asset.id)
          })
          updateSelectAllState()
          
          // Show bulk print dialog automatically
          await showBarcodeDialog()
          
          // Clear the query parameter
          const newUrl = window.location.pathname
          window.history.replaceState({}, '', newUrl)
        }
      }
    } catch (error) {
      console.error('Failed to fetch bulk created assets:', error)
    }
  }
})

const printBarcode = async () => {
  const assetsToPrint = barcodeDialog.value.selectedAssets
  if (!assetsToPrint || assetsToPrint.length === 0) return
  
  console.log('Print settings:', {
    useDefaultPrinter: barcodeDialog.value.useDefaultPrinter,
    selectedPrinter: barcodeDialog.value.selectedPrinter,
    availablePrinters: barcodeDialog.value.availablePrinters
  });
  
  barcodeDialog.value.printing = true
  barcodeDialog.value.status = `Printing ${assetsToPrint.length} label(s)...`
  
  try {
    if (assetsToPrint.length === 1) {
      // Print single asset
      await printService.printAssetLabel(
        assetsToPrint[0], 
        barcodeDialog.value.useDefaultPrinter, 
        barcodeDialog.value.selectedPrinter
      );
    } else {
      // Print multiple assets
      await printService.printMultipleAssetLabels(
        assetsToPrint, 
        barcodeDialog.value.useDefaultPrinter, 
        barcodeDialog.value.selectedPrinter
      );
    }
    
    barcodeDialog.value.status = `${assetsToPrint.length} label(s) sent to printer successfully!`
    
    // Close dialog after successful print
    setTimeout(() => {
      barcodeDialog.value.show = false
      barcodeDialog.value.printing = false
      // Clear selections after successful print
      selectedAssets.value.clear()
      selectAllChecked.value = false
    }, 2000)
    
  } catch (error) {
    console.error('Print error:', error)
    barcodeDialog.value.status = 'Error occurred while printing: ' + (error instanceof Error ? error.message : String(error))
    barcodeDialog.value.printing = false
  }
};
</script>

<template>
    <Head :title="t('assets.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between my-2">
                <Heading :title="t('assets.title')" />
                <Button asChild>
                    <Link :href="route('assets.create')">
                        <Icon name="Plus" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                        {{ t('assets.create_asset') }}
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 pl-3 rtl:pl-0 rtl:pr-3 flex items-center pointer-events-none">
                        <Icon name="Search" class="h-4 w-4 text-gray-400" />
                    </div>
                    <Input
                        v-model="search"
                        :placeholder="t('assets.search_placeholder')"
                        class="w-full pl-10 rtl:pl-3 rtl:pr-10"
                    />
                </div>
                
                <select 
                    v-model="categoryFilter"
                    class="flex h-10 w-full sm:w-48 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="">{{ t('assets.filter_by_category') }}</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>

                <select 
                    v-model="locationFilter"
                    class="flex h-10 w-full sm:w-48 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="">{{ t('assets.filter_by_location') }}</option>
                    <option v-for="location in locations" :key="location.id" :value="location.id">
                        {{ `${location.code}: ${location.name}` }}
                    </option>
                </select>

                <Button variant="ghost" @click="clearFilters" v-if="search || categoryFilter || locationFilter">
                    <Icon name="X" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                    {{ t('common.clear') }}
                </Button>
                <Button variant="outline" @click="clearFilters" v-if="search || categoryFilter || locationFilter">
                    <Icon name="RefreshCw" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                    {{ t('common.clear_all_filters') }}
                </Button>
            </div>

            <!-- Bulk Actions -->
            <div v-if="selectedAssets.size > 0" class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg mb-4">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-blue-900 dark:text-blue-100">
                        {{ selectedAssets.size }} asset(s) selected
                    </span>
                    <Button variant="outline" size="sm" @click="selectedAssets.clear(); selectAllChecked = false">
                        Clear Selection
                    </Button>
                </div>
                <div class="flex items-center gap-2">
                    <Button @click="showBarcodeDialog()" class="bg-blue-600 hover:bg-blue-700">
                        <Icon name="Printer" class="h-4 w-4 mr-2" />
                        Print {{ selectedAssets.size }} Label(s)
                    </Button>
                </div>
            </div>

            <!-- Search Results Count -->
            <div v-if="search || categoryFilter || locationFilter" class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="text-sm text-blue-900 dark:text-blue-100">
                    <span v-if="assets.data.length > 0">
                        {{ t('assets.pagination_info', { from: assets.from, to: assets.to, total: assets.total }) }}
                    </span>
                    <span v-else>
                        {{ t('assets.no_assets_found') }}
                    </span>
                </div>
            </div>

            <div v-if="assets.data.length === 0" class="text-center py-12">
                <Icon name="HardDrive" class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    {{ search || categoryFilter || locationFilter ? t('assets.no_assets_found') : t('assets.no_assets') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    {{ search || categoryFilter || locationFilter ? t('assets.try_adjusting_search') : t('assets.create_first_asset') }}
                </p>
                <div class="flex justify-center gap-3">
                    <Button asChild v-if="!search && !categoryFilter && !locationFilter">
                        <Link :href="route('assets.create')">
                            <Icon name="Plus" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                            {{ t('assets.create_asset') }}
                        </Link>
                    </Button>
                    <Button variant="outline" @click="clearFilters" v-if="search || categoryFilter || locationFilter">
                        <Icon name="RefreshCw" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                        {{ t('common.clear_all_filters') }}
                    </Button>
                </div>
            </div>

            <!-- Assets Table -->
            <div v-else class="bg-white dark:bg-gray-800 rounded-lg border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr class="text-left rtl:text-right">
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-12">
                                    <input 
                                        type="checkbox" 
                                        :checked="selectAllChecked"
                                        @change="toggleSelectAll"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    />
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('assets.asset') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('assets.template_image') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('assets.company') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('assets.category') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('assets.location') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('assets.condition') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('assets.status') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('common.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="asset in assets.data" :key="asset.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input 
                                        type="checkbox" 
                                        :checked="selectedAssets.has(asset.id)"
                                        @change="toggleAssetSelection(asset.id)"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <!-- Asset Image (priority 1) -->
                                            <div v-if="asset.image_path && !failedAssetImages.has(asset.id)" class="h-10 w-10 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                                                <img 
                                                    :src="`/storage/${asset.image_path}`" 
                                                    :alt="asset.asset_tag"
                                                    class="h-full w-full object-cover"
                                                    @error="(e) => handleAssetImageError(e, asset)"
                                                />
                                            </div>
                                            <!-- Template Image (priority 2) -->
                                            <div v-else-if="asset.asset_template?.image_path && !failedTemplateImages.has(asset.id)" class="h-10 w-10 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                                                <img 
                                                    :src="`/storage/${asset.asset_template.image_path}`" 
                                                    :alt="asset.asset_template.name || 'Template'"
                                                    class="h-full w-full object-cover"
                                                    @error="(e) => handleTemplateImageError(e, asset)"
                                                />
                                            </div>
                                            <!-- Fallback Icon (priority 3) -->
                                            <div v-else class="h-10 w-10 rounded-lg bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                <Icon name="HardDrive" class="h-5 w-5 text-gray-700 dark:text-gray-300" />
                                            </div>
                                        </div>
                                        <div class="ml-4 rtl:ml-0 rtl:mr-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ asset.asset_tag }}
                                            </div>
                                            <div v-if="asset.asset_template?.name" class="text-xs text-gray-400 dark:text-gray-500">
                                                {{ asset.asset_template.name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ asset.model_name || asset.serial_number || '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex justify-center">
                                        <img 
                                            v-if="asset.asset_template?.image_path"
                                            :src="`/storage/${asset.asset_template.image_path}`" 
                                            :alt="asset.asset_template.name || 'Template'" 
                                            class="h-12 w-12 object-cover rounded-lg border border-gray-200 dark:border-gray-600"
                                        />
                                        <div 
                                            v-else
                                            class="h-12 w-12 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 flex items-center justify-center"
                                        >
                                            <Icon name="Image" class="h-5 w-5 text-gray-400" />
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ asset.company?.name_en || '-' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ asset.company?.slug || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ asset.category?.name || '-' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ asset.category?.code || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ asset.location?.name || '-' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ asset.location?.code || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Badge :variant="asset.condition === 'good' ? 'default' : 'destructive'">
                                        {{ t(`assets.condition_${asset.condition}`) }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Badge :variant="getStatusVariant(asset.status)">
                                        {{ t(`assets.status_${asset.status}`) }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right rtl:text-left text-sm font-medium">
                                    <div class="flex justify-end rtl:justify-start gap-1">
                                        <Button variant="ghost" size="sm" @click="showBarcodeDialog(asset)" title="Print Label">
                                            <Icon name="Printer" class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="showSendToPrintDialog(asset)" title="Send to Print Machine">
                                            <Icon name="Send" class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="showAssignmentDialog(asset)" title="Assignment Info">
                                            <Icon name="Info" class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" asChild title="View">
                                            <Link :href="route('assets.show', asset.id)">
                                                <Icon name="Eye" class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" asChild title="Edit">
                                            <Link :href="route('assets.edit', asset.id)">
                                                <Icon name="Pencil" class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="assets.last_page > 1" class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    {{ t('messages.showing') }} {{ assets.from }}-{{ assets.to }} {{ t('messages.of') }} {{ assets.total }}
                </div>
                <div class="flex space-x-1">
                    <Button
                        v-for="link in assets.links"
                        :key="link.label"
                        variant="ghost"
                        size="sm"
                        :disabled="!link.url"
                        :class="{
                            'bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-300': link.active
                        }"
                        @click="handlePagination(link.url)"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>

        <!-- Barcode Print Dialog -->
        <Dialog v-model:open="barcodeDialog.show">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Print Barcode Label</DialogTitle>
                    <DialogDescription>
                        Configure printer settings for the Zebra label printer.
                        <div v-if="barcodeDialog.selectedAssets.length === 1" class="mt-2 font-medium">
                            Asset: {{ barcodeDialog.selectedAssets[0].asset_tag }}
                        </div>
                        <div v-else-if="barcodeDialog.selectedAssets.length > 1" class="mt-2">
                            <div class="font-medium mb-2">
                                Printing {{ barcodeDialog.selectedAssets.length }} assets:
                            </div>
                            <div class="max-h-32 overflow-y-auto bg-gray-50 dark:bg-gray-800 rounded p-2 text-sm">
                                <div v-for="asset in barcodeDialog.selectedAssets" :key="asset.id" class="py-1">
                                    {{ asset.asset_tag }}
                                    <span v-if="asset.serial_number" class="text-gray-500 ml-2">({{ asset.serial_number }})</span>
                                </div>
                            </div>
                        </div>
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="flex items-center space-x-2">
                        <input 
                            type="checkbox" 
                            id="useDefaultPrinter" 
                            v-model="barcodeDialog.useDefaultPrinter"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        />
                        <Label for="useDefaultPrinter" class="font-medium">
                            Print to Default printer
                        </Label>
                    </div>
                    
                    <div v-if="!barcodeDialog.useDefaultPrinter" class="space-y-2">
                        <Label for="installedPrinterName">Select an installed Printer:</Label>
                        <select 
                            id="installedPrinterName" 
                            v-model="barcodeDialog.selectedPrinter"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                            <option value="">Select a printer...</option>
                            <option v-for="printer in barcodeDialog.availablePrinters" :key="printer" :value="printer">
                                {{ printer }}
                            </option>
                        </select>
                    </div>

                    <div v-if="barcodeDialog.status" class="text-sm text-gray-600 dark:text-gray-400">
                        Status: {{ barcodeDialog.status }}
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="barcodeDialog.show = false">
                        Cancel
                    </Button>
                    <Button 
                        @click="printBarcode" 
                        :disabled="barcodeDialog.printing || (!barcodeDialog.useDefaultPrinter && !barcodeDialog.selectedPrinter)"
                    >
                        <Icon v-if="barcodeDialog.printing" name="Loader2" class="h-4 w-4 mr-2 animate-spin" />
                        Print {{ barcodeDialog.selectedAssets.length > 1 ? `${barcodeDialog.selectedAssets.length} Labels` : 'Label' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Send to Print Machine Dialog -->
        <Dialog v-model:open="sendToPrintDialog.show">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Send to Print Machine</DialogTitle>
                    <DialogDescription>
                        Send this asset to the print station queue.
                        <div v-if="sendToPrintDialog.asset" class="mt-2 font-medium">
                            Asset: {{ sendToPrintDialog.asset.asset_tag }}
                        </div>
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="priority">Priority</Label>
                        <select 
                            id="priority" 
                            v-model="sendToPrintDialog.priority"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                            <option value="low">Low</option>
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <Label for="comment">Comment (Optional)</Label>
                        <textarea 
                            id="comment" 
                            v-model="sendToPrintDialog.comment"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            rows="3"
                            placeholder="Add a comment for the print station operator..."
                        ></textarea>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="sendToPrintDialog.show = false">
                        Cancel
                    </Button>
                    <Button 
                        @click="sendToPrintMachine" 
                        :disabled="sendToPrintDialog.sending"
                    >
                        <Icon v-if="sendToPrintDialog.sending" name="Loader2" class="h-4 w-4 mr-2 animate-spin" />
                        Send to Print Machine
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Assignment Tracking Dialog -->
        <Dialog v-model:open="assignmentDialog.show">
            <DialogContent class="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>Assignment History for {{ assignmentDialog.asset?.asset_tag }}</DialogTitle>
                    <DialogDescription>
                        View and manage the assignment history for this asset.
                    </DialogDescription>
                </DialogHeader>
                <div v-if="assignmentDialog.loading" class="text-center py-8">
                    <Icon name="Loader2" class="mx-auto h-12 w-12 text-blue-500 animate-spin" />
                    <p class="mt-4 text-gray-600 dark:text-gray-400">Loading assignments...</p>
                </div>
                <div v-else-if="assignmentDialog.assignments.length === 0" class="text-center py-8">
                    <Icon name="Info" class="mx-auto h-12 w-12 text-gray-400" />
                    <p class="mt-4 text-gray-600 dark:text-gray-400">No assignment history found for this asset.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr class="text-left rtl:text-right">
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Assignment ID
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Assigned To
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Assigned By
                                </th>
                                                                 <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                     Assigned Date
                                 </th>
                                 <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                     Returned Date
                                 </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="assignment in assignmentDialog.assignments" :key="assignment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ assignment.id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Badge :variant="getStatusVariant(assignment.status)">
                                        {{ assignment.status }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ assignment.assigned_to_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ assignment.assigned_by_name }}
                                </td>
                                                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                     {{ formatDate(assignment.assigned_date) }}
                                 </td>
                                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                     {{ formatDate(assignment.returned_date) }}
                                 </td>
                                                                 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                     <div class="flex justify-end rtl:justify-start gap-1">
                                         <Button variant="ghost" size="sm" @click="printAssignmentDocument(assignment.id, 'assignment')" :disabled="assignmentDialog.currentAction === `printing_assignment_${assignment.id}`" title="Print Assignment Document">
                                             <Icon v-if="assignmentDialog.currentAction === `printing_assignment_${assignment.id}`" name="Loader2" class="h-4 w-4 animate-spin" />
                                             <Icon v-else name="Printer" class="h-4 w-4" />
                                         </Button>
                                         <Button variant="ghost" size="sm" @click="uploadSignedDocument(assignment.id, 'assignment')" :disabled="assignmentDialog.currentAction === `uploading_assignment_${assignment.id}`" title="Upload Signed Assignment Document">
                                             <Icon v-if="assignmentDialog.currentAction === `uploading_assignment_${assignment.id}`" name="Loader2" class="h-4 w-4 animate-spin" />
                                             <Icon v-else name="Upload" class="h-4 w-4" />
                                         </Button>
                                         <Button variant="ghost" size="sm" @click="downloadSignedDocument(assignment.id, 'assignment')" :disabled="assignmentDialog.currentAction === `downloading_assignment_${assignment.id}`" title="Download Signed Assignment Document">
                                             <Icon v-if="assignmentDialog.currentAction === `downloading_assignment_${assignment.id}`" name="Loader2" class="h-4 w-4 animate-spin" />
                                             <Icon v-else name="Download" class="h-4 w-4" />
                                         </Button>
                                         <Button v-if="assignment.status === 'returned'" variant="ghost" size="sm" @click="printAssignmentDocument(assignment.id, 'return')" :disabled="assignmentDialog.currentAction === `printing_return_${assignment.id}`" title="Print Return Document">
                                             <Icon v-if="assignmentDialog.currentAction === `printing_return_${assignment.id}`" name="Loader2" class="h-4 w-4 animate-spin" />
                                             <Icon v-else name="FileText" class="h-4 w-4" />
                                         </Button>
                                         <Button v-if="assignment.status === 'returned'" variant="ghost" size="sm" @click="uploadSignedDocument(assignment.id, 'return')" :disabled="assignmentDialog.currentAction === `uploading_return_${assignment.id}`" title="Upload Signed Return Document">
                                             <Icon v-if="assignmentDialog.currentAction === `uploading_return_${assignment.id}`" name="Loader2" class="h-4 w-4 animate-spin" />
                                             <Icon v-else name="Upload" class="h-4 w-4" />
                                         </Button>
                                     </div>
                                 </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="assignmentDialog.show = false">
                        Close
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template> 