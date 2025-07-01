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
import { computed, ref, watch, nextTick } from 'vue';
import type { Asset, Company, AssetCategory, Location, BreadcrumbItem } from '@/types';

const { t } = useI18n();

// Type declarations for JSPrintManager
declare global {
  interface Window {
    JSPM: {
      JSPrintManager: {
        auto_reconnect: boolean
        start: () => void
        websocket_status: number
        WS: {
          onStatusChanged: () => void
        }
        getPrinters: () => Promise<string[]>
      }
      WSStatus: {
        Open: number
        Closed: number
        Blocked: number
      }
      ClientPrintJob: new () => {
        clientPrinter: any
        printerCommands: string
        sendToClient: () => void
      }
      DefaultPrinter: new () => any
      InstalledPrinter: new (printerName: string) => any
    }
  }
}

interface Props {
    assets: {
        data: Asset[];
        links: any[];
        meta: any;
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
        default:
            return 'secondary';
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

const getImageSrc = (asset: Asset) => {
    // First try asset's own image
    if (asset.image_path) {
        return `/storage/${asset.image_path}`;
    }
    // Then try asset template's image
    if (asset.assetTemplate?.image_path) {
        return `/storage/${asset.assetTemplate.image_path}`;
    }
    return null;
};

const handleImageError = (event: Event) => {
    const target = event.target as HTMLImageElement;
    
    // Get asset from data attribute
    const assetId = target.getAttribute('data-asset-id');
    if (!assetId) {
        target.style.display = 'none';
        return;
    }
    
    const asset = props.assets.data.find(a => a.id.toString() === assetId);
    if (!asset) {
        target.style.display = 'none';
        const fallbackElement = document.getElementById(`fallback-${assetId}`);
        if (fallbackElement) {
            fallbackElement.classList.remove('hidden');
        }
        return;
    }
    
    // If asset image failed and we have a template image, try that
    if (asset.image_path && asset.assetTemplate?.image_path) {
        const templateImageSrc = `/storage/${asset.assetTemplate.image_path}`;
        if (target.src !== templateImageSrc) {
            target.src = templateImageSrc;
            return;
        }
    }
    
    // If both failed or no images available, hide image and show fallback icon
    target.style.display = 'none';
    const fallbackElement = document.getElementById(`fallback-${assetId}`);
    if (fallbackElement) {
        fallbackElement.classList.remove('hidden');
    }
};

// Function to dynamically load JavaScript files
const loadScript = (src: string): Promise<void> => {
  return new Promise((resolve, reject) => {
    // Check if script is already loaded
    const existingScript = document.querySelector(`script[src="${src}"]`)
    if (existingScript) {
      resolve()
      return
    }

    const script = document.createElement('script')
    script.src = src
    script.onload = () => resolve()
    script.onerror = () => reject(new Error(`Failed to load script: ${src}`))
    document.head.appendChild(script)
  })
}

// Load all required scripts
const loadRequiredScripts = async (): Promise<void> => {
  try {
    barcodeDialog.value.status = 'Loading print libraries...'
    
    // Load scripts in sequence
    await loadScript('/js/bluebird.min.js')
    await loadScript('/js/jquery-3.2.1.slim.min.js') 
    await loadScript('/js/jsmanager/JSPrintManager.js')
    
    barcodeDialog.value.status = 'Print libraries loaded successfully'
  } catch (error) {
    console.error('Failed to load scripts:', error)
    barcodeDialog.value.status = 'Failed to load print libraries'
  }
}

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
  
  // Load scripts if not already loaded
  if (typeof window.JSPM === 'undefined') {
    await loadRequiredScripts()
  }
  
  initializeJSPrintManager()
}

const initializeJSPrintManager = () => {
  nextTick(() => {
    if (typeof window.JSPM !== 'undefined') {
      // WebSocket settings
      window.JSPM.JSPrintManager.auto_reconnect = true
      window.JSPM.JSPrintManager.start()
      window.JSPM.JSPrintManager.WS.onStatusChanged = function () {
        if (jspmWSStatus()) {
          barcodeDialog.value.status = 'Connected to JSPrintManager'
          // Get client installed printers
          window.JSPM.JSPrintManager.getPrinters().then(function (myPrinters: string[]) {
            barcodeDialog.value.availablePrinters = myPrinters
          })
        }
      }
    } else {
      barcodeDialog.value.status = 'JSPrintManager not loaded'
    }
  })
}

const jspmWSStatus = () => {
  if (window.JSPM.JSPrintManager.websocket_status == window.JSPM.WSStatus.Open) {
    return true
  } else if (window.JSPM.JSPrintManager.websocket_status == window.JSPM.WSStatus.Closed) {
    barcodeDialog.value.status = 'JSPrintManager is not installed or not running! Download JSPM Client App from https://neodynamic.com/downloads/jspm'
    return false
  } else if (window.JSPM.JSPrintManager.websocket_status == window.JSPM.WSStatus.Blocked) {
    barcodeDialog.value.status = 'JSPM has blocked this website!'
    return false
  }
  return false
}

const printBarcode = () => {
  const assetsToPrint = barcodeDialog.value.selectedAssets
  if (!assetsToPrint || assetsToPrint.length === 0) return
  
  if (jspmWSStatus()) {
    barcodeDialog.value.printing = true
    barcodeDialog.value.status = `Printing ${assetsToPrint.length} label(s)...`
    
    try {
      // Create a ClientPrintJob
      const cpj = new window.JSPM.ClientPrintJob()
      
      // Set Printer type
      if (barcodeDialog.value.useDefaultPrinter) {
        cpj.clientPrinter = new window.JSPM.DefaultPrinter()
      } else {
        cpj.clientPrinter = new window.JSPM.InstalledPrinter(barcodeDialog.value.selectedPrinter)
      }
      
      // Generate ZPL commands for all selected assets
      let allCommands = ""
      
      assetsToPrint.forEach((asset, index) => {
        // Set content to print - Create Zebra ZPL commands for 2" x 1" label (406 x 203 dots at 203 DPI)
        let cmds = "^XA"
        
        // Set label dimensions for 2" x 1" at 203 DPI
        cmds += "^PW406"  // Print width 406 dots (2 inches * 203 DPI)
        cmds += "^LL203"  // Label length 203 dots (1 inch * 203 DPI)
        
        // Set print density for darker printing
        cmds += "^MD30"   // Media darkness (0-30, higher = darker)
        cmds += "^SD15"   // Set darkness (0-30, higher = darker)
        
        // Company logo on the left side (centered with top margin)
        cmds += "^FO10,50^GFA,329,329,7,,,,,,K0F1C,J01E3E,I039F1C4,I079E10F,I07BF03E,I073E17E,00213E7FC,00607CFF8,00F87CFF,00F8F9F800C,00FCF8J08,007EF,003E6,03BEK03F,039FK07F,039FK07F,07CFK03F8,008F,I0F8I018,I07J03C,03DK03E68,07FCJ01C7,03FFJ01E7,03FF8I01F3,017FEI09F,I0FE0018F8,0043E003CF8,00F1I03C7C,00F80807C3C,0070700F838,0020F00F008,I03E00F3,I07E01F38,I07C01E78,I03801E3,L01E,L01C,L01,,,,,^FS"
        
        // Company name above asset tag (small text)
        cmds += "^FO70,30^A0N,18,18^FDFAHAD NAWAF ALZEER HOLDING CO.^FS"  // Company name in English
        
        // Asset tag next to logo (with double margin under company name)
        cmds += `^FO70,65^A0N,24,24^FD${asset.asset_tag}^FS`
        
        // Serial number or asset tag as barcode (Code 128) - centered
        cmds += "^FO70,100^BY2,2,35"  // Adjusted position to maintain spacing
        cmds += "^BCN,35,Y,N,N"
        cmds += `^FD${asset.serial_number || asset.asset_tag}^FS`
        
        // QR Code on the right side - centered with top margin
        cmds += "^FO320,50^BQN,2,4"  // Moved down to align with logo and title
        cmds += `^FDMM,${asset.asset_tag}^FS`
        
        // Date at bottom left (below barcode)
        cmds += `^FO70,175^A0N,18,18^FD${new Date().toISOString().split('T')[0]}^FS`
        
        cmds += "^XZ"
        
        allCommands += cmds
      })
      
      cpj.printerCommands = allCommands
      
      // Send print job to printer!
      cpj.sendToClient()
      
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
      barcodeDialog.value.status = 'Error occurred while printing'
      barcodeDialog.value.printing = false
    }
  } else {
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

            <div v-if="assets.data.length === 0" class="text-center py-12">
                <Icon name="HardDrive" class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    {{ t('assets.no_assets') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    {{ search || categoryFilter || locationFilter ? t('assets.no_assets_found') : t('assets.create_first_asset') }}
                </p>
                <Button asChild v-if="!search && !categoryFilter && !locationFilter">
                    <Link :href="route('assets.create')">
                        <Icon name="Plus" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                        {{ t('assets.create_asset') }}
                    </Link>
                </Button>
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
                                            <!-- Asset Image or Template Image -->
                                            <div v-if="getImageSrc(asset)" class="h-10 w-10 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600 relative">
                                                <img 
                                                    :src="getImageSrc(asset)!" 
                                                    :alt="asset.asset_tag"
                                                    :data-asset-id="asset.id"
                                                    class="h-full w-full object-cover"
                                                    @error="handleImageError"
                                                />
                                                <!-- Fallback Icon (hidden by default, shown when image fails) -->
                                                <div class="absolute inset-0 bg-gray-300 dark:bg-gray-600 flex items-center justify-center hidden" :id="`fallback-${asset.id}`">
                                                    <Icon name="HardDrive" class="h-5 w-5 text-gray-700 dark:text-gray-300" />
                                                </div>
                                            </div>
                                            <!-- Fallback Icon (when no image source available) -->
                                            <div v-else class="h-10 w-10 rounded-lg bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                <Icon name="HardDrive" class="h-5 w-5 text-gray-700 dark:text-gray-300" />
                                            </div>
                                        </div>
                                        <div class="ml-4 rtl:ml-0 rtl:mr-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ asset.asset_tag }}
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
                                            v-if="asset.assetTemplate?.image_path"
                                            :src="`/storage/${asset.assetTemplate.image_path}`" 
                                            :alt="asset.assetTemplate.name || 'Template'" 
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
                                    <div v-if="asset.assetTemplate?.name" class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ asset.assetTemplate.name }}</div>
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

            <!-- Pagination would go here if needed -->
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
    </AppLayout>
</template> 