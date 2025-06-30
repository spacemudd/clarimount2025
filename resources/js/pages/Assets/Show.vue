<template>
  <Head :title="asset.asset_tag" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <Heading :title="asset.asset_tag" />
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            {{ t('assets.asset_details') }}
          </p>
        </div>
        <div class="flex items-center space-x-3">
          <Button
            variant="outline"
            @click="showBarcodeDialog"
            class="inline-flex items-center"
          >
            <Icon name="Printer" class="mr-2 h-4 w-4" />
            Barcode
          </Button>
          <Link 
            :href="route('assets.edit', asset.id)"
            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2"
          >
            <Icon name="Edit" class="mr-2 h-4 w-4" />
            {{ t('common.edit') }}
          </Link>
          <Button
            variant="destructive"
            @click="confirmDelete"
          >
            <Icon name="Trash2" class="mr-2 h-4 w-4" />
            {{ t('common.delete') }}
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Asset Image -->
          <Card v-if="asset.image_path">
            <CardHeader>
              <CardTitle>{{ t('assets.asset_image') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="flex justify-center">
                <img
                  :src="`/storage/${asset.image_path}`"
                  :alt="asset.asset_tag"
                  class="max-w-full h-auto max-h-96 rounded-lg border border-gray-200 dark:border-gray-700"
                />
              </div>
            </CardContent>
          </Card>

          <!-- Basic Information -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('assets.basic_information') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.asset_tag') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                    {{ asset.asset_tag }}
                  </p>
                </div>
                
                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.serial_number') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                    {{ asset.serial_number || '—' }}
                  </p>
                </div>

                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.manufacturer') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ asset.manufacturer || '—' }}
                  </p>
                </div>

                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.model_name') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ asset.model_name || '—' }}
                  </p>
                </div>

                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.model_number') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ asset.model_number || '—' }}
                  </p>
                </div>

                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.status') }}
                  </Label>
                  <Badge 
                    :variant="getStatusVariant(asset.status)"
                    class="mt-1"
                  >
                    {{ t(`assets.status_${asset.status}`) }}
                  </Badge>
                </div>
              </div>

              <div v-if="asset.notes">
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('assets.notes') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">
                  {{ asset.notes }}
                </p>
              </div>
            </CardContent>
          </Card>

          <!-- Template Information -->
          <Card v-if="asset.asset_template">
            <CardHeader>
              <CardTitle>{{ t('assets.template_information') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.template_name') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ asset.asset_template.name }}
                  </p>
                </div>
                
                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('assets.template_type') }}
                  </Label>
                  <Badge 
                    :variant="asset.asset_template.is_global ? 'secondary' : 'default'"
                    class="mt-1"
                  >
                    {{ asset.asset_template.is_global ? t('asset_templates.global') : asset.asset_template.company?.name_en }}
                  </Badge>
                </div>
              </div>
              
              <div class="flex justify-end">
                <Link 
                  :href="route('asset-templates.show', asset.asset_template.id)"
                  class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  {{ t('assets.view_template') }}
                  <Icon name="ExternalLink" class="ml-1 h-3 w-3" />
                </Link>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Location Information -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('assets.location_information') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('assets.location') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ asset.location?.name || '—' }}
                </p>
              </div>
              
              <div v-if="asset.location?.code">
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('locations.code') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                  {{ asset.location.code }}
                </p>
              </div>

              <div v-if="asset.location?.building">
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('locations.building') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ asset.location.building }}
                </p>
              </div>

              <div v-if="asset.location?.office_number">
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('locations.office_number') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ asset.location.office_number }}
                </p>
              </div>

              <div v-if="asset.location" class="flex justify-end">
                <Link 
                  :href="route('locations.show', asset.location.id)"
                  class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  {{ t('assets.view_location') }}
                  <Icon name="ExternalLink" class="ml-1 h-3 w-3" />
                </Link>
              </div>
            </CardContent>
          </Card>

          <!-- Category Information -->
          <Card v-if="asset.category">
            <CardHeader>
              <CardTitle>{{ t('assets.category_information') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('assets.category') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ asset.category.name }}
                </p>
              </div>
              
              <div v-if="asset.category.code">
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('asset_categories.code') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                  {{ asset.category.code }}
                </p>
              </div>

              <div class="flex justify-end">
                <Link 
                  :href="route('asset-categories.show', asset.category.id)"
                  class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  {{ t('assets.view_category') }}
                  <Icon name="ExternalLink" class="ml-1 h-3 w-3" />
                </Link>
              </div>
            </CardContent>
          </Card>

          <!-- Asset Metadata -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('assets.asset_metadata') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('common.created_at') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ new Date(asset.created_at).toLocaleDateString() }}
                </p>
              </div>
              
              <div>
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('common.updated_at') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ new Date(asset.updated_at).toLocaleDateString() }}
                </p>
              </div>

              <div>
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('assets.company') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ asset.company?.name_en || '—' }}
                </p>
              </div>
            </CardContent>
          </Card>

          <!-- Assignment Information -->
          <Card v-if="asset.status === 'assigned' && asset.assigned_to">
            <CardHeader>
              <CardTitle>{{ t('assets.assignment_information') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('assets.assigned_to') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ asset.assigned_to?.name || '—' }}
                </p>
              </div>
              
              <div v-if="asset.assigned_date">
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('assets.assigned_date') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ new Date(asset.assigned_date).toLocaleDateString() }}
                </p>
              </div>
            </CardContent>
          </Card>
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
            Print Now
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="deleteDialog.show">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{ t('assets.confirm_delete') }}</DialogTitle>
          <DialogDescription>
            {{ t('assets.delete_warning') }}
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" @click="deleteDialog.show = false">
            {{ t('common.cancel') }}
          </Button>
          <Button variant="destructive" @click="handleDelete" :disabled="deleteDialog.loading">
            <Icon v-if="deleteDialog.loading" name="Loader2" class="h-4 w-4 mr-2 animate-spin" />
            {{ t('common.delete') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, nextTick } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import Icon from '@/components/Icon.vue'
import Heading from '@/components/Heading.vue'
import type { BreadcrumbItem } from '@/types'

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

const { t } = useI18n()

interface Asset {
  id: number
  asset_tag: string
  serial_number?: string
  manufacturer?: string
  model_name?: string
  model_number?: string
  status: string
  notes?: string
  image_path?: string
  assigned_date?: string
  created_at: string
  updated_at: string
  asset_template?: {
    id: number
    name: string
    is_global: boolean
    company?: {
      name_en: string
    }
  }
  location?: {
    id: number
    name: string
    code?: string
    building?: string
    office_number?: string
  }
  category?: {
    id: number
    name: string
    code?: string
  }
  company?: {
    name_en: string
  }
  assigned_to?: {
    name: string
  }
}

interface Props {
  asset: Asset
}

const props = defineProps<Props>()

const deleteDialog = ref({
  show: false,
  loading: false
})

const barcodeDialog = ref({
  show: false,
  printing: false,
  useDefaultPrinter: false,
  selectedPrinter: '',
  availablePrinters: [] as string[],
  status: ''
})

const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: t('nav.dashboard'),
    href: '/dashboard',
  },
  {
    title: t('nav.assets'),
    href: '/assets',
  },
  {
    title: props.asset.asset_tag,
    href: `/assets/${props.asset.id}`,
  },
])

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'available':
      return 'default'
    case 'assigned':
      return 'secondary'
    case 'maintenance':
      return 'destructive'
    case 'retired':
      return 'outline'
    default:
      return 'secondary'
  }
}

const confirmDelete = () => {
  deleteDialog.value.show = true
}

const handleDelete = () => {
  deleteDialog.value.loading = true
  
  router.delete(route('assets.destroy', props.asset.id), {
    onSuccess: () => {
      router.visit(route('assets.index'))
    },
    onFinish: () => {
      deleteDialog.value.loading = false
    }
  })
}

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

const showBarcodeDialog = async () => {
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
  if (jspmWSStatus()) {
    barcodeDialog.value.printing = true
    
    try {
      // Create a ClientPrintJob
      const cpj = new window.JSPM.ClientPrintJob()
      
      // Set Printer type
      if (barcodeDialog.value.useDefaultPrinter) {
        cpj.clientPrinter = new window.JSPM.DefaultPrinter()
      } else {
        cpj.clientPrinter = new window.JSPM.InstalledPrinter(barcodeDialog.value.selectedPrinter)
      }
      
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
      cmds += `^FO70,65^A0N,24,24^FD${props.asset.asset_tag}^FS`
      
      // Serial number or asset tag as barcode (Code 128) - centered
      cmds += "^FO70,100^BY2,2,35"  // Adjusted position to maintain spacing
      cmds += "^BCN,35,Y,N,N"
      cmds += `^FD${props.asset.serial_number || props.asset.asset_tag}^FS`
      
      // QR Code on the right side - centered with top margin
      cmds += "^FO320,50^BQN,2,4"  // Moved down to align with logo and title
      cmds += `^FDMM,${props.asset.asset_tag}^FS`
      
      // Date at bottom left (below barcode)
      cmds += `^FO70,175^A0N,18,18^FD${new Date().toISOString().split('T')[0]}^FS`
      
      cmds += "^XZ"
      
      cpj.printerCommands = cmds
      
      // Send print job to printer!
      cpj.sendToClient()
      
      barcodeDialog.value.status = 'Print job sent successfully!'
      
      // Close dialog after successful print
      setTimeout(() => {
        barcodeDialog.value.show = false
        barcodeDialog.value.printing = false
      }, 2000)
      
    } catch (error) {
      console.error('Print error:', error)
      barcodeDialog.value.status = 'Error occurred while printing'
      barcodeDialog.value.printing = false
    }
  } else {
    barcodeDialog.value.printing = false
  }
}
</script>

<style>
/* Add any custom styles if needed */
</style> 